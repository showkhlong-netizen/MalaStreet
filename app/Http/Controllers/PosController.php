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
use App\Models\Setting;
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
        $livePosOrders = $this->buildLivePosOrders();
        $onlineOrderStatus = Setting::get('online_order_status', 'preview');

        $posHeroBannerImage = Setting::get('pos_hero_banner_image', '');
        $posHeroHistoryB64 = json_decode(Setting::get('pos_hero_banner_history_b64', '{}'), true) ?: [];
        $activeHeroB64 = $posHeroHistoryB64[$posHeroBannerImage] ?? Setting::get('pos_hero_banner_base64');
        $posHeroBannerImage = $this->restorePersistedHeroUrl($posHeroBannerImage, $activeHeroB64);

        $rawPosHeroPos = Setting::get('pos_hero_banner_position', '{"x":50,"y":50,"scale":1}');
        $posHeroBannerPosition = json_decode($rawPosHeroPos, true) ?: ['x' => 50, 'y' => 50, 'scale' => 1];
        $rawHistory = json_decode(Setting::get('pos_hero_banner_history', '[]'), true) ?: [];
        $posHeroBannerHistory = array_values(array_map(
            fn ($url) => $this->restorePersistedHeroUrl((string) $url, $posHeroHistoryB64[$url] ?? null),
            $rawHistory
        ));

        $promptPaySettings = self::getStorePromptPaySettings();
        $webhookConfig = PaymentWebhookController::getWebhookConfig();

        return view('pos.index', compact(
            'categories',
            'todayOrders',
            'totalSalesToday',
            'totalOrdersToday',
            'nextQueueNumber',
            'priceHistoriesCount',
            'livePosOrders',
            'onlineOrderStatus',
            'posHeroBannerImage',
            'posHeroBannerPosition',
            'posHeroBannerHistory',
            'promptPaySettings',
            'webhookConfig'
        ));
    }

    /**
     * Toggle online ordering mode between 'preview' and 'live' from POS Cashier.
     */
    public function toggleOnlineMode(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $targetMode = $request->input('mode') === 'live' ? 'live' : 'preview';
        Setting::set('online_order_status', $targetMode);

        $msg = $targetMode === 'live'
            ? 'เปิดบริการสั่งออนไลน์รับออเดอร์จริงเรียบร้อยแล้ว!'
            : 'ปิดบริการออนไลน์ชั่วคราว (โหมดพรีวิว) เรียบร้อยแล้ว';

        AuditLog::record(
            'pos',
            'update',
            $msg,
            "Online Order Mode: {$targetMode}",
            'setting',
            'online_order_status'
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'mode' => $targetMode,
                'message' => $msg,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Restore an ephemeral /uploads/hero file from its DB base64 backup after redeploy/restart.
     */
    protected function restorePersistedHeroUrl(?string $url, ?string $base64Backup): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        if (str_contains($url, '/uploads/hero/')) {
            $parsedPath = parse_url($url, PHP_URL_PATH);
            $fullPath = $parsedPath ? public_path(ltrim($parsedPath, '/')) : null;

            if ($fullPath && !file_exists($fullPath) && $base64Backup && str_starts_with($base64Backup, 'data:image/')) {
                $commaPos = strpos($base64Backup, ',');
                $decoded = $commaPos !== false ? base64_decode(substr($base64Backup, $commaPos + 1)) : false;
                if ($decoded !== false && strlen($decoded) > 50) {
                    $dir = dirname($fullPath);
                    if (!file_exists($dir)) {
                        @mkdir($dir, 0755, true);
                    }
                    @file_put_contents($fullPath, $decoded);
                }

                if (!file_exists($fullPath)) {
                    return $base64Backup;
                }
            }
        }

        return $url;
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
            'payment_method' => 'nullable|in:cash,promptpay',
            'cash_received' => 'nullable|numeric|min:0',
            'cash_amount' => 'nullable|numeric|min:0',
            'transfer_amount' => 'nullable|numeric|min:0',
            'is_draft' => 'nullable|boolean',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'coupon_code' => 'nullable|string|max:30',
            'fulfillment_mode' => 'nullable|in:pos,delivery,pickup',
            'notes' => 'nullable|string|max:255',
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
            $today = Carbon::today('Asia/Bangkok');
            $dayStart = $today->copy()->startOfDay();
            $dayEnd = $today->copy()->endOfDay();
            $datePrefix = 'MS-POS-' . $today->format('Ymd') . '-';

            $existingPosToday = Order::where('order_type', 'pos')
                ->where(function ($q) use ($dayStart, $dayEnd, $datePrefix) {
                    $q->whereBetween('created_at', [$dayStart, $dayEnd])
                        ->orWhere('order_no', 'like', $datePrefix . '%');
                })
                ->lockForUpdate()
                ->get(['id', 'order_no']);

            $maxSeq = $existingPosToday->count();
            foreach ($existingPosToday as $existing) {
                if (preg_match('/^' . preg_quote($datePrefix, '/') . '(\d+)/', (string) $existing->order_no, $m)) {
                    $maxSeq = max($maxSeq, (int) $m[1]);
                }
            }
            $nextSeq = $maxSeq + 1;
            $queueNumber = 'A' . str_pad($nextSeq, 2, '0', STR_PAD_LEFT);
            $uniqueSuffix = strtoupper(\Illuminate\Support\Str::random(3));
            $orderNo = $datePrefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT) . '-' . $uniqueSuffix;

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
            $isDraft = (bool) ($validated['is_draft'] ?? false);
            $paymentMethod = $validated['payment_method'] ?? 'cash';

            $cashAmountInput = isset($validated['cash_amount']) ? (float) $validated['cash_amount'] : null;
            $transferAmountInput = isset($validated['transfer_amount']) ? (float) $validated['transfer_amount'] : null;

            $cashReceived = $validated['cash_received'] ?? null;
            $changeAmount = null;

            if ($cashAmountInput !== null || $transferAmountInput !== null) {
                $cashVal = max(0, (float) ($cashAmountInput ?? 0));
                $transferVal = max(0, (float) ($transferAmountInput ?? 0));
                if ($transferVal > 0 && $cashVal <= 0) {
                    $paymentMethod = 'promptpay';
                } elseif ($cashVal > 0 && $transferVal <= 0) {
                    $paymentMethod = 'cash';
                } elseif ($transferVal > 0 && $cashVal > 0) {
                    $paymentMethod = 'promptpay';
                }
                $cashReceived = $cashVal > 0 ? $cashVal : null;
                $changeAmount = max(0, ($cashVal + $transferVal) - $totalAmount);
            } elseif ($paymentMethod === 'cash' && $cashReceived !== null) {
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
                'payment_method' => $paymentMethod,
                'payment_status' => $isDraft ? 'unpaid' : 'paid',
                'subtotal' => $subtotal,
                'soup_extra' => $totalExtra,
                'discount' => $discountAmount,
                'total_amount' => $totalAmount,
                'cash_received' => $cashReceived,
                'change_amount' => $changeAmount,
                'notes' => $validated['notes'] ?? null,
                'paid_at' => $isDraft ? null : Carbon::now(),
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            $order->load('items.product');

            return $order;
        });

        AuditLog::record(
            'pos',
            'create',
            $order->payment_status === 'paid' ? 'เปิดบิลขายหน้าร้าน (POS)' : 'บันทึกร่างออเดอร์หน้าร้าน (POS)',
            "คิว {$order->queue_number} ({$order->order_no}) ยอด ฿" . number_format((float) $order->total_amount, 2),
            [
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'queue_number' => $order->queue_number,
                'total_amount' => $order->total_amount,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'บันทึกออเดอร์หน้าร้านเรียบร้อยแล้ว',
            'order' => $order,
        ]);
    }

    /**
     * Kitchen Display System (KDS / จอครัว).
     */
    public function kitchen(): View
    {
        $today = Carbon::today();
        $activeOrders = Order::with('items')
            ->whereIn('status', ['pending', 'confirmed', 'cooking', 'ready'])
            ->orderBy('created_at')
            ->get();

        $completedCount = Order::whereDate('created_at', $today)->where('status', 'completed')->count();
        $unreadChatCount = ChatMessage::where('sender', 'customer')->where('is_read', false)->count();
        $activeProducts = Product::where('is_available', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'price_per_stick', 'image_url', 'supports_grill', 'supports_soup']);

        return view('pos.kitchen', compact('activeOrders', 'completedCount', 'unreadChatCount', 'activeProducts'));
    }

    /**
     * Return live storefront & online orders for POS real-time sync.
     */
    public function liveOrders(Request $request): JsonResponse
    {
        return response()->json([
            'orders' => $this->buildLivePosOrders(),
            'server_time' => Carbon::now('Asia/Bangkok')->format('H:i:s'),
        ]);
    }

    /**
     * Build active and today's orders payload for the POS storefront queue panel.
     */
    private function buildLivePosOrders(): array
    {
        $today = Carbon::today('Asia/Bangkok');

        $orders = Order::with('items')
            ->where(function ($query) use ($today) {
                $query->whereIn('status', ['pending', 'confirmed', 'cooking', 'ready'])
                    ->orWhereDate('created_at', $today);
            })
            ->orderByRaw("CASE WHEN status IN ('pending','confirmed','cooking','ready') THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->limit(40)
            ->get();

        $unreadByOrder = collect();
        if ($orders->isNotEmpty()) {
            $unreadByOrder = ChatMessage::whereIn('order_no', $orders->pluck('order_no'))
                ->where('sender', 'customer')
                ->where('is_read', false)
                ->selectRaw('order_no, COUNT(*) as cnt')
                ->groupBy('order_no')
                ->pluck('cnt', 'order_no');
        }

        return $orders->map(function (Order $order) use ($unreadByOrder) {
            $itemsSummary = $order->items->map(function (OrderItem $item) {
                $qty = (int) ($item->quantity_sticks ?? 0);
                return trim($item->product_name . ' x' . $qty);
            })->implode(', ');

            return [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'queue_number' => $order->queue_number ?? '-',
                'order_type' => $order->order_type ?? 'pos',
                'customer_name' => $order->customer_name ?: ($order->order_type === 'online' ? 'ลูกค้าออนไลน์' : 'ลูกค้าหน้าร้าน'),
                'customer_phone' => $order->customer_phone ?? '',
                'pickup_time' => $order->pickup_time ?? '',
                'status' => $order->status,
                'total_amount' => (float) $order->total_amount,
                'items_count' => $order->items->count(),
                'items_summary' => $itemsSummary ?: '-',
                'unread_chat' => (int) ($unreadByOrder[$order->order_no] ?? 0),
                'created_at_hm' => $order->created_at ? $order->created_at->timezone('Asia/Bangkok')->format('H:i') : '',
            ];
        })->values()->all();
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
        $aiAutoReplyEnabled = filter_var(Setting::get('chat_ai_auto_reply_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
        $hasGeminiKey = !empty(env('GEMINI_API_KEY')) || !empty(config('services.gemini.key')) || !empty(Setting::get('gemini_api_key'));

        return view('pos.kitchen_chat', compact(
            'activeOrder',
            'recentOrders',
            'onlineOrders',
            'offlineOrders',
            'messages',
            'targetIp',
            'totalUnread',
            'orderNo',
            'aiAutoReplyEnabled',
            'hasGeminiKey'
        ));
    }

    /**
     * Toggle AI Auto-Reply for customer chat ON / OFF.
     */
    public function toggleChatAiAutoReply(Request $request): JsonResponse
    {
        $current = filter_var(Setting::get('chat_ai_auto_reply_enabled', '1'), FILTER_VALIDATE_BOOLEAN);
        $enabled = $request->has('enabled') ? $request->boolean('enabled') : !$current;

        Setting::set('chat_ai_auto_reply_enabled', $enabled ? '1' : '0', 'chat');

        AuditLog::record(
            'chat',
            'update',
            $enabled ? 'เปิดโหมดตอบแชท AI อัตโนมัติ' : 'ปิดโหมดตอบแชท AI อัตโนมัติ (แอดมินตอบเอง)',
            'chat_ai_auto_reply_enabled',
            'setting',
            null,
            ['enabled' => $enabled]
        );

        return response()->json([
            'success' => true,
            'enabled' => $enabled,
            'message' => $enabled
                ? 'เปิดใช้งานตอบแชท AI อัตโนมัติแล้ว'
                : 'ปิดตอบแชท AI อัตโนมัติแล้ว (แอดมินตอบแชทเอง)',
        ]);
    }

    /**
     * Save Gemini API Key from Kitchen Chat panel into persistent Settings.
     */
    public function saveChatGeminiKey(Request $request): JsonResponse
    {
        $key = trim((string) $request->input('gemini_api_key', ''));
        if ($key === '') {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาระบุ Gemini API Key',
            ], 422);
        }

        Setting::set('gemini_api_key', $key, 'ai');

        AuditLog::record(
            'chat',
            'update',
            'บันทึก Gemini API Key สำหรับระบบตอบแชท AI',
            'gemini_api_key',
            'setting'
        );

        return response()->json([
            'success' => true,
            'has_key' => true,
            'message' => 'บันทึก Gemini API Key เรียบร้อยแล้ว! ระบบแชท Gemini AI พร้อมใช้งานทันที',
        ]);
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
            'mark_paid' => 'nullable|boolean',
        ]);

        $oldStatus = $order->status;
        $order->status = $validated['status'];
        if (!empty($validated['mark_paid']) && $order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
            $order->paid_at = Carbon::now();
        }
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

    /**
     * Advance or revert a Delivery order across Kitchen 3-step workflow.
     */
    public function updateDeliveryKitchenStep(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:accept,start_cooking,mark_ready,back_to_step1',
        ]);

        $oldStatus = $order->status;
        $action = $validated['action'];

        if ($action === 'accept') {
            $order->kitchen_step = 2;
            $order->cooking_started_at = null;
            if (in_array($order->status, ['pending', 'ready'], true)) {
                $order->status = 'confirmed';
            }
        } elseif ($action === 'start_cooking') {
            $order->kitchen_step = 2;
            $order->cooking_started_at = $order->cooking_started_at ?: Carbon::now();
            $order->status = 'cooking';
        } elseif ($action === 'mark_ready') {
            $order->kitchen_step = 3;
            $order->status = 'ready';
        } elseif ($action === 'back_to_step1') {
            $order->kitchen_step = 1;
            $order->cooking_started_at = null;
            if (in_array($order->status, ['confirmed', 'cooking', 'ready'], true)) {
                $order->status = 'confirmed';
            }
        }

        $order->save();

        AuditLog::record(
            'kitchen',
            'status',
            "อัปเดตขั้นตอน Delivery ({$action})",
            "คิว {$order->queue_number} ({$order->order_no})",
            [
                'order_id' => $order->id,
                'action' => $action,
                'old_status' => $oldStatus,
                'new_status' => $order->status,
                'kitchen_step' => $order->kitchen_step,
            ]
        );

        return response()->json([
            'success' => true,
            'kitchen_step' => $order->kitchen_step,
            'status' => $order->status,
            'cooking_started_at' => $order->cooking_started_at?->toIso8601String(),
        ]);
    }

    /**
     * Update order items and totals from Kitchen Step 1 Edit modal.
     */
    public function updateOrderItems(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer|exists:products,id',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.quantity_sticks' => 'required|integer|min:1|max:999',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.cooking_type' => 'required|in:grill,soup',
            'items.*.grill_style' => 'nullable|string|max:50',
            'items.*.soup_style' => 'nullable|string|max:50',
            'items.*.spiciness_level' => 'required|in:none,mild,medium,hot',
            'items.*.special_instructions' => 'nullable|string|max:500',
            'pickup_time' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($order, $validated) {
            $oldQtyByProduct = $order->items()
                ->whereNotNull('product_id')
                ->selectRaw('product_id, SUM(quantity_sticks) as qty')
                ->groupBy('product_id')
                ->pluck('qty', 'product_id')
                ->all();

            $newQtyByProduct = [];
            $subtotal = 0;

            $order->items()->delete();
            foreach ($validated['items'] as $row) {
                $qty = (int) $row['quantity_sticks'];
                $unitPrice = (float) $row['unit_price'];
                $lineSubtotal = round($qty * $unitPrice, 2);
                $subtotal += $lineSubtotal;

                if (!empty($row['product_id'])) {
                    $pid = (int) $row['product_id'];
                    $newQtyByProduct[$pid] = ($newQtyByProduct[$pid] ?? 0) + $qty;
                }

                $order->items()->create([
                    'product_id' => $row['product_id'] ?? null,
                    'product_name' => trim($row['product_name']),
                    'quantity_sticks' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineSubtotal,
                    'cooking_type' => $row['cooking_type'],
                    'grill_style' => $row['cooking_type'] === 'grill' ? ($row['grill_style'] ?? 'brush') : null,
                    'soup_style' => $row['cooking_type'] === 'soup' ? ($row['soup_style'] ?? 'mala_rich') : null,
                    'spiciness_level' => $row['spiciness_level'],
                    'special_instructions' => $row['special_instructions'] ?? null,
                ]);
            }

            // Reconcile product stock_sticks delta
            $allProductIds = array_unique(array_merge(array_keys($oldQtyByProduct), array_keys($newQtyByProduct)));
            foreach ($allProductIds as $pid) {
                $delta = ($newQtyByProduct[$pid] ?? 0) - ($oldQtyByProduct[$pid] ?? 0);
                if ($delta !== 0) {
                    $product = Product::find($pid);
                    if ($product) {
                        $product->stock_sticks = max(0, (int) $product->stock_sticks - $delta);
                        $product->save();
                    }
                }
            }

            if (array_key_exists('pickup_time', $validated) && $validated['pickup_time'] !== null) {
                $order->pickup_time = trim($validated['pickup_time']);
            }
            if (array_key_exists('notes', $validated)) {
                $order->notes = $validated['notes'] !== null ? trim($validated['notes']) : null;
            }
            $order->subtotal = round($subtotal, 2);
            $order->total_amount = round(max(0, $subtotal + (float) $order->soup_extra - (float) $order->discount), 2);
            $order->save();
        });

        $order->load('items');

        AuditLog::record(
            'kitchen',
            'update',
            'แก้ไขออเดอร์ Delivery ในครัว',
            "คิว {$order->queue_number} ({$order->order_no}) ยอดใหม่ ฿" . number_format((float) $order->total_amount, 2),
            ['order_id' => $order->id, 'total_amount' => (float) $order->total_amount]
        );

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    /**
     * Cancel/delete an order from Kitchen Step 1 using admin PIN (1122).
     */
    public function cancelOrderWithPin(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'pin' => 'required|string|max:20',
        ]);

        if (trim($validated['pin']) !== '1122') {
            return response()->json([
                'success' => false,
                'message' => 'รหัส PIN ไม่ถูกต้อง (กรุณากรอก 1122)',
            ], 422);
        }

        if ($order->status !== 'cancelled') {
            DB::transaction(function () use ($order) {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->increment('stock_sticks', (int) $item->quantity_sticks);
                    }
                }
                $order->status = 'cancelled';
                $order->save();
            });

            AuditLog::record(
                'kitchen',
                'delete',
                'ยกเลิก/ลบออเดอร์ด้วยรหัส PIN',
                "คิว {$order->queue_number} ({$order->order_no})",
                ['order_id' => $order->id]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'ยกเลิกออเดอร์เรียบร้อยแล้ว',
        ]);
    }

    /**
     * Complete a Delivery order with cash/transfer split payment summary and note.
     */
    public function completeDeliveryOrder(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'cash_amount' => 'required|numeric|min:0',
            'transfer_amount' => 'required|numeric|min:0',
            'delivery_note' => 'nullable|string|max:1000',
        ]);

        $cashAmount = round((float) $validated['cash_amount'], 2);
        $transferAmount = round((float) $validated['transfer_amount'], 2);

        $order->paid_cash_amount = $cashAmount;
        $order->paid_transfer_amount = $transferAmount;
        $order->delivery_note = isset($validated['delivery_note']) && trim((string) $validated['delivery_note']) !== ''
            ? trim((string) $validated['delivery_note'])
            : null;
        if ($transferAmount > 0 && $cashAmount <= 0) {
            $order->payment_method = 'promptpay';
        } elseif ($cashAmount > 0 && $transferAmount <= 0) {
            $order->payment_method = 'cash';
        }
        $order->payment_status = 'paid';
        $order->paid_at = $order->paid_at ?: Carbon::now();
        $order->status = 'completed';
        $order->completed_at = Carbon::now();
        $order->save();

        AuditLog::record(
            'kitchen',
            'status',
            'ปิดออเดอร์ Delivery สำเร็จ',
            "คิว {$order->queue_number} (สด ฿" . number_format($cashAmount, 2) . " / โอน ฿" . number_format($transferAmount, 2) . ")",
            [
                'order_id' => $order->id,
                'cash_amount' => $cashAmount,
                'transfer_amount' => $transferAmount,
                'delivery_note' => $order->delivery_note,
            ]
        );

        return response()->json([
            'success' => true,
            'status' => $order->status,
            'paid_cash_amount' => $cashAmount,
            'paid_transfer_amount' => $transferAmount,
        ]);
    }

    public static function getStorePromptPaySettings(): array
    {
        $defaultPayload = '0002010102110216478772000407574704155303920004076001531343007640052044640122194421100130810016A00000067701011201150107536000315010214KB0000020757060320KPS004KB00000207570631690016A00000067701011301030040214KB0000020757060420KPS004KB00000207570651430014A000000004101001064169710211123456789015204731153037645802TH5913CHAWKLONG.PRO6004CITY622505094548452260708421944216304D288';

        $payload = Setting::get('store_promptpay_qr_payload', $defaultPayload) ?: $defaultPayload;
        $storeName = Setting::get('store_name', 'ร้านหม่าล่าสตรีท (Mala Street)') ?: 'ร้านหม่าล่าสตรีท (Mala Street)';
        $storePhone = Setting::get('store_phone', '089-123-4567') ?: '089-123-4567';
        $receiptNote = Setting::get('store_receipt_note', 'สแกนได้ทุกแอปธนาคารไทย ยอดเงินจะขึ้นตรงอัตโนมัติ') ?: 'สแกนได้ทุกแอปธนาคารไทย ยอดเงินจะขึ้นตรงอัตโนมัติ';
        $accountName = Setting::get('store_promptpay_account_name', 'มูนีบ สและแม') ?: 'มูนีบ สและแม';
        $accountNo = Setting::get('store_promptpay_account_no', '098-1-53225-2 (กสิกรไทย)') ?: '098-1-53225-2 (กสิกรไทย)';
        $activeImage = Setting::get('store_promptpay_active_image', '');
        $activeQrId = Setting::get('store_promptpay_active_qr_id', 'qr_default');

        $history = json_decode(Setting::get('store_promptpay_qr_history', '[]'), true);
        if (!is_array($history) || empty($history)) {
            $history = [
                [
                    'id' => 'qr_default',
                    'label' => 'QR หลักของร้าน',
                    'store_name' => $storeName,
                    'account_name' => $accountName,
                    'account_no' => $accountNo,
                    'payload' => $payload,
                    'image_data' => $activeImage,
                    'created_at' => Carbon::now('Asia/Bangkok')->format('d/m/Y H:i'),
                ],
            ];
            Setting::set('store_promptpay_qr_history', json_encode($history, JSON_UNESCAPED_UNICODE), 'payment');
        }

        return [
            'payload' => $payload,
            'store_name' => $storeName,
            'store_phone' => $storePhone,
            'receipt_note' => $receiptNote,
            'account_name' => $accountName,
            'account_no' => $accountNo,
            'active_image' => $activeImage,
            'active_qr_id' => $activeQrId,
            'history' => array_values($history),
        ];
    }

    /**
     * Save or replace the store's PromptPay EMVCo QR payload, uploaded QR image archive & store/cashier info.
     */
    public function savePromptPayQr(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payload' => 'required|string|min:10|max:1000',
            'store_name' => 'nullable|string|max:150',
            'store_phone' => 'nullable|string|max:50',
            'receipt_note' => 'nullable|string|max:250',
            'account_name' => 'nullable|string|max:120',
            'account_no' => 'nullable|string|max:120',
            'label' => 'nullable|string|max:120',
            'image_data' => 'nullable|string|max:900000',
        ]);

        $payload = trim($validated['payload']);
        $storeName = trim((string) ($validated['store_name'] ?? ''));
        $storePhone = trim((string) ($validated['store_phone'] ?? ''));
        $receiptNote = trim((string) ($validated['receipt_note'] ?? ''));
        $accountName = trim((string) ($validated['account_name'] ?? ''));
        $accountNo = trim((string) ($validated['account_no'] ?? ''));
        $label = trim((string) ($validated['label'] ?? ''));
        $imageData = trim((string) ($validated['image_data'] ?? ''));

        $current = self::getStorePromptPaySettings();
        $finalStoreName = $storeName !== '' ? $storeName : $current['store_name'];
        $finalStorePhone = $storePhone !== '' ? $storePhone : $current['store_phone'];
        $finalReceiptNote = $receiptNote !== '' ? $receiptNote : $current['receipt_note'];
        $finalAccountName = $accountName !== '' ? $accountName : $current['account_name'];
        $finalAccountNo = $accountNo !== '' ? $accountNo : $current['account_no'];

        Setting::set('store_promptpay_qr_payload', $payload, 'payment');
        Setting::set('store_name', $finalStoreName, 'payment');
        Setting::set('store_phone', $finalStorePhone, 'payment');
        Setting::set('store_receipt_note', $finalReceiptNote, 'payment');
        Setting::set('store_promptpay_account_name', $finalAccountName, 'payment');
        Setting::set('store_promptpay_account_no', $finalAccountNo, 'payment');

        $history = $current['history'];
        $existingIndex = null;
        foreach ($history as $idx => $item) {
            if (($item['payload'] ?? '') === $payload) {
                $existingIndex = $idx;
                break;
            }
        }

        $finalImage = $imageData !== ''
            ? $imageData
            : ($existingIndex !== null ? ($history[$existingIndex]['image_data'] ?? '') : $current['active_image']);

        $presetId = $existingIndex !== null
            ? ($history[$existingIndex]['id'] ?? ('qr_' . uniqid()))
            : ('qr_' . uniqid());

        $entry = [
            'id' => $presetId,
            'label' => $label !== '' ? $label : ($finalAccountNo ?: 'QR พร้อมเพย์'),
            'store_name' => $finalStoreName,
            'account_name' => $finalAccountName,
            'account_no' => $finalAccountNo,
            'payload' => $payload,
            'image_data' => $finalImage,
            'created_at' => Carbon::now('Asia/Bangkok')->format('d/m/Y H:i'),
        ];

        if ($existingIndex !== null) {
            unset($history[$existingIndex]);
        }
        array_unshift($history, $entry);
        $history = array_values(array_slice($history, 0, 30));

        Setting::set('store_promptpay_qr_history', json_encode($history, JSON_UNESCAPED_UNICODE), 'payment');
        Setting::set('store_promptpay_active_image', $finalImage, 'payment');
        Setting::set('store_promptpay_active_qr_id', $presetId, 'payment');

        AuditLog::record(
            'pos',
            'settings',
            'อัปเดตจัดการแคชเชียร์ & QR พร้อมเพย์',
            "{$finalStoreName} • {$finalAccountName} ({$finalAccountNo})",
            ['payload_length' => strlen($payload), 'preset_id' => $presetId]
        );

        return response()->json([
            'success' => true,
            'message' => 'บันทึกการตั้งค่าแคชเชียร์ & QR พร้อมเพย์เรียบร้อยแล้ว (เก็บภาพเข้าคลังและมีผลทั้ง POS และออนไลน์)',
            'settings' => self::getStorePromptPaySettings(),
        ]);
    }

    /**
     * Switch active PromptPay QR to a previously saved preset in history.
     */
    public function activatePromptPayQrPreset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|string|max:100',
        ]);

        $current = self::getStorePromptPaySettings();
        $found = collect($current['history'])->firstWhere('id', $validated['id']);

        if (!$found) {
            return response()->json(['success' => false, 'message' => 'ไม่พบรายการ QR ที่เลือก'], 404);
        }

        Setting::set('store_promptpay_qr_payload', $found['payload'], 'payment');
        if (!empty($found['store_name'])) {
            Setting::set('store_name', $found['store_name'], 'payment');
        }
        if (!empty($found['account_name'])) {
            Setting::set('store_promptpay_account_name', $found['account_name'], 'payment');
        }
        if (!empty($found['account_no'])) {
            Setting::set('store_promptpay_account_no', $found['account_no'], 'payment');
        }
        Setting::set('store_promptpay_active_image', (string) ($found['image_data'] ?? ''), 'payment');
        Setting::set('store_promptpay_active_qr_id', $found['id'], 'payment');

        AuditLog::record(
            'pos',
            'settings',
            'สลับใช้งาน QR พร้อมเพย์จากคลัง',
            ($found['label'] ?? 'QR') . ' • ' . ($found['account_no'] ?? ''),
            ['preset_id' => $found['id']]
        );

        return response()->json([
            'success' => true,
            'message' => 'สลับมาใช้งาน QR "' . ($found['label'] ?? $found['account_no']) . '" เรียบร้อยแล้ว',
            'settings' => self::getStorePromptPaySettings(),
        ]);
    }

    /**
     * Remove a saved QR preset from history.
     */
    public function deletePromptPayQrPreset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|string|max:100',
        ]);

        $current = self::getStorePromptPaySettings();
        $filtered = array_values(array_filter(
            $current['history'],
            fn ($item) => ($item['id'] ?? '') !== $validated['id']
        ));

        Setting::set('store_promptpay_qr_history', json_encode($filtered, JSON_UNESCAPED_UNICODE), 'payment');

        return response()->json([
            'success' => true,
            'message' => 'ลบรายการออกจากคลัง QR แล้ว',
            'settings' => self::getStorePromptPaySettings(),
        ]);
    }
}
