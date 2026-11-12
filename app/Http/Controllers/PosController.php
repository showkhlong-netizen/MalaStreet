<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\ChatMessage;
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
        $unreadChatCount = ChatMessage::where('sender', 'customer')->where('is_read', false)->count();

        return view('pos.kitchen', compact('activeOrders', 'completedCount', 'unreadChatCount'));
    }

    /**
     * Admin Kitchen Chat (Mobile Frame Layout with IP & Order Tracking).
     */
    public function kitchenChat(Request $request): View
    {
        $orderNo = $request->query('order_no');
        $activeOrder = null;

        if ($orderNo) {
            $activeOrder = Order::with('items')->where('order_no', $orderNo)->first();
        }

        // Get recent orders list for LINE / Messenger chat inbox
        $recentOrders = Order::with('items')
            ->latest('id')
            ->take(35)
            ->get();

        $recentOrders->each(function ($ord) {
            $lastMsg = ChatMessage::where(function ($q) use ($ord) {
                $q->where('order_no', $ord->order_no);
                if ($ord->customer_phone) {
                    $q->orWhere('customer_phone', $ord->customer_phone);
                }
            })->latest('id')->first();

            $ord->last_message = $lastMsg ? $lastMsg->message : null;
            $ord->last_message_time = $lastMsg ? $lastMsg->created_at : $ord->created_at;
            $ord->last_message_sender = $lastMsg ? $lastMsg->sender : null;

            $ord->unread_count = ChatMessage::where('order_no', $ord->order_no)
                ->where('sender', 'customer')
                ->where('is_read', false)
                ->count();

            // Online criteria: active order in kitchen OR message within last 12 hours
            $isActiveInKitchen = in_array($ord->status, ['pending', 'confirmed', 'cooking', 'ready']);
            $isRecent = $lastMsg && $lastMsg->created_at && $lastMsg->created_at->gt(now()->subHours(12));
            $ord->is_online = $isActiveInKitchen || $isRecent;
        });

        // Split into Online and Offline collections
        $onlineOrders = $recentOrders->filter(fn($o) => $o->is_online)->values();
        $offlineOrders = $recentOrders->filter(fn($o) => !$o->is_online)->values();

        // Get messages for current active order/customer (if viewing chat room)
        $messages = collect();
        $targetIp = '127.0.0.1';

        if ($activeOrder) {
            $messages = ChatMessage::where(function ($q) use ($activeOrder) {
                $q->where('order_no', $activeOrder->order_no);
                if ($activeOrder->customer_phone) {
                    $q->orWhere('customer_phone', $activeOrder->customer_phone);
                }
            })->orderBy('created_at', 'asc')->get();

            // Mark customer messages as read
            ChatMessage::where('order_no', $activeOrder->order_no)
                ->where('sender', 'customer')
                ->where('is_read', false)
                ->update(['is_read' => true]);

            // Resolve IP
            $targetIp = $activeOrder->ip_address;
            if (empty($targetIp)) {
                $lastMsgWithIp = $messages->whereNotNull('ip_address')->last();
                $targetIp = $lastMsgWithIp ? $lastMsgWithIp->ip_address : null;
            }
        }

        if (empty($targetIp)) {
            $targetIp = $this->getClientIp($request);
        }

        // Unread count across all orders
        $totalUnread = ChatMessage::where('sender', 'customer')->where('is_read', false)->count();

        return view('pos.kitchen_chat', compact(
            'activeOrder',
            'recentOrders',
            'onlineOrders',
            'offlineOrders',
            'messages',
            'targetIp',
            'totalUnread',
            'orderNo'
        ));
    }

    /**
     * Send message from admin / kitchen.
     */
    public function sendAdminChatMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:500',
            'order_no' => 'nullable|string|max:100',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:50',
            'ip_address' => 'nullable|string|max:50',
        ]);

        if (empty($validated['message']) && empty($validated['image_url'])) {
            return response()->json(['success' => false, 'message' => 'กรุณากรอกข้อความหรือแนบรูปภาพ'], 422);
        }

        $order = null;
        if (!empty($validated['order_no'])) {
            $order = Order::where('order_no', $validated['order_no'])->first();
        }

        $ipAddress = $validated['ip_address'] ?? ($order ? $order->ip_address : $this->getClientIp($request));

        $chatMsg = ChatMessage::create([
            'session_id' => session()->getId(),
            'order_id' => $order ? $order->id : null,
            'order_no' => $validated['order_no'] ?? ($order ? $order->order_no : null),
            'customer_name' => $validated['customer_name'] ?? ($order ? $order->customer_name : 'ลูกค้า'),
            'customer_phone' => $validated['customer_phone'] ?? ($order ? $order->customer_phone : null),
            'ip_address' => $ipAddress,
            'sender' => 'shop',
            'message' => $validated['message'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => $chatMsg,
        ]);
    }

    /**
     * Fetch messages for Admin Kitchen chat via polling.
     */
    public function getAdminChatMessages(Request $request): JsonResponse
    {
        $orderNo = $request->query('order_no');
        $phone = $request->query('phone');

        $query = ChatMessage::query();
        if ($orderNo) {
            $query->where(function ($q) use ($orderNo, $phone) {
                $q->where('order_no', $orderNo);
                if ($phone) {
                    $q->orWhere('customer_phone', $phone);
                }
            });
        } elseif ($phone) {
            $query->where('customer_phone', $phone);
        }

        $messages = $query->orderBy('created_at', 'asc')->get();

        // Mark as read
        if ($orderNo) {
            ChatMessage::where('order_no', $orderNo)
                ->where('sender', 'customer')
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $totalUnread = ChatMessage::where('sender', 'customer')->where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'messages' => $messages,
            'total_unread' => $totalUnread,
        ]);
    }

    /**
     * Resolve true client device IP address behind Cloudflare / reverse proxy.
     */
    public function getClientIp(Request $request): string
    {
        $ip = $request->header('cf-connecting-ip')
            ?: $request->header('x-real-ip')
            ?: $request->header('x-forwarded-for')
            ?: $request->ip();

        if (str_contains($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
        }

        return $ip ?: '127.0.0.1';
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
