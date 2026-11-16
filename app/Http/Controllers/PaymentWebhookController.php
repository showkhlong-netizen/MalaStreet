<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentWebhookController extends Controller
{
    public static function getWebhookConfig(): array
    {
        $secret = Setting::get('android_payment_webhook_secret');
        if (empty($secret)) {
            $secret = 'ms_' . Str::random(24);
            Setting::set('android_payment_webhook_secret', $secret, 'payment');
        }

        $webhookUrl = url('/webhooks/android-payment');
        if (str_contains($webhookUrl, 'onrender.com') && str_starts_with($webhookUrl, 'http://')) {
            $webhookUrl = 'https://' . substr($webhookUrl, 7);
        }

        return [
            'webhook_url' => $webhookUrl,
            'secret' => $secret,
            'last_received_at' => Setting::get('android_webhook_last_received_at', ''),
            'last_amount' => Setting::get('android_webhook_last_amount', ''),
            'last_message' => Setting::get('android_webhook_last_message', ''),
            'last_matched_order_no' => Setting::get('android_webhook_last_matched_order_no', ''),
            'last_result' => Setting::get('android_webhook_last_result', ''),
        ];
    }

    public function getWebhookStatusJson(): JsonResponse
    {
        $pendingOrders = Order::where('payment_method', 'promptpay')
            ->where('payment_status', 'unpaid')
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('created_at', '>=', Carbon::now()->subMinutes(60))
            ->latest('id')
            ->take(10)
            ->get(['id', 'order_no', 'queue_number', 'customer_name', 'total_amount', 'status', 'created_at'])
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'order_no' => $o->order_no,
                'queue_number' => $o->queue_number,
                'customer_name' => $o->customer_name ?: 'ลูกค้าออนไลน์',
                'total_amount' => (float) $o->total_amount,
                'created_at_hm' => $o->created_at ? $o->created_at->timezone('Asia/Bangkok')->format('H:i') : '',
            ])
            ->values();

        return response()->json([
            'success' => true,
            'config' => self::getWebhookConfig(),
            'pending_orders' => $pendingOrders,
        ]);
    }

    /**
     * Receive bank/K PLUS/LINE notification from shop Android phone/tablet (MacroDroid/Automate).
     */
    public function handleAndroidPayment(Request $request): JsonResponse
    {
        $config = self::getWebhookConfig();
        $rawBody = (string) $request->getContent();

        $decodedFallback = null;
        if ($rawBody !== '' && empty($request->all())) {
            $sanitizedJson = preg_replace('/[\r\n\t]+/', ' ', $rawBody);
            $decoded = json_decode($sanitizedJson, true);
            if (is_array($decoded)) {
                $decodedFallback = $decoded;
            }
        }

        $providedSecret = (string) (
            $request->header('X-Webhook-Secret')
            ?: $request->input('secret')
            ?: ($decodedFallback['secret'] ?? '')
            ?: $request->query('secret', '')
        );

        // Fallback if Android MacroDroid sent raw JSON text
        if ($providedSecret === '' && preg_match('/"secret"\s*:\s*"([^"]+)"/', $rawBody, $sm)) {
            $providedSecret = $sm[1];
        }

        if ($providedSecret === '' || !hash_equals($config['secret'], $providedSecret)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized webhook secret',
            ], 401);
        }

        $rawMessage = trim((string) (
            $request->input('message')
            ?? $request->input('text')
            ?? $request->input('notification')
            ?? $request->input('body')
            ?? ($decodedFallback['message'] ?? $decodedFallback['text'] ?? $decodedFallback['notification'] ?? $decodedFallback['body'] ?? '')
        ));

        if ($rawMessage === '' && preg_match('/"message"\s*:\s*"([\s\S]*)"\s*\}?\s*$/', $rawBody, $mm)) {
            $rawMessage = trim($mm[1]);
        }
        if ($rawMessage === '' && $rawBody !== '') {
            $rawMessage = trim($rawBody);
        }

        $explicitAmount = $request->input('amount') ?? ($decodedFallback['amount'] ?? null);
        $isOutgoingTransfer = $this->looksLikeOutgoingTransfer($rawMessage);
        $parsedAmount = $isOutgoingTransfer
            ? null
            : (is_numeric($explicitAmount)
                ? (float) $explicitAmount
                : $this->extractAmountFromNotificationText($rawMessage));

        $nowStr = Carbon::now('Asia/Bangkok')->format('d/m/Y H:i:s');
        Setting::set('android_webhook_last_received_at', $nowStr, 'payment');
        Setting::set('android_webhook_last_message', Str::limit($rawMessage ?: ('Amount: ' . $parsedAmount), 250), 'payment');
        Setting::set('android_webhook_last_amount', $parsedAmount !== null ? number_format($parsedAmount, 2, '.', '') : '', 'payment');

        if ($isOutgoingTransfer) {
            Setting::set('android_webhook_last_matched_order_no', '', 'payment');
            Setting::set('android_webhook_last_result', 'ℹ️ ข้ามรายการโอนออก (ไม่ใช่เงินเข้าจากลูกค้า)', 'payment');

            return response()->json([
                'success' => true,
                'matched' => false,
                'ignored_outgoing' => true,
                'message' => 'ข้ามรายการโอนออก (ไม่ใช่เงินเข้า)',
                'parsed_amount' => null,
            ]);
        }

        if ($parsedAmount === null || $parsedAmount <= 0) {
            Setting::set('android_webhook_last_matched_order_no', '', 'payment');
            Setting::set('android_webhook_last_result', '⚠️ ไม่พบตัวเลขยอดเงินในข้อความแจ้งเตือน', 'payment');

            return response()->json([
                'success' => true,
                'matched' => false,
                'message' => 'ไม่พบยอดเงินในข้อความแจ้งเตือน',
                'parsed_amount' => null,
            ]);
        }

        $parsedSatang = (int) round($parsedAmount * 100);
        $mismatchHint = '';

        $matchedOrder = DB::transaction(function () use ($parsedSatang, $parsedAmount, &$mismatchHint) {
            $candidates = Order::where('payment_method', 'promptpay')
                ->where('payment_status', 'unpaid')
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('created_at', '>=', Carbon::now()->subMinutes(60))
                ->latest('id')
                ->lockForUpdate()
                ->get();

            $found = $candidates->first(function (Order $order) use ($parsedSatang) {
                return (int) round(((float) $order->total_amount) * 100) === $parsedSatang;
            });

            if (!$found) {
                if ($candidates->isNotEmpty()) {
                    $closest = $candidates->sortBy(fn (Order $o) => abs((float) $o->total_amount - $parsedAmount))->first();
                    $diff = round($parsedAmount - (float) $closest->total_amount, 2);
                    $diffLabel = $diff < 0
                        ? ('ขาด ฿' . number_format(abs($diff), 2))
                        : ('เกิน ฿' . number_format($diff, 2));
                    $mismatchHint = "⚠️ ยอดไม่ตรง! เงินเข้า ฿" . number_format($parsedAmount, 2) . " แต่บิล {$closest->order_no} (คิว {$closest->queue_number}) ยอด ฿" . number_format((float) $closest->total_amount, 2) . " ({$diffLabel})";
                }
                return null;
            }

            $found->payment_status = 'paid';
            $found->paid_at = Carbon::now();
            if ($found->status === 'pending') {
                $found->status = 'confirmed';
            }
            $found->save();

            return $found;
        });

        if (!$matchedOrder) {
            Setting::set('android_webhook_last_matched_order_no', '', 'payment');
            $resultMsg = $mismatchHint !== '' ? $mismatchHint : ('ℹ️ ได้รับยอด ฿' . number_format($parsedAmount, 2) . ' (ยังไม่มีบิลรอโอนที่ยอดตรงกัน)');
            Setting::set('android_webhook_last_result', $resultMsg, 'payment');

            return response()->json([
                'success' => true,
                'matched' => false,
                'message' => $resultMsg,
                'parsed_amount' => round($parsedAmount, 2),
            ]);
        }

        $okResult = "✅ ยอดตรงครบถ้วน! รับเงิน ฿" . number_format((float) $matchedOrder->total_amount, 2) . " -> ยืนยันบิล {$matchedOrder->order_no} (คิว {$matchedOrder->queue_number}) อัตโนมัติแล้ว";
        Setting::set('android_webhook_last_matched_order_no', $matchedOrder->order_no, 'payment');
        Setting::set('android_webhook_last_result', $okResult, 'payment');

        AuditLog::record(
            'payment',
            'webhook',
            "ยืนยันยอดโอนอัตโนมัติจาก Android: ฿" . number_format((float) $matchedOrder->total_amount, 2),
            "คิว {$matchedOrder->queue_number} ({$matchedOrder->order_no})",
            [
                'order_id' => $matchedOrder->id,
                'parsed_amount' => round($parsedAmount, 2),
                'raw_message' => Str::limit($rawMessage, 150),
            ]
        );

        return response()->json([
            'success' => true,
            'matched' => true,
            'order_no' => $matchedOrder->order_no,
            'queue_number' => $matchedOrder->queue_number,
            'amount' => (float) $matchedOrder->total_amount,
        ]);
    }

    /**
     * Public status check endpoint polled by the customer's QR scan modal.
     */
    public function checkOrderPaymentStatus(string $orderNo): JsonResponse
    {
        $order = Order::where('order_no', $orderNo)
            ->select(['id', 'order_no', 'queue_number', 'payment_status', 'status', 'total_amount'])
            ->first();
        if (!$order) {
            return response()->json(['success' => false, 'paid' => false], 404);
        }

        return response()->json([
            'success' => true,
            'paid' => $order->payment_status === 'paid',
            'payment_status' => $order->payment_status,
            'status' => $order->status,
            'order_no' => $order->order_no,
            'queue_number' => $order->queue_number,
            'total_amount' => (float) $order->total_amount,
        ]);
    }

    private function looksLikeOutgoingTransfer(string $text): bool
    {
        if ($text === '') {
            return false;
        }
        $hasIncomingKeyword = (bool) preg_match('/(?:เงินเข้า|ได้รับ|รับเงิน|เข้าพร้อมเพย์|เข้าบัญชี|received)/u', $text);
        if ($hasIncomingKeyword) {
            return false;
        }
        return (bool) preg_match('/(?:โอนเงินสำเร็จ|โอนออก|ชำระเงินสำเร็จ|จ่ายบิล|หักบัญชี)/u', $text);
    }

    /**
     * Extract THB amount from Thai bank notification messages (K PLUS, KBank Live, SCB, etc.).
     */
    private function extractAmountFromNotificationText(string $text): ?float
    {
        if ($text === '') {
            return null;
        }

        // Priority 1: Amount right before บาท / บ. / THB (e.g., "เงินเข้า 35.00 บาท", "ได้รับ +37.00 บาท", "1,250.50 บ.")
        if (preg_match('/(\d{1,3}(?:,\d{3})*(?:\.\d{1,2})?|\d+(?:\.\d{1,2})?)\s*(?:บาท|บ\.|THB)/u', $text, $m)) {
            return (float) str_replace(',', '', $m[1]);
        }

        // Priority 2: Currency before amount (e.g., "฿37.00", "THB 37.00", "จำนวน 37.00")
        if (preg_match('/(?:฿|THB|จำนวน|ยอด|เงินเข้า)\s*\+?(\d{1,3}(?:,\d{3})*(?:\.\d{1,2})?|\d+(?:\.\d{1,2})?)/u', $text, $m)) {
            return (float) str_replace(',', '', $m[1]);
        }

        // Priority 3: Decimal amount like 35.00 or 1,250.50 (ignores times like 08:14 or account numbers with hyphens)
        if (preg_match('/(?<![\d:-])(\d{1,3}(?:,\d{3})*\.\d{2}|\d+\.\d{2})(?![\d:-])/', $text, $m)) {
            return (float) str_replace(',', '', $m[1]);
        }

        return null;
    }
}
