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
<body class="min-h-screen bg-[#111317] flex justify-center items-start selection:bg-[#E60018] selection:text-white">

    <!-- MOBILE CONTAINER (400 - 430px Frame Layout) -->
    <div class="w-full max-w-[430px] min-h-screen bg-[#F6F7F9] shadow-[0_0_60px_rgba(0,0,0,0.6)] flex flex-col justify-between relative overflow-hidden">

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
                            <span>เปิดรับออเดอร์ • ตอบกลับทันที</span>
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
                                ({{ match($activeOrder->status) {
                                    'pending' => 'รอยืนยัน',
                                    'confirmed' => 'รับออเดอร์แล้ว',
                                    'cooking' => '🔥 กำลังย่างในครัว',
                                    'ready' => '🎉 พร้อมรับหน้าร้าน',
                                    'completed' => '✅ จัดส่งสำเร็จ',
                                    default => $activeOrder->status
                                } }})
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

                        <!-- Live Track Link -->
                        <a href="{{ route('online.track', ['order_no' => $activeOrder->order_no]) }}"
                           class="bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-[10px] font-bold px-2 py-1 rounded-lg transition-all flex items-center gap-0.5 shadow-2xs">
                            <span>สถานะ</span>
                        </a>

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
                        <div class="flex items-start gap-2 max-w-[85%]">
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
                        <div class="flex items-end justify-end gap-1.5 ml-auto max-w-[85%]">
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

    <!-- JAVASCRIPT LOGIC -->
    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const ACTIVE_ORDER_NO = "{{ $activeOrder ? $activeOrder->order_no : '' }}";
        const CUSTOMER_NAME = "{{ $activeOrder ? $activeOrder->customer_name : 'ลูกค้า' }}";
        const CUSTOMER_PHONE = "{{ $phone }}";

        let currentAttachedImageUrl = null;

        // Auto Scroll to Chat Bottom
        function scrollToBottom() {
            const container = document.getElementById('chat-messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
        window.addEventListener('DOMContentLoaded', scrollToBottom);

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
                if (data.success && data.bot_reply) {
                    // Slight delay for realistic chat typing experience
                    setTimeout(() => {
                        appendShopBubble(data.bot_reply.message);
                        scrollToBottom();
                    }, 650);
                }
            } catch (err) {
                console.error('Chat error:', err);
            }
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

        function appendShopBubble(text) {
            const list = document.getElementById('messages-list');
            const now = new Date();
            const timeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} น.`;

            const html = `
                <div class="flex items-start gap-2 max-w-[85%] animate-in fade-in slide-in-from-bottom-2 duration-150">
                    <img src="{{ asset('assets/brand/logo-card.png') }}"
                         alt="Shop"
                         class="w-7 h-7 rounded-full object-cover shrink-0 mt-0.5 border border-rose-200">
                    <div class="relative bg-white text-slate-800 rounded-2xl rounded-tl-xs px-3.5 py-2 shadow-2xs border border-slate-200/80 bubble-tail-left text-[12.5px] leading-relaxed">
                        <div class="text-[10px] font-bold text-rose-600 mb-0.5">หม่าล่าสตรีท (ร้านค้า)</div>
                        <div class="whitespace-pre-line">${escapeHtml(text)}</div>
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
    </script>
</body>
</html>

