<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\InventoryDaily;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PriceHistory;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    /**
     * Display POS Cashier interface.
     */
    public function index(Request $request): View|JsonResponse
    {
        $categories = Category::with(['products' => function ($query) {
            $query->with('rawMaterial')->where('is_available', true)->orderBy('sort_order');
        }])->where('is_active', true)->orderBy('sort_order')->get();

        if ($request->wantsJson() || $request->boolean('json')) {
            $allProducts = $categories->flatMap(fn ($c) => $c->products)->sortBy('sort_order')->values();
            return response()->json([
                'success' => true,
                'products' => $allProducts->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->display_name,
                    'original_name' => $p->name,
                    'image_url' => $p->display_image,
                    'price_per_stick' => (float) $p->price_per_stick,
                    'stock_sticks' => (int) $p->stock_sticks,
                    'raw_material_id' => $p->raw_material_id,
                    'category_slug' => $p->category->slug ?? 'all',
                ])->values(),
                // Active categories (name/icon) so the cashier pills stay in sync with the stock page
                'categories' => $categories->map(fn ($c) => [
                    'id' => $c->id,
                    'slug' => $c->slug,
                    'name' => $c->name,
                    'icon' => (empty($c->icon) || in_array($c->icon, ['🥩', '🍗', '🍢', '🥬', '🍥', '🥤', '🍟'])) ? null : (str_starts_with($c->icon, 'http') ? $c->icon : asset($c->icon)),
                ])->values(),
            ]);
        }

        $today = Carbon::today();

        // Today's stats for POS
        $todayOrders = Order::whereDate('created_at', $today)->orderByDesc('created_at')->get();
        $totalSalesToday = $todayOrders->where('payment_status', 'paid')->sum('total_amount');
        $totalOrdersToday = $todayOrders->count();
        $nextQueueNumber = 'A' . str_pad($todayOrders->where('order_type', 'pos')->count() + 1, 2, '0', STR_PAD_LEFT);
        $priceHistoriesCount = PriceHistory::count();

        return view('pos.index', compact('categories', 'todayOrders', 'totalSalesToday', 'totalOrdersToday', 'nextQueueNumber', 'priceHistoriesCount'));
    }

    /**
     * Store new POS order.
     */
    public function storeOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cooking_type' => 'required|in:grill,soup',
            'items.*.grill_style' => 'nullable|in:brush,dip',
            'items.*.soup_style' => 'nullable|in:mala_rich,tomyum_rich,black_soup',
            'items.*.spiciness_level' => 'required|in:none,mild,medium,hot',
            'items.*.special_instructions' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,promptpay',
            'cash_received' => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'coupon_code' => 'nullable|string|max:30',
            'fulfillment_mode' => 'nullable|in:pos,delivery,pickup',
            'notes' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $today = Carbon::today();
            $posCountToday = Order::whereDate('created_at', $today)->where('order_type', 'pos')->count();
            $queueNumber = 'A' . str_pad($posCountToday + 1, 2, '0', STR_PAD_LEFT);
            $uniqueSuffix = strtoupper(\Illuminate\Support\Str::random(3));
            $orderNo = 'MS-POS-' . $today->format('Ymd') . '-' . str_pad($posCountToday + 1, 3, '0', STR_PAD_LEFT) . '-' . $uniqueSuffix;

            $subtotal = 0;
            $soupExtraTotal = 0;
            $orderItemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                $lineSubtotal = $product->price_per_stick * $qty;
                $subtotal += $lineSubtotal;

                // Extra soup charge if soup style selected (optional: e.g. 15 baht if flat, or 0 included)
                $soupExtra = 0;
                if ($item['cooking_type'] === 'soup') {
                    // In Mala street food, soup is typically 10-15 THB per bowl or included. We can add 15 THB per bowl if specified.
                    $soupExtra = 0.00;
                }
                $soupExtraTotal += $soupExtra;

                // Deduct inventory sticks from Product
                $product->decrement('stock_sticks', $qty);

                // Update or create InventoryDaily for today
                $inventory = InventoryDaily::firstOrCreate(
                    [
                        'record_date' => $today,
                        'product_id' => $product->id,
                    ],
                    [
                        'opening_sticks' => $product->stock_sticks + $qty, // default assumption
                        'added_sticks' => 0,
                        'sold_sticks' => 0,
                        'waste_sticks' => 0,
                        'expected_closing_sticks' => $product->stock_sticks,
                    ]
                );

                $inventory->increment('sold_sticks', $qty);
                $inventory->expected_closing_sticks = max(0, ($inventory->opening_sticks + $inventory->added_sticks) - $inventory->sold_sticks - $inventory->waste_sticks);
                $inventory->save();

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_sticks' => $qty,
                    'unit_price' => $product->price_per_stick,
                    'subtotal' => $lineSubtotal,
                    'cooking_type' => $item['cooking_type'],
                    'grill_style' => $item['cooking_type'] === 'grill' ? ($item['grill_style'] ?? 'brush') : null,
                    'soup_style' => $item['cooking_type'] === 'soup' ? ($item['soup_style'] ?? 'mala_rich') : null,
                    'spiciness_level' => $item['spiciness_level'] ?? 'medium',
                    'special_instructions' => $item['special_instructions'] ?? null,
                ];
            }

            $fulfillmentMode = $validated['fulfillment_mode'] ?? 'pos';
            $deliveryFee = ($fulfillmentMode === 'delivery') ? 30.00 : 0.00;
            $discountAmount = 0.00;
            $couponCode = strtoupper(trim($validated['coupon_code'] ?? ''));

            if ($couponCode === 'DISC10' && $subtotal >= 150) {
                $discountAmount = 10.00;
            } elseif ($couponCode === 'DISC20' && $subtotal >= 300) {
                $discountAmount = 20.00;
            } elseif ($couponCode === 'FREESHIP' && $subtotal >= 400 && $fulfillmentMode === 'delivery') {
                $discountAmount = $deliveryFee;
            }

            $totalExtra = $soupExtraTotal + $deliveryFee;
            $totalAmount = max(0, $subtotal + $totalExtra - $discountAmount);
            $cashReceived = $validated['cash_received'] ?? null;
            $changeAmount = null;

            if ($validated['payment_method'] === 'cash' && $cashReceived !== null) {
                $changeAmount = max(0, $cashReceived - $totalAmount);
            }

            $order = Order::create([
                'order_no' => $orderNo,
                'queue_number' => $queueNumber,
                'order_type' => 'pos',
                'customer_name' => $validated['customer_name'] ?: 'ลูกค้าหน้าร้าน',
                'customer_phone' => $validated['customer_phone'] ?? null,
                'pickup_time' => $fulfillmentMode === 'delivery' ? 'เดลิเวอรี่ (ส่งที่บ้าน)' : ($fulfillmentMode === 'pickup' ? 'รับที่ร้าน' : 'บริการหน้าร้าน'),
                'status' => 'cooking', // POS orders immediately start cooking
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'paid',
                'subtotal' => $subtotal,
                'soup_extra' => $totalExtra,
                'discount' => $discountAmount,
                'total_amount' => $totalAmount,
                'cash_received' => $cashReceived,
                'change_amount' => $changeAmount,
                'notes' => $validated['notes'] ?? null,
                'paid_at' => Carbon::now(),
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            $order->load('items.product');

            AuditLog::record(
                'pos',
                'create',
                'เปิดบิลขายหน้าร้าน (POS)',
                "คิว {$queueNumber} ({$orderNo}) ยอด ฿" . number_format($totalAmount, 2),
                [
                    'order_id' => $order->id,
                    'order_no' => $orderNo,
                    'queue_number' => $queueNumber,
                    'total_amount' => $totalAmount,
                    'payment_method' => $validated['payment_method'],
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'บันทึกออเดอร์หน้าร้านเรียบร้อยแล้ว',
                'order' => $order,
            ]);
        });
    }

    /**
     * Kitchen Display System (KDS / จอครัว).
     */
    public function kitchen(): View
    {
        $today = Carbon::today();
        $activeOrders = Order::with('items')
            ->whereDate('created_at', $today)
            ->whereIn('status', ['pending', 'confirmed', 'cooking', 'ready'])
            ->orderBy('created_at')
            ->get();

        $completedCount = Order::whereDate('created_at', $today)->where('status', 'completed')->count();

        return view('pos.kitchen', compact('activeOrders', 'completedCount'));
    }

    /**
     * Update order status (pending -> cooking -> ready -> completed).
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cooking,ready,completed,cancelled',
        ]);

        $oldStatus = $order->status;
        $order->status = $validated['status'];
        if ($validated['status'] === 'completed') {
            $order->completed_at = Carbon::now();
        }
        $order->save();

        $statusLabels = [
            'pending' => 'รอคิว',
            'confirmed' => 'รับออเดอร์',
            'cooking' => 'กำลังปรุง',
            'ready' => 'พร้อมเสิร์ฟ',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิกออเดอร์',
        ];
        $statusLabel = $statusLabels[$validated['status']] ?? $validated['status'];

        AuditLog::record(
            'kitchen',
            'status',
            "อัปเดตสถานะครัว: {$statusLabel}",
            "คิว {$order->queue_number} ({$order->order_no})",
            [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $order->status,
            ]
        );

        return response()->json([
            'success' => true,
            'status' => $order->status,
            'completed_at' => $order->completed_at ? $order->completed_at->toTimeString() : null,
        ]);
    }
}
