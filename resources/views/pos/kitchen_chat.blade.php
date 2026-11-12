<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>แชทลูกค้า (ฝั่งแอดมิน / จอครัว) - หม่าล่าสตรีท</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('fonts/mala-street-font.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Prompt', sans-serif; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .bubble-tail-right::after {
            content: '';
            position: absolute;
            bottom: 4px;
            right: -6px;
            width: 0;
            height: 0;
            border-top: 6px solid transparent;
            border-left: 8px solid #D90012;
            border-bottom: 6px solid transparent;
        }
        .bubble-tail-left::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: -6px;
            width: 0;
            height: 0;
            border-top: 6px solid transparent;
            border-right: 8px solid #ffffff;
            border-bottom: 6px solid transparent;
        }
    </style>
</head>
<body class="h-full min-h-screen bg-[#111317] flex justify-center items-stretch selection:bg-[#E60018] selection:text-white">

    <!-- MOBILE CONTAINER (400 - 430px Frame Layout ตรงกลางจอคอมพิวเตอร์ / ฝั่งขวาของจอครัว) -->
    <div class="w-full max-w-[430px] h-full min-h-screen bg-[#F6F7F9] shadow-[0_0_60px_rgba(0,0,0,0.6)] flex flex-col justify-between relative overflow-hidden">

        <!-- ================================================================= -->
        <!-- 1. TOP HEADER (ADMIN KITCHEN BAR)                                 -->
        <!-- ================================================================= -->
        <header class="sticky top-0 z-30 bg-slate-950 text-white px-3.5 py-2.5 shadow-md border-b border-slate-800 shrink-0">
            <div class="flex items-center justify-between gap-2">
                <!-- Back Button to Kitchen (KDS) & Title -->
                <div class="flex items-center gap-2 min-w-0">
                    <button type="button"
                       onclick="handleBackToKitchen()"
                       class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-200 flex items-center justify-center transition-all shrink-0 active:scale-95 cursor-pointer"
                       title="กลับจอครัว (KDS)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <!-- Pop-out standalone button -->
                    <a href="{{ route('kitchen.chat', array_filter(['order_no' => $activeOrder ? $activeOrder->order_no : null])) }}" target="_blank"
                       class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center transition-all shrink-0 active:scale-95"
                       title="เปิดเต็มหน้าต่างใหม่">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>

                    <!-- Admin Mini Avatar -->
                    <div class="relative shrink-0">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-rose-600 to-amber-500 flex items-center justify-center text-white font-bold text-sm shadow-xs border border-rose-400/40">
                            👨‍🍳
                        </div>
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-slate-950"></span>
                    </div>

                    <!-- Title & Kitchen Role -->
                    <div class="min-w-0 leading-tight">
                        <div class="flex items-center gap-1.5">
                            <h1 class="text-[13.5px] font-bold text-white truncate">แชทลูกค้า (แอดมินครัว)</h1>
                            <span class="text-[9px] bg-rose-900/90 text-rose-200 border border-rose-700/60 font-extrabold px-1.5 py-0.2 rounded-full shrink-0">ตอบสด</span>
                        </div>
                        <div class="text-[10px] text-emerald-400 font-medium flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>กำลังเชื่อมต่อจอครัว • ซิงค์เรียลไทม์</span>
                        </div>
                    </div>
                </div>

                <!-- Right Action: Switch Order Button -->
                <button type="button"
                        onclick="openOrderSelectorModal()"
                        class="px-2.5 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 text-[11px] font-bold transition-all flex items-center gap-1 shrink-0 active:scale-95"
                        title="สลับออร์เดอร์ / ลูกค้าที่คุย">
                    <span>สลับคิว</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>
        </header>

        <!-- ================================================================= -->
        <!-- 2. ORDER + IP ADDRESS BINDING CARD (ป้องกันการสับสนและออร์เดอร์ผิดพลาด) -->
        <!-- ================================================================= -->
        @if($activeOrder)
        <div class="bg-gradient-to-r from-slate-900 via-slate-950 to-slate-900 text-white px-3.5 py-2.5 border-b border-slate-800 shrink-0 shadow-sm">
            <!-- Row 1: Queue, Order No & Status -->
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="px-2 py-0.5 rounded-md bg-rose-600 text-white text-xs font-black shrink-0 tracking-tight shadow-xs">
                        คิว {{ $activeOrder->queue_number }}
                    </span>
                    <div class="min-w-0">
                        <div class="text-[12px] font-bold text-white truncate flex items-center gap-1">
                            <span>{{ $activeOrder->customer_name ?: 'ลูกค้าสั่งออนไลน์' }}</span>
                            @if($activeOrder->customer_phone)
                                <span class="text-[10.5px] text-slate-400 font-mono">({{ $activeOrder->customer_phone }})</span>
                            @endif
                        </div>
                        <div class="text-[10px] text-slate-400 font-mono truncate">
                            {{ $activeOrder->order_no }}
                        </div>
                    </div>
                </div>

                @php
                    $statusBadge = match($activeOrder->status) {
                        'pending' => ['bg' => 'bg-amber-950/80 text-amber-300 border-amber-700/50', 'label' => 'รอรับคิว'],
                        'confirmed' => ['bg' => 'bg-blue-950/80 text-blue-300 border-blue-700/50', 'label' => 'รับออเดอร์'],
                        'cooking' => ['bg' => 'bg-rose-950/80 text-rose-300 border-rose-700/50', 'label' => '🔥 กำลังปรุง'],
                        'ready' => ['bg' => 'bg-emerald-950/80 text-emerald-300 border-emerald-700/50', 'label' => '✅ พร้อมเสิร์ฟ'],
                        'completed' => ['bg' => 'bg-slate-800 text-slate-300 border-slate-700', 'label' => 'เสร็จสิ้น'],
                        default => ['bg' => 'bg-slate-800 text-slate-300 border-slate-700', 'label' => $activeOrder->status]
                    };
                @endphp
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge['bg'] }} shrink-0">
                    {{ $statusBadge['label'] }}
                </span>
            </div>

            <!-- Row 2: IP Address Device Tracker & Security Shield -->
            <div class="mt-2 pt-2 border-t border-slate-800/80 flex items-center justify-between text-[10.5px]">
                <div class="flex items-center gap-1.5 text-amber-300 font-mono font-semibold">
                    <!-- Global / Device Network SVG -->
                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke-width="1.8"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                    <span>IP เครื่องลูกค้า:</span>
                    <span class="text-white bg-slate-800 px-1.5 py-0.2 rounded font-black tracking-tight" id="active-device-ip">
                        {{ $targetIp }}
                    </span>
                </div>

                <div class="flex items-center gap-1 text-[10px] text-emerald-400">
                    <svg class="w-3 h-3 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>จับคู่เครื่องถูกต้อง</span>
                </div>
            </div>

            <!-- Row 3: Items Ordered Preview -->
            @if($activeOrder->items && $activeOrder->items->count() > 0)
            <div class="mt-1.5 text-[10.5px] text-slate-300 truncate bg-slate-900/80 px-2 py-1 rounded-lg border border-slate-800">
                <span class="text-amber-400 font-bold">🍢 รายการ ({{ $activeOrder->items->count() }}):</span>
                @foreach($activeOrder->items->take(3) as $item)
                    <span>{{ $item->product_name ?? 'รายการ' }} ({{ $item->quantity }}x)@if(!$loop->last), @endif</span>
                @endforeach
                @if($activeOrder->items->count() > 3)
                    <span class="text-slate-400">+อีก {{ $activeOrder->items->count() - 3 }} รายการ</span>
                @endif
                <span class="text-amber-300 font-bold font-mono ml-1">฿{{ number_format($activeOrder->total_amount, 0) }}</span>
            </div>
            @endif
        </div>
        @else
        <div class="bg-slate-900 text-white px-3.5 py-3 border-b border-slate-800 shrink-0 text-center">
            <p class="text-xs text-amber-300 font-bold">ยังไม่มีออเดอร์ออนไลน์ที่กำลังสนทนา</p>
            <p class="text-[10px] text-slate-400 mt-0.5">กดปุ่ม "สลับคิว" ด้านบนเพื่อเลือกออเดอร์หรือลูกค้าที่ต้องการแชท</p>
        </div>
        @endif

        <!-- ================================================================= -->
        <!-- 3. CHAT MESSAGES SCROLLABLE LIST                                  -->
        <!-- ================================================================= -->
        <main id="chat-messages-container" class="flex-1 overflow-y-auto p-3.5 space-y-3 hide-scrollbar">
            <!-- Date Separator -->
            <div class="flex items-center justify-center my-1">
                <span class="px-3 py-1 rounded-full bg-slate-200/80 text-[10.5px] font-bold text-slate-600 shadow-2xs">
                    วันนี้ · {{ \App\Support\ThaiDate::format(now(), false) }}
                </span>
            </div>

            <div id="messages-list" class="space-y-3">
                @if($messages->isEmpty())
                    <!-- Empty State -->
                    <div class="text-center py-10 px-4" id="empty-chat-state">
                        <div class="w-14 h-14 rounded-full bg-slate-200/90 text-slate-500 flex items-center justify-center mx-auto text-2xl mb-2.5">
                            💬
                        </div>
                        <h3 class="text-xs font-bold text-slate-700">เริ่มสนทนากับลูกค้า</h3>
                        <p class="text-[11px] text-slate-500 mt-1 max-w-xs mx-auto">
                            พิมพ์ข้อความหรือกดเลือกคำตอบด่วนด้านล่าง เพื่อแจ้งสถานะการทำอาหารหรือสอบถามลูกค้ารายนี้
                        </p>
                    </div>
                @else
                    @foreach($messages as $msg)
                        @php
                            $isShop = $msg->sender === 'shop';
                            $timeStr = $msg->created_at ? $msg->created_at->timezone('Asia/Bangkok')->format('H:i') . ' น.' : '';
                        @endphp

                        @if($isShop)
                            <!-- Shop / Admin Bubble (Right side in Admin view) -->
                            <div class="flex items-end justify-end gap-1.5 ml-auto max-w-[85%] animate-in fade-in slide-in-from-bottom-2 duration-150">
                                <div class="relative bg-[#D90012] text-white rounded-2xl rounded-tr-xs px-3.5 py-2 shadow-2xs bubble-tail-right text-[12.5px] leading-relaxed">
                                    <div class="text-[9.5px] font-black text-rose-200 mb-0.5 flex items-center justify-end gap-1">
                                        <span>คุณ (แอดมินร้านค้า)</span>
                                        <span>👨‍🍳</span>
                                    </div>
                                    <div class="whitespace-pre-line">{{ $msg->message }}</div>
                                    @if($msg->image_url)
                                        <div class="mt-2 rounded-xl overflow-hidden border border-white/30 bg-black/20">
                                            <img src="{{ $msg->image_url }}" alt="ภาพแนบ" class="w-full max-h-48 object-cover cursor-pointer" onclick="viewFullImage('{{ $msg->image_url }}')">
                                        </div>
                                    @endif
                                    <div class="text-[9px] text-white/70 text-right mt-1 font-mono flex items-center justify-end gap-1">
                                        <span>{{ $timeStr }}</span>
                                        <span>✓✓</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Customer Bubble (Left side in Admin view) -->
                            <div class="flex items-start gap-2 max-w-[85%] animate-in fade-in slide-in-from-bottom-2 duration-150">
                                <div class="w-7 h-7 rounded-full bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                                    👤
                                </div>
                                <div class="relative bg-white text-slate-800 rounded-2xl rounded-tl-xs px-3.5 py-2 shadow-2xs border border-slate-200/90 bubble-tail-left text-[12.5px] leading-relaxed">
                                    <div class="flex items-center justify-between gap-2 mb-0.5">
                                        <span class="text-[10px] font-bold text-slate-900">{{ $msg->customer_name ?: 'ลูกค้า' }}</span>
                                        @if($msg->ip_address)
                                            <span class="text-[8.5px] bg-slate-100 text-slate-500 font-mono px-1 rounded border border-slate-200">
                                                IP: {{ $msg->ip_address }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="whitespace-pre-line text-slate-800 font-medium">{{ $msg->message }}</div>
                                    @if($msg->image_url)
                                        <div class="mt-2 rounded-xl overflow-hidden border border-slate-200 bg-slate-50">
                                            <img src="{{ $msg->image_url }}" alt="ภาพแนบ" class="w-full max-h-48 object-cover cursor-pointer" onclick="viewFullImage('{{ $msg->image_url }}')">
                                        </div>
                                    @endif
                                    <div class="text-[9px] text-slate-400 text-right mt-1 font-mono">
                                        {{ $timeStr }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </main>

        <!-- ================================================================= -->
        <!-- 4. QUICK REPLIES CHIPS FOR KITCHEN                                -->
        <!-- ================================================================= -->
        <div class="px-3 pt-2 bg-slate-100 border-t border-slate-200/80 overflow-x-auto hide-scrollbar flex items-center gap-1.5 shrink-0">
            <span class="text-[10px] text-slate-500 font-bold shrink-0">คำตอบด่วน:</span>
            <button type="button" onclick="sendQuickReply('🔥 กำลังย่างเตาถ่านให้ร้อนๆ หอมกรุ่นครับ')"
                    class="text-[11px] font-semibold text-slate-700 bg-white hover:bg-rose-50 hover:text-rose-600 hover:border-rose-300 px-2.5 py-1 rounded-full border border-slate-300/80 shrink-0 transition-all active:scale-95 shadow-2xs">
                🔥 กำลังย่างเตาถ่าน
            </button>
            <button type="button" onclick="sendQuickReply('🍲 น้ำซุปกำลังเดือดเข้มข้น จัดเตรียมให้เรียบร้อยครับ')"
                    class="text-[11px] font-semibold text-slate-700 bg-white hover:bg-rose-50 hover:text-rose-600 hover:border-rose-300 px-2.5 py-1 rounded-full border border-slate-300/80 shrink-0 transition-all active:scale-95 shadow-2xs">
                🍲 กำลังต้มซุป
            </button>
            <button type="button" onclick="sendQuickReply('✅ ปรุงเสร็จเรียบร้อยแล้วครับ สามารถเข้ามารับที่หน้าร้านได้เลยครับ')"
                    class="text-[11px] font-semibold text-slate-700 bg-white hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-300 px-2.5 py-1 rounded-full border border-slate-300/80 shrink-0 transition-all active:scale-95 shadow-2xs">
                ✅ ปรุงเสร็จแล้ว มารับได้เลย
            </button>
            <button type="button" onclick="sendQuickReply('🌶️ รับทราบระดับความเผ็ดชาครับ ทางร้านปรับสูตรพิเศษให้ตามที่ขอครับ')"
                    class="text-[11px] font-semibold text-slate-700 bg-white hover:bg-rose-50 hover:text-rose-600 hover:border-rose-300 px-2.5 py-1 rounded-full border border-slate-300/80 shrink-0 transition-all active:scale-95 shadow-2xs">
                🌶️ ปรับความเผ็ดให้แล้ว
            </button>
            <button type="button" onclick="sendQuickReply('🛵 ไรเดอร์กำลังเดินทางมา รอสักครู่เดียวครับ')"
                    class="text-[11px] font-semibold text-slate-700 bg-white hover:bg-amber-50 hover:text-amber-700 hover:border-amber-300 px-2.5 py-1 rounded-full border border-slate-300/80 shrink-0 transition-all active:scale-95 shadow-2xs">
                🛵 ไรเดอร์กำลังมา
            </button>
        </div>

        <!-- ================================================================= -->
        <!-- 5. CHAT INPUT BAR                                                 -->
        <!-- ================================================================= -->
        <footer class="bg-white border-t border-slate-200 p-2.5 shrink-0 relative z-20">
            <!-- Image Attachment Preview (Hidden by default) -->
            <div id="attached-image-preview-bar" class="hidden mb-2 p-2 bg-slate-50 border border-slate-200 rounded-xl items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <img id="attached-preview-img" src="" alt="แนบรูป" class="w-10 h-10 object-cover rounded-lg border border-slate-300">
                    <span class="text-xs text-slate-600 truncate font-medium">แนบภาพประกอบออเดอร์</span>
                </div>
                <button type="button" onclick="cancelImageAttachment()" class="text-rose-600 hover:text-rose-700 p-1 text-xs font-bold">
                    ✕ ยกเลิก
                </button>
            </div>

            <form id="chat-form" onsubmit="handleChatSubmit(event)" class="flex items-center gap-1.5">
                <!-- Attach Image Button -->
                <button type="button"
                        onclick="document.getElementById('chat-image-input').click()"
                        class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition shrink-0 active:scale-95 cursor-pointer"
                        title="แนบรูปภาพส่งให้ลูกค้า">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </button>
                <input type="file" id="chat-image-input" accept="image/*" class="hidden" onchange="handleImageSelection(event)">

                <!-- Text Input -->
                <input type="text"
                       id="chat-message-input"
                       placeholder="พิมพ์ข้อความตอบกลับลูกค้า (ในชื่อแอดมิน)..."
                       autocomplete="off"
                       class="flex-1 bg-slate-100 border border-slate-200 rounded-full px-4 py-2 text-xs sm:text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition">

                <!-- Send Button -->
                <button type="submit"
                        id="send-button"
                        class="w-9 h-9 rounded-full bg-gradient-to-r from-[#D90012] to-[#B8000F] hover:from-[#B8000F] hover:to-[#900000] text-white flex items-center justify-center transition shrink-0 shadow-sm shadow-rose-950/20 active:scale-95 cursor-pointer">
                    <svg class="w-4 h-4 translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </button>
            </form>
        </footer>

        <!-- ================================================================= -->
        <!-- 6. ORDER SELECTOR MODAL (สลับคิวและเลือกลูกค้า)                      -->
        <!-- ================================================================= -->
        <div id="order-selector-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-sm w-full p-4 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-150 max-h-[85vh] flex flex-col">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 shrink-0">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">เลือกออร์เดอร์ / ลูกค้าที่ต้องการคุย</h3>
                        <p class="text-[10.5px] text-slate-500">จับคู่เลขคิว + ชื่อ + เลขไอพีเครื่องเพื่อความถูกต้อง</p>
                    </div>
                    <button type="button" onclick="closeOrderSelectorModal()" class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-xs">✕</button>
                </div>

                <div class="overflow-y-auto space-y-2 py-3 flex-1 hide-scrollbar">
                    @forelse($recentOrders as $ord)
                        @php
                            $isSelected = $activeOrder && $activeOrder->id === $ord->id;
                            $ordIp = $ord->ip_address ?: '127.0.0.1';
                        @endphp
                        <button type="button"
                                onclick="selectOrderForChat('{{ $ord->order_no }}')"
                                class="w-full text-left p-2.5 rounded-2xl border transition-all flex items-center justify-between gap-2 {{ $isSelected ? 'bg-rose-50 border-rose-300 ring-2 ring-rose-500/20' : 'bg-slate-50 hover:bg-white border-slate-200' }}">
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-black text-[11px]">
                                        คิว {{ $ord->queue_number }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-900 truncate">
                                        {{ $ord->customer_name ?: 'ลูกค้าสั่งออนไลน์' }}
                                    </span>
                                </div>
                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                                    {{ $ord->order_no }}
                                </div>
                                <div class="text-[9.5px] text-amber-700 font-mono flex items-center gap-1 mt-0.5">
                                    <span>🌐 IP: {{ $ordIp }}</span>
                                    <span>•</span>
                                    <span>฿{{ number_format($ord->total_amount, 0) }}</span>
                                </div>
                            </div>
                            @if($isSelected)
                                <span class="text-[10px] bg-rose-600 text-white font-bold px-2 py-0.5 rounded-full shrink-0">กำลังคุย</span>
                            @else
                                <span class="text-[11px] text-slate-400 shrink-0">เลือก →</span>
                            @endif
                        </button>
                    @empty
                        <div class="text-center py-6 text-xs text-slate-400">ยังไม่มีรายการออร์เดอร์</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- 7. IMAGE VIEWER FULLSCREEN MODAL                                  -->
        <!-- ================================================================= -->
        <div id="image-viewer-modal" class="fixed inset-0 z-50 bg-black/90 hidden items-center justify-center p-4" onclick="closeImageViewer()">
            <img id="image-viewer-img" src="" alt="ภาพเต็ม" class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl">
        </div>

    </div>

    <!-- ===================================================================== -->
    <!-- SCRIPT LOGIC (POLLING, SENDING, REAL-TIME SYNC)                       -->
    <!-- ===================================================================== -->
    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const ACTIVE_ORDER_NO = "{{ $activeOrder ? $activeOrder->order_no : '' }}";
        const CUSTOMER_NAME = "{{ $activeOrder ? ($activeOrder->customer_name ?: 'ลูกค้า') : 'ลูกค้า' }}";
        const CUSTOMER_PHONE = "{{ $activeOrder ? $activeOrder->customer_phone : '' }}";
        const TARGET_IP = "{{ $targetIp }}";

        let currentAttachedImageUrl = null;
        let lastMessageCount = {{ $messages->count() }};

        // Scroll down on load
        window.addEventListener('DOMContentLoaded', () => {
            scrollToBottom();
            // Start polling every 3 seconds for live chat sync
            setInterval(pollNewMessages, 3000);
        });

        function scrollToBottom() {
            const container = document.getElementById('chat-messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }

        // 1. Handle Form Submit
        function handleChatSubmit(event) {
            event.preventDefault();
            const input = document.getElementById('chat-message-input');
            const text = input.value.trim();

            if (!text && !currentAttachedImageUrl) return;

            submitAdminMessage(text, currentAttachedImageUrl);
            input.value = '';
            cancelImageAttachment();
        }

        // 2. Quick Reply Helper
        function sendQuickReply(text) {
            submitAdminMessage(text, null);
        }

        // 3. Submit Admin Message to Server
        async function submitAdminMessage(text, imageUrl) {
            // Remove empty state if present
            const emptyState = document.getElementById('empty-chat-state');
            if (emptyState) emptyState.remove();

            // Optimistic render Admin Bubble (Right side)
            appendAdminBubble(text, imageUrl);
            scrollToBottom();

            try {
                const response = await fetch("{{ route('pos.kitchen.chat.send') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        message: text,
                        image_url: imageUrl,
                        order_no: ACTIVE_ORDER_NO,
                        customer_name: CUSTOMER_NAME,
                        customer_phone: CUSTOMER_PHONE,
                        ip_address: TARGET_IP
                    })
                });

                const data = await response.json();
                if (!data.success) {
                    console.error('Send error:', data.message);
                }
            } catch (err) {
                console.error('Chat error:', err);
            }
        }

        function appendAdminBubble(text, imageUrl) {
            const list = document.getElementById('messages-list');
            const now = new Date();
            const timeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} น.`;

            let imgHtml = '';
            if (imageUrl) {
                imgHtml = `<div class="mt-2 rounded-xl overflow-hidden border border-white/30 bg-black/20"><img src="${imageUrl}" alt="ภาพแนบ" class="w-full max-h-48 object-cover cursor-pointer" onclick="viewFullImage('${imageUrl}')"></div>`;
            }

            const html = `
                <div class="flex items-end justify-end gap-1.5 ml-auto max-w-[85%] animate-in fade-in slide-in-from-bottom-2 duration-150">
                    <div class="relative bg-[#D90012] text-white rounded-2xl rounded-tr-xs px-3.5 py-2 shadow-2xs bubble-tail-right text-[12.5px] leading-relaxed">
                        <div class="text-[9.5px] font-black text-rose-200 mb-0.5 flex items-center justify-end gap-1">
                            <span>คุณ (แอดมินร้านค้า)</span>
                            <span>👨‍🍳</span>
                        </div>
                        <div class="whitespace-pre-line">${escapeHtml(text)}</div>
                        ${imgHtml}
                        <div class="text-[9px] text-white/70 text-right mt-1 font-mono flex items-center justify-end gap-1">
                            <span>${timeStr}</span>
                            <span>✓✓</span>
                        </div>
                    </div>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', html);
        }

        function appendCustomerBubble(text, imageUrl, ip) {
            const list = document.getElementById('messages-list');
            const now = new Date();
            const timeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} น.`;

            let imgHtml = '';
            if (imageUrl) {
                imgHtml = `<div class="mt-2 rounded-xl overflow-hidden border border-slate-200 bg-slate-50"><img src="${imageUrl}" alt="ภาพแนบ" class="w-full max-h-48 object-cover cursor-pointer" onclick="viewFullImage('${imageUrl}')"></div>`;
            }

            const ipBadge = ip ? `<span class="text-[8.5px] bg-slate-100 text-slate-500 font-mono px-1 rounded border border-slate-200">IP: ${escapeHtml(ip)}</span>` : '';

            const html = `
                <div class="flex items-start gap-2 max-w-[85%] animate-in fade-in slide-in-from-bottom-2 duration-150">
                    <div class="w-7 h-7 rounded-full bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                        👤
                    </div>
                    <div class="relative bg-white text-slate-800 rounded-2xl rounded-tl-xs px-3.5 py-2 shadow-2xs border border-slate-200/90 bubble-tail-left text-[12.5px] leading-relaxed">
                        <div class="flex items-center justify-between gap-2 mb-0.5">
                            <span class="text-[10px] font-bold text-slate-900">${escapeHtml(CUSTOMER_NAME)}</span>
                            ${ipBadge}
                        </div>
                        <div class="whitespace-pre-line text-slate-800 font-medium">${escapeHtml(text)}</div>
                        ${imgHtml}
                        <div class="text-[9px] text-slate-400 text-right mt-1 font-mono">
                            ${timeStr}
                        </div>
                    </div>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', html);
        }

        // 4. Poll New Messages from Server
        async function pollNewMessages() {
            if (!ACTIVE_ORDER_NO) return;

            try {
                const response = await fetch(`{{ route('pos.kitchen.chat.messages') }}?order_no=${encodeURIComponent(ACTIVE_ORDER_NO)}&phone=${encodeURIComponent(CUSTOMER_PHONE)}`);
                const data = await response.json();

                if (data.success && data.messages) {
                    if (data.messages.length > lastMessageCount) {
                        // New message received!
                        const newMsgs = data.messages.slice(lastMessageCount);
                        newMsgs.forEach(m => {
                            if (m.sender === 'customer') {
                                appendCustomerBubble(m.message, m.image_url, m.ip_address);
                            }
                        });
                        lastMessageCount = data.messages.length;
                        scrollToBottom();
                    }
                }
            } catch (err) {
                // Silently ignore background polling network errors
            }
        }

        // 5. Image Upload Handling
        async function handleImageSelection(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = async function(e) {
                const base64 = e.target.result;
                // Preview
                document.getElementById('attached-preview-img').src = base64;
                document.getElementById('attached-image-preview-bar').classList.remove('hidden');
                document.getElementById('attached-image-preview-bar').classList.add('flex');

                try {
                    const response = await fetch("{{ route('online.chat.upload_image') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ image_base64: base64 })
                    });
                    const resData = await response.json();
                    if (resData.success && resData.url) {
                        currentAttachedImageUrl = resData.url;
                    }
                } catch (err) {
                    console.error('Image upload err:', err);
                }
            };
            reader.readAsDataURL(file);
        }

        function cancelImageAttachment() {
            currentAttachedImageUrl = null;
            document.getElementById('chat-image-input').value = '';
            document.getElementById('attached-preview-img').src = '';
            document.getElementById('attached-image-preview-bar').classList.add('hidden');
            document.getElementById('attached-image-preview-bar').classList.remove('flex');
        }

        function selectOrderForChat(orderNo) {
            window.location.href = "{{ route('pos.kitchen.chat') }}?order_no=" + encodeURIComponent(orderNo);
        }

        function openOrderSelectorModal() {
            const modal = document.getElementById('order-selector-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeOrderSelectorModal() {
            const modal = document.getElementById('order-selector-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function viewFullImage(url) {
            const modal = document.getElementById('image-viewer-modal');
            const img = document.getElementById('image-viewer-img');
            img.src = url;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeImageViewer() {
            const modal = document.getElementById('image-viewer-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function handleBackToKitchen() {
            if (window.self !== window.top) {
                if (window.parent && typeof window.parent.scrollToKitchenOrders === 'function') {
                    window.parent.scrollToKitchenOrders();
                }
            } else {
                window.location.href = "{{ route('pos.kitchen') }}";
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>"']/g, function (m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
            });
        }
    </script>
</body>
</html>

