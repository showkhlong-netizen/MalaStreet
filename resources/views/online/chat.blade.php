<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>แชทกับร้านค้า - หม่าล่าสตรีท (Mala Street)</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('fonts/mala-street-font.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Prompt', sans-serif; }
        /* ซ่อนแถบเลื่อนขึ้นลงข้างๆ ออกทั้งหมด แต่ยังเลื่อนได้ปกติ (Hide scrollbars while preserving scroll) */
        html, body {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
        html::-webkit-scrollbar,
        body::-webkit-scrollbar,
        *::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
        }
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
<body class="min-h-screen {{ request()->has('hub') ? 'bg-[#F6F7F9]' : 'bg-[#111317]' }} flex justify-center items-start selection:bg-[#E60018] selection:text-white">

    <!-- MOBILE CONTAINER (400 - 430px Frame Layout) -->
    <div class="w-full {{ request()->has('hub') ? 'max-w-none shadow-none' : 'max-w-[430px] shadow-[0_0_60px_rgba(0,0,0,0.6)]' }} min-h-screen bg-[#F6F7F9] flex flex-col justify-between relative overflow-hidden">

        <!-- ================================================================= -->
        <!-- 1. TOP HEADER (Shopee / Grab Style with Shop Logo & Call Buttons)  -->
        <!-- ================================================================= -->
        <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-3.5 py-2.5 shadow-xs">
            <div class="flex items-center justify-between gap-2">
                <!-- Back Button & Shop Info -->
                <div class="flex items-center gap-2.5 min-w-0">
                    <a href="{{ route('online.index') }}"
                       class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-all shrink-0 active:scale-95"
                       title="กลับหน้าร้านค้า">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>

                    <!-- Shop Mini Avatar -->
                    <div class="relative shrink-0">
                        <img src="{{ asset('assets/brand/logo-card.png') }}"
                             alt="Mala Street Logo"
                             class="w-9 h-9 rounded-full object-cover border border-rose-200 shadow-2xs">
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white"></span>
                    </div>

                    <!-- Shop Title & Live Status -->
                    <div class="min-w-0 leading-tight">
                        <div class="flex items-center gap-1.5">
                            <h1 class="text-[13.5px] font-bold text-slate-900 truncate">หม่าล่าสตรีท (Mala Street)</h1>
                            <span class="text-[9px] bg-rose-100 text-rose-700 font-extrabold px-1.5 py-0.2 rounded-full shrink-0">ร้านหลัก</span>
                        </div>
                        <div class="text-[10px] text-emerald-600 font-medium flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ ($aiAutoReplyEnabled ?? true) ? 'เปิดรับออเดอร์ • AI ตอบกลับทันที' : 'เปิดรับออเดอร์ • แอดมินร้านดูแลแชท' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right Action Buttons: Mock Phone Call & Video Call -->
                <div class="flex items-center gap-1 shrink-0">
                    <!-- Audio Call Mock -->
                    <button type="button"
                            onclick="openCallModal('voice')"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-emerald-50 hover:text-emerald-600 text-slate-700 flex items-center justify-center transition-all cursor-pointer active:scale-95"
                            title="โทรเสียงผ่านแอป (ระบบกำลังพัฒนา)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </button>

                    <!-- Video Call Mock -->
                    <button type="button"
                            onclick="openCallModal('video')"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-700 flex items-center justify-center transition-all cursor-pointer active:scale-95"
                            title="วิดีโอคอล (ระบบกำลังพัฒนา)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>

                    <!-- Loyalty Stamp Quick Link -->
                    <a href="{{ route('online.rewards') }}"
                       class="w-8 h-8 rounded-full bg-amber-50 hover:bg-amber-100 text-amber-700 flex items-center justify-center transition-all shrink-0 active:scale-95 border border-amber-200"
                       title="ดูแต้มสะสม">
                        <span class="text-xs">🎁</span>
                    </a>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 2. ORDER SYNC BAR (เชื่อมต่อกับออเดอร์ปัจจุบัน)                   -->
            <!-- ============================================================= -->
            <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
                @if($activeOrder)
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="bg-rose-50 text-rose-700 font-extrabold px-2 py-0.5 rounded-md border border-rose-200 shrink-0">
                            คิว {{ $activeOrder->queue_number }}
                        </span>
                        <div class="min-w-0 leading-tight">
                            <div class="font-bold text-slate-800 truncate text-[11.5px]">
                                {{ $activeOrder->order_no }}
                            </div>
                            <div class="text-[9.5px] text-slate-500 truncate">
                                {{ $activeOrder->items->count() }} รายการ • รวม ฿{{ number_format($activeOrder->total_amount, 0) }}
                                (<span id="order-sync-status-label">{{ match($activeOrder->status) {
                                    'pending' => 'รอยืนยัน',
                                    'confirmed' => 'รับออเดอร์แล้ว',
                                    'cooking' => '🔥 กำลังย่างในครัว',
                                    'ready' => '🎉 พร้อมรับหน้าร้าน',
                                    'completed' => '✅ จัดส่งสำเร็จ',
                                    default => $activeOrder->status
                                } }}</span>)
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1 shrink-0">
                        <!-- Send Order Card to Chat Button -->
                        <button type="button"
                                onclick="sendOrderCardToChat('{{ $activeOrder->order_no }}', '{{ $activeOrder->queue_number }}', '{{ $activeOrder->total_amount }}', '{{ $activeOrder->items->count() }}', '{{ $activeOrder->status }}')"
                                class="bg-slate-900 hover:bg-black active:scale-95 text-white text-[10px] font-bold px-2 py-1 rounded-lg transition-all flex items-center gap-1 shadow-2xs cursor-pointer">
                            <span>แชร์ออเดอร์</span>
                        </button>

                        <!-- Live Track Sheet Button (ติดตามออเดอร์ สีเขียวสดใส) -->
                        <button type="button"
                                onclick="openOrderDetailSheet()"
                                class="bg-[#00C853] hover:bg-[#00B248] active:scale-95 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg transition-all flex items-center gap-0.5 shadow-2xs cursor-pointer">
                            <span>ติดตามออเดอร์</span>
                        </button>

                        <!-- Switch Order Button -->
                        <button type="button"
                                onclick="openOrderSelectorModal()"
                                class="text-slate-400 hover:text-slate-600 text-xs px-1 cursor-pointer"
                                title="สลับออเดอร์อื่น">
                            ▼
                        </button>
                    </div>
                @else
                    <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        <span>ยังไม่ได้เลือกออเดอร์เชื่อมต่อ</span>
                    </div>
                    <button type="button"
                            onclick="openOrderSelectorModal()"
                            class="text-rose-600 hover:text-rose-700 font-bold text-[10.5px] cursor-pointer">
                        + ค้นหาออเดอร์ของฉัน
                    </button>
                @endif
            </div>

            @if($activeOrder)
                <!-- Compact Live Order Timeline Strip (Mala Street Red Theme) -->
                <div id="order-timeline-strip"
                     onclick="openOrderDetailSheet()"
                     role="button"
                     tabindex="0"
                     class="mt-2 pt-2.5 border-t border-slate-100 cursor-pointer hover:bg-rose-50/40 -mx-3.5 px-3.5 pb-1 transition-colors">
                    @include('online.partials.order-timeline-stepper', ['variant' => 'strip', 'order' => $activeOrder])
                </div>
            @endif
        </header>

        <!-- ================================================================= -->
        <!-- 3. CHAT MESSAGE THREAD FEED                                       -->
        <!-- ================================================================= -->
        <main id="chat-messages-container" class="flex-1 overflow-y-auto p-3.5 space-y-3.5 hide-scrollbar">
            <!-- Date Divider -->
            <div class="text-center my-2">
                <span class="bg-slate-200 text-slate-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                    วันนี้ • วันที่ {{ date('d/m/Y') }}
                </span>
            </div>

            <!-- Messages List -->
            <div id="messages-list" class="space-y-3">
                @foreach($messages as $msg)
                    @if($msg->sender === 'shop')
                        <!-- Shop Incoming Message Bubble (Left) -->
                        <div class="flex items-start gap-2 max-w-[85%]" data-msg-id="{{ $msg->id }}">
                            <img src="{{ asset('assets/brand/logo-card.png') }}"
                                 alt="Shop"
                                 class="w-7 h-7 rounded-full object-cover shrink-0 mt-0.5 border border-rose-200">
                            <div class="relative bg-white text-slate-800 rounded-2xl rounded-tl-xs px-3.5 py-2 shadow-2xs border border-slate-200/80 bubble-tail-left text-[12.5px] leading-relaxed">
                                <div class="text-[10px] font-bold text-rose-600 mb-0.5">หม่าล่าสตรีท (ร้านค้า)</div>
                                <div class="whitespace-pre-line">{!! nl2br(e($msg->message)) !!}</div>
                                @if($msg->image_url)
                                    <div class="mt-2 rounded-xl overflow-hidden border border-slate-200">
                                        <img src="{{ $msg->image_url }}" alt="ภาพแนบ" class="w-full max-h-48 object-cover cursor-pointer" onclick="viewFullImage('{{ $msg->image_url }}')">
                                    </div>
                                @endif
                                <div class="text-[9.5px] text-slate-400 text-right mt-1 font-mono">
                                    {{ $msg->created_at->format('H:i') }} น.
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Customer Outgoing Message Bubble (Right) -->
                        <div class="flex items-end justify-end gap-1.5 ml-auto max-w-[85%]" data-msg-id="{{ $msg->id }}">
                            <div class="relative bg-[#D90012] text-white rounded-2xl rounded-tr-xs px-3.5 py-2 shadow-2xs bubble-tail-right text-[12.5px] leading-relaxed">
                                <div class="whitespace-pre-line">{!! nl2br(e($msg->message)) !!}</div>
                                @if($msg->image_url)
                                    <div class="mt-2 rounded-xl overflow-hidden border border-white/30 bg-black/20">
                                        <img src="{{ $msg->image_url }}" alt="ภาพแนบ" class="w-full max-h-48 object-cover cursor-pointer" onclick="viewFullImage('{{ $msg->image_url }}')">
                                    </div>
                                @endif
                                <div class="text-[9.5px] text-white/70 text-right mt-1 font-mono flex items-center justify-end gap-1">
                                    <span>{{ $msg->created_at->format('H:i') }} น.</span>
                                    <span>✓✓</span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Dynamic Bottom Anchor for Auto-scroll -->
            <div id="chat-bottom-anchor"></div>
        </main>

        <!-- ================================================================= -->
        <!-- 4. QUICK SUGGESTION CHIPS (Shopee Style Tap-to-Ask)               -->
        <!-- ================================================================= -->
        <div class="px-3.5 py-1.5 bg-slate-100/90 border-t border-slate-200/80 overflow-x-auto whitespace-nowrap hide-scrollbar flex items-center gap-1.5">
            <span class="text-[10px] text-slate-400 font-bold shrink-0">คำถามด่วน:</span>
            <button type="button" onclick="quickSendQuestion('ออเดอร์ของฉันถึงคิวทำหรือยังครับ?')"
                    class="bg-white hover:bg-slate-50 text-slate-700 text-[10.5px] font-medium px-2.5 py-1 rounded-full border border-slate-200/80 shadow-2xs shrink-0 active:scale-95 transition-all">
                🍢 ออเดอร์ถึงขั้นตอนไหนแล้ว?
            </button>
            <button type="button" onclick="quickSendQuestion('ขอเพิ่มผงพริกหม่าล่าเผ็ดชาเป็นพิเศษให้หน่อยครับ')"
                    class="bg-white hover:bg-slate-50 text-slate-700 text-[10.5px] font-medium px-2.5 py-1 rounded-full border border-slate-200/80 shadow-2xs shrink-0 active:scale-95 transition-all">
                🌶️ ขอเพิ่มพริกหม่าล่าพิเศษ
            </button>
            <button type="button" onclick="quickSendQuestion('ขอแยกน้ำซุปหม่าล่า และแยกผงพริกโรยครับ')"
                    class="bg-white hover:bg-slate-50 text-slate-700 text-[10.5px] font-medium px-2.5 py-1 rounded-full border border-slate-200/80 shadow-2xs shrink-0 active:scale-95 transition-all">
                🥡 ขอแยกน้ำซุป / ผงพริก
            </button>
            <button type="button" onclick="quickSendQuestion('ร้านอยู่พิกัดไหน มีที่จอดรถไหมครับ?')"
                    class="bg-white hover:bg-slate-50 text-slate-700 text-[10.5px] font-medium px-2.5 py-1 rounded-full border border-slate-200/80 shadow-2xs shrink-0 active:scale-95 transition-all">
                📍 พิกัดร้าน & ที่จอดรถ
            </button>
        </div>

        <!-- ================================================================= -->
        <!-- 5. COMPRESSED IMAGE PREVIEW ROW (Before Sending)                  -->
        <!-- ================================================================= -->
        <div id="image-preview-container" class="hidden px-3.5 py-2 bg-amber-50 border-t border-amber-200 items-center justify-between">
            <div class="flex items-center gap-2">
                <img id="image-preview-thumb" src="" alt="พรีวิว" class="w-12 h-12 object-cover rounded-lg border border-amber-300 shadow-xs">
                <div>
                    <span class="text-[11px] font-bold text-amber-950 block">บีบอัดรูปและแปลงเป็น URL เรียบร้อย</span>
                    <span id="image-preview-url" class="text-[9px] text-amber-800 font-mono block truncate max-w-[200px]"></span>
                </div>
            </div>
            <button type="button" onclick="cancelImageAttachment()" class="w-6 h-6 rounded-full bg-slate-200 hover:bg-rose-100 hover:text-rose-600 text-slate-600 flex items-center justify-center text-xs font-bold">
                ✕
            </button>
        </div>

        <!-- ================================================================= -->
        <!-- 6. BOTTOM INPUT TOOLBAR (Camera + Canvas Compressor + Text Input)  -->
        <!-- ================================================================= -->
        <footer class="bg-white border-t border-slate-200 px-3 py-2 shadow-lg z-30">
            <!-- Hidden File Input for Image Upload -->
            <input type="file" id="chat-file-input" accept="image/*" class="hidden" onchange="handleChatImageSelection(event)">

            <form id="chat-send-form" onsubmit="handleChatSubmit(event)" class="flex items-center gap-2">
                <!-- Camera / Photo Attachment Button -->
                <button type="button"
                        onclick="document.getElementById('chat-file-input').click()"
                        class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-all cursor-pointer active:scale-95 shrink-0"
                        title="ถ่ายภาพหรือแนบรูปภาพ (ระบบจะบีบอัดและแปลงเป็น URL)">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>

                <!-- Input Text Field -->
                <div class="flex-1 relative">
                    <input type="text"
                           id="chat-message-input"
                           placeholder="พิมพ์ข้อความสอบถามทางร้าน..."
                           autocomplete="off"
                           class="w-full bg-slate-100 focus:bg-white text-slate-900 text-[12.5px] rounded-full pl-3.5 pr-8 py-2 border border-slate-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 outline-none transition-all">
                </div>

                <!-- Send Button -->
                <button type="submit"
                        id="chat-send-btn"
                        class="w-9 h-9 rounded-full bg-[#D90012] hover:bg-[#B8000F] active:scale-95 text-white flex items-center justify-center shadow-md shadow-rose-900/30 transition-all cursor-pointer shrink-0">
                    <svg class="w-4 h-4 ml-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </button>
            </form>
        </footer>

    </div>

    <!-- ===================================================================== -->
    <!-- MODAL 1: VOICE / VIDEO CALL MOCK MODAL                                -->
    <!-- ===================================================================== -->
    <div id="call-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-6 max-w-xs w-full text-center shadow-2xl relative animate-in fade-in zoom-in-95 duration-150">
            <button type="button" onclick="closeCallModal()" class="absolute top-3.5 right-3.5 text-slate-400 hover:text-slate-600 text-lg cursor-pointer">✕</button>
            
            <div class="w-16 h-16 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3 shadow-inner">
                <span id="call-modal-icon" class="text-3xl">📞</span>
            </div>

            <h3 id="call-modal-title" class="text-base font-extrabold text-slate-900">ระบบสนทนาเสียง</h3>
            <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                ระบบโทรและวิดีโอคอลผ่านแอปพลิเคชันกำลังอยู่ระหว่างการพัฒนา จะพร้อมให้บริการในเวอร์ชันถัดไปครับ
            </p>

            <div class="mt-4 p-3 bg-slate-50 rounded-2xl border border-slate-200 text-left text-xs space-y-1">
                <div class="font-bold text-slate-800">ติดต่อด่วนผ่านเบอร์โทรร้านค้า:</div>
                <div class="text-rose-600 font-extrabold text-sm flex items-center gap-1.5">
                    <span>☎️ 089-999-8888</span>
                </div>
                <div class="text-[10px] text-slate-400">เปิดให้บริการ 16:00 - 23:00 น. ทุกวัน</div>
            </div>

            <div class="mt-4 flex gap-2">
                <button type="button" onclick="closeCallModal()" class="flex-1 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs">
                    ปิดหน้าต่าง
                </button>
                <a href="tel:0899998888" class="flex-1 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-1 shadow-md shadow-emerald-700/30">
                    โทรทันที
                </a>
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL 2: ORDER SELECTOR DRAWER                                        -->
    <!-- ===================================================================== -->
    <div id="order-selector-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden items-end justify-center">
        <div class="bg-white rounded-t-3xl p-5 max-w-[430px] w-full max-h-[80vh] overflow-y-auto shadow-2xl relative animate-in slide-in-from-bottom duration-200">
            <div class="w-10 h-1 bg-slate-300 rounded-full mx-auto mb-3"></div>
            
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-extrabold text-slate-900">เลือกออเดอร์เพื่อเชื่อมต่อกับแชท</h3>
                <button type="button" onclick="closeOrderSelectorModal()" class="text-slate-400 hover:text-slate-600 text-sm">✕</button>
            </div>

            <!-- Recent Orders List -->
            <div class="space-y-2">
                @forelse($recentOrders as $ro)
                    <div onclick="selectOrderForChat('{{ $ro->order_no }}')"
                         class="p-3 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-2 {{ ($activeOrder && $activeOrder->id === $ro->id) ? 'bg-rose-50 border-rose-300 text-rose-950' : 'bg-slate-50 hover:bg-slate-100 border-slate-200 text-slate-800' }}">
                        <div>
                            <div class="flex items-center gap-1.5 font-bold text-xs">
                                <span class="bg-slate-900 text-white px-1.5 py-0.2 rounded text-[10px]">คิว {{ $ro->queue_number }}</span>
                                <span>{{ $ro->order_no }}</span>
                            </div>
                            <div class="text-[10px] text-slate-500 mt-0.5">
                                ผู้สั่ง: {{ $ro->customer_name ?: 'ลูกค้า' }} • ฿{{ number_format($ro->total_amount, 0) }} • {{ $ro->created_at->format('d/m H:i') }} น.
                            </div>
                        </div>
                        <div class="text-[10px] font-bold text-emerald-600 shrink-0">
                            {{ $ro->status }} →
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-xs text-slate-400">
                        ยังไม่มีประวัติออเดอร์ในระบบ
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL 3: FULLSCREEN IMAGE VIEWER MODAL                                -->
    <!-- ===================================================================== -->
    <div id="image-viewer-modal" class="fixed inset-0 z-50 bg-black/90 hidden items-center justify-center p-4" onclick="closeImageViewer()">
        <img id="image-viewer-img" src="" alt="รูปภาพขนาดเต็ม" class="max-w-full max-h-[85vh] rounded-2xl object-contain shadow-2xl">
        <button type="button" class="absolute top-4 right-4 text-white text-2xl font-bold bg-black/50 w-10 h-10 rounded-full flex items-center justify-center">✕</button>
    </div>

    @if($activeOrder)
        <!-- ===================================================================== -->
        <!-- MODAL 4: FULL ORDER TRACKING DETAIL SHEET (RED THEME)                 -->
        <!-- ===================================================================== -->
        <div id="order-detail-sheet" class="fixed inset-0 z-50 hidden justify-center bg-black/60 backdrop-blur-xs">
            <div class="w-full {{ request()->has('hub') ? 'max-w-none' : 'max-w-[430px]' }} h-full bg-white flex flex-col overflow-y-auto hide-scrollbar relative shadow-2xl">
                <!-- Top Red-Themed Sky & Cityscape Mascot Banner -->
                <div class="relative h-44 bg-gradient-to-b from-[#FFE4E6] via-[#FFF1F2] to-[#FECDD3] overflow-hidden shrink-0">
                    <!-- Soft Clouds -->
                    <div class="absolute top-5 left-12 w-12 h-4 bg-white/80 rounded-full blur-[0.5px]"></div>
                    <div class="absolute top-9 right-10 w-16 h-5 bg-white/80 rounded-full blur-[0.5px]"></div>

                    <!-- Stylized Rose City Skyline -->
                    <svg class="absolute bottom-2 inset-x-0 w-full h-20 text-[#FDA4AF]/45" preserveAspectRatio="none" viewBox="0 0 400 80" fill="currentColor">
                        <rect x="10" y="25" width="32" height="55" rx="2"/>
                        <rect x="48" y="38" width="24" height="42" rx="2"/>
                        <rect x="78" y="16" width="36" height="64" rx="2"/>
                        <rect x="120" y="34" width="26" height="46" rx="2"/>
                        <rect x="255" y="28" width="30" height="52" rx="2"/>
                        <rect x="292" y="14" width="38" height="66" rx="2"/>
                        <rect x="336" y="36" width="24" height="44" rx="2"/>
                        <rect x="366" y="24" width="28" height="56" rx="2"/>
                    </svg>

                    <!-- Top Bar Controls -->
                    <div class="relative z-20 flex items-center justify-between px-4 pt-4">
                        <button type="button"
                                onclick="closeOrderDetailSheet()"
                                aria-label="ปิดรายละเอียดออเดอร์"
                                class="w-9 h-9 rounded-full bg-white hover:bg-slate-50 text-slate-700 shadow-md flex items-center justify-center transition-all cursor-pointer active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>

                        <span class="px-3.5 py-1.5 rounded-full bg-white text-[#D90012] font-extrabold text-[11.5px] shadow-md border border-rose-100">
                            คิว {{ $activeOrder->queue_number }}
                        </span>
                    </div>

                    <!-- Centered Mala Street Red Mascot Character SVG -->
                    <div class="relative z-10 flex justify-center -mt-3">
                        <svg class="w-28 h-28 drop-shadow-md" viewBox="0 0 120 120" fill="none">
                            <!-- Ground shadow -->
                            <ellipse cx="60" cy="110" rx="26" ry="5" fill="#BE123C" fill-opacity="0.18"/>
                            <!-- Legs -->
                            <rect x="47" y="84" width="9" height="22" rx="4" fill="#1F2937"/>
                            <rect x="64" y="84" width="9" height="22" rx="4" fill="#1F2937"/>
                            <ellipse cx="50" cy="107" rx="7" ry="4" fill="#111827"/>
                            <ellipse cx="70" cy="107" rx="7" ry="4" fill="#111827"/>
                            <!-- Jacket Body (Mala Street Red) -->
                            <path d="M40 58C40 51 48 48 60 48C72 48 80 51 80 58L82 86C82 89 79 91 75 91H45C41 91 38 89 38 86L40 58Z" fill="#D90012"/>
                            <path d="M60 48V91" stroke="#B8000F" stroke-width="2"/>
                            <!-- White Apron / Center Stripe -->
                            <rect x="52" y="53" width="16" height="33" rx="4" fill="#FFFFFF"/>
                            <text x="60" y="73" text-anchor="middle" font-size="11" font-weight="900" fill="#D90012" font-family="sans-serif">M</text>
                            <!-- Arms -->
                            <path d="M40 56L26 70" stroke="#D90012" stroke-width="9" stroke-linecap="round"/>
                            <path d="M80 56L94 68" stroke="#D90012" stroke-width="9" stroke-linecap="round"/>
                            <circle cx="24" cy="72" r="5" fill="#FDE68A"/>
                            <circle cx="96" cy="69" r="5" fill="#FDE68A"/>
                            <!-- Mala Skewer in Hand -->
                            <line x1="96" y1="69" x2="106" y2="44" stroke="#92400E" stroke-width="2.5" stroke-linecap="round"/>
                            <circle cx="101" cy="57" r="4" fill="#EA580C"/>
                            <circle cx="104" cy="50" r="4" fill="#DC2626"/>
                            <circle cx="106" cy="43" r="3.5" fill="#F59E0B"/>
                            <!-- Head / Face -->
                            <circle cx="60" cy="34" r="19" fill="#FFFFFF" stroke="#1E293B" stroke-width="2"/>
                            <!-- Red Delivery Helmet -->
                            <path d="M39 32C39 17 48 10 60 10C72 10 81 17 81 32H39Z" fill="#D90012"/>
                            <rect x="55" y="10" width="10" height="22" fill="#FFFFFF" fill-opacity="0.9"/>
                            <rect x="37" y="30" width="46" height="5" rx="2.5" fill="#991B1B"/>
                            <!-- Cute Determined Face -->
                            <path d="M49 33L55 35" stroke="#1E293B" stroke-width="2" stroke-linecap="round"/>
                            <path d="M71 33L65 35" stroke="#1E293B" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="53" cy="38" r="2.2" fill="#1E293B"/>
                            <circle cx="67" cy="38" r="2.2" fill="#1E293B"/>
                            <ellipse cx="47" cy="41" rx="3" ry="1.8" fill="#FDA4AF"/>
                            <ellipse cx="73" cy="41" rx="3" ry="1.8" fill="#FDA4AF"/>
                            <path d="M56 44C58 46.5 62 46.5 64 44" stroke="#D90012" stroke-width="2.2" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>

                <!-- White Sheet Container -->
                <div class="-mt-4 rounded-t-[24px] bg-white relative z-20 px-5 pt-2.5 pb-8 flex-1 shadow-[0_-6px_24px_rgba(0,0,0,0.06)]">
                    <div class="w-10 h-1 rounded-full bg-slate-200 mx-auto mb-3.5"></div>

                    <!-- Synchronized Red-Themed Live Order Timeline -->
                    @include('online.partials.order-timeline-stepper', ['variant' => 'sheet', 'order' => $activeOrder])

                    <hr class="border-slate-100 my-4">

                    <!-- Store Name -->
                    <div class="text-[15px] font-bold text-slate-900 mb-3">หม่าล่าสตรีท (Mala Street)</div>

                    <!-- Payment & Totals Summary -->
                    <div class="space-y-2.5 text-[13px]">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>ยอดรวม</span>
                            <span class="font-bold text-slate-900 text-[14px]">฿{{ number_format($activeOrder->total_amount, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>คูปอง</span>
                            <span class="{{ ($activeOrder->discount ?? 0) > 0 ? 'font-bold text-[#D90012]' : 'text-slate-700' }}">
                                @if(($activeOrder->discount ?? 0) > 0)
                                    -฿{{ number_format($activeOrder->discount, 2) }}
                                @else
                                    ไม่ได้ใช้คูปอง
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>วิธีชำระเงิน</span>
                            <span class="flex items-center gap-1.5 font-medium text-slate-800">
                                @if(($activeOrder->payment_method ?? 'promptpay') === 'promptpay')
                                    <span class="px-1.5 py-0.5 rounded bg-rose-50 text-[#D90012] border border-rose-200 text-[10px] font-extrabold">PromptPay</span>
                                    <span>โอนจ่าย / QR</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-extrabold">เงินสด</span>
                                    <span>ชำระหน้าร้าน</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <hr class="border-slate-100 my-4">

                    <!-- Order Items List -->
                    <div class="space-y-3">
                        @foreach($activeOrder->items as $idx => $item)
                            @php
                                $cookLabel = $item->cooking_type === 'grill'
                                    ? ($item->grill_style === 'brush' ? 'ปิ้งย่าง (ทาซอสหม่าล่า)' : 'ปิ้งย่าง (จุ่มซอสหม่าล่า)')
                                    : 'ต้มซุปหม่าล่า';
                                $spiceLabel = match($item->spiciness_level) {
                                    'none' => 'ไม่เผ็ด',
                                    'mild' => 'เผ็ดน้อย',
                                    'medium' => 'เผ็ดกลาง',
                                    'hot' => 'เผ็ดมาก',
                                    default => $item->spiciness_level ?: 'เผ็ดกลาง',
                                };
                            @endphp
                            <div class="flex items-start gap-3 {{ $idx >= 2 ? 'hidden extra-order-detail-item' : '' }}">
                                <div class="w-5 h-5 rounded-[4px] border border-slate-300 text-[11px] font-bold text-slate-800 flex items-center justify-center shrink-0 mt-0.5">
                                    {{ $item->quantity_sticks }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[13px] font-medium text-slate-900 leading-snug">{{ $item->product_name }}</div>
                                    <div class="text-[11px] text-slate-400 leading-snug mt-0.5">{{ $cookLabel }} • {{ $spiceLabel }}</div>
                                </div>
                                <div class="text-[12px] font-semibold text-slate-600 shrink-0">
                                    ฿{{ number_format($item->subtotal, 2) }}
                                </div>
                            </div>
                        @endforeach

                        <!-- Expandable Order Breakdown (Shown on Show more toggle) -->
                        <div id="order-detail-extra-summary" class="hidden pt-2 mt-2 border-t border-dashed border-slate-200 space-y-1.5 text-[11.5px] text-slate-500">
                            <div class="flex items-center justify-between">
                                <span>จำนวนทั้งหมด</span>
                                <span class="font-bold text-slate-700">{{ $activeOrder->items->sum('quantity_sticks') }} ไม้ ({{ $activeOrder->items->count() }} รายการ)</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>ค่าซุปหม่าล่าเพิ่มเติม</span>
                                <span class="font-bold text-slate-700">฿{{ number_format($activeOrder->soup_extra ?? 0, 2) }}</span>
                            </div>
                            @if(!empty($activeOrder->notes))
                                <div class="pt-1 text-slate-600">
                                    <span class="font-bold text-slate-700">หมายเหตุถึงครัว:</span> {{ $activeOrder->notes }}
                                </div>
                            @endif
                        </div>

                        <button type="button"
                                id="toggle-more-items-btn"
                                onclick="toggleOrderDetailMoreItems()"
                                class="w-full py-2 text-xs font-bold text-[#D90012] hover:bg-rose-50 rounded-xl flex items-center justify-center gap-1 transition-colors cursor-pointer">
                            <span id="toggle-more-items-label">ดูเพิ่มเติม</span>
                            <svg id="toggle-more-items-icon" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    <hr class="border-slate-100 my-3">

                    <!-- Pickup / Delivery Location Footer -->
                    <div class="flex items-start gap-2.5 pt-1">
                        <svg class="w-4 h-4 text-[#D90012] shrink-0 mt-0.5" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd" d="M12 2.25c-4.142 0-7.5 3.28-7.5 7.326 0 4.92 6.082 10.973 7.015 11.87a.7.7 0 00.97 0C13.418 20.55 19.5 14.497 19.5 9.576 19.5 5.53 16.142 2.25 12 2.25zm0 9.826a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" clip-rule="evenodd"/>
                        </svg>
                        <div class="min-w-0">
                            <div class="text-[13px] font-bold text-slate-900">รับอาหารหน้าร้าน • หม่าล่าสตรีท</div>
                            <div class="text-[11.5px] text-slate-500 mt-0.5 leading-relaxed">
                                {{ $activeOrder->customer_name ?: 'ลูกค้าออนไลน์' }}{{ $activeOrder->customer_phone ? ' (' . $activeOrder->customer_phone . ')' : '' }}
                                • เวลานัดรับ: {{ $activeOrder->pickup_time ?: 'ตามคิวหน้าร้าน' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- JAVASCRIPT LOGIC -->
    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const ACTIVE_ORDER_NO = "{{ $activeOrder ? $activeOrder->order_no : '' }}";
        const ACTIVE_ORDER_QUEUE = "{{ $activeOrder ? $activeOrder->queue_number : '' }}";
        const ACTIVE_ORDER_STATUS_URL = @json($activeOrder ? route('online.status', ['order_no' => $activeOrder->order_no]) : '');
        let ACTIVE_ORDER_STATUS = "{{ $activeOrder ? $activeOrder->status : '' }}";
        const CUSTOMER_NAME = "{{ $activeOrder ? $activeOrder->customer_name : 'ลูกค้า' }}";
        const CUSTOMER_PHONE = "{{ $phone }}";

        let currentAttachedImageUrl = null;

        const renderedMessageIds = new Set();

        // Auto Scroll to Chat Bottom & Init Live Poll for Admin Replies
        function scrollToBottom() {
            const container = document.getElementById('chat-messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
        window.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('#messages-list [data-msg-id]').forEach(el => {
                const id = Number(el.getAttribute('data-msg-id'));
                if (id) renderedMessageIds.add(id);
            });
            scrollToBottom();
            setInterval(pollCustomerChatMessages, 3000);
            if (ACTIVE_ORDER_STATUS_URL) {
                setInterval(pollActiveOrderStatus, 4000);
            }
        });

        // 1. Send Order Card into Chat
        function sendOrderCardToChat(orderNo, queueNo, total, count, status) {
            const messageText = `🍢 ข้อมูลออเดอร์ที่เชื่อมต่อ:\n• หมายเลขคิว: [${queueNo}]\n• รหัสคำสั่งซื้อ: ${orderNo}\n• จำนวน: ${count} ไม้\n• ยอดชำระ: ฿${total}\n(ต้องการสอบถามสถานะความคืบหน้าของออเดอร์นี้ครับ)`;
            submitChatMessage(messageText, null);
        }

        // 2. Quick Question Button
        function quickSendQuestion(text) {
            submitChatMessage(text, null);
        }

        // 3. Compress Image using HTML5 Canvas & Upload to Server URL
        async function handleChatImageSelection(event) {
            const file = event.target.files[0];
            if (!file) return;

            // Show compressing status toast
            const previewContainer = document.getElementById('image-preview-container');
            const previewUrl = document.getElementById('image-preview-url');
            const previewThumb = document.getElementById('image-preview-thumb');

            previewUrl.innerText = 'กำลังบีบอัดรูปภาพด้วย Canvas...';
            previewContainer.classList.remove('hidden');
            previewContainer.classList.add('flex');

            try {
                // Client-side Canvas Image Compression
                const compressedBase64 = await compressImageFile(file, 1200, 1200, 0.82);
                previewThumb.src = compressedBase64;
                previewUrl.innerText = 'กำลังอัปโหลดและแปลงเป็น URL...';

                // Send to Server to get permanent URL
                const response = await fetch("{{ route('online.chat.upload_image') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        image_base64: compressedBase64
                    })
                });

                const data = await response.json();
                if (data.success && data.url) {
                    currentAttachedImageUrl = data.url;
                    previewThumb.src = data.url;
                    previewUrl.innerText = data.url;
                } else {
                    alert(data.message || 'ไม่สามารถแปลงรูปเป็น URL ได้');
                    cancelImageAttachment();
                }
            } catch (err) {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการบีบอัดรูป: ' + err.message);
                cancelImageAttachment();
            }
        }

        // Helper Canvas Compression
        function compressImageFile(file, maxWidth, maxHeight, quality) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function (e) {
                    const img = new Image();
                    img.src = e.target.result;
                    img.onload = function () {
                        let w = img.width;
                        let h = img.height;
                        if (w > h) {
                            if (w > maxWidth) {
                                h = Math.round((h * maxWidth) / w);
                                w = maxWidth;
                            }
                        } else {
                            if (h > maxHeight) {
                                w = Math.round((w * maxHeight) / h);
                                h = maxHeight;
                            }
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = w;
                        canvas.height = h;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, w, h);
                        resolve(canvas.toDataURL('image/jpeg', quality));
                    };
                    img.onerror = reject;
                };
                reader.onerror = reject;
            });
        }

        function cancelImageAttachment() {
            currentAttachedImageUrl = null;
            document.getElementById('chat-file-input').value = '';
            const previewContainer = document.getElementById('image-preview-container');
            previewContainer.classList.add('hidden');
            previewContainer.classList.remove('flex');
        }

        // 4. Form Submit
        function handleChatSubmit(event) {
            event.preventDefault();
            const input = document.getElementById('chat-message-input');
            const text = input.value.trim();

            if (!text && !currentAttachedImageUrl) return;

            submitChatMessage(text, currentAttachedImageUrl);
            input.value = '';
            cancelImageAttachment();
        }

        // 5. Submit Message to Server & Render
        async function submitChatMessage(text, imageUrl) {
            // Render optimistic customer bubble
            appendCustomerBubble(text, imageUrl);
            scrollToBottom();

            try {
                const response = await fetch("{{ route('online.chat.send') }}", {
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
                        customer_phone: CUSTOMER_PHONE
                    })
                });

                const data = await response.json();
                if (data.success && data.message && data.message.id) {
                    renderedMessageIds.add(Number(data.message.id));
                }
                if (data.success && data.bot_reply) {
                    if (data.bot_reply.id) renderedMessageIds.add(Number(data.bot_reply.id));
                    // Slight delay for realistic chat typing experience
                    setTimeout(() => {
                        appendShopBubble(data.bot_reply.message, data.bot_reply.image_url || null, data.bot_reply.id || null);
                        scrollToBottom();
                    }, 650);
                }
            } catch (err) {
                console.error('Chat error:', err);
            }
        }

        async function pollCustomerChatMessages() {
            try {
                const url = `{{ route('online.chat.messages') }}?order_no=${encodeURIComponent(ACTIVE_ORDER_NO)}&phone=${encodeURIComponent(CUSTOMER_PHONE)}`;
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await response.json();
                if (data.success && Array.isArray(data.messages)) {
                    let appended = false;
                    data.messages.forEach(m => {
                        const msgId = Number(m.id);
                        if (msgId && !renderedMessageIds.has(msgId)) {
                            renderedMessageIds.add(msgId);
                            if (m.sender === 'shop') {
                                appendShopBubble(m.message || '', m.image_url || null, msgId);
                                appended = true;
                            }
                        }
                    });
                    if (appended) {
                        scrollToBottom();
                    }
                }
            } catch (err) {}
        }

        function appendCustomerBubble(text, imageUrl) {
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
                        <div class="whitespace-pre-line">${escapeHtml(text)}</div>
                        ${imgHtml}
                        <div class="text-[9.5px] text-white/70 text-right mt-1 font-mono flex items-center justify-end gap-1">
                            <span>${timeStr}</span>
                            <span>✓✓</span>
                        </div>
                    </div>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', html);
        }

        function appendShopBubble(text, imageUrl = null, msgId = null) {
            const list = document.getElementById('messages-list');
            const now = new Date();
            const timeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} น.`;
            const idAttr = msgId ? ` data-msg-id="${msgId}"` : '';
            const imgHtml = imageUrl
                ? `<div class="mt-2 rounded-xl overflow-hidden border border-slate-200"><img src="${imageUrl}" alt="ภาพแนบ" class="w-full max-h-48 object-cover cursor-pointer" onclick="viewFullImage('${imageUrl}')"></div>`
                : '';

            const html = `
                <div class="flex items-start gap-2 max-w-[85%] animate-in fade-in slide-in-from-bottom-2 duration-150"${idAttr}>
                    <img src="{{ asset('assets/brand/logo-card.png') }}"
                         alt="Shop"
                         class="w-7 h-7 rounded-full object-cover shrink-0 mt-0.5 border border-rose-200">
                    <div class="relative bg-white text-slate-800 rounded-2xl rounded-tl-xs px-3.5 py-2 shadow-2xs border border-slate-200/80 bubble-tail-left text-[12.5px] leading-relaxed">
                        <div class="text-[10px] font-bold text-rose-600 mb-0.5">หม่าล่าสตรีท (ร้านค้า)</div>
                        <div class="whitespace-pre-line">${escapeHtml(text)}</div>
                        ${imgHtml}
                        <div class="text-[9.5px] text-slate-400 text-right mt-1 font-mono">
                            ${timeStr}
                        </div>
                    </div>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', html);
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>"']/g, function (m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
            });
        }

        // Modals
        function openCallModal(type) {
            const modal = document.getElementById('call-modal');
            const icon = document.getElementById('call-modal-icon');
            const title = document.getElementById('call-modal-title');
            if (type === 'video') {
                icon.innerText = '📹';
                title.innerText = 'ระบบวิดีโอคอลหน้าร้าน';
            } else {
                icon.innerText = '📞';
                title.innerText = 'ระบบสนทนาเสียงผ่านแอป';
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeCallModal() {
            const modal = document.getElementById('call-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
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

        function selectOrderForChat(orderNo) {
            window.location.href = "{{ route('online.chat') }}?order_no=" + encodeURIComponent(orderNo);
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

        function openOrderDetailSheet() {
            const sheet = document.getElementById('order-detail-sheet');
            if (!sheet) return;
            sheet.classList.remove('hidden');
            sheet.classList.add('flex');
        }

        function closeOrderDetailSheet() {
            const sheet = document.getElementById('order-detail-sheet');
            if (!sheet) return;
            sheet.classList.add('hidden');
            sheet.classList.remove('flex');
        }

        function toggleOrderDetailMoreItems() {
            const extraItems = document.querySelectorAll('.extra-order-detail-item');
            const extraSummary = document.getElementById('order-detail-extra-summary');
            const label = document.getElementById('toggle-more-items-label');
            const icon = document.getElementById('toggle-more-items-icon');
            const isExpanded = extraSummary && !extraSummary.classList.contains('hidden');

            extraItems.forEach((el) => el.classList.toggle('hidden', isExpanded));
            if (extraSummary) extraSummary.classList.toggle('hidden', isExpanded);
            if (label) label.textContent = isExpanded ? 'ดูเพิ่มเติม' : 'แสดงน้อยลง';
            if (icon) icon.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(180deg)';
        }

        function updateOrderTimelineUI(status) {
            const stageMap = { pending: 1, confirmed: 1, cooking: 2, ready: 3, completed: 4, cancelled: 0 };
            const stage = stageMap[status] ?? 1;

            const titleMap = {
                pending: 'รอร้านค้ายืนยันออเดอร์',
                confirmed: 'ร้านรับออเดอร์แล้ว',
                cooking: 'กำลังปิ้งย่างในครัว',
                ready: 'อาหารเสร็จแล้ว พร้อมรับ!',
                completed: 'ส่งมอบอาหารเรียบร้อย',
                cancelled: 'ยกเลิกออเดอร์แล้ว'
            };
            const subtitleMap = {
                pending: 'กำลังดำเนินการ...',
                confirmed: 'กำลังเตรียมวัตถุดิบและคิวเตาย่าง...',
                cooking: 'กำลังย่างเตาถ่านและปรุงตามสั่ง...',
                ready: `แจ้งคิว ${ACTIVE_ORDER_QUEUE} รับอาหารที่หน้าร้านได้เลย`,
                completed: 'ขอบพระคุณที่อุดหนุนหม่าล่าสตรีท',
                cancelled: 'กรุณาสอบถามแอดมินในแชท'
            };
            const syncLabelMap = {
                pending: 'รอยืนยัน',
                confirmed: 'รับออเดอร์แล้ว',
                cooking: '🔥 กำลังย่างในครัว',
                ready: '🎉 พร้อมรับหน้าร้าน',
                completed: '✅ จัดส่งสำเร็จ',
                cancelled: '❌ ยกเลิกแล้ว'
            };

            const syncEl = document.getElementById('order-sync-status-label');
            if (syncEl) syncEl.textContent = syncLabelMap[status] || status;

            document.querySelectorAll('[data-order-timeline]').forEach((root) => {
                const titleEl = root.querySelector('[data-tl-title]');
                const subEl = root.querySelector('[data-tl-subtitle]');
                if (titleEl) {
                    titleEl.textContent = titleMap[status] || 'กำลังดำเนินการ';
                    titleEl.classList.toggle('text-[#D90012]', status !== 'cancelled');
                    titleEl.classList.toggle('text-slate-500', status === 'cancelled');
                }
                if (subEl) subEl.textContent = subtitleMap[status] || 'กำลังดำเนินการ...';

                const step1 = root.querySelector('[data-tl-step="1"]');
                if (step1) {
                    step1.classList.toggle('bg-[#D90012]', stage >= 1);
                    step1.classList.toggle('text-white', stage >= 1);
                    step1.classList.toggle('bg-slate-200', stage < 1);
                    step1.classList.toggle('text-slate-400', stage < 1);
                }
                [2, 3, 4].forEach((n) => {
                    const stepEl = root.querySelector(`[data-tl-step="${n}"]`);
                    const lineEl = root.querySelector(`[data-tl-line="${n}"]`);
                    if (stepEl) {
                        stepEl.classList.toggle('text-[#D90012]', stage >= n);
                        stepEl.classList.toggle('text-slate-300', stage < n);
                    }
                    if (lineEl) {
                        lineEl.classList.toggle('bg-[#D90012]', stage >= n);
                        lineEl.classList.toggle('bg-slate-200', stage < n);
                    }
                });
            });
        }

        async function pollActiveOrderStatus() {
            if (!ACTIVE_ORDER_STATUS_URL) return;
            try {
                const res = await fetch(ACTIVE_ORDER_STATUS_URL, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                if (data.status && data.status !== ACTIVE_ORDER_STATUS) {
                    ACTIVE_ORDER_STATUS = data.status;
                    updateOrderTimelineUI(data.status);
                }
            } catch (err) {}
        }
    </script>
</body>
</html>

