<!DOCTYPE html>
<html lang="th" class="h-full bg-[#111317]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ร้านหม่าล่าสตรีท (Mala Street) - สั่งอาหารออนไลน์ Takeaway & Delivery</title>

    <!-- Google Fonts: Prompt -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('fonts/mala-street-font.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Prompt', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-tap-highlight-color: transparent;
        }
        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-none {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        /* Custom Chat Button Styles: ปกติสีดำ, วางเมาส์หรือกดเป็นสีเขียวอ่อนสดใส */
        .btn-chat-custom {
            background-color: #101114 !important;
            color: #ffffff !important;
            border: 1px solid #2d3748 !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2) !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            cursor: pointer !important;
        }
        .btn-chat-custom svg {
            color: #ffffff !important;
            stroke: #ffffff !important;
            transition: transform 0.2s ease, stroke 0.2s ease !important;
        }
        .btn-chat-custom .chat-dot {
            background-color: #34d399 !important;
            transition: background-color 0.2s ease !important;
        }
        /* Hover (วางเมาส์/ลากเมาส์ไปชี้) เป็นสีเขียวอ่อนสดใส */
        .btn-chat-custom:hover {
            background-color: #10B981 !important; /* เขียวสดใส */
            border-color: #059669 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.45) !important;
            transform: translateY(-1px) scale(1.02);
        }
        .btn-chat-custom:hover svg {
            transform: scale(1.1);
        }
        .btn-chat-custom:hover .chat-dot {
            background-color: #ffffff !important;
        }
        /* Active / Click (เมื่อทำการกด) เป็นสีเขียวอ่อนสดใส */
        .btn-chat-custom:active,
        .btn-chat-custom:focus {
            background-color: #34D399 !important; /* เขียวอ่อนสดใส */
            border-color: #10B981 !important;
            color: #022c22 !important;
            transform: scale(0.95) !important;
        }
        .btn-chat-custom:active svg,
        .btn-chat-custom:focus svg {
            stroke: #022c22 !important;
        }
        .btn-chat-custom:active .chat-dot,
        .btn-chat-custom:focus .chat-dot {
            background-color: #065f46 !important;
        }
    </style>
</head>
<body class="min-h-screen bg-[#111317] flex justify-center items-start selection:bg-[#E60018] selection:text-white font-prompt text-[#101114] antialiased">

@php
    $categoryImageMap = [
        'beef' => 'assets/categories/beef.png',
        'pork-chicken' => 'assets/categories/pork-chicken.png',
        'meatball-sausage' => 'assets/categories/meatballs.png',
        'vegetables' => 'assets/categories/vegetables.png',
        'tofu-noodles' => 'assets/categories/tofu.png',
        'snacks' => 'assets/categories/snacks.png',
        'beverages' => 'assets/categories/drinks.png',
    ];

    $CategoryShortNames = [
        'beef' => 'เนื้อโคขุน',
        'pork-chicken' => 'หมู & ไก่',
        'meatball-sausage' => 'ลูกชิ้น',
        'vegetables' => 'ผัก',
        'tofu-noodles' => 'เต้าหู้ & เส้น',
        'snacks' => 'ของทานเล่น',
        'beverages' => 'เครื่องดื่ม',
    ];

    $allProducts = $categories->flatMap(fn($c) => $c->products)->sortBy('sort_order')->values();
    $totalCount = $allProducts->count();
    $recommendedList = $recommendedProducts ?? collect();
@endphp

    <!-- ========================================================================= -->
    <!-- STANDALONE MOBILE STOREFRONT CONTAINER                                    -->
    <!-- ========================================================================= -->
    <div class="w-full max-w-[430px] min-h-screen bg-[#F6F7F9] shadow-[0_0_60px_rgba(0,0,0,0.6)] relative pb-28 overflow-x-hidden select-none">

        <!-- ===================================================================== -->
        <!-- 1. HERO RESTAURANT BANNER & FLOATING ACTION BUTTONS                   -->
        <!-- ===================================================================== -->
        <section class="relative w-full overflow-hidden bg-[#400306] aspect-[592/195]" style="aspect-ratio: 592/195;">
            <img id="online-hero-banner-img"
                 src="{{ $heroBannerImage ?? asset('assets/online/hero-banner-ref.png') }}"
                 alt="หม่าล่า Mala Street เผ็ดชา ต้นตำรับ"
                 class="w-full h-full block object-cover select-none pointer-events-none transition-all duration-150"
                 style="object-position: {{ $heroBannerPosition['x'] ?? 50 }}% {{ $heroBannerPosition['y'] ?? 50 }}%; transform: scale({{ $heroBannerPosition['scale'] ?? 1 }});">

            <!-- Stable Floating Action Buttons Over Banner -->
            <div class="absolute top-3.5 inset-x-3.5 flex items-center justify-between z-20 pointer-events-auto">
                <!-- Top-Left: Back Button -->
                <button type="button"
                        onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ route('pos.index') }}'"
                        class="w-9 h-9 rounded-full bg-white/90 hover:bg-white active:scale-95 text-slate-800 shadow-md backdrop-blur-xs flex items-center justify-center transition-all cursor-pointer border border-white/60"
                        title="ย้อนกลับ"
                        aria-label="ย้อนกลับ">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Top-Right Group: Search, Favorite, Share, Master Settings (Admin) -->
                <div class="flex items-center gap-2">
                    <!-- Search Button -->
                    <button type="button"
                            onclick="toggleSearchBar()"
                            class="w-9 h-9 rounded-full bg-white/90 hover:bg-white active:scale-95 text-slate-800 shadow-md backdrop-blur-xs flex items-center justify-center transition-all cursor-pointer border border-white/60"
                            title="ค้นหาเมนูอาหาร"
                            aria-label="ค้นหาเมนูอาหาร">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path stroke-linecap="round" d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>

                    <!-- Heart / Favorite Button -->
                    <button type="button"
                            onclick="toggleOnlineFavorite()"
                            class="w-9 h-9 rounded-full bg-white/90 hover:bg-white active:scale-95 text-[#E60018] shadow-md backdrop-blur-xs flex items-center justify-center transition-all cursor-pointer border border-white/60"
                            title="บันทึกร้านโปรด"
                            aria-label="บันทึกร้านโปรด">
                        <svg id="card-fav-heart" class="w-4 h-4 fill-none stroke-current" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </button>

                    <!-- Share Button -->
                    <button type="button"
                            onclick="shareOnlineStore()"
                            class="w-9 h-9 rounded-full bg-white/90 hover:bg-white active:scale-95 text-slate-800 shadow-md backdrop-blur-xs flex items-center justify-center transition-all cursor-pointer border border-white/60"
                            title="แชร์ร้านค้า"
                            aria-label="แชร์ร้านค้า">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                        </svg>
                    </button>

                    <!-- Settings Button: 3 Vertical Dots (ขวาสุดถัดจากปุ่มแชร์ - แอดมินเท่านั้น) -->
                    @if(auth()->check() || request()->has('admin'))
                    <div class="relative shrink-0">
                        <button type="button"
                                id="btn-master-settings"
                                onclick="toggleMasterSettingsMenu(event)"
                                class="w-9 h-9 rounded-full bg-white/90 hover:bg-white active:scale-95 text-slate-800 shadow-md backdrop-blur-xs flex items-center justify-center transition-all cursor-pointer border border-white/60 shrink-0"
                                title="ตั้งค่าระบบหน้าร้าน (แอดมิน)"
                                aria-label="ตั้งค่าระบบหน้าร้าน">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="1.8" />
                                <circle cx="12" cy="12" r="1.8" />
                                <circle cx="12" cy="19" r="1.8" />
                            </svg>
                        </button>

                        <!-- Master Settings Dropdown Menu (แผงเลือกตั้งค่าหน้าร้าน: ปก, โปรไฟล์, แถบโปรโมชั่น) -->
                        <div id="master-settings-menu"
                             class="hidden absolute right-0 top-full mt-2 w-72 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200/90 p-2 z-50 text-slate-800 select-none">
                            <div class="px-3 py-2 border-b border-slate-100 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-[#E50914] animate-pulse"></span>
                                    <span>ตั้งค่าหน้าร้าน (แอดมิน)</span>
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">ADMIN</span>
                            </div>

                            <div class="py-1 space-y-1">
                                <!-- Option 1: แก้ไขภาพปกหน้าร้าน -->
                                <button type="button"
                                        onclick="closeMasterSettingsMenu(); openHeroBannerModal();"
                                        class="w-full px-3 py-2.5 rounded-xl hover:bg-rose-50/80 active:bg-rose-100/80 flex items-center gap-3 text-left transition cursor-pointer group">
                                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-amber-500 to-rose-600 text-white flex items-center justify-center text-sm shadow-xs shrink-0 group-hover:scale-105 transition-transform">
                                        🖼️
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-bold text-slate-800 group-hover:text-[#E50914]">แก้ไขภาพปกหน้าร้าน</div>
                                        <div class="text-[10.5px] text-slate-500 truncate">ปรับตำแหน่ง ซูม และจัดการภาพปก</div>
                                    </div>
                                    <span class="text-slate-400 text-xs">›</span>
                                </button>

                                <!-- Option 2: แก้ไขรูปโปรไฟล์ / โลโก้ร้าน -->
                                <button type="button"
                                        onclick="closeMasterSettingsMenu(); openProfileLogoModal();"
                                        class="w-full px-3 py-2.5 rounded-xl hover:bg-rose-50/80 active:bg-rose-100/80 flex items-center gap-3 text-left transition cursor-pointer group">
                                    <div class="w-8 h-8 rounded-xl bg-[#900000] text-white flex items-center justify-center text-sm shadow-xs shrink-0 group-hover:scale-105 transition-transform border border-rose-300">
                                        🔴
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-xs font-bold text-slate-800 group-hover:text-[#E50914]">แก้ไขรูปโปรไฟล์ / โลโก้ร้าน</div>
                                        <div class="text-[10.5px] text-slate-500 truncate">เปลี่ยนโลโก้ร้าน จัดตำแหน่ง และประวัติภาพ</div>
                                    </div>
                                    <span class="text-slate-400 text-xs">›</span>
                                </button>

                                <!-- Option 3: แถบโปรโมชั่น (เปิด / ปิด) -->
                                <div class="px-3 py-2 rounded-xl bg-slate-50/90 border border-slate-200/60 flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-7 h-7 rounded-lg bg-rose-500 text-white flex items-center justify-center text-xs shadow-2xs shrink-0">
                                            🎟️
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold text-slate-800">แสดงแถบโปรโมชั่น</div>
                                            <div class="text-[10px] text-slate-500" id="promo-switch-status-text">{{ ($showPromoBanner ?? true) ? 'กำลังแสดงแถบคูปอง' : 'ซ่อนแถบคูปองอยู่' }}</div>
                                        </div>
                                    </div>

                                    <!-- Switch Toggle -->
                                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                        <input type="checkbox"
                                               id="promo-banner-switch"
                                               class="sr-only peer"
                                               {{ ($showPromoBanner ?? true) ? 'checked' : '' }}
                                               onchange="togglePromoBannerVisibility(this.checked)">
                                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </section>

        <!-- ===================================================================== -->
        <!-- 2. RESTAURANT INFORMATION CARD (Overlaps Banner by -24px)             -->
        <!-- ===================================================================== -->
        <section class="relative z-10 -mt-6 bg-white rounded-t-[26px] px-3.5 pt-3 pb-2.5 shadow-sm">
            <div class="flex items-start gap-3">
                <!-- Left: Rounded Red Logo Card with White Border -->
                <div class="w-[78px] h-[78px] rounded-[18px] border-[3px] border-white shadow-md overflow-hidden shrink-0 -mt-5 bg-[#900000]">
                    <img id="online-profile-logo-img"
                         src="{{ $profileLogoImage ?? asset('assets/brand/logo-card.png') }}"
                         alt="โลโก้ ร้านหม่าล่าสตรีท"
                         class="w-full h-full object-cover transition-all duration-150"
                         style="object-position: {{ $profileLogoPosition['x'] ?? 50 }}% {{ $profileLogoPosition['y'] ?? 50 }}%; transform: scale({{ $profileLogoPosition['scale'] ?? 1 }});">
                </div>

                <!-- Right: Crown Badge, Title, Subtitle, Ratings & Metrics -->
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-1">
                        <!-- สถานะร้าน: ย้ายมาจากขวาแทนที่ร้านแนะนำเดิม -->
                        <span class="inline-flex items-center gap-1.5 text-[10.5px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/80 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>เปิดรับออเดอร์</span>
                        </span>

                        <!-- Minimalist Standout Single-Color Chat Button -->
                        <a href="{{ route('online.chat') }}"
                           id="btn-chat-with-shop"
                           class="btn-chat-custom inline-flex items-center gap-1.5 px-3 py-1 rounded-full shadow-xs transition-all duration-150 cursor-pointer select-none group"
                           style="background-color: #111317; color: #ffffff; border: 1px solid #27272a;"
                           onmouseover="this.style.backgroundColor='#10B981'; this.style.borderColor='#059669'; this.style.color='#ffffff';"
                           onmouseout="this.style.backgroundColor='#111317'; this.style.borderColor='#27272a'; this.style.color='#ffffff';"
                           onmousedown="this.style.backgroundColor='#4ADE80'; this.style.borderColor='#22C55E'; this.style.color='#052e16'; this.style.transform='scale(0.95)';"
                           onmouseup="this.style.transform='scale(1)';"
                           title="แชทสอบถามร้านค้า">
                            <svg class="w-3.5 h-3.5 shrink-0 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span class="text-[11px] font-bold tracking-tight">แชทกับร้าน</span>
                            <span class="chat-dot w-2 h-2 rounded-full" style="background-color: #10B981;"></span>
                        </a>
                    </div>

                    <h1 class="text-[16.5px] font-extrabold text-[#101114] leading-tight tracking-tight mt-1 truncate">
                        ร้านหม่าล่าสตรีท (Mala Street)
                    </h1>
                    <p class="text-[11px] text-[#596272] mt-0.5">
                        หม่าล่าต้นตำรับ • ปิ้งย่าง • ต้มซุป
                    </p>

                    <!-- Store Metrics Row -->
                    <div class="flex items-center gap-1.5 text-[11px] text-[#374151] mt-1.5 whitespace-nowrap overflow-x-auto scrollbar-none">
                        <button type="button" onclick="openReviewsModal()" class="inline-flex items-center gap-1 hover:text-[#D90012] cursor-pointer" title="รีวิวลูกค้า">
                            <!-- Single-color Gold Star -->
                            <svg class="w-3.5 h-3.5 text-[#F59E0B] fill-current shrink-0" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.175 0l-3.37 2.448c-.784.57-1.838-.197-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.063 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z"/>
                            </svg>
                            <span class="font-extrabold text-[#101114]">4.9</span>
                            <span class="text-[#6B7280]">(500+ รีวิว)</span>
                        </button>
                        <span class="text-slate-300">|</span>
                        <span class="inline-flex items-center gap-1 text-[#101114] font-medium">
                            <!-- Single-color Red Vespa Scooter Icon -->
                            <svg class="w-4 h-3.5 text-[#D90012] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="5" cy="17.5" r="2.5" />
                                <circle cx="19" cy="17.5" r="2.5" />
                                <path d="M5 15c0-2.8 1.8-4.5 4.5-4.5h2.5c.9 0 1.6.4 2.1 1.2l2.6 4.8" />
                                <path d="M9.5 16.5h5" />
                                <path d="M6.5 10.5h5c.6 0 1 .4 1 1s-.4 1-1 1H7" />
                                <path d="M15 6h2.2c.8 0 1.5.5 1.7 1.3l.6 2.2" />
                                <circle cx="18.8" cy="7.2" r="1.2" fill="currentColor" />
                            </svg>
                            <span>ส่งเร็ว ~15–20 นาที</span>
                        </span>
                        <span class="text-slate-300">|</span>
                        <button type="button" onclick="openStoreInfoModal()" class="inline-flex items-center gap-1 text-[#101114] hover:text-[#D90012] cursor-pointer" title="ข้อมูลร้านและที่อยู่">
                            <!-- Single-color Pushpin Icon Matching Image 2 Exactly -->
                            <svg class="w-3.5 h-3.5 text-[#D90012] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 3.5h12c.8 0 1.5.7 1.5 1.5v.5c0 .6-.3 1.1-.8 1.4-1.2.7-2 2-2 3.4v.7c0 1.5.9 2.9 2.3 3.6l1.2.6c.7.4 1.1 1.1 1.1 1.9 0 .8-.7 1.5-1.5 1.5H4.2c-.8 0-1.5-.7-1.5-1.5 0-.8.4-1.5 1.1-1.9l1.2-.6c1.4-.7 2.3-2.1 2.3-3.6v-.7c0-1.4-.8-2.7-2-3.4-.5-.3-.8-.8-.8-1.4V5c0-.8.7-1.5 1.5-1.5z" />
                                <line x1="12" y1="18" x2="12" y2="23" stroke-width="2.2" stroke-linecap="round" />
                            </svg>
                            <span>ระยะทาง 2.3 กม.</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- 3. VOUCHER & DISCOUNT BOX (Clean 2 Columns with Fixed Layout)     -->
            <!-- ================================================================= -->
            <div id="online-promo-banner" class="mt-2.5 bg-[#FFF0F1] border border-[#FAD4D8] rounded-[14px] p-2 flex items-center justify-between gap-2 {{ ($showPromoBanner ?? true) ? '' : 'hidden' }}">
                <!-- Left Voucher: ส่วนลด ฿10 เมื่อสั่งขั้นต่ำ ฿150 -->
                <div class="flex-1 flex items-center justify-between gap-1.5 pr-2 border-r border-[#F5C2C7]">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <div class="w-6 h-5 rounded-[4px] bg-[#E60018] text-white flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-white fill-current shrink-0" viewBox="0 0 24 24">
                                <path d="M2 9a3 3 0 0 1 0 6v3a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-3a3 3 0 0 1 0-6V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v3zm4 2a1 1 0 0 1 1-1h10a1 1 0 1 1 0 2H7a1 1 0 0 1-1-1zm0 3a1 1 0 0 1 1-1h6a1 1 0 1 1 0 2H7a1 1 0 0 1-1-1z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 leading-tight">
                            <div class="text-[11.5px] font-extrabold text-[#101114] truncate">ลด ฿10</div>
                            <div class="text-[9px] text-[#596272] truncate">ขั้นต่ำ ฿150</div>
                        </div>
                    </div>
                    <button type="button"
                            id="coupon-btn-DISC10"
                            onclick="claimAndApplyCoupon('DISC10')"
                            class="bg-[#D90012] hover:bg-[#B8000F] active:scale-95 text-white text-[9.5px] font-bold px-2 py-1 rounded-full shrink-0 shadow-2xs transition-all cursor-pointer">
                        <span id="coupon-text-DISC10">เก็บคูปอง</span>
                    </button>
                </div>

                <!-- Right Voucher: ส่วนลด ฿20 เมื่อสั่งขั้นต่ำ ฿300 -->
                <div class="flex-1 flex items-center justify-between gap-1.5 pl-1">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <div class="w-6 h-5 rounded-[4px] bg-[#E60018] text-white flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-white fill-current shrink-0" viewBox="0 0 24 24">
                                <path d="M2 9a3 3 0 0 1 0 6v3a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-3a3 3 0 0 1 0-6V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v3zm4 2a1 1 0 0 1 1-1h10a1 1 0 1 1 0 2H7a1 1 0 0 1-1-1zm0 3a1 1 0 0 1 1-1h6a1 1 0 1 1 0 2H7a1 1 0 0 1-1-1z"/>
                            </svg>
                        </div>
                        <div class="min-w-0 leading-tight">
                            <div class="text-[11.5px] font-extrabold text-[#101114] truncate">ลด ฿20</div>
                            <div class="text-[9px] text-[#596272] truncate">ขั้นต่ำ ฿300</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <button type="button"
                                id="coupon-btn-DISC20"
                                onclick="claimAndApplyCoupon('DISC20')"
                                class="bg-[#D90012] hover:bg-[#B8000F] active:scale-95 text-white text-[9.5px] font-bold px-2 py-1 rounded-full shrink-0 shadow-2xs transition-all cursor-pointer">
                            <span id="coupon-text-DISC20">เก็บคูปอง</span>
                        </button>
                        <button type="button" onclick="openPromotionsModal()" class="text-[#D90012] font-bold text-xs p-0.5 cursor-pointer" title="ดูโปรทั้งหมด">&gt;</button>
                    </div>
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- 3.1 LOYALTY CARD & STAMP PROMPT BANNER                             -->
            <!-- ================================================================= -->
            <a href="{{ route('online.rewards') }}" class="mt-2 block bg-gradient-to-r from-amber-50 via-orange-50 to-rose-50 border border-amber-200/90 rounded-[14px] p-2 hover:border-amber-300 transition-all shadow-2xs group">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-amber-500 to-rose-500 text-white flex items-center justify-center shrink-0 shadow-xs text-xs font-black">
                            🎁
                        </div>
                        <div class="min-w-0">
                            <div class="text-[11.5px] font-extrabold text-amber-950 truncate flex items-center gap-1.5">
                                <span>สะสมแต้ม: ซื้อ 1 ครั้ง = 1 แต้ม (ครบ 5 ฟรี 1 ไม้)</span>
                                <span class="text-[8.5px] bg-rose-600 text-white font-black px-1 rounded-sm">คุ้มมาก</span>
                            </div>
                            <div class="text-[9.5px] text-amber-800/90 truncate">เขียนรีวิวอาหารรับเพิ่มทันที +1 แต้ม • กดเช็คแต้มเลย</div>
                        </div>
                    </div>
                    <span class="text-[10px] font-extrabold text-amber-900 bg-white/95 border border-amber-300/80 px-2 py-0.5 rounded-full shrink-0 group-hover:bg-amber-100 transition-colors">
                        เช็คแต้ม →
                    </span>
                </div>
            </a>

            <!-- ================================================================= -->
            <!-- 4. RESTAURANT NAVIGATION TABS (4 Clean Tabs)                      -->
            <!-- ================================================================= -->
            <div class="mt-2.5 border-b border-[#ECEEF2] grid grid-cols-4 text-[12px] text-center">
                <button type="button"
                        class="pb-2 px-1 border-b-[2.5px] border-[#D90012] text-[#D90012] font-bold flex items-center justify-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                        <path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/>
                    </svg>
                    <span>สั่งอาหาร</span>
                </button>

                <button type="button"
                        onclick="openReviewsModal()"
                        class="pb-2 px-1 border-b-[2.5px] border-transparent text-[#4B5563] hover:text-[#101114] font-medium flex items-center justify-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5 fill-none stroke-current" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    <span>รีวิว (500+)</span>
                </button>

                <button type="button"
                        onclick="openStoreInfoModal()"
                        class="pb-2 px-1 border-b-[2.5px] border-transparent text-[#4B5563] hover:text-[#101114] font-medium flex items-center justify-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5 fill-none stroke-current" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>ข้อมูลร้าน</span>
                </button>

                <button type="button"
                        onclick="openPromotionsModal()"
                        class="pb-2 px-1 border-b-[2.5px] border-transparent text-[#4B5563] hover:text-[#101114] font-medium flex items-center justify-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5 fill-none stroke-current" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    <span>โปรโมชั่น</span>
                </button>
            </div>

            <!-- ================================================================= -->
            <!-- 5. ORDERING METHOD SELECTION (3 Cards Side-by-Side)               -->
            <!-- ================================================================= -->
            <div class="mt-2.5 grid grid-cols-3 gap-1.5 sm:gap-2">
                <!-- Method 1: สั่งเดลิเวอรี่ -->
                <button type="button"
                        id="btn-method-delivery"
                        onclick="setOrderingMethod('delivery')"
                        class="rounded-[14px] px-2 py-2 sm:px-2.5 sm:py-2.5 bg-gradient-to-r from-[#D91A1E] via-[#CB1318] to-[#B80F13] text-white shadow-[0_4px_14px_rgba(217,26,30,0.35)] flex items-center gap-1.5 sm:gap-2 text-left transition-all cursor-pointer border border-[#E6282C]">
                    <img id="scooter-delivery-icon" src="{{ asset('assets/brand/scooter-white.png') }}" class="w-5 sm:w-5.5 h-auto shrink-0 object-contain drop-shadow-xs" alt="Delivery">
                    <div class="min-w-0">
                        <div class="method-title text-[11px] sm:text-[12px] font-bold leading-tight truncate text-white">สั่งเดลิเวอรี่</div>
                        <div class="method-sub text-[8.5px] sm:text-[9.5px] text-white/95 leading-tight truncate mt-0.5 font-normal tracking-tight">ส่งถึงที่ ~15–20 นาที</div>
                    </div>
                </button>

                <!-- Method 2: รับที่ร้าน -->
                <button type="button"
                        id="btn-method-pickup"
                        onclick="setOrderingMethod('pickup')"
                        class="rounded-[14px] px-2 py-2 sm:px-2.5 sm:py-2.5 bg-white text-[#101114] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex items-center gap-1.5 sm:gap-2 text-left transition-all cursor-pointer">
                    <svg class="method-icon w-4 h-4 sm:w-4.5 sm:h-4.5 shrink-0 transition-colors text-[#101114]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="8" width="16" height="13" rx="2.5" />
                        <path d="M8 11V6a4 4 0 0 1 8 0v5" />
                    </svg>
                    <div class="min-w-0">
                        <div class="method-title text-[11px] sm:text-[12px] font-bold leading-tight truncate text-[#101114]">รับที่ร้าน</div>
                        <div class="method-sub text-[8.5px] sm:text-[9.5px] text-[#596272] leading-tight truncate mt-0.5 font-normal tracking-tight">สั่งล่วงหน้า ไม่ต้องรอ</div>
                    </div>
                </button>

                <!-- Method 3: จองคิว -->
                <button type="button"
                        id="btn-method-booking"
                        onclick="setOrderingMethod('booking')"
                        class="rounded-[14px] px-2 py-2 sm:px-2.5 sm:py-2.5 bg-white text-[#101114] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex items-center gap-1.5 sm:gap-2 text-left transition-all cursor-pointer">
                    <svg class="method-icon w-4 h-4 sm:w-4.5 sm:h-4.5 shrink-0 transition-colors text-[#101114]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3.5" y="5" width="17" height="16" rx="3" />
                        <line x1="8" y1="2.5" x2="8" y2="6.5" />
                        <line x1="16" y1="2.5" x2="16" y2="6.5" />
                        <line x1="3.5" y1="10" x2="20.5" y2="10" />
                        <circle cx="8" cy="14" r="0.9" fill="currentColor" stroke="none" />
                        <circle cx="12" cy="14" r="0.9" fill="currentColor" stroke="none" />
                        <circle cx="16" cy="14" r="0.9" fill="currentColor" stroke="none" />
                        <circle cx="8" cy="17.5" r="0.9" fill="currentColor" stroke="none" />
                    </svg>
                    <div class="min-w-0">
                        <div class="method-title text-[11px] sm:text-[12px] font-bold leading-tight truncate text-[#101114]">จองคิว</div>
                        <div class="method-sub text-[8.5px] sm:text-[9.5px] text-[#596272] leading-tight truncate mt-0.5 font-normal tracking-tight">เลือกเวลาเข้ารับ</div>
                    </div>
                </button>
            </div>

            <!-- ================================================================= -->
            <!-- 6. FOOD CATEGORY NAVIGATION PILLS (Smooth Horizontal Scroll)      -->
            <!-- ================================================================= -->
            <div class="mt-3 flex items-center gap-2 overflow-x-auto scrollbar-none pb-1">
                <!-- Item 1: ทั้งหมด (Active Pill with 4 Dots Icon) -->
                <button type="button"
                        onclick="filterOnlineCategory('all', this)"
                        class="online-cat-pill active shrink-0 h-[38px] px-3.5 rounded-full border border-[#E60018] bg-[#E60018] text-white flex items-center gap-2 cursor-pointer transition-all shadow-xs">
                    <div class="grid grid-cols-2 gap-0.5">
                        <span class="cat-dot w-1 h-1 rounded-full bg-white block"></span>
                        <span class="cat-dot w-1 h-1 rounded-full bg-white block"></span>
                        <span class="cat-dot w-1 h-1 rounded-full bg-white block"></span>
                        <span class="cat-dot w-1 h-1 rounded-full bg-white block"></span>
                    </div>
                    <span class="cat-label text-[12px] font-bold leading-none">ทั้งหมด</span>
                </button>

                <!-- Items 2–8: Category Pills with Circular Icon + Name -->
                @foreach($categories as $category)
                    @php
                        // ใช้ชื่อและไอคอนจากฐานข้อมูล (ซิงค์กับหน้าสต๊อกสินค้า) ถ้าไม่มีค่อยใช้ค่าเริ่มต้น
                        $catImg = !empty($category->icon) && !in_array($category->icon, ['🥩', '🍗', '🍢', '🥬', '🍥', '🥤', '🍟'])
                            ? (str_starts_with($category->icon, 'http') ? $category->icon : asset($category->icon))
                            : asset($categoryImageMap[$category->slug] ?? 'assets/categories/beef.png');
                        $shortName = $category->name ?: ($CategoryShortNames[$category->slug] ?? '');
                    @endphp
                    <button type="button"
                            data-cat="{{ $category->slug }}"
                            onclick="filterOnlineCategory('{{ $category->slug }}', this)"
                            class="online-cat-pill shrink-0 h-[38px] px-3 rounded-full border border-slate-200/90 bg-white text-[#101114] flex items-center gap-2 cursor-pointer transition-all shadow-2xs hover:border-[#E60018]">
                        <div class="cat-circle w-6 h-6 rounded-full overflow-hidden shrink-0 border border-slate-100">
                            <img src="{{ $catImg }}"
                                 alt="{{ $shortName }}"
                                 class="w-full h-full object-cover">
                        </div>
                        <span class="cat-label text-[11.5px] font-semibold text-[#101114] whitespace-nowrap leading-none">
                            {{ $shortName }}
                        </span>
                    </button>
                @endforeach
            </div>
        </section>

        <!-- ===================================================================== -->
        <!-- 7. RECOMMENDED PRODUCTS SECTION (เมนูแนะนำ 🔥 ซิงค์จากหน้าสต๊อกสินค้า)     -->
        <!-- ===================================================================== -->
        <section class="px-3.5 pt-3 {{ $recommendedList->isEmpty() ? 'hidden' : '' }}" id="recommended-section">
            <div class="flex items-center gap-1.5 mb-2">
                <span class="w-1 h-4 rounded-full bg-[#D90012] block"></span>
                <h2 class="text-[14.5px] font-extrabold text-[#101114] tracking-tight">เมนูแนะนำ 🔥</h2>
            </div>

            <div class="grid grid-cols-3 gap-2" id="recommended-grid">
                @foreach($recommendedList as $prod)
                    @php
                        $prodId = $prod->id;
                        $displayName = $prod->display_name ?: $prod->name;
                        $displayPrice = (float) $prod->price_per_stick;
                        $displayImg = $prod->display_image;
                    @endphp
                    <div class="bg-white rounded-[14px] p-1.5 border border-[#ECEEF2] shadow-[0_2px_6px_rgba(0,0,0,0.04)] flex flex-col justify-between" data-rec-product-id="{{ $prodId }}">
                        <div onclick="openCustomizationModal({{ $prodId }})" class="relative w-full aspect-[16/10] rounded-[10px] overflow-hidden bg-slate-900 cursor-pointer">
                            <img src="{{ $displayImg }}"
                                 alt="{{ $displayName }}"
                                 class="w-full h-full object-cover">
                        </div>

                        <div class="pt-1.5 px-0.5">
                            <h3 onclick="openCustomizationModal({{ $prodId }})" class="text-[11px] font-bold text-[#101114] leading-tight truncate cursor-pointer">
                                {{ $displayName }}
                            </h3>
                            <div class="flex items-center justify-between mt-1">
                                <div class="text-[12px] font-extrabold text-[#D90012] leading-none">
                                    ฿{{ number_format($displayPrice, 0) }}
                                    <span class="text-[9px] text-[#596272] font-normal">/ไม้</span>
                                </div>
                                <button type="button"
                                        onclick="quickAddStick({{ $prodId }})"
                                        class="w-[22px] h-[22px] rounded-full bg-[#D90012] hover:bg-[#B8000F] text-white flex items-center justify-center text-sm font-bold leading-none shadow-2xs active:scale-90 transition-transform cursor-pointer"
                                        title="เพิ่มลงตะกร้า">
                                    +
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- ===================================================================== -->
        <!-- 8. ALL MENU SECTION (เมนูทั้งหมด + Horizontal Product Cards)            -->
        <!-- ===================================================================== -->
        <section class="px-3.5 pt-3.5">
            <!-- Header + Sort Dropdown -->
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-1.5">
                    <span class="w-1 h-4 rounded-full bg-[#D90012] block"></span>
                    <h2 class="text-[14.5px] font-extrabold text-[#101114] tracking-tight">เมนูทั้งหมด</h2>
                    <span class="text-[11px] text-[#596272]">(<span id="all-menu-count">{{ $totalCount }}</span> รายการ)</span>
                </div>

                <!-- Sort Pill Dropdown -->
                <div class="relative">
                    <select id="sort-select" onchange="handleMenuSort()"
                            class="appearance-none bg-white border border-[#E2E5EC] rounded-full pl-3 pr-6 py-1 text-[11px] font-medium text-[#374151] shadow-2xs focus:outline-none focus:border-[#D90012] cursor-pointer">
                        <option value="featured">เรียงตาม : แนะนำ</option>
                        <option value="price_asc">ราคา : ต่ำ → สูง</option>
                        <option value="price_desc">ราคา : สูง → ต่ำ</option>
                        <option value="stock">จำนวนคงเหลือ</option>
                    </select>
                    <span class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] text-slate-500">⌄</span>
                </div>
            </div>

            <!-- Collapsible Quick Search Bar -->
            <div id="search-bar-wrapper" class="hidden mt-2">
                <div class="relative">
                    <input type="text" id="search-input" oninput="handleMenuSearch()"
                           placeholder="ค้นหาชื่อเมนูหม่าล่า..."
                           class="w-full pl-8 pr-8 py-1.5 bg-white border border-[#E2E5EC] rounded-full text-xs text-[#101114] placeholder:text-slate-400 focus:outline-none focus:border-[#D90012] shadow-2xs">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400">🔍</span>
                    <button type="button" onclick="clearSearchBar()" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 hover:text-slate-600">✕</button>
                </div>
            </div>

            <!-- Vertical Stack of Horizontal Product Cards -->
            <div id="product-list-container" class="mt-2.5 space-y-2">
                @foreach($allProducts as $idx => $product)
                    @php
                        $catSlug = $product->category->slug ?? 'all';
                        $productImg = $product->display_image;
                        $productName = $product->display_name ?: $product->name;
                        $productPrice = (float) $product->price_per_stick;
                    @endphp
                    <div class="product-item bg-white rounded-[15px] p-2 border border-[#ECEEF2] shadow-[0_2px_6px_rgba(0,0,0,0.03)] flex items-center justify-between gap-2.5 transition-all"
                         data-id="{{ $product->id }}"
                         data-name="{{ $productName }}"
                         data-price="{{ $productPrice }}"
                         data-category="{{ $catSlug }}"
                         data-stock="{{ (int) $product->stock_sticks }}">

                        <!-- Left: Food Image -->
                        <div onclick="openCustomizationModal({{ $product->id }})"
                             class="relative w-[84px] h-[62px] rounded-[11px] overflow-hidden bg-slate-900 shrink-0 cursor-pointer">
                            <img src="{{ $productImg }}"
                                 alt="{{ $productName }}"
                                 class="w-full h-full object-cover"
                                 onerror="this.src='{{ asset('assets/menu/food-fallback.svg') }}'">
                            @if($product->stock_sticks <= 0)
                                <div class="absolute inset-0 bg-black/65 flex items-center justify-center text-white text-[10px] font-bold">
                                    สินค้าหมด
                                </div>
                            @endif
                        </div>

                        <!-- Center: Title, Description, Price & Stock Badge -->
                        <div onclick="openCustomizationModal({{ $product->id }})" class="min-w-0 flex-1 cursor-pointer">
                            <h3 class="text-[13px] font-bold text-[#101114] leading-snug truncate">
                                {{ $productName }}
                            </h3>
                            <p class="text-[10px] text-[#596272] line-clamp-1 mt-0.5">
                                {{ $product->description ?: 'วัตถุดิบคัดพิเศษ ย่างหอมเครื่องหม่าล่าต้นตำรับ' }}
                            </p>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="text-[13px] font-extrabold text-[#D90012] leading-none">
                                    ฿{{ number_format($productPrice, 0) }}
                                    <span class="text-[9.5px] text-[#596272] font-normal">/ไม้</span>
                                </div>
                                @if($product->stock_sticks > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#E3FCEF] text-[#057A55] text-[9px] font-bold leading-none">
                                        เหลือ {{ $product->stock_sticks }} ไม้
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 text-[9px] font-bold leading-none">
                                        หมด
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Action Button or Stepper (Fixed-width container to prevent layout jump) -->
                        <div class="w-[76px] shrink-0 flex items-center justify-end" id="row-action-{{ $product->id }}">
                            @if($product->stock_sticks > 0)
                                <button type="button"
                                        onclick="quickAddStick({{ $product->id }})"
                                        class="w-[28px] h-[28px] rounded-full bg-[#D90012] hover:bg-[#B8000F] text-white flex items-center justify-center text-base font-bold leading-none shadow-xs active:scale-90 transition-transform cursor-pointer">
                                    +
                                </button>
                            @else
                                <button type="button" disabled
                                        class="w-[28px] h-[28px] rounded-full bg-slate-200 text-slate-400 flex items-center justify-center text-xs cursor-not-allowed">
                                    ✕
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Empty State -->
            <div id="empty-product-state" class="hidden bg-white rounded-[16px] p-8 text-center border border-[#ECEEF2] my-3">
                <div class="text-3xl mb-1.5">🍢</div>
                <div class="text-sm font-bold text-[#101114]">ไม่พบรายการเมนูที่ค้นหา</div>
                <p class="text-[11px] text-[#596272] mt-0.5">ลองเลือกหมวดหมู่อื่นหรือค้นหาใหม่อีกครั้ง</p>
            </div>
        </section>

        <!-- ===================================================================== -->
        <!-- 9. STICKY BOTTOM CART SUMMARY (Fixed to Bottom of Mobile View)        -->
        <!-- ===================================================================== -->
        <div id="sticky-cart-bar"
             class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-[430px] z-40 bg-white rounded-t-[22px] shadow-[0_-6px_24px_rgba(0,0,0,0.14)] border-t border-slate-100 px-4 py-2.5 flex items-center justify-between">
            <!-- Left: Red Circular Cart Icon + Price & Count -->
            <button type="button" onclick="openCheckoutModal()" class="flex items-center gap-3 text-left cursor-pointer group">
                <div class="relative w-[42px] h-[42px] rounded-full bg-gradient-to-b from-[#E60018] to-[#C20010] text-white flex items-center justify-center shadow-md group-hover:scale-105 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span id="cart-item-badge"
                          class="absolute -top-1 -right-1 bg-[#FF5500] text-white text-[10px] font-extrabold w-[18px] h-[18px] rounded-full flex items-center justify-center border-[1.5px] border-white shadow-2xs">
                        0
                    </span>
                </div>
                <div>
                    <div class="text-[18px] font-extrabold text-[#101114] leading-none" id="cart-total-price">฿0</div>
                    <div class="text-[11px] text-[#596272] mt-0.5" id="cart-count-label">0 รายการ</div>
                </div>
            </button>

            <!-- Right: Divider + Large Red Pill Button "สั่งอาหาร" -->
            <div class="flex items-center gap-3">
                <span class="h-8 w-px bg-slate-200 block"></span>
                <button type="button"
                        onclick="openCheckoutModal()"
                        class="bg-gradient-to-r from-[#D90012] to-[#B8000F] hover:from-[#B8000F] hover:to-[#99000C] text-white font-bold text-[14.5px] px-8 py-2.5 rounded-full shadow-md active:scale-95 transition-all cursor-pointer">
                    สั่งอาหาร
                </button>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 10. PRODUCT DETAIL & CUSTOMIZATION MODAL                                  -->
    <!-- ========================================================================= -->
    <div id="custom-modal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-2xs flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white w-full max-w-[430px] rounded-t-[26px] sm:rounded-[26px] overflow-hidden shadow-2xl border border-slate-100 max-h-[88vh] flex flex-col">
            <div class="relative h-40 bg-slate-900 shrink-0">
                <img id="modal-product-img" src="" alt="" class="w-full h-full object-cover opacity-90">
                <button type="button" onclick="closeCustomizationModal()"
                        class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/60 text-white flex items-center justify-center hover:bg-black/80 cursor-pointer">
                    ✕
                </button>
                <div class="absolute bottom-0 inset-x-0 p-3.5 bg-gradient-to-t from-black/85 via-black/40 to-transparent text-white">
                    <h3 class="text-base font-bold" id="modal-product-name">ชื่อเมนู</h3>
                    <p class="text-xs text-white/85" id="modal-product-price">฿0 /ไม้</p>
                </div>
            </div>

            <div class="p-4 overflow-y-auto space-y-3.5 flex-1 text-xs">
                <!-- Cooking Style -->
                <div>
                    <label class="font-bold text-[#101114] block mb-1.5">รูปแบบการปรุงอาหาร <span class="text-[#D90012]">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 cursor-pointer has-checked:border-[#D90012] has-checked:bg-[#FFF0F1]">
                            <input type="radio" name="opt_cooking_type" value="grill" checked class="text-[#D90012]">
                            <div>
                                <div class="font-bold text-[#101114]">🍢 ย่างเตาถ่าน</div>
                                <div class="text-[10px] text-[#596272]">ทาซอสหม่าล่าต้นตำรับ</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 cursor-pointer has-checked:border-[#D90012] has-checked:bg-[#FFF0F1]">
                            <input type="radio" name="opt_cooking_type" value="soup" class="text-[#D90012]">
                            <div>
                                <div class="font-bold text-[#101114]">🍲 ต้มซุปหม่าล่า</div>
                                <div class="text-[10px] text-[#596272]">ซุปหม่าล่าน้ำข้นเสฉวน</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Spiciness -->
                <div>
                    <label class="font-bold text-[#101114] block mb-1.5">ระดับความเผ็ด <span class="text-[#D90012]">*</span></label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex flex-col items-center p-2 rounded-xl border border-slate-200 text-center cursor-pointer has-checked:border-[#D90012] has-checked:bg-[#FFF0F1]">
                            <input type="radio" name="opt_spice_level" value="mild" class="text-[#D90012] mb-1">
                            <span class="font-bold text-[#101114]">🌶️ เผ็ดน้อย</span>
                        </label>
                        <label class="flex flex-col items-center p-2 rounded-xl border border-slate-200 text-center cursor-pointer has-checked:border-[#D90012] has-checked:bg-[#FFF0F1]">
                            <input type="radio" name="opt_spice_level" value="medium" checked class="text-[#D90012] mb-1">
                            <span class="font-bold text-[#101114]">🌶️🌶️ เผ็ดกลาง</span>
                        </label>
                        <label class="flex flex-col items-center p-2 rounded-xl border border-slate-200 text-center cursor-pointer has-checked:border-[#D90012] has-checked:bg-[#FFF0F1]">
                            <input type="radio" name="opt_spice_level" value="hot" class="text-[#D90012] mb-1">
                            <span class="font-bold text-[#101114]">🌶️🌶️🌶️ เผ็ดมาก</span>
                        </label>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="font-bold text-[#101114] block mb-1">คำขอพิเศษเพิ่มเติม</label>
                    <input type="text" id="modal-special-notes" placeholder="เช่น แยกพริก, ไม่โรยงา..."
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#D90012]">
                </div>

                <!-- Stepper -->
                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <span class="font-bold text-[#101114] text-sm">จำนวนไม้</span>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="adjustModalQty(-1)" class="w-7 h-7 rounded-full bg-[#FFE4E6] text-[#D90012] font-bold flex items-center justify-center cursor-pointer">-</button>
                        <span id="modal-qty" class="font-bold text-sm text-[#101114] min-w-5 text-center">1</span>
                        <button type="button" onclick="adjustModalQty(1)" class="w-7 h-7 rounded-full bg-[#D90012] text-white font-bold flex items-center justify-center cursor-pointer">+</button>
                    </div>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50 border-t border-slate-100 shrink-0">
                <button type="button" onclick="confirmAddToCart()"
                        class="w-full py-2.5 rounded-full bg-gradient-to-r from-[#D90012] to-[#B8000F] text-white font-bold text-sm shadow-md cursor-pointer">
                    เพิ่มลงตะกร้า • <span id="modal-item-total">฿0</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 11. CART DETAIL & CHECKOUT MODAL                                          -->
    <!-- ========================================================================= -->
    <div id="checkout-modal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-2xs flex items-end sm:items-center justify-center p-0 sm:p-4">
        <div class="bg-white w-full max-w-[430px] rounded-t-[26px] sm:rounded-[26px] overflow-hidden shadow-2xl border border-slate-100 max-h-[92vh] flex flex-col">
            <div class="px-4 py-3 bg-gradient-to-r from-[#4A0408] to-[#800810] text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <span class="text-lg">🛒</span>
                    <div>
                        <h3 class="font-bold text-sm leading-tight">สรุปรายการสั่งอาหาร</h3>
                        <p class="text-[10px] text-amber-200">ร้านหม่าล่าสตรีท (Mala Street)</p>
                    </div>
                </div>
                <button type="button" onclick="closeCheckoutModal()" class="w-7 h-7 rounded-full bg-white/20 text-white flex items-center justify-center cursor-pointer">✕</button>
            </div>

            <div class="p-4 overflow-y-auto space-y-3.5 flex-1 text-xs">
                <!-- Cart Items -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="font-bold text-[#101114]">รายการอาหารที่เลือก</span>
                        <button type="button" onclick="clearCart()" class="text-[#D90012] hover:underline text-[11px] cursor-pointer">ล้างตะกร้า</button>
                    </div>
                    <div id="checkout-items-list" class="space-y-2 max-h-44 overflow-y-auto pr-1"></div>
                </div>

                <!-- Customer Info -->
                <div class="bg-[#F7F8FA] p-3 rounded-2xl border border-slate-200 space-y-2">
                    <span class="font-bold text-[#101114] block">ข้อมูลผู้สั่งอาหาร</span>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-[10.5px] text-[#596272] block mb-0.5">ชื่อผู้สั่ง <span class="text-[#D90012]">*</span></label>
                            <input type="text" id="cust-name" placeholder="ชื่อของคุณ"
                                   class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-xl focus:border-[#D90012] focus:outline-none">
                        </div>
                        <div>
                            <label class="text-[10.5px] text-[#596272] block mb-0.5">เบอร์โทรศัพท์ <span class="text-[#D90012]">*</span></label>
                            <input type="tel" id="cust-phone" placeholder="08X-XXX-XXXX"
                                   class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-xl focus:border-[#D90012] focus:outline-none">
                        </div>
                    </div>

                    <div id="delivery-address-group">
                        <label class="text-[10.5px] text-[#596272] block mb-0.5">ที่อยู่จัดส่งเดลิเวอรี่ <span class="text-[#D90012]">*</span></label>
                        <textarea id="cust-address" rows="2" placeholder="บ้านเลขที่ / ซอย / จุดสังเกต..."
                                  class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-xl focus:border-[#D90012] focus:outline-none"></textarea>
                    </div>

                    <div id="pickup-time-group" class="hidden">
                        <label class="text-[10.5px] text-[#596272] block mb-0.5">เวลานัดรับ / จองคิว</label>
                        <select id="cust-pickup-time" class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-xl focus:border-[#D90012] focus:outline-none">
                            <option value="ทันที (~15-20 นาที)">รับทันที (~15-20 นาที)</option>
                            <option value="17:30 น.">17:30 น.</option>
                            <option value="18:00 น.">18:00 น.</option>
                            <option value="18:30 น.">18:30 น.</option>
                            <option value="19:00 น.">19:00 น.</option>
                            <option value="20:00 น.">20:00 น.</option>
                        </select>
                    </div>
                </div>

                <!-- Coupon Input -->
                <div class="bg-[#FFF0F1] p-2.5 rounded-2xl border border-[#FAD4D8] flex items-center justify-between gap-2">
                    <input type="text" id="cart-coupon-input" placeholder="โค้ดส่วนลด (DISC10, DISC20)"
                           class="bg-white px-2.5 py-1 rounded-xl border border-[#FAD4D8] text-xs uppercase flex-1 focus:outline-none">
                    <button type="button" onclick="applyCustomCoupon()"
                            class="px-3 py-1 rounded-xl bg-[#D90012] text-white text-xs font-bold cursor-pointer">
                        ใช้โค้ด
                    </button>
                </div>

                <!-- Payment Method -->
                <div>
                    <label class="font-bold text-[#101114] block mb-1">วิธีชำระเงิน</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 cursor-pointer has-checked:border-[#D90012] has-checked:bg-[#FFF0F1]">
                            <input type="radio" name="payment_method" value="promptpay" checked class="text-[#D90012]">
                            <span class="font-bold text-[#101114]">📱 พร้อมเพย์</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-200 cursor-pointer has-checked:border-[#D90012] has-checked:bg-[#FFF0F1]">
                            <input type="radio" name="payment_method" value="cash" class="text-[#D90012]">
                            <span class="font-bold text-[#101114]">💵 เงินสด</span>
                        </label>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="border-t border-slate-200 pt-2.5 space-y-1 text-xs text-[#596272]">
                    <div class="flex justify-between">
                        <span>ค่าอาหาร (<span id="summary-total-sticks">0</span> ไม้)</span>
                        <span class="font-bold text-[#101114]" id="summary-subtotal">฿0</span>
                    </div>
                    <div class="flex justify-between text-[#D90012]">
                        <span>ส่วนลดคูปอง</span>
                        <span class="font-bold" id="summary-discount">-฿0</span>
                    </div>
                    <div class="flex justify-between">
                        <span>ค่าจัดส่ง (<span id="summary-method-name">สั่งเดลิเวอรี่</span>)</span>
                        <span class="font-bold text-[#101114]" id="summary-shipping">฿0</span>
                    </div>
                    <div class="flex justify-between text-sm font-extrabold text-[#101114] pt-1.5 border-t border-slate-100">
                        <span>ยอดชำระสุทธิ</span>
                        <span class="text-base text-[#D90012]" id="summary-grand-total">฿0</span>
                    </div>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50 border-t border-slate-100 shrink-0">
                <button type="button" id="btn-submit-order" onclick="submitOnlineOrder()"
                        class="w-full py-3 rounded-full bg-gradient-to-r from-[#D90012] to-[#B8000F] text-white font-bold text-sm shadow-md flex items-center justify-center gap-2 cursor-pointer">
                    <span id="submit-btn-text">ยืนยันสั่งอาหาร • <span id="btn-final-amount">฿0</span></span>
                    <span id="submit-btn-spinner" class="hidden animate-spin">⏳</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 12. ORDER CONFIRMATION MODAL                                              -->
    <!-- ========================================================================= -->
    <div id="success-modal" class="hidden fixed inset-0 z-50 bg-black/70 backdrop-blur-2xs flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-[380px] rounded-[26px] p-5 text-center shadow-2xl">
            <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl mx-auto mb-2.5 font-bold">✓</div>
            <h3 class="text-lg font-extrabold text-[#101114]">สั่งอาหารสำเร็จแล้ว!</h3>
            <p class="text-xs text-[#596272] mt-0.5">ออเดอร์ถูกส่งเข้าครัวหม่าล่าสตรีทเรียบร้อยแล้ว</p>

            <div class="my-3.5 p-3.5 rounded-2xl bg-[#F7F8FA] border border-slate-200">
                <div class="text-[11px] text-[#596272]">หมายเลขคิวของคุณ</div>
                <div class="text-3xl font-black text-[#D90012]" id="success-queue-tag">G01</div>
                <div class="text-[11px] text-[#374151] mt-0.5">เลขที่ออเดอร์: <span id="success-order-no" class="font-bold"></span></div>
            </div>

            <div class="space-y-2">
                <a id="success-track-link" href="#"
                   class="w-full block py-2.5 rounded-full bg-gradient-to-r from-[#D90012] to-[#B8000F] text-white font-bold text-xs shadow-md">
                    🛵 ติดตามสถานะออเดอร์เรียลไทม์
                </a>
                <button type="button" onclick="resetAfterSuccess()"
                        class="w-full py-2 rounded-full bg-slate-100 text-[#374151] font-bold text-xs cursor-pointer">
                    กลับหน้าสั่งอาหาร
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- 13. REVIEWS, STORE INFO & PROMOTIONS MODALS                               -->
    <!-- ========================================================================= -->
    <div id="reviews-modal" class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-[380px] rounded-[24px] p-4 shadow-2xl">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-[#101114]">รีวิวจากลูกค้า (500+ รีวิว)</h3>
                <button type="button" onclick="closeReviewsModal()" class="text-slate-400 cursor-pointer">✕</button>
            </div>
            <div class="py-3 text-center border-b border-slate-100">
                <div class="text-3xl font-black text-[#FFB800]">4.9 ★</div>
                <p class="text-[11px] text-[#596272] mt-0.5">เผ็ดชา หอมเครื่องหม่าล่าต้นตำรับ</p>
            </div>
            <div class="py-2.5 space-y-2 text-xs">
                <div class="p-2 rounded-xl bg-slate-50">
                    <div class="flex justify-between font-bold"><span>คุณธนกร</span><span class="text-[#FFB800]">★★★★★</span></div>
                    <p class="text-[#596272] mt-0.5">"เนื้อโคขุนพรีเมียมนุ่มมาก พริกหม่าล่าหอมชาลิ้นสุดๆ"</p>
                </div>
                <div class="p-2 rounded-xl bg-slate-50">
                    <div class="flex justify-between font-bold"><span>คุณศิริพร</span><span class="text-[#FFB800]">★★★★★</span></div>
                    <p class="text-[#596272] mt-0.5">"สามชั้นพันเห็ดเข็มทองย่างมาพอดี ส่งไวมากค่ะ"</p>
                </div>
            </div>
            <button type="button" onclick="closeReviewsModal()" class="w-full mt-2 py-2 rounded-xl bg-slate-100 text-xs font-bold cursor-pointer">ปิด</button>
        </div>
    </div>

    <div id="store-info-modal" class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-[380px] rounded-[24px] p-4 shadow-2xl text-xs">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-[#101114]">ข้อมูลร้านหม่าล่าสตรีท</h3>
                <button type="button" onclick="closeStoreInfoModal()" class="text-slate-400 cursor-pointer">✕</button>
            </div>
            <div class="py-3 space-y-2.5 text-[#374151]">
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#D90012] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 3.5h12c.8 0 1.5.7 1.5 1.5v.5c0 .6-.3 1.1-.8 1.4-1.2.7-2 2-2 3.4v.7c0 1.5.9 2.9 2.3 3.6l1.2.6c.7.4 1.1 1.1 1.1 1.9 0 .8-.7 1.5-1.5 1.5H4.2c-.8 0-1.5-.7-1.5-1.5 0-.8.4-1.5 1.1-1.9l1.2-.6c1.4-.7 2.3-2.1 2.3-3.6v-.7c0-1.4-.8-2.7-2-3.4-.5-.3-.8-.8-.8-1.4V5c0-.8.7-1.5 1.5-1.5z" />
                        <line x1="12" y1="18" x2="12" y2="23" stroke-width="2.2" stroke-linecap="round" />
                    </svg>
                    <span><strong class="text-[#101114]">ที่ตั้งร้าน:</strong> สาขาสุขุมวิท (ระยะทาง 2.3 กม.)</span>
                </div>
                <div><strong class="text-[#101114]">🕒 เวลาทำการ:</strong> เปิดบริการทุกวัน 16:00 – 02:00 น.</div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-3.5 text-[#D90012] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="5" cy="17.5" r="2.5" />
                        <circle cx="19" cy="17.5" r="2.5" />
                        <path d="M5 15c0-2.8 1.8-4.5 4.5-4.5h2.5c.9 0 1.6.4 2.1 1.2l2.6 4.8" />
                        <path d="M9.5 16.5h5" />
                        <path d="M6.5 10.5h5c.6 0 1 .4 1 1s-.4 1-1 1H7" />
                        <path d="M15 6h2.2c.8 0 1.5.5 1.7 1.3l.6 2.2" />
                        <circle cx="18.8" cy="7.2" r="1.2" fill="currentColor" />
                    </svg>
                    <span><strong class="text-[#101114]">บริการจัดส่ง:</strong> ส่งเร็วถึงที่ภายใน ~15–20 นาที</span>
                </div>
            </div>
            <button type="button" onclick="closeStoreInfoModal()" class="w-full mt-2 py-2 rounded-xl bg-slate-100 text-xs font-bold cursor-pointer">ปิด</button>
        </div>
    </div>

    <div id="promotions-modal" class="hidden fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-[380px] rounded-[24px] p-4 shadow-2xl text-xs">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-[#101114]">คูปองและโปรโมชั่น</h3>
                <button type="button" onclick="closePromotionsModal()" class="text-slate-400 cursor-pointer">✕</button>
            </div>
            <div class="py-3 space-y-2">
                <div class="p-2.5 rounded-xl bg-[#FFF0F1] border border-[#FAD4D8] flex items-center justify-between">
                    <div>
                        <div class="font-bold text-[#D90012]">ส่วนลด ฿10 (โค้ด DISC10)</div>
                        <div class="text-[10px] text-[#596272]">เมื่อสั่งขั้นต่ำ ฿150</div>
                    </div>
                    <button type="button" onclick="claimAndApplyCoupon('DISC10'); closePromotionsModal();" class="px-2.5 py-1 rounded-full bg-[#D90012] text-white text-[10px] font-bold cursor-pointer">เก็บคูปอง</button>
                </div>
                <div class="p-2.5 rounded-xl bg-[#FFF0F1] border border-[#FAD4D8] flex items-center justify-between">
                    <div>
                        <div class="font-bold text-[#D90012]">ส่วนลด ฿20 (โค้ด DISC20)</div>
                        <div class="text-[10px] text-[#596272]">เมื่อสั่งขั้นต่ำ ฿300</div>
                    </div>
                    <button type="button" onclick="claimAndApplyCoupon('DISC20'); closePromotionsModal();" class="px-2.5 py-1 rounded-full bg-[#D90012] text-white text-[10px] font-bold cursor-pointer">เก็บคูปอง</button>
                </div>
            </div>
            <button type="button" onclick="closePromotionsModal()" class="w-full mt-2 py-2 rounded-xl bg-slate-100 text-xs font-bold cursor-pointer">ปิด</button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- HERO BANNER SETTINGS MODAL (MOBILE FIXED PREVIEW & ADJUSTMENTS)           -->
    <!-- ========================================================================= -->
    <div id="hero-banner-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/75 backdrop-blur-xs p-3 overflow-y-auto" onclick="if(event.target === this) closeHeroBannerModal()">
        <div class="bg-white rounded-[26px] max-w-[420px] w-full shadow-2xl border border-slate-100 overflow-hidden flex flex-col my-auto max-h-[92vh]">
            <!-- Modal Header -->
            <div class="p-4 bg-gradient-to-r from-[#4A0408] via-[#8B0912] to-[#4A0408] text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-white/15 flex items-center justify-center text-base">
                        🖼️
                    </div>
                    <div>
                        <h3 class="text-sm font-bold leading-tight">ตั้งค่าภาพปกหน้าร้าน</h3>
                        <p class="text-[11px] text-rose-200/90 font-normal">ปรับตำแหน่ง ซูม และจัดการภาพปกสำหรับโมบาย</p>
                    </div>
                </div>
                <button type="button" onclick="closeHeroBannerModal()" class="w-7 h-7 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center text-white cursor-pointer transition">
                    ✕
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-4 space-y-4 overflow-y-auto">
                <!-- 1. Mobile Fixed Live Preview Frame -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <span class="w-1.5 h-3.5 rounded-full bg-[#E50914]"></span>
                            <span>ตัวอย่างขนาดจริงบนโมบาย (Mobile 16:5.3)</span>
                        </span>
                        <span class="text-[10.5px] text-slate-500 font-medium">กดปุ่มปรับตำแหน่งสดได้ทันที</span>
                    </div>
                    
                    <div class="relative w-full rounded-2xl overflow-hidden border-2 border-slate-300/80 shadow-md bg-slate-900 select-none" style="aspect-ratio: 592/195;">
                        <img id="modal-hero-preview-img"
                             src="{{ $heroBannerImage ?? asset('assets/online/hero-banner-ref.png') }}"
                             alt="พรีวิวภาพปกโมบาย"
                             class="w-full h-full object-cover transition-all duration-75 pointer-events-none"
                             style="object-position: {{ $heroBannerPosition['x'] ?? 50 }}% {{ $heroBannerPosition['y'] ?? 50 }}%; transform: scale({{ $heroBannerPosition['scale'] ?? 1 }});">

                        <!-- Overlay Mock Top Floating Buttons (แสดงตำแหน่งจริงของปุ่มบนมือถือ) -->
                        <div class="absolute top-2 inset-x-2 flex items-center justify-between pointer-events-none opacity-85">
                            <div class="w-6 h-6 rounded-full bg-white/90 shadow text-slate-800 text-[10px] flex items-center justify-center font-bold">‹</div>
                            <div class="flex items-center gap-1">
                                <div class="w-6 h-6 rounded-full bg-white/90 shadow text-[9px] flex items-center justify-center">🔍</div>
                                <div class="w-6 h-6 rounded-full bg-white/90 shadow text-[9px] flex items-center justify-center text-rose-600">❤️</div>
                                <div class="w-6 h-6 rounded-full bg-white/90 shadow text-[9px] flex items-center justify-center">🔗</div>
                                <div class="w-6 h-6 rounded-full bg-white/90 shadow text-[9px] flex items-center justify-center font-bold">⋮</div>
                            </div>
                        </div>

                        <!-- Guide Label -->
                        <div class="absolute bottom-1.5 right-2 px-2 py-0.5 rounded-md bg-black/60 backdrop-blur-xs text-white text-[9.5px] font-medium pointer-events-none">
                            📱 สัดส่วนโมบาย
                        </div>
                    </div>
                </div>

                <!-- 2. Controls: D-Pad & Sliders (ปรับซ้าย ขวา บน ล่าง และ ซูม) -->
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700">ปรับมุมมอง (ซ้าย ขวา บน ล่าง)</span>
                        <button type="button" onclick="resetHeroBannerPosition()" class="text-[11px] font-bold text-rose-600 hover:text-rose-700 cursor-pointer">
                            🔄 รีเซ็ตกึ่งกลาง
                        </button>
                    </div>

                    <!-- D-Pad Buttons + Zoom -->
                    <div class="flex items-center justify-around gap-2 pt-1">
                        <!-- Directional Cross -->
                        <div class="grid grid-cols-3 gap-1.5 w-32 shrink-0">
                            <div></div>
                            <button type="button" onclick="nudgeHeroPos(0, -5)" title="เลื่อนขึ้น" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-rose-50 hover:text-[#E50914] shadow-2xs font-bold text-xs flex items-center justify-center cursor-pointer active:scale-95">
                                ⬆️
                            </button>
                            <div></div>

                            <button type="button" onclick="nudgeHeroPos(-5, 0)" title="เลื่อนซ้าย" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-rose-50 hover:text-[#E50914] shadow-2xs font-bold text-xs flex items-center justify-center cursor-pointer active:scale-95">
                                ⬅️
                            </button>
                            <button type="button" onclick="resetHeroBannerPosition()" title="กึ่งกลาง" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-rose-50 hover:text-[#E50914] shadow-2xs text-[10px] font-bold flex items-center justify-center cursor-pointer active:scale-95">
                                🎯
                            </button>
                            <button type="button" onclick="nudgeHeroPos(5, 0)" title="เลื่อนขวา" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-rose-50 hover:text-[#E50914] shadow-2xs font-bold text-xs flex items-center justify-center cursor-pointer active:scale-95">
                                ➡️
                            </button>

                            <div></div>
                            <button type="button" onclick="nudgeHeroPos(0, 5)" title="เลื่อนลง" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-rose-50 hover:text-[#E50914] shadow-2xs font-bold text-xs flex items-center justify-center cursor-pointer active:scale-95">
                                ⬇️
                            </button>
                            <div></div>
                        </div>

                        <!-- Zoom & Reset Quick Controls -->
                        <div class="flex flex-col gap-1.5 flex-1 max-w-[170px]">
                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="nudgeHeroZoom(-0.1)" title="ซูมออก" class="flex-1 py-1.5 px-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold shadow-2xs flex items-center justify-center gap-1 cursor-pointer active:scale-95">
                                    <span>➖</span> <span>ซูมออก</span>
                                </button>
                                <button type="button" onclick="nudgeHeroZoom(0.1)" title="ซูมเข้า" class="flex-1 py-1.5 px-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold shadow-2xs flex items-center justify-center gap-1 cursor-pointer active:scale-95">
                                    <span>➕</span> <span>ซูมเข้า</span>
                                </button>
                            </div>
                            <button type="button" onclick="restoreDefaultHeroImage()" class="w-full py-1.5 px-2 rounded-xl bg-white border border-rose-200 text-rose-700 hover:bg-rose-50 text-[11px] font-bold shadow-2xs flex items-center justify-center gap-1 cursor-pointer transition">
                                <span>↩️</span> <span>ใช้ภาพปกเริ่มต้นของร้าน</span>
                            </button>
                        </div>
                    </div>

                    <!-- Sliders with Live Values -->
                    <div class="space-y-1.5 pt-1 text-[11px]">
                        <div class="flex items-center justify-between text-slate-600 font-medium">
                            <span>แนวนอน (ซ้าย - ขวา):</span>
                            <span id="hero-val-x" class="font-bold text-slate-800">{{ $heroBannerPosition['x'] ?? 50 }}%</span>
                        </div>
                        <input type="range" id="hero-slider-x" min="0" max="100" value="{{ $heroBannerPosition['x'] ?? 50 }}" oninput="updateHeroSliderPos()" class="w-full accent-[#E50914] cursor-pointer">

                        <div class="flex items-center justify-between text-slate-600 font-medium pt-1">
                            <span>แนวตั้ง (บน - ล่าง):</span>
                            <span id="hero-val-y" class="font-bold text-slate-800">{{ $heroBannerPosition['y'] ?? 50 }}%</span>
                        </div>
                        <input type="range" id="hero-slider-y" min="0" max="100" value="{{ $heroBannerPosition['y'] ?? 50 }}" oninput="updateHeroSliderPos()" class="w-full accent-[#E50914] cursor-pointer">

                        <div class="flex items-center justify-between text-slate-600 font-medium pt-1">
                            <span>อัตราขยาย (Zoom):</span>
                            <span id="hero-val-scale" class="font-bold text-slate-800">{{ $heroBannerPosition['scale'] ?? 1 }}x</span>
                        </div>
                        <input type="range" id="hero-slider-scale" min="1" max="2.5" step="0.05" value="{{ $heroBannerPosition['scale'] ?? 1 }}" oninput="updateHeroSliderPos()" class="w-full accent-[#E50914] cursor-pointer">
                    </div>
                </div>

                <!-- 3. Upload New Image (Canvas Compress to URL) -->
                <div>
                    <label class="flex items-center justify-center gap-2 p-3 rounded-2xl border-2 border-dashed border-rose-300 hover:border-[#E50914] bg-rose-50/60 hover:bg-rose-50 text-[#E50914] text-xs font-bold cursor-pointer transition select-none">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <span id="hero-upload-label">📷 เลือกรูปภาพใหม่จากเครื่อง (บีบอัดความละเอียดสูงอัตโนมัติ)</span>
                        <input type="file" id="hero-banner-file-input" accept="image/*" class="hidden" onchange="handleHeroBannerUpload(event)">
                    </label>
                    <canvas id="hero-compress-canvas" class="hidden"></canvas>
                </div>

                <!-- 4. History of Past Images (ภาพปกเก่าๆ + กากบาทมุมขวาบนไว้ลบ) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <span class="w-1.5 h-3.5 rounded-full bg-[#E50914]"></span>
                            <span>ประวัติภาพปกเก่า & ภาพที่บันทึกไว้</span>
                        </span>
                        <span class="text-[10px] text-slate-400">กด ✕ บนรูปเพื่อลบ</span>
                    </div>

                    <div id="hero-history-grid" class="grid grid-cols-4 gap-2.5 max-h-40 overflow-y-auto p-1 pr-1.5">
                        <!-- Rendered by JS -->
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2 shrink-0">
                <button type="button" onclick="closeHeroBannerModal()" class="py-2.5 px-4 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold cursor-pointer transition">
                    ยกเลิก
                </button>
                <button type="button" id="btn-save-hero-banner" onclick="saveHeroBannerSettings()" class="py-2.5 px-5 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-bold shadow-sm inline-flex items-center gap-1.5 cursor-pointer transition active:scale-95">
                    <span>💾 บันทึกภาพปกนี้</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- PROFILE LOGO SETTINGS MODAL (MOBILE LOGO BADGE & ADJUSTMENTS)             -->
    <!-- ========================================================================= -->
    <div id="profile-logo-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/75 backdrop-blur-xs p-3 overflow-y-auto" onclick="if(event.target === this) closeProfileLogoModal()">
        <div class="bg-white rounded-[26px] max-w-[420px] w-full shadow-2xl border border-slate-100 overflow-hidden flex flex-col my-auto max-h-[92vh]">
            <!-- Modal Header -->
            <div class="p-4 bg-gradient-to-r from-[#500000] via-[#900000] to-[#500000] text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-white/15 flex items-center justify-center text-base">
                        🔴
                    </div>
                    <div>
                        <h3 class="text-sm font-bold leading-tight">ตั้งค่ารูปโปรไฟล์ / โลโก้ร้าน</h3>
                        <p class="text-[11px] text-rose-200/90 font-normal">ปรับตำแหน่ง ซูม และจัดการรูปโปรไฟล์ร้าน</p>
                    </div>
                </div>
                <button type="button" onclick="closeProfileLogoModal()" class="w-7 h-7 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center text-white cursor-pointer transition">
                    ✕
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-4 space-y-4 overflow-y-auto">
                <!-- 1. Live Preview Frame (Square-Rounded badge matching mobile) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <span class="w-1.5 h-3.5 rounded-full bg-[#900000]"></span>
                            <span>ตัวอย่างรูปโปรไฟล์ / โลโก้ร้าน</span>
                        </span>
                        <span class="text-[10.5px] text-slate-500 font-medium">กดปุ่มปรับตำแหน่งสดได้ทันที</span>
                    </div>

                    <div class="p-4 bg-slate-100/80 rounded-2xl border border-slate-200/80 flex flex-col items-center justify-center">
                        <div class="relative w-32 h-32 rounded-[24px] border-[4px] border-white shadow-xl overflow-hidden bg-[#900000] select-none">
                            <img id="modal-logo-preview-img"
                                 src="{{ $profileLogoImage ?? asset('assets/brand/logo-card.png') }}"
                                 alt="พรีวิวโลโก้ร้าน"
                                 class="w-full h-full object-cover transition-all duration-75 pointer-events-none"
                                 style="object-position: {{ $profileLogoPosition['x'] ?? 50 }}% {{ $profileLogoPosition['y'] ?? 50 }}%; transform: scale({{ $profileLogoPosition['scale'] ?? 1 }});">
                        </div>
                        <div class="mt-2 text-center text-[10.5px] font-bold text-slate-600">
                            ร้านหม่าล่าสตรีท
                        </div>
                    </div>
                </div>

                <!-- 2. Controls: D-Pad & Sliders (ปรับซ้าย ขวา บน ล่าง และ ซูม) -->
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700">ปรับมุมมอง (ซ้าย ขวา บน ล่าง)</span>
                        <button type="button" onclick="resetProfileLogoPosition()" class="text-[11px] font-bold text-rose-600 hover:text-rose-700 cursor-pointer">
                            🔄 รีเซ็ตกึ่งกลาง
                        </button>
                    </div>

                    <!-- D-Pad Buttons + Zoom -->
                    <div class="flex items-center justify-around gap-2 pt-1">
                        <!-- Directional Cross -->
                        <div class="grid grid-cols-3 gap-1.5 w-32 shrink-0">
                            <div></div>
                            <button type="button" onclick="nudgeLogoPos(0, -5)" title="เลื่อนขึ้น" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-rose-50 hover:text-[#900000] shadow-2xs font-bold text-xs flex items-center justify-center cursor-pointer active:scale-95">
                                ⬆️
                            </button>
                            <div></div>

                            <button type="button" onclick="nudgeLogoPos(-5, 0)" title="เลื่อนซ้าย" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-rose-50 hover:text-[#900000] shadow-2xs font-bold text-xs flex items-center justify-center cursor-pointer active:scale-95">
                                ⬅️
                            </button>
                            <button type="button" onclick="resetProfileLogoPosition()" title="กึ่งกลาง" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-rose-50 hover:text-[#900000] shadow-2xs text-[10px] font-bold flex items-center justify-center cursor-pointer active:scale-95">
                                🎯
                            </button>
                            <button type="button" onclick="nudgeLogoPos(5, 0)" title="เลื่อนขวา" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-rose-50 hover:text-[#900000] shadow-2xs font-bold text-xs flex items-center justify-center cursor-pointer active:scale-95">
                                ➡️
                            </button>

                            <div></div>
                            <button type="button" onclick="nudgeLogoPos(0, 5)" title="เลื่อนลง" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-rose-50 hover:text-[#900000] shadow-2xs font-bold text-xs flex items-center justify-center cursor-pointer active:scale-95">
                                ⬇️
                            </button>
                            <div></div>
                        </div>

                        <!-- Zoom & Reset Quick Controls -->
                        <div class="flex flex-col gap-1.5 flex-1 max-w-[170px]">
                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="nudgeLogoZoom(-0.1)" title="ซูมออก" class="flex-1 py-1.5 px-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold shadow-2xs flex items-center justify-center gap-1 cursor-pointer active:scale-95">
                                    <span>➖</span> <span>ซูมออก</span>
                                </button>
                                <button type="button" onclick="nudgeLogoZoom(0.1)" title="ซูมเข้า" class="flex-1 py-1.5 px-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold shadow-2xs flex items-center justify-center gap-1 cursor-pointer active:scale-95">
                                    <span>➕</span> <span>ซูมเข้า</span>
                                </button>
                            </div>
                            <button type="button" onclick="restoreDefaultLogoImage()" class="w-full py-1.5 px-2 rounded-xl bg-white border border-rose-200 text-rose-700 hover:bg-rose-50 text-[11px] font-bold shadow-2xs flex items-center justify-center gap-1 cursor-pointer transition">
                                <span>↩️</span> <span>ใช้โลโก้เริ่มต้นของร้าน</span>
                            </button>
                        </div>
                    </div>

                    <!-- Sliders with Live Values -->
                    <div class="space-y-1.5 pt-1 text-[11px]">
                        <div class="flex items-center justify-between text-slate-600 font-medium">
                            <span>แนวนอน (ซ้าย - ขวา):</span>
                            <span id="logo-val-x" class="font-bold text-slate-800">{{ $profileLogoPosition['x'] ?? 50 }}%</span>
                        </div>
                        <input type="range" id="logo-slider-x" min="0" max="100" value="{{ $profileLogoPosition['x'] ?? 50 }}" oninput="updateLogoSliderPos()" class="w-full accent-[#900000] cursor-pointer">

                        <div class="flex items-center justify-between text-slate-600 font-medium pt-1">
                            <span>แนวตั้ง (บน - ล่าง):</span>
                            <span id="logo-val-y" class="font-bold text-slate-800">{{ $profileLogoPosition['y'] ?? 50 }}%</span>
                        </div>
                        <input type="range" id="logo-slider-y" min="0" max="100" value="{{ $profileLogoPosition['y'] ?? 50 }}" oninput="updateLogoSliderPos()" class="w-full accent-[#900000] cursor-pointer">

                        <div class="flex items-center justify-between text-slate-600 font-medium pt-1">
                            <span>อัตราขยาย (Zoom):</span>
                            <span id="logo-val-scale" class="font-bold text-slate-800">{{ $profileLogoPosition['scale'] ?? 1 }}x</span>
                        </div>
                        <input type="range" id="logo-slider-scale" min="1" max="2.5" step="0.05" value="{{ $profileLogoPosition['scale'] ?? 1 }}" oninput="updateLogoSliderPos()" class="w-full accent-[#900000] cursor-pointer">
                    </div>
                </div>

                <!-- 3. Upload New Image (Canvas Compress to URL) -->
                <div>
                    <label class="flex items-center justify-center gap-2 p-3 rounded-2xl border-2 border-dashed border-rose-300 hover:border-[#900000] bg-rose-50/60 hover:bg-rose-50 text-[#900000] text-xs font-bold cursor-pointer transition select-none">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                        <span id="logo-upload-label">📷 เลือกรูปภาพใหม่จากเครื่อง (บีบอัดความละเอียดสูงอัตโนมัติ)</span>
                        <input type="file" id="profile-logo-file-input" accept="image/*" class="hidden" onchange="handleProfileLogoUpload(event)">
                    </label>
                    <canvas id="logo-compress-canvas" class="hidden"></canvas>
                </div>

                <!-- 4. History of Past Images (ภาพโปรไฟล์เก่าๆ + กากบาทมุมขวาบนไว้ลบ) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <span class="w-1.5 h-3.5 rounded-full bg-[#900000]"></span>
                            <span>ประวัติรูปโปรไฟล์เก่า & ภาพที่บันทึกไว้</span>
                        </span>
                        <span class="text-[10px] text-slate-400">กด ✕ บนรูปเพื่อลบ</span>
                    </div>

                    <div id="logo-history-grid" class="grid grid-cols-4 gap-2.5 max-h-40 overflow-y-auto p-1 pr-1.5">
                        <!-- Rendered by JS -->
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2 shrink-0">
                <button type="button" onclick="closeProfileLogoModal()" class="py-2.5 px-4 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold cursor-pointer transition">
                    ยกเลิก
                </button>
                <button type="button" id="btn-save-profile-logo" onclick="saveProfileLogoSettings()" class="py-2.5 px-5 rounded-xl bg-[#900000] hover:bg-[#700000] text-white text-xs font-bold shadow-sm inline-flex items-center gap-1.5 cursor-pointer transition active:scale-95">
                    <span>💾 บันทึกรูปโปรไฟล์นี้</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast-container" class="fixed top-5 left-1/2 -translate-x-1/2 z-50 space-y-1.5 pointer-events-none w-full max-w-[380px] px-4"></div>

    @php
        $jsProducts = $allProducts->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->display_name ?: $p->name,
                'price_per_stick' => (float) $p->price_per_stick,
                'stock_sticks' => (int) $p->stock_sticks,
                'image_url' => $p->display_image,
                'category_slug' => $p->category->slug ?? 'all',
                'description' => $p->description,
                'supports_grill' => (bool) $p->supports_grill,
                'supports_soup' => (bool) $p->supports_soup,
            ];
        })->values();
    @endphp

    <!-- ========================================================================= -->
    <!-- JAVASCRIPT: STOREFRONT LOGIC (100% SELF-CONTAINED IN THIS TAB)            -->
    <!-- ========================================================================= -->
    <script>
        const rawProducts = @json($jsProducts);
        const productMap = {};
        rawProducts.forEach(p => { productMap[p.id] = p; });

        let cart = [];
        let orderingMethod = 'delivery';
        let activeCoupon = null;
        let currentModalProductId = null;
        let currentModalQty = 1;

        // Clean initial state: cart starts completely empty
        document.addEventListener('DOMContentLoaded', () => {
            updateCartUI();
        });

        // Quick +/- directly on product cards
        function quickAddStick(productId) {
            const p = productMap[productId];
            if (!p) return;

            const existing = cart.find(item => item.product_id === p.id && item.cooking_type === 'grill' && item.spice_level === 'medium' && !item.notes);
            const totalForProduct = getProductQtyInCart(p.id);
            if (p.stock_sticks && totalForProduct >= p.stock_sticks) {
                showToast('สินค้าคงเหลือ ' + p.stock_sticks + ' ไม้');
                return;
            }

            if (existing) {
                existing.quantity += 1;
            } else {
                cart.push({
                    product_id: p.id,
                    name: p.name,
                    unit_price: parseFloat(p.price_per_stick),
                    quantity: 1,
                    cooking_type: 'grill',
                    spice_level: 'medium',
                    notes: ''
                });
            }
            updateCartUI();
        }

        function quickRemoveStick(productId) {
            for (let i = cart.length - 1; i >= 0; i--) {
                if (cart[i].product_id === productId) {
                    cart[i].quantity -= 1;
                    if (cart[i].quantity <= 0) {
                        cart.splice(i, 1);
                    }
                    break;
                }
            }
            updateCartUI();
        }

        function getProductQtyInCart(productId) {
            return cart.reduce((sum, item) => item.product_id === productId ? sum + item.quantity : sum, 0);
        }

        function renderRowActionButtons() {
            rawProducts.forEach(p => {
                const container = document.getElementById('row-action-' + p.id);
                if (!container) return;
                if (p.stock_sticks <= 0) return;

                const qty = getProductQtyInCart(p.id);
                if (qty > 0) {
                    container.innerHTML = `
                        <div class="flex items-center gap-1.5 bg-rose-50 border border-rose-200/60 rounded-full px-1.5 py-0.5">
                            <button type="button" onclick="quickRemoveStick(${p.id})"
                                    class="w-[22px] h-[22px] rounded-full bg-white text-[#D90012] shadow-2xs flex items-center justify-center text-sm font-bold leading-none active:scale-90 transition-transform cursor-pointer border border-rose-200">
                                −
                            </button>
                            <span class="text-[12px] font-extrabold text-[#101114] min-w-[14px] text-center">${qty}</span>
                            <button type="button" onclick="quickAddStick(${p.id})"
                                    class="w-[22px] h-[22px] rounded-full bg-[#D90012] hover:bg-[#B8000F] text-white flex items-center justify-center text-sm font-bold leading-none shadow-2xs active:scale-90 transition-transform cursor-pointer">
                                +
                            </button>
                        </div>
                    `;
                } else {
                    container.innerHTML = `
                        <button type="button" onclick="quickAddStick(${p.id})"
                                class="w-[28px] h-[28px] rounded-full bg-[#D90012] hover:bg-[#B8000F] text-white flex items-center justify-center text-base font-bold leading-none shadow-xs active:scale-90 transition-transform cursor-pointer">
                            +
                        </button>
                    `;
                }
            });
        }

        // Ordering Method Switcher
        function setOrderingMethod(method) {
            orderingMethod = method;
            ['delivery', 'pickup', 'booking'].forEach(m => {
                const btn = document.getElementById('btn-method-' + m);
                if (!btn) return;
                const title = btn.querySelector('.method-title');
                const sub = btn.querySelector('.method-sub');
                const icon = btn.querySelector('.method-icon');
                const scooterImg = document.getElementById('scooter-delivery-icon');

                if (m === method) {
                    btn.className = 'rounded-[14px] px-2 py-2 sm:px-2.5 sm:py-2.5 bg-gradient-to-r from-[#D91A1E] via-[#CB1318] to-[#B80F13] text-white shadow-[0_4px_14px_rgba(217,26,30,0.35)] flex items-center gap-1.5 sm:gap-2 text-left transition-all cursor-pointer border border-[#E6282C]';
                    if (title) title.className = 'method-title text-[11px] sm:text-[12px] font-bold leading-tight truncate text-white';
                    if (sub) sub.className = 'method-sub text-[8.5px] sm:text-[9.5px] text-white/95 leading-tight truncate mt-0.5 font-normal tracking-tight';
                    if (icon) {
                        icon.classList.remove('text-[#101114]');
                        icon.classList.add('text-white');
                    }
                    if (m === 'delivery' && scooterImg) {
                        scooterImg.src = '{{ asset("assets/brand/scooter-white.png") }}';
                    }
                } else {
                    btn.className = 'rounded-[14px] px-2 py-2 sm:px-2.5 sm:py-2.5 bg-white text-[#101114] border border-[#E5E7EB] shadow-[0_2px_8px_rgba(0,0,0,0.02)] flex items-center gap-1.5 sm:gap-2 text-left transition-all cursor-pointer';
                    if (title) title.className = 'method-title text-[11px] sm:text-[12px] font-bold leading-tight truncate text-[#101114]';
                    if (sub) sub.className = 'method-sub text-[8.5px] sm:text-[9.5px] text-[#596272] leading-tight truncate mt-0.5 font-normal tracking-tight';
                    if (icon) {
                        icon.classList.remove('text-white');
                        icon.classList.add('text-[#101114]');
                    }
                    if (m === 'delivery' && scooterImg) {
                        scooterImg.src = '{{ asset("assets/brand/scooter-dark.png") }}';
                    }
                }
            });

            const addrGroup = document.getElementById('delivery-address-group');
            const timeGroup = document.getElementById('pickup-time-group');
            const methodSummaryName = document.getElementById('summary-method-name');
            if (method === 'delivery') {
                addrGroup?.classList.remove('hidden');
                timeGroup?.classList.add('hidden');
                if (methodSummaryName) methodSummaryName.innerText = 'สั่งเดลิเวอรี่';
            } else {
                addrGroup?.classList.add('hidden');
                timeGroup?.classList.remove('hidden');
                if (methodSummaryName) methodSummaryName.innerText = method === 'pickup' ? 'รับที่ร้าน' : 'จองคิว';
            }

            updateCartUI();
            showToast(method === 'delivery' ? 'เลือก: สั่งเดลิเวอรี่' : (method === 'pickup' ? 'เลือก: รับที่ร้าน' : 'เลือก: จองคิว'));
        }

        // Category Filter
        function filterOnlineCategory(slug, element) {
            document.querySelectorAll('.online-cat-pill').forEach(btn => {
                btn.classList.remove('active', 'border-[#E60018]', 'bg-[#E60018]', 'text-white');
                btn.classList.add('border-slate-200/90', 'bg-white', 'text-[#101114]');
                const dots = btn.querySelectorAll('.cat-dot');
                dots.forEach(d => { d.classList.remove('bg-white'); d.classList.add('bg-[#E60018]'); });
                const label = btn.querySelector('.cat-label');
                if (label) {
                    label.classList.remove('text-white', 'font-bold');
                    label.classList.add('text-[#101114]', 'font-semibold');
                }
            });

            if (element) {
                element.classList.add('active', 'border-[#E60018]', 'bg-[#E60018]', 'text-white');
                element.classList.remove('border-slate-200/90', 'bg-white', 'text-[#101114]');
                const dots = element.querySelectorAll('.cat-dot');
                dots.forEach(d => { d.classList.remove('bg-[#E60018]'); d.classList.add('bg-white'); });
                const label = element.querySelector('.cat-label');
                if (label) {
                    label.classList.remove('text-[#101114]', 'font-semibold');
                    label.classList.add('text-white', 'font-bold');
                }
            }

            const items = document.querySelectorAll('.product-item');
            let visibleCount = 0;
            items.forEach(item => {
                const itemCat = item.getAttribute('data-category');
                if (slug === 'all' || itemCat === slug) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            document.getElementById('empty-product-state')?.classList.toggle('hidden', visibleCount > 0);
            const countSpan = document.getElementById('all-menu-count');
            if (countSpan) countSpan.innerText = visibleCount;
        }

        // Search & Sort
        function toggleSearchBar() {
            const wrapper = document.getElementById('search-bar-wrapper');
            if (!wrapper) return;
            wrapper.classList.toggle('hidden');
            if (!wrapper.classList.contains('hidden')) {
                document.getElementById('search-input')?.focus();
            }
        }

        function clearSearchBar() {
            const input = document.getElementById('search-input');
            if (input) input.value = '';
            handleMenuSearch();
            document.getElementById('search-bar-wrapper')?.classList.add('hidden');
        }

        function handleMenuSearch() {
            const query = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
            const items = document.querySelectorAll('.product-item');
            let visibleCount = 0;
            items.forEach(item => {
                const name = (item.getAttribute('data-name') || '').toLowerCase();
                if (name.includes(query)) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });
            document.getElementById('empty-product-state')?.classList.toggle('hidden', visibleCount > 0);
            const countSpan = document.getElementById('all-menu-count');
            if (countSpan) countSpan.innerText = visibleCount;
        }

        function handleMenuSort() {
            const sortVal = document.getElementById('sort-select')?.value;
            const container = document.getElementById('product-list-container');
            if (!container) return;
            const items = Array.from(container.children);
            items.sort((a, b) => {
                if (sortVal === 'price_asc') return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
                if (sortVal === 'price_desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
                if (sortVal === 'stock') return parseInt(b.dataset.stock) - parseInt(a.dataset.stock);
                return parseInt(a.dataset.id) - parseInt(b.dataset.id);
            });
            items.forEach(item => container.appendChild(item));
        }

        // Customization Modal
        function openCustomizationModal(productId) {
            const p = productMap[productId];
            if (!p || p.stock_sticks <= 0) return;
            currentModalProductId = productId;
            currentModalQty = 1;

            document.getElementById('modal-product-img').src = p.image_url || '{{ asset("assets/menu/food-fallback.svg") }}';
            document.getElementById('modal-product-name').innerText = p.name;
            document.getElementById('modal-product-price').innerText = '฿' + parseFloat(p.price_per_stick).toFixed(0) + ' /ไม้';
            document.getElementById('modal-special-notes').value = '';
            document.getElementById('modal-qty').innerText = '1';

            updateModalPrice();
            document.getElementById('custom-modal').classList.remove('hidden');
        }

        function closeCustomizationModal() {
            document.getElementById('custom-modal').classList.add('hidden');
        }

        function adjustModalQty(delta) {
            currentModalQty = Math.max(1, currentModalQty + delta);
            const p = productMap[currentModalProductId];
            if (p && p.stock_sticks && currentModalQty > p.stock_sticks) {
                currentModalQty = p.stock_sticks;
            }
            document.getElementById('modal-qty').innerText = currentModalQty;
            updateModalPrice();
        }

        function updateModalPrice() {
            const p = productMap[currentModalProductId];
            if (!p) return;
            const total = parseFloat(p.price_per_stick) * currentModalQty;
            document.getElementById('modal-item-total').innerText = '฿' + total.toFixed(0);
        }

        function confirmAddToCart() {
            const p = productMap[currentModalProductId];
            if (!p) return;

            const cookingType = document.querySelector('input[name="opt_cooking_type"]:checked')?.value || 'grill';
            const spiceLevel = document.querySelector('input[name="opt_spice_level"]:checked')?.value || 'medium';
            const specialNotes = document.getElementById('modal-special-notes')?.value.trim() || '';

            const existing = cart.find(item =>
                item.product_id === p.id &&
                item.cooking_type === cookingType &&
                item.spice_level === spiceLevel &&
                item.notes === specialNotes
            );

            if (existing) {
                existing.quantity += currentModalQty;
            } else {
                cart.push({
                    product_id: p.id,
                    name: p.name,
                    unit_price: parseFloat(p.price_per_stick),
                    quantity: currentModalQty,
                    cooking_type: cookingType,
                    spice_level: spiceLevel,
                    notes: specialNotes
                });
            }

            closeCustomizationModal();
            updateCartUI();
            showToast('เพิ่ม ' + p.name + ' ลงตะกร้าแล้ว');
        }

        // Update Cart UI & Totals
        function updateCartUI() {
            let totalSticks = 0;
            let subtotal = 0;
            cart.forEach(item => {
                totalSticks += item.quantity;
                subtotal += item.unit_price * item.quantity;
            });

            let discount = 0;
            if (activeCoupon === 'DISC10' && subtotal >= 150) discount = 10;
            if (activeCoupon === 'DISC20' && subtotal >= 300) discount = 20;

            let shipping = 0;
            const grandTotal = Math.max(0, subtotal - discount + shipping);

            const badge = document.getElementById('cart-item-badge');
            if (badge) badge.innerText = totalSticks;

            const totalDisplay = document.getElementById('cart-total-price');
            if (totalDisplay) totalDisplay.innerText = '฿' + grandTotal.toFixed(0);

            const countLabel = document.getElementById('cart-count-label');
            if (countLabel) countLabel.innerText = totalSticks + ' รายการ';

            // Checkout modal summary
            const summarySticks = document.getElementById('summary-total-sticks');
            if (summarySticks) summarySticks.innerText = totalSticks;
            const summarySub = document.getElementById('summary-subtotal');
            if (summarySub) summarySub.innerText = '฿' + subtotal.toFixed(0);
            const summaryDisc = document.getElementById('summary-discount');
            if (summaryDisc) summaryDisc.innerText = '-฿' + discount.toFixed(0);
            const summaryShip = document.getElementById('summary-shipping');
            if (summaryShip) summaryShip.innerText = shipping === 0 ? 'ฟรี' : '฿' + shipping.toFixed(0);
            const summaryGrand = document.getElementById('summary-grand-total');
            if (summaryGrand) summaryGrand.innerText = '฿' + grandTotal.toFixed(0);
            const btnFinal = document.getElementById('btn-final-amount');
            if (btnFinal) btnFinal.innerText = '฿' + grandTotal.toFixed(0);

            renderRowActionButtons();
            renderCheckoutItems();
        }

        function renderCheckoutItems() {
            const container = document.getElementById('checkout-items-list');
            if (!container) return;
            if (cart.length === 0) {
                container.innerHTML = '<div class="text-center py-4 text-slate-400">ยังไม่มีรายการอาหารในตะกร้า</div>';
                return;
            }
            container.innerHTML = cart.map((item, idx) => `
                <div class="p-2.5 rounded-xl bg-white border border-slate-200 flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-[#101114] truncate">${item.name}</div>
                        <div class="text-[10px] text-[#596272]">
                            ${item.cooking_type === 'grill' ? '🍢 ย่างเตาถ่าน' : '🍲 ต้มซุปหม่าล่า'} • ${item.spice_level === 'mild' ? 'เผ็ดน้อย' : (item.spice_level === 'hot' ? 'เผ็ดมาก' : 'เผ็ดกลาง')}
                        </div>
                        <div class="text-[11px] font-bold text-[#D90012]">฿${(item.unit_price * item.quantity).toFixed(0)}</div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" onclick="adjustCartItemQty(${idx}, -1)" class="w-6 h-6 rounded-full bg-[#FFE4E6] text-[#D90012] font-bold flex items-center justify-center cursor-pointer">−</button>
                        <span class="w-4 text-center font-bold">${item.quantity}</span>
                        <button type="button" onclick="adjustCartItemQty(${idx}, 1)" class="w-6 h-6 rounded-full bg-[#D90012] text-white font-bold flex items-center justify-center cursor-pointer">+</button>
                    </div>
                </div>
            `).join('');
        }

        function adjustCartItemQty(index, delta) {
            if (!cart[index]) return;
            cart[index].quantity += delta;
            if (cart[index].quantity <= 0) cart.splice(index, 1);
            updateCartUI();
        }

        function clearCart() {
            cart = [];
            updateCartUI();
            showToast('ล้างตะกร้าเรียบร้อย');
        }

        // Coupons
        function claimAndApplyCoupon(code) {
            activeCoupon = code;
            const text10 = document.getElementById('coupon-text-DISC10');
            const text20 = document.getElementById('coupon-text-DISC20');
            if (text10) text10.innerText = code === 'DISC10' ? 'เก็บแล้ว ✓' : 'เก็บคูปอง';
            if (text20) text20.innerText = code === 'DISC20' ? 'เก็บแล้ว ✓' : 'เก็บคูปอง';
            updateCartUI();
            showToast('เก็บคูปอง ' + code + ' เรียบร้อยแล้ว');
        }

        function applyCustomCoupon() {
            const val = (document.getElementById('cart-coupon-input')?.value || '').trim().toUpperCase();
            if (val === 'DISC10' || val === 'DISC20') {
                claimAndApplyCoupon(val);
            } else {
                showToast('ไม่พบโค้ดส่วนลดนี้');
            }
        }

        // Checkout Modal & Real Order Submission
        function openCheckoutModal() {
            if (cart.length === 0) {
                showToast('กรุณาเลือกเมนูอาหารลงตะกร้าก่อนสั่งซื้อ');
                return;
            }
            updateCartUI();
            document.getElementById('checkout-modal').classList.remove('hidden');
        }

        function closeCheckoutModal() {
            document.getElementById('checkout-modal').classList.add('hidden');
        }

        async function submitOnlineOrder() {
            if (cart.length === 0) return;
            const name = document.getElementById('cust-name')?.value.trim();
            const phone = document.getElementById('cust-phone')?.value.trim();
            const address = document.getElementById('cust-address')?.value.trim();
            const pickupTime = document.getElementById('cust-pickup-time')?.value || 'ทันที (~15-20 นาที)';
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'promptpay';

            if (!name) { showToast('กรุณาระบุชื่อผู้สั่งอาหาร'); document.getElementById('cust-name')?.focus(); return; }
            if (!phone) { showToast('กรุณาระบุเบอร์โทรศัพท์'); document.getElementById('cust-phone')?.focus(); return; }
            if (orderingMethod === 'delivery' && !address) { showToast('กรุณาระบุที่อยู่จัดส่ง'); document.getElementById('cust-address')?.focus(); return; }

            const btn = document.getElementById('btn-submit-order');
            const spinner = document.getElementById('submit-btn-spinner');
            btn.disabled = true;
            spinner.classList.remove('hidden');

            const payload = {
                customer_name: name,
                customer_phone: phone,
                pickup_time: orderingMethod === 'delivery' ? ('เดลิเวอรี่: ' + address) : pickupTime,
                items: cart.map(item => ({
                    product_id: item.product_id,
                    quantity: item.quantity,
                    cooking_type: item.cooking_type,
                    grill_style: 'brush',
                    soup_style: 'mala_rich',
                    spiciness_level: item.spice_level,
                    special_instructions: item.notes || null,
                })),
                coupon_code: activeCoupon,
                payment_method: paymentMethod,
                notes: orderingMethod === 'delivery' ? ('จัดส่งที่: ' + address) : ('รับที่ร้าน/จองคิว: ' + pickupTime),
            };

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const res = await fetch('{{ route("online.submit") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                if (res.ok && result.success) {
                    closeCheckoutModal();
                    cart = [];
                    updateCartUI();
                    document.getElementById('success-queue-tag').innerText = result.queue_number || 'G01';
                    document.getElementById('success-order-no').innerText = result.order_no || '-';
                    document.getElementById('success-track-link').href = result.redirect_url || result.track_url || ('/order/track/' + result.order_no);
                    document.getElementById('success-modal').classList.remove('hidden');
                } else {
                    showToast(result.message || 'ไม่สามารถส่งออเดอร์ได้ กรุณาลองใหม่');
                }
            } catch (e) {
                showToast('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            } finally {
                btn.disabled = false;
                spinner.classList.add('hidden');
            }
        }

        function resetAfterSuccess() {
            document.getElementById('success-modal').classList.add('hidden');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Modals & Actions
        function openReviewsModal() { document.getElementById('reviews-modal')?.classList.remove('hidden'); }
        function closeReviewsModal() { document.getElementById('reviews-modal')?.classList.add('hidden'); }
        function openStoreInfoModal() { document.getElementById('store-info-modal')?.classList.remove('hidden'); }
        function closeStoreInfoModal() { document.getElementById('store-info-modal')?.classList.add('hidden'); }
        function openPromotionsModal() { document.getElementById('promotions-modal')?.classList.remove('hidden'); }
        function closePromotionsModal() { document.getElementById('promotions-modal')?.classList.add('hidden'); }

        let isFav = false;
        function toggleOnlineFavorite() {
            isFav = !isFav;
            const heart = document.getElementById('card-fav-heart');
            if (heart) {
                heart.classList.toggle('fill-current', isFav);
                heart.classList.toggle('fill-none', !isFav);
            }
            showToast(isFav ? 'บันทึกร้านหม่าล่าสตรีทเป็นร้านโปรดแล้ว ❤️' : 'นำออกจากร้านโปรดแล้ว');
        }

        function shareOnlineStore() {
            if (navigator.share) {
                navigator.share({ title: 'ร้านหม่าล่าสตรีท (Mala Street)', url: window.location.href }).catch(() => {});
            } else {
                navigator.clipboard?.writeText(window.location.href);
                showToast('คัดลอกลิงก์หน้าร้านแล้ว 📋');
            }
        }

        function showToast(msg) {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const el = document.createElement('div');
            el.className = 'bg-[#101114]/95 text-white px-3.5 py-2 rounded-full shadow-lg text-xs font-medium flex items-center justify-center gap-1.5 border border-white/10 transition-all duration-200';
            el.innerHTML = '<span>🔥</span><span>' + msg + '</span>';
            container.appendChild(el);
            setTimeout(() => el.remove(), 2200);
        }

        // 🔄 Real-time category sync with Stock / Price Tracker page (ชื่อหมวดหมู่, ไอคอน, การอนุมัติแสดงผล)
        function syncOnlineCategoryPills(categories) {
            if (!Array.isArray(categories)) return;
            const activeSlugs = new Set(categories.map(c => c.slug));
            categories.forEach(c => {
                const pill = document.querySelector(`.online-cat-pill[data-cat="${c.slug}"]`);
                if (!pill) return;
                const label = pill.querySelector('.cat-label');
                if (label && c.name && label.textContent.trim() !== c.name) {
                    label.textContent = c.name;
                }
                const img = pill.querySelector('img');
                if (img && c.icon && img.getAttribute('src') !== c.icon) {
                    img.src = c.icon;
                }
                if (img && c.name) {
                    img.alt = c.name;
                }
                pill.style.display = '';
            });
            if (activeSlugs.size > 0) {
                document.querySelectorAll('.online-cat-pill[data-cat]').forEach(pill => {
                    const slug = pill.getAttribute('data-cat');
                    if (slug && slug !== 'all' && !activeSlugs.has(slug)) {
                        pill.style.display = 'none';
                    }
                });
            }
        }

        function syncOnlineRecommendedProducts(recommendedProducts) {
            if (!Array.isArray(recommendedProducts)) return;
            const section = document.getElementById('recommended-section');
            const grid = document.getElementById('recommended-grid');
            if (!section || !grid) return;

            if (recommendedProducts.length === 0) {
                section.classList.add('hidden');
                grid.innerHTML = '';
                return;
            }

            recommendedProducts.forEach(p => {
                if (p && p.id) {
                    productMap[p.id] = p;
                }
            });

            grid.innerHTML = recommendedProducts.map(p => {
                const safeName = String(p.name || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                const priceFormatted = Math.round(Number(p.price_per_stick || 0)).toLocaleString('en-US');
                return `
                    <div class="bg-white rounded-[14px] p-1.5 border border-[#ECEEF2] shadow-[0_2px_6px_rgba(0,0,0,0.04)] flex flex-col justify-between" data-rec-product-id="${p.id}">
                        <div onclick="openCustomizationModal(${p.id})" class="relative w-full aspect-[16/10] rounded-[10px] overflow-hidden bg-slate-900 cursor-pointer">
                            <img src="${p.image_url}"
                                 alt="${safeName}"
                                 class="w-full h-full object-cover">
                        </div>

                        <div class="pt-1.5 px-0.5">
                            <h3 onclick="openCustomizationModal(${p.id})" class="text-[11px] font-bold text-[#101114] leading-tight truncate cursor-pointer">
                                ${safeName}
                            </h3>
                            <div class="flex items-center justify-between mt-1">
                                <div class="text-[12px] font-extrabold text-[#D90012] leading-none">
                                    ฿${priceFormatted}
                                    <span class="text-[9px] text-[#596272] font-normal">/ไม้</span>
                                </div>
                                <button type="button"
                                        onclick="quickAddStick(${p.id})"
                                        class="w-[22px] h-[22px] rounded-full bg-[#D90012] hover:bg-[#B8000F] text-white flex items-center justify-center text-sm font-bold leading-none shadow-2xs active:scale-90 transition-transform cursor-pointer"
                                        title="เพิ่มลงตะกร้า">
                                    +
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            section.classList.remove('hidden');
        }

        async function refreshOnlineCategoriesLive() {
            try {
                const res = await fetch('/order?categories_json=1', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data && data.success) {
                    if (Array.isArray(data.categories)) {
                        syncOnlineCategoryPills(data.categories);
                    }
                    if (Array.isArray(data.recommended_products)) {
                        syncOnlineRecommendedProducts(data.recommended_products);
                    }
                    if (data.hero_banner && data.hero_banner.image) {
                        const bannerImg = document.getElementById('online-hero-banner-img');
                        if (bannerImg) {
                            bannerImg.src = data.hero_banner.image;
                            const pos = data.hero_banner.position || { x: 50, y: 50, scale: 1 };
                            bannerImg.style.objectPosition = `${pos.x}% ${pos.y}%`;
                            bannerImg.style.transform = `scale(${pos.scale})`;
                        }
                    }
                    if (data.profile_logo && data.profile_logo.image) {
                        const logoImg = document.getElementById('online-profile-logo-img');
                        if (logoImg) {
                            logoImg.src = data.profile_logo.image;
                            const pos = data.profile_logo.position || { x: 50, y: 50, scale: 1 };
                            logoImg.style.objectPosition = `${pos.x}% ${pos.y}%`;
                            logoImg.style.transform = `scale(${pos.scale})`;
                        }
                    }
                    if (typeof data.show_promo_banner !== 'undefined') {
                        const promoBanner = document.getElementById('online-promo-banner');
                        const promoSwitch = document.getElementById('promo-banner-switch');
                        const statusText = document.getElementById('promo-switch-status-text');
                        if (promoBanner) {
                            if (data.show_promo_banner) {
                                promoBanner.classList.remove('hidden');
                            } else {
                                promoBanner.classList.add('hidden');
                            }
                        }
                        if (promoSwitch) promoSwitch.checked = Boolean(data.show_promo_banner);
                        if (statusText) statusText.textContent = data.show_promo_banner ? 'กำลังแสดงแถบคูปอง' : 'ซ่อนแถบคูปองอยู่';
                    }
                }
            } catch (e) {}
        }

        function applyOnlineCategorySyncMessage(data) {
            if (!data || !data.action) return;
            if (data.action === 'hero_banner_updated' && data.hero) {
                const bannerImg = document.getElementById('online-hero-banner-img');
                if (bannerImg) {
                    bannerImg.src = data.hero.image;
                    bannerImg.style.objectPosition = `${data.hero.x}% ${data.hero.y}%`;
                    bannerImg.style.transform = `scale(${data.hero.scale})`;
                }
            }
            if (data.action === 'profile_logo_updated' && data.logo) {
                const logoImg = document.getElementById('online-profile-logo-img');
                if (logoImg) {
                    logoImg.src = data.logo.image;
                    logoImg.style.objectPosition = `${data.logo.x}% ${data.logo.y}%`;
                    logoImg.style.transform = `scale(${data.logo.scale})`;
                }
            }
            if (data.action === 'promo_banner_toggled') {
                const promoBanner = document.getElementById('online-promo-banner');
                const promoSwitch = document.getElementById('promo-banner-switch');
                const statusText = document.getElementById('promo-switch-status-text');
                if (promoBanner) {
                    if (data.show) {
                        promoBanner.classList.remove('hidden');
                    } else {
                        promoBanner.classList.add('hidden');
                    }
                }
                if (promoSwitch) promoSwitch.checked = Boolean(data.show);
                if (statusText) statusText.textContent = data.show ? 'กำลังแสดงแถบคูปอง' : 'ซ่อนแถบคูปองอยู่';
            }
            const oldSlug = data.old_slug || data.slug;
            const pill = document.querySelector(`.online-cat-pill[data-cat="${oldSlug}"]`);
            if (data.action === 'category_deleted' || (data.action === 'category_updated' && data.is_active === false)) {
                if (pill) pill.style.display = 'none';
                return;
            }
            if (data.action === 'category_updated' && pill) {
                if (data.slug && data.slug !== oldSlug) {
                    pill.setAttribute('data-cat', data.slug);
                    pill.setAttribute('onclick', `filterOnlineCategory('${data.slug}', this)`);
                    document.querySelectorAll(`[data-category="${oldSlug}"]`).forEach(item => item.setAttribute('data-category', data.slug));
                }
                const label = pill.querySelector('.cat-label');
                if (label && data.name) label.textContent = data.name;
                const img = pill.querySelector('img');
                if (img && data.icon) img.src = data.icon;
                pill.style.display = '';
            }
            refreshOnlineCategoriesLive();
        }

        // =========================================================================
        // MASTER SETTINGS MENU (3-DOTS POPUP & PROMO BANNER TOGGLE)
        // =========================================================================
        function toggleMasterSettingsMenu(event) {
            if (event) {
                event.stopPropagation();
            }
            const menu = document.getElementById('master-settings-menu');
            if (!menu) return;
            const isHidden = menu.classList.contains('hidden');
            if (isHidden) {
                menu.classList.remove('hidden');
                setTimeout(() => {
                    document.addEventListener('click', handleMasterMenuClickOutside, { once: true });
                }, 10);
            } else {
                menu.classList.add('hidden');
            }
        }

        function closeMasterSettingsMenu() {
            const menu = document.getElementById('master-settings-menu');
            if (menu) menu.classList.add('hidden');
            document.removeEventListener('click', handleMasterMenuClickOutside);
        }

        function handleMasterMenuClickOutside(event) {
            const menu = document.getElementById('master-settings-menu');
            const btn = document.getElementById('btn-master-settings');
            if (menu && !menu.contains(event.target) && (!btn || !btn.contains(event.target))) {
                menu.classList.add('hidden');
            }
        }

        async function togglePromoBannerVisibility(shouldShow) {
            const promoBanner = document.getElementById('online-promo-banner');
            const statusText = document.getElementById('promo-switch-status-text');

            if (promoBanner) {
                if (shouldShow) {
                    promoBanner.classList.remove('hidden');
                } else {
                    promoBanner.classList.add('hidden');
                }
            }
            if (statusText) {
                statusText.textContent = shouldShow ? 'กำลังแสดงแถบคูปอง' : 'ซ่อนแถบคูปองอยู่';
            }

            try {
                const res = await fetch('{{ route("online.toggle_promo_banner") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ show: shouldShow })
                });
                const data = await res.json();
                if (data && data.success) {
                    showToast(shouldShow ? 'เปิดแสดงแถบโปรโมชั่นแล้ว' : 'ปิดการแสดงแถบโปรโมชั่นแล้ว');
                    if ('BroadcastChannel' in window) {
                        try {
                            const catChannel = new BroadcastChannel('mala_category_sync_channel');
                            catChannel.postMessage({ action: 'promo_banner_toggled', show: shouldShow });
                        } catch (e) {}
                    }
                } else {
                    showToast(data.message || 'ตั้งค่าไม่สำเร็จ');
                }
            } catch (err) {
                showToast('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            }
        }

        // =========================================================================
        // HERO BANNER SETTINGS (MOBILE COVER BANNER ADMIN MODAL)
        // =========================================================================
        const defaultHeroImageUrl = '{{ asset("assets/online/hero-banner-ref.png") }}';
        let currentHeroBanner = {
            image: @json($heroBannerImage ?? asset('assets/online/hero-banner-ref.png')),
            x: {{ $heroBannerPosition['x'] ?? 50 }},
            y: {{ $heroBannerPosition['y'] ?? 50 }},
            scale: {{ $heroBannerPosition['scale'] ?? 1 }},
            history: @json($heroBannerHistory ?? [asset('assets/online/hero-banner-ref.png')])
        };

        function openHeroBannerModal() {
            const modal = document.getElementById('hero-banner-modal');
            if (!modal) return;
            renderHeroPreview();
            renderHeroHistoryGrid();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeHeroBannerModal() {
            const modal = document.getElementById('hero-banner-modal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function renderHeroPreview() {
            const previewImg = document.getElementById('modal-hero-preview-img');
            const sliderX = document.getElementById('hero-slider-x');
            const sliderY = document.getElementById('hero-slider-y');
            const sliderScale = document.getElementById('hero-slider-scale');
            const valX = document.getElementById('hero-val-x');
            const valY = document.getElementById('hero-val-y');
            const valScale = document.getElementById('hero-val-scale');

            if (previewImg) {
                previewImg.src = currentHeroBanner.image;
                previewImg.style.objectPosition = `${currentHeroBanner.x}% ${currentHeroBanner.y}%`;
                previewImg.style.transform = `scale(${currentHeroBanner.scale})`;
            }

            if (sliderX) sliderX.value = currentHeroBanner.x;
            if (sliderY) sliderY.value = currentHeroBanner.y;
            if (sliderScale) sliderScale.value = currentHeroBanner.scale;

            if (valX) valX.textContent = `${currentHeroBanner.x}%`;
            if (valY) valY.textContent = `${currentHeroBanner.y}%`;
            if (valScale) valScale.textContent = `${currentHeroBanner.scale}x`;
        }

        function renderHeroHistoryGrid() {
            const grid = document.getElementById('hero-history-grid');
            if (!grid) return;

            const list = Array.isArray(currentHeroBanner.history) ? currentHeroBanner.history : [];
            if (list.length === 0) {
                grid.innerHTML = '<div class="col-span-4 text-center text-xs text-slate-400 py-3">ยังไม่มีประวัติภาพปก</div>';
                return;
            }

            grid.innerHTML = list.map(url => {
                const isSelected = (url === currentHeroBanner.image);
                const isDefault = url.includes('hero-banner-ref.png');
                const safeUrl = String(url).replace(/'/g, "\\'");

                return `
                    <div class="relative group rounded-xl overflow-visible aspect-[16/8] border-2 ${isSelected ? 'border-[#E50914] ring-2 ring-rose-200' : 'border-slate-200 hover:border-slate-300'} bg-slate-900 cursor-pointer transition select-none" onclick="selectHeroHistoryImage('${safeUrl}')">
                        <img src="${url}" alt="ภาพปกประวัติ" class="w-full h-full object-cover rounded-[10px]">
                        ${isSelected ? '<span class="absolute bottom-1 left-1 px-1 py-0.2 rounded bg-[#E50914] text-white text-[8px] font-bold">เลือกอยู่</span>' : ''}
                        ${isDefault ? '' : `
                            <button type="button"
                                    onclick="deleteHeroHistoryImage(event, '${safeUrl}')"
                                    class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center text-[10px] font-bold shadow-md cursor-pointer z-20 active:scale-90 transition-transform"
                                    title="ลบภาพนี้">
                                ✕
                            </button>
                        `}
                    </div>
                `;
            }).join('');
        }

        function selectHeroHistoryImage(url) {
            currentHeroBanner.image = url;
            renderHeroPreview();
            renderHeroHistoryGrid();
        }

        function nudgeHeroPos(dx, dy) {
            currentHeroBanner.x = Math.max(0, Math.min(100, Math.round(currentHeroBanner.x + dx)));
            currentHeroBanner.y = Math.max(0, Math.min(100, Math.round(currentHeroBanner.y + dy)));
            renderHeroPreview();
        }

        function nudgeHeroZoom(dScale) {
            currentHeroBanner.scale = Math.max(1, Math.min(2.5, +(currentHeroBanner.scale + dScale).toFixed(2)));
            renderHeroPreview();
        }

        function updateHeroSliderPos() {
            const sliderX = document.getElementById('hero-slider-x');
            const sliderY = document.getElementById('hero-slider-y');
            const sliderScale = document.getElementById('hero-slider-scale');

            if (sliderX) currentHeroBanner.x = Number(sliderX.value);
            if (sliderY) currentHeroBanner.y = Number(sliderY.value);
            if (sliderScale) currentHeroBanner.scale = Number(sliderScale.value);

            renderHeroPreview();
        }

        function resetHeroBannerPosition() {
            currentHeroBanner.x = 50;
            currentHeroBanner.y = 50;
            currentHeroBanner.scale = 1;
            renderHeroPreview();
            showToast('รีเซ็ตตำแหน่งภาพปกเป็นกึ่งกลางแล้ว');
        }

        function restoreDefaultHeroImage() {
            currentHeroBanner.image = defaultHeroImageUrl;
            currentHeroBanner.x = 50;
            currentHeroBanner.y = 50;
            currentHeroBanner.scale = 1;
            renderHeroPreview();
            renderHeroHistoryGrid();
            showToast('คืนค่าภาพปกเริ่มต้นของร้านแล้ว');
        }

        async function handleHeroBannerUpload(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) return;

            const label = document.getElementById('hero-upload-label');
            if (label) label.textContent = '⏳ กำลังประมวลผลและบีบอัดภาพความละเอียดสูง...';

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = async function() {
                    const canvas = document.getElementById('hero-compress-canvas');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');

                    // Maximum dimension 1920 to keep crystal clear sharpness while avoiding upload timeouts
                    const maxDim = 1920;
                    let width = img.width;
                    let height = img.height;

                    if (width > maxDim || height > maxDim) {
                        if (width > height) {
                            height = Math.round((height * maxDim) / width);
                            width = maxDim;
                        } else {
                            width = Math.round((width * maxDim) / height);
                            height = maxDim;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    // High quality JPEG (0.92)
                    const base64Data = canvas.toDataURL('image/jpeg', 0.92);

                    try {
                        const res = await fetch('{{ route("online.hero_banner.upload") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ image_base64: base64Data })
                        });
                        const data = await res.json();
                        if (data && data.success && data.url) {
                            currentHeroBanner.image = data.url;
                            if (Array.isArray(data.history)) {
                                currentHeroBanner.history = data.history;
                            }
                            renderHeroPreview();
                            renderHeroHistoryGrid();
                            showToast('อัปโหลดและแปลงภาพเป็น URL สำเร็จ 📷');
                        } else {
                            showToast(data.message || 'อัปโหลดล้มเหลว');
                        }
                    } catch (err) {
                        showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                    } finally {
                        if (label) label.textContent = '📷 เลือกรูปภาพใหม่จากเครื่อง (บีบอัดความละเอียดสูงอัตโนมัติ)';
                        event.target.value = '';
                    }
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        async function deleteHeroHistoryImage(event, url) {
            if (event) event.stopPropagation();
            if (!confirm('ต้องการลบภาพปกนี้ออกจากประวัติหรือไม่?')) return;

            try {
                const res = await fetch('{{ route("online.hero_banner.delete_history") }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ image_url: url })
                });
                const data = await res.json();
                if (data && data.success) {
                    if (Array.isArray(data.history)) {
                        currentHeroBanner.history = data.history;
                    }
                    if (data.active_image) {
                        currentHeroBanner.image = data.active_image;
                    }
                    renderHeroPreview();
                    renderHeroHistoryGrid();
                    showToast('ลบภาพปกออกจากประวัติแล้ว');
                } else {
                    showToast(data.message || 'ไม่สามารถลบภาพได้');
                }
            } catch (err) {
                showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            }
        }

        async function saveHeroBannerSettings() {
            const btn = document.getElementById('btn-save-hero-banner');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span>⏳ กำลังบันทึก...</span>';
            }

            try {
                const res = await fetch('{{ route("online.hero_banner.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        image_url: currentHeroBanner.image,
                        pos_x: currentHeroBanner.x,
                        pos_y: currentHeroBanner.y,
                        scale: currentHeroBanner.scale
                    })
                });
                const data = await res.json();
                if (data && data.success) {
                    // Update main page hero banner live
                    const bannerImg = document.getElementById('online-hero-banner-img');
                    if (bannerImg) {
                        bannerImg.src = currentHeroBanner.image;
                        bannerImg.style.objectPosition = `${currentHeroBanner.x}% ${currentHeroBanner.y}%`;
                        bannerImg.style.transform = `scale(${currentHeroBanner.scale})`;
                    }
                    if (Array.isArray(data.history)) {
                        currentHeroBanner.history = data.history;
                    }
                    closeHeroBannerModal();
                    showToast('บันทึกภาพปกหน้าร้านเรียบร้อยแล้ว 🔥');

                    if ('BroadcastChannel' in window) {
                        try {
                            const catChannel = new BroadcastChannel('mala_category_sync_channel');
                            catChannel.postMessage({ action: 'hero_banner_updated', hero: currentHeroBanner });
                        } catch (e) {}
                    }
                } else {
                    showToast(data.message || 'บันทึกภาพปล้มเหลว');
                }
            } catch (err) {
                showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<span>💾 บันทึกภาพปกนี้</span>';
                }
            }
        }

        // =========================================================================
        // PROFILE LOGO SETTINGS (MOBILE LOGO BADGE ADMIN MODAL)
        // =========================================================================
        const defaultProfileLogoUrl = '{{ asset("assets/brand/logo-card.png") }}';
        let currentProfileLogo = {
            image: @json($profileLogoImage ?? asset('assets/brand/logo-card.png')),
            x: {{ $profileLogoPosition['x'] ?? 50 }},
            y: {{ $profileLogoPosition['y'] ?? 50 }},
            scale: {{ $profileLogoPosition['scale'] ?? 1 }},
            history: @json($profileLogoHistory ?? [asset('assets/brand/logo-card.png')])
        };

        function openProfileLogoModal() {
            const modal = document.getElementById('profile-logo-modal');
            if (!modal) return;
            renderProfileLogoPreview();
            renderProfileLogoHistoryGrid();
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeProfileLogoModal() {
            const modal = document.getElementById('profile-logo-modal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function renderProfileLogoPreview() {
            const previewImg = document.getElementById('modal-logo-preview-img');
            const sliderX = document.getElementById('logo-slider-x');
            const sliderY = document.getElementById('logo-slider-y');
            const sliderScale = document.getElementById('logo-slider-scale');
            const valX = document.getElementById('logo-val-x');
            const valY = document.getElementById('logo-val-y');
            const valScale = document.getElementById('logo-val-scale');

            if (previewImg) {
                previewImg.src = currentProfileLogo.image;
                previewImg.style.objectPosition = `${currentProfileLogo.x}% ${currentProfileLogo.y}%`;
                previewImg.style.transform = `scale(${currentProfileLogo.scale})`;
            }

            if (sliderX) sliderX.value = currentProfileLogo.x;
            if (sliderY) sliderY.value = currentProfileLogo.y;
            if (sliderScale) sliderScale.value = currentProfileLogo.scale;

            if (valX) valX.textContent = `${currentProfileLogo.x}%`;
            if (valY) valY.textContent = `${currentProfileLogo.y}%`;
            if (valScale) valScale.textContent = `${currentProfileLogo.scale}x`;
        }

        function renderProfileLogoHistoryGrid() {
            const grid = document.getElementById('logo-history-grid');
            if (!grid) return;

            const list = Array.isArray(currentProfileLogo.history) ? currentProfileLogo.history : [];
            if (list.length === 0) {
                grid.innerHTML = '<div class="col-span-4 text-center text-xs text-slate-400 py-3">ยังไม่มีประวัติรูปโปรไฟล์</div>';
                return;
            }

            grid.innerHTML = list.map(url => {
                const isSelected = (url === currentProfileLogo.image);
                const isDefault = url.includes('logo-card.png');
                const safeUrl = String(url).replace(/'/g, "\\'");

                return `
                    <div class="relative group rounded-xl overflow-visible aspect-square border-2 ${isSelected ? 'border-[#900000] ring-2 ring-rose-200' : 'border-slate-200 hover:border-slate-300'} bg-[#900000] cursor-pointer transition select-none flex items-center justify-center p-0.5" onclick="selectProfileLogoHistoryImage('${safeUrl}')">
                        <img src="${url}" alt="ภาพโปรไฟล์ประวัติ" class="w-full h-full object-cover rounded-[10px]">
                        ${isSelected ? '<span class="absolute bottom-1 left-1 px-1 py-0.2 rounded bg-[#900000] text-white text-[8px] font-bold">เลือกอยู่</span>' : ''}
                        ${isDefault ? '' : `
                            <button type="button"
                                    onclick="deleteProfileLogoHistoryImage(event, '${safeUrl}')"
                                    class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center text-[10px] font-bold shadow-md cursor-pointer z-20 active:scale-90 transition-transform"
                                    title="ลบภาพนี้">
                                ✕
                            </button>
                        `}
                    </div>
                `;
            }).join('');
        }

        function selectProfileLogoHistoryImage(url) {
            currentProfileLogo.image = url;
            renderProfileLogoPreview();
            renderProfileLogoHistoryGrid();
        }

        function nudgeLogoPos(dx, dy) {
            currentProfileLogo.x = Math.max(0, Math.min(100, Math.round(currentProfileLogo.x + dx)));
            currentProfileLogo.y = Math.max(0, Math.min(100, Math.round(currentProfileLogo.y + dy)));
            renderProfileLogoPreview();
        }

        function nudgeLogoZoom(dScale) {
            currentProfileLogo.scale = Math.max(1, Math.min(2.5, +(currentProfileLogo.scale + dScale).toFixed(2)));
            renderProfileLogoPreview();
        }

        function updateLogoSliderPos() {
            const sliderX = document.getElementById('logo-slider-x');
            const sliderY = document.getElementById('logo-slider-y');
            const sliderScale = document.getElementById('logo-slider-scale');

            if (sliderX) currentProfileLogo.x = Number(sliderX.value);
            if (sliderY) currentProfileLogo.y = Number(sliderY.value);
            if (sliderScale) currentProfileLogo.scale = Number(sliderScale.value);

            renderProfileLogoPreview();
        }

        function resetProfileLogoPosition() {
            currentProfileLogo.x = 50;
            currentProfileLogo.y = 50;
            currentProfileLogo.scale = 1;
            renderProfileLogoPreview();
            showToast('รีเซ็ตตำแหน่งรูปโปรไฟล์เป็นกึ่งกลางแล้ว');
        }

        function restoreDefaultLogoImage() {
            currentProfileLogo.image = defaultProfileLogoUrl;
            currentProfileLogo.x = 50;
            currentProfileLogo.y = 50;
            currentProfileLogo.scale = 1;
            renderProfileLogoPreview();
            renderProfileLogoHistoryGrid();
            showToast('คืนค่าโลโก้เริ่มต้นของร้านแล้ว');
        }

        async function handleProfileLogoUpload(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) return;

            const label = document.getElementById('logo-upload-label');
            if (label) label.textContent = '⏳ กำลังประมวลผลและบีบอัดภาพความละเอียดสูง...';

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = async function() {
                    const canvas = document.getElementById('logo-compress-canvas');
                    if (!canvas) return;
                    const ctx = canvas.getContext('2d');

                    const maxDim = 1024;
                    let width = img.width;
                    let height = img.height;

                    if (width > maxDim || height > maxDim) {
                        if (width > height) {
                            height = Math.round((height * maxDim) / width);
                            width = maxDim;
                        } else {
                            width = Math.round((width * maxDim) / height);
                            height = maxDim;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    const base64Data = canvas.toDataURL('image/png', 0.95);

                    try {
                        const res = await fetch('{{ route("online.profile_logo.upload") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ image_base64: base64Data })
                        });
                        const data = await res.json();
                        if (data && data.success && data.url) {
                            currentProfileLogo.image = data.url;
                            if (Array.isArray(data.history)) {
                                currentProfileLogo.history = data.history;
                            }
                            renderProfileLogoPreview();
                            renderProfileLogoHistoryGrid();
                            showToast('อัปโหลดและแปลงภาพโปรไฟล์เป็น URL สำเร็จ 📷');
                        } else {
                            showToast(data.message || 'อัปโหลดล้มเหลว');
                        }
                    } catch (err) {
                        showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                    } finally {
                        if (label) label.textContent = '📷 เลือกรูปภาพใหม่จากเครื่อง (บีบอัดความละเอียดสูงอัตโนมัติ)';
                        event.target.value = '';
                    }
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        async function deleteProfileLogoHistoryImage(event, url) {
            if (event) event.stopPropagation();
            if (!confirm('ต้องการลบรูปโปรไฟล์นี้ออกจากประวัติหรือไม่?')) return;

            try {
                const res = await fetch('{{ route("online.profile_logo.delete_history") }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ image_url: url })
                });
                const data = await res.json();
                if (data && data.success) {
                    if (Array.isArray(data.history)) {
                        currentProfileLogo.history = data.history;
                    }
                    if (data.active_image) {
                        currentProfileLogo.image = data.active_image;
                    }
                    renderProfileLogoPreview();
                    renderProfileLogoHistoryGrid();
                    showToast('ลบรูปโปรไฟล์ออกจากประวัติแล้ว');
                } else {
                    showToast(data.message || 'ไม่สามารถลบภาพได้');
                }
            } catch (err) {
                showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            }
        }

        async function saveProfileLogoSettings() {
            const btn = document.getElementById('btn-save-profile-logo');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span>⏳ กำลังบันทึก...</span>';
            }

            try {
                const res = await fetch('{{ route("online.profile_logo.update") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        image_url: currentProfileLogo.image,
                        pos_x: currentProfileLogo.x,
                        pos_y: currentProfileLogo.y,
                        scale: currentProfileLogo.scale
                    })
                });
                const data = await res.json();
                if (data && data.success) {
                    const logoImg = document.getElementById('online-profile-logo-img');
                    if (logoImg) {
                        logoImg.src = currentProfileLogo.image;
                        logoImg.style.objectPosition = `${currentProfileLogo.x}% ${currentProfileLogo.y}%`;
                        logoImg.style.transform = `scale(${currentProfileLogo.scale})`;
                    }
                    if (Array.isArray(data.history)) {
                        currentProfileLogo.history = data.history;
                    }
                    closeProfileLogoModal();
                    showToast('บันทึกรูปโปรไฟล์ร้านเรียบร้อยแล้ว 🔥');

                    if ('BroadcastChannel' in window) {
                        try {
                            const catChannel = new BroadcastChannel('mala_category_sync_channel');
                            catChannel.postMessage({ action: 'profile_logo_updated', logo: currentProfileLogo });
                        } catch (e) {}
                    }
                } else {
                    showToast(data.message || 'บันทึกภาพล้มเหลว');
                }
            } catch (err) {
                showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<span>💾 บันทึกรูปโปรไฟล์นี้</span>';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            if ('BroadcastChannel' in window) {
                try {
                    const catChannel = new BroadcastChannel('mala_category_sync_channel');
                    catChannel.onmessage = (ev) => applyOnlineCategorySyncMessage(ev.data);
                    const invChannel = new BroadcastChannel('mala_inventory_sync_channel');
                    invChannel.onmessage = () => refreshOnlineCategoriesLive();
                } catch (e) {}
            }
            setInterval(() => {
                if (!document.hidden) refreshOnlineCategoriesLive();
            }, 20000);
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') refreshOnlineCategoriesLive();
            });
        });
    </script>
</body>
</html>
