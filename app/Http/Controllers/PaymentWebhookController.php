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

        $providedSecret = (string) (
            $request->header('X-Webhook-Secret')
            ?: $request->input('secret')
            ?: $request->query('secret', '')
        );

        // Fallback if Android MacroDroid sent unescaped newlines inside JSON body
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
            ?? ''
        ));

        if ($rawMessage === '' && preg_match('/"message"\s*:\s*"([\s\S]*)"\s*\}?\s*$/', $rawBody, $mm)) {
            $rawMessage = trim($mm[1]);
        }
        if ($rawMessage === '' && $rawBody !== '') {
            $rawMessage = trim($rawBody);
        }

        $explicitAmount = $request->input('amount');
        $parsedAmount = is_numeric($explicitAmount)
            ? (float) $explicitAmount
            : $this->extractAmountFromNotificationText($rawMessage);

        $nowStr = Carbon::now('Asia/Bangkok')->format('d/m/Y H:i:s');
        Setting::set('android_webhook_last_received_at', $nowStr, 'payment');
        Setting::set('android_webhook_last_message', Str::limit($rawMessage ?: ('Amount: ' . $parsedAmount), 250), 'payment');
        Setting::set('android_webhook_last_amount', $parsedAmount !== null ? number_format($parsedAmount, 2, '.', '') : '', 'payment');

        if ($parsedAmount === null || $parsedAmount <= 0) {
            Setting::set('android_webhook_last_matched_order_no', '', 'payment');

            return response()->json([
                'success' => true,
                'matched' => false,
                'message' => 'ไม่พบยอดเงินในข้อความแจ้งเตือน',
                'parsed_amount' => null,
            ]);
        }

        $parsedSatang = (int) round($parsedAmount * 100);

        $matchedOrder = DB::transaction(function () use ($parsedSatang) {
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

            return response()->json([
                'success' => true,
                'matched' => false,
                'message' => 'ได้รับแจ้งเตือนแล้ว แต่ยังไม่พบบิลรอโอนที่ยอดตรงกัน',
                'parsed_amount' => round($parsedAmount, 2),
            ]);
        }

        Setting::set('android_webhook_last_matched_order_no', $matchedOrder->order_no, 'payment');

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
