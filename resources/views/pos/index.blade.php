@extends('layouts.app', ['title' => 'แคชเชียร์หน้าร้าน & สั่งอาหารออนไลน์ - หม่าล่าสตรีท POS'])

@php
    $allProducts = $categories->flatMap(fn($c) => $c->products)->sortBy('sort_order')->values();
    $totalProductsCount = $allProducts->count();

    $categoryImageMap = [
        'beef' => 'assets/categories/beef.png',
        'pork-chicken' => 'assets/categories/pork-chicken.png',
        'meatball-sausage' => 'assets/categories/meatballs.png',
        'vegetables' => 'assets/categories/vegetables.png',
        'tofu-noodles' => 'assets/categories/tofu.png',
        'snacks' => 'assets/categories/snacks.png',
        'beverages' => 'assets/categories/drinks.png',
    ];

    $badgeMetaMap = [
        'เนื้อโคขุนพรีเมียม' => ['label' => 'ขายดี', 'icon' => '👑', 'bg' => 'from-[#B91C1C] to-[#E50914]', 'rank' => 1],
        'หมูสามชั้นสไลซ์' => ['label' => 'แนะนำ', 'icon' => '👍', 'bg' => 'from-[#C2410C] to-[#EA580C]', 'rank' => 2],
        'ลูกชิ้นรวม' => ['label' => 'ยอดนิยม', 'icon' => '🔥', 'bg' => 'from-[#B91C1C] to-[#DC2626]', 'rank' => 3],
        'เต้าหู้หม่าล่า' => ['label' => 'ใหม่', 'icon' => '★', 'bg' => 'from-[#B91C1C] to-[#E50914]', 'rank' => 4],
        'พันผักรวม' => ['label' => 'ต้องลอง', 'icon' => '♥', 'bg' => 'from-[#991B1B] to-[#DC2626]', 'rank' => 5],
        'เห็ดเข็มทองพันเบคอน' => ['label' => 'แซ่บ', 'icon' => '🌶️', 'bg' => 'from-[#B91C1C] to-[#EA580C]', 'rank' => 6],
    ];

    // Pre-build initial cart matching Image 1 so the screen renders 100% identical to Image 1 on load
    $itemBeef = $allProducts->firstWhere('name', 'เนื้อโคขุนพรีเมียม') ?? $allProducts->first();
    $itemPorkEnoki = $allProducts->firstWhere('name', 'หมูสามชั้นสไลซ์พันเห็ดเข็มทอง') ?? $allProducts->skip(1)->first();
    $itemBalls = $allProducts->firstWhere('name', 'ลูกชิ้นรวม') ?? $allProducts->skip(2)->first();

    $initialCartSeed = [];
    if ($itemBeef) {
        $initialCartSeed[] = [
            'product_id' => $itemBeef->id,
            'name' => $itemBeef->display_name ?: $itemBeef->name,
            'unit_price' => (float) $itemBeef->price_per_stick,
            'quantity' => 2,
            'image' => $itemBeef->display_image ?: asset('assets/menu/beef-premium.png'),
            'cooking_type' => 'grill',
            'grill_style' => 'brush',
            'soup_style' => null,
            'spiciness_level' => 'medium',
            'special_instructions' => '',
        ];
    }
    if ($itemPorkEnoki) {
        $initialCartSeed[] = [
            'product_id' => $itemPorkEnoki->id,
            'name' => $itemPorkEnoki->display_name ?: $itemPorkEnoki->name,
            'unit_price' => (float) $itemPorkEnoki->price_per_stick,
            'quantity' => 1,
            'image' => $itemPorkEnoki->display_image ?: asset('assets/menu/pork-raw.png'),
            'cooking_type' => 'grill',
            'grill_style' => 'brush',
            'soup_style' => null,
            'spiciness_level' => 'medium',
            'special_instructions' => '',
        ];
    }
    if ($itemBalls) {
        $initialCartSeed[] = [
            'product_id' => $itemBalls->id,
            'name' => $itemBalls->display_name ?: $itemBalls->name,
            'unit_price' => (float) $itemBalls->price_per_stick,
            'quantity' => 2,
            'image' => $itemBalls->display_image ?: asset('assets/menu/meatball-assorted.png'),
            'cooking_type' => 'grill',
            'grill_style' => 'brush',
            'soup_style' => null,
            'spiciness_level' => 'medium',
            'special_instructions' => '',
        ];
    }
@endphp

@section('content')
<style>
    #sticky-floating-bar {
        position: -webkit-sticky;
        position: sticky;
        top: var(--app-header-h, 80px);
    }
    @media (max-width: 1023px) {
        #sticky-floating-bar {
            top: var(--app-header-h, 122px);
        }
    }
