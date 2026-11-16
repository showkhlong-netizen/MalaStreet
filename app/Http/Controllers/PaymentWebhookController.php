<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Order;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        return [
            'webhook_url' => url('/webhooks/android-payment'),
            'secret' => $secret,
            'last_received_at' => Setting::get('android_webhook_last_received_at', ''),
            'last_amount' => Setting::get('android_webhook_last_amount', ''),
            'last_message' => Setting::get('android_webhook_last_message', ''),
            'last_matched_order_no' => Setting::get('android_webhook_last_matched_order_no', ''),
        ];
    }

    /**
     * Receive bank/K PLUS/LINE notification from shop Android phone/tablet (MacroDroid/Automate).
     */
    public function handleAndroidPayment(Request $request): JsonResponse
    {
        $config = self::getWebhookConfig();
        $providedSecret = (string) (
            $request->header('X-Webhook-Secret')
            ?: $request->input('secret')
            ?: $request->query('secret', '')
        );

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

        $candidates = Order::where('payment_method', 'promptpay')
            ->where('payment_status', 'unpaid')
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->latest('id')
            ->get();

        $parsedSatang = (int) round($parsedAmount * 100);
        $matchedOrder = $candidates->first(function (Order $order) use ($parsedSatang) {
            return (int) round(((float) $order->total_amount) * 100) === $parsedSatang;
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

        $matchedOrder->payment_status = 'paid';
        $matchedOrder->paid_at = Carbon::now();
        if ($matchedOrder->status === 'pending') {
            $matchedOrder->status = 'confirmed';
        }
        $matchedOrder->save();

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
        $order = Order::where('order_no', $orderNo)->first();
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

        // Priority 1: Amount right before บาท / บ. / THB (e.g., "เงินเข้า 35.00 บาท", "1,250.50 บ.")
        if (preg_match('/(\d{1,3}(?:,\d{3})*(?:\.\d{1,2})?|\d+(?:\.\d{1,2})?)\s*(?:บาท|บ\.|THB)/u', $text, $m)) {
            return (float) str_replace(',', '', $m[1]);
        }

        // Priority 2: Decimal amount like 35.00 or 1,250.50 (ignores times like 08:14 or phone/account numbers with hyphens)
        if (preg_match('/(?<![\d:-])(\d{1,3}(?:,\d{3})*\.\d{2}|\d+\.\d{2})(?![\d:-])/', $text, $m)) {
            return (float) str_replace(',', '', $m[1]);
        }

        return null;
    }
}
