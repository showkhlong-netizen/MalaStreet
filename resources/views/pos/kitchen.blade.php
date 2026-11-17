@extends('layouts.app', ['title' => 'จอครัว (KDS) - ร้านหม่าล่าสตรีท'])

@section('content')
<div class="flex-1 bg-slate-100/70 text-slate-800 p-3 sm:p-4 lg:p-6 overflow-x-clip">
    <div class="max-w-[1920px] mx-auto flex flex-col xl:flex-row gap-5 items-start">
        
        <!-- LEFT: KITCHEN DISPLAY (จอคุมเตาย่าง & หม้อซุป) -->
        <div class="flex-1 min-w-0 space-y-6 w-full" id="kitchen-main-orders-area">
        
        <!-- 1. Kitchen Header (Light Theme, Premium White Card) -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-13 h-13 rounded-2xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600 shadow-xs">
                    <!-- Clean Monochrome Chef / Fire SVG -->
                    <svg class="w-7 h-7 stroke-current" fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">จอคุมเตาย่าง & หม้อซุป (Kitchen Display)</h1>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">ระบบจัดการลำดับคิวและแสดงสถานะปรุงอาหารแบบเรียลไทม์ (หน้าร้าน & สั่งออนไลน์)</p>
                </div>
            </div>

            <!-- Stats & Quick Refresh -->
            <div id="kitchen-header-stats" class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs w-full sm:w-auto justify-between sm:justify-end">
                <div class="px-3.5 py-2 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700 font-medium flex items-center space-x-2 whitespace-nowrap shrink-0">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse shrink-0"></span>
                    <span>รอปรุง: <strong class="text-slate-900 font-black text-sm">{{ $activeOrders->count() }}</strong> บิล</span>
                </div>
                <div class="px-3.5 py-2 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700 font-medium flex items-center space-x-2 whitespace-nowrap shrink-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>เสร็จวันนี้: <strong class="text-slate-900 font-black text-sm">{{ $completedCount }}</strong> บิล</span>
                </div>
                <button onclick="syncKitchenOrdersSilently(true)" 
                    id="manual-refresh-btn"
                    class="py-2 px-3.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition-all shadow-xs flex items-center space-x-1.5 cursor-pointer active:scale-95 whitespace-nowrap shrink-0" title="รีเฟรชออเดอร์">
                    <svg id="manual-refresh-icon" class="w-4 h-4 stroke-current transition-transform duration-500 shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>รีเฟรช</span>
                </button>

                <!-- แถบหน้าต่างแชทลูกค้า (ฝั่งแอดมิน) - ตั้งฝั่งขวาสุดของแถบหน้าจอครัว -->
                <button type="button" 
                    onclick="scrollToKitchenChat()" 
                    class="py-2 px-4 rounded-2xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white font-black transition-all shadow-md shadow-rose-950/20 flex items-center space-x-2 shrink-0 group active:scale-95 border border-rose-400/40 cursor-pointer whitespace-nowrap" 
                    title="เลื่อนไปยังหน้าต่างแชทลูกค้า (ฝั่งแอดมิน) ฝั่งขวามือสุด">
                    <div class="relative flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-emerald-400 border border-white animate-pulse"></span>
                    </div>
                    <span>แชทลูกค้า (แอดมิน)</span>
                    @if(!empty($unreadChatCount) && $unreadChatCount > 0)
                        <span class="text-[10px] bg-amber-400 text-slate-950 font-black px-1.5 py-0.2 rounded-full animate-bounce shadow-2xs shrink-0">
                            {{ $unreadChatCount }}
                        </span>
                    @endif
                </button>
            </div>
        </div>

        <!-- 1.5 Kitchen Search, 3/4 Column Toggle & Sort/Filter Bar (Floating Sticky Card) -->
        <section id="kitchen-floating-toolbar" class="sticky top-[88px] z-30 bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/90 shadow-sm px-4 py-3 flex flex-wrap items-center justify-between gap-3 transition-shadow">
            <div class="flex items-center gap-2.5 whitespace-nowrap shrink-0">
                <span class="w-1.5 h-5 rounded-full bg-[#E50914] block shrink-0"></span>
                <h2 class="text-[17px] font-black text-slate-900 tracking-tight whitespace-nowrap">รายการออเดอร์ในครัว</h2>
                <span class="text-[13px] font-medium text-slate-500 whitespace-nowrap">(<span id="kitchen-visible-count">{{ $activeOrders->count() }}</span> บิล)</span>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end flex-nowrap overflow-x-auto no-scrollbar">
                <!-- Quick Search Input -->
                <div class="relative flex-1 sm:w-56 sm:flex-initial">
                    <input type="text" id="kitchen-search-input"
                           oninput="applyKitchenOrderFiltersAndLayout()"
                           placeholder="ค้นหาคิว ชื่อ เบอร์ หรือเมนู..."
                           class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200/90 rounded-full text-[12.5px] font-medium text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-[#E50914] shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- 3 / 4 Items per Row Toggle (Matches POS Grid Toggle UI) -->
                <div class="flex items-center bg-white border border-slate-200/90 rounded-full p-0.5 shadow-2xs shrink-0" title="เลือกการแสดงผลจำนวนรายการต่อแถว">
                    <button type="button" onclick="setKitchenGridColumns(3)" id="btn-kitchen-grid-3"
                            class="kitchen-grid-toggle-btn px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all bg-[#E50914] text-white shadow-2xs cursor-pointer"
                            title="แสดง 3 รายการต่อแถว">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                            <rect x="2" y="7" width="4" height="6" rx="1"/>
                            <rect x="8" y="7" width="4" height="6" rx="1"/>
                            <rect x="14" y="7" width="4" height="6" rx="1"/>
                        </svg>
                        <span class="text-[11px] leading-none">3</span>
                    </button>

                    <button type="button" onclick="setKitchenGridColumns(4)" id="btn-kitchen-grid-4"
                            class="kitchen-grid-toggle-btn px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all text-slate-500 hover:text-slate-800 hover:bg-slate-100 cursor-pointer"
                            title="แสดง 4 รายการต่อแถว">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                            <rect x="1.5" y="7" width="3.2" height="6" rx="0.8"/>
                            <rect x="6.1" y="7" width="3.2" height="6" rx="0.8"/>
                            <rect x="10.7" y="7" width="3.2" height="6" rx="0.8"/>
                            <rect x="15.3" y="7" width="3.2" height="6" rx="0.8"/>
                        </svg>
                        <span class="text-[11px] leading-none">4</span>
                    </button>
                </div>

                <!-- Sort & Filter Dropdown -->
                <div class="relative shrink-0">
                    <select id="kitchen-sort-select" onchange="onKitchenSortChange(this.value)"
                            class="appearance-none bg-white border border-slate-200/90 hover:border-slate-300 rounded-full pl-3.5 pr-8 py-1.5 text-[12.5px] font-bold text-slate-700 shadow-2xs focus:outline-none focus:border-[#E50914] cursor-pointer">
                        <option value="newest">เรียงตาม : ออเดอร์ล่าสุดก่อน</option>
                        <option value="oldest">เรียงตาม : คิวมาก่อน (FIFO)</option>
                        <option value="online_first">แสดง : ออนไลน์ขึ้นก่อน</option>
                        <option value="pos_first">แสดง : หน้าร้านขึ้นก่อน</option>
                        <option value="cooking_first">สถานะ : กำลังปรุงขึ้นก่อน</option>
                        <option value="ready_first">สถานะ : พร้อมรับขึ้นก่อน</option>
                        <option value="filter_online">กรองเฉพาะ : สั่งออนไลน์</option>
                        <option value="filter_pos">กรองเฉพาะ : หน้าร้าน</option>
                        <option value="filter_cooking">กรองเฉพาะ : กำลังปรุง</option>
                        <option value="filter_pending">กรองเฉพาะ : รอเริ่มทำ</option>
                    </select>
                    <svg class="w-3.5 h-3.5 text-slate-500 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>

            <!-- Top Order Channel Tabs (แยกประเภทออเดอร์: ทั้งหมด / สั่งรับหน้าร้าน / Delivery / หน้าร้าน) -->
            <div id="kitchen-channel-tabs" class="w-full pt-2.5 mt-0.5 border-t border-slate-100 flex items-center gap-2 overflow-x-auto no-scrollbar">
                <button type="button" data-channel-tab="all" onclick="setKitchenChannelTab('all')"
                        class="kitchen-channel-tab shrink-0 whitespace-nowrap inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-extrabold transition-all bg-[#E50914] text-white shadow-xs cursor-pointer">
                    <span>ทั้งหมด</span>
                    <span id="tab-count-all" class="px-1.5 py-0.2 rounded-full text-[11px] bg-white/20 text-white whitespace-nowrap">{{ $activeOrders->count() }}</span>
                </button>
                <button type="button" data-channel-tab="online_pickup" onclick="setKitchenChannelTab('online_pickup')"
                        class="kitchen-channel-tab shrink-0 whitespace-nowrap inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 border border-slate-200/80 cursor-pointer">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>สั่งรับหน้าร้าน (ออนไลน์)</span>
                    <span id="tab-count-online_pickup" class="px-1.5 py-0.2 rounded-full text-[11px] bg-white text-slate-700 border border-slate-200 whitespace-nowrap">0</span>
                </button>
                <button type="button" data-channel-tab="delivery" onclick="setKitchenChannelTab('delivery')"
                        class="kitchen-channel-tab shrink-0 whitespace-nowrap inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-700 hover:bg-sky-50 hover:text-sky-800 border border-slate-200/80 cursor-pointer">
                    <span class="w-2 h-2 rounded-full bg-sky-500 shrink-0"></span>
                    <span>Delivery (จัดส่ง)</span>
                    <span id="tab-count-delivery" class="px-1.5 py-0.2 rounded-full text-[11px] bg-white text-slate-700 border border-slate-200 whitespace-nowrap">0</span>
                </button>
                <button type="button" data-channel-tab="pos" onclick="setKitchenChannelTab('pos')"
                        class="kitchen-channel-tab shrink-0 whitespace-nowrap inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-700 hover:bg-slate-200 hover:text-slate-900 border border-slate-200/80 cursor-pointer">
                    <span class="w-2 h-2 rounded-full bg-slate-500 shrink-0"></span>
                    <span>หน้าร้าน (POS)</span>
                    <span id="tab-count-pos" class="px-1.5 py-0.2 rounded-full text-[11px] bg-white text-slate-700 border border-slate-200 whitespace-nowrap">0</span>
                </button>
                <button type="button" data-channel-tab="history" onclick="setKitchenChannelTab('history')"
                        class="kitchen-channel-tab ml-auto shrink-0 whitespace-nowrap inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-700 hover:bg-slate-200 hover:text-slate-900 border border-slate-200/80 cursor-pointer">
                    <svg class="w-3.5 h-3.5 stroke-current shrink-0" fill="none" stroke-width="2.1" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>ประวัติทำรายการ</span>
                    <span id="tab-count-history" class="px-1.5 py-0.2 rounded-full text-[11px] bg-white text-slate-700 border border-slate-200 whitespace-nowrap">{{ $completedOrders->count() }}</span>
                </button>
            </div>
        </section>

        <div id="kitchen-orders-sync-container">
        <!-- 2. Active & Completed History Orders Grid (Light Theme, Luxury Modern Cards) -->
        @php
            $allKitchenOrders = $activeOrders->concat($completedOrders ?? collect());
        @endphp
        @if($allKitchenOrders->isEmpty())
            <div class="bg-white border border-slate-200/90 rounded-3xl p-16 text-center text-slate-400 shadow-sm">
                <div class="w-16 h-16 rounded-3xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 stroke-current" fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-black text-slate-800">ไม่มีออเดอร์ค้างในครัว</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">ย่างเตาถ่านและต้มซุปเสร็จครบทุกรายการแล้ว ระบบจะตรวจจับและดึงออเดอร์ใหม่เข้ามาอัตโนมัติ</p>
            </div>
        @else
            <div id="kitchen-orders-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($allKitchenOrders as $order)
                    @php
                        $isOnline = $order->order_type === 'online';
                        $channelHaystack = mb_strtolower(trim(($order->pickup_time ?? '') . ' ' . ($order->notes ?? '')), 'UTF-8');
                        $isDelivery = ($order->order_type === 'delivery')
                            || ($isOnline && (
                                str_contains($channelHaystack, 'delivery')
                                || str_contains($channelHaystack, 'จัดส่ง')
                                || str_contains($channelHaystack, 'ส่งถึง')
                                || str_contains($channelHaystack, 'เดลิเวอรี่')
                            ));
                        $orderChannel = $isDelivery ? 'delivery' : ($isOnline ? 'online_pickup' : 'pos');
                        $statusBorder = match($order->status) {
                            'pending', 'confirmed' => 'border-t-4 border-t-amber-500',
                            'cooking' => 'border-t-4 border-t-rose-600 ring-2 ring-rose-500/10',
                            'ready' => 'border-t-4 border-t-emerald-500 ring-2 ring-emerald-500/10',
                            default => 'border-t-4 border-t-slate-400'
                        };
                        $searchText = mb_strtolower(trim(
                            ($order->queue_number ?? '') . ' ' .
                            ($order->order_no ?? '') . ' ' .
                            ($order->customer_name ?? '') . ' ' .
                            ($order->customer_phone ?? '') . ' ' .
                            ($order->ip_address ?? '') . ' ' .
                            ($order->notes ?? '') . ' ' .
                            $order->items->pluck('product_name')->implode(' ')
                        ), 'UTF-8');
                        $isHistory = ($order->status === 'completed');
                    @endphp

                    <div class="kitchen-order-card bg-white rounded-3xl border border-slate-200/90 {{ $statusBorder }} shadow-sm hover:shadow-md transition-all flex flex-col justify-between overflow-hidden {{ $isHistory ? 'hidden' : '' }}" 
                         id="order-card-{{ $order->id }}"
                         data-order-id="{{ $order->id }}"
                         data-order-type="{{ $order->order_type ?? 'pos' }}"
                         data-channel="{{ $orderChannel }}"
                         data-status="{{ $order->status }}"
                         data-is-history="{{ $isHistory ? '1' : '0' }}"
                         data-created-ts="{{ $order->created_at ? $order->created_at->timestamp : 0 }}"
                         data-search="{{ $searchText }}">
                        
                        <div class="flex-1 flex flex-col">
                            <!-- 1. Top Red-Themed Sky & Cityscape Mascot Banner (เหมือนหน้าติดตามออเดอร์ภาพที่ 2) -->
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
                                            onclick="openOrderInSideChat('{{ $order->order_no }}')"
                                            aria-label="เปิดแชทกับลูกค้า"
                                            title="เปิดแชทกับลูกค้ารายนี้ในหน้าต่างแชทขวามือสุด"
                                            class="w-9 h-9 rounded-full bg-white hover:bg-slate-50 text-slate-700 hover:text-[#D90012] shadow-md flex items-center justify-center transition-all cursor-pointer active:scale-95">
                                        <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2.1" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </button>

                                    <span class="px-3.5 py-1.5 rounded-full bg-white text-[#D90012] font-extrabold text-[11.5px] shadow-md border border-rose-100">
                                        คิว {{ $order->queue_number }}
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

                            <!-- 2. White Sheet Container (ตามภาพที่ 2 เป๊ะ) -->
                            <div class="-mt-4 rounded-t-[24px] bg-white relative z-20 px-5 pt-2.5 pb-4 flex-1 shadow-[0_-6px_24px_rgba(0,0,0,0.06)]">
                                <div class="w-10 h-1 rounded-full bg-slate-200 mx-auto mb-3.5"></div>

                                <!-- Synchronized Red-Themed Live Order Timeline -->
                                @include('online.partials.order-timeline-stepper', ['variant' => 'sheet', 'order' => $order, 'channel' => $orderChannel])

                                <hr class="border-slate-100 my-4">

                                <!-- Store Name -->
                                <div class="text-[15px] font-bold text-slate-900 mb-3">หม่าล่าสตรีท (Mala Street)</div>

                                <!-- Payment & Totals Summary -->
                                <div class="space-y-2.5 text-[13px]">
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span>ยอดรวม</span>
                                        <span class="font-bold text-slate-900 text-[14px]">฿{{ number_format((float) $order->total_amount, 2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-slate-600">
                                        <span>คูปอง</span>
                                        <span class="{{ ($order->discount ?? 0) > 0 ? 'font-bold text-[#D90012]' : 'text-slate-700' }}">
                                            @if(($order->discount ?? 0) > 0)
                                                -฿{{ number_format((float) $order->discount, 2) }}
                                            @else
                                                ไม่ได้ใช้คูปอง
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between text-slate-600 gap-2">
                                        <span>วิธีชำระเงิน</span>
                                        <span class="flex items-center gap-1.5 font-medium text-slate-800 flex-wrap justify-end">
                                            @if($order->payment_status !== 'paid')
                                                <button type="button"
                                                        onclick="if(confirm('ยืนยันได้รับยอดชำระเงินสำหรับคิว {{ $order->queue_number }} แล้วใช่หรือไม่?')) updateStatus({{ $order->id }}, 'confirmed', true)"
                                                        title="คลิกเพื่อยืนยันรับเงินแล้ว"
                                                        class="px-5 py-1.5 rounded-xl bg-[#C8102E] hover:bg-[#A80B24] active:scale-[0.98] text-white text-[16px] font-black tracking-wide shadow-md shadow-rose-700/25 whitespace-nowrap cursor-pointer transition-all">
                                                    ยังไม่ได้ชำระ
                                                </button>
                                            @elseif(($order->payment_method ?? 'promptpay') === 'promptpay')
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
                                    @foreach($order->items as $idx => $item)
                                        @php
                                            $cookLabel = $item->cooking_type === 'grill'
                                                ? ($item->grill_style === 'brush' ? 'ปิ้งย่าง (ทาซอสหม่าล่า)' : 'ปิ้งย่าง (จุ่มซอสหม่าล่า)')
                                                : match($item->soup_style) {
                                                    'black_soup' => 'ต้มซุปน้ำดำ',
                                                    'tomyum_rich' => 'ต้มซุปต้มยำ-น้ำข้น',
                                                    default => 'ต้มซุปหม่าล่า',
                                                };
                                            $spiceLabel = match($item->spiciness_level) {
                                                'none' => 'ไม่เผ็ด',
                                                'mild' => 'เผ็ดน้อย',
                                                'medium' => 'เผ็ดกลาง',
                                                'hot' => 'เผ็ดมาก',
                                                default => $item->spiciness_level ?: 'เผ็ดกลาง',
                                            };
                                        @endphp
                                        <div class="flex items-start gap-3 {{ $idx >= 2 ? 'extra-kitchen-item-' . $order->id : '' }}">
                                            <div class="w-5 h-5 rounded-[4px] border border-slate-300 text-[11px] font-bold text-slate-800 flex items-center justify-center shrink-0 mt-0.5">
                                                {{ $item->quantity_sticks }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-[13px] font-medium text-slate-900 leading-snug">{{ $item->product_name }}</div>
                                                <div class="text-[11px] text-slate-400 leading-snug mt-0.5">{{ $cookLabel }} • {{ $spiceLabel }}</div>
                                                @if(!empty($item->special_instructions))
                                                    <div class="text-[11px] text-amber-700 font-medium leading-snug mt-0.5">หมายเหตุ: {{ $item->special_instructions }}</div>
                                                @endif
                                            </div>
                                            <div class="text-[12px] font-semibold text-slate-600 shrink-0">
                                                ฿{{ number_format((float) $item->subtotal, 2) }}
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Expandable Order Breakdown (เปิดแสดงครบตามภาพที่ 2 และย่อ/ขยายได้) -->
                                    <div id="kitchen-order-extra-summary-{{ $order->id }}" class="pt-2.5 mt-2 border-t border-slate-100 space-y-1.5 text-[11.5px] text-slate-500">
                                        <div class="flex items-center justify-between">
                                            <span>จำนวนทั้งหมด</span>
                                            <span class="font-bold text-slate-700">{{ $order->items->sum('quantity_sticks') }} ไม้ ({{ $order->items->count() }} รายการ)</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span>ค่าซุปหม่าล่าเพิ่มเติม</span>
                                            <span class="font-bold text-slate-700">฿{{ number_format((float) ($order->soup_extra ?? 0), 2) }}</span>
                                        </div>
                                        @if(!empty($order->notes))
                                            <div class="pt-1 text-slate-600">
                                                <span class="font-bold text-slate-700">หมายเหตุถึงครัว:</span> {{ $order->notes }}
                                            </div>
                                        @endif
                                    </div>

                                    <button type="button"
                                            id="toggle-kitchen-more-btn-{{ $order->id }}"
                                            onclick="toggleKitchenCardMoreItems({{ $order->id }})"
                                            class="w-full py-2 text-xs font-bold text-[#D90012] border border-slate-900 hover:bg-rose-50 rounded-xl flex items-center justify-center gap-1 transition-colors cursor-pointer">
                                        <span id="toggle-kitchen-more-label-{{ $order->id }}">แสดงน้อยลง</span>
                                        <svg id="toggle-kitchen-more-icon-{{ $order->id }}" class="w-3.5 h-3.5 transition-transform rotate-180" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Action Buttons & Details Modal Trigger -->
                        @if($isHistory)
                            <div class="p-3.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between gap-2">
                                <button type="button" onclick="openOrderModal({{ $order->id }})"
                                    class="p-2.5 rounded-2xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold shadow-xs transition-colors flex items-center justify-center cursor-pointer" title="ดูรายละเอียดแบบขยาย">
                                    <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                <div class="flex-1 py-2.5 px-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-extrabold flex items-center justify-center gap-1.5 whitespace-nowrap">
                                    <svg class="w-4 h-4 stroke-current shrink-0" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>ทำรายการสำเร็จแล้ว{{ $order->updated_at ? ' • ' . $order->updated_at->timezone('Asia/Bangkok')->format('H:i') : '' }}</span>
                                </div>
                            </div>
                        @elseif($orderChannel === 'delivery')
                            @php
                                $deliveryStep = (int) ($order->kitchen_step ?: ($order->status === 'ready' ? 3 : ($order->status === 'cooking' ? 2 : 1)));
                                $cookingStartedTs = $order->cooking_started_at ? $order->cooking_started_at->timestamp : null;
                            @endphp
                            <div class="p-3.5 bg-slate-50 border-t border-slate-200 flex items-center gap-2">
                                @if($deliveryStep === 1)
                                    <!-- Step 1: 3 ปุ่ม -> 1.รับออร์เดอร์  2.แก้ไข  3.ยกเลิก (ไอคอนวงกลมมีกากบาท) -->
                                    <button type="button" onclick="updateDeliveryStep({{ $order->id }}, 'accept')"
                                        class="flex-1 py-2.5 px-3 rounded-2xl bg-[#E50914] hover:bg-[#C8102E] active:scale-[0.99] text-white text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-1.5 whitespace-nowrap cursor-pointer">
                                        <svg class="w-4 h-4 stroke-current shrink-0" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>รับออร์เดอร์</span>
                                    </button>

                                    <button type="button" onclick="openDeliveryEditModal({{ $order->id }})"
                                        class="py-2.5 px-3.5 rounded-2xl bg-white hover:bg-slate-100 active:scale-[0.99] text-slate-800 border border-slate-200 text-xs font-bold shadow-2xs transition-all flex items-center justify-center gap-1.5 whitespace-nowrap shrink-0 cursor-pointer">
                                        <svg class="w-3.5 h-3.5 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <span>แก้ไข</span>
                                    </button>

                                    <button type="button" onclick="openPinCancelModal({{ $order->id }})"
                                        class="w-10 h-10 rounded-full bg-white hover:bg-rose-50 text-slate-600 hover:text-rose-600 border border-slate-300 hover:border-rose-400 shadow-2xs transition-all flex items-center justify-center shrink-0 cursor-pointer"
                                        title="ยกเลิกออร์เดอร์">
                                        <svg class="w-5 h-5 stroke-current" fill="none" stroke-width="1.8" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="9" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6M9 9l6 6" />
                                        </svg>
                                    </button>
                                @elseif($deliveryStep === 2)
                                    <!-- Step 2: 3 ปุ่ม -> 1.เริ่มทำออร์เดอร์ (สีเขียวเข้มสดใส -> หายเป็นนับถอยหลัง 10 นาที)  2.พร้อมส่ง  3.สี่เหลี่ยมมนย้อนกลับสเต็ป 1 -->
                                    @if(!$cookingStartedTs)
                                        <button type="button" onclick="updateDeliveryStep({{ $order->id }}, 'start_cooking')"
                                            class="flex-1 py-2.5 px-2.5 rounded-2xl bg-[#059669] hover:bg-[#047857] active:scale-[0.99] text-white text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-1.5 whitespace-nowrap cursor-pointer">
                                            <svg class="w-4 h-4 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                            </svg>
                                            <span>เริ่มทำออร์เดอร์</span>
                                        </button>
                                    @else
                                        <div class="delivery-countdown-pill flex-1 py-2.5 px-2.5 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-black flex items-center justify-center gap-1.5 whitespace-nowrap"
                                             data-started-ts="{{ $cookingStartedTs }}"
                                             data-duration="600">
                                            <svg class="w-4 h-4 stroke-current shrink-0" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="countdown-text font-mono">10:00</span>
                                        </div>
                                    @endif

                                    <button type="button" onclick="updateDeliveryStep({{ $order->id }}, 'mark_ready')"
                                        class="flex-1 py-2.5 px-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 active:scale-[0.99] text-white text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-1.5 whitespace-nowrap cursor-pointer">
                                        <svg class="w-4 h-4 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                        </svg>
                                        <span>พร้อมส่ง</span>
                                    </button>

                                    <button type="button" onclick="updateDeliveryStep({{ $order->id }}, 'back_to_step1')"
                                        class="w-10 h-10 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 shadow-2xs transition-all flex items-center justify-center shrink-0 cursor-pointer"
                                        title="ย้อนกลับไปแก้ไขในสเต็ปที่ 1">
                                        <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l-4-4 4-4" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 10h11a4 4 0 110 8h-1" />
                                        </svg>
                                    </button>
                                @else
                                    <!-- Step 3: 2 ปุ่ม -> 1.ไปที่โลเคชั่น  2.ส่งออร์เดอร์สำเร็จ (เปิดป๊อปอัปสรุปยอดก่อนยืนยันปิดบิล) -->
                                    <button type="button" onclick="openDeliveryLocation({{ $order->id }})"
                                        class="flex-1 py-2.5 px-3 rounded-2xl bg-white hover:bg-slate-100 active:scale-[0.99] text-slate-800 border border-slate-200 text-xs font-bold shadow-2xs transition-all flex items-center justify-center gap-1.5 whitespace-nowrap cursor-pointer">
                                        <svg class="w-4 h-4 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>ไปที่โลเคชั่น</span>
                                    </button>

                                    <button type="button" onclick="openDeliveryCompleteModal({{ $order->id }})"
                                        class="flex-1 py-2.5 px-3 rounded-2xl bg-[#059669] hover:bg-[#047857] active:scale-[0.99] text-white text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-1.5 whitespace-nowrap cursor-pointer">
                                        <svg class="w-4 h-4 stroke-current shrink-0" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>ส่งออร์เดอร์สำเร็จ</span>
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center gap-2">
                                <button onclick="openOrderModal({{ $order->id }})" 
                                    class="p-3 rounded-2xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold shadow-xs transition-colors flex items-center justify-center" title="ดูรายละเอียดแบบขยาย">
                                    <!-- Monochrome Eye / Expand SVG -->
                                    <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>

                                @if($order->status === 'pending' || $order->status === 'confirmed')
                                    <button onclick="updateStatus({{ $order->id }}, 'cooking')"
                                        class="flex-1 py-3 rounded-2xl bg-rose-600 hover:bg-rose-500 active:scale-[0.99] text-white text-xs font-bold shadow-md shadow-rose-600/20 transition-all flex items-center justify-center space-x-1.5 whitespace-nowrap">
                                        <svg class="w-4 h-4 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                        </svg>
                                        <span>เริ่มย่าง/ต้ม</span>
                                    </button>
                                @elseif($order->status === 'cooking')
                                    <button onclick="updateStatus({{ $order->id }}, 'ready')"
                                        class="flex-1 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 active:scale-[0.99] text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center space-x-1.5 whitespace-nowrap">
                                        <svg class="w-4 h-4 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        <span>พร้อมรับแล้ว</span>
                                    </button>
                                @elseif($order->status === 'ready')
                                    <button onclick="updateStatus({{ $order->id }}, 'completed')"
                                        class="flex-1 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 active:scale-[0.99] text-white text-xs font-bold shadow-xs transition-all flex items-center justify-center space-x-1.5 whitespace-nowrap">
                                        <svg class="w-4 h-4 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>ส่งมอบอาหารแล้ว</span>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
            <div id="kitchen-filter-empty" class="hidden bg-white border border-slate-200/90 rounded-3xl p-12 text-center text-slate-400 shadow-sm mt-2">
                <p class="text-sm font-black text-slate-700">ไม่พบออเดอร์ที่ตรงกับการค้นหาหรือตัวกรอง</p>
                <button type="button" onclick="resetKitchenFilters()" class="mt-3 px-4 py-1.5 rounded-full bg-rose-50 hover:bg-rose-100 text-[#E50914] text-xs font-bold transition-colors cursor-pointer">
                    ล้างตัวกรองทั้งหมด
                </button>
            </div>
        </div>
        </div> <!-- End of LEFT: KITCHEN DISPLAY (จอคุมเตาย่าง & หม้อซุป) -->

        <!-- RIGHT: PERMANENTLY OPEN ADMIN CHAT (ฝั่งขวามือสุด เปิดค้างอยู่ตลอด) -->
        <aside class="w-full xl:w-[420px] 2xl:w-[430px] shrink-0 xl:sticky xl:top-[88px] h-[calc(100vh-6.5rem)] min-h-[580px] max-h-[860px] flex flex-col bg-white rounded-3xl border-2 border-slate-200/90 shadow-xl overflow-hidden transition-all ring-1 ring-slate-200/60" id="kitchen-side-chat-panel">
            <iframe id="kitchen-side-chat-frame" 
                    src="{{ route('kitchen.chat', array_filter(['order_no' => request('chat_order') ?: request('order_no')])) }}" 
                    class="w-full h-full border-0 flex-1 bg-white"
                    title="แชทลูกค้า (ฝั่งแอดมิน / จอครัว) เปิดค้างตลอดเวลา">
            </iframe>
        </aside>

    </div>
</div>

<!-- 4. Order Details Modal (ป๊อปอัปเรียบหรู พรีเมียม อ่านง่าย โทนสว่าง สะอาดตา พร้อมไอคอนสีเดียว) -->
<div id="order-detail-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full shadow-2xl overflow-hidden border border-slate-200 flex flex-col max-h-[90vh] animate-in fade-in zoom-in-95 duration-150">
        <!-- Modal Top Header (Light Elegant White & Slate-50) -->
        <div class="p-6 bg-slate-50 border-b border-slate-200/90 flex justify-between items-center">
            <div class="flex items-center space-x-3.5">
                <div class="px-3.5 py-1.5 rounded-2xl bg-rose-600 text-white font-black text-2xl tracking-tight shadow-sm shadow-rose-900/20" id="modal-queue-tag">
                    A01
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h3 class="font-black text-base text-slate-900" id="modal-order-no">MS-POS-001</h3>
                        <span id="modal-channel-badge" class="px-2 py-0.5 rounded-lg text-[10px] font-bold border flex items-center space-x-1"></span>
                    </div>
                    <span class="text-xs text-slate-500 font-medium" id="modal-customer-info">ลูกค้าหน้าร้าน</span>
                </div>
            </div>
            <button onclick="closeOrderModal()" class="w-9 h-9 rounded-2xl bg-white border border-slate-200 text-slate-400 hover:text-slate-800 hover:bg-slate-100 flex items-center justify-center transition-colors shadow-xs">
                <svg class="w-5 h-5 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 text-xs">
            <!-- Pickup Info Box -->
            <div class="p-4 bg-amber-50/70 rounded-2xl border border-amber-200/70 flex justify-between items-center">
                <div class="flex items-center space-x-2 text-amber-950 font-medium">
                    <svg class="w-4 h-4 text-amber-700 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>เวลานัดรับสินค้า:</span>
                </div>
                <strong class="font-bold text-amber-900 text-sm" id="modal-pickup-time">ทันที</strong>
            </div>

            <div id="modal-notes-box" class="hidden p-3.5 bg-rose-50/70 rounded-2xl border border-rose-100 text-xs text-rose-950 flex items-start space-x-2">
                <svg class="w-4 h-4 text-rose-600 stroke-current shrink-0 mt-0.5" fill="none" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
                <div>
                    <span class="font-bold text-rose-700">หมายเหตุจากลูกค้า:</span>
                    <span class="text-rose-900 ml-1" id="modal-notes-text"></span>
                </div>
            </div>

            <!-- Items Container -->
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2.5">รายการไม้หม่าล่าที่สั่งทั้งหมด:</span>
                <div class="space-y-2.5" id="modal-items-list">
                    <!-- Populated by JS -->
                </div>
            </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="p-4 bg-slate-50 border-t border-slate-200/90 flex items-center justify-between gap-3">
            <button onclick="closeOrderModal()" class="py-2.5 px-4 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-100 shadow-xs">
                ปิดหน้าต่าง
            </button>
            <div id="modal-action-slot" class="flex-1 max-w-xs">
                <!-- Populated by JS for quick status update inside modal -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
@php
    $allKitchenOrdersMap = $activeOrders->concat($completedOrders ?? collect())->keyBy('id');
@endphp
<script id="kitchen-data-script" type="application/json">
@json($allKitchenOrdersMap)
</script>
<script>
    // All active & completed orders data for modal preview
    let activeOrdersMap = @json($allKitchenOrdersMap);

    function openOrderModal(orderId) {
        const order = activeOrdersMap[orderId];
        if (!order) return;

        document.getElementById('modal-queue-tag').innerText = order.queue_number;
        document.getElementById('modal-order-no').innerText = order.order_no;
        document.getElementById('modal-customer-info').innerText = `${order.customer_name} ${order.customer_phone ? '(' + order.customer_phone + ')' : ''}`;
        document.getElementById('modal-pickup-time').innerText = order.pickup_time || 'หน้าร้าน (ปรุงและส่งมอบทันที)';

        const channelBadge = document.getElementById('modal-channel-badge');
        if (order.order_type === 'online') {
            channelBadge.className = 'px-2 py-0.5 rounded-lg text-[10px] font-bold border flex items-center space-x-1 bg-emerald-50 text-emerald-800 border-emerald-200';
            channelBadge.innerHTML = `
                <svg class="w-3 h-3 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                <span>ออนไลน์ (รับเอง)</span>
            `;
        } else {
            channelBadge.className = 'px-2 py-0.5 rounded-lg text-[10px] font-bold border flex items-center space-x-1 bg-slate-100 text-slate-700 border-slate-200';
            channelBadge.innerHTML = `
                <svg class="w-3 h-3 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span>หน้าร้าน</span>
            `;
        }

        const notesBox = document.getElementById('modal-notes-box');
        const notesText = document.getElementById('modal-notes-text');
        if (order.notes) {
            notesText.innerText = order.notes;
            notesBox.classList.remove('hidden');
        } else {
            notesBox.classList.add('hidden');
        }

        const listContainer = document.getElementById('modal-items-list');
        let html = '';
        order.items.forEach(item => {
            let cookingBadge = '';
            if (item.cooking_type === 'grill') {
                if (item.grill_style === 'brush') {
                    cookingBadge = `
                        <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-900 border border-amber-200">
                            <svg class="w-3 h-3 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            <span>ย่างทาซอส</span>
                        </span>
                    `;
                } else {
                    cookingBadge = `
                        <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg text-[11px] font-bold bg-rose-50 text-rose-900 border border-rose-200">
                            <svg class="w-3 h-3 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a7 7 0 01-7 7m7-7a7 7 0 00-7-7m-7 7a7 7 0 007 7m-7-7a7 7 0 017-7"/></svg>
                            <span>ย่างจุ่มซอส</span>
                        </span>
                    `;
                }
            } else {
                const soupTitle = item.soup_style === 'mala_rich' ? 'ซุปหม่าล่าน้ำข้น' : (item.soup_style === 'tomyum_rich' ? 'ซุปต้มยำน้ำข้น' : 'ซุปน้ำดำญี่ปุ่น');
                cookingBadge = `
                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg text-[11px] font-bold bg-red-50 text-red-900 border border-red-200">
                        <svg class="w-3 h-3 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 009-9H3a9 9 0 009 9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v2m4-3v3m4-2v2"/></svg>
                        <span>${soupTitle}</span>
                    </span>
                `;
            }

            let spiceBadge = '';
            if (item.spiciness_level === 'hot') {
                spiceBadge = `
                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg text-[11px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200">
                        <svg class="w-3 h-3 fill-rose-600 stroke-rose-600" viewBox="0 0 24 24"><path d="M12 2C11.5 2 11 2.2 10.6 2.6L9.2 4H7C5.3 4 4 5.3 4 7C4 8.7 5.3 10 7 10V11C7 16 10 21 16 22C16.6 22 17 21.6 17 21C17 20.4 16.6 20 16 20C11.8 19.2 9.2 15.6 9 11.7C10.1 11.9 11.2 12 12 12C16.4 12 20 8.4 20 4C20 3.4 19.8 2.8 19.4 2.4C19 2 18.4 1.8 17.8 1.9C16.8 2.1 15.6 2.7 14.5 3.5C13.8 2.6 12.9 2 12 2Z"/></svg>
                        <span>เผ็ดมาก</span>
                    </span>
                `;
            } else if (item.spiciness_level === 'medium') {
                spiceBadge = `
                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        <svg class="w-3 h-3 fill-amber-500 stroke-amber-500" viewBox="0 0 24 24"><path d="M12 2C11.5 2 11 2.2 10.6 2.6L9.2 4H7C5.3 4 4 5.3 4 7C4 8.7 5.3 10 7 10V11C7 16 10 21 16 22C16.6 22 17 21.6 17 21C17 20.4 16.6 20 16 20C11.8 19.2 9.2 15.6 9 11.7C10.1 11.9 11.2 12 12 12C16.4 12 20 8.4 20 4C20 3.4 19.8 2.8 19.4 2.4C19 2 18.4 1.8 17.8 1.9C16.8 2.1 15.6 2.7 14.5 3.5C13.8 2.6 12.9 2 12 2Z"/></svg>
                        <span>เผ็ดกลาง</span>
                    </span>
                `;
            } else if (item.spiciness_level === 'mild') {
                spiceBadge = `
                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg text-[11px] font-medium bg-slate-50 text-slate-700 border border-slate-200">
                        <svg class="w-3 h-3 stroke-slate-500" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" /></svg>
                        <span>เผ็ดน้อย</span>
                    </span>
                `;
            } else {
                spiceBadge = `
                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg text-[11px] font-normal bg-slate-50 text-slate-400 border border-slate-200">
                        <svg class="w-3 h-3 stroke-slate-400" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6"/></svg>
                        <span>ไม่เผ็ด</span>
                    </span>
                `;
            }

            html += `
                <div class="p-3.5 bg-slate-50/80 rounded-2xl border border-slate-200/80 flex justify-between items-start text-xs">
                    <div class="space-y-1.5 flex-1 pr-3">
                        <div class="flex items-center font-bold text-slate-900 text-sm">
                            <span class="text-rose-600 font-black mr-2">${item.quantity_sticks}x</span>
                            <span>${item.product_name}</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5 pt-0.5">
                            ${cookingBadge}
                            ${spiceBadge}
                        </div>
                        ${item.special_instructions ? `
                            <div class="text-[10px] text-amber-950 bg-amber-50 px-2 py-1 rounded-lg border border-amber-200/50 mt-1 flex items-center space-x-1">
                                <svg class="w-3 h-3 text-amber-700 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                <span>${item.special_instructions}</span>
                            </div>
                        ` : ''}
                    </div>
                    <div class="px-2.5 py-1 rounded-xl bg-white border border-slate-200 font-black text-slate-700 text-xs shrink-0 shadow-xs">
                        ${item.quantity_sticks} ไม้
                    </div>
                </div>
            `;
        });
        listContainer.innerHTML = html;

        // Populate action slot in modal
        const actionSlot = document.getElementById('modal-action-slot');
        if (order.status === 'pending' || order.status === 'confirmed') {
            actionSlot.innerHTML = `
                <button onclick="updateStatus(${order.id}, 'cooking')" class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold shadow-md shadow-rose-600/20 transition-all flex items-center justify-center space-x-1.5">
                    <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" /></svg>
                    <span>เริ่มย่าง/ต้ม</span>
                </button>
            `;
        } else if (order.status === 'cooking') {
            actionSlot.innerHTML = `
                <button onclick="updateStatus(${order.id}, 'ready')" class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center space-x-1.5">
                    <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    <span>พร้อมรับแล้ว</span>
                </button>
            `;
        } else if (order.status === 'ready') {
            actionSlot.innerHTML = `
                <button onclick="updateStatus(${order.id}, 'completed')" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition-all flex items-center justify-center space-x-1.5">
                    <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                    <span>ส่งมอบอาหารแล้ว</span>
                </button>
            `;
        } else {
            actionSlot.innerHTML = '';
        }

        document.getElementById('order-detail-modal').classList.remove('hidden');
    }

    function closeOrderModal() {
        document.getElementById('order-detail-modal').classList.add('hidden');
    }

    async function updateStatus(orderId, nextStatus, markPaid = false) {
        try {
            const res = await fetch(`/pos/orders/${orderId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: nextStatus, mark_paid: Boolean(markPaid) })
            });

            const data = await res.json();
            if (data.success) {
                closeOrderModal();
                await syncKitchenOrdersSilently(true);
            } else {
                alert('เกิดข้อผิดพลาดในการอัปเดตสถานะ');
            }
        } catch (err) {
            console.error(err);
            alert('ข้อผิดพลาดเครือข่าย');
        }
    }

    // Smooth scrolling to chat panel on mobile/tablet or highlight on desktop
    function scrollToKitchenChat() {
        const chatPanel = document.getElementById('kitchen-side-chat-panel');
        if (chatPanel) {
            chatPanel.scrollIntoView({ behavior: 'smooth' });
            chatPanel.classList.add('ring-4', 'ring-rose-500/40');
            setTimeout(() => chatPanel.classList.remove('ring-4', 'ring-rose-500/40'), 1500);
        }
    }

    function scrollToKitchenOrders() {
        const ordersArea = document.getElementById('kitchen-main-orders-area');
        if (ordersArea) {
            ordersArea.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Switch embedded chat to specific order
    function openOrderInSideChat(orderNo) {
        const frame = document.getElementById('kitchen-side-chat-frame');
        if (frame) {
            frame.src = "{{ route('kitchen.chat') }}?order_no=" + encodeURIComponent(orderNo);
            localStorage.setItem('kitchen_active_chat_order', orderNo);
        }
        scrollToKitchenChat();
    }

    // SILENT REAL-TIME BACKGROUND SYNC (NO FULL PAGE RELOAD - 100% FLICKER-FREE)
    let isSyncing = false;

    async function syncKitchenOrdersSilently(isManual = false) {
        if (isSyncing) return;
        isSyncing = true;

        const refreshIcon = document.getElementById('manual-refresh-icon');
        if (isManual && refreshIcon) {
            refreshIcon.classList.add('rotate-180');
        }

        try {
            const res = await fetch(window.location.href, {
                cache: 'no-store',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) return;

            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // 1. Update Header Stats & Kitchen Orders Grid without touching search/filter bar
            const newStats = doc.getElementById('kitchen-header-stats');
            const curStats = document.getElementById('kitchen-header-stats');
            if (newStats && curStats && newStats.innerHTML !== curStats.innerHTML) {
                curStats.innerHTML = newStats.innerHTML;
            }

            const newOrders = doc.getElementById('kitchen-orders-sync-container');
            const curOrders = document.getElementById('kitchen-orders-sync-container');
            if (newOrders && curOrders && newOrders.innerHTML !== curOrders.innerHTML) {
                curOrders.innerHTML = newOrders.innerHTML;
                applyKitchenOrderFiltersAndLayout();
                updateDeliveryCountdowns();
                reapplyKitchenCollapsedCards();
            }

            // 2. Update activeOrdersMap JSON in memory
            const newScript = doc.getElementById('kitchen-data-script');
            if (newScript) {
                try {
                    activeOrdersMap = JSON.parse(newScript.textContent);
                } catch (e) {}
            }
        } catch (err) {
            // Silently ignore background polling network errors
        } finally {
            isSyncing = false;
            if (isManual && refreshIcon) {
                setTimeout(() => refreshIcon.classList.remove('rotate-180'), 500);
            }
        }
    }

    // === Kitchen Grid Columns (3 / 4), Channel Tabs, Live Search & Sort/Filter ===
    let kitchenGridColumns = Number(localStorage.getItem('kitchen_grid_columns')) === 4 ? 4 : 3;
    let kitchenSortMode = localStorage.getItem('kitchen_sort_mode') || 'newest';
    let kitchenChannelTab = localStorage.getItem('kitchen_channel_tab') || 'all';

    function setKitchenChannelTab(channel) {
        kitchenChannelTab = channel || 'all';
        localStorage.setItem('kitchen_channel_tab', kitchenChannelTab);
        renderKitchenChannelTabsUI();
        applyKitchenOrderFiltersAndLayout();
    }

    function renderKitchenChannelTabsUI() {
        document.querySelectorAll('.kitchen-channel-tab').forEach((btn) => {
            const tab = btn.dataset.channelTab || 'all';
            const isActive = tab === kitchenChannelTab;
            const pushRight = tab === 'history' ? 'ml-auto ' : '';
            btn.className = isActive
                ? `kitchen-channel-tab ${pushRight}shrink-0 whitespace-nowrap inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-extrabold transition-all bg-[#E50914] text-white shadow-xs cursor-pointer`
                : `kitchen-channel-tab ${pushRight}shrink-0 whitespace-nowrap inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold transition-all bg-slate-100 text-slate-700 hover:bg-slate-200 hover:text-slate-900 border border-slate-200/80 cursor-pointer`;
            const countBadge = btn.querySelector('span[id^="tab-count-"]');
            if (countBadge) {
                countBadge.className = isActive
                    ? 'px-1.5 py-0.2 rounded-full text-[11px] bg-white/20 text-white whitespace-nowrap'
                    : 'px-1.5 py-0.2 rounded-full text-[11px] bg-white text-slate-700 border border-slate-200 whitespace-nowrap';
            }
        });
    }

    function setKitchenGridColumns(cols) {
        kitchenGridColumns = cols === 4 ? 4 : 3;
        localStorage.setItem('kitchen_grid_columns', String(kitchenGridColumns));

        const btn3 = document.getElementById('btn-kitchen-grid-3');
        const btn4 = document.getElementById('btn-kitchen-grid-4');
        const activeCls = 'kitchen-grid-toggle-btn px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all bg-[#E50914] text-white shadow-2xs cursor-pointer';
        const inactiveCls = 'kitchen-grid-toggle-btn px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all text-slate-500 hover:text-slate-800 hover:bg-slate-100 cursor-pointer';

        if (btn3) btn3.className = kitchenGridColumns === 3 ? activeCls : inactiveCls;
        if (btn4) btn4.className = kitchenGridColumns === 4 ? activeCls : inactiveCls;

        const grid = document.getElementById('kitchen-orders-grid');
        if (grid) {
            grid.className = kitchenGridColumns === 4
                ? 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4'
                : 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5';
        }
    }

    function onKitchenSortChange(mode) {
        kitchenSortMode = mode || 'newest';
        localStorage.setItem('kitchen_sort_mode', kitchenSortMode);
        applyKitchenOrderFiltersAndLayout();
    }

    function resetKitchenFilters() {
        const searchEl = document.getElementById('kitchen-search-input');
        const sortEl = document.getElementById('kitchen-sort-select');
        if (searchEl) searchEl.value = '';
        if (sortEl) sortEl.value = 'newest';
        kitchenSortMode = 'newest';
        kitchenChannelTab = 'all';
        localStorage.setItem('kitchen_sort_mode', 'newest');
        localStorage.setItem('kitchen_channel_tab', 'all');
        renderKitchenChannelTabsUI();
        applyKitchenOrderFiltersAndLayout();
    }

    function updateKitchenChannelTabCounts(cards) {
        const counts = { all: 0, online_pickup: 0, delivery: 0, pos: 0, history: 0 };
        cards.forEach((card) => {
            if (card.dataset.isHistory === '1') {
                counts.history++;
                return;
            }
            counts.all++;
            const ch = card.dataset.channel || 'pos';
            if (Object.prototype.hasOwnProperty.call(counts, ch)) {
                counts[ch]++;
            }
        });
        Object.entries(counts).forEach(([key, val]) => {
            const el = document.getElementById(`tab-count-${key}`);
            if (el) el.textContent = String(val);
        });
    }

    function applyKitchenOrderFiltersAndLayout() {
        setKitchenGridColumns(kitchenGridColumns);
        renderKitchenChannelTabsUI();

        const grid = document.getElementById('kitchen-orders-grid');
        const countEl = document.getElementById('kitchen-visible-count');
        const emptyEl = document.getElementById('kitchen-filter-empty');
        if (!grid) {
            updateKitchenChannelTabCounts([]);
            if (countEl) countEl.textContent = '0';
            if (emptyEl) emptyEl.classList.add('hidden');
            return;
        }

        const query = (document.getElementById('kitchen-search-input')?.value || '').trim().toLowerCase();
        const cards = Array.from(grid.querySelectorAll('.kitchen-order-card'));
        updateKitchenChannelTabCounts(cards);

        const statusPriorityCooking = { cooking: 0, confirmed: 1, pending: 1, ready: 2 };
        const statusPriorityReady = { ready: 0, cooking: 1, confirmed: 2, pending: 2 };

        cards.sort((a, b) => {
            const tsA = Number(a.dataset.createdTs || 0);
            const tsB = Number(b.dataset.createdTs || 0);
            const typeA = a.dataset.orderType || 'pos';
            const typeB = b.dataset.orderType || 'pos';
            const stA = a.dataset.status || '';
            const stB = b.dataset.status || '';

            switch (kitchenSortMode) {
                case 'oldest':
                    return tsA - tsB;
                case 'online_first':
                    if (typeA !== typeB) return typeA === 'online' ? -1 : 1;
                    return tsB - tsA;
                case 'pos_first':
                    if (typeA !== typeB) return typeA === 'pos' ? -1 : 1;
                    return tsB - tsA;
                case 'cooking_first':
                    if ((statusPriorityCooking[stA] ?? 9) !== (statusPriorityCooking[stB] ?? 9)) {
                        return (statusPriorityCooking[stA] ?? 9) - (statusPriorityCooking[stB] ?? 9);
                    }
                    return tsB - tsA;
                case 'ready_first':
                    if ((statusPriorityReady[stA] ?? 9) !== (statusPriorityReady[stB] ?? 9)) {
                        return (statusPriorityReady[stA] ?? 9) - (statusPriorityReady[stB] ?? 9);
                    }
                    return tsB - tsA;
                case 'newest':
                default:
                    return tsB - tsA;
            }
        });

        let visibleCount = 0;
        cards.forEach((card) => {
            grid.appendChild(card);

            const haystack = card.dataset.search || '';
            const type = card.dataset.orderType || 'pos';
            const channel = card.dataset.channel || 'pos';
            const status = card.dataset.status || '';
            const isHistory = (card.dataset.isHistory === '1');

            let show = false;
            const matchesQuery = !query || haystack.includes(query);

            if (kitchenChannelTab === 'history') {
                show = isHistory && matchesQuery;
            } else if (!isHistory) {
                const matchesChannelTab = (kitchenChannelTab === 'all') || (channel === kitchenChannelTab);
                let matchesFilter = true;
                if (kitchenSortMode === 'filter_online') matchesFilter = (type === 'online');
                else if (kitchenSortMode === 'filter_pos') matchesFilter = (type === 'pos');
                else if (kitchenSortMode === 'filter_cooking') matchesFilter = (status === 'cooking');
                else if (kitchenSortMode === 'filter_pending') matchesFilter = (status === 'pending' || status === 'confirmed');
                show = matchesChannelTab && matchesFilter && matchesQuery;
            }

            card.classList.toggle('hidden', !show);
            if (show) visibleCount++;
        });

        if (countEl) countEl.textContent = String(visibleCount);
        if (emptyEl) emptyEl.classList.toggle('hidden', visibleCount > 0);
    }

    const collapsedKitchenCards = new Set();

    function applyKitchenCardExpandState(orderId, collapsed) {
        const extraItems = document.querySelectorAll(`.extra-kitchen-item-${orderId}`);
        const summary = document.getElementById(`kitchen-order-extra-summary-${orderId}`);
        const label = document.getElementById(`toggle-kitchen-more-label-${orderId}`);
        const icon = document.getElementById(`toggle-kitchen-more-icon-${orderId}`);
        if (!label || !icon) return;

        extraItems.forEach((el) => el.classList.toggle('hidden', collapsed));
        if (summary) summary.classList.toggle('hidden', collapsed);

        if (collapsed) {
            label.textContent = 'ดูเพิ่มเติม';
            icon.classList.remove('rotate-180');
        } else {
            label.textContent = 'แสดงน้อยลง';
            icon.classList.add('rotate-180');
        }
    }

    function toggleKitchenCardMoreItems(orderId) {
        const isCollapsed = collapsedKitchenCards.has(orderId);
        if (isCollapsed) {
            collapsedKitchenCards.delete(orderId);
            applyKitchenCardExpandState(orderId, false);
        } else {
            collapsedKitchenCards.add(orderId);
            applyKitchenCardExpandState(orderId, true);
        }
    }

    function reapplyKitchenCollapsedCards() {
        collapsedKitchenCards.forEach((oid) => applyKitchenCardExpandState(oid, true));
    }

    document.addEventListener('DOMContentLoaded', () => {
        const sortEl = document.getElementById('kitchen-sort-select');
        if (sortEl) sortEl.value = kitchenSortMode;
        applyKitchenOrderFiltersAndLayout();
        updateDeliveryCountdowns();
    });

    // Update 10-minute cooking countdown bars every second
    function updateDeliveryCountdowns() {
        const nowSec = Math.floor(Date.now() / 1000);
        document.querySelectorAll('.delivery-countdown-pill').forEach((pill) => {
            const startedTs = Number(pill.dataset.startedTs || 0);
            const duration = Number(pill.dataset.duration || 600);
            const textEl = pill.querySelector('.countdown-text');
            if (!startedTs || !textEl) return;

            const remaining = (startedTs + duration) - nowSec;
            if (remaining >= 0) {
                const mins = Math.floor(remaining / 60);
                const secs = remaining % 60;
                textEl.textContent = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                pill.className = remaining <= 120
                    ? 'delivery-countdown-pill flex-1 py-2.5 px-2.5 rounded-2xl bg-amber-50 border border-amber-300 text-amber-800 text-xs font-black flex items-center justify-center gap-1.5 whitespace-nowrap'
                    : 'delivery-countdown-pill flex-1 py-2.5 px-2.5 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs font-black flex items-center justify-center gap-1.5 whitespace-nowrap';
            } else {
                const over = Math.abs(remaining);
                const mins = Math.floor(over / 60);
                const secs = over % 60;
                textEl.textContent = `+${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                pill.className = 'delivery-countdown-pill flex-1 py-2.5 px-2.5 rounded-2xl bg-rose-50 border border-rose-300 text-rose-700 text-xs font-black flex items-center justify-center gap-1.5 whitespace-nowrap';
            }
        });
    }
    setInterval(updateDeliveryCountdowns, 1000);

    // === DELIVERY STEP 1 / 2 / 3 WORKFLOW CONTROLLERS ===
    const kitchenActiveProducts = @json($activeProducts ?? []);
    const csrfMetaToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    async function updateDeliveryStep(orderId, action) {
        try {
            const res = await fetch(`/pos/orders/${orderId}/kitchen-step`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMetaToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ action }),
            });
            const data = await res.json();
            if (data.success) {
                await syncKitchenOrdersSilently(true);
                updateDeliveryCountdowns();
            } else {
                alert(data.message || 'ไม่สามารถอัปเดตขั้นตอนได้');
            }
        } catch (err) {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        }
    }

    // 1) STEP 1: Edit Delivery Order Items Modal
    let editingDeliveryOrderId = null;
    let editingDeliveryItems = [];
    let editingDeliveryOriginalTotal = 0;

    function openDeliveryEditModal(orderId) {
        const order = activeOrdersMap[orderId];
        if (!order) return;

        editingDeliveryOrderId = orderId;
        editingDeliveryOriginalTotal = Number(order.total_amount || 0);
        editingDeliveryItems = (order.items || []).map((it) => ({
            product_id: it.product_id || null,
            product_name: it.product_name,
            quantity_sticks: Number(it.quantity_sticks || 1),
            unit_price: Number(it.unit_price || 0),
            cooking_type: it.cooking_type || 'grill',
            grill_style: it.grill_style || 'brush',
            soup_style: it.soup_style || 'mala_rich',
            spiciness_level: it.spiciness_level || 'medium',
            special_instructions: it.special_instructions || '',
        }));

        document.getElementById('edit-deliv-queue-tag').textContent = order.queue_number || '-';
        document.getElementById('edit-deliv-order-no').textContent = order.order_no || '-';
        document.getElementById('edit-deliv-address').value = order.pickup_time || '';
        document.getElementById('edit-deliv-notes').value = order.notes || '';
        document.getElementById('edit-deliv-product-search').value = '';

        filterDeliveryEditProducts();
        renderDeliveryEditItems();

        const modal = document.getElementById('delivery-edit-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeliveryEditModal() {
        const modal = document.getElementById('delivery-edit-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        editingDeliveryOrderId = null;
    }

    function filterDeliveryEditProducts() {
        const query = (document.getElementById('edit-deliv-product-search')?.value || '').trim().toLowerCase();
        const container = document.getElementById('edit-deliv-product-results');
        if (!container) return;

        const filtered = (kitchenActiveProducts || []).filter((p) =>
            !query || String(p.name || '').toLowerCase().includes(query)
        ).slice(0, 18);

        if (filtered.length === 0) {
            container.innerHTML = `<div class="col-span-full text-center text-xs text-slate-400 py-3">ไม่พบเมนูที่ค้นหา</div>`;
            return;
        }

        container.innerHTML = filtered.map((p) => `
            <button type="button" onclick="addProductToEditingOrder(${p.id})"
                class="flex items-center justify-between gap-2 px-2.5 py-2 rounded-xl bg-white hover:bg-rose-50/70 border border-slate-200 hover:border-rose-300 text-left transition cursor-pointer">
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-bold text-slate-800 truncate">${String(p.name || '').replace(/</g, '&lt;')}</div>
                    <div class="text-[10.5px] text-slate-500 font-semibold">฿${Number(p.price_per_stick || 0).toFixed(0)}/ไม้</div>
                </div>
                <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 stroke-current" fill="none" stroke-width="2.4" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </span>
            </button>
        `).join('');
    }

    function addProductToEditingOrder(productId) {
        const prod = (kitchenActiveProducts || []).find((p) => Number(p.id) === Number(productId));
        if (!prod) return;

        const existingIdx = editingDeliveryItems.findIndex(
            (it) => Number(it.product_id) === Number(prod.id) && it.cooking_type === 'grill' && it.spiciness_level === 'medium'
        );
        if (existingIdx >= 0) {
            editingDeliveryItems[existingIdx].quantity_sticks += 1;
        } else {
            editingDeliveryItems.push({
                product_id: prod.id,
                product_name: prod.name,
                quantity_sticks: 1,
                unit_price: Number(prod.price_per_stick || 0),
                cooking_type: 'grill',
                grill_style: 'brush',
                soup_style: 'mala_rich',
                spiciness_level: 'medium',
                special_instructions: '',
            });
        }
        renderDeliveryEditItems();
    }

    function changeEditingItemQty(idx, delta) {
        if (!editingDeliveryItems[idx]) return;
        const nextQty = editingDeliveryItems[idx].quantity_sticks + delta;
        if (nextQty <= 0) {
            editingDeliveryItems.splice(idx, 1);
        } else {
            editingDeliveryItems[idx].quantity_sticks = nextQty;
        }
        renderDeliveryEditItems();
    }

    function removeEditingItem(idx) {
        editingDeliveryItems.splice(idx, 1);
        renderDeliveryEditItems();
    }

    function updateEditingItemField(idx, field, val) {
        if (!editingDeliveryItems[idx]) return;
        editingDeliveryItems[idx][field] = val;
        if (field === 'cooking_type') {
            renderDeliveryEditItems();
        }
    }

    function renderDeliveryEditItems() {
        const listEl = document.getElementById('edit-deliv-items-list');
        if (!listEl) return;

        if (editingDeliveryItems.length === 0) {
            listEl.innerHTML = `<div class="p-6 text-center text-xs text-slate-400 bg-slate-50 rounded-2xl border border-dashed border-slate-200">ยังไม่มีรายการอาหาร กรุณาค้นหาเมนูด้านบนเพื่อเพิ่ม</div>`;
        } else {
            listEl.innerHTML = editingDeliveryItems.map((it, idx) => {
                const lineTotal = Number(it.unit_price || 0) * Number(it.quantity_sticks || 0);
                return `
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-extrabold text-slate-900 truncate">${String(it.product_name || '').replace(/</g, '&lt;')}</div>
                                <div class="text-[11px] text-slate-500">฿${Number(it.unit_price || 0).toFixed(0)}/ไม้ • รวม <strong class="text-slate-900">฿${lineTotal.toFixed(2)}</strong></div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button" onclick="changeEditingItemQty(${idx}, -1)"
                                    class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 font-bold flex items-center justify-center cursor-pointer">-</button>
                                <span class="w-7 text-center text-xs font-black text-slate-900">${it.quantity_sticks}</span>
                                <button type="button" onclick="changeEditingItemQty(${idx}, 1)"
                                    class="w-7 h-7 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-bold flex items-center justify-center cursor-pointer">+</button>
                                <button type="button" onclick="removeEditingItem(${idx})"
                                    class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-300 flex items-center justify-center ml-1 cursor-pointer" title="ลบรายการนี้">
                                    <svg class="w-3.5 h-3.5 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <select onchange="updateEditingItemField(${idx}, 'cooking_type', this.value)"
                                class="px-2 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 font-semibold">
                                <option value="grill" ${it.cooking_type === 'grill' ? 'selected' : ''}>ปิ้งย่าง</option>
                                <option value="soup" ${it.cooking_type === 'soup' ? 'selected' : ''}>ต้มน้ำซุป</option>
                            </select>
                            <select onchange="updateEditingItemField(${idx}, 'spiciness_level', this.value)"
                                class="px-2 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 font-semibold">
                                <option value="none" ${it.spiciness_level === 'none' ? 'selected' : ''}>ไม่เผ็ด</option>
                                <option value="mild" ${it.spiciness_level === 'mild' ? 'selected' : ''}>เผ็ดน้อย</option>
                                <option value="medium" ${it.spiciness_level === 'medium' ? 'selected' : ''}>เผ็ดกลาง</option>
                                <option value="hot" ${it.spiciness_level === 'hot' ? 'selected' : ''}>เผ็ดมาก</option>
                            </select>
                        </div>
                    </div>
                `;
            }).join('');
        }

        const totalSticks = editingDeliveryItems.reduce((acc, it) => acc + Number(it.quantity_sticks || 0), 0);
        const order = activeOrdersMap[editingDeliveryOrderId] || {};
        const subtotal = editingDeliveryItems.reduce((acc, it) => acc + (Number(it.unit_price || 0) * Number(it.quantity_sticks || 0)), 0);
        const newTotal = Math.max(0, subtotal + Number(order.soup_extra || 0) - Number(order.discount || 0));

        document.getElementById('edit-deliv-total-sticks').textContent = `${totalSticks} ไม้`;
        document.getElementById('edit-deliv-old-total').textContent = `฿${editingDeliveryOriginalTotal.toFixed(2)}`;
        document.getElementById('edit-deliv-new-total').textContent = `฿${newTotal.toFixed(2)}`;
    }

    async function saveDeliveryEditModal() {
        if (!editingDeliveryOrderId) return;
        if (editingDeliveryItems.length === 0) {
            alert('กรุณาเพิ่มรายการอาหารอย่างน้อย 1 รายการ');
            return;
        }
        const btn = document.getElementById('btn-save-delivery-edit');
        if (btn) btn.disabled = true;

        try {
            const res = await fetch(`/pos/orders/${editingDeliveryOrderId}/items`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMetaToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    items: editingDeliveryItems,
                    pickup_time: document.getElementById('edit-deliv-address')?.value || '',
                    notes: document.getElementById('edit-deliv-notes')?.value || '',
                }),
            });
            const data = await res.json();
            if (res.ok && data.success) {
                closeDeliveryEditModal();
                await syncKitchenOrdersSilently(true);
            } else {
                alert(data.message || 'ไม่สามารถบันทึกการแก้ไขออเดอร์ได้');
            }
        } catch (err) {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // 2) STEP 1: Admin PIN (1122) Cancel Order Modal
    let pinCancelOrderId = null;

    function openPinCancelModal(orderId) {
        pinCancelOrderId = orderId;
        const input = document.getElementById('kitchen-cancel-pin-input');
        const err = document.getElementById('kitchen-cancel-pin-error');
        if (input) input.value = '';
        if (err) err.classList.add('hidden');
        const modal = document.getElementById('kitchen-pin-cancel-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => input?.focus(), 60);
    }

    function closePinCancelModal() {
        const modal = document.getElementById('kitchen-pin-cancel-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        pinCancelOrderId = null;
    }

    async function submitPinCancelOrder() {
        if (!pinCancelOrderId) return;
        const input = document.getElementById('kitchen-cancel-pin-input');
        const err = document.getElementById('kitchen-cancel-pin-error');
        const pin = (input?.value || '').trim();

        if (pin !== '1122') {
            if (err) {
                err.textContent = 'รหัส PIN ไม่ถูกต้อง (กรุณากรอก 1122)';
                err.classList.remove('hidden');
            }
            input?.focus();
            return;
        }

        const btn = document.getElementById('btn-confirm-pin-cancel');
        if (btn) btn.disabled = true;

        try {
            const res = await fetch(`/pos/orders/${pinCancelOrderId}/cancel-with-pin`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMetaToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ pin }),
            });
            const data = await res.json();
            if (res.ok && data.success) {
                closePinCancelModal();
                await syncKitchenOrdersSilently(true);
            } else if (err) {
                err.textContent = data.message || 'ไม่สามารถยกเลิกออเดอร์ได้';
                err.classList.remove('hidden');
            }
        } catch (e) {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // 3) STEP 3: Open Delivery Location in Google Maps + Complete Summary Modal
    function openDeliveryLocation(orderId) {
        const order = activeOrdersMap[orderId];
        if (!order) return;

        let rawAddress = String(order.pickup_time || '').replace(/^เดลิเวอรี่\s*:\s*/i, '').trim();
        if (!rawAddress || rawAddress === 'ทันที (~15-20 นาที)') {
            rawAddress = String(order.notes || '').trim();
        }
        if (!rawAddress) {
            alert('ไม่พบข้อมูลที่อยู่จัดส่งสำหรับออเดอร์นี้');
            return;
        }
        const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(rawAddress)}`;
        window.open(mapsUrl, '_blank', 'noopener');
    }

    let completeDeliveryOrderId = null;
    let completeDeliveryExpectedTotal = 0;

    function openDeliveryCompleteModal(orderId) {
        const order = activeOrdersMap[orderId];
        if (!order) return;

        completeDeliveryOrderId = orderId;
        completeDeliveryExpectedTotal = Number(order.total_amount || 0);

        document.getElementById('complete-deliv-queue-tag').textContent = order.queue_number || '-';
        document.getElementById('complete-deliv-customer').textContent = order.customer_name || '-';
        document.getElementById('complete-deliv-total').textContent = `฿${completeDeliveryExpectedTotal.toFixed(2)}`;
        document.getElementById('complete-deliv-note').value = order.delivery_note || '';

        const isCash = order.payment_method === 'cash';
        setCompletePaymentMode(isCash ? 'cash' : 'transfer');

        const modal = document.getElementById('delivery-complete-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeliveryCompleteModal() {
        const modal = document.getElementById('delivery-complete-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        completeDeliveryOrderId = null;
    }

    function setCompletePaymentMode(mode) {
        const cashInput = document.getElementById('complete-deliv-cash');
        const transferInput = document.getElementById('complete-deliv-transfer');
        if (!cashInput || !transferInput) return;

        const total = completeDeliveryExpectedTotal;
        if (mode === 'cash') {
            cashInput.value = total.toFixed(2);
            transferInput.value = '0.00';
        } else if (mode === 'transfer') {
            cashInput.value = '0.00';
            transferInput.value = total.toFixed(2);
        } else {
            const halfCash = Math.floor(total / 2);
            cashInput.value = halfCash.toFixed(2);
            transferInput.value = Math.max(0, total - halfCash).toFixed(2);
        }

        document.querySelectorAll('.deliv-pay-mode-btn').forEach((btn) => {
            const active = btn.dataset.mode === mode;
            btn.className = active
                ? 'deliv-pay-mode-btn py-2 px-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold border border-slate-900 transition cursor-pointer whitespace-nowrap'
                : 'deliv-pay-mode-btn py-2 px-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold border border-slate-200 transition cursor-pointer whitespace-nowrap';
        });

        recalcCompletePaymentSummary();
    }

    function onCompletePaymentInput(changedField) {
        const cashInput = document.getElementById('complete-deliv-cash');
        const transferInput = document.getElementById('complete-deliv-transfer');
        if (!cashInput || !transferInput) return;

        const total = completeDeliveryExpectedTotal;
        if (changedField === 'cash') {
            const c = Math.max(0, parseFloat(cashInput.value || '0'));
            if (c <= total) {
                transferInput.value = Math.max(0, total - c).toFixed(2);
            }
        }
        recalcCompletePaymentSummary();
    }

    function recalcCompletePaymentSummary() {
        const cashVal = Math.max(0, parseFloat(document.getElementById('complete-deliv-cash')?.value || '0'));
        const transferVal = Math.max(0, parseFloat(document.getElementById('complete-deliv-transfer')?.value || '0'));
        const sum = cashVal + transferVal;
        const diff = sum - completeDeliveryExpectedTotal;

        const sumEl = document.getElementById('complete-deliv-received-sum');
        const statusEl = document.getElementById('complete-deliv-match-status');
        if (sumEl) sumEl.textContent = `฿${sum.toFixed(2)}`;
        if (statusEl) {
            if (Math.abs(diff) < 0.01) {
                statusEl.textContent = 'ยอดตรงตามบิลครบถ้วน ✓';
                statusEl.className = 'text-[11px] font-bold text-emerald-700';
            } else if (diff > 0) {
                statusEl.textContent = `รับเกินบิล +฿${diff.toFixed(2)}`;
                statusEl.className = 'text-[11px] font-bold text-amber-700';
            } else {
                statusEl.textContent = `ยังขาดอีก ฿${Math.abs(diff).toFixed(2)}`;
                statusEl.className = 'text-[11px] font-bold text-rose-600';
            }
        }
    }

    async function confirmDeliveryCompleteModal() {
        if (!completeDeliveryOrderId) return;
        const cashAmount = Math.max(0, parseFloat(document.getElementById('complete-deliv-cash')?.value || '0'));
        const transferAmount = Math.max(0, parseFloat(document.getElementById('complete-deliv-transfer')?.value || '0'));
        const note = document.getElementById('complete-deliv-note')?.value || '';

        const btn = document.getElementById('btn-confirm-delivery-complete');
        if (btn) btn.disabled = true;

        try {
            const res = await fetch(`/pos/orders/${completeDeliveryOrderId}/complete-delivery`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMetaToken(),
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    cash_amount: cashAmount,
                    transfer_amount: transferAmount,
                    delivery_note: note,
                }),
            });
            const data = await res.json();
            if (res.ok && data.success) {
                closeDeliveryCompleteModal();
                await syncKitchenOrdersSilently(true);
            } else {
                alert(data.message || 'ไม่สามารถยืนยันปิดออเดอร์ได้');
            }
        } catch (err) {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    // Background silent sync every 4 seconds - ZERO screen flickering, ZERO iframe reload!
    setInterval(() => syncKitchenOrdersSilently(false), 4000);
</script>

<!-- MODAL A: Delivery Step 1 - Edit Order Items Modal -->
<div id="delivery-edit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/65 backdrop-blur-xs p-4"
     onclick="if(event.target === this) closeDeliveryEditModal()">
    <div class="bg-white rounded-3xl max-w-xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden">
        <!-- Header -->
        <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <span id="edit-deliv-queue-tag" class="px-3 py-1 rounded-xl bg-slate-900 text-white font-black text-lg">-</span>
                <div>
                    <h3 class="font-black text-sm text-slate-900">แก้ไขรายการออร์เดอร์ (Delivery)</h3>
                    <p class="text-[11px] text-slate-500">เลขบิล: <span id="edit-deliv-order-no" class="font-mono font-bold">-</span></p>
                </div>
            </div>
            <button type="button" onclick="closeDeliveryEditModal()" class="w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-500 hover:text-slate-900 flex items-center justify-center cursor-pointer">
                <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Top Search Bar to Add More Menu Items -->
        <div class="px-5 pt-3.5 pb-3 border-b border-slate-100 bg-slate-50/50 space-y-2.5 shrink-0">
            <div class="relative">
                <input type="text" id="edit-deliv-product-search" oninput="filterDeliveryEditProducts()"
                    placeholder="ค้นหาเมนูอาหารเพื่อเพิ่มเข้าออร์เดอร์..."
                    class="w-full pl-9 pr-3 py-2 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-800 focus:outline-none focus:border-slate-900">
                <svg class="w-4 h-4 text-slate-400 stroke-current absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <div id="edit-deliv-product-results" class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-36 overflow-y-auto pr-0.5"></div>
        </div>

        <!-- Middle Existing Order Items + Address/Notes -->
        <div class="p-5 overflow-y-auto flex-1 space-y-3">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wide">รายการในออร์เดอร์</div>
            <div id="edit-deliv-items-list" class="space-y-2"></div>

            <div class="pt-2 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                <div>
                    <label class="text-[11px] font-bold text-slate-600 block mb-1">ที่อยู่จัดส่ง / นัดรับ</label>
                    <input type="text" id="edit-deliv-address" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs text-slate-800">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-600 block mb-1">หมายเหตุออร์เดอร์</label>
                    <input type="text" id="edit-deliv-notes" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs text-slate-800">
                </div>
            </div>
        </div>

        <!-- Bottom New Summary Bar -->
        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200 space-y-3 shrink-0">
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-3">
                    <span class="text-slate-500">รวม <strong id="edit-deliv-total-sticks" class="text-slate-900">0 ไม้</strong></span>
                    <span class="text-slate-400">ยอดเดิม: <span id="edit-deliv-old-total" class="line-through">฿0.00</span></span>
                </div>
                <div class="text-right">
                    <span class="text-slate-600 font-bold mr-1">ยอดใหม่สุทธิ:</span>
                    <span id="edit-deliv-new-total" class="text-lg font-black text-[#E50914] font-mono">฿0.00</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <button type="button" onclick="closeDeliveryEditModal()"
                    class="py-2.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs cursor-pointer">
                    ยกเลิก
                </button>
                <button type="button" id="btn-save-delivery-edit" onclick="saveDeliveryEditModal()"
                    class="py-2.5 rounded-2xl bg-[#E50914] hover:bg-[#C8102E] text-white font-bold text-xs shadow-sm cursor-pointer">
                    บันทึกการแก้ไขออร์เดอร์
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL B: Delivery Step 1 - Admin PIN Cancel Modal (ตรงตามภาพที่ 2 เป๊ะ) -->
<div id="kitchen-pin-cancel-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
     onclick="if(event.target === this) closePinCancelModal()">
    <div class="bg-white rounded-[28px] max-w-sm w-full p-6 sm:p-7 shadow-2xl border border-slate-100 text-center">
        <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-center mx-auto mb-3 text-slate-800">
            <svg class="w-6 h-6 stroke-current" fill="none" stroke-width="1.9" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.97-3.05 9.15-7 10-3.95-.85-7-5.03-7-10V6l7-3z" />
                <circle cx="12" cy="11" r="1.3" fill="currentColor" stroke="none" />
                <path stroke-linecap="round" d="M12 12.3v2.4" />
            </svg>
        </div>
        <h3 class="text-lg font-black text-slate-900 tracking-tight">รหัส PIN ผู้ดูแลระบบ</h3>
        <p class="text-xs text-slate-500 mt-1">กรอกรหัส PIN (1122) เพื่อยืนยันการลบและยกเลิกออร์เดอร์</p>

        <input type="password"
               id="kitchen-cancel-pin-input"
               maxlength="10"
               inputmode="numeric"
               autocomplete="off"
               placeholder="••••"
               onkeydown="if(event.key === 'Enter'){ event.preventDefault(); submitPinCancelOrder(); }"
               class="mt-4 w-full px-4 py-3 rounded-2xl border border-slate-300 text-center text-lg font-bold tracking-[0.45em] text-slate-900 focus:outline-none focus:border-slate-900">

        <p id="kitchen-cancel-pin-error" class="hidden text-xs font-bold text-rose-600 mt-2"></p>

        <div class="grid grid-cols-2 gap-3 mt-4">
            <button type="button" onclick="closePinCancelModal()"
                class="py-2.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-sm cursor-pointer">
                ยกเลิก
            </button>
            <button type="button" id="btn-confirm-pin-cancel" onclick="submitPinCancelOrder()"
                class="py-2.5 rounded-2xl bg-[#0F172A] hover:bg-slate-800 text-white font-bold text-sm shadow-md cursor-pointer">
                ยืนยัน
            </button>
        </div>
    </div>
</div>

<!-- MODAL C: Delivery Step 3 - Complete Delivery Summary & Payment Split Modal -->
<div id="delivery-complete-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/65 backdrop-blur-xs p-4"
     onclick="if(event.target === this) closeDeliveryCompleteModal()">
    <div class="bg-white rounded-3xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
                <span id="complete-deliv-queue-tag" class="px-3 py-1 rounded-xl bg-emerald-600 text-white font-black text-base">-</span>
                <div>
                    <h3 class="text-sm font-black text-slate-900">สรุปยอดก่อนยืนยันส่งสำเร็จ</h3>
                    <p class="text-[11px] text-slate-500">ลูกค้า: <span id="complete-deliv-customer" class="font-bold text-slate-700">-</span></p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[10.5px] text-slate-400 block">ยอดตามบิล</span>
                <strong id="complete-deliv-total" class="text-lg font-black text-[#E50914] font-mono">฿0.00</strong>
            </div>
        </div>

        <!-- Quick Split Mode Selector -->
        <div class="grid grid-cols-3 gap-2">
            <button type="button" data-mode="transfer" onclick="setCompletePaymentMode('transfer')"
                class="deliv-pay-mode-btn py-2 px-2.5 rounded-xl text-xs font-bold border transition cursor-pointer whitespace-nowrap">
                เงินโอนทั้งหมด
            </button>
            <button type="button" data-mode="cash" onclick="setCompletePaymentMode('cash')"
                class="deliv-pay-mode-btn py-2 px-2.5 rounded-xl text-xs font-bold border transition cursor-pointer whitespace-nowrap">
                เงินสดทั้งหมด
            </button>
            <button type="button" data-mode="split" onclick="setCompletePaymentMode('split')"
                class="deliv-pay-mode-btn py-2 px-2.5 rounded-xl text-xs font-bold border transition cursor-pointer whitespace-nowrap">
                แบ่งจ่าย
            </button>
        </div>

        <!-- Cash & Transfer Inputs -->
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-[11px] font-bold text-slate-700 block mb-1">เงินสด (บาท)</label>
                <input type="number" step="0.01" min="0" id="complete-deliv-cash" oninput="onCompletePaymentInput('cash')"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm font-mono font-bold text-slate-900 focus:outline-none focus:border-slate-900">
            </div>
            <div>
                <label class="text-[11px] font-bold text-slate-700 block mb-1">เงินโอน (บาท)</label>
                <input type="number" step="0.01" min="0" id="complete-deliv-transfer" oninput="recalcCompletePaymentSummary()"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm font-mono font-bold text-slate-900 focus:outline-none focus:border-slate-900">
            </div>
        </div>

        <div class="px-3.5 py-2 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-600">ยอดรับรวม: <strong id="complete-deliv-received-sum" class="font-mono text-slate-900">฿0.00</strong></span>
            <span id="complete-deliv-match-status" class="text-[11px] font-bold text-emerald-700">ยอดตรงตามบิลครบถ้วน ✓</span>
        </div>

        <!-- Delivery Note Textbox -->
        <div>
            <label class="text-[11px] font-bold text-slate-700 block mb-1">หมายเหตุ</label>
            <textarea id="complete-deliv-note" rows="2" placeholder="ระบุหมายเหตุเพิ่มเติม (ถ้ามี)..."
                class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 focus:outline-none focus:border-slate-900"></textarea>
        </div>

        <!-- 2 Bottom Buttons -->
        <div class="grid grid-cols-2 gap-2.5 pt-1">
            <button type="button" onclick="closeDeliveryCompleteModal()"
                class="py-2.5 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs cursor-pointer whitespace-nowrap">
                กลับไปหน้าทำรายการ
            </button>
            <button type="button" id="btn-confirm-delivery-complete" onclick="confirmDeliveryCompleteModal()"
                class="py-2.5 rounded-2xl bg-[#059669] hover:bg-[#047857] text-white font-bold text-xs shadow-sm cursor-pointer whitespace-nowrap">
                ยืนยันถูกต้องแล้ว
            </button>
        </div>
    </div>
</div>
@endpush
@endsection
