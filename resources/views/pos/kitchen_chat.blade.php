<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>แชทลูกค้า (แอดมินครัว) - หม่าล่าสตรีท</title>

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

        <!-- ========================================================================= -->
        <!-- VIEW 1: CHAT INBOX LIST (หน้าต่างแรก แสดงเป็นแชทเลย เหมือน LINE / Messenger) -->
        <!-- ========================================================================= -->
        <div id="view-chat-inbox" class="{{ !empty($orderNo) && $activeOrder ? 'hidden' : 'flex' }} flex-col flex-1 h-full min-h-0">

            <!-- 1.1 TOP BAR (MESSENGER / LINE STYLE HEADER) -->
            <header class="bg-slate-950 text-white px-3.5 pt-3 pb-2.5 shadow-md border-b border-slate-800 shrink-0">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="relative shrink-0">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-rose-600 to-amber-500 flex items-center justify-center text-white font-bold text-sm shadow-xs border border-rose-400/40">
                                💬
                            </div>
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-400 border-2 border-slate-950 animate-pulse"></span>
                        </div>
                        <div class="min-w-0 leading-tight">
                            <div class="flex items-center gap-1.5">
                                <h1 class="text-sm font-extrabold text-white truncate">กล่องข้อความลูกค้า</h1>
                                <span class="text-[9px] bg-rose-900/90 text-rose-200 border border-rose-700/60 font-extrabold px-1.5 py-0.2 rounded-full shrink-0">สด</span>
                            </div>
                            <p class="text-[10.5px] text-slate-400 truncate mt-0.5">จับคู่เลขคิว + ชื่อ + เลขไอพีเครื่อง</p>
                        </div>
                    </div>

                    <!-- Right Controls: Popout & Refresh -->
                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button"
                                onclick="window.location.reload()"
                                class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center transition-all cursor-pointer active:scale-95"
                                title="รีเฟรชรายการแชท">
                            <svg class="w-3.5 h-3.5 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                        <a href="{{ route('kitchen.chat') }}" target="_blank"
                           class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 flex items-center justify-center transition-all active:scale-95"
                           title="เปิดเต็มหน้าต่างใหม่">
                            <svg class="w-3.5 h-3.5 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- 1.2 SEARCH INPUT -->
                <div class="mt-2.5 relative">
                    <input type="text"
                           id="inbox-search-input"
                           oninput="filterInboxItems(this.value)"
                           placeholder="🔍 ค้นหาชื่อลูกค้า, เลขคิว, หรือ IP..."
                           class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-rose-500 focus:border-rose-500 transition font-prompt">
                </div>

                <!-- 1.3 TOP BAR TABS: ONLINE vs OFFLINE (ตามคำสั่ง) -->
                <div class="mt-2.5 flex items-center gap-1.5 p-1 bg-slate-900/90 rounded-2xl border border-slate-800 text-xs font-bold">
                    <!-- Tab: Online -->
                    <button type="button"
                            onclick="setInboxFilter('online')"
                            id="tab-btn-online"
                            class="flex-1 py-1.5 px-2 rounded-xl text-center transition-all flex items-center justify-center gap-1.5 bg-rose-600 text-white shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Online</span>
                        <span class="text-[10px] bg-white/20 px-1.5 py-0.2 rounded-full font-mono">{{ $onlineOrders->count() }}</span>
                    </button>

                    <!-- Tab: Offline -->
                    <button type="button"
                            onclick="setInboxFilter('offline')"
                            id="tab-btn-offline"
                            class="flex-1 py-1.5 px-2 rounded-xl text-center transition-all flex items-center justify-center gap-1.5 text-slate-400 hover:text-white hover:bg-slate-800">
                        <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                        <span>Offline</span>
                        <span class="text-[10px] bg-slate-800 px-1.5 py-0.2 rounded-full font-mono text-slate-400">{{ $offlineOrders->count() }}</span>
                    </button>

                    <!-- Tab: All -->
                    <button type="button"
                            onclick="setInboxFilter('all')"
                            id="tab-btn-all"
                            class="py-1.5 px-2.5 rounded-xl text-center transition-all text-slate-400 hover:text-white hover:bg-slate-800 text-[11px]">
                        <span>ทั้งหมด ({{ $recentOrders->count() }})</span>
                    </button>
                </div>
            </header>

            <!-- 1.4 INBOX CHAT LIST (เหมือน LINE / Messenger เรียงลงมา) -->
            <main class="flex-1 overflow-y-auto divide-y divide-slate-100 bg-white hide-scrollbar" id="inbox-chats-container">
                @forelse($recentOrders as $ord)
                    @php
                        $ordIp = $ord->ip_address ?: '127.0.0.1';
                        $isOnline = $ord->is_online;
                        $timeStr = $ord->last_message_time ? $ord->last_message_time->timezone('Asia/Bangkok')->format('H:i') . ' น.' : '';
                        $preview = $ord->last_message ?: 'สั่งอาหาร ' . ($ord->items->count() > 0 ? $ord->items->first()->product_name : 'หม่าล่า') . '...';
                        $isSenderShop = $ord->last_message_sender === 'shop';
                    @endphp
                    <div class="inbox-item-row p-3 hover:bg-slate-50 transition-all cursor-pointer flex items-center gap-3 active:bg-slate-100/90 select-none {{ $ord->unread_count > 0 ? 'bg-rose-50/40' : '' }}"
                         data-online="{{ $isOnline ? '1' : '0' }}"
                         data-search="{{ strtolower($ord->customer_name . ' ' . $ord->queue_number . ' ' . $ord->order_no . ' ' . $ordIp) }}"
                         onclick="openChatRoom('{{ $ord->order_no }}')">
                        
                        <!-- Avatar with Online/Offline indicator -->
                        <div class="relative shrink-0">
                            <div class="w-11 h-11 rounded-2xl {{ $isOnline ? 'bg-gradient-to-tr from-rose-500 to-amber-500 text-white' : 'bg-slate-200 text-slate-600' }} flex items-center justify-center font-extrabold text-sm shadow-2xs">
                                {{ mb_substr($ord->customer_name ?: 'ลูก', 0, 1) }}
                            </div>
                            @if($isOnline)
                                <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white animate-pulse" title="ออนไลน์"></span>
                            @else
                                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-slate-400 border-2 border-white" title="ออฟไลน์"></span>
                            @endif
                        </div>

                        <!-- Chat Details -->
                        <div class="flex-1 min-w-0">
                            <!-- Line 1: Queue Pill + Customer Name -->
                            <div class="flex items-center justify-between gap-1">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="px-2 py-0.5 rounded-md {{ $isOnline ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-700' }} font-black text-[11px] shrink-0 tracking-tight">
                                        คิว {{ $ord->queue_number }}
                                    </span>
                                    <span class="text-[13px] font-extrabold text-slate-900 truncate">
                                        {{ $ord->customer_name ?: 'ลูกค้าสั่งออนไลน์' }}
                                    </span>
                                </div>
                                <span class="text-[10px] text-slate-400 shrink-0 font-mono">
                                    {{ $timeStr }}
                                </span>
                            </div>

                            <!-- Line 2: IP Address Device Tracker + Total -->
                            <div class="flex items-center justify-between text-[10.5px] mt-0.5">
                                <span class="font-mono text-amber-800 font-semibold truncate flex items-center gap-1">
                                    <span class="text-amber-600">🌐</span>
                                    <span>IP: {{ $ordIp }}</span>
                                    @if($ord->customer_phone)
                                        <span class="text-slate-400 font-normal">({{ $ord->customer_phone }})</span>
                                    @endif
                                </span>
                                <span class="font-bold text-slate-800 font-mono shrink-0">
                                    ฿{{ number_format($ord->total_amount, 0) }}
                                </span>
                            </div>

                            <!-- Line 3: Last Message Preview + Unread Badge -->
                            <div class="flex items-center justify-between gap-2 mt-0.5">
                                <p class="text-[11.5px] text-slate-500 truncate leading-snug">
                                    @if($isSenderShop)
                                        <span class="text-rose-600 font-bold">คุณ: </span>
                                    @endif
                                    <span>{{ $preview }}</span>
                                </p>
                                @if($ord->unread_count > 0)
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-[#D90012] text-white shrink-0 animate-bounce">
                                        {{ $ord->unread_count }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Right Chevron -->
                        <div class="text-slate-300 shrink-0 pl-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16 px-4 text-slate-400">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-xl mb-2">💬</div>
                        <p class="text-xs font-bold text-slate-600">ยังไม่มีรายการแชท</p>
                    </div>
                @endforelse
            </main>

            <!-- 1.5 INBOX FOOTER: STATS BAR -->
            <footer class="p-2.5 bg-slate-50 border-t border-slate-200/90 text-center shrink-0 flex items-center justify-between text-xs text-slate-500">
                <span class="text-[11px] font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>ซิงค์อัตโนมัติ (Live Sync)</span>
                </span>
                <span class="text-[11px] font-bold text-slate-700">
                    🟢 Online {{ $onlineOrders->count() }} • ⚪ Offline {{ $offlineOrders->count() }}
                </span>
            </footer>
        </div>

        <!-- ========================================================================= -->
        <!-- VIEW 2: CHAT ROOM VIEW (หน้าต่างสนทนาของออเดอร์ที่เลือก)                    -->
        <!-- ========================================================================= -->
        <div id="view-chat-room" class="{{ !empty($orderNo) && $activeOrder ? 'flex' : 'hidden' }} flex-col flex-1 h-full min-h-0">

            <!-- 2.1 ROOM TOP HEADER -->
            <header class="sticky top-0 z-30 bg-slate-950 text-white px-3 py-2 shadow-md border-b border-slate-800 shrink-0">
                <div class="flex items-center justify-between gap-2">
                    <!-- Back Button to LINE/Messenger Inbox List -->
                    <div class="flex items-center gap-2 min-w-0">
                        <button type="button"
                                onclick="backToInboxView()"
                                class="py-1 px-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 flex items-center gap-1 transition-all shrink-0 active:scale-95 text-xs font-bold border border-slate-700"
                                title="กลับหน้ารวมแชท (Chat List)">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span>แชท</span>
                        </button>

                        <!-- Customer Avatar & Online Dot -->
                        <div class="relative shrink-0">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-rose-600 to-amber-500 flex items-center justify-center text-white font-bold text-xs shadow-xs">
                                👤
                            </div>
                            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full {{ $activeOrder && $activeOrder->is_online ? 'bg-emerald-400' : 'bg-slate-400' }} border-2 border-slate-950"></span>
                        </div>

                        <!-- Customer Name & Status -->
                        <div class="min-w-0 leading-tight">
                            <div class="flex items-center gap-1.5">
                                <h1 class="text-[13px] font-bold text-white truncate" id="room-header-name">
                                    {{ $activeOrder ? ($activeOrder->customer_name ?: 'ลูกค้า') : 'ลูกค้า' }}
                                </h1>
                                @if($activeOrder)
                                    <span class="text-[9px] {{ $activeOrder->is_online ? 'bg-emerald-950 text-emerald-300 border-emerald-700' : 'bg-slate-800 text-slate-400 border-slate-700' }} border font-extrabold px-1.5 py-0.2 rounded-full shrink-0">
                                        {{ $activeOrder->is_online ? 'Online' : 'Offline' }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-[10px] text-amber-300 font-mono truncate" id="room-header-order-no">
                                {{ $activeOrder ? $activeOrder->order_no : '' }}
                            </div>
                        </div>
                    </div>

                    <!-- Right Action: Queue tag -->
                    @if($activeOrder)
                        <span class="px-2 py-0.5 rounded-md bg-rose-600 text-white font-black text-xs shrink-0 tracking-tight" id="room-header-queue">
                            คิว {{ $activeOrder->queue_number }}
                        </span>
                    @endif
                </div>
            </header>

            <!-- 2.2 ORDER + IP ADDRESS BINDING CARD -->
            @if($activeOrder)
            <div class="bg-gradient-to-r from-slate-900 via-slate-950 to-slate-900 text-white px-3.5 py-2 border-b border-slate-800 shrink-0 shadow-sm">
                <div class="flex items-center justify-between text-[10.5px]">
                    <div class="flex items-center gap-1.5 text-amber-300 font-mono font-semibold">
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
                        <span>จับคู่ IP เครื่องถูกต้อง</span>
                    </div>
                </div>

                <!-- Order items summary -->
                @if($activeOrder->items && $activeOrder->items->count() > 0)
                <div class="mt-1.5 text-[10.5px] text-slate-300 truncate bg-slate-900/90 px-2 py-0.5 rounded-lg border border-slate-800 flex items-center justify-between">
                    <span class="truncate">
                        <strong class="text-amber-400">🍢 เมนู ({{ $activeOrder->items->count() }}):</strong>
                        @foreach($activeOrder->items->take(2) as $it)
                            <span>{{ $it->product_name }} ({{ $it->quantity }}x)@if(!$loop->last), @endif</span>
                        @endforeach
                        @if($activeOrder->items->count() > 2)
                            <span class="text-slate-400">+{{ $activeOrder->items->count() - 2 }}</span>
                        @endif
                    </span>
                    <span class="text-amber-300 font-bold font-mono shrink-0 pl-1">฿{{ number_format($activeOrder->total_amount, 0) }}</span>
                </div>
                @endif
            </div>
            @endif

            <!-- 2.3 CHAT MESSAGES CONTAINER -->
            <main id="chat-messages-container" class="flex-1 overflow-y-auto p-3.5 space-y-3 hide-scrollbar">
                <div class="flex items-center justify-center my-1">
                    <span class="px-3 py-1 rounded-full bg-slate-200/80 text-[10.5px] font-bold text-slate-600 shadow-2xs">
                        วันนี้ · {{ \App\Support\ThaiDate::format(now(), false) }}
                    </span>
                </div>

                <div id="messages-list" class="space-y-3">
                    @if($messages->isEmpty())
                        <div class="text-center py-10 px-4" id="empty-chat-state">
                            <div class="w-14 h-14 rounded-full bg-slate-200/90 text-slate-500 flex items-center justify-center mx-auto text-2xl mb-2.5">💬</div>
                            <h3 class="text-xs font-bold text-slate-700">เริ่มสนทนากับลูกค้า</h3>
                            <p class="text-[11px] text-slate-500 mt-1 max-w-xs mx-auto">
                                พิมพ์ข้อความหรือกดคำตอบด่วนด้านล่าง เพื่อแจ้งสถานะการทำอาหารให้ลูกค้ารายนี้
                            </p>
                        </div>
                    @else
                        @foreach($messages as $msg)
                            @php
                                $isShop = $msg->sender === 'shop';
                                $timeStr = $msg->created_at ? $msg->created_at->timezone('Asia/Bangkok')->format('H:i') . ' น.' : '';
                            @endphp

                            @if($isShop)
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
                                <div class="flex items-start gap-2 max-w-[85%] animate-in fade-in slide-in-from-bottom-2 duration-150">
                                    <div class="w-7 h-7 rounded-full bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">👤</div>
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
                                        <div class="text-[9px] text-slate-400 text-right mt-1 font-mono">{{ $timeStr }}</div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </main>

            <!-- 2.4 KITCHEN QUICK REPLIES CHIPS -->
            <div class="px-3 pt-2 pb-1.5 bg-slate-100 border-t border-slate-200/80 overflow-x-auto hide-scrollbar flex items-center gap-1.5 shrink-0">
                <span class="text-[10px] text-slate-500 font-bold shrink-0">ตอบด่วน:</span>
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

            <!-- 2.5 CHAT INPUT BAR -->
            <footer class="bg-white border-t border-slate-200 p-2.5 shrink-0 relative z-20">
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
                    <button type="button"
                            onclick="document.getElementById('chat-image-input').click()"
                            class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition shrink-0 active:scale-95 cursor-pointer"
                            title="แนบรูปภาพ">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </button>
                    <input type="file" id="chat-image-input" accept="image/*" class="hidden" onchange="handleImageSelection(event)">

                    <input type="text"
                           id="chat-message-input"
                           placeholder="พิมพ์ข้อความตอบกลับลูกค้า..."
                           autocomplete="off"
                           class="flex-1 bg-slate-100 border border-slate-200 rounded-full px-4 py-2 text-xs sm:text-[13px] text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:bg-white transition">

                    <button type="submit"
                            id="send-button"
                            class="w-9 h-9 rounded-full bg-gradient-to-r from-[#D90012] to-[#B8000F] hover:from-[#B8000F] hover:to-[#900000] text-white flex items-center justify-center transition shrink-0 shadow-sm shadow-rose-950/20 active:scale-95 cursor-pointer">
                        <svg class="w-4 h-4 translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </button>
                </form>
            </footer>
        </div>

        <!-- ========================================================================= -->
        <!-- IMAGE VIEWER FULLSCREEN MODAL                                             -->
        <!-- ========================================================================= -->
        <div id="image-viewer-modal" class="fixed inset-0 z-50 bg-black/90 hidden items-center justify-center p-4" onclick="closeImageViewer()">
            <img id="image-viewer-img" src="" alt="ภาพเต็ม" class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl">
        </div>

    </div>

    <!-- ===================================================================== -->
    <!-- SCRIPT LOGIC (INBOX TABS, FILTER, LIVE SYNC, SENDING)                 -->
    <!-- ===================================================================== -->
    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let ACTIVE_ORDER_NO = "{{ $activeOrder ? $activeOrder->order_no : '' }}";
        let CUSTOMER_NAME = "{{ $activeOrder ? ($activeOrder->customer_name ?: 'ลูกค้า') : 'ลูกค้า' }}";
        let CUSTOMER_PHONE = "{{ $activeOrder ? $activeOrder->customer_phone : '' }}";
        let TARGET_IP = "{{ $targetIp }}";

        let currentAttachedImageUrl = null;
        let lastMessageCount = {{ $messages->count() }};
        let currentTabFilter = 'online'; // 'online', 'offline', 'all'

        window.addEventListener('DOMContentLoaded', () => {
            // Default filter to Online
            applyInboxFilter();
            scrollToBottom();
            // Start polling every 3 seconds for new chat messages
            setInterval(pollNewMessages, 3000);
        });

        // 1. INBOX TABS: ONLINE vs OFFLINE vs ALL
        function setInboxFilter(filter) {
            currentTabFilter = filter;
            const btnOnline = document.getElementById('tab-btn-online');
            const btnOffline = document.getElementById('tab-btn-offline');
            const btnAll = document.getElementById('tab-btn-all');

            // Reset tab styles
            [btnOnline, btnOffline, btnAll].forEach(btn => {
                btn.className = "flex-1 py-1.5 px-2 rounded-xl text-center transition-all flex items-center justify-center gap-1.5 text-slate-400 hover:text-white hover:bg-slate-800 text-xs font-bold";
            });

            if (filter === 'online') {
                btnOnline.className = "flex-1 py-1.5 px-2 rounded-xl text-center transition-all flex items-center justify-center gap-1.5 bg-rose-600 text-white shadow-xs text-xs font-bold";
            } else if (filter === 'offline') {
                btnOffline.className = "flex-1 py-1.5 px-2 rounded-xl text-center transition-all flex items-center justify-center gap-1.5 bg-slate-800 text-white shadow-xs text-xs font-bold";
            } else {
                btnAll.className = "py-1.5 px-2.5 rounded-xl text-center transition-all bg-slate-800 text-white shadow-xs text-[11px] font-bold";
            }

            applyInboxFilter();
        }

        function applyInboxFilter() {
            const rows = document.querySelectorAll('.inbox-item-row');
            const searchQuery = (document.getElementById('inbox-search-input')?.value || '').trim().toLowerCase();

            rows.forEach(row => {
                const isOnline = row.getAttribute('data-online') === '1';
                const searchData = row.getAttribute('data-search') || '';

                let matchesTab = true;
                if (currentTabFilter === 'online') {
                    matchesTab = isOnline;
                } else if (currentTabFilter === 'offline') {
                    matchesTab = !isOnline;
                }

                let matchesSearch = true;
                if (searchQuery) {
                    matchesSearch = searchData.includes(searchQuery);
                }

                if (matchesTab && matchesSearch) {
                    row.classList.remove('hidden');
                    row.classList.add('flex');
                } else {
                    row.classList.add('hidden');
                    row.classList.remove('flex');
                }
            });
        }

        function filterInboxItems(val) {
            applyInboxFilter();
        }

        // 2. SWITCH TO SPECIFIC CHAT ROOM
        function openChatRoom(orderNo) {
            window.location.href = "{{ route('pos.kitchen.chat') }}?order_no=" + encodeURIComponent(orderNo);
        }

        // 3. BACK TO MESSENGER INBOX LIST (กดย้อนกลับหน้าต่างแรก)
        function backToInboxView() {
            const viewInbox = document.getElementById('view-chat-inbox');
            const viewRoom = document.getElementById('view-chat-room');
            if (viewInbox && viewRoom) {
                viewRoom.classList.add('hidden');
                viewRoom.classList.remove('flex');
                viewInbox.classList.remove('hidden');
                viewInbox.classList.add('flex');
                // Remove order_no from URL cleanly
                const url = new URL(window.location.href);
                url.searchParams.delete('order_no');
                window.history.replaceState({}, '', url.toString());
                localStorage.removeItem('kitchen_active_chat_order');
            } else {
                window.location.href = "{{ route('pos.kitchen.chat') }}";
            }
        }

        function scrollToBottom() {
            const container = document.getElementById('chat-messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }

        // 4. Handle Form Submit
        function handleChatSubmit(event) {
            event.preventDefault();
            const input = document.getElementById('chat-message-input');
            const text = input.value.trim();

            if (!text && !currentAttachedImageUrl) return;

            submitAdminMessage(text, currentAttachedImageUrl);
            input.value = '';
            cancelImageAttachment();
        }

        function sendQuickReply(text) {
            submitAdminMessage(text, null);
        }

        async function submitAdminMessage(text, imageUrl) {
            const emptyState = document.getElementById('empty-chat-state');
            if (emptyState) emptyState.remove();

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
                    <div class="w-7 h-7 rounded-full bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">👤</div>
                    <div class="relative bg-white text-slate-800 rounded-2xl rounded-tl-xs px-3.5 py-2 shadow-2xs border border-slate-200/90 bubble-tail-left text-[12.5px] leading-relaxed">
                        <div class="flex items-center justify-between gap-2 mb-0.5">
                            <span class="text-[10px] font-bold text-slate-900">${escapeHtml(CUSTOMER_NAME)}</span>
                            ${ipBadge}
                        </div>
                        <div class="whitespace-pre-line text-slate-800 font-medium">${escapeHtml(text)}</div>
                        ${imgHtml}
                        <div class="text-[9px] text-slate-400 text-right mt-1 font-mono">${timeStr}</div>
                    </div>
                </div>
            `;
            list.insertAdjacentHTML('beforeend', html);
        }

        // 5. Poll New Messages from Server
        async function pollNewMessages() {
            if (!ACTIVE_ORDER_NO) return;

            try {
                const response = await fetch(`{{ route('pos.kitchen.chat.messages') }}?order_no=${encodeURIComponent(ACTIVE_ORDER_NO)}&phone=${encodeURIComponent(CUSTOMER_PHONE)}`);
                const data = await response.json();

                if (data.success && data.messages) {
                    if (data.messages.length > lastMessageCount) {
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
            } catch (err) {}
        }

        // 6. Image Upload Handling
        async function handleImageSelection(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = async function(e) {
                const base64 = e.target.result;
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

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>"']/g, function (m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
            });
        }
    </script>
</body>
</html>
