<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ChatMessage;
use App\Models\CustomerReward;
use App\Models\CustomerReview;
use App\Models\InventoryDaily;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RewardRedemption;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OnlineOrderController extends Controller
{
    /**
     * Grab Food 100% Style Online Storefront for Takeaway Pickup.
     */
    public function index(Request $request): View|JsonResponse
    {
        $recommendedMaterialIds = array_values(array_unique(array_map(
            'intval',
            json_decode(Setting::get('recommended_material_ids', '[]'), true) ?: []
        )));

        $recommendedProducts = collect();
        if (!empty($recommendedMaterialIds)) {
            $orderMap = array_flip($recommendedMaterialIds);
            $recommendedProducts = Product::with(['rawMaterial', 'category'])
                ->whereIn('raw_material_id', $recommendedMaterialIds)
                ->where('is_available', true)
                ->get()
                ->unique('raw_material_id')
                ->sortBy(fn ($p) => $orderMap[(int) $p->raw_material_id] ?? 9999)
                ->values();
        }

        $defaultHeroImage = asset('assets/online/hero-banner-ref.png');
        $heroBannerImage = Setting::get('online_hero_banner_image') ?: $defaultHeroImage;
        if (str_contains($heroBannerImage, '/uploads/hero/')) {
            $parsedPath = parse_url($heroBannerImage, PHP_URL_PATH);
            if ($parsedPath && !file_exists(public_path(ltrim($parsedPath, '/')))) {
                $savedBase64 = Setting::get('online_hero_banner_base64');
                if ($savedBase64) {
                    $heroBannerImage = $savedBase64;
                }
            }
        }
        $rawHeroPos = Setting::get('online_hero_banner_position', '{"x":50,"y":50,"scale":1}');
        $heroBannerPosition = is_array($rawHeroPos) ? $rawHeroPos : (json_decode($rawHeroPos, true) ?: ['x' => 50, 'y' => 50, 'scale' => 1]);
        $heroBannerHistory = json_decode(Setting::get('online_hero_banner_history', '[]'), true) ?: [];
        if (empty($heroBannerHistory)) {
            $heroBannerHistory = [$defaultHeroImage];
        }

        $defaultLogoImage = asset('assets/brand/logo-card.png');
        $profileLogoImage = Setting::get('online_profile_logo_image') ?: $defaultLogoImage;
        if (str_contains($profileLogoImage, '/uploads/logo/')) {
            $parsedPath = parse_url($profileLogoImage, PHP_URL_PATH);
            if ($parsedPath && !file_exists(public_path(ltrim($parsedPath, '/')))) {
                $savedBase64 = Setting::get('online_profile_logo_base64');
                if ($savedBase64) {
                    $profileLogoImage = $savedBase64;
                }
            }
        }
        $rawLogoPos = Setting::get('online_profile_logo_position', '{"x":50,"y":50,"scale":1}');
        $profileLogoPosition = is_array($rawLogoPos) ? $rawLogoPos : (json_decode($rawLogoPos, true) ?: ['x' => 50, 'y' => 50, 'scale' => 1]);
        $profileLogoHistory = json_decode(Setting::get('online_profile_logo_history', '[]'), true) ?: [];
        if (empty($profileLogoHistory)) {
            $profileLogoHistory = [$defaultLogoImage];
        }

        $showPromoBanner = Setting::get('online_show_promo_banner', '1') !== '0';

        // Lightweight live-sync endpoint: active category names/icons & recommended products (synced with the stock page)
        if ($request->boolean('categories_json')) {
            $emojiIcons = ['🥩', '🍗', '🍢', '🥬', '🍥', '🥤', '🍟'];
            return response()->json([
                'success' => true,
                'categories' => Category::where('is_active', true)->orderBy('sort_order')->get()
                    ->map(fn ($c) => [
                        'slug' => $c->slug,
                        'name' => $c->name,
                        'icon' => (empty($c->icon) || in_array($c->icon, $emojiIcons)) ? null : (str_starts_with($c->icon, 'http') ? $c->icon : asset($c->icon)),
                    ])->values(),
                'recommended_products' => $recommendedProducts->map(fn ($p) => [
                    'id' => $p->id,
                    'raw_material_id' => $p->raw_material_id,
                    'name' => $p->display_name ?: $p->name,
                    'price_per_stick' => (float) $p->price_per_stick,
                    'stock_sticks' => (int) $p->stock_sticks,
                    'image_url' => $p->display_image,
                    'category_slug' => $p->category?->slug ?? 'all',
                    'description' => $p->description,
                    'supports_grill' => (bool) $p->supports_grill,
                    'supports_soup' => (bool) $p->supports_soup,
                ])->values(),
                'hero_banner' => [
                    'image' => $heroBannerImage,
                    'position' => $heroBannerPosition,
                    'history' => $heroBannerHistory,
                ],
                'profile_logo' => [
                    'image' => $profileLogoImage,
                    'position' => $profileLogoPosition,
                    'history' => $profileLogoHistory,
                ],
                'show_promo_banner' => $showPromoBanner,
            ]);
        }

        $categories = Category::where('is_active', true)
            ->with(['products' => function ($query) {
                $query->with('rawMaterial')
                    ->where('is_available', true)
                    ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        $featuredProducts = Product::with('rawMaterial')
            ->where('is_available', true)
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        return view('online.index', compact('categories', 'featuredProducts', 'recommendedProducts', 'heroBannerImage', 'heroBannerPosition', 'heroBannerHistory', 'profileLogoImage', 'profileLogoPosition', 'profileLogoHistory', 'showPromoBanner'));
    }

    /**
     * Checkout Screen with PromptPay Payment & Canvas Compression Slip Upload.
     */
    public function checkout(): View
    {
        return view('online.checkout');
    }

    /**
     * Store new online order with promptpay slip.
     */
    public function storeOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'pickup_time' => 'required|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cooking_type' => 'required|in:grill,soup',
            'items.*.grill_style' => 'nullable|in:brush,dip',
            'items.*.soup_style' => 'nullable|in:mala_rich,tomyum_rich,black_soup',
            'items.*.spiciness_level' => 'required|in:none,mild,medium,hot',
            'items.*.special_instructions' => 'nullable|string|max:255',
            'soup_extra' => 'nullable|numeric|min:0',
            'coupon_code' => 'nullable|string|max:30',
            'payment_method' => 'nullable|in:cash,promptpay',
            'slip_base64' => 'nullable|string', // Support HTML5 canvas compressed base64
            'notes' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $today = Carbon::today();
            $onlineCountToday = Order::whereDate('created_at', $today)->where('order_type', 'online')->count();
            $queueNumber = 'G' . str_pad($onlineCountToday + 1, 2, '0', STR_PAD_LEFT);
            $uniqueSuffix = strtoupper(Str::random(3));
            $orderNo = 'MS-ONL-' . $today->format('Ymd') . '-' . str_pad($onlineCountToday + 1, 3, '0', STR_PAD_LEFT) . '-' . $uniqueSuffix;

            $subtotal = 0;
            $soupExtraTotal = (float) ($validated['soup_extra'] ?? 0);
            $orderItemsData = [];

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                $lineSubtotal = $product->price_per_stick * $qty;
                $subtotal += $lineSubtotal;

                // Decrement stock
                $product->decrement('stock_sticks', $qty);

                // Update inventory record
                $inventory = InventoryDaily::firstOrCreate(
                    [
                        'record_date' => $today,
                        'product_id' => $product->id,
                    ],
                    [
                        'opening_sticks' => $product->stock_sticks + $qty,
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

            $discountAmount = 0.00;
            $couponCode = strtoupper(trim($validated['coupon_code'] ?? ''));
            if ($couponCode === 'DISC10' && $subtotal >= 150) {
                $discountAmount = 10.00;
            } elseif ($couponCode === 'DISC20' && $subtotal >= 300) {
                $discountAmount = 20.00;
            } elseif ($couponCode === 'FREESHIP' && $subtotal >= 400) {
                $discountAmount = min(30.00, $soupExtraTotal > 0 ? $soupExtraTotal : 30.00);
            }

            $totalAmount = max(0, $subtotal + $soupExtraTotal - $discountAmount);
            $paymentMethod = $validated['payment_method'] ?? 'promptpay';
            $slipPath = null;

            // Save compressed slip if provided
            if (!empty($validated['slip_base64'])) {
                $base64Image = $validated['slip_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
                    $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                    $type = strtolower($type[1]);
                    $imageDecoded = base64_decode($base64Image);
                    $fileName = 'slip_' . time() . '_' . Str::random(8) . '.jpg';
                    $targetPath = public_path('uploads/slips/' . $fileName);
                    file_put_contents($targetPath, $imageDecoded);
                    $slipPath = 'uploads/slips/' . $fileName;
                }
            } elseif ($request->hasFile('slip_file')) {
                $file = $request->file('slip_file');
                $fileName = 'slip_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/slips'), $fileName);
                $slipPath = 'uploads/slips/' . $fileName;
            }

            $order = Order::create([
                'order_no' => $orderNo,
                'queue_number' => $queueNumber,
                'order_type' => 'online',
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'],
                'pickup_time' => $validated['pickup_time'],
                'status' => 'confirmed',
                'payment_method' => $paymentMethod,
                'payment_status' => 'paid',
                'subtotal' => $subtotal,
                'soup_extra' => $soupExtraTotal,
                'discount' => $discountAmount,
                'total_amount' => $totalAmount,
                'slip_image' => $slipPath,
                'notes' => $validated['notes'] ?? null,
                'paid_at' => Carbon::now(),
            ]);

            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // Record customer loyalty point (+1 stamp for purchase: ซื้อ 1 ครั้ง = ได้ 1 แต้ม)
            if (!empty($validated['customer_phone'])) {
                $cleanPhone = trim($validated['customer_phone']);
                $reward = CustomerReward::firstOrCreate(
                    ['customer_phone' => $cleanPhone],
                    [
                        'customer_name' => $validated['customer_name'] ?? 'ลูกค้าออนไลน์',
                        'points' => 0,
                        'total_stamps' => 0,
                        'free_skewers_redeemed' => 0,
                    ]
                );
                $reward->increment('points', 1);
                $reward->increment('total_stamps', 1);
                if (!empty($validated['customer_name'])) {
                    $reward->customer_name = $validated['customer_name'];
                    $reward->save();
                }
                session(['customer_phone' => $cleanPhone]);
                session(['last_order_no' => $order->order_no]);
            }

            // Sync with Chat: Seed an order notification in the chat
            ChatMessage::create([
                'session_id' => session()->getId(),
                'order_id' => $order->id,
                'order_no' => $order->order_no,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'sender' => 'shop',
                'message' => 'สวัสดีครับคุณ ' . ($order->customer_name ?: 'ลูกค้า') . ' ร้านหม่าล่าสตรีทได้รับคำสั่งซื้อคิว [' . $order->queue_number . '] (' . $order->order_no . ') เรียบร้อยแล้วครับ! กำลังเร่งย่างเตาถ่านให้ตามคิว หากต้องการสอบถามระดับความเผ็ดหรือขอสิ่งใดเพิ่มเติม พิมพ์บอกได้เลยครับ 🔥🍢',
                'is_read' => false,
            ]);

            \App\Models\AuditLog::record(
                'online',
                'create',
                "รับออเดอร์ออนไลน์คิว {$order->queue_number} ({$order->order_no}) ลูกค้า: {$order->customer_name} ยอด ฿" . number_format($order->total_amount, 2),
                "{$order->queue_number} ({$order->order_no})",
                'order',
                (string) $order->id,
                ['queue' => $order->queue_number, 'customer' => $order->customer_name, 'total' => $order->total_amount]
            );

            return response()->json([
                'success' => true,
                'message' => 'ส่งออเดอร์สำเร็จ ชำระเงินเรียบร้อยแล้ว',
                'order_no' => $order->order_no,
                'redirect_url' => route('online.track', ['order_no' => $order->order_no]),
            ]);
        });
    }

    /**
     * Customer Chat Screen (Shopee-like live chat synced with orders).
     */
    public function chat(Request $request): View
    {
        $orderNo = $request->query('order_no') ?: session('last_order_no');
        $activeOrder = null;
        if ($orderNo) {
            $activeOrder = Order::with('items')->where('order_no', $orderNo)->first();
        }
        if (!$activeOrder) {
            $activeOrder = Order::with('items')->where('order_type', 'online')->latest('id')->first();
        }

        $recentOrders = Order::where('order_type', 'online')
            ->latest('id')
            ->take(8)
            ->get();

        $sessionId = session()->getId();
        $phone = $request->query('phone') ?: ($activeOrder ? $activeOrder->customer_phone : session('customer_phone'));

        $query = ChatMessage::query();
        if ($activeOrder) {
            $query->where(function ($q) use ($activeOrder, $sessionId, $phone) {
                $q->where('order_no', $activeOrder->order_no);
                if ($phone) {
                    $q->orWhere('customer_phone', $phone);
                }
                $q->orWhere('session_id', $sessionId);
            });
        } elseif ($phone) {
            $query->where('customer_phone', $phone);
        } else {
            $query->where('session_id', $sessionId);
        }

        $messages = $query->orderBy('created_at', 'asc')->get();

        if ($messages->isEmpty()) {
            $welcome = ChatMessage::create([
                'session_id' => $sessionId,
                'order_no' => $activeOrder ? $activeOrder->order_no : null,
                'customer_name' => $activeOrder ? $activeOrder->customer_name : 'ลูกค้า',
                'customer_phone' => $phone,
                'sender' => 'shop',
                'message' => 'สวัสดีครับคุณลูกค้า! ร้านหม่าล่าสตรีทยินดีให้บริการครับ 🔥 หากมีข้อสงสัยเกี่ยวกับออเดอร์ สอบถามระดับความเผ็ดชา หรือขออะไรเพิ่มเติมพิมพ์ไว้ในนี้ได้เลยครับ 🍢',
                'is_read' => false,
            ]);
            $messages = collect([$welcome]);
        }

        return view('online.chat', compact('activeOrder', 'recentOrders', 'messages', 'phone'));
    }

    /**
     * Get chat messages JSON for real-time live poll.
     */
    public function getChatMessages(Request $request): JsonResponse
    {
        $orderNo = $request->query('order_no');
        $phone = $request->query('phone');
        $sessionId = session()->getId();

        $query = ChatMessage::query();
        if ($orderNo) {
            $query->where('order_no', $orderNo);
        } elseif ($phone) {
            $query->where('customer_phone', $phone);
        } else {
            $query->where('session_id', $sessionId);
        }

        $messages = $query->orderBy('created_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Send a customer chat message with optional image URL and smart bot reply.
     */
    public function sendChatMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:500',
            'order_no' => 'nullable|string|max:100',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:50',
        ]);

        if (empty($validated['message']) && empty($validated['image_url'])) {
            return response()->json(['success' => false, 'message' => 'กรุณากรอกข้อความหรือแนบรูปภาพ'], 422);
        }

        $sessionId = session()->getId();
        $order = null;
        if (!empty($validated['order_no'])) {
            $order = Order::where('order_no', $validated['order_no'])->first();
        }

        $chatMsg = ChatMessage::create([
            'session_id' => $sessionId,
            'order_id' => $order ? $order->id : null,
            'order_no' => $validated['order_no'] ?? ($order ? $order->order_no : null),
            'customer_name' => $validated['customer_name'] ?? ($order ? $order->customer_name : 'ลูกค้า'),
            'customer_phone' => $validated['customer_phone'] ?? ($order ? $order->customer_phone : null),
            'sender' => 'customer',
            'message' => $validated['message'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'is_read' => true,
        ]);

        // Smart Bot Auto-Reply
        $botReply = null;
        $userText = mb_strtolower($validated['message'] ?? '');

        if (str_contains($userText, 'ถึงไหน') || str_contains($userText, 'สถานะ') || str_contains($userText, 'คิว') || str_contains($userText, 'เสร็จยัง')) {
            $statusText = 'รอยืนยัน';
            $qNum = '-';
            if ($order) {
                $statusMap = [
                    'pending' => 'รอร้านค้ายืนยันคำสั่งซื้อ',
                    'confirmed' => 'ร้านรับออเดอร์แล้ว กำลังรอคิวขึ้นเตา',
                    'cooking' => '🔥 กำลังย่างเตาถ่าน / ต้มซุปในครัว',
                    'ready' => '🎉 ทำเสร็จแล้ว พร้อมให้รับที่หน้าร้านได้เลยครับ!',
                    'completed' => '✅ ส่งมอบอาหารเรียบร้อยแล้ว',
                ];
                $statusText = $statusMap[$order->status] ?? $order->status;
                $qNum = $order->queue_number;
            }
            $replyText = "ขณะนี้ออเดอร์คิว [{$qNum}] อยู่ในสถานะ: \"{$statusText}\" ครับ ทางร้านกำลังเร่งทำให้เต็มที่ครับ 🙏";
        } elseif (str_contains($userText, 'พริก') || str_contains($userText, 'เผ็ด') || str_contains($userText, 'ชา') || str_contains($userText, 'ซอส')) {
            $replyText = "รับทราบครับ! ทางร้านแจ้งเตาปิ้งย่างเพื่อปรับความเผ็ดชาและรสชาติตามที่คุณลูกค้าต้องการเรียบร้อยครับ 🌶️🔥";
        } elseif (str_contains($userText, 'ที่อยู่') || str_contains($userText, 'ร้านอยู่') || str_contains($userText, 'พิกัด') || str_contains($userText, 'แผนที่')) {
            $replyText = "📍 ร้านหม่าล่าสตรีท ตั้งอยู่หน้าตลาดสตรีทฟู้ด ใกล้สี่แยก มีที่จอดรถหน้าร้านสะดวกสบาย เปิดทุกวัน 16:00 - 23:00 น. ครับ";
        } elseif (!empty($validated['image_url'])) {
            $replyText = "ได้รับรูปภาพเรียบร้อยแล้วครับ ขอบคุณที่ส่งภาพให้ทางร้านตรวจสอบครับ 📸✨";
        } elseif (str_contains($userText, 'สวัสดี') || str_contains($userText, 'ดีครับ') || str_contains($userText, 'ดีค่ะ')) {
            $replyText = "สวัสดีครับ! หากต้องการสอบถามรายการอาหาร หรือปรับเปลี่ยนอะไรในออเดอร์ แจ้งไว้ได้เลยนะครับ 🍢";
        } else {
            $replyText = "รับทราบข้อความครับ ร้านหม่าล่าสตรีทได้รับข้อความของคุณแล้ว และยินดีดูแลคุณลูกค้าอย่างเต็มที่ครับ 🙏✨";
        }

        if ($replyText) {
            $botReply = ChatMessage::create([
                'session_id' => $sessionId,
                'order_id' => $order ? $order->id : null,
                'order_no' => $chatMsg->order_no,
                'customer_name' => $chatMsg->customer_name,
                'customer_phone' => $chatMsg->customer_phone,
                'sender' => 'shop',
                'message' => $replyText,
                'is_read' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $chatMsg,
            'bot_reply' => $botReply,
        ]);
    }

    /**
     * Upload and compress chat photo to URL.
     */
    public function uploadChatImage(Request $request): JsonResponse
    {
        $destDir = public_path('assets/chat');
        if (!file_exists($destDir)) {
            mkdir($destDir, 0777, true);
        }

        // 1. Base64 (from HTML5 Canvas client compressor)
        if ($request->filled('image_base64')) {
            $base64 = $request->input('image_base64');
            $ext = 'jpg';
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
                $ext = strtolower($matches[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                $base64 = substr($base64, strpos($base64, ',') + 1);
            }
            $decoded = base64_decode($base64);
            if ($decoded !== false && strlen($decoded) > 50) {
                $filename = 'chat_' . time() . '_' . Str::random(8) . '.' . $ext;
                file_put_contents($destDir . '/' . $filename, $decoded);
                $url = asset('assets/chat/' . $filename);
                return response()->json([
                    'success' => true,
                    'url' => $url,
                    'filename' => $filename,
                ]);
            }
        }

        // 2. Direct File Upload
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            if ($file->isValid()) {
                $ext = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
                $filename = 'chat_' . time() . '_' . Str::random(8) . '.' . $ext;
                $file->move($destDir, $filename);
                $url = asset('assets/chat/' . $filename);
                return response()->json([
                    'success' => true,
                    'url' => $url,
                    'filename' => $filename,
                ]);
            }
        }

        return response()->json(['success' => false, 'message' => 'ไม่พบไฟล์รูปภาพ'], 422);
    }

    /**
     * Customer Rewards Screen (Loyalty points: 1 order = 1 pt, 5 pts = 1 free stick, review = +1 pt).
     */
    public function rewards(Request $request): View
    {
        $phone = $request->query('phone') ?: session('customer_phone', '089-123-4567');
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($cleanPhone)) {
            $phone = '089-123-4567';
        }

        $reward = CustomerReward::firstOrCreate(
            ['customer_phone' => $phone],
            [
                'customer_name' => 'ลูกค้าประจำหม่าล่าสตรีท',
                'points' => 3, // Initial friendly starting stamps for demonstration
                'total_stamps' => 3,
                'free_skewers_redeemed' => 0,
            ]
        );

        $vouchers = RewardRedemption::where('customer_phone', $phone)
            ->latest('id')
            ->get();

        $reviews = CustomerReview::latest('id')->take(20)->get();

        // Seed sample reviews if empty
        if ($reviews->isEmpty()) {
            $sampleReviews = [
                [
                    'customer_name' => 'คุณพิมลภัส (ลูกค้าประจำ)',
                    'customer_phone' => '081-xxx-5678',
                    'order_no' => 'MS-ONL-20261110-001',
                    'rating' => 5,
                    'favorite_dishes' => 'หมูสามชั้น, เอ็นไก่, น้ำซุปหม่าล่าเข้มข้น',
                    'comment' => 'หม่าล่าอร่อยมากกก เผ็ดชาถึงใจ ย่างเตาถ่านหอมกรุ่น สั่งมากินกับเพื่อนทุกอาทิตย์เลยค่ะ ได้แต้มสะสมแลกฟรีอีก คุ้มมาก!',
                    'image_url' => asset('assets/menu/pork-belly.jpg'),
                    'bonus_points' => 1,
                ],
                [
                    'customer_name' => 'คุณธนกฤต',
                    'customer_phone' => '089-xxx-9912',
                    'order_no' => 'MS-ONL-20261110-002',
                    'rating' => 5,
                    'favorite_dishes' => 'เนื้อริบอาย, เห็ดเข็มทองพันเบคอน',
                    'comment' => 'ซอสจุ่มเด็ดจริง เผ็ดชากำลังดี ไม้ใหญ่เครื่องแน่น สั่งออนไลน์มารับหน้าร้านสะดวกมาก ไม่ต้องรอคิวนาน',
                    'image_url' => asset('assets/menu/chicken-tendon.jpg'),
                    'bonus_points' => 1,
                ],
                [
                    'customer_name' => 'คุณนภัสสร',
                    'customer_phone' => '092-xxx-4321',
                    'order_no' => 'MS-ONL-20261109-005',
                    'rating' => 5,
                    'favorite_dishes' => 'เต้าหู้ชีส, ลูกชิ้นเอ็นเนื้อ',
                    'comment' => 'ครบ 5 แต้มแลกหม่าล่าฟรี 1 ไม้ได้จริง ยื่นคูปองให้พนักงานได้เลย บริการดีประทับใจสุดๆ ค่ะ ⭐⭐⭐⭐⭐',
                    'image_url' => asset('assets/menu/cheese-tofu.jpg'),
                    'bonus_points' => 1,
                ],
            ];
            foreach ($sampleReviews as $s) {
                CustomerReview::create($s);
            }
            $reviews = CustomerReview::latest('id')->take(20)->get();
        }

        $recentOrders = Order::where('customer_phone', $phone)->orWhere('customer_phone', $cleanPhone)->latest('id')->take(5)->get();

        return view('online.rewards', compact('reward', 'vouchers', 'reviews', 'recentOrders', 'phone'));
    }

    /**
     * AJAX Lookup Customer Rewards by Phone.
     */
    public function lookupRewards(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:50',
        ]);
        $phone = trim($validated['phone']);
        $reward = CustomerReward::firstOrCreate(
            ['customer_phone' => $phone],
            ['customer_name' => 'ลูกค้า', 'points' => 0, 'total_stamps' => 0, 'free_skewers_redeemed' => 0]
        );
        session(['customer_phone' => $phone]);

        $vouchers = RewardRedemption::where('customer_phone', $phone)->latest('id')->get();

        return response()->json([
            'success' => true,
            'reward' => $reward,
            'vouchers' => $vouchers,
        ]);
    }

    /**
     * Submit customer review and award +1 loyalty point instantly.
     */
    public function submitReview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:50',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
            'favorite_dishes' => 'nullable|string|max:255',
            'image_url' => 'nullable|string|max:500',
            'order_no' => 'nullable|string|max:100',
        ]);

        $phone = trim($validated['customer_phone']);

        $review = CustomerReview::create([
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $phone,
            'rating' => (int) $validated['rating'],
            'comment' => $validated['comment'],
            'favorite_dishes' => $validated['favorite_dishes'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'order_no' => $validated['order_no'] ?? null,
            'bonus_points' => 1,
        ]);

        // Reward customer with +1 point for review!
        $reward = CustomerReward::firstOrCreate(
            ['customer_phone' => $phone],
            ['customer_name' => $validated['customer_name'], 'points' => 0, 'total_stamps' => 0, 'free_skewers_redeemed' => 0]
        );
        $reward->increment('points', 1);
        $reward->increment('total_stamps', 1);
        if (!empty($validated['customer_name'])) {
            $reward->customer_name = $validated['customer_name'];
            $reward->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'ขอบคุณสำหรับรีวิว! คุณได้รับแต้มสะสมเพิ่ม +1 แต้มเรียบร้อยแล้ว',
            'review' => $review,
            'points' => $reward->points,
            'total_stamps' => $reward->total_stamps,
        ]);
    }

    /**
     * Redeem 5 loyalty points for 1 Free Skewer voucher.
     */
    public function redeemReward(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:50',
        ]);

        $phone = trim($validated['phone']);
        $reward = CustomerReward::where('customer_phone', $phone)->first();

        if (!$reward || $reward->points < 5) {
            return response()->json([
                'success' => false,
                'message' => 'แต้มสะสมของคุณยังไม่ครบ 5 แต้ม (ปัจจุบันมี ' . ($reward ? $reward->points : 0) . ' แต้ม)',
            ], 422);
        }

        // Deduct 5 points and generate voucher
        $reward->decrement('points', 5);
        $reward->increment('free_skewers_redeemed', 1);

        $voucherCode = 'FREE-' . strtoupper(Str::random(6));

        $redemption = RewardRedemption::create([
            'customer_phone' => $phone,
            'voucher_code' => $voucherCode,
            'reward_title' => 'ฟรีหม่าล่า 1 ไม้ (เลือกรสชาติและระดับความเผ็ดได้ตามใจชอบ)',
            'points_spent' => 5,
            'status' => 'active',
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'แลกรับสิทธิ์สำเร็จ! รับคูปองฟรีหม่าล่า 1 ไม้ รหัส: ' . $voucherCode,
            'voucher' => $redemption,
            'new_points' => $reward->points,
        ]);
    }

    /**
     * Grab Food Live Order Tracking View.
     */
    public function track(string $order_no): View
    {
        $order = Order::with('items')->where('order_no', $order_no)->firstOrFail();
        return view('online.track', compact('order'));
    }

    /**
     * Live JSON status endpoint for AJAX poll in Grab tracker.
     */
    public function statusJson(string $order_no): JsonResponse
    {
        $order = Order::where('order_no', $order_no)->firstOrFail();

        return response()->json([
            'status' => $order->status,
            'queue_number' => $order->queue_number,
            'updated_at' => $order->updated_at->toTimeString(),
        ]);
    }

    /**
     * Update active Online Hero Banner image and position.
     */
    public function updateHeroBanner(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image_url' => 'required|string',
            'pos_x' => 'nullable|numeric|min:0|max:100',
            'pos_y' => 'nullable|numeric|min:0|max:100',
            'scale' => 'nullable|numeric|min:0.5|max:3',
        ]);

        $imageUrl = trim($validated['image_url']);
        $position = [
            'x' => isset($validated['pos_x']) ? round((float) $validated['pos_x'], 1) : 50,
            'y' => isset($validated['pos_y']) ? round((float) $validated['pos_y'], 1) : 50,
            'scale' => isset($validated['scale']) ? round((float) $validated['scale'], 2) : 1.0,
        ];

        Setting::set('online_hero_banner_image', $imageUrl, 'storefront');
        Setting::set('online_hero_banner_position', json_encode($position), 'storefront');

        // Update history
        $history = json_decode(Setting::get('online_hero_banner_history', '[]'), true) ?: [];
        $defaultHeroImage = asset('assets/online/hero-banner-ref.png');
        if (!in_array($defaultHeroImage, $history)) {
            $history[] = $defaultHeroImage;
        }
        if (!in_array($imageUrl, $history)) {
            array_unshift($history, $imageUrl);
        } else {
            $history = array_values(array_diff($history, [$imageUrl]));
            array_unshift($history, $imageUrl);
        }
        $history = array_slice($history, 0, 24);
        Setting::set('online_hero_banner_history', json_encode($history), 'storefront');

        return response()->json([
            'success' => true,
            'message' => 'บันทึกภาพปกหน้าร้านและตำแหน่งแสดงผลเรียบร้อยแล้ว 🔥',
            'image_url' => $imageUrl,
            'position' => $position,
            'history' => $history,
        ]);
    }

    /**
     * Upload and canvas-compress a new Online Hero Banner image, converting to URL immediately.
     */
    public function uploadHeroBannerImage(Request $request): JsonResponse
    {
        $destDir = public_path('uploads/hero');
        if (!file_exists($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $imageUrl = null;

        // Process client canvas-compressed base64 or file upload
        if ($request->filled('image_base64')) {
            $base64 = $request->input('image_base64');
            $ext = 'jpg';
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
                $ext = strtolower($matches[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                $base64 = substr($base64, strpos($base64, ',') + 1);
            }
            $decoded = base64_decode($base64);
            if ($decoded !== false && strlen($decoded) > 50) {
                $filename = 'hero_' . time() . '_' . Str::random(6) . '.' . $ext;
                file_put_contents($destDir . '/' . $filename, $decoded);
                $imageUrl = asset('uploads/hero/' . $filename);
            }
        } elseif ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            if ($file->isValid()) {
                $ext = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
                if ($ext === 'jpeg') $ext = 'jpg';
                $filename = 'hero_' . time() . '_' . Str::random(6) . '.' . $ext;
                $file->move($destDir, $filename);
                $imageUrl = asset('uploads/hero/' . $filename);
            }
        }

        if (!$imageUrl) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบไฟล์ภาพหรือการประมวลผลล้มเหลว กรุณาลองใหม่อีกครั้ง',
            ], 422);
        }

        // Persist immediately as active and save base64 backup
        Setting::set('online_hero_banner_image', $imageUrl, 'storefront');
        if ($request->filled('image_base64')) {
            Setting::set('online_hero_banner_base64', $request->input('image_base64'), 'storefront');
        }

        // Add to history
        $history = json_decode(Setting::get('online_hero_banner_history', '[]'), true) ?: [];
        $defaultHeroImage = asset('assets/online/hero-banner-ref.png');
        if (!in_array($defaultHeroImage, $history)) {
            $history[] = $defaultHeroImage;
        }
        if (!in_array($imageUrl, $history)) {
            array_unshift($history, $imageUrl);
        }
        $history = array_slice($history, 0, 24);
        Setting::set('online_hero_banner_history', json_encode($history), 'storefront');

        return response()->json([
            'success' => true,
            'message' => 'อัปโหลดและแปลงภาพปกเป็น URL สำเร็จแล้ว',
            'url' => $imageUrl,
            'history' => $history,
        ]);
    }

    /**
     * Delete an image from hero banner history.
     */
    public function deleteHeroBannerHistory(Request $request): JsonResponse
    {
        $imageUrl = trim((string) $request->input('image_url'));
        if (empty($imageUrl)) {
            return response()->json(['success' => false, 'message' => 'กรุณาระบุ URL ภาพ'], 422);
        }

        $defaultHeroImage = asset('assets/online/hero-banner-ref.png');
        if (str_contains($imageUrl, 'hero-banner-ref.png')) {
            return response()->json(['success' => false, 'message' => 'ภาพปกเริ่มต้นของระบบไม่สามารถลบได้'], 422);
        }

        $history = json_decode(Setting::get('online_hero_banner_history', '[]'), true) ?: [];
        $history = array_values(array_filter($history, fn ($url) => trim($url) !== $imageUrl));
        if (empty($history)) {
            $history = [$defaultHeroImage];
        }
        Setting::set('online_hero_banner_history', json_encode($history), 'storefront');

        // If active image was deleted, fallback to default
        $currentActive = Setting::get('online_hero_banner_image');
        $newActive = $currentActive;
        if ($currentActive === $imageUrl) {
            $newActive = $history[0] ?? $defaultHeroImage;
            Setting::set('online_hero_banner_image', $newActive, 'storefront');
        }

        // Try deleting file if stored in uploads/hero
        if (str_contains($imageUrl, '/uploads/hero/')) {
            $filename = basename(parse_url($imageUrl, PHP_URL_PATH));
            $path = public_path('uploads/hero/' . $filename);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'ลบภาพปกออกจากประวัติเรียบร้อยแล้ว',
            'active_image' => $newActive,
            'history' => $history,
        ]);
    }

    /**
     * Update storefront Profile / Logo image and focal position.
     */
    public function updateProfileLogo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image_url' => 'required|string|max:1000',
            'pos_x' => 'nullable|numeric|min:0|max:100',
            'pos_y' => 'nullable|numeric|min:0|max:100',
            'scale' => 'nullable|numeric|min:0.5|max:3',
        ]);

        $imageUrl = trim($validated['image_url']);
        $position = [
            'x' => isset($validated['pos_x']) ? round((float) $validated['pos_x'], 1) : 50,
            'y' => isset($validated['pos_y']) ? round((float) $validated['pos_y'], 1) : 50,
            'scale' => isset($validated['scale']) ? round((float) $validated['scale'], 2) : 1.0,
        ];

        Setting::set('online_profile_logo_image', $imageUrl, 'storefront');
        Setting::set('online_profile_logo_position', json_encode($position), 'storefront');

        // Update history
        $history = json_decode(Setting::get('online_profile_logo_history', '[]'), true) ?: [];
        $defaultLogo = asset('assets/brand/logo-card.png');
        if (!in_array($defaultLogo, $history)) {
            $history[] = $defaultLogo;
        }
        if (!in_array($imageUrl, $history)) {
            array_unshift($history, $imageUrl);
        } else {
            $history = array_values(array_diff($history, [$imageUrl]));
            array_unshift($history, $imageUrl);
        }
        $history = array_slice($history, 0, 24);
        Setting::set('online_profile_logo_history', json_encode($history), 'storefront');

        return response()->json([
            'success' => true,
            'message' => 'บันทึกรูปโปรไฟล์และตำแหน่งแสดงผลเรียบร้อยแล้ว 🔥',
            'image_url' => $imageUrl,
            'position' => $position,
            'history' => $history,
        ]);
    }

    /**
     * Upload and canvas-compress a new Online Profile / Logo image, converting to URL immediately.
     */
    public function uploadProfileLogoImage(Request $request): JsonResponse
    {
        $destDir = public_path('uploads/logo');
        if (!file_exists($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $imageUrl = null;

        if ($request->filled('image_base64')) {
            $base64 = $request->input('image_base64');
            $ext = 'png';
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
                $ext = strtolower($matches[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                $base64 = substr($base64, strpos($base64, ',') + 1);
            }
            $decoded = base64_decode($base64);
            if ($decoded !== false && strlen($decoded) > 50) {
                $filename = 'logo_' . time() . '_' . Str::random(6) . '.' . $ext;
                file_put_contents($destDir . '/' . $filename, $decoded);
                $imageUrl = asset('uploads/logo/' . $filename);
            }
        } elseif ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            if ($file->isValid()) {
                $ext = strtolower($file->getClientOriginalExtension()) ?: 'png';
                if ($ext === 'jpeg') $ext = 'jpg';
                $filename = 'logo_' . time() . '_' . Str::random(6) . '.' . $ext;
                $file->move($destDir, $filename);
                $imageUrl = asset('uploads/logo/' . $filename);
            }
        }

        if (!$imageUrl) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบไฟล์ภาพหรือการประมวลผลล้มเหลว กรุณาลองใหม่อีกครั้ง',
            ], 422);
        }

        // Persist immediately as active and save base64 backup
        Setting::set('online_profile_logo_image', $imageUrl, 'storefront');
        if ($request->filled('image_base64')) {
            Setting::set('online_profile_logo_base64', $request->input('image_base64'), 'storefront');
        }

        $history = json_decode(Setting::get('online_profile_logo_history', '[]'), true) ?: [];
        $defaultLogo = asset('assets/brand/logo-card.png');
        if (!in_array($defaultLogo, $history)) {
            $history[] = $defaultLogo;
        }
        if (!in_array($imageUrl, $history)) {
            array_unshift($history, $imageUrl);
        }
        $history = array_slice($history, 0, 24);
        Setting::set('online_profile_logo_history', json_encode($history), 'storefront');

        return response()->json([
            'success' => true,
            'message' => 'อัปโหลดและแปลงรูปโปรไฟล์เป็น URL สำเร็จแล้ว',
            'url' => $imageUrl,
            'history' => $history,
        ]);
    }

    /**
     * Delete an image from profile logo history.
     */
    public function deleteProfileLogoHistory(Request $request): JsonResponse
    {
        $imageUrl = trim((string) $request->input('image_url'));
        if (empty($imageUrl)) {
            return response()->json(['success' => false, 'message' => 'กรุณาระบุ URL ภาพ'], 422);
        }

        $defaultLogo = asset('assets/brand/logo-card.png');
        if (str_contains($imageUrl, 'logo-card.png')) {
            return response()->json(['success' => false, 'message' => 'รูปโปรไฟล์เริ่มต้นของระบบไม่สามารถลบได้'], 422);
        }

        $history = json_decode(Setting::get('online_profile_logo_history', '[]'), true) ?: [];
        $history = array_values(array_filter($history, fn ($url) => trim($url) !== $imageUrl));
        if (empty($history)) {
            $history = [$defaultLogo];
        }
        Setting::set('online_profile_logo_history', json_encode($history), 'storefront');

        $currentActive = Setting::get('online_profile_logo_image');
        $newActive = $currentActive;
        if ($currentActive === $imageUrl) {
            $newActive = $history[0] ?? $defaultLogo;
            Setting::set('online_profile_logo_image', $newActive, 'storefront');
        }

        if (str_contains($imageUrl, '/uploads/logo/')) {
            $filename = basename(parse_url($imageUrl, PHP_URL_PATH));
            $path = public_path('uploads/logo/' . $filename);
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'ลบรูปโปรไฟล์ออกจากประวัติเรียบร้อยแล้ว',
            'active_image' => $newActive,
            'history' => $history,
        ]);
    }

    /**
     * Toggle storefront promotion voucher banner visibility.
     */
    public function togglePromoBanner(Request $request): JsonResponse
    {
        $show = $request->has('show') ? $request->boolean('show') : (Setting::get('online_show_promo_banner', '1') === '0');
        Setting::set('online_show_promo_banner', $show ? '1' : '0', 'storefront');

        return response()->json([
            'success' => true,
            'message' => $show ? 'เปิดการแสดงแถบโปรโมชั่นแล้ว' : 'ปิดการแสดงแถบโปรโมชั่นแล้ว',
            'show' => $show,
        ]);
    }
}