</style>
<div class="flex-1 bg-[#F5F5F7] py-4 px-3 sm:px-5 lg:px-6">
    @php
        $isOnlineLive = ($onlineOrderStatus ?? 'preview') === 'live';
        $posHeroImg = $posHeroBannerImage ?? '';
        $posHeroPosX = $posHeroBannerPosition['x'] ?? 50;
        $posHeroPosY = $posHeroBannerPosition['y'] ?? 50;
        $posHeroScale = $posHeroBannerPosition['scale'] ?? 1;
    @endphp

    <div class="max-w-[1420px] mx-auto grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- ========================================================================= -->
        <!-- LEFT COLUMN (8 Cols): Hero Banner, Categories, All Menu Grid           -->
        <!-- ========================================================================= -->
        <div class="lg:col-span-8 xl:col-span-8 space-y-3.5 min-w-0">

            <!-- 1. RESTAURANT HERO & PRICE TRACKER BANNER (ทั้งแถบสีแดงคือขนาดภาพปก + การ์ดลอยอยู่ข้างบน) -->
            <section id="pos-hero-banner-section" class="relative rounded-[22px] overflow-hidden shadow-md border border-[#5A080C] bg-[#3D0307] p-3 sm:p-4">
                <!-- Full-Width Cover Image spanning the ENTIRE red section (1:7 แนวนอน) -->
                <img id="pos-hero-banner-img"
                     src="{{ $posHeroImg }}"
                     alt="ภาพปกหน้าร้าน หม่าล่าสตรีท"
                     class="absolute inset-0 w-full h-full object-cover select-none pointer-events-none transition-all duration-150 {{ $posHeroImg ? '' : 'hidden' }}"
                     style="object-position: {{ $posHeroPosX }}% {{ $posHeroPosY }}%; transform: scale({{ $posHeroScale }});">

                <!-- Floating Controls & Price Tracker Row Over the Full-Width Cover Image -->
                <div class="relative z-10 flex flex-col lg:flex-row items-stretch lg:items-start justify-between gap-3 sm:gap-3.5">
                    <!-- Left Floating Online Switch (70% Compact, Same Structure) + 3-Bar Menu Button (ปุ่มสามขีด) -->
                    <div class="flex-1 flex flex-wrap sm:flex-nowrap items-start justify-between gap-2 pointer-events-auto min-w-0">
                        <!-- FLOATING COMPACT SWITCH: เปิดบริการออนไลน์ -->
                        <div id="pos-online-switch-card"
                             class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-[14px] border shadow-lg backdrop-blur-xs transition-all duration-200 max-w-full {{ $isOnlineLive ? 'bg-emerald-50/95 border-emerald-400 shadow-emerald-950/20' : 'bg-white/95 border-white/90 shadow-black/25' }}">
                            <button type="button"
                                    id="pos-online-switch-btn"
                                    role="switch"
                                    aria-checked="{{ $isOnlineLive ? 'true' : 'false' }}"
                                    onclick="togglePosOnlineServiceSwitch()"
                                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500 {{ $isOnlineLive ? 'bg-emerald-500' : 'bg-slate-300' }}"
                                    title="สลับเปิด/ปิดบริการรับออเดอร์ออนไลน์">
                                <span id="pos-online-switch-knob"
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out flex items-center justify-center {{ $isOnlineLive ? 'translate-x-5' : 'translate-x-0' }}">
                                    <span id="pos-online-switch-dot" class="w-1.5 h-1.5 rounded-full {{ $isOnlineLive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                                </span>
                            </button>

                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[11px] sm:text-[11.5px] font-black tracking-tight text-slate-900 whitespace-nowrap">เปิดบริการออนไลน์</span>
                                    <span id="pos-online-status-badge"
                                          class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-extrabold whitespace-nowrap {{ $isOnlineLive ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700' }}">
                                        <span id="pos-online-badge-dot" class="w-1.5 h-1.5 rounded-full {{ $isOnlineLive ? 'bg-white animate-pulse' : 'bg-slate-500' }}"></span>
                                        <span id="pos-online-badge-text">{{ $isOnlineLive ? 'กำลังเปิดรับออเดอร์ (LIVE)' : 'ปิดชั่วคราว (พรีวิว)' }}</span>
                                    </span>
                                </div>
                                <p id="pos-online-status-sub" class="text-[8.5px] sm:text-[9px] font-medium leading-tight truncate {{ $isOnlineLive ? 'text-emerald-700' : 'text-slate-500' }}">
                                    {{ $isOnlineLive ? 'ลูกค้าสามารถกดสั่งอาหารออนไลน์ได้ทันที' : 'ลูกค้าเข้าดูเมนูได้ แต่ยังไม่เปิดรับออเดอร์จริง' }}
                                </p>
                            </div>
                        </div>

                        <div class="relative shrink-0">
                            <button type="button"
                                    id="btn-pos-master-settings"
                                    onclick="togglePosMasterSettingsMenu(event)"
                                    class="w-9 h-9 rounded-full bg-white/95 hover:bg-white active:scale-95 text-slate-800 shadow-md backdrop-blur-xs flex items-center justify-center transition-all cursor-pointer border border-white/80"
                                    title="ตั้งค่าหน้าร้าน (แอดมิน)"
                                    aria-label="ตั้งค่าหน้าร้าน">
                                <!-- 3-Bar Hamburger Icon (ปุ่มสามขีด) -->
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" viewBox="0 0 24 24">
                                    <line x1="4" y1="6.5" x2="20" y2="6.5" />
                                    <line x1="4" y1="12" x2="20" y2="12" />
                                    <line x1="4" y1="17.5" x2="20" y2="17.5" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu: ตั้งค่าหน้าร้าน (แอดมิน) -->
                            <div id="pos-master-settings-menu"
                                 class="hidden fixed w-72 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200/90 p-2 z-[9999] text-slate-800 select-none">
                                <div class="px-3 py-2 border-b border-slate-100 flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-[#E50914] animate-pulse"></span>
                                        <span>ตั้งค่าหน้าร้าน (แอดมิน)</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-mono">ADMIN</span>
                                </div>

                                <div class="py-1 space-y-1">
                                    <!-- Option 1: แก้ไขภาพปกหน้าร้าน (1:7 แนวนอน) -->
                                    <button type="button"
                                            onclick="closePosMasterSettingsMenu(); openPosHeroBannerModal();"
                                            class="w-full px-3 py-2.5 rounded-xl hover:bg-rose-50/80 active:bg-rose-100/80 flex items-center gap-3 text-left transition cursor-pointer group">
                                        <div class="w-8 h-8 rounded-xl bg-rose-50 border border-rose-200/80 text-[#D90012] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                            <svg class="w-4 h-4 text-[#D90012]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="18" height="18" rx="3" />
                                                <circle cx="8.5" cy="8.5" r="1.8" fill="currentColor" stroke="none" />
                                                <path d="M21 15l-5-5L5 21" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-xs font-bold text-slate-800 group-hover:text-[#E50914]">แก้ไขภาพปกหน้าร้าน</div>
                                            <div class="text-[10.5px] text-slate-500 truncate">ปรับตำแหน่ง ซูม และจัดการภาพปก (1:7)</div>
                                        </div>
                                        <span class="text-slate-400 text-xs">›</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT FLOATING CARD: รูปแบบการปรุงอาหาร (แบบย่าง -> ความเผ็ด / น้ำซุป -> ประเภทซุป) -->
                    <div class="w-full lg:w-[445px] xl:w-[485px] shrink-0">
                        <div id="pos-top-cooking-card" class="relative bg-white/98 rounded-[18px] p-3.5 shadow-lg border border-white space-y-3">
                            <!-- 1. รูปแบบการปรุงอาหาร -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-bold text-slate-800">
                                        รูปแบบการปรุงอาหาร <span class="text-[#D90012]">*</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-normal">เลือกอย่างใดอย่างหนึ่ง</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <!-- Option 1: หม่าล่าย่าง -->
                                    <button type="button" onclick="setPosTopCookingType('grill')" id="pos-top-btn-grill"
                                            class="p-2.5 rounded-2xl border-2 text-left transition-all border-[#D90012] bg-[#FFF0F1] shadow-xs flex items-center gap-2 group cursor-pointer">
                                        <div id="pos-top-radio-grill" class="w-4 h-4 rounded-full border-2 border-blue-600 flex items-center justify-center shrink-0 p-0.5">
                                            <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                                        </div>
                                        <div class="w-7 h-7 rounded-xl bg-white border border-rose-100 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition-transform">
                                            <svg class="w-4.5 h-4.5 text-[#D90012] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M5 19L19 5" stroke-width="2.6" />
                                                <path d="M8 14l2 4" />
                                                <path d="M11 11l2 4" />
                                                <path d="M14 8l2 4" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-extrabold text-[#D90012] text-[12px] leading-tight">หม่าล่าย่าง</div>
                                            <div class="text-[9.5px] text-[#596272] truncate mt-0.5">ทาซอสหม่าล่าต้นตำรับ</div>
                                        </div>
                                    </button>

                                    <!-- Option 2: น้ำซุป -->
                                    <button type="button" onclick="setPosTopCookingType('soup')" id="pos-top-btn-soup"
                                            class="p-2.5 rounded-2xl border-2 text-left transition-all border-slate-200 bg-white flex items-center gap-2 hover:border-slate-300 group cursor-pointer">
                                        <div id="pos-top-radio-soup" class="w-4 h-4 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0"></div>
                                        <div class="w-7 h-7 rounded-xl bg-white border border-rose-100 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition-transform">
                                            <svg class="w-4.5 h-4.5 text-[#D90012] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 11h16a1 1 0 0 1 1 1c0 5-3.5 8-9 8s-9-3-9-8a1 1 0 0 1 1-1z" />
                                                <path d="M2 13h2M20 13h2" />
                                                <path d="M4 11h16" />
                                                <path d="M9 7c.5-1 1.5-1.5 2-1.5s1.5.5 2 1.5" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-extrabold text-[#101114] text-[12px] leading-tight">น้ำซุป</div>
                                            <div class="text-[9.5px] text-[#596272] truncate mt-0.5">เลือกประเภทน้ำซุป</div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- 2.1 ระดับความเผ็ด (เมื่อเลือก หม่าล่าย่าง - ตามภาพที่ 2) -->
                            <div id="pos-top-spiciness-section">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-bold text-slate-800">
                                        ระดับความเผ็ด <span class="text-[#D90012]">*</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-normal">รสชาติหม่าล่า</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <label class="flex flex-col items-center justify-center p-2 rounded-2xl border-2 border-slate-200 bg-white text-center cursor-pointer transition-all has-checked:border-[#D90012] has-checked:bg-[#FFF0F1] has-checked:shadow-xs group">
                                        <input type="radio" name="pos_top_spiciness" value="mild" onchange="posTopSpiciness = this.value" class="w-4 h-4 text-blue-600 accent-blue-600 mb-1 focus:ring-0">
                                        <div class="h-5 flex items-center justify-center mb-0.5 group-hover:scale-110 transition-transform">
                                            <img src="{{ asset('assets/online/spicy-chili-flame.png') }}" class="h-5 w-auto object-contain shrink-0" alt="เผ็ดน้อย">
                                        </div>
                                        <span class="font-extrabold text-[#101114] text-[11px]">เผ็ดน้อย</span>
                                    </label>
                                    <label class="flex flex-col items-center justify-center p-2 rounded-2xl border-2 border-slate-200 bg-white text-center cursor-pointer transition-all has-checked:border-[#D90012] has-checked:bg-[#FFF0F1] has-checked:shadow-xs group">
                                        <input type="radio" name="pos_top_spiciness" value="medium" checked onchange="posTopSpiciness = this.value" class="w-4 h-4 text-blue-600 accent-blue-600 mb-1 focus:ring-0">
                                        <div class="h-5 flex items-center justify-center gap-1 mb-0.5 group-hover:scale-110 transition-transform">
                                            <img src="{{ asset('assets/online/spicy-chili-flame.png') }}" class="h-4.5 w-auto object-contain shrink-0" alt="เผ็ดกลาง">
                                            <img src="{{ asset('assets/online/spicy-chili-flame.png') }}" class="h-4.5 w-auto object-contain shrink-0" alt="เผ็ดกลาง">
                                        </div>
                                        <span class="font-extrabold text-[#101114] text-[11px]">เผ็ดกลาง</span>
                                    </label>
                                    <label class="flex flex-col items-center justify-center p-2 rounded-2xl border-2 border-slate-200 bg-white text-center cursor-pointer transition-all has-checked:border-[#D90012] has-checked:bg-[#FFF0F1] has-checked:shadow-xs group">
                                        <input type="radio" name="pos_top_spiciness" value="hot" onchange="posTopSpiciness = this.value" class="w-4 h-4 text-blue-600 accent-blue-600 mb-1 focus:ring-0">
                                        <div class="h-5 flex items-center justify-center gap-0.5 mb-0.5 group-hover:scale-110 transition-transform">
                                            <img src="{{ asset('assets/online/spicy-chili-flame.png') }}" class="h-4 w-auto object-contain shrink-0" alt="เผ็ดมาก">
                                            <img src="{{ asset('assets/online/spicy-chili-flame.png') }}" class="h-4 w-auto object-contain shrink-0" alt="เผ็ดมาก">
                                            <img src="{{ asset('assets/online/spicy-chili-flame.png') }}" class="h-4 w-auto object-contain shrink-0" alt="เผ็ดมาก">
                                        </div>
                                        <span class="font-extrabold text-[#101114] text-[11px]">เผ็ดมาก</span>
                                    </label>
                                </div>
                            </div>

                            <!-- 2.2 เลือกประเภทน้ำซุป (เมื่อเลือก น้ำซุป - ตามภาพที่ 3) -->
                            <div id="pos-top-soup-section" class="hidden">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-xs font-bold text-slate-800">เลือกประเภทน้ำซุป <span class="text-[#D90012]">*</span></span>
                                    <span class="text-[10px] text-slate-400 font-normal">รสชาติน้ำซุป</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <label class="flex flex-col items-center justify-center p-2 rounded-2xl border-2 border-slate-200 bg-white text-center cursor-pointer transition-all has-checked:border-[#D90012] has-checked:bg-[#FFF0F1] has-checked:shadow-xs group">
                                        <input type="radio" name="pos_top_soup_style" value="black_soup" onchange="posTopSoupStyle = this.value" class="w-4 h-4 text-blue-600 accent-blue-600 mb-1 focus:ring-0">
                                        <div class="h-5 flex items-center justify-center mb-0.5 group-hover:scale-110 transition-transform">
                                            <svg class="w-5 h-5 text-[#D90012] shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M21 11H3a1 1 0 0 0-1 1c0 5 3.5 9 8.5 9.8V23h3v-1.2c5-.8 8.5-4.8 8.5-9.8a1 1 0 0 0-1-1zm-9 8.8C7.5 19.8 4.5 16.5 4.1 13h15.8c-.4 3.5-3.4 6.8-7.9 6.8zM6.5 8a1 1 0 0 1 1-1c1 0 1-1 1-1s0-1 1-1a1 1 0 0 1 2 0c0 1 1 2 2 2s1-1 1-1 0-1 1-1a1 1 0 0 1 2 0c0 1 1 2 2 2a1 1 0 1 1 0 2c-1.5 0-2.5-1-2.9-1.6-.4.6-1.4 1.6-2.9 1.6s-2.5-1-2.9-1.6c-.4.6-1.4 1.6-2.9 1.6a1 1 0 0 1-1-1z" />
                                            </svg>
                                        </div>
                                        <span class="font-extrabold text-[#101114] text-[10.5px] leading-tight">ซุปน้ำดำ</span>
                                    </label>
                                    <label class="flex flex-col items-center justify-center p-2 rounded-2xl border-2 border-slate-200 bg-white text-center cursor-pointer transition-all has-checked:border-[#D90012] has-checked:bg-[#FFF0F1] has-checked:shadow-xs group">
                                        <input type="radio" name="pos_top_soup_style" value="tomyum_rich" checked onchange="posTopSoupStyle = this.value" class="w-4 h-4 text-blue-600 accent-blue-600 mb-1 focus:ring-0">
                                        <div class="h-5 flex items-center justify-center mb-0.5 group-hover:scale-110 transition-transform">
                                            <svg class="w-5 h-5 text-[#D90012] shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 2c-1.5 2.5-4 4.5-4 7a4 4 0 0 0 8 0c0-2.5-2.5-4.5-4-7zm0 9c-1.1 0-2-.9-2-2 0-1 .9-2 2-3.3 1.1 1.3 2 2.3 2 3.3 0 1.1-.9 2-2 2zM2 13h20c0 5-4.5 9-10 9S2 18 2 13z"/>
                                            </svg>
                                        </div>
                                        <span class="font-extrabold text-[#101114] text-[10.5px] leading-tight">ซุปต้มยำ-น้ำข้น</span>
                                    </label>
                                    <label class="flex flex-col items-center justify-center p-2 rounded-2xl border-2 border-slate-200 bg-white text-center cursor-pointer transition-all has-checked:border-[#D90012] has-checked:bg-[#FFF0F1] has-checked:shadow-xs group">
                                        <input type="radio" name="pos_top_soup_style" value="mala_rich" onchange="posTopSoupStyle = this.value" class="w-4 h-4 text-blue-600 accent-blue-600 mb-1 focus:ring-0">
                                        <div class="h-5 flex items-center justify-center mb-0.5 group-hover:scale-110 transition-transform">
                                            <svg class="w-5 h-5 text-[#D90012] shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 1a3 3 0 0 0-3 3c0 2 2 3 3 5 1-2 3-3 3-5a3 3 0 0 0-3-3zm-6 4a2 2 0 0 0-2 2c0 1.5 1.5 2.5 2 4 .5-1.5 2-2.5 2-4a2 2 0 0 0-2-2zm12 0a2 2 0 0 0-2 2c0 1.5 1.5 2.5 2 4 .5-1.5 2-2.5 2-4a2 2 0 0 0-2-2zM2 13h20c0 5-4.5 9-10 9S2 18 2 13z"/>
                                            </svg>
                                        </div>
                                        <span class="font-extrabold text-[#101114] text-[10.5px] leading-tight">ซุปหม่าล่า-น้ำข้น</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ========================================================================= -->
            <!-- STICKY FLOATING BAR (Categories + Menu Header)                            -->
            <!-- ========================================================================= -->
            <div id="sticky-floating-bar"
                 class="sticky z-30 bg-[#F5F5F7] -mx-1 px-1 sm:-mx-2 sm:px-2 pt-1.5 pb-2 space-y-2 transition-all duration-150"
                 style="top: var(--app-header-h, 80px);">

                <!-- FOOD CATEGORY NAVIGATION (Horizontal Category Pills matching Price Tracker Image 2) -->
                <section class="py-1">
                    <div class="flex items-center gap-2.5 overflow-x-auto scrollbar-none pb-0.5" id="category-pills-bar">
                        <!-- Item 1: ทั้งหมด (Active Pill matching Image 2) -->
                        <button type="button" onclick="filterCategory('all', this)"
                                data-cat="all"
                                class="cat-pill active shrink-0 h-[44px] px-4 rounded-[14px] bg-[#E50914] text-white border border-[#E50914] shadow-sm flex items-center gap-2.5 transition-all cursor-pointer">
                            <span class="cat-icon-box w-6 h-6 rounded-lg bg-white/15 flex items-center justify-center">
                                <svg class="cat-icon-svg w-4 h-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                                    <circle cx="5.5" cy="5.5" r="2.3"></circle>
                                    <circle cx="14.5" cy="5.5" r="2.3"></circle>
                                    <circle cx="5.5" cy="14.5" r="2.3"></circle>
                                    <circle cx="14.5" cy="14.5" r="2.3"></circle>
                                </svg>
                            </span>
                            <span class="cat-label text-[13.5px] font-semibold tracking-tight whitespace-nowrap">ทั้งหมด</span>
                        </button>

                        <!-- Items 2–8: Category Pills (matching Image 2) -->
                        @foreach($categories as $category)
                            @php
                                $catImg = !empty($category->icon) && !in_array($category->icon, ['🥩', '🍗', '🍢', '🥬', '🍥', '🥤', '🍟'])
                                    ? (str_starts_with($category->icon, 'http') ? $category->icon : asset($category->icon))
                                    : asset($categoryImageMap[$category->slug] ?? 'assets/categories/all.png');
                            @endphp
                            <button type="button" onclick="filterCategory('{{ $category->slug }}', this)"
                                    data-cat="{{ $category->slug }}"
                                    class="cat-pill shrink-0 h-[44px] px-4 rounded-[14px] bg-white hover:bg-rose-50/50 text-[#1F2430] border border-slate-200/90 shadow-2xs flex items-center gap-2.5 transition-all cursor-pointer">
                                <img src="{{ $catImg }}"
                                     alt="{{ $category->name }}"
                                     class="w-7 h-7 rounded-full object-cover shrink-0 border border-slate-100"
                                     onerror="this.onerror=null;this.src='{{ asset('assets/menu/food-fallback.svg') }}';">
                                <span class="cat-label text-[13.5px] font-semibold tracking-tight whitespace-nowrap">{{ $category->name }}</span>
                            </button>
                        @endforeach
                    </div>
                </section>

                <!-- 4. ALL MENU SECTION HEADER + SEARCH + 3/4 GRID TOGGLE + SORT DROPDOWN -->
                <section class="flex flex-wrap items-center justify-between gap-3 pt-0.5">
                    <div class="flex items-center gap-2.5">
                        <span class="w-1.5 h-5 rounded-full bg-[#E50914] block"></span>
                        <h2 class="text-[19px] mala-font-bold text-slate-900 tracking-tight">เมนูทั้งหมด</h2>
                        <span class="text-[14px] mala-font-thin text-slate-500">(<span id="visible-menu-count">{{ $totalProductsCount }}</span> รายการ)</span>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end flex-wrap">
                        <!-- Quick Search Input -->
                        <div class="relative flex-1 sm:w-48 sm:flex-initial">
                            <input type="text" id="search-input" placeholder="ค้นหาเมนูหม่าล่า..."
                                   class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200/90 rounded-full text-[12.5px] mala-font-thin text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-[#E50914] shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <!-- 3 / 4 Items per Row Toggle (อยู่ข้างขวาของแถบค้นหา ตามภาพที่ 3) -->
                        <div class="flex items-center bg-white border border-slate-200/90 rounded-full p-0.5 shadow-2xs shrink-0" title="เลือกการแสดงผลจำนวนรายการต่อแถว">
                            <!-- 1. สี่เหลี่ยม 3 จุด (3 รายการต่อแถว) -->
                            <button type="button" onclick="setGridColumns(3)" id="btn-grid-3"
                                    class="grid-toggle-btn active px-2.5 py-1 rounded-full text-xs mala-font-bold flex items-center gap-1.5 transition-all bg-[#E50914] text-white shadow-2xs cursor-pointer"
                                    title="แสดง 3 รายการต่อแถว">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                    <rect x="2" y="7" width="4" height="6" rx="1"/>
                                    <rect x="8" y="7" width="4" height="6" rx="1"/>
                                    <rect x="14" y="7" width="4" height="6" rx="1"/>
                                </svg>
                                <span class="text-[11px] leading-none">3</span>
                            </button>

                            <!-- 2. สี่เหลี่ยม 4 จุด (4 รายการต่อแถว) -->
                            <button type="button" onclick="setGridColumns(4)" id="btn-grid-4"
                                    class="grid-toggle-btn px-2.5 py-1 rounded-full text-xs mala-font-bold flex items-center gap-1.5 transition-all text-slate-500 hover:text-slate-800 hover:bg-slate-100 cursor-pointer"
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

                        <!-- Sort Dropdown -->
                        <div class="relative shrink-0">
                            <select id="menu-sort-select" onchange="sortProductsGrid(this.value)"
                                    class="appearance-none bg-white border border-slate-200/90 hover:border-slate-300 rounded-full pl-3.5 pr-8 py-1.5 text-[12.5px] mala-font-mid-bold text-slate-700 shadow-2xs focus:outline-none focus:border-[#E50914] cursor-pointer">
                                <option value="recommended">เรียงตาม : แนะนำ</option>
                                <option value="popular">เรียงตาม : ขายดี</option>
                                <option value="price_asc">เรียงตาม : ราคาต่ำ-สูง</option>
                                <option value="price_desc">เรียงตาม : ราคาสูง-ต่ำ</option>
                            </select>
                            <svg class="w-3.5 h-3.5 text-slate-500 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                </section>
            </div>

            <!-- 5. 3-COLUMN 16:9 MENU PRODUCT GRID (Matches Image 1 100%) -->
            <section>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 transition-all duration-200" id="products-grid">
                    @foreach($allProducts as $index => $product)
                        @php
                            $catSlug = $product->category->slug ?? 'all';
                            $displayName = $product->display_name ?: $product->name;
                            $badge = $badgeMetaMap[$displayName] ?? ($badgeMetaMap[$product->name] ?? null);
                            $displayImg = $product->display_image ?: asset($product->image_url ?: 'assets/menu/beef-premium.png');
                        @endphp
                        <div class="product-card bg-white rounded-[18px] overflow-hidden border border-slate-200/85 shadow-xs hover:shadow-md hover:border-[#E50914]/50 active:scale-[0.98] transition-all duration-150 flex flex-col justify-between cursor-pointer select-none touch-manipulation group"
                             data-category="{{ $catSlug }}"
                             data-id="{{ $product->id }}"
                             data-raw-id="{{ $product->raw_material_id }}"
                             data-name="{{ $displayName }}"
                             data-price="{{ $product->price_per_stick }}"
                             data-stock="{{ $product->stock_sticks }}"
                             data-image="{{ $displayImg }}"
                             data-sort="{{ $product->sort_order }}"
                             data-popular="{{ $badge['rank'] ?? ($index + 10) }}"
                             data-grill="{{ $product->supports_grill ? '1' : '0' }}"
                             data-soup="{{ $product->supports_soup ? '1' : '0' }}"
                             onclick="quickAddFromCard(this)">

                            <!-- Top 16:9 Food Photo on Black Plate -->
                            <div class="relative aspect-[16/9] w-full bg-[#140D0B] overflow-hidden">
                                <img src="{{ $displayImg }}"
                                     alt="{{ $displayName }}"
                                     loading="lazy"
                                     data-role="product-img"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                     onerror="this.onerror=null;this.src='{{ asset('assets/menu/food-fallback.svg') }}';">

                                <!-- Top-Left Badge (ขายดี / แนะนำ / ยอดนิยม / ใหม่ / ต้องลอง / แซ่บ) -->
                                @if($badge)
                                    <div class="absolute top-2.5 left-2.5 z-10 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-gradient-to-r {{ $badge['bg'] }} text-white text-[11px] mala-font-bold shadow-sm">
                                        <span>{{ $badge['icon'] }}</span>
                                        <span>{{ $badge['label'] }}</span>
                                    </div>
                                @else
                                    <div class="absolute top-2.5 left-2.5 z-10 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-black/70 backdrop-blur-xs text-white text-[10.5px] mala-font-mid-bold">
                                        <span>🔥</span>
                                        <span data-role="top-stock-text">{{ $product->stock_sticks > 0 ? 'เหลือ ' . $product->stock_sticks . ' ไม้' : 'หมดชั่วคราว' }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Card Content: Title, 1-line Description, Price & Red + Button -->
                            <div class="p-3.5 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-[15.5px] mala-font-bold text-slate-900 group-hover:text-[#E50914] transition-colors leading-snug line-clamp-1" data-role="product-name">
                                        {{ $displayName }}
                                    </h3>
                                    <p class="text-[12px] mala-font-thin text-slate-500 mt-0.5 line-clamp-1">
                                        {{ $product->description ?: 'ย่างซอสหม่าล่าเสฉวนหอมชา หรือต้มน้ำซุปเข้มข้น' }}
                                    </p>
                                </div>

                                <div class="flex items-center justify-between gap-2 mt-3 pt-1">
                                    <div class="flex items-center flex-wrap gap-x-2 gap-y-1 min-w-0">
                                        <div class="flex items-baseline gap-1 shrink-0">
                                            <span data-role="product-price" class="text-[18.5px] mala-font-bold text-[#E50914] leading-none">฿{{ number_format($product->price_per_stick, 0) }}</span>
                                            <span class="text-[12px] mala-font-thin text-slate-500">/ไม้</span>
                                        </div>

                                        @php
                                            $stockQty = (int) $product->stock_sticks;
                                            $stockBadgeClass = $stockQty <= 0
                                                ? 'bg-rose-50 text-rose-700 border-rose-200'
                                                : ($stockQty <= 5
                                                    ? 'bg-amber-50 text-amber-800 border-amber-200'
                                                    : 'bg-emerald-50 text-emerald-700 border-emerald-200');
                                        @endphp
                                        <span data-role="stock-badge"
                                              class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10.5px] mala-font-mid-bold leading-tight whitespace-nowrap shrink-0 {{ $stockBadgeClass }}">
                                            {{ $stockQty > 0 ? 'เหลือ ' . $stockQty . ' ไม้' : 'หมดชั่วคราว' }}
                                        </span>
                                    </div>

                                    <!-- Quick Add (+) Circular Red Button -->
                                    <button type="button"
                                            onclick="event.stopPropagation(); quickAddFromCard(this.closest('.product-card'))"
                                            class="w-8 h-8 rounded-full bg-[#E50914] hover:bg-[#C8102E] text-white flex items-center justify-center shadow-sm hover:scale-105 active:scale-95 transition-all cursor-pointer shrink-0"
                                            title="เพิ่ม {{ $displayName }} ลงบิลทันที">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.6" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m-7-7h14"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Empty Search / Filter State -->
                <div id="menu-empty-state" class="hidden bg-white rounded-[20px] p-10 text-center border border-slate-200/80 my-2">
                    <div class="text-4xl mb-2">🍢</div>
                    <p class="text-[15px] mala-font-bold text-slate-800">ไม่พบเมนูที่ค้นหาในหมวดนี้</p>
                    <p class="text-[12.5px] mala-font-thin text-slate-500 mt-1">ลองเลือกหมวดหมู่ "ทั้งหมด" หรือค้นหาด้วยคำอื่น</p>
                </div>
            </section>
        </div>

        <!-- ========================================================================= -->
        <!-- RIGHT COLUMN (4 Cols): Order Cart Panel ("บิลออเดอร์ปัจจุบัน")             -->
        <!-- ========================================================================= -->
        <aside id="pos-cart-aside" class="lg:col-span-4 xl:col-span-4 lg:sticky w-full"
               style="top: calc(var(--app-header-h, 80px) + 16px);">
            <div id="pos-cart-panel" class="bg-white rounded-[22px] shadow-lg border border-slate-200/90 overflow-hidden flex flex-col transition-all origin-top-right">

                <!-- 1. Red Gradient Cart Header -->
                <div class="bg-gradient-to-r from-[#9E0812] via-[#C8102E] to-[#B00C1B] px-4 py-3.5 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-[17px] mala-font-bold text-white leading-tight truncate">บิลออเดอร์ปัจจุบัน</h2>
                            <div class="text-[11.5px] mala-font-thin text-rose-100 flex items-center gap-1 truncate">
                                <span>โต๊ะ <span id="current-queue-tag" class="mala-font-bold text-white">A02</span> • <span id="fulfillment-mode-label">บริการหน้าร้าน</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" onclick="clearCart()"
                                class="bg-black/20 hover:bg-black/30 text-white/95 text-[11.5px] mala-font-mid-bold px-2.5 py-1.5 rounded-lg flex items-center gap-1 shrink-0 transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>ล้างตะกร้า</span>
                        </button>
                        <button type="button" onclick="openPosCartSizeModal()"
                                title="ปรับขนาดหน้าต่างบิลออเดอร์ปัจจุบัน"
                                aria-label="ปรับขนาดหน้าต่างบิลออเดอร์ปัจจุบัน"
                                class="bg-black/20 hover:bg-black/35 text-white/95 p-1.5 rounded-lg flex items-center justify-center shrink-0 transition-colors cursor-pointer">
                            <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- 2. Customer / Phone / Delivery Input -->
                <div class="px-4 pt-3 pb-2 bg-white">
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <input type="text" id="customer-name-input"
                               placeholder="ชื่อลูกค้า / เบอร์โทร (สำหรับจัดส่ง)"
                               class="w-full pl-9 pr-3 py-2 bg-[#F8F9FA] border border-slate-200/90 rounded-xl text-[12.5px] mala-font-thin text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-[#E50914] focus:bg-white transition-all">
                    </div>
                </div>

                <!-- 3. Selected Items Count Header -->
                <div class="px-4 py-1.5 flex items-center justify-between">
                    <div class="text-[14px] mala-font-bold text-slate-900">
                        รายการที่เลือก (<span id="cart-items-count">0</span> รายการ)
                    </div>
                    <span class="text-[11.5px] mala-font-thin text-slate-400">
                        รวม <span id="cart-sticks-count" class="mala-font-bold text-[#E50914]">0</span> ไม้
                    </span>
                </div>

                <!-- 4. Selected Cart Items List -->
                <div class="px-4 divide-y divide-slate-100 max-h-[300px] overflow-y-auto" id="cart-items-container">
                    <!-- Populated dynamically by renderCart() -->
                </div>

                <!-- Empty Cart State (Hidden when items exist) -->
                <div id="cart-empty-state" class="hidden px-4 py-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-rose-50 text-[#E50914] flex items-center justify-center mx-auto mb-2 text-2xl">🍢</div>
                    <p class="text-[13.5px] mala-font-bold text-slate-700">ยังไม่มีรายการในบิล</p>
                    <p class="text-[11.5px] mala-font-thin text-slate-400 mt-0.5">คลิกปุ่ม + บนการ์ดเมนูเพื่อเพิ่มรายการอาหาร</p>
                </div>

                <!-- 5. Promo / Coupon Code Bar -->
                <div class="px-4 pt-2.5 pb-2">
                    <button type="button" onclick="openCouponModal()"
                            class="w-full bg-[#FFF2F3] hover:bg-[#FFE6E8] border border-[#FCD5D8] rounded-[12px] px-3.5 py-2.5 flex items-center justify-between transition-colors cursor-pointer">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-6 h-5 rounded bg-[#E50914] text-white flex items-center justify-center text-[10px] mala-font-bold shrink-0">🎟️</span>
                            <span id="cart-coupon-label" class="text-[13px] mala-font-mid-bold text-[#C8102E] truncate">ใช้โค้ดส่วนลด</span>
                        </div>
                        <span class="text-[#C8102E] text-sm mala-font-bold">&gt;</span>
                    </button>
                </div>

                <!-- 6. Order Summary Breakdown -->
                <div class="px-4 py-2.5 space-y-1.5 text-[13.5px] border-t border-slate-100">
                    <div class="flex justify-between items-center text-slate-700">
                        <span class="mala-font-thin">ยอดรวมค่าสินค้า (<span id="summary-sticks">0</span> ไม้)</span>
                        <span class="mala-font-mid-bold text-slate-900" id="summary-subtotal">฿0.00</span>
                    </div>

                    <div class="flex justify-between items-center text-slate-700">
                        <span class="mala-font-thin inline-flex items-center gap-1">
                            <span>ค่าจัดส่ง</span>
                        </span>
                        <span class="mala-font-mid-bold text-slate-900" id="summary-shipping">฿0.00</span>
                    </div>

                    <div class="flex justify-between items-center text-[#E50914]">
                        <span class="mala-font-mid-bold">ส่วนลด</span>
                        <span class="mala-font-bold" id="summary-discount">-฿0.00</span>
                    </div>

                    <div class="pt-2.5 mt-1 border-t border-slate-200/90 flex items-baseline justify-between">
                        <span class="text-[16px] mala-font-bold text-slate-900">ยอดสุทธิที่ต้องชำระ</span>
                        <span class="text-[24px] mala-font-max-bold text-[#E50914] tracking-tight leading-none" id="summary-total">฿0.00</span>
                    </div>
                </div>

                <!-- 7. Bottom 2 Stacked Action Tabs: 1.ยืนยันทำรายการ  2.บันทึกร่าง -->
                <div class="p-4 pt-2 flex flex-col gap-2">
                    <!-- Tab 1: ยืนยันทำรายการ (เปิดป๊อปอัปสรุปยอดและวิธีชำระเงิน) -->
                    <button type="button" onclick="openPosConfirmModal()" id="btn-pos-confirm"
                            class="w-full py-3 px-4 rounded-[14px] bg-gradient-to-r from-[#E50914] to-[#C8102E] hover:from-[#C8102E] hover:to-[#A80B24] disabled:opacity-45 disabled:cursor-not-allowed text-white flex items-center justify-center gap-2 shadow-md shadow-rose-600/25 transition-all cursor-pointer">
                        <svg class="w-[18px] h-[18px] stroke-current shrink-0" fill="none" stroke-width="2.3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-[14.5px] mala-font-bold">ยืนยันทำรายการ</span>
                    </button>

                    <!-- Tab 2 (Row 2): บันทึกร่าง (ย้ายไปหน้าจอครัว + แสดงรอยังไม่ได้ชำระยอด) -->
                    <button type="button" onclick="submitPosDraftOrder()" id="btn-pos-draft"
                            class="w-full py-2.5 px-4 rounded-[14px] bg-[#5B6470] hover:bg-[#49505A] disabled:opacity-45 disabled:cursor-not-allowed text-white flex items-center justify-center gap-2 shadow-sm transition-all cursor-pointer">
                        <svg class="w-[17px] h-[17px] stroke-current shrink-0" fill="none" stroke-width="2.1" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-[13.5px] mala-font-bold">บันทึกร่าง</span>
                    </button>
                </div>
            </div>
        </aside>

    </div>
</div>

<!-- Non-blocking Rapid-Tap Quantity Popup (x2 เพิ่ม 2 ไม้, x3 เพิ่ม 3 ไม้ ...) -->
<div id="pos-rapid-add-popup"
     class="pointer-events-none hidden fixed top-20 left-1/2 -translate-x-1/2 z-[80] px-5 py-2.5 rounded-full bg-slate-900/95 text-white shadow-2xl border border-white/15 flex items-center gap-2.5 transition-transform duration-100">
    <span id="pos-rapid-add-count" class="px-2.5 py-0.5 rounded-full bg-[#E50914] text-white font-black text-sm font-mono">x2</span>
    <span id="pos-rapid-add-text" class="text-sm font-extrabold whitespace-nowrap">เพิ่ม 2 ไม้</span>
</div>

<!-- ============================================================================= -->
<!-- MODAL 2: COUPON / PROMO CODE SELECTOR                                         -->
<!-- ============================================================================= -->
<div id="coupon-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100">
        <div class="bg-gradient-to-r from-[#9E0812] to-[#C8102E] p-4 text-white flex justify-between items-center">
            <div class="flex items-center gap-2">
                <span>🎟️</span>
                <h3 class="mala-font-bold text-base">เลือกใช้โค้ดส่วนลดหม่าล่าสตรีท</h3>
            </div>
            <button type="button" onclick="closeCouponModal()" class="text-white/80 hover:text-white text-xl mala-font-bold">&times;</button>
        </div>

        <div class="p-5 space-y-3">
            <div onclick="claimAndApplyCoupon('DISC10'); closeCouponModal();"
                 class="p-3.5 rounded-2xl border-2 border-[#FCD5D8] bg-[#FFF5F5] hover:border-[#E50914] flex items-center justify-between cursor-pointer transition-all">
                <div>
                    <div class="text-sm mala-font-bold text-[#C8102E]">โค้ด DISC10 — ส่วนลด ฿10</div>
                    <div class="text-xs mala-font-thin text-slate-600">เมื่อสั่งอาหารครบ ฿150 ขึ้นไป (หรือคูปองต้อนรับลูกค้า)</div>
                </div>
                <span class="px-3 py-1 rounded-full bg-[#E50914] text-white text-xs mala-font-bold">ใช้คูปอง</span>
            </div>

            <div onclick="claimAndApplyCoupon('DISC20'); closeCouponModal();"
                 class="p-3.5 rounded-2xl border-2 border-[#FCD5D8] bg-[#FFF5F5] hover:border-[#E50914] flex items-center justify-between cursor-pointer transition-all">
                <div>
                    <div class="text-sm mala-font-bold text-[#C8102E]">โค้ด DISC20 — ส่วนลด ฿20</div>
                    <div class="text-xs mala-font-thin text-slate-600">เมื่อสั่งอาหารขั้นต่ำ ฿300 ขึ้นไป</div>
                </div>
                <span class="px-3 py-1 rounded-full bg-[#E50914] text-white text-xs mala-font-bold">ใช้คูปอง</span>
            </div>

            <div onclick="claimAndApplyCoupon('FREESHIP'); closeCouponModal();"
                 class="p-3.5 rounded-2xl border-2 border-[#FCD5D8] bg-[#FFF5F5] hover:border-[#E50914] flex items-center justify-between cursor-pointer transition-all">
                <div>
                    <div class="text-sm mala-font-bold text-[#C8102E]">โค้ด FREESHIP — ส่งฟรี (ลด ฿30)</div>
                    <div class="text-xs mala-font-thin text-slate-600">เมื่อสั่งอาหารขั้นต่ำ ฿400 ขึ้นไป</div>
                </div>
                <span class="px-3 py-1 rounded-full bg-[#E50914] text-white text-xs mala-font-bold">ใช้คูปอง</span>
            </div>

            <div class="pt-2 flex justify-between items-center">
                <button type="button" onclick="removeCoupon(); closeCouponModal();" class="text-xs mala-font-mid-bold text-slate-500 hover:text-rose-600">
                    ยกเลิกการใช้คูปอง
                </button>
                <button type="button" onclick="closeCouponModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs mala-font-mid-bold text-slate-700">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================================= -->
<!-- MODAL 3: PAYMENT MODAL (Cash / PromptPay QR)                                  -->
<!-- ============================================================================= -->
<div id="payment-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100">
        <div class="bg-slate-900 p-4 text-white flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <span class="text-xl" id="pay-modal-icon">💵</span>
                <h3 class="mala-font-bold text-base" id="pay-modal-title">ชำระเงิน</h3>
            </div>
            <button type="button" onclick="closePaymentModal()" class="text-slate-400 hover:text-white text-xl mala-font-bold">&times;</button>
        </div>

        <div class="p-5 space-y-4">
            <div class="text-center bg-rose-50/70 p-4 rounded-2xl border border-rose-200">
                <span class="text-xs text-rose-800 mala-font-mid-bold block">ยอดสุทธิที่ต้องชำระ</span>
                <span class="text-3xl mala-font-max-bold text-[#E50914] tracking-tight" id="pay-modal-amount">฿0.00</span>
                <span class="text-[11px] text-slate-500 mt-1 block">คิวออเดอร์ <span id="pay-queue-tag" class="mala-font-bold text-slate-700">A02</span></span>
            </div>

            <!-- Cash Tender Section -->
            <div id="cash-payment-section" class="space-y-3">
                <div>
                    <label class="block text-xs mala-font-bold text-slate-700 mb-1">รับเงินสดมา (บาท):</label>
                    <input type="number" id="cash-received-input" step="1" oninput="calculateChange()"
                           class="w-full text-xl mala-font-bold text-slate-800 px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:border-[#E50914]">
                </div>

                <div class="grid grid-cols-4 gap-2">
                    <button type="button" onclick="setCashTender('exact')" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs mala-font-bold text-slate-700">พอดี</button>
                    <button type="button" onclick="setCashTender(100)" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs mala-font-bold text-slate-700">฿100</button>
                    <button type="button" onclick="setCashTender(500)" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs mala-font-bold text-slate-700">฿500</button>
                    <button type="button" onclick="setCashTender(1000)" class="py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs mala-font-bold text-slate-700">฿1000</button>
                </div>

                <div class="p-3 bg-slate-100 rounded-xl flex justify-between items-center text-sm mala-font-bold">
                    <span class="text-slate-600">เงินทอน:</span>
                    <span class="text-emerald-600 text-lg" id="change-display">฿0.00</span>
                </div>
            </div>

            <!-- PromptPay QR Section -->
            <div id="promptpay-payment-section" class="space-y-3 hidden text-center">
                <div class="p-4 bg-white border-2 border-dashed border-rose-300 rounded-2xl flex flex-col items-center">
                    <img id="promptpay-qr-img" src="" alt="PromptPay QR" class="w-48 h-48 object-contain rounded-lg shadow-xs">
                    <div class="mt-2 text-xs text-slate-600 mala-font-mid-bold">
                        <div id="pos-qr-store-name" class="font-black text-slate-900 mb-0.5">{{ $promptPaySettings['store_name'] ?? 'ร้านหม่าล่าสตรีท (Mala Street)' }}</div>
                        บัญชี: <strong id="pos-qr-account-no" class="text-slate-800">{{ $promptPaySettings['account_no'] ?? '098-1-53225-2 (กสิกรไทย)' }}</strong>
                        <span class="block text-[11px] text-slate-500 mt-0.5">ชื่อบัญชี: <span id="pos-qr-account-name">{{ $promptPaySettings['account_name'] ?? 'มูนีบ สและแม' }}</span></span>
                    </div>
                    <span class="text-[11px] text-emerald-600 mt-1 mala-font-bold">● สแกน QR โอนชำระได้ทันที (ล็อคยอดเงินอัตโนมัติ)</span>
                    <button type="button" onclick="openPosQrSettingsModal()"
                            class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold border border-slate-200 transition-colors cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        <span>แก้ไข / เปลี่ยนภาพ QR พร้อมเพย์</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-100 flex gap-2">
            <button type="button" onclick="closePaymentModal()" class="w-1/3 py-3 rounded-xl border border-slate-300 text-xs mala-font-mid-bold text-slate-700 hover:bg-slate-100">ยกเลิก</button>
            <button type="button" onclick="submitOrder()" id="btn-confirm-submit" class="w-2/3 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs mala-font-bold shadow-md flex items-center justify-center space-x-1.5">
                <span>✅ ยืนยันชำระเงิน & พิมพ์ใบเสร็จ</span>
            </button>
        </div>
    </div>
</div>

<!-- ============================================================================= -->
<!-- MODAL 3.2: POS CONFIRM CHECKOUT SUMMARY & PAYMENT SPLIT (ตามภาพที่ 2 เป๊ะ)    -->
<!-- ============================================================================= -->
<div id="pos-confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/65 backdrop-blur-xs p-4"
     onclick="if(event.target === this) closePosConfirmModal()">
    <div class="bg-white rounded-3xl max-w-md w-full max-h-[92dvh] overflow-y-auto p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2.5">
                <span id="pos-confirm-queue-tag" class="px-3 py-1 rounded-xl bg-emerald-600 text-white font-black text-base">{{ $nextQueueNumber }}</span>
                <div>
                    <h3 class="text-sm font-black text-slate-900">สรุปยอดก่อนยืนยันทำรายการ</h3>
                    <p class="text-[11px] text-slate-500">ลูกค้า: <span id="pos-confirm-customer" class="font-bold text-slate-700">ลูกค้าหน้าร้าน</span></p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[10.5px] text-slate-400 block">ยอดตามบิล</span>
                <strong id="pos-confirm-total" class="text-lg font-black text-[#E50914] font-mono">฿0.00</strong>
            </div>
        </div>

        <!-- Quick Payment Mode Selector (พร้อมไอคอนสีเดียวเด่น) -->
        <div class="grid grid-cols-3 gap-2">
            <button type="button" data-mode="transfer" onclick="setPosConfirmMode('transfer')"
                class="pos-confirm-mode-btn py-2 px-2.5 rounded-xl text-xs font-bold border transition cursor-pointer whitespace-nowrap flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5 stroke-current shrink-0" fill="none" stroke-width="2.1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span>เงินโอนทั้งหมด</span>
            </button>
            <button type="button" data-mode="cash" onclick="setPosConfirmMode('cash')"
                class="pos-confirm-mode-btn py-2 px-2.5 rounded-xl text-xs font-bold border transition cursor-pointer whitespace-nowrap flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5 stroke-current shrink-0" fill="none" stroke-width="2.1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span>เงินสดทั้งหมด</span>
            </button>
            <button type="button" data-mode="split" onclick="setPosConfirmMode('split')"
                class="pos-confirm-mode-btn py-2 px-2.5 rounded-xl text-xs font-bold border transition cursor-pointer whitespace-nowrap flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5 stroke-current shrink-0" fill="none" stroke-width="2.1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span>แบ่งจ่าย</span>
            </button>
        </div>

        <!-- Mode 1: Transfer All PromptPay Scan Section (ตรงตามภาพที่ 1 เป๊ะๆ) -->
        <div id="pos-confirm-transfer-qr-section" class="hidden space-y-2.5">
            <!-- แถบสรุปราคา (Price Summary Bar) -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-[14px] p-2.5 space-y-1 text-[11px] text-[#596272]">
                <div class="flex justify-between">
                    <span>ค่าอาหาร (<span id="pos-transfer-qr-sticks">0</span> ไม้)</span>
                    <span class="font-bold text-[#101114]" id="pos-transfer-qr-subtotal">฿0</span>
                </div>
                <div class="flex justify-between text-[#D90012]">
                    <span>ส่วนลดคูปอง</span>
                    <span class="font-bold" id="pos-transfer-qr-discount">-฿0</span>
                </div>
                <div class="flex justify-between">
                    <span>ค่าจัดส่ง (<span id="pos-transfer-qr-method">บริการหน้าร้าน</span>)</span>
                    <span class="font-bold text-[#101114]" id="pos-transfer-qr-shipping">ฟรี</span>
                </div>
                <div class="flex justify-between items-center pt-1 mt-1 border-t border-slate-200">
                    <span class="font-extrabold text-[#101114] text-xs">ยอดชำระสุทธิ</span>
                    <span class="text-lg font-black text-[#D90012] font-mono" id="pos-transfer-qr-total">฿0.00</span>
                </div>
            </div>

            <!-- QR Code Box -->
            <div class="bg-white border-2 border-sky-100 rounded-[14px] p-2.5 text-center shadow-xs">
                <div class="w-44 h-44 mx-auto bg-white p-1.5 rounded-xl border border-slate-200 flex items-center justify-center">
                    <img id="pos-transfer-qr-img" src="" alt="PromptPay QR Code" class="w-full h-full object-contain rounded-lg">
                </div>
                <div class="mt-2 text-[11px] text-slate-600">
                    <div data-role="pos-qr-store-name" class="font-bold text-slate-800">{{ $promptPaySettings['store_name'] ?? 'ร้านหม่าล่าสตรีท (Mala Street)' }}</div>
                    บัญชี: <strong data-role="pos-qr-account-no" class="text-slate-900 font-mono">{{ $promptPaySettings['account_no'] ?? '098-1-53225-2 (กสิกรไทย)' }}</strong>
                    <span class="block text-[10px] text-slate-500 mt-0.5">ชื่อบัญชี: <span data-role="pos-qr-account-name">{{ $promptPaySettings['account_name'] ?? 'มูนีบ สและแม' }}</span></span>
                </div>
                <p class="text-[9.5px] text-slate-400 mt-0.5">{{ $promptPaySettings['receipt_note'] ?? 'สแกนได้ทุกแอปธนาคารไทย ยอดเงินจะขึ้นตรงอัตโนมัติ' }}</p>
            </div>

            <!-- Actions -->
            <div class="space-y-1.5 pt-0.5">
                <button type="button" id="btn-pos-transfer-qr-submit" onclick="submitPosConfirmedOrder()"
                        class="w-full py-2.5 rounded-full bg-gradient-to-r from-[#D90012] to-[#B8000F] text-white font-bold text-[13px] shadow-md flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99] transition-transform">
                    <span>โอนเงินเรียบร้อย • ยืนยันสั่งอาหาร</span>
                </button>
                <button type="button" onclick="closePosConfirmModal()"
                        class="w-full py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] cursor-pointer transition-colors">
                    กลับไปแก้ไขข้อมูล
                </button>
            </div>
        </div>

        <!-- Mode 3: Split Inputs & Summary -->
        <div id="pos-confirm-split-section" class="space-y-4">
            <!-- Cash & Transfer Inputs (พร้อมไอคอนสีเดียวเด่น) -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-[11px] font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-700 stroke-current shrink-0" fill="none" stroke-width="2.1" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>เงินสด (บาท)</span>
                    </label>
                    <input type="number" step="0.01" min="0" id="pos-confirm-cash" oninput="onPosConfirmInput('cash')"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm font-mono font-bold text-slate-900 focus:outline-none focus:border-slate-900">
                </div>
                <div>
                    <label class="text-[11px] font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-700 stroke-current shrink-0" fill="none" stroke-width="2.1" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span>เงินโอน (บาท)</span>
                    </label>
                    <input type="number" step="0.01" min="0" id="pos-confirm-transfer" oninput="recalcPosConfirmSummary()"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm font-mono font-bold text-slate-900 focus:outline-none focus:border-slate-900">
                </div>
            </div>

            <div class="px-3.5 py-2 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600">ยอดรับรวม: <strong id="pos-confirm-received-sum" class="font-mono text-slate-900">฿0.00</strong></span>
                <span id="pos-confirm-match-status" class="text-[11px] font-bold text-emerald-700">ยอดตรงตามบิลครบถ้วน ✓</span>
            </div>

            <!-- Order Note Textbox -->
            <div>
                <label class="text-[11px] font-bold text-slate-700 block mb-1">หมายเหตุ</label>
                <textarea id="pos-confirm-note" rows="2" placeholder="ระบุหมายเหตุเพิ่มเติม (ถ้ามี)..."
                    class="w-full px-3 py-2 rounded-2xl border border-slate-200 text-xs text-slate-900 focus:outline-none focus:border-slate-900"></textarea>
            </div>
        </div>

        <!-- Mode 2: Cash Tender Section (ตรงตามภาพที่ 2 เป๊ะๆ) -->
        <div id="pos-confirm-cash-tender-section" class="hidden space-y-3">
            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1.5">รับเงินสดมา (บาท):</label>
                <input type="number" step="0.01" min="0" id="pos-confirm-cash-received" oninput="recalcPosConfirmCashChange()"
                    class="w-full px-4 py-3 rounded-2xl border border-slate-300 text-lg font-bold text-slate-900 focus:outline-none focus:border-slate-900">
            </div>

            <div class="grid grid-cols-4 gap-2">
                <button type="button" onclick="setPosConfirmCashTender('exact')"
                    class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold cursor-pointer transition">พอดี</button>
                <button type="button" onclick="setPosConfirmCashTender(100)"
                    class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold cursor-pointer transition">฿100</button>
                <button type="button" onclick="setPosConfirmCashTender(500)"
                    class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold cursor-pointer transition">฿500</button>
                <button type="button" onclick="setPosConfirmCashTender(1000)"
                    class="py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold cursor-pointer transition">฿1000</button>
            </div>

            <div class="px-4 py-3 rounded-2xl bg-slate-100/90 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700">เงินทอน:</span>
                <strong id="pos-confirm-change-display" class="text-lg font-black text-emerald-600 font-mono">฿0.00</strong>
            </div>
        </div>

        <div id="pos-confirm-default-footer" class="grid grid-cols-2 gap-2.5 pt-1">
            <button type="button" id="btn-pos-confirm-cancel" onclick="closePosConfirmModal()"
                class="py-2.5 rounded-2xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 text-xs font-bold cursor-pointer">
                กลับไปหน้าทำรายการ
            </button>
            <button type="button" id="btn-pos-confirm-submit" onclick="submitPosConfirmedOrder()"
                class="py-2.5 rounded-2xl bg-[#059669] hover:bg-[#047857] text-white text-xs font-bold shadow-sm cursor-pointer">
                ยืนยันถูกต้องแล้ว
            </button>
        </div>
    </div>
</div>

<!-- ============================================================================= -->
<!-- MODAL 3.3: SPLIT PAYMENT FINAL PROMPTPAY QR SCAN MODAL (ตามภาพที่ 1 เป๊ะ)      -->
<!-- ============================================================================= -->
<div id="pos-split-qr-modal" class="hidden fixed inset-0 z-[55] bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto"
     onclick="if(event.target === this) backFromSplitQrToConfirmModal()">
    <div class="bg-white w-full max-w-[340px] my-auto max-h-[90dvh] flex flex-col rounded-[22px] overflow-hidden shadow-2xl border border-slate-100">
        <!-- Header -->
        <div class="shrink-0 bg-gradient-to-r from-[#0F2C59] to-[#1B498A] px-3.5 py-2.5 text-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-white/15 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h6v6H3V3zm12 0h6v6h-6V3zM3 15h6v6H3v-6zm12 0h2v2h-2v-2zm4 0h2v2h-2v-2zm-4 4h2v2h-2v-2zm4 0h2v2h-2v-2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-[13px] font-black tracking-tight leading-tight">สแกนจ่าย PromptPay</h3>
                    <p class="text-[10px] text-sky-100">คิวออเดอร์: <span id="pos-split-qr-queue" class="font-mono font-bold text-white">-</span> (แบ่งจ่าย)</p>
                </div>
            </div>
            <button type="button" onclick="backFromSplitQrToConfirmModal()" class="w-6 h-6 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center text-[11px] cursor-pointer">✕</button>
        </div>

        <div class="flex-1 overflow-y-auto overscroll-contain p-3 space-y-2.5">
            <!-- แถบสรุปราคาแบ่งจ่าย -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-[14px] p-2.5 space-y-1 text-[11px] text-[#596272]">
                <div class="flex justify-between">
                    <span>ยอดรวมตามบิล (<span id="pos-split-qr-sticks">0</span> ไม้)</span>
                    <span class="font-bold text-[#101114]" id="pos-split-qr-bill-total">฿0.00</span>
                </div>
                <div class="flex justify-between text-emerald-700">
                    <span>รับเงินสดแล้ว</span>
                    <span class="font-bold" id="pos-split-qr-cash-paid">-฿0.00</span>
                </div>
                <div class="flex justify-between items-center pt-1 mt-1 border-t border-slate-200">
                    <span class="font-extrabold text-[#101114] text-xs">ยอดชำระสุทธิ (โอน)</span>
                    <span class="text-lg font-black text-[#D90012] font-mono" id="pos-split-qr-transfer-total">฿0.00</span>
                </div>
            </div>

            <!-- QR Code Box -->
            <div class="bg-white border-2 border-sky-100 rounded-[14px] p-2.5 text-center shadow-xs">
                <div class="w-44 h-44 mx-auto bg-white p-1.5 rounded-xl border border-slate-200 flex items-center justify-center">
                    <img id="pos-split-qr-img" src="" alt="PromptPay QR Code" class="w-full h-full object-contain rounded-lg">
                </div>
                <div class="mt-2 text-[11px] text-slate-600">
                    <div data-role="pos-qr-store-name" class="font-bold text-slate-800">{{ $promptPaySettings['store_name'] ?? 'ร้านหม่าล่าสตรีท (Mala Street)' }}</div>
                    บัญชี: <strong data-role="pos-qr-account-no" class="text-slate-900 font-mono">{{ $promptPaySettings['account_no'] ?? '098-1-53225-2 (กสิกรไทย)' }}</strong>
                    <span class="block text-[10px] text-slate-500 mt-0.5">ชื่อบัญชี: <span data-role="pos-qr-account-name">{{ $promptPaySettings['account_name'] ?? 'มูนีบ สและแม' }}</span></span>
                </div>
                <p class="text-[9.5px] text-slate-400 mt-0.5">{{ $promptPaySettings['receipt_note'] ?? 'สแกนได้ทุกแอปธนาคารไทย ยอดเงินจะขึ้นตรงอัตโนมัติ' }}</p>
            </div>

            <!-- Actions -->
            <div class="space-y-1.5 pt-0.5">
                <button type="button" id="btn-pos-split-qr-submit" onclick="executePosConfirmedOrderSubmission()"
                        class="w-full py-2.5 rounded-full bg-gradient-to-r from-[#D90012] to-[#B8000F] text-white font-bold text-[13px] shadow-md flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99] transition-transform">
                    <span>โอนเงินเรียบร้อย • ยืนยันสั่งอาหาร</span>
                </button>
                <button type="button" onclick="backFromSplitQrToConfirmModal()"
                        class="w-full py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] cursor-pointer transition-colors">
                    กลับไปแก้ไขข้อมูล
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================================= -->
<!-- MODAL 3.35: POS CART PANEL SIZE ADJUSTER (5 LEVELS: -1 / +1 + LOCAL/GLOBAL)   -->
<!-- ============================================================================= -->
<div id="pos-cart-size-modal" class="hidden fixed inset-0 z-[65] bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
     onclick="if(event.target === this) closePosCartSizeModal(true)">
    <div class="bg-white rounded-3xl max-w-sm w-full p-5 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-black text-slate-900">ปรับขนาดหน้าต่างบิลออเดอร์</h3>
                <p class="text-[11px] text-slate-500">ปรับได้ 10 ระดับ (ค่ากลางมาตรฐานคือระดับ 5)</p>
            </div>
            <button type="button" onclick="closePosCartSizeModal(true)" class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs cursor-pointer">✕</button>
        </div>

        <!-- Stepper -1 / +1 -->
        <div class="flex items-center justify-between gap-3 bg-slate-50 border border-slate-200/80 rounded-2xl p-3">
            <button type="button" id="btn-cart-size-minus" onclick="stepPosCartSize(-1)"
                    class="w-11 h-11 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-800 font-black text-base shadow-2xs cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                -1
            </button>
            <div class="text-center">
                <div class="text-lg font-black text-[#E50914]" id="pos-cart-size-level-text">ระดับ 5 / 10</div>
                <div class="text-[11px] font-bold text-slate-500" id="pos-cart-size-desc-text">ขนาดกลาง (เริ่มต้น)</div>
            </div>
            <button type="button" id="btn-cart-size-plus" onclick="stepPosCartSize(1)"
                    class="w-11 h-11 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-800 font-black text-base shadow-2xs cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                +1
            </button>
        </div>

        <!-- 2 Save Modes -->
        <div class="space-y-2 pt-1">
            <button type="button" onclick="savePosCartSizeLocal()"
                    class="w-full py-2.5 px-4 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm cursor-pointer transition">
                บันทึกเฉพาะเครื่องนี้
            </button>
            <button type="button" id="btn-save-cart-size-global" onclick="savePosCartSizeGlobal()"
                    class="w-full py-2.5 px-4 rounded-2xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-bold shadow-sm cursor-pointer transition">
                บันทึกสำหรับทุกคน (รีเซ็ทเป็นค่าเดียวกัน)
            </button>
        </div>
    </div>
</div>

<!-- ============================================================================= -->
<!-- MODAL 3.4: POS PROMPTPAY WEBHOOK VERIFICATION MODAL (เหมือนหน้าออนไลน์)       -->
<!-- ============================================================================= -->
<div id="pos-payment-verifying-modal" class="hidden fixed inset-0 z-[70] bg-black/75 backdrop-blur-2xs flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-[380px] rounded-[26px] p-6 text-center shadow-2xl border border-slate-100 space-y-4">
        <!-- State 1: Verifying -->
        <div id="pos-verify-state-waiting" class="space-y-3">
            <div class="w-16 h-16 rounded-full bg-amber-50 border-2 border-amber-200 text-amber-600 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"></path>
                </svg>
            </div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-700 font-mono text-[11px] font-bold">
                <span>ออเดอร์ <span id="pos-verify-order-no">-</span></span>
                <span>•</span>
                <span>คิว <span id="pos-verify-queue-no" class="text-[#D90012]">-</span></span>
            </div>
            <h3 class="text-base font-black text-slate-900">โปรดรอตรวจสอบเพื่อการชำระเงินของท่าน</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                ระบบกำลังตรวจสอบยอดเงินเข้าจริงและความครบถ้วนของยอด
                <strong id="pos-verify-expected-amount" class="text-[#D90012] font-mono">฿0.00</strong>
            </p>
            <div class="pt-1">
                <button type="button" onclick="reopenPosQrFromVerifying()"
                        class="w-full py-2.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer transition-colors">
                    กลับไปหน้า QR สแกนจ่าย
                </button>
            </div>
        </div>

        <!-- State 2: Verified Full Amount -->
        <div id="pos-verify-state-success" class="hidden space-y-3">
            <div class="w-16 h-16 rounded-full bg-emerald-100 border-2 border-emerald-300 text-emerald-600 flex items-center justify-center mx-auto text-3xl font-black animate-bounce">
                ✓
            </div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono text-[11px] font-bold">
                <span>รับยอดสำเร็จ</span>
                <span>•</span>
                <span id="pos-verified-order-no">-</span>
            </div>
            <h3 class="text-base font-black text-emerald-700 leading-snug">
                เราได้รับยอดครบเต็มจำนวนแล้ว <span id="pos-verified-paid-amount" class="font-mono underline decoration-2">0.00</span> บาท
            </h3>
            <p class="text-sm font-bold text-slate-800">
                กำลังเปิดใบเสร็จรับเงิน...
            </p>
        </div>
    </div>
</div>

<!-- ============================================================================= -->
<!-- MODAL 3.5: EDIT / UPLOAD STORE PROMPTPAY QR SETTINGS                          -->
<!-- ============================================================================= -->
<div id="pos-qr-settings-modal" class="fixed inset-0 z-[60] bg-black/65 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden border border-slate-200">
        <div class="px-5 py-4 bg-slate-900 text-white flex items-center justify-between">
            <div>
                <h3 class="text-sm font-black">แก้ไข / เปลี่ยน QR พร้อมเพย์ของร้าน</h3>
                <p class="text-[11px] text-slate-300 mt-0.5">อัปโหลดภาพ QR ใหม่ ระบบจะถอดรหัสและล็อคยอดเงินให้อัตโนมัติ</p>
            </div>
            <button type="button" onclick="closePosQrSettingsModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center cursor-pointer">✕</button>
        </div>

        <div class="p-5 space-y-3.5 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1.5">อัปโหลดภาพ QR Code ใหม่ (PNG / JPG)</label>
                <label class="flex flex-col items-center justify-center gap-1.5 p-4 rounded-2xl border-2 border-dashed border-sky-300 bg-sky-50/60 hover:bg-sky-50 cursor-pointer transition-colors">
                    <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span class="font-bold text-sky-800">คลิกเพื่อเลือกภาพ QR พร้อมเพย์จากเครื่อง</span>
                    <span class="text-[10.5px] text-slate-500">รองรับภาพ QR ธนาคาร, แม่มณี, KShop หรือโปสเตอร์ร้าน</span>
                    <input type="file" id="pos-qr-file-input" accept="image/*" class="hidden" onchange="decodeUploadedPromptPayQr(this)">
                </label>
                <div id="pos-qr-decode-status" class="mt-1.5 text-[11px] font-bold hidden"></div>
            </div>

            <div class="grid grid-cols-1 gap-2.5">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">เลขบัญชี / ธนาคารที่แสดง</label>
                    <input type="text" id="pos-qr-input-account-no"
                           value="{{ $promptPaySettings['account_no'] ?? '098-1-53225-2 (กสิกรไทย)' }}"
                           placeholder="เช่น 098-1-53225-2 (กสิกรไทย)"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-rose-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">ชื่อบัญชีที่แสดง</label>
                    <input type="text" id="pos-qr-input-account-name"
                           value="{{ $promptPaySettings['account_name'] ?? 'มูนีบ สและแม' }}"
                           placeholder="เช่น มูนีบ สและแม"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:border-rose-500 focus:outline-none">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">รหัส QR (EMVCo Payload) หรือเบอร์พร้อมเพย์</label>
                    <textarea id="pos-qr-input-payload" rows="2"
                              class="w-full px-3 py-2 rounded-xl border border-slate-200 font-mono text-[10.5px] text-slate-700 focus:border-rose-500 focus:outline-none">{{ $promptPaySettings['payload'] ?? '' }}</textarea>
                </div>
            </div>

            <!-- Android Notification Webhook (Auto Confirm Paid Without Slip) -->
            <div class="rounded-2xl bg-emerald-50/70 border border-emerald-200 p-3.5 space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-extrabold text-emerald-900 text-xs">🤖 ยืนยันเงินเข้าอัตโนมัติ (Android / MacroDroid)</span>
                    <button type="button" onclick="testAndroidPaymentWebhook()" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[10.5px] font-bold cursor-pointer">ทดสอบ Webhook</button>
                </div>
                <div class="space-y-1.5 text-[11px]">
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-600 shrink-0 font-bold">URL:</span>
                        <input type="text" id="pos-webhook-url" readonly value="{{ $webhookConfig['webhook_url'] ?? url('/webhooks/android-payment') }}" class="flex-1 px-2 py-1 rounded-lg bg-white border border-emerald-200 font-mono text-[10px] text-slate-700">
                        <button type="button" onclick="copyPosField('pos-webhook-url')" class="px-2 py-1 rounded-lg bg-white border border-emerald-200 text-emerald-800 font-bold text-[10px] cursor-pointer hover:bg-emerald-100">คัดลอก</button>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-600 shrink-0 font-bold">Secret:</span>
                        <input type="text" id="pos-webhook-secret" readonly value="{{ $webhookConfig['secret'] ?? '' }}" class="flex-1 px-2 py-1 rounded-lg bg-white border border-emerald-200 font-mono text-[10px] text-slate-700">
                        <button type="button" onclick="copyPosField('pos-webhook-secret')" class="px-2 py-1 rounded-lg bg-white border border-emerald-200 text-emerald-800 font-bold text-[10px] cursor-pointer hover:bg-emerald-100">คัดลอก</button>
                    </div>
                    <div id="pos-webhook-last-status" class="text-[10.5px] text-emerald-800 font-medium">
                        @if(!empty($webhookConfig['last_received_at']))
                            ล่าสุด: {{ $webhookConfig['last_received_at'] }} (฿{{ $webhookConfig['last_amount'] ?: '-' }})
                            @if(!empty($webhookConfig['last_matched_order_no'])) • จับคู่บิล {{ $webhookConfig['last_matched_order_no'] }} @endif
                        @else
                            สถานะ: พร้อมรับแจ้งเตือนจากมือถือ/แท็บเล็ต Android (ส่ง JSON: secret, message)
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
            <button type="button" onclick="closePosQrSettingsModal()" class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-xs font-bold text-slate-700 cursor-pointer">ยกเลิก</button>
            <button type="button" id="btn-save-pos-qr" onclick="savePosPromptPayQr()" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm cursor-pointer">บันทึก QR พร้อมเพย์</button>
        </div>
    </div>
</div>
<script src="{{ asset('js/jsQR.js') }}"></script>

<!-- ============================================================================= -->
<!-- MODAL 4: 80MM THERMAL RECEIPT PREVIEW                                         -->
<!-- ============================================================================= -->
<div id="receipt-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full shadow-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[95vh]">
        <div class="p-4 bg-slate-900 text-white flex justify-between items-center no-print">
            <h3 class="mala-font-bold text-sm">ใบเสร็จออเดอร์ (80mm)</h3>
            <button type="button" onclick="closeReceiptModal()" class="text-slate-400 hover:text-white">&times;</button>
        </div>

        <div class="p-6 overflow-y-auto" id="thermal-receipt">
            <div class="text-center border-b border-dashed border-slate-400 pb-3 mb-3">
                <h2 class="text-lg mala-font-max-bold tracking-tight">ร้านหม่าล่าสตรีท</h2>
                <p class="text-xs text-slate-600">Mala Street Bar & Skewers</p>
                <p class="text-[11px] text-slate-500">โทร 089-123-4567</p>
                <div class="mt-2 py-1 bg-slate-100 rounded text-center">
                    <span class="text-xs text-slate-500">บัตรคิว / โต๊ะ</span>
                    <div class="text-2xl mala-font-max-bold text-slate-900" id="receipt-queue-number">A02</div>
                </div>
                <div class="text-[10px] text-slate-400 mt-1" id="receipt-datetime"></div>
                <div class="text-[10px] text-slate-400" id="receipt-order-no"></div>
            </div>

            <div class="text-xs space-y-1.5 border-b border-dashed border-slate-400 pb-3 mb-3" id="receipt-items-list"></div>

            <div class="text-xs space-y-1 border-b border-dashed border-slate-400 pb-3 mb-3">
                <div class="flex justify-between mala-font-bold text-sm">
                    <span>ยอดสุทธิทั้งสิ้น:</span>
                    <span id="receipt-total-amount">฿0.00</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>ชำระโดย:</span>
                    <span id="receipt-pay-method">เงินสด</span>
                </div>
            </div>

            <div class="text-center text-[10px] text-slate-400 pt-1">
                <p>ขอบพระคุณที่อุดหนุนหม่าล่าสตรีท</p>
                <p>ย่างหอม ซุปเข้มข้น เผ็ดชาถึงใจ!</p>
            </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200 flex gap-2 no-print">
            <button type="button" onclick="window.print()" class="flex-1 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs mala-font-bold flex items-center justify-center space-x-1.5 shadow">
                <span>🖨️ พิมพ์ใบเสร็จ</span>
            </button>
            <button type="button" onclick="closeReceiptModal()" class="py-2.5 px-4 rounded-xl border border-slate-300 text-xs mala-font-mid-bold text-slate-700 hover:bg-slate-100">
                เสร็จสิ้น
            </button>
        </div>
    </div>
</div>

<!-- ============================================================================= -->
<!-- MODAL 5: STORE REVIEWS & STORE INFORMATION                                    -->
<!-- ============================================================================= -->
<div id="store-info-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl overflow-hidden border border-slate-100">
        <div class="bg-gradient-to-r from-[#450408] to-[#8F0B16] p-4 text-white flex justify-between items-center">
            <h3 class="mala-font-bold text-base" id="store-info-title">รีวิวและข้อมูลร้านหม่าล่าสตรีท</h3>
            <button type="button" onclick="document.getElementById('store-info-modal').classList.add('hidden')" class="text-white/80 hover:text-white text-xl mala-font-bold">&times;</button>
        </div>
        <div class="p-5 space-y-3 text-xs text-slate-700">
            <div class="flex items-center gap-3 p-3 rounded-2xl bg-amber-50 border border-amber-200">
                <span class="text-3xl">⭐</span>
                <div>
                    <div class="text-base mala-font-bold text-slate-900">คะแนน 4.9 / 5.0 (500+ รีวิวลูกค้าจริง)</div>
                    <div class="text-slate-600 mala-font-thin">หม่าล่าสไตล์เสฉวนต้นตำรับ ย่างเตาถ่านหอมไร้ควัน ซุปเข้มข้น</div>
                </div>
            </div>
            <div class="space-y-1.5 p-3 rounded-2xl bg-slate-50 border border-slate-200">
                <div><strong class="mala-font-bold">📍 พิกัดร้าน:</strong> ตลาดสตรีทฟู้ด หม่าล่าสตรีท (ระยะทาง 2.3 กม.)</div>
                <div><strong class="mala-font-bold">🕒 เวลาทำการ:</strong> เปิดบริการทุกวัน 15:00 - 24:00 น.</div>
                <div><strong class="mala-font-bold">🛵 เวลาจัดส่งเฉลี่ย:</strong> ~15-20 นาที (ค่าส่ง ฿30 / ส่งฟรีเมื่อครบ ฿400)</div>
                <div><strong class="mala-font-bold">📞 โทรศัพท์:</strong> 089-123-4567</div>
            </div>
            <button type="button" onclick="document.getElementById('store-info-modal').classList.add('hidden')" class="w-full py-2.5 rounded-xl bg-[#E50914] text-white mala-font-bold">
                ปิดหน้าต่าง
            </button>
        </div>
    </div>
</div>

<!-- Floating Real-time Sync Toast -->
<div id="pos-sync-toast" class="fixed bottom-6 left-6 z-50 hidden items-center gap-2.5 px-4 py-3 rounded-2xl bg-slate-900/95 text-white text-[12.5px] mala-font-mid-bold shadow-2xl border border-slate-700/80 backdrop-blur-sm pointer-events-none transition-all">
    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
    <span id="pos-sync-toast-text">ซิงค์ข้อมูลจากหน้าตรวจจับราคาเรียบร้อยแล้ว</span>
</div>

@push('scripts')
<script>
    // Initial Cart State (Seeded with the 3 items from Image 1 so visual state matches 100% immediately)
    let cart = @json($initialCartSeed);
    let currentItem = null;
    let currentPaymentMethod = 'cash';
    let currentQueue = 'A02';
    let fulfillmentMode = 'delivery'; // 'delivery' (฿30) or 'pos' (฿0)
    let activeCoupon = 'DISC10';      // Matches Image 1's -฿10.00 discount
    let claimedCoupons = { DISC10: true, DISC20: false, FREESHIP: false };
    let isFavoriteStore = true;
    let currentGridCols = 3;

    document.addEventListener('DOMContentLoaded', function() {
        renderCart();
        updateCouponButtonsUI();
        setupStickyBar();
        const savedCols = parseInt(localStorage.getItem('mala_street_grid_cols')) || 3;
        setGridColumns(savedCols);

        // 🔄 Real-time synchronization with Price Tracker (ภาพ & ชื่อรายการ)
        if ('BroadcastChannel' in window) {
            try {
                const posSyncChannel = new BroadcastChannel('mala_pos_sync_channel');
                posSyncChannel.onmessage = function(ev) {
                    const data = ev.data;
                    if (data && data.action === 'material_updated') {
                        syncPosItemFromPriceTracker(
                            data.material_id,
                            data.name,
                            data.image_url,
                            data.category_slug,
                            data.stock_sticks,
                            data.selling_price
                        );
                    } else if (data && data.action === 'qr_updated' && data.settings) {
                        STORE_PROMPTPAY_SETTINGS = data.settings;
                        const sEl = document.getElementById('pos-qr-store-name');
                        const nEl = document.getElementById('pos-qr-account-no');
                        const aEl = document.getElementById('pos-qr-account-name');
                        if (sEl && data.settings.store_name) sEl.textContent = data.settings.store_name;
                        if (nEl && data.settings.account_no) nEl.textContent = data.settings.account_no;
                        if (aEl && data.settings.account_name) aEl.textContent = data.settings.account_name;
                        if (typeof refreshPosPromptPayQrImage === 'function') {
                            refreshPosPromptPayQrImage(getCartTotal());
                        }
                        showPosSyncToast('ซิงค์ QR พร้อมเพย์และข้อมูลร้านจากหน้าจัดการแคชเชียร์แล้ว');
                    }
                };

                const receiptSyncChannel = new BroadcastChannel('mala_receipt_sync_channel');
                receiptSyncChannel.onmessage = function() {
                    refreshPosMenuLive();
                };

                const categorySyncChannel = new BroadcastChannel('mala_category_sync_channel');
                categorySyncChannel.onmessage = function(ev) {
                    applyCategorySyncMessage(ev.data);
                };
            } catch (e) {}
        }

        // Live polling to keep stocks, names & photos synchronized (paused when tab hidden)
        setInterval(function() {
            if (!document.hidden) {
                refreshPosMenuLive();
            }
        }, 20000);
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                refreshPosMenuLive();
            }
        });
    });

    // Toggle 3 vs 4 columns grid per row (Matches Image 3)
    function setGridColumns(cols) {
        currentGridCols = cols;
        try { localStorage.setItem('mala_street_grid_cols', cols); } catch (e) {}

        const grid = document.getElementById('products-grid');
        const btn3 = document.getElementById('btn-grid-3');
        const btn4 = document.getElementById('btn-grid-4');

        if (!grid || !btn3 || !btn4) return;

        if (cols === 4) {
            grid.className = 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 transition-all duration-200';
            btn4.className = 'grid-toggle-btn active px-2.5 py-1 rounded-full text-xs mala-font-bold flex items-center gap-1.5 transition-all bg-[#E50914] text-white shadow-2xs cursor-pointer';
            btn3.className = 'grid-toggle-btn px-2.5 py-1 rounded-full text-xs mala-font-bold flex items-center gap-1.5 transition-all text-slate-500 hover:text-slate-800 hover:bg-slate-100 cursor-pointer';
        } else {
            grid.className = 'grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 transition-all duration-200';
            btn3.className = 'grid-toggle-btn active px-2.5 py-1 rounded-full text-xs mala-font-bold flex items-center gap-1.5 transition-all bg-[#E50914] text-white shadow-2xs cursor-pointer';
            btn4.className = 'grid-toggle-btn px-2.5 py-1 rounded-full text-xs mala-font-bold flex items-center gap-1.5 transition-all text-slate-500 hover:text-slate-800 hover:bg-slate-100 cursor-pointer';
        }
    }

    // Sticky Header Offset & Floating Bar Shadow Handling
    function setupStickyBar() {
        const header = document.querySelector('header');
        const bar = document.getElementById('sticky-floating-bar');

        function updateOffset() {
            const h = header ? header.offsetHeight : 80;
            document.documentElement.style.setProperty('--app-header-h', h + 'px');

            if (bar) {
                const rect = bar.getBoundingClientRect();
                // When pinned against top header
                if (rect.top <= h + 2) {
                    bar.classList.add('shadow-md', 'border-b', 'border-slate-300/80', 'rounded-b-2xl');
                } else {
                    bar.classList.remove('shadow-md', 'border-b', 'border-slate-300/80', 'rounded-b-2xl');
                }
            }
        }

        window.addEventListener('scroll', updateOffset, { passive: true });
        window.addEventListener('resize', updateOffset, { passive: true });
        updateOffset();
    }

    // Live Search Filter
    document.getElementById('search-input').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase().trim();
        let visibleCount = 0;
        document.querySelectorAll('.product-card').forEach(card => {
            const name = card.dataset.name.toLowerCase();
            const match = name.includes(query);
            card.style.display = match ? 'flex' : 'none';
            if (match) visibleCount++;
        });
        document.getElementById('visible-menu-count').innerText = visibleCount;
        document.getElementById('menu-empty-state').classList.toggle('hidden', visibleCount > 0);
    });

    // Category Filter (Horizontal Pills matching Image 2)
    function filterCategory(slug, btnEl) {
        document.querySelectorAll('.cat-pill').forEach(btn => {
            btn.classList.remove('bg-[#E50914]', 'text-white', 'border-[#E50914]', 'shadow-sm', 'active');
            btn.classList.add('bg-white', 'text-[#1F2430]', 'border-slate-200/90', 'shadow-2xs');
            const iconBox = btn.querySelector('.cat-icon-box');
            if (iconBox) iconBox.classList.remove('bg-white/15');
            const iconSvg = btn.querySelector('.cat-icon-svg');
            if (iconSvg) {
                iconSvg.classList.remove('text-white');
                iconSvg.classList.add('text-[#E50914]');
            }
        });

        if (btnEl) {
            btnEl.classList.remove('bg-white', 'text-[#1F2430]', 'border-slate-200/90', 'shadow-2xs');
            btnEl.classList.add('bg-[#E50914]', 'text-white', 'border-[#E50914]', 'shadow-sm', 'active');
            const iconBox = btnEl.querySelector('.cat-icon-box');
            if (iconBox) iconBox.classList.add('bg-white/15');
            const iconSvg = btnEl.querySelector('.cat-icon-svg');
            if (iconSvg) {
                iconSvg.classList.remove('text-[#E50914]');
                iconSvg.classList.add('text-white');
            }
        }

        let visibleCount = 0;
        document.querySelectorAll('.product-card').forEach(card => {
            if (slug === 'all' || card.dataset.category === slug) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        document.getElementById('visible-menu-count').innerText = visibleCount;
        document.getElementById('menu-empty-state').classList.toggle('hidden', visibleCount > 0);
    }

    // Sort Products Grid
    function sortProductsGrid(mode) {
        const grid = document.getElementById('products-grid');
        const cards = Array.from(grid.querySelectorAll('.product-card'));

        cards.sort((a, b) => {
            const priceA = parseFloat(a.dataset.price);
            const priceB = parseFloat(b.dataset.price);
            const sortA = parseInt(a.dataset.sort);
            const sortB = parseInt(b.dataset.sort);
            const popA = parseInt(a.dataset.popular);
            const popB = parseInt(b.dataset.popular);

            if (mode === 'price_asc') return priceA - priceB;
            if (mode === 'price_desc') return priceB - priceA;
            if (mode === 'popular') return popA - popB;
            return sortA - sortB;
        });

        cards.forEach(c => grid.appendChild(c));
    }

    // Top Cooking Style & Spice / Soup Quick Preset Controller
    let posTopCookingType = 'grill';
    let posTopSpiciness = 'medium';
    let posTopSoupStyle = 'tomyum_rich';

    function setPosTopCookingType(type) {
        posTopCookingType = type === 'soup' ? 'soup' : 'grill';
        const btnGrill = document.getElementById('pos-top-btn-grill');
        const btnSoup = document.getElementById('pos-top-btn-soup');
        const radioGrill = document.getElementById('pos-top-radio-grill');
        const radioSoup = document.getElementById('pos-top-radio-soup');
        const spiceSec = document.getElementById('pos-top-spiciness-section');
        const soupSec = document.getElementById('pos-top-soup-section');

        const activeBtnCls = 'p-2.5 rounded-2xl border-2 text-left transition-all border-[#D90012] bg-[#FFF0F1] shadow-xs flex items-center gap-2 group cursor-pointer';
        const inactiveBtnCls = 'p-2.5 rounded-2xl border-2 text-left transition-all border-slate-200 bg-white flex items-center gap-2 hover:border-slate-300 group cursor-pointer';
        const activeRadioHtml = '<div class="w-2 h-2 rounded-full bg-blue-600"></div>';

        if (posTopCookingType === 'grill') {
            if (btnGrill) {
                btnGrill.className = activeBtnCls;
                btnGrill.querySelector('.font-extrabold')?.classList.replace('text-[#101114]', 'text-[#D90012]');
            }
            if (btnSoup) {
                btnSoup.className = inactiveBtnCls;
                btnSoup.querySelector('.font-extrabold')?.classList.replace('text-[#D90012]', 'text-[#101114]');
            }
            if (radioGrill) {
                radioGrill.className = 'w-4 h-4 rounded-full border-2 border-blue-600 flex items-center justify-center shrink-0 p-0.5';
                radioGrill.innerHTML = activeRadioHtml;
            }
            if (radioSoup) {
                radioSoup.className = 'w-4 h-4 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0';
                radioSoup.innerHTML = '';
            }
            if (spiceSec) spiceSec.classList.remove('hidden');
            if (soupSec) soupSec.classList.add('hidden');
        } else {
            if (btnSoup) {
                btnSoup.className = activeBtnCls;
                btnSoup.querySelector('.font-extrabold')?.classList.replace('text-[#101114]', 'text-[#D90012]');
            }
            if (btnGrill) {
                btnGrill.className = inactiveBtnCls;
                btnGrill.querySelector('.font-extrabold')?.classList.replace('text-[#D90012]', 'text-[#101114]');
            }
            if (radioSoup) {
                radioSoup.className = 'w-4 h-4 rounded-full border-2 border-blue-600 flex items-center justify-center shrink-0 p-0.5';
                radioSoup.innerHTML = activeRadioHtml;
            }
            if (radioGrill) {
                radioGrill.className = 'w-4 h-4 rounded-full border-2 border-slate-400 flex items-center justify-center shrink-0';
                radioGrill.innerHTML = '';
            }
            if (soupSec) soupSec.classList.remove('hidden');
            if (spiceSec) spiceSec.classList.add('hidden');
        }
    }

    let posRapidAddTimer = null;

    function showRapidAddPopup(productName, qty) {
        const popup = document.getElementById('pos-rapid-add-popup');
        const countEl = document.getElementById('pos-rapid-add-count');
        const textEl = document.getElementById('pos-rapid-add-text');
        if (!popup || !countEl || !textEl) return;

        countEl.textContent = `x${qty}`;
        textEl.textContent = `${productName} • เพิ่ม ${qty} ไม้`;

        popup.classList.remove('hidden', 'scale-105');
        void popup.offsetWidth;
        popup.classList.add('scale-105');
        setTimeout(() => popup.classList.remove('scale-105'), 90);

        if (posRapidAddTimer) clearTimeout(posRapidAddTimer);
        posRapidAddTimer = setTimeout(() => {
            popup.classList.add('hidden');
        }, 1400);
    }

    // Fast 1-Click / Rapid-Tap Add from Card Image or (+) Button
    function quickAddFromCard(card) {
        if (!card) return;
        const id = parseInt(card.dataset.id, 10);
        const name = card.dataset.name;
        const price = parseFloat(card.dataset.price);
        const image = card.dataset.image;
        const supportsGrill = card.dataset.grill === '1';
        const supportsSoup = card.dataset.soup === '1';

        const chosenType = (posTopCookingType === 'soup' && supportsSoup)
            ? 'soup'
            : ((posTopCookingType === 'grill' && supportsGrill) ? 'grill' : (supportsGrill ? 'grill' : 'soup'));
        const chosenSoup = chosenType === 'soup' ? posTopSoupStyle : null;
        const chosenSpice = chosenType === 'grill' ? posTopSpiciness : 'medium';

        const existingIndex = cart.findIndex(i =>
            parseInt(i.product_id, 10) === id &&
            i.cooking_type === chosenType &&
            (i.soup_style || null) === chosenSoup &&
            i.spiciness_level === chosenSpice &&
            !i.special_instructions
        );

        let newQty = 1;
        if (existingIndex > -1) {
            cart[existingIndex].quantity += 1;
            newQty = cart[existingIndex].quantity;
        } else {
            cart.push({
                product_id: id,
                name: name,
                unit_price: price,
                quantity: 1,
                image: image,
                cooking_type: chosenType,
                grill_style: chosenType === 'grill' ? 'brush' : null,
                soup_style: chosenSoup,
                spiciness_level: chosenSpice,
                special_instructions: ''
            });
        }

        renderCart();
        if (newQty >= 2) {
            showRapidAddPopup(name, newQty);
        }
    }

    // Coupon Logic
    function claimAndApplyCoupon(code) {
        claimedCoupons[code] = true;
        activeCoupon = code;
        updateCouponButtonsUI();
        renderCart();
    }

    function removeCoupon() {
        activeCoupon = null;
        updateCouponButtonsUI();
        renderCart();
    }

    function updateCouponButtonsUI() {
        ['DISC10', 'DISC20'].forEach(code => {
            const txtEl = document.getElementById(`coupon-btn-text-${code}`);
            if (txtEl) {
                txtEl.innerText = (activeCoupon === code) ? 'ใช้คูปองแล้ว' : (claimedCoupons[code] ? 'เก็บแล้ว' : 'เก็บคูปอง');
            }
        });
    }

    function openCouponModal() {
        document.getElementById('coupon-modal').classList.remove('hidden');
    }

    function closeCouponModal() {
        document.getElementById('coupon-modal').classList.add('hidden');
    }

    function toggleFulfillmentMode() {
        fulfillmentMode = 'pos';
        const labelEl = document.getElementById('fulfillment-mode-label');
        if (labelEl) labelEl.innerText = 'บริการหน้าร้าน';
        renderCart();
    }

    function getCartPricing() {
        let totalSticks = 0;
        let subtotal = 0;
        cart.forEach(item => {
            totalSticks += item.quantity;
            subtotal += item.unit_price * item.quantity;
        });

        const shipping = (cart.length > 0 && fulfillmentMode === 'delivery') ? 30.00 : 0.00;
        let discount = 0.00;

        if (cart.length > 0 && activeCoupon) {
            if (activeCoupon === 'DISC10') {
                discount = 10.00;
            } else if (activeCoupon === 'DISC20') {
                discount = subtotal >= 300 ? 20.00 : 10.00;
            } else if (activeCoupon === 'FREESHIP') {
                discount = subtotal >= 400 ? shipping : 10.00;
            }
        }

        const total = Math.max(0, subtotal + shipping - discount);
        return { totalSticks, subtotal, shipping, discount, total };
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const emptyState = document.getElementById('cart-empty-state');
        const btnConfirm = document.getElementById('btn-pos-confirm');
        const btnDraft = document.getElementById('btn-pos-draft');

        const { totalSticks, subtotal, shipping, discount, total } = getCartPricing();

        document.getElementById('cart-items-count').innerText = cart.length;
        document.getElementById('cart-sticks-count').innerText = totalSticks;
        document.getElementById('summary-sticks').innerText = totalSticks;
        document.getElementById('summary-subtotal').innerText = `฿${subtotal.toFixed(2)}`;
        document.getElementById('summary-shipping').innerText = `฿${shipping.toFixed(2)}`;
        document.getElementById('summary-discount').innerText = `-฿${discount.toFixed(2)}`;
        document.getElementById('summary-total').innerText = `฿${total.toFixed(2)}`;

        const couponLabel = document.getElementById('cart-coupon-label');
        if (activeCoupon && discount > 0) {
            couponLabel.innerText = `ใช้โค้ดส่วนลด (${activeCoupon} ลด ฿${discount.toFixed(0)})`;
        } else {
            couponLabel.innerText = 'ใช้โค้ดส่วนลด';
        }

        if (cart.length === 0) {
            container.innerHTML = '';
            emptyState.classList.remove('hidden');
            if (btnConfirm) btnConfirm.disabled = true;
            if (btnDraft) btnDraft.disabled = true;
            return;
        }

        emptyState.classList.add('hidden');
        if (btnConfirm) btnConfirm.disabled = false;
        if (btnDraft) btnDraft.disabled = false;

        let html = '';
        cart.forEach((item, index) => {
            const lineSubtotal = item.unit_price * item.quantity;
            const imgUrl = item.image || "{{ asset('assets/menu/beef-raw.png') }}";

            html += `
                <div class="py-3 flex items-center justify-between gap-2.5 group">
                    <!-- Left: 1:1 Food Thumbnail + Name & Unit Price -->
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        <img src="${imgUrl}" alt="${item.name}"
                             class="w-[52px] h-[52px] rounded-[12px] object-cover border border-slate-200 shrink-0 bg-slate-900">
                        <div class="min-w-0 flex-1">
                            <h4 class="text-[13.5px] mala-font-bold text-slate-900 leading-snug truncate">${item.name}</h4>
                            <div class="text-[11.5px] mala-font-thin text-slate-500 mt-0.5">
                                ฿${item.unit_price.toFixed(0)} /ไม้
                            </div>
                        </div>
                    </div>

                    <!-- Right: Remove (x) on top-right + Stepper (- qty +) + Line Total -->
                    <div class="flex flex-col items-end gap-1 shrink-0">
                        <button type="button" onclick="removeFromCart(${index})"
                                class="text-slate-400 hover:text-[#E50914] text-xs leading-none px-1 cursor-pointer" title="ลบรายการ">&times;</button>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="changeCartQty(${index}, -1)"
                                        class="w-6 h-6 rounded-full bg-[#FFE4E6] hover:bg-[#FECDD3] text-[#E50914] text-sm mala-font-bold flex items-center justify-center transition-colors cursor-pointer">−</button>
                                <span class="text-[13.5px] mala-font-bold text-slate-900 w-4 text-center">${item.quantity}</span>
                                <button type="button" onclick="changeCartQty(${index}, 1)"
                                        class="w-6 h-6 rounded-full bg-[#E50914] hover:bg-[#C8102E] text-white text-sm mala-font-bold flex items-center justify-center transition-colors cursor-pointer">+</button>
                            </div>
                            <div class="text-[14px] mala-font-bold text-slate-900 min-w-[54px] text-right">
                                ฿${lineSubtotal.toFixed(2)}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function changeCartQty(index, delta) {
        if (!cart[index]) return;
        cart[index].quantity += delta;
        if (cart[index].quantity <= 0) {
            cart.splice(index, 1);
        }
        renderCart();
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function getCartTotal() {
        return getCartPricing().total;
    }

    // Store Favorite & Share
    function toggleFavoriteStore() {
        isFavoriteStore = !isFavoriteStore;
        const btn = document.getElementById('btn-favorite-store');
        btn.className = isFavoriteStore
            ? 'w-9 h-9 rounded-full bg-white text-[#E50914] shadow-md flex items-center justify-center hover:scale-105 active:scale-95 transition-all'
            : 'w-9 h-9 rounded-full bg-white text-slate-400 shadow-md flex items-center justify-center hover:scale-105 active:scale-95 transition-all';
    }

    function shareStoreLink() {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(window.location.href);
        }
        openStoreInfoModal('info');
    }

    function openStoreInfoModal() {
        document.getElementById('store-info-modal').classList.remove('hidden');
    }

    // Payment Modal Logic
    function openPaymentModal(method) {
        if (cart.length === 0) return;
        currentPaymentMethod = method;
        const total = getCartTotal();

        document.getElementById('pay-modal-amount').innerText = `฿${total.toFixed(2)}`;
        document.getElementById('pay-queue-tag').innerText = currentQueue;

        const cashSec = document.getElementById('cash-payment-section');
        const qrSec = document.getElementById('promptpay-payment-section');

        if (method === 'cash') {
            document.getElementById('pay-modal-icon').innerText = '💵';
            document.getElementById('pay-modal-title').innerText = 'ชำระเงินสด (เมื่อรับสินค้า)';
            cashSec.classList.remove('hidden');
            qrSec.classList.add('hidden');
            document.getElementById('cash-received-input').value = total;
            calculateChange();
        } else {
            document.getElementById('pay-modal-icon').innerText = '📱';
            document.getElementById('pay-modal-title').innerText = 'สแกนจ่าย PromptPay';
            qrSec.classList.remove('hidden');
            cashSec.classList.add('hidden');
            refreshPosPromptPayQrImage(total);
        }

        document.getElementById('payment-modal').classList.remove('hidden');
    }

    let STORE_PROMPTPAY_SETTINGS = @json($promptPaySettings ?? ['payload' => '', 'account_name' => '', 'account_no' => '']);

    function posCrc16Ccitt(payload) {
        let crc = 0xFFFF;
        for (let i = 0; i < payload.length; i++) {
            crc ^= (payload.charCodeAt(i) & 0xFF) << 8;
            for (let bit = 0; bit < 8; bit++) {
                crc = ((crc & 0x8000) !== 0) ? (((crc << 1) ^ 0x1021) & 0xFFFF) : ((crc << 1) & 0xFFFF);
            }
        }
        return crc.toString(16).toUpperCase().padStart(4, '0');
    }

    function posEmvTlv(tag, value) {
        return `${tag}${String(value.length).padStart(2, '0')}${value}`;
    }

    function buildPosLockedPromptPayPayload(basePayloadOrPhone, amount) {
        const raw = String(basePayloadOrPhone || '').trim();
        const numericAmount = Number(amount || 0);

        if (raw.startsWith('000201') && raw.length > 20) {
            const tags = [];
            let i = 0;
            while (i + 4 <= raw.length) {
                const tag = raw.slice(i, i + 2);
                const len = parseInt(raw.slice(i + 2, i + 4), 10);
                if (Number.isNaN(len) || i + 4 + len > raw.length) break;
                tags.push({ tag, val: raw.slice(i + 4, i + 4 + len) });
                i += 4 + len;
            }
            let out = '';
            let insertedAmount = false;
            for (const t of tags.filter(x => x.tag !== '54' && x.tag !== '63')) {
                const val = (t.tag === '01') ? (numericAmount > 0 ? '12' : '11') : t.val;
                out += posEmvTlv(t.tag, val);
                if (t.tag === '53' && numericAmount > 0) {
                    out += posEmvTlv('54', numericAmount.toFixed(2));
                    insertedAmount = true;
                }
            }
            if (!insertedAmount && numericAmount > 0) {
                out += posEmvTlv('54', numericAmount.toFixed(2));
            }
            out += '6304';
            return out + posCrc16Ccitt(out);
        }

        const clean = raw.replace(/[^0-9]/g, '') || '0891234567';
        const formattedTarget = clean.startsWith('0') ? ('0066' + clean.slice(1)) : clean;
        const merchantInfo = posEmvTlv('00', 'A000000677010111') + posEmvTlv('01', formattedTarget);
        let payload = posEmvTlv('00', '01') + posEmvTlv('01', numericAmount > 0 ? '12' : '11') + posEmvTlv('29', merchantInfo) + posEmvTlv('53', '764');
        if (numericAmount > 0) payload += posEmvTlv('54', numericAmount.toFixed(2));
        payload += posEmvTlv('58', 'TH') + '6304';
        return payload + posCrc16Ccitt(payload);
    }

    function refreshPosPromptPayQrImage(total) {
        const emv = buildPosLockedPromptPayPayload(STORE_PROMPTPAY_SETTINGS.payload, total);
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=280x280&margin=8&data=${encodeURIComponent(emv)}`;
        const img = document.getElementById('promptpay-qr-img');
        if (img) img.src = qrUrl;
    }

    function openPosQrSettingsModal() {
        document.getElementById('pos-qr-input-account-no').value = STORE_PROMPTPAY_SETTINGS.account_no || '';
        document.getElementById('pos-qr-input-account-name').value = STORE_PROMPTPAY_SETTINGS.account_name || '';
        document.getElementById('pos-qr-input-payload').value = STORE_PROMPTPAY_SETTINGS.payload || '';
        const statusEl = document.getElementById('pos-qr-decode-status');
        if (statusEl) statusEl.classList.add('hidden');
        document.getElementById('pos-qr-settings-modal')?.classList.remove('hidden');
    }

    function closePosQrSettingsModal() {
        document.getElementById('pos-qr-settings-modal')?.classList.add('hidden');
    }

    function decodeUploadedPromptPayQr(input) {
        const file = input.files?.[0];
        if (!file) return;
        const statusEl = document.getElementById('pos-qr-decode-status');

        if (typeof jsQR !== 'function') {
            if (statusEl) {
                statusEl.textContent = 'ไม่พบตัวอ่าน QR (jsQR)';
                statusEl.className = 'mt-1.5 text-[11px] font-bold text-rose-600';
            }
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
                const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const decoded = jsQR(imgData.data, imgData.width, imgData.height, { inversionAttempts: 'attemptBoth' });
                if (decoded && decoded.data) {
                    document.getElementById('pos-qr-input-payload').value = decoded.data;
                    if (statusEl) {
                        statusEl.textContent = '✅ ถอดรหัส QR สำเร็จ! กด "บันทึก QR พร้อมเพย์" เพื่อใช้งานทันที';
                        statusEl.className = 'mt-1.5 text-[11px] font-bold text-emerald-600';
                    }
                } else if (statusEl) {
                    statusEl.textContent = '⚠️ ไม่พบ QR Code ในภาพ กรุณาครอปเฉพาะส่วน QR แล้วลองใหม่อีกครั้ง';
                    statusEl.className = 'mt-1.5 text-[11px] font-bold text-amber-600';
                }
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    async function savePosPromptPayQr() {
        const payload = document.getElementById('pos-qr-input-payload')?.value.trim() || '';
        const accountNo = document.getElementById('pos-qr-input-account-no')?.value.trim() || '';
        const accountName = document.getElementById('pos-qr-input-account-name')?.value.trim() || '';

        if (!payload) {
            alert('กรุณาอัปโหลดภาพ QR หรือกรอกรหัส QR / เบอร์พร้อมเพย์');
            return;
        }

        const btn = document.getElementById('btn-save-pos-qr');
        if (btn) btn.disabled = true;

        try {
            const res = await fetch('{{ route("pos.settings.promptpay_qr") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    payload,
                    account_no: accountNo,
                    account_name: accountName,
                }),
            });
            const data = await res.json();
            if (res.ok && data.success) {
                STORE_PROMPTPAY_SETTINGS = data.settings;
                const accNoEl = document.getElementById('pos-qr-account-no');
                const accNameEl = document.getElementById('pos-qr-account-name');
                if (accNoEl) accNoEl.textContent = data.settings.account_no;
                if (accNameEl) accNameEl.textContent = data.settings.account_name;
                refreshPosPromptPayQrImage(getCartTotal());
                closePosQrSettingsModal();
                showPosSyncToast(data.message || 'บันทึก QR พร้อมเพย์ใหม่แล้ว');
            } else {
                alert(data.message || 'ไม่สามารถบันทึก QR ได้');
            }
        } catch (err) {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    function copyPosField(inputId) {
        const el = document.getElementById(inputId);
        if (!el) return;
        el.select();
        navigator.clipboard?.writeText(el.value);
        showPosSyncToast('คัดลอกแล้ว: ' + el.value);
    }

    async function testAndroidPaymentWebhook() {
        const url = document.getElementById('pos-webhook-url')?.value;
        const secret = document.getElementById('pos-webhook-secret')?.value;
        const statusEl = document.getElementById('pos-webhook-last-status');
        if (!url || !secret) return;

        const testAmount = getCartTotal() > 0 ? getCartTotal().toFixed(2) : '35.00';
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Webhook-Secret': secret,
                },
                body: JSON.stringify({
                    secret,
                    message: `KBank: เงินเข้าบัญชี 098-1-53225-2 จำนวน ${testAmount} บาท`,
                }),
            });
            const data = await res.json();
            if (statusEl) {
                statusEl.textContent = data.matched
                    ? `✅ ทดสอบสำเร็จ! จับคู่บิล ${data.order_no} (฿${data.amount})`
                    : `✅ เชื่อมต่อ Webhook สำเร็จ! (อ่านยอด ฿${data.parsed_amount ?? testAmount} ได้ปกติ)`;
            }
            showPosSyncToast(data.matched ? `ยืนยันบิล ${data.order_no} แล้ว` : 'ทดสอบ Webhook สำเร็จ');
        } catch (err) {
            if (statusEl) statusEl.textContent = '❌ ทดสอบเชื่อมต่อ Webhook ไม่สำเร็จ';
        }
    }

    // === POS CONFIRM CHECKOUT MODAL & DRAFT ORDER LOGIC ===
    let posConfirmExpectedTotal = 0;
    let posConfirmCurrentMode = 'transfer';
    let posPendingSubmission = null;

    function buildPosQrImageUrl(amount) {
        const emv = buildPosLockedPromptPayPayload(STORE_PROMPTPAY_SETTINGS.payload, amount);
        return `https://api.qrserver.com/v1/create-qr-code/?size=280x280&margin=8&data=${encodeURIComponent(emv)}`;
    }

    function openPosConfirmModal() {
        if (cart.length === 0) return;
        posConfirmExpectedTotal = getCartTotal();
        const customerName = document.getElementById('customer-name-input')?.value.trim() || 'ลูกค้าหน้าร้าน';

        const qEl = document.getElementById('pos-confirm-queue-tag');
        const cEl = document.getElementById('pos-confirm-customer');
        const tEl = document.getElementById('pos-confirm-total');
        const nEl = document.getElementById('pos-confirm-note');
        if (qEl) qEl.textContent = currentQueue;
        if (cEl) cEl.textContent = customerName;
        if (tEl) tEl.textContent = `฿${posConfirmExpectedTotal.toFixed(2)}`;
        if (nEl) nEl.value = '';

        setPosConfirmMode('transfer');

        const modal = document.getElementById('pos-confirm-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closePosConfirmModal() {
        const modal = document.getElementById('pos-confirm-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function populatePosTransferQrView() {
        const { totalSticks, subtotal, shipping, discount, total } = getCartPricing();
        const sticksEl = document.getElementById('pos-transfer-qr-sticks');
        const subEl = document.getElementById('pos-transfer-qr-subtotal');
        const discEl = document.getElementById('pos-transfer-qr-discount');
        const methodEl = document.getElementById('pos-transfer-qr-method');
        const shipEl = document.getElementById('pos-transfer-qr-shipping');
        const totEl = document.getElementById('pos-transfer-qr-total');
        const imgEl = document.getElementById('pos-transfer-qr-img');

        if (sticksEl) sticksEl.textContent = String(totalSticks);
        if (subEl) subEl.textContent = `฿${subtotal.toFixed(0)}`;
        if (discEl) discEl.textContent = `-฿${discount.toFixed(0)}`;
        if (methodEl) methodEl.textContent = fulfillmentMode === 'delivery' ? 'สั่งเดลิเวอรี่' : 'บริการหน้าร้าน';
        if (shipEl) shipEl.textContent = shipping > 0 ? `฿${shipping.toFixed(0)}` : 'ฟรี';
        if (totEl) totEl.textContent = `฿${total.toFixed(2)}`;
        if (imgEl) imgEl.src = buildPosQrImageUrl(total);
    }

    function setPosConfirmMode(mode) {
        posConfirmCurrentMode = mode;
        const cashInput = document.getElementById('pos-confirm-cash');
        const transferInput = document.getElementById('pos-confirm-transfer');
        const transferQrSection = document.getElementById('pos-confirm-transfer-qr-section');
        const splitSection = document.getElementById('pos-confirm-split-section');
        const cashTenderSection = document.getElementById('pos-confirm-cash-tender-section');
        const cashReceivedInput = document.getElementById('pos-confirm-cash-received');
        const defaultFooter = document.getElementById('pos-confirm-default-footer');
        const cancelBtn = document.getElementById('btn-pos-confirm-cancel');
        const submitBtn = document.getElementById('btn-pos-confirm-submit');

        const total = posConfirmExpectedTotal;

        if (mode === 'transfer') {
            if (transferQrSection) transferQrSection.classList.remove('hidden');
            if (splitSection) splitSection.classList.add('hidden');
            if (cashTenderSection) cashTenderSection.classList.add('hidden');
            if (defaultFooter) defaultFooter.classList.add('hidden');
            populatePosTransferQrView();
        } else if (mode === 'cash') {
            if (transferQrSection) transferQrSection.classList.add('hidden');
            if (splitSection) splitSection.classList.add('hidden');
            if (cashTenderSection) cashTenderSection.classList.remove('hidden');
            if (defaultFooter) defaultFooter.classList.remove('hidden');
            if (cashReceivedInput) cashReceivedInput.value = Number(total.toFixed(2)).toString();
            if (cancelBtn) cancelBtn.textContent = 'ยกเลิก';
            if (submitBtn) submitBtn.textContent = '✅ ยืนยันชำระเงิน & พิมพ์ใบเสร็จ';
            recalcPosConfirmCashChange();
        } else {
            if (transferQrSection) transferQrSection.classList.add('hidden');
            if (splitSection) splitSection.classList.remove('hidden');
            if (cashTenderSection) cashTenderSection.classList.add('hidden');
            if (defaultFooter) defaultFooter.classList.remove('hidden');
            if (cancelBtn) cancelBtn.textContent = 'กลับไปหน้าทำรายการ';
            if (submitBtn) submitBtn.textContent = 'ยืนยันถูกต้องแล้ว';
            if (cashInput && transferInput) {
                const halfCash = Math.floor(total / 2);
                cashInput.value = halfCash.toFixed(2);
                transferInput.value = Math.max(0, total - halfCash).toFixed(2);
            }
            recalcPosConfirmSummary();
        }

        document.querySelectorAll('.pos-confirm-mode-btn').forEach((btn) => {
            const active = btn.dataset.mode === mode;
            btn.className = active
                ? 'pos-confirm-mode-btn py-2 px-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold border border-slate-900 transition cursor-pointer whitespace-nowrap flex items-center justify-center gap-1.5'
                : 'pos-confirm-mode-btn py-2 px-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold border border-slate-200 transition cursor-pointer whitespace-nowrap flex items-center justify-center gap-1.5';
        });
    }

    function setPosConfirmCashTender(amount) {
        const input = document.getElementById('pos-confirm-cash-received');
        if (!input) return;
        input.value = amount === 'exact' ? Number(posConfirmExpectedTotal.toFixed(2)).toString() : String(amount);
        recalcPosConfirmCashChange();
    }

    function recalcPosConfirmCashChange() {
        const received = parseFloat(document.getElementById('pos-confirm-cash-received')?.value || '0') || 0;
        const change = Math.max(0, received - posConfirmExpectedTotal);
        const displayEl = document.getElementById('pos-confirm-change-display');
        if (displayEl) {
            displayEl.textContent = `฿${change.toFixed(2)}`;
        }
    }

    function onPosConfirmInput(changedField) {
        const cashInput = document.getElementById('pos-confirm-cash');
        const transferInput = document.getElementById('pos-confirm-transfer');
        if (!cashInput || !transferInput) return;

        const total = posConfirmExpectedTotal;
        if (changedField === 'cash') {
            const c = Math.max(0, parseFloat(cashInput.value || '0'));
            if (c <= total) {
                transferInput.value = Math.max(0, total - c).toFixed(2);
            }
        }
        recalcPosConfirmSummary();
    }

    function recalcPosConfirmSummary() {
        const cashVal = Math.max(0, parseFloat(document.getElementById('pos-confirm-cash')?.value || '0'));
        const transferVal = Math.max(0, parseFloat(document.getElementById('pos-confirm-transfer')?.value || '0'));
        const sum = cashVal + transferVal;
        const diff = sum - posConfirmExpectedTotal;

        const sumEl = document.getElementById('pos-confirm-received-sum');
        const statusEl = document.getElementById('pos-confirm-match-status');
        if (sumEl) sumEl.textContent = `฿${sum.toFixed(2)}`;
        if (statusEl) {
            if (Math.abs(diff) < 0.01) {
                statusEl.textContent = 'ยอดตรงตามบิลครบถ้วน ✓';
                statusEl.className = 'text-[11px] font-bold text-emerald-700';
            } else if (diff > 0) {
                statusEl.textContent = `เงินทอน / เกินบิล +฿${diff.toFixed(2)}`;
                statusEl.className = 'text-[11px] font-bold text-amber-700';
            } else {
                statusEl.textContent = `ยังขาดอีก ฿${Math.abs(diff).toFixed(2)}`;
                statusEl.className = 'text-[11px] font-bold text-rose-600';
            }
        }
    }

    function buildPosCheckoutItemsPayload() {
        return cart.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            cooking_type: item.cooking_type || 'grill',
            grill_style: item.grill_style || 'brush',
            soup_style: item.soup_style || null,
            spiciness_level: item.spiciness_level || 'medium',
            special_instructions: item.special_instructions || ''
        }));
    }

    function openSplitQrModal(cashVal, transferVal) {
        const { totalSticks, total } = getCartPricing();
        const qEl = document.getElementById('pos-split-qr-queue');
        const sticksEl = document.getElementById('pos-split-qr-sticks');
        const billEl = document.getElementById('pos-split-qr-bill-total');
        const cashEl = document.getElementById('pos-split-qr-cash-paid');
        const transferEl = document.getElementById('pos-split-qr-transfer-total');
        const imgEl = document.getElementById('pos-split-qr-img');

        if (qEl) qEl.textContent = currentQueue;
        if (sticksEl) sticksEl.textContent = String(totalSticks);
        if (billEl) billEl.textContent = `฿${total.toFixed(2)}`;
        if (cashEl) cashEl.textContent = `-฿${cashVal.toFixed(2)}`;
        if (transferEl) transferEl.textContent = `฿${transferVal.toFixed(2)}`;
        if (imgEl) imgEl.src = buildPosQrImageUrl(transferVal);

        closePosConfirmModal();
        const splitModal = document.getElementById('pos-split-qr-modal');
        if (splitModal) {
            splitModal.classList.remove('hidden');
            splitModal.classList.add('flex');
        }
    }

    function closeSplitQrModal() {
        const splitModal = document.getElementById('pos-split-qr-modal');
        if (splitModal) {
            splitModal.classList.add('hidden');
            splitModal.classList.remove('flex');
        }
    }

    function backFromSplitQrToConfirmModal() {
        closeSplitQrModal();
        const confirmModal = document.getElementById('pos-confirm-modal');
        if (confirmModal) {
            confirmModal.classList.remove('hidden');
            confirmModal.classList.add('flex');
        }
    }

    async function submitPosConfirmedOrder() {
        if (cart.length === 0) return;

        let cashVal = 0;
        let transferVal = 0;
        let payMethod = 'cash';

        if (posConfirmCurrentMode === 'cash') {
            cashVal = Math.max(0, parseFloat(document.getElementById('pos-confirm-cash-received')?.value || '0'));
            if (cashVal + 0.001 < posConfirmExpectedTotal) {
                alert('จำนวนเงินสดที่รับมาไม่เพียงพอกับยอดชำระ');
                return;
            }
            payMethod = 'cash';
        } else if (posConfirmCurrentMode === 'transfer') {
            cashVal = 0;
            transferVal = posConfirmExpectedTotal;
            payMethod = 'promptpay';
        } else {
            cashVal = Math.max(0, parseFloat(document.getElementById('pos-confirm-cash')?.value || '0'));
            transferVal = Math.max(0, parseFloat(document.getElementById('pos-confirm-transfer')?.value || '0'));
            if ((cashVal + transferVal) + 0.001 < posConfirmExpectedTotal) {
                alert('ยอดรับรวมยังไม่ครบตามยอดบิล');
                return;
            }
            payMethod = transferVal > 0 ? 'promptpay' : 'cash';
        }

        const customerName = document.getElementById('customer-name-input')?.value.trim() || 'ลูกค้าหน้าร้าน';
        const note = posConfirmCurrentMode === 'split'
            ? (document.getElementById('pos-confirm-note')?.value.trim() || '')
            : '';

        posPendingSubmission = {
            payment_method: payMethod,
            cash_amount: cashVal,
            transfer_amount: transferVal,
            customer_name: customerName,
            notes: note,
        };

        if (posConfirmCurrentMode === 'split' && transferVal > 0) {
            openSplitQrModal(cashVal, transferVal);
            return;
        }

        await executePosConfirmedOrderSubmission();
    }

    let posVerifyPollTimer = null;
    let posVerifyingOrder = null;

    function openPosVerifyingModal(order, expectedAmount) {
        posVerifyingOrder = order;
        document.getElementById('pos-verify-order-no').textContent = order.order_no;
        document.getElementById('pos-verify-queue-no').textContent = order.queue_number;
        document.getElementById('pos-verify-expected-amount').textContent = `฿${Number(expectedAmount).toFixed(2)}`;
        document.getElementById('pos-verify-state-waiting').classList.remove('hidden');
        document.getElementById('pos-verify-state-success').classList.add('hidden');
        document.getElementById('pos-payment-verifying-modal')?.classList.remove('hidden');

        if (posVerifyPollTimer) clearInterval(posVerifyPollTimer);
        posVerifyPollTimer = setInterval(() => pollPosWebhookVerification(expectedAmount), 2000);
    }

    function reopenPosQrFromVerifying() {
        if (posVerifyPollTimer) {
            clearInterval(posVerifyPollTimer);
            posVerifyPollTimer = null;
        }
        document.getElementById('pos-payment-verifying-modal')?.classList.add('hidden');
        if (posConfirmCurrentMode === 'split' && posPendingSubmission && posPendingSubmission.transfer_amount > 0) {
            const splitModal = document.getElementById('pos-split-qr-modal');
            if (splitModal) {
                splitModal.classList.remove('hidden');
                splitModal.classList.add('flex');
            }
        } else {
            const confirmModal = document.getElementById('pos-confirm-modal');
            if (confirmModal) {
                confirmModal.classList.remove('hidden');
                confirmModal.classList.add('flex');
            }
        }
    }

    async function pollPosWebhookVerification(expectedAmount) {
        if (!posVerifyingOrder) return;
        try {
            const res = await fetch(`/order/status/${encodeURIComponent(posVerifyingOrder.order_no)}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.payment_status === 'paid') {
                clearInterval(posVerifyPollTimer);
                posVerifyPollTimer = null;
                document.getElementById('pos-verified-order-no').textContent = posVerifyingOrder.order_no;
                document.getElementById('pos-verified-paid-amount').textContent = Number(expectedAmount).toFixed(2);
                document.getElementById('pos-verify-state-waiting').classList.add('hidden');
                document.getElementById('pos-verify-state-success').classList.remove('hidden');

                const completedOrder = posVerifyingOrder;
                setTimeout(() => {
                    document.getElementById('pos-payment-verifying-modal')?.classList.add('hidden');
                    posPendingSubmission = null;
                    posVerifyingOrder = null;
                    showThermalReceipt(completedOrder);
                    cart = [];
                    renderCart();
                    document.getElementById('customer-name-input').value = '';
                    const num = parseInt(completedOrder.queue_number.replace(/\D/g, ''), 10) + 1;
                    currentQueue = 'A' + String(num).padStart(2, '0');
                    document.getElementById('current-queue-tag').innerText = currentQueue;
                }, 1200);
            }
        } catch (err) {}
    }

    async function executePosConfirmedOrderSubmission() {
        if (cart.length === 0 || !posPendingSubmission) return;

        const isTransferWebhook = posPendingSubmission.payment_method === 'promptpay' && posPendingSubmission.transfer_amount > 0;
        const btnDefault = document.getElementById('btn-pos-confirm-submit');
        const btnTransfer = document.getElementById('btn-pos-transfer-qr-submit');
        const btnSplitQr = document.getElementById('btn-pos-split-qr-submit');
        const activeBtn = posConfirmCurrentMode === 'transfer'
            ? btnTransfer
            : (posConfirmCurrentMode === 'split' && posPendingSubmission.transfer_amount > 0 ? btnSplitQr : btnDefault);

        const originalText = activeBtn ? activeBtn.textContent : '';
        if (activeBtn) {
            activeBtn.disabled = true;
            activeBtn.textContent = 'กำลังบันทึก...';
        }

        try {
            const response = await fetch("{{ route('pos.order.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    items: buildPosCheckoutItemsPayload(),
                    payment_method: posPendingSubmission.payment_method,
                    cash_amount: posPendingSubmission.cash_amount,
                    transfer_amount: posPendingSubmission.transfer_amount,
                    is_draft: false,
                    awaiting_webhook: isTransferWebhook,
                    customer_name: posPendingSubmission.customer_name,
                    coupon_code: activeCoupon,
                    fulfillment_mode: fulfillmentMode,
                    notes: posPendingSubmission.notes
                })
            });

            const data = await response.json();
            if (data.success) {
                closeSplitQrModal();
                closePosConfirmModal();
                if (isTransferWebhook) {
                    openPosVerifyingModal(data.order, posPendingSubmission.transfer_amount);
                } else {
                    posPendingSubmission = null;
                    showThermalReceipt(data.order);
                    cart = [];
                    renderCart();
                    document.getElementById('customer-name-input').value = '';
                    const num = parseInt(data.order.queue_number.replace(/\D/g, ''), 10) + 1;
                    currentQueue = 'A' + String(num).padStart(2, '0');
                    document.getElementById('current-queue-tag').innerText = currentQueue;
                }
            } else {
                alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกออเดอร์ได้'));
            }
        } catch (err) {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเครือข่าย');
        } finally {
            if (activeBtn) {
                activeBtn.disabled = false;
                activeBtn.textContent = originalText;
            }
        }
    }

    async function submitPosDraftOrder() {
        if (cart.length === 0) return;
        const btn = document.getElementById('btn-pos-draft');
        if (btn) {
            btn.disabled = true;
        }
        const customerName = document.getElementById('customer-name-input')?.value.trim() || 'ลูกค้าหน้าร้าน';

        try {
            const response = await fetch("{{ route('pos.order.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    items: buildPosCheckoutItemsPayload(),
                    payment_method: 'cash',
                    is_draft: true,
                    customer_name: customerName,
                    coupon_code: activeCoupon,
                    fulfillment_mode: fulfillmentMode,
                    notes: ''
                })
            });

            const data = await response.json();
            if (data.success) {
                cart = [];
                renderCart();
                window.location.href = "{{ route('pos.kitchen') }}";
            } else {
                alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกร่างได้'));
            }
        } catch (err) {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเครือข่าย');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    function closePaymentModal() {
        document.getElementById('payment-modal').classList.add('hidden');
    }

    function calculateChange() {
        const total = getCartTotal();
        const received = parseFloat(document.getElementById('cash-received-input').value) || 0;
        const change = Math.max(0, received - total);
        document.getElementById('change-display').innerText = `฿${change.toFixed(2)}`;
    }

    function setCashTender(amount) {
        const total = getCartTotal();
        document.getElementById('cash-received-input').value = (amount === 'exact') ? total : amount;
        calculateChange();
    }

    // Submit Order to Backend
    async function submitOrder() {
        const total = getCartTotal();
        const customerName = document.getElementById('customer-name-input').value.trim() || 'โต๊ะ A02 • เดลิเวอรี่';
        const received = parseFloat(document.getElementById('cash-received-input').value) || total;

        if (currentPaymentMethod === 'cash' && received < total) {
            alert('จำนวนเงินสดที่รับมาไม่เพียงพอกับยอดชำระ');
            return;
        }

        const btn = document.getElementById('btn-confirm-submit');
        btn.disabled = true;
        btn.innerHTML = '⏳ กำลังบันทึกออเดอร์...';

        const payload = {
            items: cart.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity,
                cooking_type: item.cooking_type || 'grill',
                grill_style: item.grill_style || 'brush',
                soup_style: item.soup_style || null,
                spiciness_level: item.spiciness_level || 'medium',
                special_instructions: item.special_instructions || ''
            })),
            payment_method: currentPaymentMethod,
            cash_received: currentPaymentMethod === 'cash' ? received : null,
            customer_name: customerName,
            coupon_code: activeCoupon,
            fulfillment_mode: fulfillmentMode,
            notes: ''
        };

        try {
            const response = await fetch("{{ route('pos.order.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                closePaymentModal();
                showThermalReceipt(data.order);
                cart = [];
                renderCart();
                document.getElementById('customer-name-input').value = '';
                const num = parseInt(data.order.queue_number.replace(/\D/g, '')) + 1;
                currentQueue = 'A' + String(num).padStart(2, '0');
                document.getElementById('current-queue-tag').innerText = currentQueue;
            } else {
                alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกออเดอร์ได้'));
            }
        } catch (err) {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเครือข่าย');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '✅ ยืนยันชำระเงิน & พิมพ์ใบเสร็จ';
        }
    }

    function showThermalReceipt(order) {
        document.getElementById('receipt-queue-number').innerText = order.queue_number;
        document.getElementById('receipt-datetime').innerText = new Date(order.created_at).toLocaleString('th-TH');
        document.getElementById('receipt-order-no').innerText = order.order_no;
        document.getElementById('receipt-total-amount').innerText = `฿${parseFloat(order.total_amount).toFixed(2)}`;
        document.getElementById('receipt-pay-method').innerText = order.payment_method === 'cash' ? 'เงินสด' : 'เงินโอน';

        const listContainer = document.getElementById('receipt-items-list');
        let html = '';
        order.items.forEach(item => {
            html += `
                <div class="flex justify-between items-start">
                    <div>
                        <div class="mala-font-bold">${item.product_name} x ${item.quantity_sticks} ไม้</div>
                    </div>
                    <span class="mala-font-bold">฿${parseFloat(item.subtotal).toFixed(2)}</span>
                </div>
            `;
        });
        listContainer.innerHTML = html;
        document.getElementById('receipt-modal').classList.remove('hidden');
    }

    function closeReceiptModal() {
        document.getElementById('receipt-modal').classList.add('hidden');
    }

    // === POS CART PANEL SIZE ADJUSTER (10 LEVELS: 1..10, STANDARD DEFAULT 5) ===
    const POS_CART_ZOOM_MAP = {
        1: 0.76, 2: 0.82, 3: 0.88, 4: 0.94, 5: 1.0,
        6: 1.05, 7: 1.10, 8: 1.15, 9: 1.20, 10: 1.25
    };
    const POS_CART_DESC_MAP = {
        1: 'เล็กที่สุด',
        2: 'เล็กมาก',
        3: 'เล็ก',
        4: 'ค่อนข้างเล็ก',
        5: 'ขนาดกลาง (เริ่มต้น)',
        6: 'ค่อนข้างใหญ่',
        7: 'ใหญ่',
        8: 'ใหญ่มาก',
        9: 'ใหญ่พิเศษ',
        10: 'ใหญ่ที่สุด'
    };
    const POS_GLOBAL_CART_LEVEL = Number(@json($globalCartLevel ?? 5));
    const POS_GLOBAL_CART_VERSION = String(@json($globalCartVersion ?? '0'));
    let posCartCurrentLevel = 5;
    let posCartSavedLevel = 5;

    function applyPosCartSizeLevel(level) {
        const lv = Math.max(1, Math.min(10, Number(level) || 5));
        posCartCurrentLevel = lv;
        const panel = document.getElementById('pos-cart-panel');
        if (panel) {
            panel.style.zoom = String(POS_CART_ZOOM_MAP[lv] || 1);
            panel.style.maxWidth = '100%';
        }
        const levelText = document.getElementById('pos-cart-size-level-text');
        const descText = document.getElementById('pos-cart-size-desc-text');
        const minusBtn = document.getElementById('btn-cart-size-minus');
        const plusBtn = document.getElementById('btn-cart-size-plus');
        if (levelText) levelText.textContent = `ระดับ ${lv} / 10`;
        if (descText) descText.textContent = POS_CART_DESC_MAP[lv] || '';
        if (minusBtn) minusBtn.disabled = lv <= 1;
        if (plusBtn) plusBtn.disabled = lv >= 10;
    }

    function initPosCartSize() {
        const seenGlobalVer = localStorage.getItem('pos_cart_panel_global_seen_v') || '0';
        const localLevel = localStorage.getItem('pos_cart_panel_level');
        let startLevel = POS_GLOBAL_CART_LEVEL;
        if (POS_GLOBAL_CART_VERSION !== '0' && POS_GLOBAL_CART_VERSION !== seenGlobalVer) {
            localStorage.setItem('pos_cart_panel_global_seen_v', POS_GLOBAL_CART_VERSION);
            localStorage.removeItem('pos_cart_panel_level');
            startLevel = POS_GLOBAL_CART_LEVEL;
        } else if (localLevel) {
            startLevel = Number(localLevel);
        }
        posCartSavedLevel = Math.max(1, Math.min(10, startLevel || 5));
        applyPosCartSizeLevel(posCartSavedLevel);
    }

    function openPosCartSizeModal() {
        applyPosCartSizeLevel(posCartSavedLevel);
        const modal = document.getElementById('pos-cart-size-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closePosCartSizeModal(revert = false) {
        if (revert) {
            applyPosCartSizeLevel(posCartSavedLevel);
        }
        const modal = document.getElementById('pos-cart-size-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function stepPosCartSize(delta) {
        applyPosCartSizeLevel(posCartCurrentLevel + delta);
    }

    function savePosCartSizeLocal() {
        posCartSavedLevel = posCartCurrentLevel;
        localStorage.setItem('pos_cart_panel_level', String(posCartSavedLevel));
        localStorage.setItem('pos_cart_panel_global_seen_v', POS_GLOBAL_CART_VERSION);
        closePosCartSizeModal(false);
        showPosSyncToast(`บันทึกขนาดหน้าต่างระดับ ${posCartSavedLevel}/5 เฉพาะเครื่องนี้แล้ว`);
    }

    async function savePosCartSizeGlobal() {
        const btn = document.getElementById('btn-save-cart-size-global');
        if (btn) btn.disabled = true;
        try {
            const res = await fetch("{{ url('/pos/cart-panel-size') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({ level: posCartCurrentLevel }),
            });
            const data = await res.json();
            if (res.ok && data.success) {
                posCartSavedLevel = data.level;
                localStorage.setItem('pos_cart_panel_global_seen_v', String(data.version));
                localStorage.removeItem('pos_cart_panel_level');
                closePosCartSizeModal(false);
                showPosSyncToast(data.message || 'บันทึกขนาดหน้าต่างสำหรับทุกคนแล้ว');
            } else {
                alert(data.message || 'ไม่สามารถบันทึกได้');
            }
        } catch (err) {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', initPosCartSize);

    function updatePosCardStockBadges(card, stockQty) {
        if (!card) return;
        const qty = Math.max(0, Number(stockQty || 0));
        card.setAttribute('data-stock', String(qty));

        const topStockTextEl = card.querySelector('[data-role="top-stock-text"]');
        if (topStockTextEl) {
            topStockTextEl.textContent = qty > 0 ? `เหลือ ${qty} ไม้` : 'หมดชั่วคราว';
        }

        const stockBadgeEl = card.querySelector('[data-role="stock-badge"]');
        if (stockBadgeEl) {
            stockBadgeEl.textContent = qty > 0 ? `เหลือ ${qty} ไม้` : 'หมดชั่วคราว';
            stockBadgeEl.className = 'inline-flex items-center px-2 py-0.5 rounded-full border text-[10.5px] mala-font-mid-bold leading-tight whitespace-nowrap shrink-0 ' + (
                qty <= 0
                    ? 'bg-rose-50 text-rose-700 border-rose-200'
                    : (qty <= 5
                        ? 'bg-amber-50 text-amber-800 border-amber-200'
                        : 'bg-emerald-50 text-emerald-700 border-emerald-200')
            );
        }
    }

    // 🔄 Sync helper functions connecting POS with Price Tracker
    function syncPosItemFromPriceTracker(rawMaterialId, newName, newImageUrl, newCategorySlug, newStockSticks, newSellingPrice) {
        if (!rawMaterialId) return;
        const cards = document.querySelectorAll(`.product-card[data-raw-id="${rawMaterialId}"]`);
        cards.forEach(card => {
            if (newName) {
                const titleEl = card.querySelector('[data-role="product-name"]') || card.querySelector('h3');
                if (titleEl) titleEl.textContent = newName;
                card.setAttribute('data-name', newName);
            }
            if (newImageUrl) {
                const imgEl = card.querySelector('[data-role="product-img"]') || card.querySelector('img');
                if (imgEl) imgEl.src = newImageUrl;
                card.setAttribute('data-image', newImageUrl);
            }
            if (newCategorySlug) {
                card.setAttribute('data-category', newCategorySlug);
            }
            if (newStockSticks !== undefined && newStockSticks !== null) {
                updatePosCardStockBadges(card, newStockSticks);
            }
            if (newSellingPrice !== undefined && newSellingPrice !== null && Number(newSellingPrice) > 0) {
                const priceNum = Number(newSellingPrice);
                card.setAttribute('data-price', String(priceNum));
                const priceEl = card.querySelector('[data-role="product-price"]');
                if (priceEl) priceEl.textContent = `฿${priceNum.toFixed(0)}`;
            }
        });

        if (currentItem && currentItem.raw_material_id == rawMaterialId) {
            if (newName) {
                const modalTitle = document.getElementById('modal-item-name');
                if (modalTitle) modalTitle.textContent = newName;
                currentItem.name = newName;
            }
            if (newImageUrl) {
                const modalImg = document.getElementById('modal-item-image');
                if (modalImg) modalImg.src = newImageUrl;
                currentItem.image = newImageUrl;
            }
        }

        if (newCategorySlug) {
            const activePill = document.querySelector('.cat-pill.active');
            if (activePill) {
                filterCategory(activePill.getAttribute('data-cat') || 'all', activePill);
            }
        }

        showPosSyncToast(`ซิงค์ข้อมูล "${newName || 'วัตถุดิบ'}" จากหน้าตรวจจับราคาแล้ว`);
    }

    async function refreshPosMenuLive() {
        try {
            const res = await fetch('/pos?json=1', {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (data && data.success && Array.isArray(data.products)) {
                let categoryChanged = false;
                data.products.forEach(p => {
                    const card = document.querySelector(`.product-card[data-id="${p.id}"]`);
                    if (card) {
                        const titleEl = card.querySelector('[data-role="product-name"]') || card.querySelector('h3');
                        if (titleEl && p.name) titleEl.textContent = p.name;
                        card.setAttribute('data-name', p.name);

                        const imgEl = card.querySelector('[data-role="product-img"]') || card.querySelector('img');
                        if (imgEl && p.image_url) imgEl.src = p.image_url;
                        card.setAttribute('data-image', p.image_url);

                        updatePosCardStockBadges(card, p.stock_sticks);
                        if (p.price_per_stick !== undefined && p.price_per_stick !== null) {
                            const priceNum = Number(p.price_per_stick);
                            card.setAttribute('data-price', String(priceNum));
                            const priceEl = card.querySelector('[data-role="product-price"]');
                            if (priceEl) priceEl.textContent = `฿${priceNum.toFixed(0)}`;
                        }
                        if (p.category_slug && card.getAttribute('data-category') !== p.category_slug) {
                            card.setAttribute('data-category', p.category_slug);
                            categoryChanged = true;
                        }
                    }
                });
                if (categoryChanged) {
                    const activePill = document.querySelector('.cat-pill.active');
                    if (activePill) {
                        filterCategory(activePill.getAttribute('data-cat') || 'all', activePill);
                    }
                }
            }
            if (data && data.success && Array.isArray(data.categories)) {
                syncPosCategoryPills(data.categories);
            }
        } catch (e) {}
    }

    // 🔄 Keep category pills (name / icon / visibility) in sync with the stock page (สต๊อกสินค้า)
    function syncPosCategoryPills(categories) {
        const activeSlugs = new Set(categories.map(c => c.slug));
        categories.forEach(c => {
            const pill = document.querySelector(`.cat-pill[data-cat="${c.slug}"]`);
            if (!pill) return;
            const label = pill.querySelector('.cat-label');
            if (label && c.name && label.textContent !== c.name) label.textContent = c.name;
            const img = pill.querySelector('img');
            if (img && c.icon && img.getAttribute('src') !== c.icon) img.src = c.icon;
            if (img && c.name) img.alt = c.name;
            pill.style.display = '';
        });
        if (activeSlugs.size > 0) {
            document.querySelectorAll('.cat-pill[data-cat]').forEach(pill => {
                const slug = pill.getAttribute('data-cat');
                if (slug && slug !== 'all' && !activeSlugs.has(slug)) pill.style.display = 'none';
            });
        }
    }

    function applyCategorySyncMessage(data) {
        if (!data || !data.action) return;
        const oldSlug = data.old_slug || data.slug;
        const pill = document.querySelector(`.cat-pill[data-cat="${oldSlug}"]`);
        if (data.action === 'category_deleted' || (data.action === 'category_updated' && data.is_active === false)) {
            if (pill) pill.style.display = 'none';
            return;
        }
        if (data.action === 'category_updated' && pill) {
            if (data.slug && data.slug !== oldSlug) {
                pill.setAttribute('data-cat', data.slug);
                pill.setAttribute('onclick', `filterCategory('${data.slug}', this)`);
                document.querySelectorAll(`.product-card[data-category="${oldSlug}"]`).forEach(card => card.setAttribute('data-category', data.slug));
            }
            const label = pill.querySelector('.cat-label');
            if (label && data.name) label.textContent = data.name;
            const img = pill.querySelector('img');
            if (img && data.icon) img.src = data.icon;
            pill.style.display = '';
            showPosSyncToast(`ซิงค์หมวดหมู่ "${data.name || ''}" จากหน้าสต๊อกสินค้าแล้ว`);
        }
        refreshPosMenuLive();
    }

    function showPosSyncToast(msg) {
        const toast = document.getElementById('pos-sync-toast');
        const textEl = document.getElementById('pos-sync-toast-text');
        if (!toast || !textEl) return;
        textEl.textContent = msg;
        toast.classList.remove('hidden');
        toast.classList.add('flex');
        clearTimeout(window.__posSyncToastTimer);
        window.__posSyncToastTimer = setTimeout(() => {
            toast.classList.add('hidden');
            toast.classList.remove('flex');
        }, 3200);
    }

    // =========================================================================
    // ONLINE SERVICE SWITCH (เปิดบริการออนไลน์)
    // =========================================================================
    let isPosOnlineLive = {{ $isOnlineLive ? 'true' : 'false' }};
    let isTogglingOnlineMode = false;

    async function togglePosOnlineServiceSwitch() {
        if (isTogglingOnlineMode) return;
        isTogglingOnlineMode = true;

        const targetMode = isPosOnlineLive ? 'preview' : 'live';
        try {
            const res = await fetch('{{ route("pos.toggle_online") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ mode: targetMode })
            });
            const data = await res.json();
            if (data && data.success) {
                isPosOnlineLive = (data.mode === 'live');
                renderPosOnlineSwitchUI();
                if (typeof showToast === 'function') {
                    showToast(data.message);
                }
            } else {
                if (typeof showToast === 'function') showToast('ไม่สามารถเปลี่ยนสถานะบริการออนไลน์ได้');
            }
        } catch (err) {
            if (typeof showToast === 'function') showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        } finally {
            isTogglingOnlineMode = false;
        }
    }

    function renderPosOnlineSwitchUI() {
        const card = document.getElementById('pos-online-switch-card');
        const btn = document.getElementById('pos-online-switch-btn');
        const knob = document.getElementById('pos-online-switch-knob');
        const dot = document.getElementById('pos-online-switch-dot');
        const badge = document.getElementById('pos-online-status-badge');
        const badgeDot = document.getElementById('pos-online-badge-dot');
        const badgeText = document.getElementById('pos-online-badge-text');
        const sub = document.getElementById('pos-online-status-sub');

        if (card) {
            card.className = `inline-flex items-center gap-2 px-2.5 py-1.5 rounded-[14px] border shadow-lg backdrop-blur-xs transition-all duration-200 max-w-full ${isPosOnlineLive ? 'bg-emerald-50/95 border-emerald-400 shadow-emerald-950/20' : 'bg-white/95 border-white/90 shadow-black/25'}`;
        }
        if (btn) {
            btn.setAttribute('aria-checked', isPosOnlineLive ? 'true' : 'false');
            btn.className = `relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-emerald-500 ${isPosOnlineLive ? 'bg-emerald-500' : 'bg-slate-300'}`;
        }
        if (knob) {
            knob.className = `pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out flex items-center justify-center ${isPosOnlineLive ? 'translate-x-5' : 'translate-x-0'}`;
        }
        if (dot) {
            dot.className = `w-1.5 h-1.5 rounded-full ${isPosOnlineLive ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'}`;
        }
        if (badge) {
            badge.className = `inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9px] font-extrabold whitespace-nowrap ${isPosOnlineLive ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700'}`;
        }
        if (badgeDot) {
            badgeDot.className = `w-1.5 h-1.5 rounded-full ${isPosOnlineLive ? 'bg-white animate-pulse' : 'bg-slate-500'}`;
        }
        if (badgeText) {
            badgeText.textContent = isPosOnlineLive ? 'กำลังเปิดรับออเดอร์ (LIVE)' : 'ปิดชั่วคราว (พรีวิว)';
        }
        if (sub) {
            sub.className = `text-[8.5px] sm:text-[9px] font-medium leading-tight truncate ${isPosOnlineLive ? 'text-emerald-700' : 'text-slate-500'}`;
            sub.textContent = isPosOnlineLive ? 'ลูกค้าสามารถกดสั่งอาหารออนไลน์ได้ทันที' : 'ลูกค้าเข้าดูเมนูได้ แต่ยังไม่เปิดรับออเดอร์จริง';
        }
    }

    // =========================================================================
    // POS 3-BAR SETTINGS MENU & 1:7 HORIZONTAL HERO COVER EDITOR
    // =========================================================================
    let posHeroBanner = {
        image: @json($posHeroBannerImage ?? ''),
        x: {{ $posHeroBannerPosition['x'] ?? 50 }},
        y: {{ $posHeroBannerPosition['y'] ?? 50 }},
        scale: {{ $posHeroBannerPosition['scale'] ?? 1 }},
        history: @json($posHeroBannerHistory ?? [])
    };

    function togglePosMasterSettingsMenu(event) {
        if (event) event.stopPropagation();
        const menu = document.getElementById('pos-master-settings-menu');
        const btn = document.getElementById('btn-pos-master-settings');
        if (!menu || !btn) return;
        const isHidden = menu.classList.contains('hidden');
        if (isHidden) {
            const rect = btn.getBoundingClientRect();
            menu.style.top = (rect.bottom + 8) + 'px';
            menu.style.right = Math.max(12, window.innerWidth - rect.right) + 'px';
            menu.classList.remove('hidden');
        } else {
            menu.classList.add('hidden');
        }
    }

    function closePosMasterSettingsMenu() {
        const menu = document.getElementById('pos-master-settings-menu');
        if (menu) menu.classList.add('hidden');
    }

    document.addEventListener('click', (e) => {
        const menu = document.getElementById('pos-master-settings-menu');
        const btn = document.getElementById('btn-pos-master-settings');
        if (menu && !menu.classList.contains('hidden') && !menu.contains(e.target) && (!btn || !btn.contains(e.target))) {
            menu.classList.add('hidden');
        }
    });

    function openPosHeroBannerModal() {
        const modal = document.getElementById('pos-hero-banner-modal');
        if (!modal) return;
        renderPosHeroPreview();
        renderPosHeroHistoryGrid();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closePosHeroBannerModal() {
        const modal = document.getElementById('pos-hero-banner-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function applyPosHeroToBanner() {
        const bannerImg = document.getElementById('pos-hero-banner-img');
        if (!bannerImg) return;
        if (posHeroBanner.image) {
            bannerImg.src = posHeroBanner.image;
            bannerImg.classList.remove('hidden');
            bannerImg.style.objectPosition = `${posHeroBanner.x}% ${posHeroBanner.y}%`;
            bannerImg.style.transform = `scale(${posHeroBanner.scale})`;
        } else {
            bannerImg.removeAttribute('src');
            bannerImg.classList.add('hidden');
        }
    }

    function renderPosHeroPreview() {
        const previewImg = document.getElementById('pos-modal-hero-preview-img');
        const emptyHint = document.getElementById('pos-modal-hero-empty');
        const sliderX = document.getElementById('pos-hero-slider-x');
        const sliderY = document.getElementById('pos-hero-slider-y');
        const sliderScale = document.getElementById('pos-hero-slider-scale');
        const valX = document.getElementById('pos-hero-val-x');
        const valY = document.getElementById('pos-hero-val-y');
        const valScale = document.getElementById('pos-hero-val-scale');

        if (previewImg) {
            if (posHeroBanner.image) {
                previewImg.src = posHeroBanner.image;
                previewImg.classList.remove('hidden');
                previewImg.style.objectPosition = `${posHeroBanner.x}% ${posHeroBanner.y}%`;
                previewImg.style.transform = `scale(${posHeroBanner.scale})`;
                if (emptyHint) emptyHint.classList.add('hidden');
            } else {
                previewImg.removeAttribute('src');
                previewImg.classList.add('hidden');
                if (emptyHint) emptyHint.classList.remove('hidden');
            }
        }

        if (sliderX) sliderX.value = posHeroBanner.x;
        if (sliderY) sliderY.value = posHeroBanner.y;
        if (sliderScale) sliderScale.value = posHeroBanner.scale;
        if (valX) valX.textContent = `${posHeroBanner.x}%`;
        if (valY) valY.textContent = `${posHeroBanner.y}%`;
        if (valScale) valScale.textContent = `${posHeroBanner.scale}x`;
    }

    function renderPosHeroHistoryGrid() {
        const grid = document.getElementById('pos-hero-history-grid');
        if (!grid) return;

        const list = Array.isArray(posHeroBanner.history) ? posHeroBanner.history : [];
        if (list.length === 0) {
            grid.innerHTML = '<div class="col-span-4 text-center text-xs text-slate-400 py-3">ยังไม่มีประวัติภาพปก</div>';
            return;
        }

        grid.innerHTML = list.map(url => {
            const isSelected = (url === posHeroBanner.image);
            const safeUrl = String(url).replace(/'/g, "\\'");
            return `
                <div class="relative group rounded-xl overflow-visible aspect-[7/2] border-2 ${isSelected ? 'border-[#E50914] ring-2 ring-rose-200' : 'border-slate-200 hover:border-slate-300'} bg-slate-900 cursor-pointer transition select-none" onclick="selectPosHeroHistoryImage('${safeUrl}')">
                    <img src="${url}" alt="ภาพปกประวัติ" class="w-full h-full object-cover rounded-[10px]">
                    ${isSelected ? '<span class="absolute bottom-1 left-1 px-1 rounded bg-[#E50914] text-white text-[8px] font-bold">เลือกอยู่</span>' : ''}
                    <button type="button"
                            onclick="deletePosHeroHistoryImage(event, '${safeUrl}')"
                            class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center text-[10px] font-bold shadow-md cursor-pointer z-20 active:scale-90 transition-transform"
                            title="ลบภาพนี้">
                        ✕
                    </button>
                </div>
            `;
        }).join('');
    }

    function selectPosHeroHistoryImage(url) {
        posHeroBanner.image = url;
        renderPosHeroPreview();
        renderPosHeroHistoryGrid();
        applyPosHeroToBanner();
    }

    function nudgePosHeroPos(dx, dy) {
        posHeroBanner.x = Math.max(0, Math.min(100, Math.round(posHeroBanner.x + dx)));
        posHeroBanner.y = Math.max(0, Math.min(100, Math.round(posHeroBanner.y + dy)));
        renderPosHeroPreview();
    }

    function nudgePosHeroZoom(dScale) {
        posHeroBanner.scale = Math.max(1, Math.min(2.5, +(posHeroBanner.scale + dScale).toFixed(2)));
        renderPosHeroPreview();
    }

    function updatePosHeroSliderPos() {
        const sliderX = document.getElementById('pos-hero-slider-x');
        const sliderY = document.getElementById('pos-hero-slider-y');
        const sliderScale = document.getElementById('pos-hero-slider-scale');
        if (sliderX) posHeroBanner.x = Number(sliderX.value);
        if (sliderY) posHeroBanner.y = Number(sliderY.value);
        if (sliderScale) posHeroBanner.scale = Number(sliderScale.value);
        renderPosHeroPreview();
    }

    function resetPosHeroBannerPosition() {
        posHeroBanner.x = 50;
        posHeroBanner.y = 50;
        posHeroBanner.scale = 1;
        renderPosHeroPreview();
    }

    function clearPosHeroImage() {
        posHeroBanner.image = '';
        posHeroBanner.x = 50;
        posHeroBanner.y = 50;
        posHeroBanner.scale = 1;
        renderPosHeroPreview();
        renderPosHeroHistoryGrid();
        applyPosHeroToBanner();
    }

    async function handlePosHeroBannerUpload(event) {
        const file = event.target.files && event.target.files[0];
        if (!file) return;

        const label = document.getElementById('pos-hero-upload-label');
        if (label) label.textContent = '⏳ กำลังประมวลผลและอัปโหลดภาพ...';

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = async function() {
                const canvas = document.getElementById('pos-hero-compress-canvas');
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
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
                const base64Data = canvas.toDataURL('image/jpeg', 0.92);

                try {
                    const res = await fetch('{{ route("online.hero_banner.upload") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ scope: 'pos', image_base64: base64Data })
                    });
                    const data = await res.json();
                    if (data && data.success && data.url) {
                        posHeroBanner.image = data.url;
                        if (Array.isArray(data.history)) posHeroBanner.history = data.history;
                        renderPosHeroPreview();
                        renderPosHeroHistoryGrid();
                        applyPosHeroToBanner();
                        if (typeof showToast === 'function') showToast('อัปโหลดภาพปกเรียบร้อยแล้ว 📷');
                    } else {
                        if (typeof showToast === 'function') showToast(data.message || 'อัปโหลดล้มเหลว');
                    }
                } catch (err) {
                    if (typeof showToast === 'function') showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                } finally {
                    if (label) label.textContent = '📷 เลือกรูปภาพใหม่จากเครื่อง (ขนาดแนะนำ 1:7 แนวนอน)';
                    event.target.value = '';
                }
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    async function deletePosHeroHistoryImage(event, url) {
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
                body: JSON.stringify({ scope: 'pos', image_url: url })
            });
            const data = await res.json();
            if (data && data.success) {
                posHeroBanner.history = data.history || [];
                if (data.active_image !== undefined) {
                    posHeroBanner.image = data.active_image || '';
                }
                renderPosHeroPreview();
                renderPosHeroHistoryGrid();
                applyPosHeroToBanner();
                if (typeof showToast === 'function') showToast(data.message || 'ลบภาพปกแล้ว');
            }
        } catch (err) {
            if (typeof showToast === 'function') showToast('เกิดข้อผิดพลาดในการลบภาพ');
        }
    }

    async function savePosHeroBannerSettings() {
        const btn = document.getElementById('btn-save-pos-hero-banner');
        if (btn) btn.disabled = true;
        try {
            const res = await fetch('{{ route("online.hero_banner.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    scope: 'pos',
                    image_url: posHeroBanner.image || '',
                    pos_x: posHeroBanner.x,
                    pos_y: posHeroBanner.y,
                    scale: posHeroBanner.scale
                })
            });
            const data = await res.json();
            if (data && data.success) {
                if (Array.isArray(data.history)) posHeroBanner.history = data.history;
                applyPosHeroToBanner();
                closePosHeroBannerModal();
                if (typeof showToast === 'function') showToast(data.message || 'บันทึกภาพปกเรียบร้อยแล้ว');
            } else {
                if (typeof showToast === 'function') showToast(data.message || 'บันทึกไม่สำเร็จ');
            }
        } catch (err) {
            if (typeof showToast === 'function') showToast('เกิดข้อผิดพลาดในการบันทึก');
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    function copyPosField(inputId) {
        const el = document.getElementById(inputId);
        if (!el) return;
        el.select();
        navigator.clipboard?.writeText(el.value);
        showPosSyncToast('คัดลอกแล้ว: ' + el.value);
    }

    async function testAndroidPaymentWebhook() {
        const url = document.getElementById('pos-webhook-url')?.value;
        const secret = document.getElementById('pos-webhook-secret')?.value;
        const statusEl = document.getElementById('pos-webhook-last-status');
        if (!url || !secret) return;

        const testAmount = getCartTotal() > 0 ? getCartTotal().toFixed(2) : '35.00';
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Webhook-Secret': secret,
                },
                body: JSON.stringify({
                    secret,
                    message: `KBank: เงินเข้าบัญชี 098-1-53225-2 จำนวน ${testAmount} บาท`,
                }),
            });
            const data = await res.json();
            if (statusEl) {
                statusEl.textContent = data.matched
                    ? `✅ ทดสอบสำเร็จ! จับคู่บิล ${data.order_no} (฿${data.amount})`
                    : `✅ เชื่อมต่อ Webhook สำเร็จ! (อ่านยอด ฿${data.parsed_amount ?? testAmount} ได้ปกติ)`;
            }
            showPosSyncToast(data.matched ? `ยืนยันบิล ${data.order_no} แล้ว` : 'ทดสอบ Webhook สำเร็จ');
        } catch (err) {
            if (statusEl) statusEl.textContent = '❌ ทดสอบเชื่อมต่อ Webhook ไม่สำเร็จ';
        }
    }
</script>

<!-- POS HERO BANNER SETTINGS MODAL (1:7 แนวนอน) -->
<div id="pos-hero-banner-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/75 backdrop-blur-xs p-3 overflow-y-auto" onclick="if(event.target === this) closePosHeroBannerModal()">
    <div class="bg-white rounded-[26px] max-w-[540px] w-full shadow-2xl border border-slate-100 overflow-hidden flex flex-col my-auto max-h-[92vh]">
        <div class="p-4 bg-gradient-to-r from-[#4A0408] via-[#8B0912] to-[#4A0408] text-white flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-white/15 flex items-center justify-center text-base">🖼️</div>
                <div>
                    <h3 class="text-sm font-bold leading-tight">แก้ไขภาพปกหน้าร้าน (POS)</h3>
                    <p class="text-[11px] text-rose-200/90 font-normal">ปรับตำแหน่ง ซูม และจัดการภาพปกขนาด 1:7 แนวนอน</p>
                </div>
            </div>
            <button type="button" onclick="closePosHeroBannerModal()" class="w-7 h-7 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center text-white cursor-pointer transition">✕</button>
        </div>

        <div class="p-4 space-y-4 overflow-y-auto">
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <span class="w-1.5 h-3.5 rounded-full bg-[#E50914]"></span>
                        <span>ตัวอย่างขนาดปก (1:7 แนวนอน)</span>
                    </span>
                    <span class="text-[10.5px] text-slate-500 font-medium">7 : 1 Wide Banner</span>
                </div>

                <div class="relative w-full rounded-2xl overflow-hidden border-2 border-slate-300/80 shadow-md bg-gradient-to-br from-[#4A0408] via-[#6B0910] to-[#2B0204] select-none" style="aspect-ratio: 7 / 1;">
                    <img id="pos-modal-hero-preview-img"
                         src="{{ $posHeroImg }}"
                         alt="พรีวิวภาพปก 1:7"
                         class="w-full h-full object-cover transition-all duration-75 pointer-events-none {{ $posHeroImg ? '' : 'hidden' }}"
                         style="object-position: {{ $posHeroPosX }}% {{ $posHeroPosY }}%; transform: scale({{ $posHeroScale }});">
                    <div id="pos-modal-hero-empty" class="absolute inset-0 flex items-center justify-center text-rose-200/80 text-xs font-bold pointer-events-none {{ $posHeroImg ? 'hidden' : '' }}">
                        ยังไม่มีภาพปก (ใช้พื้นหลังสีแดงเข้มเรียบ)
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700">ปรับมุมมอง (ซ้าย ขวา บน ล่าง)</span>
                    <button type="button" onclick="resetPosHeroBannerPosition()" class="text-[11px] font-bold text-rose-600 hover:text-rose-700 cursor-pointer">🔄 รีเซ็ตกึ่งกลาง</button>
                </div>

                <div class="flex items-center justify-around gap-2 pt-1">
                    <div class="grid grid-cols-3 gap-1.5 w-32 shrink-0">
                        <div></div>
                        <button type="button" onclick="nudgePosHeroPos(0, -5)" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-rose-50 hover:text-[#E50914] shadow-2xs font-bold text-xs flex items-center justify-center cursor-pointer">⬆️</button>
                        <div></div>
                        <button type="button" onclick="nudgePosHeroPos(-5, 0)" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-rose-50 hover:text-[#E50914] shadow-2xs font-bold text-xs flex items-center justify-center cursor-pointer">⬅️</button>
                        <button type="button" onclick="resetPosHeroBannerPosition()" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-rose-50 hover:text-[#E50914] shadow-2xs text-[10px] font-bold flex items-center justify-center cursor-pointer">🎯</button>
                        <button type="button" onclick="nudgePosHeroPos(5, 0)" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-rose-50 hover:text-[#E50914] shadow-2xs font-bold text-xs flex items-center justify-center cursor-pointer">➡️</button>
                        <div></div>
                        <button type="button" onclick="nudgePosHeroPos(0, 5)" class="h-8 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-rose-50 hover:text-[#E50914] shadow-2xs font-bold text-xs flex items-center justify-center cursor-pointer">⬇️</button>
                        <div></div>
                    </div>

                    <div class="flex flex-col gap-1.5 flex-1 max-w-[190px]">
                        <div class="flex items-center gap-1.5">
                            <button type="button" onclick="nudgePosHeroZoom(-0.1)" class="flex-1 py-1.5 px-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold shadow-2xs flex items-center justify-center gap-1 cursor-pointer">➖ ซูมออก</button>
                            <button type="button" onclick="nudgePosHeroZoom(0.1)" class="flex-1 py-1.5 px-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-xs font-bold shadow-2xs flex items-center justify-center gap-1 cursor-pointer">➕ ซูมเข้า</button>
                        </div>
                        <button type="button" onclick="clearPosHeroImage()" class="w-full py-1.5 px-2 rounded-xl bg-white border border-rose-200 text-rose-700 hover:bg-rose-50 text-[11px] font-bold shadow-2xs flex items-center justify-center gap-1 cursor-pointer transition">
                            🗑️ ลบรูปภาพปกออก
                        </button>
                    </div>
                </div>

                <div class="space-y-1.5 pt-1 text-[11px]">
                    <div class="flex items-center justify-between text-slate-600 font-medium">
                        <span>แนวนอน (ซ้าย - ขวา):</span>
                        <span id="pos-hero-val-x" class="font-bold text-slate-800">{{ $posHeroPosX }}%</span>
                    </div>
                    <input type="range" id="pos-hero-slider-x" min="0" max="100" value="{{ $posHeroPosX }}" oninput="updatePosHeroSliderPos()" class="w-full accent-[#E50914] cursor-pointer">

                    <div class="flex items-center justify-between text-slate-600 font-medium pt-1">
                        <span>แนวตั้ง (บน - ล่าง):</span>
                        <span id="pos-hero-val-y" class="font-bold text-slate-800">{{ $posHeroPosY }}%</span>
                    </div>
                    <input type="range" id="pos-hero-slider-y" min="0" max="100" value="{{ $posHeroPosY }}" oninput="updatePosHeroSliderPos()" class="w-full accent-[#E50914] cursor-pointer">

                    <div class="flex items-center justify-between text-slate-600 font-medium pt-1">
                        <span>อัตราขยาย (Zoom):</span>
                        <span id="pos-hero-val-scale" class="font-bold text-slate-800">{{ $posHeroScale }}x</span>
                    </div>
                    <input type="range" id="pos-hero-slider-scale" min="1" max="2.5" step="0.05" value="{{ $posHeroScale }}" oninput="updatePosHeroSliderPos()" class="w-full accent-[#E50914] cursor-pointer">
                </div>
            </div>

            <div>
                <label class="flex items-center justify-center gap-2 p-3 rounded-2xl border-2 border-dashed border-rose-300 hover:border-[#E50914] bg-rose-50/60 hover:bg-rose-50 text-[#E50914] text-xs font-bold cursor-pointer transition select-none">
                    <span id="pos-hero-upload-label">📷 เลือกรูปภาพใหม่จากเครื่อง (ขนาดแนะนำ 1:7 แนวนอน)</span>
                    <input type="file" id="pos-hero-banner-file-input" accept="image/*" class="hidden" onchange="handlePosHeroBannerUpload(event)">
                </label>
                <canvas id="pos-hero-compress-canvas" class="hidden"></canvas>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <span class="w-1.5 h-3.5 rounded-full bg-[#E50914]"></span>
                        <span>ประวัติภาพปกเก่า &amp; ภาพที่บันทึกไว้</span>
                    </span>
                    <span class="text-[10px] text-slate-400">กด ✕ บนรูปเพื่อลบ</span>
                </div>
                <div id="pos-hero-history-grid" class="grid grid-cols-4 gap-2.5 max-h-40 overflow-y-auto p-1 pr-1.5"></div>
            </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2 shrink-0">
            <button type="button" onclick="closePosHeroBannerModal()" class="py-2.5 px-4 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-bold cursor-pointer transition">ยกเลิก</button>
            <button type="button" id="btn-save-pos-hero-banner" onclick="savePosHeroBannerSettings()" class="py-2.5 px-5 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-bold shadow-sm inline-flex items-center gap-1.5 cursor-pointer transition active:scale-95">💾 บันทึกภาพปกนี้</button>
        </div>
    </div>
</div>
@endpush
@endsection
