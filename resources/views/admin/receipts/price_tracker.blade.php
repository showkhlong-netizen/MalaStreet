@extends('layouts.app', ['title' => 'ระบบตรวจจับราคาขึ้น-ลง & ร้านค้าอ้างอิง - หม่าล่าสตรีท'])

@section('content')
@php
    // Helper to format latest purchase price: If whole number (no fraction / .00), cut .00 off.
    // If it has decimals (e.g. .02, .030, .80), display the decimals.
    $formatLatestPrice = function ($price) {
        $num = (float) $price;
        if (fmod($num, 1.0) == 0.0) {
            return number_format($num, 0);
        }
        $str = (string) $price;
        $parts = explode('.', $str);
        $dec = isset($parts[1]) ? strlen($parts[1]) : 2;
        $dec = min(max(2, $dec), 4);
        return number_format($num, $dec);
    };

    // Helper to resolve accurate 1:1 ingredient photo + category slug for every RawMaterial
    // Prioritizes custom database fields ($mat->image_url and $mat->category_slug) first!
    $resolveMaterialMeta = function (\App\Models\RawMaterial $mat) {
        $name = $mat->name ?? '';
        $linkedCatSlugs = $mat->products
            ? $mat->products->pluck('category.slug')->filter()->unique()->values()->all()
            : [];

        $catSlug = $mat->category_slug ?: ($linkedCatSlugs[0] ?? null);
        if (!$catSlug) {
            if (str_contains($name, 'เนื้อ') || str_contains($name, 'โคขุน')) {
                $catSlug = 'beef';
            } elseif (str_contains($name, 'ฟองเต้าหู้') || str_contains($name, 'เต้าหู้') || str_contains($name, 'เส้น')) {
                $catSlug = 'tofu-noodles';
            } elseif (str_contains($name, 'เห็ด') || str_contains($name, 'ผัก')) {
                $catSlug = 'vegetables';
            } elseif (
                str_contains($name, 'ลูกชิ้น') ||
                str_contains($name, 'ไส้กรอก') ||
                str_contains($name, 'ปูอัด') ||
                str_contains($name, 'ปูไลน์') ||
                str_contains($name, 'โบโลน่า') ||
                str_contains($name, 'ค็อกเทล') ||
                str_contains($name, 'เกี๊ยว') ||
                str_contains($name, 'ทูนิมิ')
            ) {
                $catSlug = 'meatball-sausage';
            } elseif (
                str_contains($name, 'หมู') ||
                str_contains($name, 'ไก่') ||
                str_contains($name, 'เอ็น') ||
                str_contains($name, 'สามชั้น') ||
                str_contains($name, 'สันใน') ||
                str_contains($name, 'ไข่')
            ) {
                $catSlug = 'pork-chicken';
            } elseif (str_contains($name, 'น้ำ') || str_contains($name, 'เครื่องดื่ม')) {
                $catSlug = 'beverages';
            } else {
                $catSlug = 'snacks';
            }
        }

        if (!in_array($catSlug, $linkedCatSlugs, true)) {
            array_unshift($linkedCatSlugs, $catSlug);
        }

        if (!empty($mat->image_url)) {
            $path = preg_replace('#^https?://(?:localhost|127\.0\.0\.1)(?::\d+)?/#', '/', $mat->image_url);
            $img = str_starts_with($path, 'http') ? $path : '/' . ltrim($path, '/');
        } elseif (str_contains($name, 'เอ็นไก่') || str_contains($name, 'ไก่ห่อ') || str_contains($name, 'ไก่ย่าง') || str_contains($name, 'เทอร์โบ')) {
            $img = '/assets/ingredients/chicken-tendon.png';
        } elseif (str_contains($name, 'ไส้กรอก') || str_contains($name, 'โบโลน่า') || str_contains($name, 'ค็อกเทล') || str_contains($name, 'ไก่ยอ')) {
            $img = '/assets/ingredients/sausage-smoked.png';
        } elseif (str_contains($name, 'ลูกชิ้น') || str_contains($name, 'ปูอัด') || str_contains($name, 'ปูไลน์') || str_contains($name, 'ทูนิมิ') || str_contains($name, 'เกี๊ยว')) {
            $img = '/assets/ingredients/fishball-phuket.png';
        } elseif (str_contains($name, 'หมู') || str_contains($name, 'สามชั้น') || str_contains($name, 'สันใน') || str_contains($name, 'เบาว์ราช')) {
            $img = '/assets/ingredients/pork-belly-sliced.png';
        } elseif (str_contains($name, 'เนื้อ') || str_contains($name, 'โคขุน')) {
            $img = '/assets/ingredients/beef-ribeye-sliced.png';
        } elseif (str_contains($name, 'เต้าหู้') || str_contains($name, 'ฟองเต้าหู้') || str_contains($name, 'ไข่')) {
            $img = '/assets/ingredients/tofu-fried-roll.png';
        } elseif (str_contains($name, 'เห็ด')) {
            $img = '/assets/ingredients/mushroom-fresh.png';
        } elseif (str_contains($name, 'ผัก')) {
            $img = '/assets/ingredients/vegetable-greens.png';
        } else {
            $catImgMap = [
                'beef' => '/assets/ingredients/beef-ribeye-sliced.png',
                'pork-chicken' => '/assets/ingredients/pork-belly-sliced.png',
                'meatball-sausage' => '/assets/ingredients/fishball-phuket.png',
                'vegetables' => '/assets/ingredients/vegetable-greens.png',
                'tofu-noodles' => '/assets/ingredients/tofu-fried-roll.png',
                'snacks' => '/assets/categories/snacks.png',
                'beverages' => '/assets/categories/drinks.png',
            ];
            $img = $catImgMap[$catSlug] ?? '/assets/ingredients/fishball-phuket.png';
        }

        return [
            'cat_slugs' => $linkedCatSlugs,
            'primary_cat' => $catSlug,
            'image_url' => $img,
        ];
    };

    $categoryIconMap = [
        'beef' => '/assets/categories/beef.png',
        'pork-chicken' => '/assets/categories/pork-chicken.png',
        'meatball-sausage' => '/assets/categories/meatballs.png',
        'vegetables' => '/assets/categories/vegetables.png',
        'tofu-noodles' => '/assets/categories/tofu.png',
        'snacks' => '/assets/categories/snacks.png',
        'beverages' => '/assets/categories/drinks.png',
    ];

    $presetIngredientImages = [
        ['label' => 'เอ็นไก่ / หมูพันเห็ด', 'url' => '/assets/ingredients/chicken-tendon.png'],
        ['label' => 'ไส้กรอก / ลูกชิ้นย่าง', 'url' => '/assets/ingredients/sausage-smoked.png'],
        ['label' => 'ลูกชิ้นปลาภูเก็ต', 'url' => '/assets/ingredients/fishball-phuket.png'],
        ['label' => 'หมูสามชั้นสไลซ์', 'url' => '/assets/ingredients/pork-belly-sliced.png'],
        ['label' => 'เนื้อใบพายโคขุน', 'url' => '/assets/ingredients/beef-ribeye-sliced.png'],
        ['label' => 'ฟองเต้าหู้ / เต้าหู้', 'url' => '/assets/ingredients/tofu-fried-roll.png'],
        ['label' => 'เห็ดสด', 'url' => '/assets/ingredients/mushroom-fresh.png'],
        ['label' => 'ผักกาด / ผักสด', 'url' => '/assets/ingredients/vegetable-greens.png'],
    ];

    $heroImage = !empty($heroSettings['hero_image'])
        ? (preg_replace('#^https?://(?:localhost|127\.0\.0\.1)(?::\d+)?/#', '/', $heroSettings['hero_image']))
        : '/assets/ingredients/hero-price-art-left.png';
    if (!str_starts_with($heroImage, 'http') && !str_starts_with($heroImage, '/')) {
        $heroImage = '/' . $heroImage;
    }
    $heroTitle = $heroSettings['hero_title'] ?? 'ระบบตรวจจับราคาขึ้น-ลง & ร้านค้าอ้างอิง';
    $heroSubtitle = $heroSettings['hero_subtitle'] ?? 'ติดตามความผันผวนของราคาวัตถุดิบสำคัญได้ ไม่ว่าจะขึ้นหรือลง เช่น เอ็นไก่, เนื้อวัว, ผักสด ฯลฯ เพื่อเลือกซื้อร้านที่ถูกที่สุด';
@endphp

<style>
    /* Sticky Category & Filter Bar locked beneath the top navbar */
    #price-sticky-bar {
        position: sticky;
        top: var(--app-header-h, 76px);
        z-index: 30;
        background: rgba(248, 249, 251, 0.96);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    #price-sticky-bar.is-pinned {
        box-shadow: 0 10px 24px -12px rgba(15, 23, 42, 0.14);
        border-bottom: 1px solid rgba(226, 232, 240, 0.95);
    }

    /* Hide scrollbar for category pills */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Edit Mode visibility for 3-line (☰) buttons on cards & hero banner */
    .edit-mode-trigger {
        display: none;
    }
    .mat-card:hover .edit-mode-trigger,
    body.price-edit-mode-active .edit-mode-trigger {
        display: inline-flex;
        animation: popInEditBtn 0.18s cubic-bezier(0.16, 1, 0.3, 1);
    }
    body.price-edit-mode-active .mat-card {
        border-color: rgba(229, 9, 20, 0.38);
        box-shadow: 0 4px 16px -4px rgba(229, 9, 20, 0.10);
    }
    body.price-edit-mode-active #hero-cover-container {
        outline: 2px dashed rgba(255, 255, 255, 0.75);
        outline-offset: 3px;
    }

    @keyframes popInEditBtn {
        from {
            opacity: 0;
            transform: scale(0.82);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Multi-Select Delete Mode Styling */
    .delete-checkbox-wrapper {
        display: none;
    }
    body.price-delete-mode-active .delete-checkbox-wrapper {
        display: block !important;
        animation: popInEditBtn 0.16s ease-out;
    }
    body.price-delete-mode-active .mat-card {
        cursor: pointer;
        user-select: none;
    }
    body.price-delete-mode-active .mat-card.is-selected-for-delete {
        border-color: #E50914 !important;
        background-color: #FFF5F5 !important;
        box-shadow: 0 4px 16px -2px rgba(229, 9, 20, 0.22) !important;
        transform: translateY(-1px);
    }

    /* Compact adjustments when 4 columns per row is selected on desktop */
    @media (min-width: 1280px) {
        #materials-grid.grid-cols-4-mode {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        }
        #materials-grid.grid-cols-4-mode .mat-thumb {
            width: 82px !important;
            height: 82px !important;
        }
        #materials-grid.grid-cols-4-mode .mat-card {
            padding: 11px 12px !important;
            gap: 10px !important;
        }
        #materials-grid.grid-cols-4-mode .mat-title {
            font-size: 14px !important;
        }
        #materials-grid.grid-cols-4-mode .mat-price-val {
            font-size: 13px !important;
        }
    }
</style>

<div class="min-h-screen bg-[#F8F9FB] pb-12">
    <!-- 1. HERO BANNER & PRICE MONITORING INFORMATION PANEL -->
    <section class="relative w-full overflow-hidden bg-gradient-to-r from-[#3E0206] via-[#8B0912] to-[#3E0206] border-b border-[#5A060C]">
        <!-- Subtle ambient glow -->
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_28%_50%,rgba(230,0,35,0.42),transparent_60%)]"></div>

        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-5">
            <div class="grid grid-cols-1 lg:grid-cols-12 items-center gap-4 lg:gap-6">
                <!-- Left + Center: Mala Street Calligraphic Title & Sizzling Skewers Bowl Photo (Cover Image) -->
                <div
                    id="hero-cover-container"
                    class="lg:col-span-7 xl:col-span-7 relative flex items-center justify-center lg:justify-start overflow-hidden rounded-2xl group transition-all"
                >
                    <img
                        id="hero-cover-img"
                        src="{{ $heroImage }}"
                        alt="หม่าล่า Mala Street ระบบตรวจจับราคาขึ้น-ลง"
                        class="w-full max-w-[680px] h-[135px] sm:h-[168px] object-cover object-left rounded-2xl select-none"
                        onerror="this.onerror=null;this.src='{{ asset('assets/ingredients/hero-price-art-left.png') }}';"
                    >

                    <!-- Three-Lines (☰) Edit Button on Hero Cover Image (Shown when Edit Mode is toggled ON) -->
                    <button
                        type="button"
                        onclick="openHeroEditModal(event)"
                        title="แก้ไขภาพปกและข้อมูลส่วนหัว"
                        class="edit-mode-trigger absolute top-3 right-3 sm:right-6 z-20 px-3.5 py-2 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-bold shadow-xl border-2 border-white items-center gap-2 cursor-pointer transition-transform hover:scale-105"
                    >
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                            <line x1="4" y1="6" x2="20" y2="6"></line>
                            <line x1="4" y1="12" x2="20" y2="12"></line>
                            <line x1="4" y1="18" x2="20" y2="18"></line>
                        </svg>
                        <span>แก้ไขภาพปก</span>
                    </button>
                </div>

                <!-- Right: White Information Panel Card -->
                <div class="lg:col-span-5 xl:col-span-5 flex justify-center lg:justify-end">
                    <div class="relative w-full max-w-[520px] bg-white/98 rounded-[22px] px-5 py-4 sm:px-6 sm:py-4.5 shadow-xl border border-white flex flex-col justify-between gap-3.5">
                        <!-- Three-Lines (☰) Quick Trigger on Right Info Box in Edit Mode -->
                        <button
                            type="button"
                            onclick="openHeroEditModal(event)"
                            title="แก้ไขข้อความและภาพปกส่วนหัว"
                            class="edit-mode-trigger absolute top-3 right-3 w-8 h-8 rounded-xl bg-rose-50 hover:bg-[#E50914] text-[#E50914] hover:text-white border border-rose-200 items-center justify-center shadow-xs cursor-pointer transition-colors"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
                                <line x1="4" y1="6" x2="20" y2="6"></line>
                                <line x1="4" y1="12" x2="20" y2="12"></line>
                                <line x1="4" y1="18" x2="20" y2="18"></line>
                            </svg>
                        </button>

                        <div class="flex items-start gap-3.5 pr-6">
                            <!-- Soft Amber/Cream Rising Chart Icon -->
                            <div class="w-12 h-12 rounded-2xl bg-[#FFF3D6] border border-[#FDE2A7] flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-6 h-6 text-[#E50914]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                                    <polyline points="16 7 22 7 22 13"></polyline>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h1 id="hero-info-title" class="text-[17px] sm:text-[18.5px] font-bold text-[#14171F] leading-snug tracking-tight">
                                    {{ $heroTitle }}
                                </h1>
                                <p id="hero-info-subtitle" class="text-[12px] text-[#687082] font-normal mt-0.5 leading-relaxed">
                                    {{ $heroSubtitle }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2.5 pt-0.5 w-full">
                            <!-- แถวที่ 1: แสกนใบเสร็จเพิ่ม, สต็อกไม้คงเหลือ, ตารางประวัติทั้งหมด (อยู่บรรทัดเดียวกัน ตารางประวัติทั้งหมดอยู่แถบขวาสุด) -->
                            <div class="grid grid-cols-3 gap-2 w-full">
                                <!-- 1. แสกนใบเสร็จเพิ่ม -->
                                <a
                                    href="{{ route('admin.receipts.index') }}"
                                    class="py-2 sm:py-2.5 px-2 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-[12px] sm:text-[12.5px] font-semibold shadow-md shadow-[#E50914]/25 flex items-center justify-center gap-1.5 transition-all active:scale-[0.99] text-center"
                                >
                                    <svg class="w-4 h-4 shrink-0 stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="truncate">แสกนใบเสร็จเพิ่ม</span>
                                </a>

                                <!-- 2. สต็อกไม้คงเหลือ -->
                                <a
                                    href="{{ route('admin.inventory.index') }}"
                                    class="py-2 sm:py-2.5 px-2 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-[12px] sm:text-[12.5px] font-semibold shadow-md shadow-[#E50914]/25 flex items-center justify-center gap-1.5 transition-all active:scale-[0.99] text-center"
                                >
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <span class="truncate">สต็อกไม้คงเหลือ</span>
                                </a>

                                <!-- 3. ตารางประวัติทั้งหมด (อยู่แถบขวาสุด) -->
                                <a
                                    href="#price-ledger-section"
                                    class="py-2 sm:py-2.5 px-2 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-[#2E3440] text-[12px] sm:text-[12.5px] font-medium flex items-center justify-center gap-1.5 transition-all shadow-2xs hover:border hover:border-slate-300 text-center"
                                >
                                    <span class="truncate">ตารางประวัติทั้งหมด</span>
                                    <span class="px-1.5 py-0.2 rounded-full bg-white text-[#E50914] font-semibold text-[11px] shadow-2xs shrink-0">{{ $priceHistories->total() }}</span>
                                </a>
                            </div>

                            <!-- แถวที่ 2: เพิ่มรายการ (อยู่เดี่ยวๆ ปรับให้ใหญ่และเด่นทั้งแถบ ความยาวแถบเสมอกับสามแถบด้านบน) -->
                            <button
                                type="button"
                                onclick="openCreateMaterialModal()"
                                class="w-full py-2.5 sm:py-3 px-4 rounded-xl bg-gradient-to-r from-[#8B0912] via-[#E50914] to-[#8B0912] hover:from-[#73070E] hover:via-[#C8102E] hover:to-[#73070E] text-white text-[13.5px] sm:text-sm font-bold flex items-center justify-center gap-2 shadow-md shadow-[#E50914]/25 hover:shadow-lg transition-all cursor-pointer active:scale-[0.99] border border-white/20"
                                title="เพิ่มรายการวัตถุดิบและข้อมูลสต็อกใหม่"
                            >
                                <span class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 5v14M5 12h14"/>
                                    </svg>
                                </span>
                                <span class="tracking-wide">เพิ่มรายการ</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. STICKY HORIZONTAL CATEGORY PILLS + SECTION HEADER & CONTROLS BAR -->
    <div id="price-sticky-bar" class="w-full py-3">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 space-y-3">
            <!-- Row A: Horizontal Category Pills -->
            <div class="flex items-center gap-2.5 overflow-x-auto no-scrollbar pb-0.5" id="category-pills-bar">
                <!-- Active Pill: ทั้งหมด -->
                <button
                    type="button"
                    data-cat="all"
                    onclick="filterMaterialsByCategory('all')"
                    class="cat-pill-btn active-cat shrink-0 h-[44px] px-4 rounded-[14px] bg-[#E50914] text-white border border-[#E50914] shadow-sm flex items-center gap-2.5 transition-all cursor-pointer"
                >
                    <span class="w-6 h-6 rounded-lg bg-white/15 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 20 20" fill="currentColor">
                            <circle cx="5.5" cy="5.5" r="2.3"></circle>
                            <circle cx="14.5" cy="5.5" r="2.3"></circle>
                            <circle cx="5.5" cy="14.5" r="2.3"></circle>
                            <circle cx="14.5" cy="14.5" r="2.3"></circle>
                        </svg>
                    </span>
                    <span class="text-[13.5px] font-semibold tracking-tight">ทั้งหมด</span>
                </button>

                @foreach($categories as $cat)
                    @php
                        $catImg = !empty($cat->icon) && !in_array($cat->icon, ['🥩', '🍗', '🍢', '🥬', '🍥', '🥤', '🍟'])
                            ? (str_starts_with($cat->icon, 'http') ? $cat->icon : asset($cat->icon))
                            : ($categoryIconMap[$cat->slug] ?? asset('assets/categories/all.png'));
                    @endphp
                    <button
                        type="button"
                        data-cat="{{ $cat->slug }}"
                        data-active="{{ $cat->is_active ? '1' : '0' }}"
                        data-cat-name="{{ $cat->name }}"
                        onclick="filterMaterialsByCategory('{{ $cat->slug }}')"
                        class="cat-pill-btn shrink-0 h-[44px] px-4 rounded-[14px] bg-white hover:bg-rose-50/50 text-[#1F2430] border border-slate-200/90 shadow-2xs flex items-center gap-2.5 transition-all cursor-pointer"
                    >
                        <img
                            src="{{ $catImg }}"
                            alt="{{ $cat->name }}"
                            class="w-7 h-7 rounded-full object-cover shrink-0 border border-slate-100"
                            onerror="this.onerror=null;this.src='{{ asset('assets/menu/food-fallback.svg') }}';"
                        >
                        <span class="text-[13.5px] font-semibold tracking-tight">{{ $cat->name }}</span>
                        @if(!$cat->is_active)
                            <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0" title="ยังไม่อนุมัติแสดงผล"></span>
                        @endif
                    </button>
                @endforeach

                <!-- Add New Category Blank Pill with '+' Button (แถบเปล่ามี + ข้างในสำหรับสร้างแถบเพิ่ม) -->
                <button
                    type="button"
                    onclick="openCreateCategoryModal()"
                    class="shrink-0 h-[44px] px-3.5 rounded-[14px] border-2 border-dashed border-slate-300 hover:border-[#E50914] bg-white/90 hover:bg-rose-50/60 text-slate-400 hover:text-[#E50914] shadow-2xs flex items-center gap-2 transition-all cursor-pointer group"
                    title="สร้างแถบหมวดหมู่เพิ่ม"
                >
                    <span class="w-6 h-6 rounded-lg bg-slate-100 group-hover:bg-[#E50914]/10 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-slate-500 group-hover:text-[#E50914] transition-colors" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                    </span>
                    <span class="text-[13px] font-semibold text-slate-500 group-hover:text-[#E50914] transition-colors">เพิ่มหมวดหมู่</span>
                </button>
            </div>

            <!-- Row B: Section Title ("▍ รายการวัตถุดิบทั้งหมด") + Right Controls Bar (Search + [3 | 4] + Sort Dropdown + [Today/All] + [☰] Master Edit Toggle Button at Far Right) -->
            <div class="flex items-center justify-between gap-3 pt-0.5 flex-nowrap overflow-x-auto no-scrollbar">
                <!-- Left: Section Title & Item Count -->
                <div class="flex items-center gap-2 shrink-0">
                    <span class="w-1.5 h-6 rounded-full bg-[#E50914] shrink-0"></span>
                    <h2 id="section-heading-text" class="text-[17px] sm:text-[18px] font-bold text-[#14171F] tracking-tight whitespace-nowrap">
                        รายการวัตถุดิบทั้งหมด
                    </h2>
                    <span class="text-[13px] text-[#687082] font-normal whitespace-nowrap">
                        (<span id="visible-mat-count">{{ $rawMaterials->count() }}</span> รายการ)
                    </span>
                    <!-- Category Approval Button when viewing a specific category tab -->
                    <button
                        type="button"
                        id="btn-cat-approval-toggle"
                        onclick="toggleActiveCategoryApproval()"
                        class="hidden shrink-0 w-8 h-8 rounded-xl transition-all shadow-2xs items-center justify-center cursor-pointer ml-1 select-none"
                        title="อนุมัติทุกรายการในแถบนี้ (สั่งออนไลน์ & แคชเชียร์)"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    </button>

                    <!-- Active Category 3-Lines Settings Button (☰) -->
                    <div class="relative inline-flex items-center">
                        <button
                            type="button"
                            id="btn-cat-main-settings-trigger"
                            onclick="toggleMainCategorySettingsDropdown(event)"
                            class="hidden shrink-0 w-8 h-8 rounded-full border-2 border-slate-900 hover:border-[#E50914] bg-white hover:bg-rose-50 text-[#E50914] items-center justify-center cursor-pointer ml-1 shadow-2xs select-none transition-all"
                            title="ตัวเลือกจัดการหมวดหมู่นี้ (☰)"
                        >
                            <svg class="w-3.5 h-3.5 text-[#E50914]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round">
                                <line x1="4" y1="7" x2="20" y2="7"></line>
                                <line x1="4" y1="12" x2="20" y2="12"></line>
                                <line x1="4" y1="17" x2="20" y2="17"></line>
                            </svg>
                        </button>

                        <!-- Main Category Settings Dropdown (☰) styled exactly like Image 2 -->
                        <div
                            id="main-category-settings-dropdown"
                            class="hidden fixed w-64 bg-white rounded-2xl shadow-2xl border border-slate-200/90 py-1.5 animate-in fade-in zoom-in-95 duration-100 divide-y divide-slate-100"
                            style="z-index: 99999;"
                        >
                            <div class="px-3.5 py-1.5 text-[11px] font-bold text-slate-400 truncate">
                                ตัวเลือกจัดการรายการ (☰)
                            </div>

                            <button
                                type="button"
                                onclick="openEditMainCategoryFromDropdown()"
                                class="w-full px-3.5 py-2.5 text-left text-xs font-semibold text-slate-700 hover:bg-rose-50 hover:text-[#E50914] flex items-center gap-2.5 transition cursor-pointer"
                            >
                                <span class="w-7 h-7 rounded-lg bg-rose-50 text-[#E50914] flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </span>
                                <div>
                                    <span class="block font-bold">แก้ไขรายการ</span>
                                    <span class="block text-[10.5px] font-normal text-slate-400">แก้ไขข้อความและภาพปกหมวดหมู่นี้</span>
                                </div>
                            </button>

                            <button
                                type="button"
                                onclick="confirmDeleteMainCategoryFromDropdown()"
                                class="w-full px-3.5 py-2.5 text-left text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 flex items-center gap-2.5 transition cursor-pointer"
                            >
                                <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </span>
                                <div>
                                    <span class="block font-bold">ลบรายการ</span>
                                    <span class="block text-[10.5px] font-normal text-slate-400">ลบหมวดหมู่นี้ออกจากระบบ</span>
                                </div>
                            </button>
                        </div>
                    </div>
                    <span id="live-sync-indicator" class="hidden 2xl:inline-flex items-center gap-1.5 ml-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/70 text-[11px] font-medium whitespace-nowrap">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>ซิงก์ราคาอัตโนมัติ</span>
                    </span>
                </div>

                <!-- Right Controls Bar (Guaranteed Single Line: Search + [3|4] + Sort + [Today/All] + [☰]) -->
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0 flex-nowrap">
                    <!-- 1. Search Input -->
                    <div class="relative shrink-0">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path stroke-linecap="round" d="m21 21-4.3-4.3"></path>
                        </svg>
                        <input
                            type="text"
                            id="mat-search-input"
                            oninput="applyMaterialFilters()"
                            placeholder="ค้นหาวัตถุดิบ / ร้านค้า..."
                            class="w-36 sm:w-44 md:w-48 pl-8 pr-3 py-1.5 rounded-full bg-white border border-slate-200/90 text-[12px] font-normal text-slate-700 placeholder-slate-400 focus:outline-none focus:border-[#E50914] shadow-2xs"
                        >
                    </div>

                    <!-- 2. 3 vs 4 Items-Per-Row Grid Switcher Pill (Default = 3 items per row) -->
                    <div class="inline-flex items-center bg-white border border-slate-200/90 rounded-full p-0.5 shadow-2xs select-none shrink-0" role="group" aria-label="จำนวนรายการต่อแถว">
                        <button
                            type="button"
                            id="btn-grid-3"
                            onclick="setPriceGridColumns(3)"
                            title="แสดง 3 รายการต่อ 1 แถว"
                            class="px-2.5 py-1 rounded-full text-[11.5px] font-semibold flex items-center gap-1.5 transition-all cursor-pointer bg-[#E50914] text-white shadow-2xs"
                        >
                            <span class="inline-flex items-center gap-[2px]" aria-hidden="true">
                                <span class="w-[4px] h-[4px] rounded-[1px] bg-current"></span>
                                <span class="w-[4px] h-[4px] rounded-[1px] bg-current"></span>
                                <span class="w-[4px] h-[4px] rounded-[1px] bg-current"></span>
                            </span>
                            <span>3</span>
                        </button>

                        <button
                            type="button"
                            id="btn-grid-4"
                            onclick="setPriceGridColumns(4)"
                            title="แสดง 4 รายการต่อ 1 แถว"
                            class="px-2.5 py-1 rounded-full text-[11.5px] font-semibold flex items-center gap-1.5 transition-all cursor-pointer text-slate-600 hover:text-slate-900"
                        >
                            <span class="inline-flex items-center gap-[2px]" aria-hidden="true">
                                <span class="w-[3.5px] h-[3.5px] rounded-[1px] bg-current"></span>
                                <span class="w-[3.5px] h-[3.5px] rounded-[1px] bg-current"></span>
                                <span class="w-[3.5px] h-[3.5px] rounded-[1px] bg-current"></span>
                                <span class="w-[3.5px] h-[3.5px] rounded-[1px] bg-current"></span>
                            </span>
                            <span>4</span>
                        </button>
                    </div>

                    <!-- 3. Sort Dropdown -->
                    <div class="relative shrink-0">
                        <select
                            id="mat-sort-select"
                            onchange="applyMaterialFilters()"
                            aria-label="เรียงลำดับวัตถุดิบ"
                            class="appearance-none bg-white border border-slate-200/90 rounded-full pl-3 pr-7 py-1.5 text-[12px] font-medium text-slate-700 focus:outline-none focus:border-[#E50914] shadow-2xs cursor-pointer"
                        >
                            <option value="latest">เรียงตาม : อัปเดตล่าสุด</option>
                            <option value="change-desc">เรียงตาม : ราคาเปลี่ยนแปลงมากสุด</option>
                            <option value="price-desc">เรียงตาม : ราคาซื้อ (สูง - ต่ำ)</option>
                            <option value="price-asc">เรียงตาม : ราคาซื้อ (ต่ำ - สูง)</option>
                            <option value="stick-asc">เรียงตาม : ต้นทุนต่อไม้ (ต่ำ - สูง)</option>
                        </select>
                        <svg class="w-3.5 h-3.5 text-slate-500 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"></path>
                        </svg>
                    </div>

                    <!-- 4. Today vs All Items Filter Pill (อยู่ฝั่งซ้ายของแถบสามขีด ☰ ตามภาพ) -->
                    <div class="inline-flex items-center bg-white border border-slate-200/90 rounded-full p-0.5 shadow-2xs select-none shrink-0" role="group" aria-label="ตัวกรองรายการวันนี้ / ทั้งหมด">
                        <button
                            type="button"
                            id="btn-filter-today"
                            onclick="setPriceItemsFilter('today')"
                            title="แสดงเฉพาะรายการที่สั่งซื้อในวันนี้ (จากหน้าสแกนใบเสร็จ)"
                            class="px-2.5 sm:px-3 py-1 rounded-full text-[11.5px] sm:text-[12px] font-semibold flex items-center gap-1.5 transition-all cursor-pointer text-slate-600 hover:text-slate-900 whitespace-nowrap"
                        >
                            <span class="w-2 h-2 rounded-full bg-[#84CC16]"></span>
                            <span>รายการวันนี้</span>
                            <span id="badge-count-today" class="px-1.5 py-0.2 rounded-full bg-slate-100 text-[10px] font-bold text-slate-700">{{ $todayBoughtCount ?? 0 }}</span>
                            <!-- ปุ่มโหลดรายการสั่งซื้อวันนี้จากหน้าสแกนใบเสร็จ -->
                            <span
                                id="btn-sync-today"
                                onclick="syncTodayReceiptItems(event)"
                                title="กดเพื่อโหลดรายการที่สั่งซื้อวันนี้จากหน้าสแกนใบเสร็จ (ตรวจชื่อหลักและชื่อรอง)"
                                class="w-5 h-5 rounded-full hover:bg-slate-200/80 inline-flex items-center justify-center transition-all cursor-pointer ml-0.5 text-slate-500 hover:text-slate-800"
                            >
                                <svg id="icon-sync-today" class="w-3.5 h-3.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                                </svg>
                            </span>
                        </button>

                        <button
                            type="button"
                            id="btn-filter-all"
                            onclick="setPriceItemsFilter('all')"
                            title="แสดงรายการทั้งหมดที่เคยมีมา พร้อมสัญลักษณ์สถานะ"
                            class="px-2.5 sm:px-3 py-1 rounded-full text-[11.5px] sm:text-[12px] font-semibold flex items-center gap-1.5 transition-all cursor-pointer bg-[#E50914] text-white shadow-2xs whitespace-nowrap"
                        >
                            <span>รายการทั้งหมด</span>
                            <span id="badge-count-all" class="px-1.5 py-0.2 rounded-full bg-white/20 text-[10px] font-bold text-white">{{ $rawMaterials->count() }}</span>
                        </button>
                    </div>

                    <!-- 5. Master Three-Lines (☰) Options Dropdown Trigger Button -->
                    <button
                        type="button"
                        id="btn-toggle-edit-mode"
                        onclick="toggleMasterOptionsMenu(event)"
                        title="ตัวเลือกจัดการรายการ (☰)"
                        aria-label="ตัวเลือกจัดการรายการ"
                        class="h-[33px] w-[33px] rounded-full bg-white hover:bg-rose-50 text-slate-700 hover:text-[#E50914] border border-slate-200/90 shadow-2xs inline-flex items-center justify-center shrink-0 transition-all cursor-pointer select-none"
                    >
                        <svg class="w-4 h-4 shrink-0 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round">
                            <line x1="4" y1="6" x2="20" y2="6"></line>
                            <line x1="4" y1="12" x2="20" y2="12"></line>
                            <line x1="4" y1="18" x2="20" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. INGREDIENT PRICE MONITORING CARDS GRID (Default: 3 items per row on Desktop, switchable to 4) -->
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 pt-2 space-y-8">
        <div id="materials-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 transition-all">
            @foreach($rawMaterials as $index => $mat)
                @php
                    $meta = $resolveMaterialMeta($mat);
                    $latestHistory = $mat->priceHistories->first();
                    $diffAmount = $latestHistory ? (float) $latestHistory->price_change_amount : 0.0;
                    $diffPercent = $latestHistory ? (float) $latestHistory->price_change_percent : 0.0;
                    $isPriceUp = $diffAmount > 0;
                    $isPriceDown = $diffAmount < 0;
                    $vendorName = $latestHistory ? $latestHistory->vendor_name : ($mat->default_vendor ?: 'ไม่ระบุ');
                    $invoiceNo = $latestHistory?->invoice_number;
                    $recordedDate = $latestHistory?->recorded_at ? $latestHistory->recorded_at->format('d/m/Y') : null;

                    // Normalize Scan Names & Store/Vendors (ฝั่งซ้าย: ชื่อสำหรับสแกน, ฝั่งขวา: ร้านที่สแกนตรงกัน)
                    $rawSns = is_array($mat->scan_names) ? $mat->scan_names : (json_decode($mat->scan_names ?? '[]', true) ?: []);
                    $normalizedScanNames = [];
                    $scanSearchTerms = [];
                    foreach ($rawSns as $snItem) {
                        if (is_array($snItem)) {
                            $snName = trim($snItem['name'] ?? '');
                            $snVendor = trim($snItem['vendor'] ?? '');
                        } else {
                            $snName = trim((string) $snItem);
                            $snVendor = '';
                        }
                        if ($snName !== '') {
                            $normalizedScanNames[] = ['name' => $snName, 'vendor' => $snVendor];
                            $scanSearchTerms[] = $snName . ($snVendor ? ' ' . $snVendor : '');
                        }
                    }

                    // Prepare JSON payload for Detail & Edit Modals
                    $modalPayload = [
                        'id' => $mat->id,
                        'name' => $mat->name,
                        'scan_names' => $normalizedScanNames,
                        'image_url' => $meta['image_url'],
                        'category_slug' => $meta['primary_cat'],
                        'purchase_unit' => $mat->purchase_unit,
                        'latest_cost_price' => (float) $mat->latest_cost_price,
                        'cost_per_stick' => (float) $mat->cost_per_stick,
                        'yield_sticks_per_unit' => (float) $mat->yield_sticks_per_unit,
                        'vendor_name' => $vendorName,
                        'invoice_number' => $invoiceNo,
                        'recorded_at' => $recordedDate,
                        'price_change_amount' => $diffAmount,
                        'price_change_percent' => $diffPercent,
                        'histories' => $mat->priceHistories->map(fn ($h) => [
                            'date' => $h->recorded_at->format('d/m/Y'),
                            'short_date' => $h->recorded_at->format('d/m'),
                            'vendor_name' => $h->vendor_name,
                            'invoice_number' => $h->invoice_number,
                            'purchase_unit_price' => (float) $h->purchase_unit_price,
                            'cost_per_stick' => (float) $h->cost_per_stick,
                            'previous_cost_price' => $h->previous_cost_price ? (float) $h->previous_cost_price : null,
                            'price_change_amount' => (float) $h->price_change_amount,
                            'price_change_percent' => (float) $h->price_change_percent,
                        ])->values()->all(),
                        'is_available_today' => (bool) $mat->is_available_today,
                        'is_bought_today' => (bool) $mat->is_bought_today,
                        'today_opening_sticks' => (int) $mat->today_opening_sticks,
                        'today_stock_sticks' => (int) $mat->today_stock_sticks,
                        'selling_price' => $mat->products->first() ? (float) $mat->products->first()->price_per_stick : null,
                        'is_approved_for_sale' => $mat->products->isNotEmpty() ? (bool) $mat->products->every(fn ($p) => $p->is_available) : false,
                    ];
                @endphp

                <article
                    id="mat-card-{{ $mat->id }}"
                    data-mat-id="{{ $mat->id }}"
                    data-name="{{ mb_strtolower($mat->name . ' ' . $vendorName . ' ' . implode(' ', $scanSearchTerms)) }}"
                    data-cats="{{ implode(',', $meta['cat_slugs']) }}"
                    data-price="{{ (float) $mat->latest_cost_price }}"
                    data-stick="{{ (float) $mat->cost_per_stick }}"
                    data-change="{{ abs($diffAmount) }}"
                    data-order="{{ $index }}"
                    data-available="{{ $mat->is_available_today ? '1' : '0' }}"
                    data-bought="{{ $mat->is_bought_today ? '1' : '0' }}"
                    data-opening="{{ (int) $mat->today_opening_sticks }}"
                    data-stock="{{ (int) $mat->today_stock_sticks }}"
                    data-modal='@json($modalPayload)'
                    onclick="handleMaterialCardClick(event, this)"
                    class="mat-card relative group bg-white rounded-[20px] p-3.5 sm:p-4 border {{ $mat->is_available_today ? 'border-slate-200/90 hover:border-lime-300' : 'border-slate-200/70 opacity-90' }} shadow-xs hover:shadow-md transition-all flex items-center gap-3.5 cursor-pointer"
                >
                    <!-- Multi-Select Delete Mode Checkbox (Pinned top-left) -->
                    <div class="delete-checkbox-wrapper absolute top-2.5 left-2.5 z-30">
                        <input
                            type="checkbox"
                            class="delete-item-checkbox w-6 h-6 rounded-lg text-[#E50914] bg-white border-2 border-slate-300 checked:bg-[#E50914] checked:border-[#E50914] focus:ring-0 shadow-md cursor-pointer transition"
                            data-id="{{ $mat->id }}"
                            data-name="{{ $mat->name }}"
                            data-img="{{ $meta['image_url'] }}"
                            onclick="handleDeleteCheckboxClick(event, this)"
                        >
                    </div>
                    <!-- A. Left Square 1:1 Ingredient Image -->
                    <div class="mat-thumb w-[96px] h-[96px] sm:w-[108px] sm:h-[108px] rounded-[16px] overflow-hidden shrink-0 bg-slate-100 border border-slate-200/70 shadow-2xs relative">
                        <img
                            src="{{ $meta['image_url'] }}"
                            alt="{{ $mat->name }}"
                            loading="lazy"
                            data-role="mat-img"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            onerror="this.onerror=null;this.src='{{ asset('assets/menu/food-fallback.svg') }}';"
                        >

                        <!-- Three-Lines (☰) Image Overlay Edit Trigger in Edit Mode -->
                        <button
                            type="button"
                            onclick="openMaterialEditModalByCard(event, this.closest('.mat-card'))"
                            title="แก้ไขรูปภาพและข้อมูล {{ $mat->name }}"
                            class="edit-mode-trigger absolute inset-0 bg-slate-900/55 hover:bg-slate-900/70 text-white flex-col items-center justify-center gap-1 transition-all cursor-pointer"
                        >
                            <span class="w-7 h-7 rounded-full bg-[#E50914] text-white flex items-center justify-center shadow-md border border-white/80">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                    <line x1="4" y1="6" x2="20" y2="6"></line>
                                    <line x1="4" y1="12" x2="20" y2="12"></line>
                                    <line x1="4" y1="18" x2="20" y2="18"></line>
                                </svg>
                            </span>
                            <span class="text-[10px] font-semibold tracking-tight">แก้ไขรูป/ข้อมูล</span>
                        </button>
                    </div>

                    <!-- B–G. Middle Information & Price Comparison Content -->
                    <div class="min-w-0 flex-1 flex flex-col justify-between">
                        <!-- Top Row: Ingredient Name + Unit & Status Badges Column -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <h3 class="mat-title font-bold text-[15px] sm:text-[15.5px] text-[#14171F] leading-snug truncate" data-role="mat-name" title="{{ $mat->name }}">
                                    {{ $mat->name }}
                                </h3>
                                <span class="text-[11.5px] text-[#687082] font-normal block leading-tight mt-0.5 truncate" data-role="mat-unit">
                                    หน่วยซื้อ: {{ $mat->purchase_unit }}
                                </span>
                            </div>

                            <!-- D. Status Badges Column: Price Change Badge + Availability Badge (Stacked neatly on right) -->
                            <div class="shrink-0 flex flex-col items-end gap-1" data-role="status-badge">
                                @if($diffAmount != 0)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-bold inline-flex items-center gap-1 {{ $isPriceUp ? 'bg-[#FDE8EA] text-[#E60023]' : 'bg-[#DDF8E8] text-[#15803D]' }}">
                                        <span>{{ $isPriceUp ? '▲ ขึ้น' : '▼ ลด' }}</span>
                                        <span>{{ rtrim(rtrim(number_format(abs($diffAmount), 2), '0'), '.') }} บ.</span>
                                        <span>({{ rtrim(rtrim(number_format(abs($diffPercent), 1), '0'), '.') }}%)</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-semibold bg-[#F1EFF8] text-[#534B6E] inline-flex items-center gap-1">
                                        <span>▬ ราคาคงเดิม</span>
                                    </span>
                                @endif

                                <!-- Availability Status Badge (ซื้อวันนี้ vs สต็อกคงเหลือ vs ไม่พบรายการ) -->
                                @if($mat->is_bought_today)
                                    <span
                                        class="mat-avail-badge px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#D4F435] text-[#143D02] border border-[#BDEB18] shadow-2xs inline-flex items-center gap-1 select-none whitespace-nowrap"
                                        title="ซื้อวันนี้: มีรายการสั่งซื้อในใบเสร็จวันนี้"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#15803D] animate-pulse"></span>
                                        <span>ซื้อวันนี้</span>
                                    </span>
                                @elseif($mat->is_available_today)
                                    <span
                                        class="mat-avail-badge px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/90 shadow-2xs inline-flex items-center gap-1 select-none whitespace-nowrap"
                                        title="สต็อกพร้อมขาย: ซิงค์จากสต็อกไม้คงเหลือ (ยกมา {{ $mat->today_opening_sticks }} ไม้ • ปัจจุบัน {{ $mat->today_stock_sticks }} ไม้)"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>สต็อกพร้อมขาย</span>
                                    </span>
                                @else
                                    <span
                                        class="mat-avail-badge px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-400 border border-slate-200/90 inline-flex items-center gap-1 select-none whitespace-nowrap"
                                        title="ไม่มีรายการวันนี้: ไม่พบการซื้อในวันนี้ และไม่มีสต็อกไม้คงเหลือ"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                        <span>ไม่พบรายการ</span>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- E. Two-Column Grey Price Comparison Box -->
                        <div class="grid grid-cols-2 gap-2 my-2 px-3 py-1.5 bg-[#F4F6F9] rounded-[12px] border border-slate-200/60">
                            <div class="min-w-0">
                                <span class="text-[10.5px] text-[#687082] font-medium block leading-tight">ราคาซื้อล่าสุด:</span>
                                <div class="truncate mt-0.5">
                                    <span class="mat-price-val text-[14.5px] font-bold text-[#14171F]" data-role="latest-price">฿{{ $formatLatestPrice($mat->latest_cost_price) }}</span>
                                    <span class="text-[10.5px] text-[#687082] font-normal" data-role="latest-unit-suffix">/{{ $mat->purchase_unit }}</span>
                                </div>
                            </div>
                            <div class="min-w-0 border-l border-slate-200/80 pl-2.5">
                                <span class="text-[10.5px] text-[#687082] font-medium block leading-tight">ต้นทุนต่อไม้:</span>
                                <div class="truncate mt-0.5">
                                    <span class="mat-price-val text-[14.5px] font-bold text-[#E60023]" data-role="stick-cost">฿{{ number_format($mat->cost_per_stick, 2) }}</span>
                                    <span class="text-[10.5px] text-[#687082] font-normal" data-role="yield-suffix">({{ number_format($mat->yield_sticks_per_unit, 2) }} ไม้/หน่วย)</span>
                                </div>
                            </div>
                        </div>

                        <!-- F & G. Reference Store + 5-Record Price History Summary -->
                        <div class="space-y-0.5 text-[11.5px] leading-snug">
                            <div class="flex items-center gap-1.5 text-[#525866] truncate">
                                <svg class="w-3.5 h-3.5 text-[#687082] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 9l1.5-5h15L21 9"></path>
                                    <path d="M3 9c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3s3-1.3 3-3c0 1.7 1.3 3 3 3s3-1.3 3-3"></path>
                                    <path d="M5 12v8a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-8"></path>
                                </svg>
                                <span class="text-[#687082]">ซื้อล่าสุดจาก:</span>
                                <strong class="font-bold text-[#14171F] truncate" data-role="vendor-name">{{ $vendorName }}</strong>
                                @if($invoiceNo)
                                    <span class="text-[10.5px] text-slate-400 hidden sm:inline">• อ้างอิงบิล: {{ $invoiceNo }} ({{ $recordedDate }})</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-1.5 text-[#525866] truncate" data-role="history-summary">
                                <svg class="w-3.5 h-3.5 text-[#687082] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                </svg>
                                <span class="text-[#687082] shrink-0">ประวัติราคา 5 ครั้งล่าสุด:</span>
                                @if($latestHistory)
                                    <span class="truncate text-[#2E3440]">
                                        {{ $latestHistory->recorded_at->format('d/m/Y') }} • {{ Str::limit($latestHistory->vendor_name, 14) }}
                                        <strong class="font-bold text-[#14171F]">
                                            ฿{{ number_format($latestHistory->purchase_unit_price, 2) }}
                                            @if($latestHistory->previous_cost_price)
                                                (เดิม ฿{{ number_format($latestHistory->previous_cost_price, 2) }})
                                            @else
                                                (ไม้ละ ฿{{ number_format($latestHistory->cost_per_stick, 2) }})
                                            @endif
                                        </strong>
                                    </span>
                                @else
                                    <span class="text-[#8A92A6] truncate">ยังไม่มีประวัติราคา รอสแกนบิลใหม่</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- H. Right Action Column: Normal (>) Detail Button + Edit Mode Three-Lines (☰) Button -->
                    <div class="flex flex-col items-center gap-1.5 shrink-0">
                        <!-- Three-Lines (☰) Edit & Delete Options Button for this Item -->
                        <button
                            type="button"
                            onclick="openCardActionMenu(event, this.closest('.mat-card'))"
                            title="ตัวเลือกแก้ไข/ลบรายการ {{ $mat->name }} (☰)"
                            aria-label="ตัวเลือกแก้ไข/ลบรายการ {{ $mat->name }}"
                            class="edit-mode-trigger w-8 h-8 rounded-full bg-[#E50914] hover:bg-[#C8102E] text-white items-center justify-center shrink-0 shadow-md border border-white cursor-pointer transition-transform hover:scale-105"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <line x1="4" y1="6" x2="20" y2="6"></line>
                                <line x1="4" y1="12" x2="20" y2="12"></line>
                                <line x1="4" y1="18" x2="20" y2="18"></line>
                            </svg>
                        </button>

                        <!-- Standard Detail Chevron (>) Button -->
                        <button
                            type="button"
                            aria-label="ดูรายละเอียด {{ $mat->name }}"
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#FDE8EA] group-hover:bg-[#E50914] text-[#E50914] group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs"
                        >
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </button>
                    </div>
                </article>
            @endforeach
        </div>

        <!-- Empty Filter State -->
        <div id="materials-empty-state" class="hidden bg-white rounded-3xl border border-slate-200/80 p-10 text-center">
            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-[#E50914] flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path stroke-linecap="round" d="m21 21-4.3-4.3"></path>
                </svg>
            </div>
            <p class="text-[15px] font-bold text-slate-800">ไม่พบรายการวัตถุดิบที่ตรงกับการค้นหา</p>
            <p class="text-[12.5px] text-slate-500 mt-1">ลองเลือกหมวดหมู่ "ทั้งหมด" หรือล้างคำค้นหาเพื่อดูรายการทั้งหมด</p>
            <button
                type="button"
                onclick="resetMaterialFilters()"
                class="mt-4 px-4 py-2 rounded-xl bg-[#E50914] text-white text-xs font-semibold hover:bg-[#C8102E] transition-all cursor-pointer"
            >
                แสดงวัตถุดิบทั้งหมด
            </button>
        </div>

        <!-- 4. COMPLETE PRICE FLUCTUATIONS LEDGER TABLE -->
        <section id="price-ledger-section" class="bg-white rounded-[22px] border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                <div class="flex items-center gap-2.5">
                    <span class="w-1.5 h-5 rounded-full bg-[#E50914]"></span>
                    <h3 class="font-bold text-[15px] text-[#14171F]">ตารางบันทึกความเปลี่ยนแปลงของราคาต้นทุนและร้านค้าอ้างอิง</h3>
                </div>
                <span class="text-xs text-slate-400">ตรวจจับอัตโนมัติจากการสแกนบิลใบเสร็จ</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-100 font-semibold">
                        <tr>
                            <th class="py-3 px-4">วันที่บันทึก</th>
                            <th class="py-3 px-4">ชื่อวัตถุดิบ</th>
                            <th class="py-3 px-4">ร้านค้า / ซัพพลายเออร์</th>
                            <th class="py-3 px-4">เลขที่ใบเสร็จ</th>
                            <th class="py-3 px-4">ราคาซื้อต่อหน่วย</th>
                            <th class="py-3 px-4">แปลงเป็นไม้ (Yield)</th>
                            <th class="py-3 px-4">ต้นทุนต่อไม้</th>
                            <th class="py-3 px-4 text-right">ส่วนต่างราคา</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($priceHistories as $ph)
                            @php
                                $diff = $ph->price_change_amount;
                                $pct = $ph->price_change_percent;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4 text-slate-500">
                                    {{ $ph->recorded_at->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    {{ $ph->rawMaterial->name }}
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-700">
                                    🏬 {{ $ph->vendor_name }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    {{ $ph->invoice_number ?: '-' }}
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    ฿{{ number_format($ph->purchase_unit_price, 2) }}
                                    <span class="text-[10px] text-slate-400 font-normal">/{{ $ph->purchase_unit }}</span>
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $ph->yield_sticks_per_unit }} ไม้/{{ $ph->purchase_unit }}
                                </td>
                                <td class="py-3 px-4 font-bold text-[#E60023]">
                                    ฿{{ number_format($ph->cost_per_stick, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if($diff != 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded font-bold text-[11px] {{ $diff > 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }} บ. ({{ $diff > 0 ? '+' : '' }}{{ number_format($pct, 1) }}%)
                                        </span>
                                    @else
                                        <span class="text-slate-400">คงเดิม</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">ยังไม่มีบันทึกประวัติราคา</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $priceHistories->links() }}
            </div>
        </section>
    </div>
</div>

<!-- 5. INGREDIENT PRICE DETAIL MODAL (VIEW DETAILS) -->
<div
    id="mat-detail-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
    onclick="if(event.target === this) closeMaterialDetailModal()"
>
    <div class="bg-white rounded-[24px] max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden">
        <!-- Modal Header -->
        <div class="p-5 bg-gradient-to-r from-[#4A0408] via-[#8B0912] to-[#4A0408] text-white flex items-center justify-between gap-3">
            <div class="flex items-center gap-3.5 min-w-0">
                <img
                    id="modal-mat-img"
                    src=""
                    alt=""
                    class="w-14 h-14 rounded-2xl object-cover border-2 border-white/80 shadow-md shrink-0 bg-white"
                >
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap mb-1">
                        <span id="modal-mat-badge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/20 text-white"></span>
                        <span id="modal-mat-avail-badge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold"></span>
                        <span id="modal-mat-sale-badge" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold"></span>
                    </div>
                    <h3 id="modal-mat-name" class="text-[18px] font-bold leading-snug truncate"></h3>
                    <p id="modal-mat-unit" class="text-[12px] text-rose-100/90 font-normal"></p>
                </div>
            </div>
            <button
                type="button"
                onclick="closeMaterialDetailModal()"
                class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center shrink-0 cursor-pointer transition"
            >
                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-5 space-y-4">
            <div class="grid grid-cols-3 gap-2.5 bg-[#F4F6F9] p-3.5 rounded-2xl border border-slate-200/70">
                <div>
                    <span class="text-[11px] text-[#687082] block">ราคาซื้อล่าสุด</span>
                    <strong id="modal-mat-price" class="text-[16px] font-bold text-[#14171F]"></strong>
                </div>
                <div class="border-l border-slate-200 pl-3">
                    <span class="text-[11px] text-[#687082] block">ต้นทุนต่อไม้</span>
                    <strong id="modal-mat-stick" class="text-[16px] font-bold text-[#E60023]"></strong>
                </div>
                <div class="border-l border-slate-200 pl-3">
                    <span class="text-[11px] text-[#687082] block">จำนวนไม้ที่ได้</span>
                    <strong id="modal-mat-yield" class="text-[14px] font-bold text-[#14171F]"></strong>
                </div>
            </div>

            <div class="flex items-center justify-between text-[13px] bg-amber-50/60 border border-amber-200/70 rounded-xl px-4 py-2.5">
                <span class="text-slate-600 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span>ร้านค้าอ้างอิงล่าสุด:</span>
                </span>
                <strong id="modal-mat-vendor" class="font-bold text-slate-900"></strong>
            </div>

            <!-- Scan Aliases in Detail Modal (ชื่อสำหรับสแกน & ร้านค้าที่ตรงกัน) -->
            <div id="modal-mat-scan-names-wrap" class="hidden text-xs bg-slate-50 border border-slate-200/80 rounded-xl p-3">
                <span class="text-slate-600 font-semibold block mb-1.5 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                    <span class="font-bold text-slate-700">ชื่อสำหรับสแกน & ร้านค้าที่ตรวจพบ:</span>
                </span>
                <div id="modal-mat-scan-names-list" class="flex flex-wrap gap-1.5"></div>
            </div>

            <div>
                <h4 class="text-[12.5px] font-bold text-slate-700 mb-2 flex items-center justify-between">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                            <polyline points="17 6 23 6 23 12"></polyline>
                        </svg>
                        <span>ประวัติราคา 5 ครั้งล่าสุด</span>
                    </span>
                    <span id="modal-mat-invoice" class="text-[11px] font-normal text-slate-400"></span>
                </h4>
                <div id="modal-mat-history-list" class="space-y-2 max-h-52 overflow-y-auto pr-1"></div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Approval Checkmark Button (ปุ่มติ๊กถูกอนุมัติแสดงผล: สั่งออนไลน์ & แคชเชียร์) -->
                <button
                    type="button"
                    id="btn-toggle-mat-approval"
                    onclick="toggleCurrentMaterialApproval()"
                    class="w-9 h-9 rounded-xl transition-all shadow-xs cursor-pointer select-none inline-flex items-center justify-center shrink-0"
                    title="อนุมัติแสดงผล (สั่งออนไลน์ & แคชเชียร์)"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                </button>

                <button
                    type="button"
                    onclick="switchFromDetailToEditModal()"
                    class="py-2 px-3.5 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs transition-all cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
                        <line x1="4" y1="6" x2="20" y2="6"></line>
                        <line x1="4" y1="12" x2="20" y2="12"></line>
                        <line x1="4" y1="18" x2="20" y2="18"></line>
                    </svg>
                    <span>แก้ไขข้อมูล / รูปภาพ</span>
                </button>
                <button
                    type="button"
                    onclick="openDeleteSingleMaterialConfirmFromDetail()"
                    class="py-2 px-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-700 text-xs font-semibold inline-flex items-center gap-1.5 transition-all cursor-pointer border border-rose-200/60"
                    title="ลบรายการวัตถุดิบนี้"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <span>ลบรายการ</span>
                </button>
            </div>
            <button
                type="button"
                onclick="closeMaterialDetailModal()"
                class="py-2 px-4 rounded-xl bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold cursor-pointer shrink-0"
            >
                ปิดหน้าต่าง
            </button>
        </div>
    </div>
</div>

<!-- 6. POPUP 1: INGREDIENT ITEM & IMAGE EDIT MODAL (ป๊อปอัปแก้ไขชื่อ รายละเอียด และรูปภาพของรายการวัตถุดิบ) -->
<div
    id="mat-edit-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/65 backdrop-blur-xs p-4 overflow-y-auto"
    onclick="if(event.target === this) closeMaterialEditModal()"
>
    <div class="bg-white rounded-[24px] max-w-2xl w-full shadow-2xl border border-slate-100 overflow-hidden my-auto">
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-[#3E0206] via-[#8B0912] to-[#3E0206] text-white flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </span>
                <div>
                    <h3 id="mat-edit-modal-title" class="text-[17px] font-bold leading-snug">แก้ไขข้อมูลและรูปภาพวัตถุดิบ</h3>
                    <p id="mat-edit-modal-subtitle" class="text-[11.5px] text-rose-100/85 font-normal">ปรับแก้ชื่อ หน่วยซื้อ ราคา ต้นทุนต่อไม้ ร้านค้าอ้างอิง และรูปภาพได้ทันที</p>
                </div>
            </div>
            <button
                type="button"
                onclick="closeMaterialEditModal()"
                class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center shrink-0 cursor-pointer transition"
            >
                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Edit Form -->
        <form id="mat-edit-form" onsubmit="submitMaterialEditForm(event)" class="p-6 space-y-4">
            <input type="hidden" id="edit-mat-id" name="id">

            <!-- Image Preview & Upload Row -->
            <div class="flex flex-col sm:flex-row items-center gap-4 p-4 rounded-2xl bg-[#F8F9FB] border border-slate-200/80">
                <div class="w-24 h-24 rounded-2xl overflow-hidden bg-white border-2 border-slate-200 shadow-xs shrink-0 relative">
                    <img
                        id="edit-mat-preview-img"
                        src="{{ asset('assets/ingredients/fishball-phuket.png') }}"
                        alt="Preview"
                        class="w-full h-full object-cover"
                    >
                </div>

                <div class="flex-1 w-full space-y-2">
                    <label class="text-xs font-bold text-slate-700 block">รูปภาพวัตถุดิบ (1:1)</label>
                    <div class="flex flex-wrap items-center gap-2">
                        <label class="px-3.5 py-1.5 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-semibold cursor-pointer inline-flex items-center gap-1.5 shadow-2xs transition-all">
                            <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                <circle cx="12" cy="13" r="4"></circle>
                            </svg>
                            <span>อัปโหลดรูปใหม่</span>
                            <input
                                type="file"
                                id="edit-mat-file-input"
                                name="image_file"
                                accept="image/*"
                                onchange="previewMaterialUploadFile(this)"
                                class="hidden"
                            >
                        </label>
                        <input
                            type="text"
                            id="edit-mat-image-url"
                            name="image_url"
                            oninput="document.getElementById('edit-mat-preview-img').src = this.value"
                            placeholder="หรือวางลิงก์รูปภาพ (URL)..."
                            class="flex-1 min-w-[160px] px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 focus:outline-none focus:border-[#E50914]"
                        >
                    </div>

                    <!-- Quick Preset Image Picker -->
                    <div>
                        <span class="text-[11px] text-slate-400 block mb-1">หรือเลือกรูปวัตถุดิบมาตรฐาน:</span>
                        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
                            @foreach($presetIngredientImages as $preset)
                                <button
                                    type="button"
                                    onclick="selectPresetMaterialImage('{{ $preset['url'] }}')"
                                    title="{{ $preset['label'] }}"
                                    class="w-9 h-9 rounded-lg overflow-hidden border border-slate-200 hover:border-[#E50914] shrink-0 transition-all cursor-pointer"
                                >
                                    <img src="{{ $preset['url'] }}" alt="{{ $preset['label'] }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Name & Category Row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="text-xs font-bold text-slate-700 block mb-1">ชื่อวัตถุดิบ <span class="text-[#E50914]">*</span></label>
                    <input
                        type="text"
                        id="edit-mat-name"
                        name="name"
                        required
                        class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-800 focus:outline-none focus:border-[#E50914]"
                    >
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">หมวดหมู่</label>
                    <select
                        id="edit-mat-category"
                        name="category_slug"
                        class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-sm font-medium text-slate-700 focus:outline-none focus:border-[#E50914]"
                    >
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Scan Aliases Section: ฝั่งซ้าย: ชื่อสำหรับสแกน • ฝั่งขวา: แสดงชื่อร้านที่สแกนตรงกัน -->
            <div class="p-3.5 rounded-2xl bg-[#F8FAFC] border border-slate-200/90 space-y-2.5">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <label class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-600 border border-slate-200/80 flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                </svg>
                            </span>
                            <span class="text-[13px] font-bold text-slate-900">ชื่อสำหรับสแกน & ร้านค้าที่ตรงกัน</span>
                            <span class="text-[10px] font-semibold text-slate-400">(ใช้จับคู่บิล / ใบเสร็จ)</span>
                        </label>
                        <p class="text-[11px] text-slate-500 font-normal leading-tight mt-0.5">
                            แบ่ง 2 ฝั่ง — <strong>ฝั่งซ้าย:</strong> ชื่อสำหรับสแกนบนใบเสร็จ • <strong>ฝั่งขวา:</strong> แสดงชื่อร้านที่สแกนตรงกับชื่อไหนจะแสดงชื่อร้านนั้น เพื่อจับคู่ตรงเป๊ะ
                        </p>
                    </div>
                    <button
                        type="button"
                        onclick="addScanNameInputRow('', '')"
                        class="px-2.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 hover:text-slate-900 border border-slate-200 text-xs font-semibold inline-flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer shrink-0"
                    >
                        <svg class="w-3.5 h-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>เพิ่มชื่อ</span>
                    </button>
                </div>

                <!-- Sub-labels for the 2 columns -->
                <div class="flex items-center gap-2 px-1 text-[11px] font-semibold text-slate-400">
                    <div class="flex-1 flex items-center gap-1">
                        <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <span>ฝั่งซ้าย: ชื่อสำหรับสแกนบนใบเสร็จ</span>
                    </div>
                    <div class="w-44 sm:w-52 flex items-center gap-1">
                        <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span>ฝั่งขวา: ร้านที่สแกนตรงกัน</span>
                    </div>
                    <div class="w-7 text-center"></div>
                </div>

                <!-- Dynamic Container for Scan Name & Vendor input rows -->
                <div id="edit-mat-scan-names-container" class="space-y-1.5 pt-0.5">
                    <!-- Dynamic input rows with delete [✕] buttons inserted here -->
                </div>
            </div>

            <!-- Datalist for Quick Vendor / Store Presets -->
            <datalist id="vendor-presets-datalist">
                <option value="โลตัส เอ็กซ์เพรส">
                <option value="โลตัส โกเฟรช">
                <option value="สยามแม็คโคร">
                <option value="บิ๊กซี ซูเปอร์เซ็นเตอร์">
                <option value="เบทาโกร ช็อป">
                <option value="มายด์ฟู้ด">
                <option value="ฟาร์มโคขุนกำแพงแสน">
                <option value="ตลาดเยาวราช">
                <option value="ตลาดสี่มุมเมือง">
                <option value="ซีพี ออลล์">
            </datalist>

            <!-- Unit & Reference Vendor Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">หน่วยซื้อ <span class="text-[#E50914]">*</span></label>
                    <input
                        type="text"
                        id="edit-mat-unit"
                        name="purchase_unit"
                        required
                        placeholder="เช่น แพ็ก (500g), กิโลกรัม, ถุง"
                        class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-sm text-slate-800 focus:outline-none focus:border-[#E50914]"
                    >
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">ร้านค้าอ้างอิง / ซัพพลายเออร์</label>
                    <input
                        type="text"
                        id="edit-mat-vendor"
                        name="default_vendor"
                        placeholder="เช่น สยามแม็คโคร, โลตัส, มายด์ฟู้ด"
                        class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-sm text-slate-800 focus:outline-none focus:border-[#E50914]"
                    >
                </div>
            </div>

            <!-- Price, Yield, Stick Cost Row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-3.5 rounded-2xl bg-[#F4F6F9] border border-slate-200/80">
                <div>
                    <label class="text-[11.5px] font-bold text-slate-700 block mb-1">ราคาซื้อล่าสุด (฿)</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        id="edit-mat-price"
                        name="latest_cost_price"
                        required
                        oninput="recalculateModalStickCost()"
                        class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-900 focus:outline-none focus:border-[#E50914]"
                    >
                </div>

                <div>
                    <label class="text-[11.5px] font-bold text-slate-700 block mb-1">จำนวนไม้ที่ได้ / หน่วย</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0.01"
                        id="edit-mat-yield"
                        name="yield_sticks_per_unit"
                        required
                        oninput="recalculateModalStickCost()"
                        class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-sm font-bold text-slate-900 focus:outline-none focus:border-[#E50914]"
                    >
                </div>

                <div>
                    <label class="text-[11.5px] font-bold text-[#E60023] block mb-1">ต้นทุนต่อไม้ (฿/ไม้)</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        id="edit-mat-stick"
                        name="cost_per_stick"
                        required
                        class="w-full px-3 py-2 rounded-xl bg-white border border-rose-200 text-sm font-bold text-[#E60023] focus:outline-none focus:border-[#E50914]"
                    >
                </div>
            </div>

            <!-- Selling Price Row (ต่อด้านล่าง ตรงตามภาพที่ 2 เป๊ะๆ) -->
            <div class="w-full sm:max-w-[210px]">
                <label class="text-[11.5px] font-bold text-[#101114] block mb-1">ราคาขายต่อไม้ (฿/ไม้)</label>
                <input
                    type="number"
                    step="1"
                    min="0"
                    id="edit-mat-selling-price"
                    name="selling_price"
                    placeholder="เช่น 15, 20"
                    class="w-full px-3 py-2 rounded-xl bg-white border border-slate-300 text-sm font-bold text-[#101114] focus:outline-none focus:border-[#E50914]"
                >
            </div>

            <!-- Record Price Change Checkbox -->
            <label class="flex items-center gap-2.5 text-xs text-slate-600 cursor-pointer select-none pt-1">
                <input
                    type="checkbox"
                    id="edit-mat-record-history"
                    name="record_history"
                    value="1"
                    checked
                    class="w-4 h-4 rounded text-[#E50914] focus:ring-[#E50914]"
                >
                <span>หากมีการเปลี่ยนราคาซื้อ ให้บันทึกเป็นประวัติราคาใหม่และคำนวณป้าย <strong>▲ ขึ้น / ▼ ลด</strong> อัตโนมัติ</span>
            </label>

            <!-- Footer Actions -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button
                    type="button"
                    onclick="closeMaterialEditModal()"
                    class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold cursor-pointer"
                >
                    ยกเลิก
                </button>
                <button
                    type="submit"
                    id="edit-mat-submit-btn"
                    class="px-5 py-2.5 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-bold shadow-md shadow-[#E50914]/25 inline-flex items-center gap-2 cursor-pointer transition-all"
                >
                    <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>บันทึกการแก้ไขรายการ</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 7. POPUP 2: HERO COVER IMAGE & BANNER INFO EDIT MODAL (ป๊อปอัปแก้ไขภาพปกส่วนหัวตามภาพตัวอย่างที่ 2) -->
<div
    id="hero-edit-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/65 backdrop-blur-xs p-4 overflow-y-auto"
    onclick="if(event.target === this) closeHeroEditModal()"
>
    <div class="bg-white rounded-[24px] max-w-xl w-full shadow-2xl border border-slate-100 overflow-hidden my-auto">
        <!-- Modal Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-[#3E0206] via-[#8B0912] to-[#3E0206] text-white flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round">
                        <line x1="4" y1="6" x2="20" y2="6"></line>
                        <line x1="4" y1="12" x2="20" y2="12"></line>
                        <line x1="4" y1="18" x2="20" y2="18"></line>
                    </svg>
                </span>
                <div>
                    <h3 class="text-[17px] font-bold leading-snug">แก้ไขภาพปกและแบนเนอร์ส่วนหัว</h3>
                    <p class="text-[11.5px] text-rose-100/85 font-normal">เปลี่ยนภาพปกหม่าล่าสตรีทด้านบนสุด และแก้ไขข้อความกล่องข้อมูลด้านขวา</p>
                </div>
            </div>
            <button
                type="button"
                onclick="closeHeroEditModal()"
                class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center shrink-0 cursor-pointer"
            >
                ✕
            </button>
        </div>

        <!-- Hero Edit Form -->
        <form id="hero-edit-form" onsubmit="submitHeroEditForm(event)" class="p-6 space-y-4">
            <!-- Cover Preview -->
            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-700 block">ตัวอย่างภาพปกปัจจุบัน</label>
                <div class="w-full h-[145px] rounded-2xl overflow-hidden bg-gradient-to-r from-[#3E0206] via-[#8B0912] to-[#3E0206] border border-slate-200 shadow-inner flex items-center justify-center">
                    <img
                        id="edit-hero-preview-img"
                        src="{{ $heroImage }}"
                        alt="Hero Cover Preview"
                        class="w-full h-full object-cover object-left"
                    >
                </div>
            </div>

            <!-- Upload or URL -->
            <div class="p-4 rounded-2xl bg-[#F8F9FB] border border-slate-200/80 space-y-3">
                <div class="flex flex-wrap items-center gap-2">
                    <label class="px-4 py-2 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-semibold cursor-pointer inline-flex items-center gap-1.5 shadow-2xs transition-all">
                        <span>🖼️ อัปโหลดภาพปกใหม่จากเครื่อง</span>
                        <input
                            type="file"
                            id="edit-hero-file-input"
                            name="hero_image_file"
                            accept="image/*"
                            onchange="previewHeroUploadFile(this)"
                            class="hidden"
                        >
                    </label>

                    <button
                        type="button"
                        onclick="selectPresetHeroImage('{{ asset('assets/ingredients/hero-price-art-left.png') }}')"
                        class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:border-[#E50914] text-slate-700 text-xs font-medium cursor-pointer transition-all"
                    >
                        ใช้ภาพปกต้นฉบับ
                    </button>

                    <button
                        type="button"
                        onclick="selectPresetHeroImage('{{ asset('assets/brand/hero-mala.png') }}')"
                        class="px-3 py-2 rounded-xl bg-white border border-slate-200 hover:border-[#E50914] text-slate-700 text-xs font-medium cursor-pointer transition-all"
                    >
                        ใช้ภาพปกหม่าล่าเสียบไม้
                    </button>
                </div>

                <div>
                    <label class="text-[11px] font-medium text-slate-500 block mb-1">หรือระบุลิงก์ภาพปก (Image URL):</label>
                    <input
                        type="text"
                        id="edit-hero-image-url"
                        name="hero_image_url"
                        value="{{ $heroImage }}"
                        oninput="document.getElementById('edit-hero-preview-img').src = this.value"
                        class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 focus:outline-none focus:border-[#E50914]"
                    >
                </div>
            </div>

            <!-- Right Info Card Title & Subtitle -->
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">หัวข้อกล่องข้อมูลด้านขวา</label>
                    <input
                        type="text"
                        id="edit-hero-title"
                        name="hero_title"
                        value="{{ $heroTitle }}"
                        class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-800 focus:outline-none focus:border-[#E50914]"
                    >
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-700 block mb-1">คำอธิบายย่อย</label>
                    <textarea
                        id="edit-hero-subtitle"
                        name="hero_subtitle"
                        rows="2"
                        class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 focus:outline-none focus:border-[#E50914]"
                    >{{ $heroSubtitle }}</textarea>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button
                    type="button"
                    onclick="closeHeroEditModal()"
                    class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold cursor-pointer"
                >
                    ยกเลิก
                </button>
                <button
                    type="submit"
                    id="edit-hero-submit-btn"
                    class="px-5 py-2.5 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-bold shadow-md shadow-[#E50914]/25 inline-flex items-center gap-2 cursor-pointer transition-all"
                >
                    <span>💾 บันทึกภาพปกและข้อมูล</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 8. POPUP 3: CREATE NEW CATEGORY MODAL (ป๊อปอัปสร้างแถบหมวดหมู่ใหม่และใส่รูปภาพ) -->
<div
    id="category-create-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/65 backdrop-blur-xs p-4 overflow-y-auto"
    onclick="if(event.target === this) closeCreateCategoryModal()"
>
    <div class="bg-white rounded-[24px] max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-[#8B0912] via-[#B91C1C] to-[#E50914] text-white flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </span>
                <div>
                    <h3 class="text-[17px] font-bold leading-snug">สร้างแถบหมวดหมู่ใหม่</h3>
                    <p class="text-[11.5px] text-rose-100/90 font-normal">เพิ่มแถบหมวดหมู่สำหรับจัดกลุ่มวัตถุดิบและเมนูในร้าน</p>
                </div>
            </div>
            <button
                type="button"
                onclick="closeCreateCategoryModal()"
                class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center shrink-0 cursor-pointer transition"
            >
                ✕
            </button>
        </div>

        <!-- Form -->
        <form id="category-create-form" onsubmit="submitCreateCategoryForm(event)" class="p-6 space-y-4">
            @csrf

            <!-- Category Image Preview & Upload -->
            <div class="p-4 rounded-2xl bg-[#F8F9FB] border border-slate-200/80 space-y-3">
                <div class="flex items-center gap-3.5">
                    <div class="w-16 h-16 rounded-full overflow-hidden bg-white border-2 border-slate-200 shadow-xs shrink-0 relative flex items-center justify-center">
                        <img
                            id="create-cat-preview-img"
                            src="{{ asset('assets/categories/all.png') }}"
                            alt="Category Preview"
                            class="w-full h-full object-cover"
                        >
                    </div>
                    <div class="flex-1 min-w-0 space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">รูปภาพไอคอนหมวดหมู่ (1:1)</label>
                        <div class="flex flex-wrap items-center gap-2">
                            <label class="px-3 py-1.5 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-semibold cursor-pointer inline-flex items-center gap-1.5 shadow-2xs transition-all">
                                <span>📷 เลือกภาพจากเครื่อง</span>
                                <input
                                    type="file"
                                    id="create-cat-file-input"
                                    name="category_image"
                                    accept="image/*"
                                    onchange="previewCategoryUploadFile(this)"
                                    class="hidden"
                                >
                            </label>
                            <input
                                type="text"
                                id="create-cat-image-url"
                                name="category_image_url"
                                oninput="updateCategoryPreviewFromUrl(this.value)"
                                placeholder="หรือวางลิงก์รูปภาพ (URL)..."
                                class="flex-1 min-w-[150px] px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 focus:outline-none focus:border-[#E50914]"
                            >
                        </div>
                    </div>
                </div>

                <!-- Preset Category Icons Quick Picker -->
                <div>
                    <span class="text-[11px] text-slate-400 block mb-1.5">หรือเลือกรูปหมวดหมู่ตัวอย่าง:</span>
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                        @php
                            $catPresets = [
                                ['url' => asset('assets/categories/beef.png'), 'label' => 'เนื้อโคขุน'],
                                ['url' => asset('assets/categories/pork-chicken.png'), 'label' => 'หมู & ไก่'],
                                ['url' => asset('assets/categories/meatballs.png'), 'label' => 'ลูกชิ้น'],
                                ['url' => asset('assets/categories/vegetables.png'), 'label' => 'ผักสด'],
                                ['url' => asset('assets/categories/tofu.png'), 'label' => 'เต้าหู้'],
                                ['url' => asset('assets/categories/snacks.png'), 'label' => 'ของทานเล่น'],
                                ['url' => asset('assets/categories/drinks.png'), 'label' => 'เครื่องดื่ม'],
                                ['url' => asset('assets/categories/all.png'), 'label' => 'รวม'],
                            ];
                        @endphp
                        @foreach($catPresets as $preset)
                            <button
                                type="button"
                                onclick="selectPresetCategoryImage('{{ $preset['url'] }}')"
                                title="{{ $preset['label'] }}"
                                class="w-10 h-10 rounded-full overflow-hidden border-2 border-slate-200 hover:border-[#E50914] shrink-0 transition-all cursor-pointer hover:scale-105 shadow-2xs"
                            >
                                <img src="{{ $preset['url'] }}" alt="{{ $preset['label'] }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Category Name -->
            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1">ชื่อแถบหมวดหมู่ <span class="text-[#E50914]">*</span></label>
                <input
                    type="text"
                    id="create-cat-name"
                    name="name"
                    required
                    placeholder="เช่น ซีฟู้ด & อาหารทะเล, น้ำซุป, เส้นและวุ้นเส้น..."
                    oninput="autoGenerateCategorySlug(this.value)"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-800 focus:outline-none focus:border-[#E50914] shadow-2xs"
                >
            </div>

            <!-- Category Slug (English identifier) -->
            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1">รหัสหมวดหมู่ (Slug / ภาษาอังกฤษ)</label>
                <input
                    type="text"
                    id="create-cat-slug"
                    name="slug"
                    placeholder="เช่น seafood, soup, noodles..."
                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono text-slate-600 focus:outline-none focus:border-[#E50914]"
                >
                <p class="text-[11px] text-slate-400 mt-0.5">ใช้สำหรับอ้างอิงรหัสหมวดหมู่ (ระบบจะสร้างให้อัตโนมัติหากเว้นว่าง)</p>
            </div>

            <!-- Approval Checkbox Toggle (ปุ่มติ๊กถูกอนุมัติแสดงผล: สั่งออนไลน์ & แคชเชียร์) -->
            <div class="p-3.5 rounded-2xl bg-emerald-50/90 border border-emerald-200/90 flex items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    </span>
                    <div>
                        <div class="text-[13px] font-bold text-emerald-950 flex items-center gap-1.5">
                            <span>อนุมัติแสดงผล (สั่งออนไลน์ & แคชเชียร์)</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-200/70 text-emerald-900">แนะนำ</span>
                        </div>
                        <div class="text-[11.5px] text-emerald-800 leading-tight mt-0.5">
                            เมื่อติ๊กถูก ทุกรายการในแถบหมวดหมู่นี้จะแสดงในหน้าสั่งออนไลน์และหน้าแคชเชียร์ทันที
                        </div>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" id="create-cat-is-active" name="is_active" value="1" checked class="sr-only peer">
                    <div class="w-12 h-6.5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2.5px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5.5 after:w-5.5 after:transition-all peer-checked:bg-emerald-600 shadow-inner"></div>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-100">
                <button
                    type="button"
                    onclick="closeCreateCategoryModal()"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold cursor-pointer transition"
                >
                    ยกเลิก
                </button>
                <button
                    type="submit"
                    id="btn-save-category"
                    class="px-5 py-2.5 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-bold cursor-pointer transition shadow-xs flex items-center gap-1.5"
                >
                    <span>💾 บันทึกแถบหมวดหมู่</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 9. POPUP 4: EDIT CATEGORY MODAL (ป๊อปอัปแก้ไขแถบหมวดหมู่: แก้ไขข้อความ รูปภาพ และอนุมัติแสดงผล) -->
<div
    id="category-edit-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/65 backdrop-blur-xs p-4 overflow-y-auto"
    onclick="if(event.target === this) closeEditCategoryModal()"
>
    <div class="bg-white rounded-[24px] max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-[#8B0912] via-[#B91C1C] to-[#E50914] text-white flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </span>
                <div>
                    <h3 class="text-[17px] font-bold leading-snug">แก้ไขแถบหมวดหมู่</h3>
                    <p class="text-[11.5px] text-rose-100/90 font-normal">แก้ไขข้อความชื่อหมวดหมู่ รหัส รูปภาพไอคอน และการแสดงผล</p>
                </div>
            </div>
            <button
                type="button"
                onclick="closeEditCategoryModal()"
                class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center shrink-0 cursor-pointer transition"
            >
                ✕
            </button>
        </div>

        <!-- Form -->
        <form id="category-edit-form" onsubmit="submitEditCategoryForm(event)" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="edit-cat-target-slug" name="target_slug">

            <!-- Category Image Preview & Upload -->
            <div class="p-4 rounded-2xl bg-[#F8F9FB] border border-slate-200/80 space-y-3">
                <div class="flex items-center gap-3.5">
                    <div class="w-16 h-16 rounded-full overflow-hidden bg-white border-2 border-slate-200 shadow-xs shrink-0 relative flex items-center justify-center">
                        <img
                            id="edit-cat-preview-img"
                            src="{{ asset('assets/categories/all.png') }}"
                            alt="Category Preview"
                            class="w-full h-full object-cover"
                        >
                    </div>
                    <div class="flex-1 min-w-0 space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 block">รูปภาพไอคอนหมวดหมู่ (1:1)</label>
                        <div class="flex flex-wrap items-center gap-2">
                            <label class="px-3 py-1.5 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-semibold cursor-pointer inline-flex items-center gap-1.5 shadow-2xs transition-all">
                                <span>📷 เลือกภาพจากเครื่อง</span>
                                <input
                                    type="file"
                                    id="edit-cat-file-input"
                                    name="category_image"
                                    accept="image/*"
                                    onchange="previewEditCategoryUploadFile(this)"
                                    class="hidden"
                                >
                            </label>
                            <input
                                type="text"
                                id="edit-cat-image-url"
                                name="category_image_url"
                                oninput="updateEditCategoryPreviewFromUrl(this.value)"
                                placeholder="หรือวางลิงก์รูปภาพ (URL)..."
                                class="flex-1 min-w-[150px] px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 focus:outline-none focus:border-[#E50914]"
                            >
                        </div>
                    </div>
                </div>

                <!-- Preset Category Icons Quick Picker -->
                <div>
                    <span class="text-[11px] text-slate-400 block mb-1.5">หรือเลือกรูปหมวดหมู่ตัวอย่าง:</span>
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                        @foreach($catPresets as $preset)
                            <button
                                type="button"
                                onclick="selectEditPresetCategoryImage('{{ $preset['url'] }}')"
                                title="{{ $preset['label'] }}"
                                class="w-10 h-10 rounded-full overflow-hidden border-2 border-slate-200 hover:border-[#E50914] shrink-0 transition-all cursor-pointer hover:scale-105 shadow-2xs"
                            >
                                <img src="{{ $preset['url'] }}" alt="{{ $preset['label'] }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Category Name -->
            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1">ชื่อแถบหมวดหมู่ <span class="text-[#E50914]">*</span></label>
                <input
                    type="text"
                    id="edit-cat-name"
                    name="name"
                    required
                    placeholder="เช่น ซีฟู้ด & อาหารทะเล, น้ำซุป, เส้นและวุ้นเส้น..."
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-800 focus:outline-none focus:border-[#E50914] shadow-2xs"
                >
            </div>

            <!-- Category Slug (English identifier) -->
            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1">รหัสหมวดหมู่ (Slug / ภาษาอังกฤษ)</label>
                <input
                    type="text"
                    id="edit-cat-slug"
                    name="slug"
                    placeholder="เช่น seafood, soup, noodles..."
                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono text-slate-600 focus:outline-none focus:border-[#E50914]"
                >
                <p class="text-[11px] text-slate-400 mt-0.5">ใช้สำหรับอ้างอิงรหัสหมวดหมู่</p>
            </div>

            <!-- Approval Checkbox Toggle (ปุ่มติ๊กถูกอนุมัติแสดงผล: สั่งออนไลน์ & แคชเชียร์) -->
            <div class="p-3.5 rounded-2xl bg-emerald-50/90 border border-emerald-200/90 flex items-center justify-between gap-3 shadow-2xs">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                    </span>
                    <div>
                        <div class="text-[13px] font-bold text-emerald-950 flex items-center gap-1.5">
                            <span>อนุมัติแสดงผล (สั่งออนไลน์ & แคชเชียร์)</span>
                        </div>
                        <div class="text-[11.5px] text-emerald-800 leading-tight mt-0.5">
                            เมื่อติ๊กถูก ทุกรายการในแถบหมวดหมู่นี้จะแสดงในหน้าสั่งออนไลน์และหน้าแคชเชียร์
                        </div>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" id="edit-cat-is-active" name="is_active" value="1" class="sr-only peer">
                    <div class="w-12 h-6.5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2.5px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5.5 after:w-5.5 after:transition-all peer-checked:bg-emerald-600 shadow-inner"></div>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between gap-2.5 pt-3 border-t border-slate-100">
                <button
                    type="button"
                    onclick="confirmDeleteCurrentEditCategory()"
                    class="px-3.5 py-2.5 rounded-xl border border-rose-200 hover:bg-rose-50 text-rose-600 text-xs font-semibold cursor-pointer transition flex items-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <span>ลบหมวดหมู่นี้</span>
                </button>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="closeEditCategoryModal()"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 text-xs font-semibold cursor-pointer transition"
                    >
                        ยกเลิก
                    </button>
                    <button
                        type="submit"
                        id="btn-update-category"
                        class="px-5 py-2.5 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-bold cursor-pointer transition shadow-xs flex items-center gap-1.5"
                    >
                        <span>💾 บันทึกการแก้ไข</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Master Options Dropdown Menu for Sticky Bar ☰ Button (Fixed positioned, avoids overflow clipping) -->
<div
    id="master-options-dropdown"
    class="hidden fixed w-60 rounded-2xl bg-white shadow-2xl border border-slate-200/90 py-1.5 animate-in fade-in zoom-in-95 duration-100"
    style="z-index: 99999;"
>
    <div class="px-3.5 py-1.5 text-[11px] font-bold text-slate-400 border-b border-slate-100 truncate">
        ตัวเลือกจัดการรายการ (☰)
    </div>
    <button
        type="button"
        onclick="chooseMasterOption('edit')"
        class="w-full px-3.5 py-2.5 text-left text-xs font-semibold text-slate-700 hover:bg-rose-50 hover:text-[#E50914] flex items-center gap-2.5 transition cursor-pointer"
    >
        <span class="w-7 h-7 rounded-lg bg-rose-50 text-[#E50914] flex items-center justify-center shrink-0">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
        </span>
        <div>
            <span class="block font-bold">แก้ไขรายการ</span>
            <span class="block text-[10.5px] font-normal text-slate-400">เปิดโหมดแก้ไขข้อมูลและภาพปก</span>
        </div>
    </button>

    <button
        type="button"
        onclick="chooseMasterOption('delete')"
        class="w-full px-3.5 py-2.5 text-left text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 flex items-center gap-2.5 transition cursor-pointer border-t border-slate-100"
    >
        <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </span>
        <div>
            <span class="block font-bold">ลบรายการ</span>
            <span class="block text-[10.5px] font-normal text-slate-400">เลือกหลายรายการแล้วลบพร้อมกัน</span>
        </div>
    </button>
</div>

<!-- Contextual Dropdown Menu for Individual Card ☰ Button -->
<div
    id="card-options-dropdown"
    class="hidden fixed z-50 w-52 rounded-2xl bg-white shadow-2xl border border-slate-200/90 py-1.5 animate-in fade-in zoom-in-95 duration-100"
>
    <div class="px-3.5 py-1.5 text-[11px] font-bold text-slate-400 border-b border-slate-100 truncate" id="card-dropdown-title">
        จัดการรายการ
    </div>
    <button
        type="button"
        id="card-dropdown-edit-btn"
        class="w-full px-3.5 py-2 text-left text-xs font-semibold text-slate-700 hover:bg-rose-50 hover:text-[#E50914] flex items-center gap-2.5 transition cursor-pointer"
    >
        <span class="w-6 h-6 rounded-lg bg-rose-50 text-[#E50914] flex items-center justify-center shrink-0">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
        </span>
        <span>แก้ไขรายการนี้</span>
    </button>
    <button
        type="button"
        id="card-dropdown-delete-btn"
        class="w-full px-3.5 py-2 text-left text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 flex items-center gap-2.5 transition cursor-pointer border-t border-slate-100"
    >
        <span class="w-6 h-6 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
        </span>
        <span>ลบรายการนี้</span>
    </button>
    <button
        type="button"
        id="card-dropdown-multidel-btn"
        class="w-full px-3.5 py-2 text-left text-xs font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 flex items-center gap-2.5 transition cursor-pointer border-t border-slate-100"
    >
        <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 11 12 14 22 4"></polyline>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
        </span>
        <span>เลือกหลายรายการเพื่อลบ</span>
    </button>
</div>

<!-- 9. FLOATING ACTION TOOLBAR FOR BATCH DELETE MODE -->
<div
    id="delete-mode-bar"
    class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-white/95 backdrop-blur-md border border-slate-300 shadow-2xl rounded-2xl px-4 sm:px-6 py-3 flex items-center gap-3 sm:gap-4 max-w-[95vw] animate-in slide-in-from-bottom-6 duration-200"
>
    <!-- Selected Count Badge -->
    <div class="flex items-center gap-2">
        <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-rose-100 text-[#E50914] flex items-center justify-center font-extrabold text-xs sm:text-sm">
            <span id="delete-selected-count">0</span>
        </span>
        <span class="text-xs sm:text-sm font-bold text-slate-800 whitespace-nowrap">
            รายการที่เลือก
        </span>
    </div>

    <div class="h-6 w-px bg-slate-200"></div>

    <!-- Select All / Deselect All Button -->
    <button
        type="button"
        id="btn-toggle-select-all"
        onclick="toggleSelectAllDeleteItems()"
        class="text-xs font-semibold text-slate-700 hover:text-slate-900 px-2.5 sm:px-3 py-1.5 rounded-xl hover:bg-slate-100 transition whitespace-nowrap cursor-pointer flex items-center gap-1.5"
    >
        <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 11 12 14 22 4"></polyline>
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
        </svg>
        <span id="btn-toggle-select-all-text">เลือกทั้งหมด</span>
    </button>

    <!-- Confirm Delete Button -->
    <button
        type="button"
        id="btn-execute-delete"
        onclick="openDeleteConfirmModal()"
        disabled
        class="px-4 sm:px-5 py-2 rounded-xl bg-[#E50914] hover:bg-[#C8102E] disabled:bg-slate-200 disabled:text-slate-400 text-white text-xs font-bold shadow-md shadow-[#E50914]/25 inline-flex items-center gap-1.5 sm:gap-2 transition cursor-pointer disabled:cursor-not-allowed whitespace-nowrap"
    >
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
        </svg>
        <span>ลบรายการที่เลือก (<span id="btn-delete-count-inline">0</span>)</span>
    </button>

    <!-- Cancel / Back Button -->
    <button
        type="button"
        onclick="exitDeleteMode()"
        class="px-3 sm:px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer whitespace-nowrap"
    >
        ยกเลิก / กลับ
    </button>
</div>

<!-- 10. POPUP 4: DELETE CONFIRMATION MODAL (ป๊อปอัปแจ้งเตือนให้ยืนยันการลบอีกครั้ง และกดกลับก็ได้) -->
<div
    id="mat-delete-confirm-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/65 backdrop-blur-xs p-4 overflow-y-auto"
    onclick="if(event.target === this) closeDeleteConfirmModal()"
>
    <div class="bg-white rounded-[24px] max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-150">
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-[#5A060C] via-[#8B0912] to-[#5A060C] text-white flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-white/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                </span>
                <div>
                    <h3 class="text-[17px] font-bold leading-snug">ยืนยันการลบรายการวัตถุดิบ</h3>
                    <p class="text-[11.5px] text-rose-100/85 font-normal">ตรวจสอบรายการก่อนยืนยัน หรือกดกลับได้</p>
                </div>
            </div>
            <button
                type="button"
                onclick="closeDeleteConfirmModal()"
                class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center shrink-0 cursor-pointer transition"
            >
                ✕
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-4">
            <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-100 space-y-1.5">
                <p class="text-xs font-bold text-slate-800">
                    คุณต้องการลบวัตถุดิบจำนวน <span id="confirm-delete-count" class="text-[#E50914] text-sm font-extrabold">0</span> รายการ ใช่หรือไม่?
                </p>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    ⚠️ ข้อมูลวัตถุดิบ ประวัติราคา และรายการสต็อกที่เกี่ยวข้องจะถูกนำออกจากระบบทันที
                </p>
            </div>

            <!-- Scrollable Item List -->
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">รายการที่จะถูกลบ:</span>
                <div id="confirm-delete-items-list" class="max-h-52 overflow-y-auto space-y-2 pr-1 divide-y divide-slate-100">
                    <!-- Dynamically inserted by JS -->
                </div>
            </div>
        </div>

        <!-- Footer (กดกลับ หรือ ยืนยัน) -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
            <button
                type="button"
                onclick="closeDeleteConfirmModal()"
                class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold cursor-pointer transition"
            >
                ยกเลิก / กลับ
            </button>
            <button
                type="button"
                id="btn-confirm-delete-submit"
                onclick="submitDeleteMaterials()"
                class="px-5 py-2.5 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-bold shadow-md shadow-[#E50914]/25 inline-flex items-center gap-2 cursor-pointer transition-all"
            >
                <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                <span>ยืนยันลบรายการ</span>
            </button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div
    id="price-toast"
    class="fixed bottom-5 right-5 z-50 hidden items-center gap-2.5 px-4 py-3 rounded-2xl bg-slate-900 text-white text-xs font-semibold shadow-xl border border-slate-700"
>
    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
    <span id="price-toast-text">บันทึกข้อมูลเรียบร้อยแล้ว</span>
</div>

<script>
    let currentMatCategory = 'all';
    let currentPriceGridCols = 3;
    let isPriceEditMode = false;
    let activeDetailCardEl = null;
    let isDeleteMode = false;
    let selectedDeleteIds = new Set();
    let pendingDeleteIds = [];
    let cardActionTargetEl = null;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    function showPriceToast(message) {
        const toast = document.getElementById('price-toast');
        const textEl = document.getElementById('price-toast-text');
        if (!toast || !textEl) return;
        textEl.textContent = message;
        toast.classList.remove('hidden');
        toast.classList.add('flex');
        clearTimeout(window.__priceToastTimer);
        window.__priceToastTimer = setTimeout(() => {
            toast.classList.add('hidden');
            toast.classList.remove('flex');
        }, 3200);
    }

    // Helper format ราคาซื้อล่าสุด: ถ้าไม่มีเศษ (.00) ให้ตัด .00 ออก ถ้ามีเศษ (.02, .030, .80) ให้แสดงตามจริง
    function formatLatestPrice(price) {
        const num = Number(price || 0);
        if (num % 1 === 0) {
            return num.toLocaleString('en-US');
        }
        const str = String(price);
        const parts = str.split('.');
        const dec = parts.length > 1 ? parts[1].length : 2;
        const decimals = Math.min(Math.max(2, dec), 4);
        return num.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
    }

    // 1. Sticky bar shadow when scrolling
    window.addEventListener('scroll', () => {
        const bar = document.getElementById('price-sticky-bar');
        if (!bar) return;
        if (window.scrollY > 140) {
            bar.classList.add('is-pinned');
        } else {
            bar.classList.remove('is-pinned');
        }
    }, { passive: true });

    // Toggle Master Three-Lines (☰) Options Dropdown
    function toggleMasterOptionsMenu(event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        const dropdown = document.getElementById('master-options-dropdown');
        const btn = document.getElementById('btn-toggle-edit-mode');
        if (!dropdown || !btn) return;

        if (!dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
            return;
        }

        // Close card options dropdown if open
        const cardDropdown = document.getElementById('card-options-dropdown');
        if (cardDropdown) cardDropdown.classList.add('hidden');

        // Calculate fixed position below the ☰ button so it is NEVER clipped by overflow containers
        const rect = btn.getBoundingClientRect();
        dropdown.style.position = 'fixed';
        dropdown.style.top = `${rect.bottom + 6}px`;
        dropdown.style.left = `${Math.max(12, Math.min(window.innerWidth - 265, rect.right - 240))}px`;
        dropdown.style.zIndex = '99999';
        dropdown.classList.remove('hidden');
    }

    function chooseMasterOption(type) {
        const dropdown = document.getElementById('master-options-dropdown');
        if (dropdown) dropdown.classList.add('hidden');

        if (type === 'edit') {
            if (isDeleteMode) exitDeleteMode();
            togglePriceEditMode();
        } else if (type === 'delete') {
            if (isPriceEditMode) togglePriceEditMode();
            enterDeleteMode();
        }
    }

    // 2. Toggle Master Three-Lines (☰) Edit Mode
    function togglePriceEditMode() {
        isPriceEditMode = !isPriceEditMode;
        document.body.classList.toggle('price-edit-mode-active', isPriceEditMode);

        const btn = document.getElementById('btn-toggle-edit-mode');

        if (btn) {
            if (isPriceEditMode) {
                btn.className = 'h-[34px] px-3 rounded-full bg-[#E50914] text-white border border-[#E50914] shadow-sm ring-2 ring-rose-200 inline-flex items-center justify-center gap-1.5 transition-all cursor-pointer select-none';
            } else {
                btn.className = 'h-[34px] w-[33px] rounded-full bg-white hover:bg-rose-50 text-slate-700 hover:text-[#E50914] border border-slate-200/90 shadow-2xs inline-flex items-center justify-center shrink-0 transition-all cursor-pointer select-none';
            }
        }

        if (isPriceEditMode) {
            showPriceToast('เปิดโหมดแก้ไขแล้ว: กดปุ่ม ☰ บนภาพปกหรือการ์ดรายการเพื่อแก้ไข');
        }
    }

    // 2.1 Multi-Select Delete Mode
    function enterDeleteMode() {
        isDeleteMode = true;
        document.body.classList.add('price-delete-mode-active');
        const bar = document.getElementById('delete-mode-bar');
        if (bar) {
            bar.classList.remove('hidden');
            bar.classList.add('flex');
        }
        selectedDeleteIds.clear();
        document.querySelectorAll('.delete-item-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.mat-card.is-selected-for-delete').forEach(c => c.classList.remove('is-selected-for-delete'));
        updateDeleteModeUI();
        showPriceToast('เข้าสู่โหมดลบรายการ: สามารถเลือกหลายรายการแล้วกดลบพร้อมกันได้');
    }

    function exitDeleteMode() {
        isDeleteMode = false;
        document.body.classList.remove('price-delete-mode-active');
        const bar = document.getElementById('delete-mode-bar');
        if (bar) {
            bar.classList.add('hidden');
            bar.classList.remove('flex');
        }
        selectedDeleteIds.clear();
        document.querySelectorAll('.delete-item-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.mat-card.is-selected-for-delete').forEach(c => c.classList.remove('is-selected-for-delete'));
        updateDeleteModeUI();
    }

    function updateDeleteModeUI() {
        const count = selectedDeleteIds.size;
        const countBadge = document.getElementById('delete-selected-count');
        const countInline = document.getElementById('btn-delete-count-inline');
        const execBtn = document.getElementById('btn-execute-delete');
        const selectAllText = document.getElementById('btn-toggle-select-all-text');

        if (countBadge) countBadge.textContent = count;
        if (countInline) countInline.textContent = count;
        if (execBtn) execBtn.disabled = count === 0;

        // Check if all visible cards are selected
        const visibleCheckboxes = Array.from(document.querySelectorAll('.mat-card:not(.hidden) .delete-item-checkbox'));
        const allVisibleSelected = visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => selectedDeleteIds.has(Number(cb.dataset.id)));
        if (selectAllText) {
            selectAllText.textContent = allVisibleSelected ? 'ยกเลิกการเลือก' : `เลือกทั้งหมด (${visibleCheckboxes.length})`;
        }
    }

    function toggleSelectAllDeleteItems() {
        const visibleCheckboxes = Array.from(document.querySelectorAll('.mat-card:not(.hidden) .delete-item-checkbox'));
        const allVisibleSelected = visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => selectedDeleteIds.has(Number(cb.dataset.id)));

        visibleCheckboxes.forEach(cb => {
            const id = Number(cb.dataset.id);
            const card = cb.closest('.mat-card');
            if (allVisibleSelected) {
                selectedDeleteIds.delete(id);
                cb.checked = false;
                if (card) card.classList.remove('is-selected-for-delete');
            } else {
                selectedDeleteIds.add(id);
                cb.checked = true;
                if (card) card.classList.add('is-selected-for-delete');
            }
        });

        updateDeleteModeUI();
    }

    function handleDeleteCheckboxClick(event, checkboxEl) {
        if (event) event.stopPropagation();
        const id = Number(checkboxEl.dataset.id);
        const card = checkboxEl.closest('.mat-card');

        if (checkboxEl.checked) {
            selectedDeleteIds.add(id);
            if (card) card.classList.add('is-selected-for-delete');
        } else {
            selectedDeleteIds.delete(id);
            if (card) card.classList.remove('is-selected-for-delete');
        }

        updateDeleteModeUI();
    }

    // Card-Level Options Dropdown Handler
    function openCardActionMenu(event, cardEl) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        cardActionTargetEl = cardEl;
        const dropdown = document.getElementById('card-options-dropdown');
        if (!dropdown || !cardEl) return;

        const rawJson = cardEl.getAttribute('data-modal');
        let data = {};
        try { data = JSON.parse(rawJson || '{}'); } catch(e) {}

        const titleEl = document.getElementById('card-dropdown-title');
        if (titleEl) titleEl.textContent = data.name || 'จัดการรายการ';

        const editBtn = document.getElementById('card-dropdown-edit-btn');
        const deleteBtn = document.getElementById('card-dropdown-delete-btn');
        const multiDelBtn = document.getElementById('card-dropdown-multidel-btn');

        if (editBtn) {
            editBtn.onclick = (e) => {
                dropdown.classList.add('hidden');
                openMaterialEditModalByCard(e, cardEl);
            };
        }
        if (deleteBtn) {
            deleteBtn.onclick = (e) => {
                dropdown.classList.add('hidden');
                openDeleteSingleMaterialConfirm(e, cardEl);
            };
        }
        if (multiDelBtn) {
            multiDelBtn.onclick = (e) => {
                dropdown.classList.add('hidden');
                if (isPriceEditMode) togglePriceEditMode();
                enterDeleteMode();
                const cb = cardEl.querySelector('.delete-item-checkbox');
                if (cb) {
                    cb.checked = true;
                    selectedDeleteIds.add(Number(cb.dataset.id));
                    cardEl.classList.add('is-selected-for-delete');
                    updateDeleteModeUI();
                }
            };
        }

        // Position dropdown fixed relative to viewport
        const btn = event.currentTarget || event.target;
        const rect = btn.getBoundingClientRect();
        dropdown.style.position = 'fixed';
        dropdown.style.zIndex = '99999';
        dropdown.style.top = `${Math.min(window.innerHeight - 180, rect.bottom + 6)}px`;
        dropdown.style.left = `${Math.max(10, Math.min(window.innerWidth - 220, rect.right - 200))}px`;
        dropdown.classList.remove('hidden');
    }

    // Global click listener to close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        const masterDropdown = document.getElementById('master-options-dropdown');
        const masterBtn = document.getElementById('btn-toggle-edit-mode');
        if (masterDropdown && !masterDropdown.classList.contains('hidden')) {
            if (!masterDropdown.contains(e.target) && masterBtn && !masterBtn.contains(e.target)) {
                masterDropdown.classList.add('hidden');
            }
        }

        const cardDropdown = document.getElementById('card-options-dropdown');
        if (cardDropdown && !cardDropdown.classList.contains('hidden')) {
            if (!cardDropdown.contains(e.target) && !e.target.closest('.edit-mode-trigger')) {
                cardDropdown.classList.add('hidden');
            }
        }

        const catDropdown = document.getElementById('category-settings-dropdown');
        const catBtn = document.getElementById('btn-cat-settings-trigger');
        if (catDropdown && !catDropdown.classList.contains('hidden')) {
            if (!catDropdown.contains(e.target) && catBtn && !catBtn.contains(e.target)) {
                catDropdown.classList.add('hidden');
            }
        }

        const mainCatDropdown = document.getElementById('main-category-settings-dropdown');
        const mainCatBtn = document.getElementById('btn-cat-main-settings-trigger');
        if (mainCatDropdown && !mainCatDropdown.classList.contains('hidden')) {
            if (!mainCatDropdown.contains(e.target) && mainCatBtn && !mainCatBtn.contains(e.target)) {
                mainCatDropdown.classList.add('hidden');
            }
        }
    });

    // Open confirmation modal for a single item
    function openDeleteSingleMaterialConfirm(event, cardEl) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        if (!cardEl) return;
        const matId = Number(cardEl.dataset.matId);
        pendingDeleteIds = [matId];

        const rawJson = cardEl.getAttribute('data-modal');
        let data = {};
        try { data = JSON.parse(rawJson || '{}'); } catch(e) {}

        renderConfirmDeleteList([{
            id: matId,
            name: data.name || cardEl.querySelector('.mat-title')?.textContent?.trim() || 'วัตถุดิบ',
            img: data.image_url || cardEl.querySelector('[data-role="mat-img"]')?.src || '',
            unit: data.purchase_unit || '',
            price: data.latest_cost_price || 0
        }]);

        showDeleteConfirmModal(1);
    }

    function openDeleteSingleMaterialConfirmFromDetail() {
        if (!activeDetailCardEl) return;
        const cardEl = activeDetailCardEl;
        closeMaterialDetailModal();
        openDeleteSingleMaterialConfirm(null, cardEl);
    }

    // Open confirmation modal for all checked items in Multi-Select Delete Mode
    function openDeleteConfirmModal() {
        if (selectedDeleteIds.size === 0) {
            alert('กรุณาเลือกรายการที่ต้องการลบอย่างน้อย 1 รายการ');
            return;
        }

        pendingDeleteIds = Array.from(selectedDeleteIds);
        const items = [];
        pendingDeleteIds.forEach(id => {
            const card = document.getElementById(`mat-card-${id}`);
            if (card) {
                const rawJson = card.getAttribute('data-modal');
                let data = {};
                try { data = JSON.parse(rawJson || '{}'); } catch(e) {}
                items.push({
                    id: id,
                    name: data.name || card.querySelector('.mat-title')?.textContent?.trim() || `วัตถุดิบ #${id}`,
                    img: data.image_url || card.querySelector('[data-role="mat-img"]')?.src || '',
                    unit: data.purchase_unit || '',
                    price: data.latest_cost_price || 0
                });
            }
        });

        renderConfirmDeleteList(items);
        showDeleteConfirmModal(items.length);
    }

    function renderConfirmDeleteList(items) {
        const listEl = document.getElementById('confirm-delete-items-list');
        if (!listEl) return;
        listEl.innerHTML = '';

        items.forEach(item => {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2.5 py-1.5';
            row.innerHTML = `
                <img src="${item.img}" alt="${item.name}" class="w-9 h-9 rounded-xl object-cover bg-slate-100 border border-slate-200 shrink-0" onerror="this.onerror=null;this.src='{{ asset('assets/menu/food-fallback.svg') }}'">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-slate-800 truncate">${item.name}</p>
                    <p class="text-[11px] text-slate-400 truncate">หน่วยซื้อ: ${item.unit || '-'} • ฿${Number(item.price).toFixed(2)}</p>
                </div>
            `;
            listEl.appendChild(row);
        });
    }

    function showDeleteConfirmModal(count) {
        const countEl = document.getElementById('confirm-delete-count');
        if (countEl) countEl.textContent = count;

        const modal = document.getElementById('mat-delete-confirm-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeDeleteConfirmModal() {
        const modal = document.getElementById('mat-delete-confirm-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    async function submitDeleteMaterials() {
        if (!pendingDeleteIds || pendingDeleteIds.length === 0) return;

        const submitBtn = document.getElementById('btn-confirm-delete-submit');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="12" r="10" stroke-width="4" class="opacity-25"></circle>
                    <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" class="opacity-75"></path>
                </svg>
                <span>กำลังลบ...</span>
            `;
        }

        try {
            const res = await fetch("{{ route('admin.receipts.price_tracker.batch_delete') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ ids: pendingDeleteIds }),
            });

            const result = await res.json();
            if (!res.ok || !result.success) {
                alert(result.message || 'เกิดข้อผิดพลาดในการลบรายการ');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = `
                        <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        <span>ยืนยันลบรายการ</span>
                    `;
                }
                return;
            }

            closeDeleteConfirmModal();
            showPriceToast(result.message || 'ลบรายการวัตถุดิบเรียบร้อยแล้ว');

            // Remove deleted cards from DOM
            pendingDeleteIds.forEach(id => {
                const card = document.getElementById(`mat-card-${id}`);
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.85)';
                    setTimeout(() => card.remove(), 250);
                }
            });

            // Update badge counts
            const badgeCountAll = document.getElementById('badge-count-all');
            if (badgeCountAll) {
                const cur = parseInt(badgeCountAll.textContent || '0');
                badgeCountAll.textContent = Math.max(0, cur - pendingDeleteIds.length);
            }

            if (isDeleteMode) {
                exitDeleteMode();
            }

            // Reload page smoothly after 450ms
            setTimeout(() => window.location.reload(), 450);

        } catch (err) {
            console.error('Delete failed:', err);
            alert('ไม่สามารถลบรายการได้ กรุณาลองใหม่อีกครั้ง');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = `
                    <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <span>ยืนยันลบรายการ</span>
                `;
            }
        }
    }

    // 3. 3 vs 4 Columns Switcher (Default = 3 items per row)
    function setPriceGridColumns(cols) {
        currentPriceGridCols = cols === 4 ? 4 : 3;
        const grid = document.getElementById('materials-grid');
        const btn3 = document.getElementById('btn-grid-3');
        const btn4 = document.getElementById('btn-grid-4');
        if (!grid || !btn3 || !btn4) return;

        if (currentPriceGridCols === 4) {
            grid.classList.remove('xl:grid-cols-3');
            grid.classList.add('xl:grid-cols-4', 'grid-cols-4-mode');

            btn4.className = 'px-2.5 py-1 rounded-full text-[11.5px] font-semibold flex items-center gap-1.5 transition-all cursor-pointer bg-[#E50914] text-white shadow-2xs';
            btn3.className = 'px-2.5 py-1 rounded-full text-[11.5px] font-semibold flex items-center gap-1.5 transition-all cursor-pointer text-slate-600 hover:text-slate-900';
        } else {
            grid.classList.remove('xl:grid-cols-4', 'grid-cols-4-mode');
            grid.classList.add('xl:grid-cols-3');

            btn3.className = 'px-2.5 py-1 rounded-full text-[11.5px] font-semibold flex items-center gap-1.5 transition-all cursor-pointer bg-[#E50914] text-white shadow-2xs';
            btn4.className = 'px-2.5 py-1 rounded-full text-[11.5px] font-semibold flex items-center gap-1.5 transition-all cursor-pointer text-slate-600 hover:text-slate-900';
        }

        try {
            localStorage.setItem('mala_price_tracker_grid_cols', String(currentPriceGridCols));
        } catch (e) {}
    }

    // 4. Category Filter Pills
    function filterMaterialsByCategory(catSlug) {
        currentMatCategory = catSlug || 'all';
        const buttons = document.querySelectorAll('.cat-pill-btn');
        let activeLabel = 'รายการวัตถุดิบทั้งหมด';

        buttons.forEach(btn => {
            const isMatch = btn.getAttribute('data-cat') === currentMatCategory;
            if (isMatch) {
                btn.classList.remove('bg-white', 'text-[#1F2430]', 'border-slate-200/90');
                btn.classList.add('bg-[#E50914]', 'text-white', 'border-[#E50914]', 'shadow-sm');
                const labelText = btn.textContent.trim();
                if (currentItemsFilter === 'today') {
                    activeLabel = currentMatCategory === 'all' ? 'รายการวัตถุดิบวันนี้' : `วัตถุดิบวันนี้: ${labelText}`;
                } else {
                    activeLabel = currentMatCategory === 'all' ? 'รายการวัตถุดิบทั้งหมด' : `หมวดหมู่: ${labelText}`;
                }
            } else {
                btn.classList.remove('bg-[#E50914]', 'text-white', 'border-[#E50914]', 'shadow-sm');
                btn.classList.add('bg-white', 'text-[#1F2430]', 'border-slate-200/90');
            }
        });

        const headingEl = document.getElementById('section-heading-text');
        if (headingEl) headingEl.textContent = activeLabel;

        const catApproveBtn = document.getElementById('btn-cat-approval-toggle');
        const catSettingsBtn = document.getElementById('btn-cat-main-settings-trigger');
        if (catApproveBtn) {
            if (currentMatCategory === 'all') {
                catApproveBtn.classList.add('hidden');
                catApproveBtn.classList.remove('inline-flex');
            } else {
                catApproveBtn.classList.remove('hidden');
                catApproveBtn.classList.add('inline-flex');
                const activeBtn = document.querySelector(`.cat-pill-btn[data-cat="${currentMatCategory}"]`);
                const isActive = activeBtn ? activeBtn.getAttribute('data-active') === '1' : true;
                updateCategoryApprovalButtonUI(isActive);
            }
        }
        if (catSettingsBtn) {
            if (currentMatCategory === 'all') {
                catSettingsBtn.classList.add('hidden');
                catSettingsBtn.classList.remove('inline-flex');
            } else {
                catSettingsBtn.classList.remove('hidden');
                catSettingsBtn.classList.add('inline-flex');
            }
        }

        applyMaterialFilters();
    }

    function updateCategoryApprovalButtonUI(isActive) {
        const btn = document.getElementById('btn-cat-approval-toggle');
        if (!btn) return;
        if (isActive) {
            btn.className = 'shrink-0 w-8 h-8 rounded-xl transition-all shadow-2xs inline-flex items-center justify-center cursor-pointer ml-1 select-none bg-emerald-600 hover:bg-emerald-700 text-white';
            btn.title = 'อนุมัติทุกรายการในแถบนี้แล้ว (คลิกเพื่อปิดการแสดงผลในหน้าสั่งออนไลน์และแคชเชียร์)';
        } else {
            btn.className = 'shrink-0 w-8 h-8 rounded-xl transition-all shadow-2xs inline-flex items-center justify-center cursor-pointer ml-1 select-none bg-white hover:bg-emerald-50 text-emerald-600 border border-emerald-400 hover:border-emerald-600';
            btn.title = 'คลิกเพื่ออนุมัติทุกรายการในแถบนี้ (สั่งออนไลน์ & แคชเชียร์)';
        }
    }

    async function toggleActiveCategoryApproval() {
        if (!currentMatCategory || currentMatCategory === 'all') return;

        const btn = document.getElementById('btn-cat-approval-toggle');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-75');
        }

        const catBtn = document.querySelector(`.cat-pill-btn[data-cat="${currentMatCategory}"]`);
        const currentActive = catBtn ? catBtn.getAttribute('data-active') === '1' : true;
        const targetActive = !currentActive;

        try {
            const url = `/admin/categories/${encodeURIComponent(currentMatCategory)}/toggle-approval`;
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    is_active: targetActive
                })
            });

            const resData = await res.json();
            if (res.ok && resData.success) {
                if (catBtn) {
                    catBtn.setAttribute('data-active', resData.is_active ? '1' : '0');
                }
                updateCategoryApprovalButtonUI(resData.is_active);
                showPriceToast(resData.message);

                // Update all cards belonging to this category in memory
                document.querySelectorAll('.mat-card').forEach(card => {
                    const catsAttr = card.getAttribute('data-cats') || '';
                    const cats = catsAttr.split(',').map(s => s.trim());
                    if (cats.includes(currentMatCategory)) {
                        const rawJson = card.getAttribute('data-modal');
                        if (rawJson) {
                            try {
                                const d = JSON.parse(rawJson);
                                d.is_approved_for_sale = resData.is_active;
                                card.setAttribute('data-modal', JSON.stringify(d));
                            } catch (e) {}
                        }
                    }
                });

                if ('BroadcastChannel' in window) {
                    try {
                        const channel = new BroadcastChannel('mala_inventory_sync_channel');
                        channel.postMessage({ type: 'category_approval_toggled', slug: currentMatCategory, is_active: resData.is_active });
                    } catch (e) {}
                }
            } else {
                alert(resData.message || 'เกิดข้อผิดพลาดในการเปลี่ยนสถานะหมวดหมู่');
            }
        } catch (err) {
            console.error('Toggle category approval error:', err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-75');
            }
        }
    }

    // 4.5 Today vs All Items Filter Mode (รายการที่สั่งซื้อในวันนี้ vs รายการทั้งหมด)
    let currentItemsFilter = 'all'; // 'all' or 'today'

    function setPriceItemsFilter(mode) {
        currentItemsFilter = mode === 'today' ? 'today' : 'all';
        const btnToday = document.getElementById('btn-filter-today');
        const btnAll = document.getElementById('btn-filter-all');
        const badgeToday = document.getElementById('badge-count-today');
        const badgeAll = document.getElementById('badge-count-all');
        const syncBtn = document.getElementById('btn-sync-today');
        const headingEl = document.getElementById('section-heading-text');

        if (currentItemsFilter === 'today') {
            btnToday?.classList.remove('text-slate-600', 'hover:text-slate-900');
            btnToday?.classList.add('bg-[#E50914]', 'text-white', 'shadow-2xs');
            badgeToday?.classList.remove('bg-slate-100', 'text-slate-700');
            badgeToday?.classList.add('bg-white/20', 'text-white');
            syncBtn?.classList.remove('text-slate-500', 'hover:text-slate-800', 'hover:bg-slate-200/80');
            syncBtn?.classList.add('text-white', 'hover:bg-white/20');

            btnAll?.classList.remove('bg-[#E50914]', 'text-white', 'shadow-2xs');
            btnAll?.classList.add('text-slate-600', 'hover:text-slate-900');
            badgeAll?.classList.remove('bg-white/20', 'text-white');
            badgeAll?.classList.add('bg-slate-100', 'text-slate-700');

            if (headingEl) {
                headingEl.textContent = 'รายการวัตถุดิบที่สั่งซื้อวันนี้';
            }
        } else {
            btnAll?.classList.remove('text-slate-600', 'hover:text-slate-900');
            btnAll?.classList.add('bg-[#E50914]', 'text-white', 'shadow-2xs');
            badgeAll?.classList.remove('bg-slate-100', 'text-slate-700');
            badgeAll?.classList.add('bg-white/20', 'text-white');

            btnToday?.classList.remove('bg-[#E50914]', 'text-white', 'shadow-2xs');
            btnToday?.classList.add('text-slate-600', 'hover:text-slate-900');
            badgeToday?.classList.remove('bg-white/20', 'text-white');
            badgeToday?.classList.add('bg-slate-100', 'text-slate-700');
            syncBtn?.classList.remove('text-white', 'hover:bg-white/20');
            syncBtn?.classList.add('text-slate-500', 'hover:text-slate-800', 'hover:bg-slate-200/80');

            if (headingEl) {
                headingEl.textContent = currentMatCategory === 'all'
                    ? 'รายการวัตถุดิบทั้งหมด'
                    : (document.querySelector(`.cat-pill-btn[data-cat="${currentMatCategory}"] span:last-child`)?.textContent || 'รายการวัตถุดิบ');
            }
        }

        applyMaterialFilters();
    }

    // ฟังก์ชันโหลด/ซิงค์รายการสั่งซื้อวันนี้จากหน้าสแกนใบเสร็จ
    async function syncTodayReceiptItems(event) {
        if (event) {
            event.stopPropagation();
        }
        const icon = document.getElementById('icon-sync-today');
        const badgeToday = document.getElementById('badge-count-today');
        if (icon) icon.classList.add('animate-spin');

        try {
            const res = await fetch("{{ route('admin.receipts.price_tracker.sync_today') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const data = await res.json();
            if (data.success) {
                if (badgeToday && typeof data.today_bought_count !== 'undefined') {
                    badgeToday.textContent = data.today_bought_count;
                }
                const boughtSet = new Set((data.today_bought_ids || []).map(id => String(id)));

                // อัปเดตข้อมูลบนการ์ดในตาราง
                const grid = document.getElementById('materials-grid');
                if (grid) {
                    const cards = grid.querySelectorAll('.mat-card');
                    cards.forEach(card => {
                        const cardId = card.id ? card.id.replace('mat-card-', '') : '';
                        const isBought = boughtSet.has(cardId);
                        card.setAttribute('data-bought', isBought ? '1' : '0');

                        const availBadge = card.querySelector('.mat-avail-badge');
                        if (availBadge && isBought) {
                            availBadge.className = 'mat-avail-badge px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#D4F435] text-[#143D02] border border-[#BDEB18] shadow-2xs inline-flex items-center gap-1 select-none whitespace-nowrap';
                            availBadge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-[#15803D] animate-pulse"></span><span>ซื้อวันนี้</span>';
                        }
                    });
                }

                // หากมีวัตถุดิบใหม่ที่ถูกเพิ่มเข้ามาใหม่ ให้รีโหลดหน้าเพื่อให้แสดงการ์ดใหม่ครบถ้วน
                if (data.new_materials_created && data.new_materials_created > 0) {
                    window.location.reload();
                    return;
                }

                // สลับมายังแถบ "รายการวันนี้" ทันที เพื่อแสดงเฉพาะรายการที่มีในวันนี้
                setPriceItemsFilter('today');
                showPriceToast(`โหลดรายการสั่งซื้อวันนี้จากหน้าสแกนใบเสร็จสำเร็จ (${data.today_bought_count} รายการ)`);
            } else {
                alert(data.message || 'เกิดข้อผิดพลาดในการโหลดรายการวันนี้');
            }
        } catch (err) {
            console.error('Sync today receipts failed:', err);
            showPriceToast('ไม่สามารถเชื่อมต่อเพื่อโหลดข้อมูลได้');
        } finally {
            if (icon) icon.classList.remove('animate-spin');
        }
    }

    // 5. Combined Search + Category Filter + Today/All Filter + Sort
    function applyMaterialFilters() {
        const searchInput = document.getElementById('mat-search-input');
        const sortSelect = document.getElementById('mat-sort-select');
        const grid = document.getElementById('materials-grid');
        const emptyState = document.getElementById('materials-empty-state');
        const countEl = document.getElementById('visible-mat-count');
        if (!grid) return;

        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
        const sortBy = sortSelect ? sortSelect.value : 'latest';
        const cards = Array.from(grid.querySelectorAll('.mat-card'));

        let visibleCards = [];

        cards.forEach(card => {
            const name = card.getAttribute('data-name') || '';
            const cats = (card.getAttribute('data-cats') || '').split(',');
            const matchCat = currentMatCategory === 'all' || cats.includes(currentMatCategory);
            const matchQuery = !query || name.includes(query);
            
            // แถบรายการวันนี้: แสดงเฉพาะรายการที่สั่งซื้อในวันนี้เท่านั้น ตามเงื่อนไข *หน้าแถบนี้จะแสดงเฉพาะที่มีวันนี้เท่านั้น**
            const isBoughtToday = card.getAttribute('data-bought') === '1';
            const matchAvailability = currentItemsFilter === 'all' || isBoughtToday;

            if (matchCat && matchQuery && matchAvailability) {
                card.style.display = 'flex';
                card.classList.remove('hidden');
                visibleCards.push(card);
            } else {
                card.style.display = 'none';
                card.classList.add('hidden');
            }
        });

        visibleCards.sort((a, b) => {
            if (sortBy === 'change-desc') {
                return parseFloat(b.dataset.change || '0') - parseFloat(a.dataset.change || '0');
            }
            if (sortBy === 'price-desc') {
                return parseFloat(b.dataset.price || '0') - parseFloat(a.dataset.price || '0');
            }
            if (sortBy === 'price-asc') {
                return parseFloat(a.dataset.price || '0') - parseFloat(b.dataset.price || '0');
            }
            if (sortBy === 'stick-asc') {
                return parseFloat(a.dataset.stick || '0') - parseFloat(b.dataset.stick || '0');
            }
            return parseInt(a.dataset.order || '0', 10) - parseInt(b.dataset.order || '0', 10);
        });

        visibleCards.forEach(card => grid.appendChild(card));

        if (countEl) countEl.textContent = String(visibleCards.length);
        if (emptyState) {
            emptyState.classList.toggle('hidden', visibleCards.length > 0);
        }
    }

    function resetMaterialFilters() {
        const searchInput = document.getElementById('mat-search-input');
        const sortSelect = document.getElementById('mat-sort-select');
        if (searchInput) searchInput.value = '';
        if (sortSelect) sortSelect.value = 'latest';
        filterMaterialsByCategory('all');
    }

    // 6. Card Click Handler (Selects for delete in Delete Mode, Opens Edit Modal if in Edit Mode, or Detail Modal normally)
    function handleMaterialCardClick(event, cardEl) {
        if (isDeleteMode) {
            const cb = cardEl.querySelector('.delete-item-checkbox');
            if (cb) {
                cb.checked = !cb.checked;
                handleDeleteCheckboxClick(null, cb);
            }
            return;
        }
        if (isPriceEditMode) {
            openMaterialEditModalByCard(event, cardEl);
            return;
        }
        openMaterialDetailModal(cardEl);
    }

    // 7. Ingredient Detail Modal
    function openMaterialDetailModal(cardEl) {
        activeDetailCardEl = cardEl;
        const rawJson = cardEl.getAttribute('data-modal');
        if (!rawJson) return;
        let data;
        try {
            data = JSON.parse(rawJson);
        } catch (e) {
            return;
        }

        const modal = document.getElementById('mat-detail-modal');
        if (!modal) return;

        document.getElementById('modal-mat-img').src = data.image_url;
        document.getElementById('modal-mat-img').alt = data.name;
        document.getElementById('modal-mat-name').textContent = data.name;
        document.getElementById('modal-mat-unit').textContent = `หน่วยซื้อ: ${data.purchase_unit}`;
        document.getElementById('modal-mat-price').textContent = `฿${formatLatestPrice(data.latest_cost_price)} /${data.purchase_unit}`;
        document.getElementById('modal-mat-stick').textContent = `฿${Number(data.cost_per_stick).toFixed(2)} /ไม้`;
        document.getElementById('modal-mat-yield').textContent = `${Number(data.yield_sticks_per_unit).toFixed(2)} ไม้/หน่วย`;
        document.getElementById('modal-mat-vendor').textContent = data.vendor_name || 'ไม่ระบุ';
        document.getElementById('modal-mat-invoice').textContent = data.invoice_number ? `บิลล่าสุด: ${data.invoice_number}` : '';

        const badgeEl = document.getElementById('modal-mat-badge');
        const diff = Number(data.price_change_amount || 0);
        const pct = Number(data.price_change_percent || 0);
        if (diff > 0) {
            badgeEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FDE8EA] text-[#E60023]';
            badgeEl.textContent = `▲ ราคาขึ้น ${Math.abs(diff)} บ. (${Math.abs(pct)}%)`;
        } else if (diff < 0) {
            badgeEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#DDF8E8] text-[#15803D]';
            badgeEl.textContent = `▼ ราคาลด ${Math.abs(diff)} บ. (${Math.abs(pct)}%)`;
        } else {
            badgeEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-white/20 text-white';
            badgeEl.textContent = '▬ ราคาคงเดิม';
        }

        const availBadgeEl = document.getElementById('modal-mat-avail-badge');
        if (availBadgeEl) {
            if (data.is_available_today) {
                availBadgeEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#D4F435] text-[#143D02] border border-[#BDEB18] shadow-2xs gap-1';
                let reason = 'มีรายการพร้อมขาย';
                if (data.today_opening_sticks > 0 || data.today_stock_sticks > 0) {
                    reason += ` (ยกมา ${data.today_opening_sticks} • สต็อก ${data.today_stock_sticks} ไม้)`;
                }
                availBadgeEl.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-[#15803D] animate-pulse"></span><span>${reason}</span>`;
            } else {
                availBadgeEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-white/15 text-rose-100 border border-white/20 gap-1';
                availBadgeEl.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-white/40"></span><span>ไม่พบรายการ</span>`;
            }
        }

        const listEl = document.getElementById('modal-mat-history-list');
        if (Array.isArray(data.histories) && data.histories.length > 0) {
            listEl.innerHTML = data.histories.map(h => `
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/70 text-xs">
                    <div>
                        <span class="font-bold text-slate-800">${h.date}</span>
                        <span class="text-slate-500"> • ${h.vendor_name}</span>
                        ${h.invoice_number ? `<span class="text-[10.5px] text-slate-400 block">บิล: ${h.invoice_number}</span>` : ''}
                    </div>
                    <div class="text-right">
                        <strong class="text-slate-900 font-bold">฿${Number(h.purchase_unit_price).toFixed(2)}</strong>
                        <span class="text-[11px] text-[#E60023] font-semibold block">ไม้ละ ฿${Number(h.cost_per_stick).toFixed(2)}</span>
                    </div>
                </div>
            `).join('');
        } else {
            listEl.innerHTML = `<div class="p-4 rounded-xl bg-slate-50 text-center text-xs text-slate-400">ยังไม่มีประวัติราคา รอสแกนบิลใหม่</div>`;
        }

        // Render Scan Aliases in Detail Modal (ชื่อสำหรับสแกน & ร้านค้าที่ตรงกัน)
        const scanWrap = document.getElementById('modal-mat-scan-names-wrap');
        const scanList = document.getElementById('modal-mat-scan-names-list');
        if (scanWrap && scanList) {
            scanList.innerHTML = '';
            const scanNames = Array.isArray(data.scan_names) ? data.scan_names : [];
            const validScanNames = scanNames.filter(sn => {
                if (typeof sn === 'object' && sn !== null) return Boolean(sn.name && String(sn.name).trim() !== '');
                return Boolean(sn && String(sn).trim() !== '');
            });
            if (validScanNames.length > 0) {
                validScanNames.forEach(sn => {
                    const name = typeof sn === 'object' && sn !== null ? sn.name : String(sn);
                    const vendor = typeof sn === 'object' && sn !== null ? (sn.vendor || '') : '';
                    const badge = document.createElement('span');
                    badge.className = 'px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-800 text-[11px] font-semibold shadow-2xs inline-flex items-center gap-1.5';
                    if (vendor) {
                        badge.innerHTML = `<span class="text-slate-400 font-normal">#</span> <span>${escapeHtml(String(name).trim())}</span> <span class="px-1.5 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium border border-slate-200/60">${escapeHtml(String(vendor).trim())}</span>`;
                    } else {
                        badge.innerHTML = `<span class="text-slate-400 font-normal">#</span> <span>${escapeHtml(String(name).trim())}</span>`;
                    }
                    scanList.appendChild(badge);
                });
                scanWrap.classList.remove('hidden');
            } else {
                scanWrap.classList.add('hidden');
            }
        }

        updateMaterialApprovalModalUI(Boolean(data.is_approved_for_sale));

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeMaterialDetailModal() {
        const modal = document.getElementById('mat-detail-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function switchFromDetailToEditModal() {
        closeMaterialDetailModal();
        if (activeDetailCardEl) {
            openMaterialEditModalByCard(null, activeDetailCardEl);
        }
    }

    function updateMaterialApprovalModalUI(isApproved) {
        const saleBadge = document.getElementById('modal-mat-sale-badge');
        const btn = document.getElementById('btn-toggle-mat-approval');

        if (saleBadge) {
            if (isApproved) {
                saleBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/25 text-emerald-100 border border-emerald-400/40 gap-1';
                saleBadge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span><span>✓ อนุมัติขาย (ออนไลน์ & แคชเชียร์)</span>`;
            } else {
                saleBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-500/20 text-amber-100 border border-amber-300/30 gap-1';
                saleBadge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-amber-300"></span><span>✗ ยังไม่อนุมัติ (ซ่อนจากออนไลน์ & แคชเชียร์)</span>`;
            }
        }

        if (btn) {
            if (isApproved) {
                btn.className = 'w-9 h-9 rounded-xl transition-all shadow-xs cursor-pointer select-none inline-flex items-center justify-center shrink-0 bg-emerald-600 hover:bg-emerald-700 text-white';
                btn.title = 'อนุมัติแล้ว (คลิกเพื่อปิดการแสดงผลในหน้าสั่งออนไลน์และแคชเชียร์)';
            } else {
                btn.className = 'w-9 h-9 rounded-xl transition-all shadow-xs cursor-pointer select-none inline-flex items-center justify-center shrink-0 bg-white hover:bg-emerald-50 text-emerald-600 border border-emerald-400 hover:border-emerald-600';
                btn.title = 'คลิกเพื่ออนุมัติให้แสดงในหน้าสั่งออนไลน์และแคชเชียร์ทันที';
            }
        }
    }

    async function toggleCurrentMaterialApproval() {
        if (!activeDetailCardEl) return;
        const rawJson = activeDetailCardEl.getAttribute('data-modal');
        if (!rawJson) return;
        let data;
        try {
            data = JSON.parse(rawJson);
        } catch (e) {
            return;
        }

        const btn = document.getElementById('btn-toggle-mat-approval');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-75');
        }

        try {
            const url = `/admin/price-tracker/materials/${data.id}/toggle-approval`;
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    is_available: !data.is_approved_for_sale
                })
            });

            const resData = await res.json();
            if (res.ok && resData.success) {
                data.is_approved_for_sale = resData.is_available;
                activeDetailCardEl.setAttribute('data-modal', JSON.stringify(data));
                updateMaterialApprovalModalUI(Boolean(resData.is_available));
                showPriceToast(resData.message);

                if ('BroadcastChannel' in window) {
                    try {
                        const channel = new BroadcastChannel('mala_inventory_sync_channel');
                        channel.postMessage({ type: 'product_approval_toggled', id: data.id, is_available: resData.is_available });
                    } catch (e) {}
                }
            } else {
                alert(resData.message || 'เกิดข้อผิดพลาดในการบันทึกสถานะอนุมัติ');
            }
        } catch (err) {
            console.error('Toggle material approval error:', err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-75');
            }
        }
    }

    // Dynamic Row Helpers for Scan Names (ชื่อสำหรับสแกน)
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function addScanNameInputRow(nameVal = '', vendorVal = '') {
        const container = document.getElementById('edit-mat-scan-names-container');
        if (!container) return;
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2 p-1.5 rounded-xl bg-white border border-slate-200/90 shadow-2xs hover:border-slate-300 transition-all scan-name-row';
        row.innerHTML = `
            <!-- ฝั่งซ้าย: ชื่อสำหรับสแกนบนใบเสร็จ -->
            <div class="relative flex-1 min-w-0">
                <span class="absolute left-2.5 top-2 text-slate-400 pointer-events-none">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <input
                    type="text"
                    name="scan_names[]"
                    value="${escapeHtml(nameVal)}"
                    placeholder="ชื่อสำหรับสแกน (เช่น สภ.ไก่, ไส้กรอกไก่ CP)"
                    class="w-full pl-8 pr-2 py-1 rounded-lg bg-transparent border-0 text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-300"
                >
            </div>

            <!-- เส้นคั่นแบ่ง 2 ฝั่ง -->
            <div class="h-5 w-px bg-slate-200 shrink-0"></div>

            <!-- ฝั่งขวา: แสดงชื่อร้านที่สแกนตรงกับชื่อไหนจะแสดงชื่อร้านนั้น -->
            <div class="relative w-44 sm:w-52 shrink-0">
                <span class="absolute left-2.5 top-2 text-slate-400 pointer-events-none">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </span>
                <input
                    type="text"
                    name="scan_vendors[]"
                    value="${escapeHtml(vendorVal)}"
                    placeholder="ชื่อร้าน (เช่น โลตัส, แม็คโคร)"
                    list="vendor-presets-datalist"
                    class="w-full pl-7 pr-2 py-1 rounded-lg bg-transparent border-0 text-xs font-medium text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-300"
                >
            </div>

            <!-- ปุ่มลบแถว (ไอคอนสีเดียว เรียบหรู) -->
            <button
                type="button"
                onclick="removeScanNameInputRow(this)"
                title="ลบแถวนี้"
                class="w-7 h-7 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-400 hover:text-rose-600 border border-slate-200/80 hover:border-rose-200 flex items-center justify-center transition-all cursor-pointer shrink-0"
            >
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        `;
        container.appendChild(row);
    }

    function removeScanNameInputRow(btn) {
        const row = btn.closest('.scan-name-row');
        if (row) row.remove();
        const container = document.getElementById('edit-mat-scan-names-container');
        if (container && container.children.length === 0) {
            addScanNameInputRow('', '');
        }
    }

    // 8. POPUP 1: Open & Submit Material Create & Edit Modal
    function openCreateMaterialModal() {
        const form = document.getElementById('mat-edit-form');
        if (form) form.reset();

        document.getElementById('edit-mat-id').value = '';

        const titleEl = document.getElementById('mat-edit-modal-title');
        const subtitleEl = document.getElementById('mat-edit-modal-subtitle');
        const submitBtn = document.getElementById('edit-mat-submit-btn');
        if (titleEl) titleEl.textContent = 'แก้ไขข้อมูลและรูปภาพวัตถุดิบ';
        if (subtitleEl) subtitleEl.textContent = 'ปรับแก้ชื่อ หน่วยซื้อ ราคา ต้นทุนต่อไม้ ร้านค้าอ้างอิง และรูปภาพได้ทันที';
        if (submitBtn) {
            submitBtn.innerHTML = `
                <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <span>บันทึกการแก้ไขรายการ</span>
            `;
        }

        const defaultImg = "{{ asset('assets/ingredients/chicken-tendon.png') }}";
        const previewImg = document.getElementById('edit-mat-preview-img');
        const urlInput = document.getElementById('edit-mat-image-url');
        if (previewImg) previewImg.src = defaultImg;
        if (urlInput) urlInput.value = defaultImg;

        // Populate 1 empty row for scan names & store as shown in Image 2
        const scanNamesContainer = document.getElementById('edit-mat-scan-names-container');
        if (scanNamesContainer) {
            scanNamesContainer.innerHTML = '';
            addScanNameInputRow('', '');
        }

        document.getElementById('edit-mat-name').value = '';
        document.getElementById('edit-mat-unit').value = 'ชิ้น';
        document.getElementById('edit-mat-vendor').value = '';
        document.getElementById('edit-mat-price').value = '';
        document.getElementById('edit-mat-yield').value = '10.00';
        document.getElementById('edit-mat-stick').value = '';
        const sellingPriceInput = document.getElementById('edit-mat-selling-price');
        if (sellingPriceInput) sellingPriceInput.value = '';

        const modal = document.getElementById('mat-edit-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        setTimeout(() => document.getElementById('edit-mat-name')?.focus(), 60);
    }

    function openMaterialEditModalByCard(event, cardEl) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        if (!cardEl) return;

        const rawJson = cardEl.getAttribute('data-modal');
        if (!rawJson) return;
        let data;
        try {
            data = JSON.parse(rawJson);
        } catch (e) {
            return;
        }

        const titleEl = document.getElementById('mat-edit-modal-title');
        const subtitleEl = document.getElementById('mat-edit-modal-subtitle');
        const submitBtn = document.getElementById('edit-mat-submit-btn');
        if (titleEl) titleEl.textContent = 'แก้ไขข้อมูลและรูปภาพวัตถุดิบ';
        if (subtitleEl) subtitleEl.textContent = 'ปรับแก้ชื่อ หน่วยซื้อ ราคา ต้นทุนต่อไม้ ร้านค้าอ้างอิง และรูปภาพได้ทันที';
        if (submitBtn) {
            submitBtn.innerHTML = `
                <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                <span>บันทึกการแก้ไขรายการ</span>
            `;
        }

        const fileInput = document.getElementById('edit-mat-file-input');
        if (fileInput) fileInput.value = '';

        document.getElementById('edit-mat-id').value = data.id;
        document.getElementById('edit-mat-preview-img').src = data.image_url;
        document.getElementById('edit-mat-image-url').value = data.image_url || '';
        document.getElementById('edit-mat-name').value = data.name || '';
        document.getElementById('edit-mat-unit').value = data.purchase_unit || '';
        document.getElementById('edit-mat-vendor').value = data.vendor_name && data.vendor_name !== 'ไม่ระบุ' ? data.vendor_name : '';
        document.getElementById('edit-mat-price').value = Number(data.latest_cost_price || 0).toFixed(2);
        document.getElementById('edit-mat-yield').value = Number(data.yield_sticks_per_unit || 10).toFixed(2);
        document.getElementById('edit-mat-stick').value = Number(data.cost_per_stick || 0).toFixed(2);
        const sellingPriceInput = document.getElementById('edit-mat-selling-price');
        if (sellingPriceInput) {
            sellingPriceInput.value = data.selling_price ? Number(data.selling_price).toFixed(0) : '';
        }

        const catSelect = document.getElementById('edit-mat-category');
        if (catSelect && data.category_slug) {
            catSelect.value = data.category_slug;
        }

        // Populate Scan Names & Store/Vendors (ฝั่งซ้าย: ชื่อสำหรับสแกน, ฝั่งขวา: ร้านที่สแกนตรงกัน)
        const scanNamesContainer = document.getElementById('edit-mat-scan-names-container');
        if (scanNamesContainer) {
            scanNamesContainer.innerHTML = '';
            const scanNames = Array.isArray(data.scan_names) ? data.scan_names : [];
            const validScanNames = scanNames.filter(sn => {
                if (typeof sn === 'object' && sn !== null) return Boolean(sn.name && String(sn.name).trim() !== '');
                return Boolean(sn && String(sn).trim() !== '');
            });
            if (validScanNames.length > 0) {
                validScanNames.forEach(sn => {
                    const name = typeof sn === 'object' && sn !== null ? sn.name : String(sn);
                    const vendor = typeof sn === 'object' && sn !== null ? (sn.vendor || '') : '';
                    addScanNameInputRow(String(name).trim(), String(vendor).trim());
                });
            } else {
                addScanNameInputRow('', '');
            }
        }

        const modal = document.getElementById('mat-edit-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeMaterialEditModal() {
        const modal = document.getElementById('mat-edit-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Helper function to compress and resize image client-side for maximum clarity and fast upload
    function compressImageFile(file, maxWidth = 1200, quality = 0.92) {
        return new Promise((resolve) => {
            if (!file || !file.type || !file.type.startsWith('image/') || file.type === 'image/svg+xml') {
                return resolve({ file: file, base64: null });
            }
            const reader = new FileReader();
            reader.onload = e => {
                const base64Data = e.target.result;
                const img = new Image();
                img.onload = () => {
                    let w = img.width;
                    let h = img.height;
                    if (w > maxWidth || h > maxWidth) {
                        if (w > h) {
                            h = Math.round((h * maxWidth) / w);
                            w = maxWidth;
                        } else {
                            w = Math.round((w * maxWidth) / h);
                            h = maxWidth;
                        }
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width = w;
                    canvas.height = h;
                    const ctx = canvas.getContext('2d');
                    ctx.imageSmoothingEnabled = true;
                    ctx.imageSmoothingQuality = 'high';
                    ctx.drawImage(img, 0, 0, w, h);

                    const mime = (file.type === 'image/png' && file.size < 600000) ? 'image/png' : 'image/jpeg';
                    const compressedBase64 = canvas.toDataURL(mime, quality);

                    canvas.toBlob(blob => {
                        if (blob) {
                            const ext = mime === 'image/png' ? '.png' : '.jpg';
                            const newFile = new File([blob], file.name.replace(/\.[^.]+$/, '') + ext, { type: mime });
                            resolve({ file: newFile, base64: compressedBase64 });
                        } else {
                            resolve({ file: file, base64: base64Data });
                        }
                    }, mime, quality);
                };
                img.onerror = () => resolve({ file: file, base64: base64Data });
                img.src = base64Data;
            };
            reader.onerror = () => resolve({ file: file, base64: null });
            reader.readAsDataURL(file);
        });
    }

    async function previewMaterialUploadFile(input) {
        if (!input.files || !input.files[0]) return;
        const originalFile = input.files[0];

        // 1. แสดงสถานะกำลังบีบอัดและแปลงรูปภาพ
        const urlInput = document.getElementById('edit-mat-image-url');
        const prevUrl = urlInput ? urlInput.value : '';
        if (urlInput) {
            urlInput.value = 'กำลังบีบอัดภาพและแปลงเป็น URL...';
            urlInput.classList.add('animate-pulse');
        }

        // 2. บีบอัดภาพให้คมชัดสูงสุด (1200px คุณภาพ 92%)
        const compressed = await compressImageFile(originalFile, 1200, 0.92);
        const fileToSend = compressed.file;
        const base64ToSend = compressed.base64;

        // แสดงตัวอย่างภาพทันที
        const previewImg = document.getElementById('edit-mat-preview-img');
        if (previewImg && base64ToSend) {
            previewImg.src = base64ToSend;
        }

        // 3. ส่งข้อมูลขึ้นเซิร์ฟเวอร์เพื่อแปลงเป็น URL ถาวร
        const formData = new FormData();
        if (base64ToSend) {
            formData.append('image_base64', base64ToSend);
        }
        if (fileToSend && fileToSend.size < 1900000) {
            formData.append('image_file', fileToSend);
        }
        formData.append('_token', csrfToken);

        try {
            const res = await fetch("{{ route('admin.receipts.price_tracker.upload_image') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });
            const data = await res.json();
            if (data.success && data.url) {
                if (urlInput) {
                    urlInput.value = data.url;
                    urlInput.classList.remove('animate-pulse');
                }
                if (previewImg) previewImg.src = data.url;
                input.value = ''; // เคลียร์ไฟล์ต้นฉบับออกจาก input ป้องกันการส่งซ้ำตอนบันทึก
                showPriceToast('บีบอัดภาพคมชัด & แปลงเป็น URL สำเร็จ');
            } else {
                if (urlInput) {
                    urlInput.value = prevUrl;
                    urlInput.classList.remove('animate-pulse');
                }
                alert(data.message || 'เกิดข้อผิดพลาดในการแปลงรูปภาพเป็น URL');
            }
        } catch (err) {
            console.error('Upload failed:', err);
            if (urlInput) {
                urlInput.value = prevUrl;
                urlInput.classList.remove('animate-pulse');
            }
            alert('ไม่สามารถอัปโหลดได้ กรุณาลองใหม่อีกครั้ง');
        }
    }

    function selectPresetMaterialImage(url) {
        const fileInput = document.getElementById('edit-mat-file-input');
        if (fileInput) fileInput.value = '';
        document.getElementById('edit-mat-image-url').value = url;
        document.getElementById('edit-mat-preview-img').src = url;
    }

    function recalculateModalStickCost() {
        const price = parseFloat(document.getElementById('edit-mat-price')?.value || '0');
        const yieldSticks = parseFloat(document.getElementById('edit-mat-yield')?.value || '1');
        if (yieldSticks > 0) {
            const stickCost = price / yieldSticks;
            document.getElementById('edit-mat-stick').value = stickCost.toFixed(2);
        }
    }

    async function submitMaterialEditForm(event) {
        event.preventDefault();
        const form = document.getElementById('mat-edit-form');
        const matId = document.getElementById('edit-mat-id').value;
        const submitBtn = document.getElementById('edit-mat-submit-btn');
        if (!form) return;

        const isCreate = !matId;
        const targetUrl = isCreate
            ? "{{ route('admin.receipts.price_tracker.store_material') }}"
            : `/admin/price-tracker/materials/${matId}`;

        const formData = new FormData(form);
        if (!document.getElementById('edit-mat-record-history').checked) {
            formData.set('record_history', '0');
        }

        // หากมี URL รูปภาพที่แปลงแล้ว ให้ใช้ URL นั้นเป็นหลัก และลบ image_file ดิบออก
        const currentImgUrl = document.getElementById('edit-mat-image-url')?.value;
        if (currentImgUrl && (currentImgUrl.startsWith('http') || currentImgUrl.startsWith('assets/'))) {
            formData.set('image_url', currentImgUrl);
            formData.delete('image_file');
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'กำลังบันทึก...';
        }

        try {
            const res = await fetch(targetUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });

            if (res.status === 401) {
                alert('เซสชันการเข้าสู่ระบบหมดอายุ กรุณาเข้าสู่ระบบใหม่อีกครั้ง');
                window.location.href = "{{ route('login') }}?redirect=" + encodeURIComponent(window.location.pathname);
                return;
            }

            const result = await res.json();
            if (!res.ok || !result.success) {
                alert(result.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                return;
            }

            if (isCreate) {
                closeMaterialEditModal();
                showPriceToast(result.message || 'เพิ่มรายการวัตถุดิบและข้อมูลสต็อกเรียบร้อยแล้ว');
                setTimeout(() => window.location.reload(), 300);
                return;
            }

            const item = result.material;
            const card = document.getElementById(`mat-card-${item.id}`);
            if (card) {
                let currentModal = {};
                try {
                    currentModal = JSON.parse(card.getAttribute('data-modal') || '{}');
                } catch (e) {}

                const resolvedImg = item.image_url || currentModal.image_url;
                const scanNamesList = Array.isArray(item.scan_names) ? item.scan_names : [];
                const updatedModal = {
                    ...currentModal,
                    name: item.name,
                    scan_names: scanNamesList,
                    purchase_unit: item.purchase_unit,
                    latest_cost_price: item.latest_cost_price,
                    yield_sticks_per_unit: item.yield_sticks_per_unit,
                    cost_per_stick: item.cost_per_stick,
                    vendor_name: item.vendor_name,
                    category_slug: item.category_slug || currentModal.category_slug,
                    image_url: resolvedImg,
                    price_change_amount: item.price_change_amount,
                    price_change_percent: item.price_change_percent,
                };

                const scanNamesSearchStr = scanNamesList.map(sn => {
                    if (typeof sn === 'object' && sn !== null) {
                        return `${sn.name || ''} ${sn.vendor || ''}`.trim();
                    }
                    return String(sn || '').trim();
                }).filter(Boolean).join(' ');
                card.setAttribute('data-modal', JSON.stringify(updatedModal));
                card.setAttribute('data-name', `${item.name} ${item.vendor_name} ${scanNamesSearchStr}`.toLowerCase());
                card.setAttribute('data-price', String(item.latest_cost_price));
                card.setAttribute('data-stick', String(item.cost_per_stick));
                card.setAttribute('data-change', String(Math.abs(item.price_change_amount || 0)));
                if (item.category_slug) {
                    card.setAttribute('data-cats', item.category_slug);
                }

                const imgEl = card.querySelector('[data-role="mat-img"]');
                if (imgEl && resolvedImg) imgEl.src = resolvedImg;

                const nameEl = card.querySelector('[data-role="mat-name"]');
                if (nameEl) nameEl.textContent = item.name;

                const unitEl = card.querySelector('[data-role="mat-unit"]');
                if (unitEl) unitEl.textContent = `หน่วยซื้อ: ${item.purchase_unit}`;

                const priceEl = card.querySelector('[data-role="latest-price"]');
                if (priceEl) priceEl.textContent = `฿${formatLatestPrice(item.latest_cost_price)}`;

                const unitSuffixEl = card.querySelector('[data-role="latest-unit-suffix"]');
                if (unitSuffixEl) unitSuffixEl.textContent = `/${item.purchase_unit}`;

                const stickEl = card.querySelector('[data-role="stick-cost"]');
                if (stickEl) stickEl.textContent = `฿${Number(item.cost_per_stick).toFixed(2)}`;

                const yieldSuffixEl = card.querySelector('[data-role="yield-suffix"]');
                if (yieldSuffixEl) yieldSuffixEl.textContent = `(${Number(item.yield_sticks_per_unit).toFixed(2)} ไม้/หน่วย)`;

                const vendorEl = card.querySelector('[data-role="vendor-name"]');
                if (vendorEl) vendorEl.textContent = item.vendor_name;

                const badgeWrap = card.querySelector('[data-role="status-badge"]');
                if (badgeWrap) {
                    const diff = Number(item.price_change_amount || 0);
                    const pct = Number(item.price_change_percent || 0);
                    if (diff > 0) {
                        badgeWrap.innerHTML = `<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold inline-flex items-center gap-1 bg-[#FDE8EA] text-[#E60023]"><span>▲ ขึ้น</span><span>${Math.abs(diff)} บ.</span><span>(${Math.abs(pct)}%)</span></span>`;
                    } else if (diff < 0) {
                        badgeWrap.innerHTML = `<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold inline-flex items-center gap-1 bg-[#DDF8E8] text-[#15803D]"><span>▼ ลด</span><span>${Math.abs(diff)} บ.</span><span>(${Math.abs(pct)}%)</span></span>`;
                    } else {
                        badgeWrap.innerHTML = `<span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#F1EFF8] text-[#534B6E] inline-flex items-center gap-1"><span>▬ ราคาคงเดิม</span></span>`;
                    }
                }
            }

            closeMaterialEditModal();
            applyMaterialFilters();
            showPriceToast(result.message || 'บันทึกรายการเรียบร้อยแล้ว');

            // 🔄 Broadcast to POS Cashier Screen to synchronize image & name immediately!
            if ('BroadcastChannel' in window) {
                try {
                    const posChannel = new BroadcastChannel('mala_pos_sync_channel');
                    posChannel.postMessage({
                        action: 'material_updated',
                        material_id: item.id,
                        name: item.name,
                        image_url: resolvedImg,
                    });
                } catch (e) {}
            }
        } catch (err) {
            alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>💾 บันทึกการแก้ไขรายการ</span>';
            }
        }
    }

    // 9. POPUP 2: Open & Submit Hero Cover Image & Banner Modal
    function openHeroEditModal(event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        const fileInput = document.getElementById('edit-hero-file-input');
        if (fileInput) fileInput.value = '';

        const currentHeroSrc = document.getElementById('hero-cover-img')?.src || '';
        document.getElementById('edit-hero-preview-img').src = currentHeroSrc;
        document.getElementById('edit-hero-image-url').value = currentHeroSrc;
        document.getElementById('edit-hero-title').value = document.getElementById('hero-info-title')?.textContent.trim() || '';
        document.getElementById('edit-hero-subtitle').value = document.getElementById('hero-info-subtitle')?.textContent.trim() || '';

        const modal = document.getElementById('hero-edit-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeHeroEditModal() {
        const modal = document.getElementById('hero-edit-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    async function previewHeroUploadFile(input) {
        if (!input.files || !input.files[0]) return;
        const originalFile = input.files[0];

        const urlInput = document.getElementById('edit-hero-image-url');
        const prevUrl = urlInput ? urlInput.value : '';
        if (urlInput) {
            urlInput.value = 'กำลังแปลงรูปเป็น URL...';
            urlInput.classList.add('animate-pulse');
        }

        const compressed = await compressImageFile(originalFile, 1200, 0.88);
        const fileToSend = compressed.file || originalFile;
        const base64ToSend = compressed.base64;

        const previewImg = document.getElementById('edit-hero-preview-img');
        if (previewImg && base64ToSend) {
            previewImg.src = base64ToSend;
        }

        const formData = new FormData();
        if (base64ToSend) {
            formData.append('image_base64', base64ToSend);
        }
        if (fileToSend && fileToSend.size < 1900000) {
            formData.append('image_file', fileToSend);
        }
        formData.append('_token', csrfToken);

        try {
            const res = await fetch("{{ route('admin.receipts.price_tracker.upload_image') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });
            const data = await res.json();
            if (data.success && data.url) {
                if (urlInput) {
                    urlInput.value = data.url;
                    urlInput.classList.remove('animate-pulse');
                }
                if (previewImg) previewImg.src = data.url;
                input.value = '';
                showPriceToast('แปลงรูปภาพปกเป็น URL เรียบร้อยแล้ว');
            } else {
                if (urlInput) {
                    urlInput.value = prevUrl;
                    urlInput.classList.remove('animate-pulse');
                }
            }
        } catch (err) {
            console.error('Hero upload failed:', err);
            if (urlInput) {
                urlInput.value = prevUrl;
                urlInput.classList.remove('animate-pulse');
            }
        }
    }

    function selectPresetHeroImage(url) {
        const fileInput = document.getElementById('edit-hero-file-input');
        if (fileInput) fileInput.value = '';
        document.getElementById('edit-hero-image-url').value = url;
        document.getElementById('edit-hero-preview-img').src = url;
    }

    async function submitHeroEditForm(event) {
        event.preventDefault();
        const form = document.getElementById('hero-edit-form');
        const submitBtn = document.getElementById('edit-hero-submit-btn');
        if (!form) return;

        const formData = new FormData(form);
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'กำลังบันทึก...';
        }

        try {
            const res = await fetch('{{ route('admin.receipts.price_tracker.update_hero') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });
            const result = await res.json();
            if (!res.ok || !result.success) {
                alert(result.message || 'เกิดข้อผิดพลาดในการบันทึกภาพปก');
                return;
            }

            if (result.hero) {
                const heroImgEl = document.getElementById('hero-cover-img');
                if (heroImgEl && result.hero.hero_image) {
                    heroImgEl.src = result.hero.hero_image;
                }
                const titleEl = document.getElementById('hero-info-title');
                if (titleEl && result.hero.hero_title) {
                    titleEl.textContent = result.hero.hero_title;
                }
                const subEl = document.getElementById('hero-info-subtitle');
                if (subEl && result.hero.hero_subtitle) {
                    subEl.textContent = result.hero.hero_subtitle;
                }
            }

            closeHeroEditModal();
            showPriceToast(result.message || 'อัปเดตภาพปกเรียบร้อยแล้ว');
        } catch (err) {
            alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้ กรุณาลองใหม่อีกครั้ง');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>💾 บันทึกภาพปกและข้อมูล</span>';
            }
        }
    }

    // 10. Real-Time Multi-Tab Price & Inventory Stock Synchronization
    async function refreshPriceTrackerData() {
        try {
            const res = await fetch('{{ route('admin.receipts.price_tracker') }}?json=1', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const payload = await res.json();
            if (!payload || !Array.isArray(payload.materials)) return;

            if (payload.summary) {
                const bToday = document.getElementById('badge-count-today');
                const bAll = document.getElementById('badge-count-all');
                if (bToday) bToday.textContent = String(typeof payload.summary.today_bought_count !== 'undefined' ? payload.summary.today_bought_count : (payload.summary.today_available_count || 0));
                if (bAll) bAll.textContent = String(payload.summary.total_materials || 0);
            }

            payload.materials.forEach(item => {
                const card = document.getElementById(`mat-card-${item.id}`);
                if (!card) return;

                card.dataset.price = String(item.latest_cost_price);
                card.dataset.stick = String(item.cost_per_stick);
                card.dataset.change = String(Math.abs(item.price_change_amount || 0));
                card.dataset.available = item.is_available_today ? '1' : '0';
                card.dataset.bought = item.is_bought_today ? '1' : '0';
                card.dataset.opening = String(item.today_opening_sticks || 0);
                card.dataset.stock = String(item.today_stock_sticks || 0);

                const priceEl = card.querySelector('[data-role="latest-price"]');
                if (priceEl) priceEl.textContent = `฿${formatLatestPrice(item.latest_cost_price)}`;

                const stickEl = card.querySelector('[data-role="stick-cost"]');
                if (stickEl) stickEl.textContent = `฿${Number(item.cost_per_stick).toFixed(2)}`;

                const vendorEl = card.querySelector('[data-role="vendor-name"]');
                if (vendorEl) vendorEl.textContent = item.vendor_name;

                // Update Availability Badge on Card
                const availBadge = card.querySelector('.mat-avail-badge');
                if (availBadge) {
                    if (item.is_bought_today) {
                        availBadge.className = 'mat-avail-badge px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#D4F435] text-[#143D02] border border-[#BDEB18] shadow-2xs inline-flex items-center gap-1 select-none whitespace-nowrap';
                        availBadge.title = 'ซื้อวันนี้: มีรายการสั่งซื้อในใบเสร็จวันนี้';
                        availBadge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-[#15803D] animate-pulse"></span><span>ซื้อวันนี้</span>';
                    } else if (item.is_available_today) {
                        availBadge.className = 'mat-avail-badge px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/90 shadow-2xs inline-flex items-center gap-1 select-none whitespace-nowrap';
                        availBadge.title = `สต็อกพร้อมขาย: (ยกมา ${item.today_opening_sticks || 0} ไม้ • ปัจจุบัน ${item.today_stock_sticks || 0} ไม้)`;
                        availBadge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span><span>สต็อกพร้อมขาย</span>';
                    } else {
                        availBadge.className = 'mat-avail-badge px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-400 border border-slate-200/90 inline-flex items-center gap-1 select-none whitespace-nowrap';
                        availBadge.title = 'ไม่มีรายการวันนี้: ไม่พบการซื้อในวันนี้ และไม่มีสต็อกไม้คงเหลือ';
                        availBadge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span><span>ไม่พบรายการ</span>';
                    }
                }

                if (typeof item.is_approved_for_sale !== 'undefined') {
                    const rawJson = card.getAttribute('data-modal');
                    if (rawJson) {
                        try {
                            const d = JSON.parse(rawJson);
                            d.is_approved_for_sale = item.is_approved_for_sale;
                            card.setAttribute('data-modal', JSON.stringify(d));
                        } catch (e) {}
                    }
                }
            });

            applyMaterialFilters();
        } catch (err) {
            // Silent background poll
        }
    }

    // 10. POPUP 3: CREATE NEW CATEGORY
    function openCreateCategoryModal() {
        const modal = document.getElementById('category-create-modal');
        if (!modal) return;
        document.getElementById('category-create-form').reset();
        document.getElementById('create-cat-preview-img').src = "{{ asset('assets/categories/all.png') }}";
        const checkEl = document.getElementById('create-cat-is-active');
        if (checkEl) checkEl.checked = true;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => document.getElementById('create-cat-name')?.focus(), 50);
    }

    function closeCreateCategoryModal() {
        const modal = document.getElementById('category-create-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function autoGenerateCategorySlug(name) {
        const slugInput = document.getElementById('create-cat-slug');
        if (!slugInput) return;
        const clean = name.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        if (clean) {
            slugInput.value = clean;
        }
    }

    async function previewCategoryUploadFile(input) {
        if (!input.files || !input.files[0]) return;
        const originalFile = input.files[0];

        const urlInput = document.getElementById('create-cat-image-url');
        const prevUrl = urlInput ? urlInput.value : '';
        if (urlInput) {
            urlInput.value = 'กำลังแปลงรูปเป็น URL...';
            urlInput.classList.add('animate-pulse');
        }

        const compressed = await compressImageFile(originalFile, 800, 0.88);
        const fileToSend = compressed.file || originalFile;
        const base64ToSend = compressed.base64;

        const previewImg = document.getElementById('create-cat-preview-img');
        if (previewImg && base64ToSend) {
            previewImg.src = base64ToSend;
        }

        const formData = new FormData();
        if (base64ToSend) {
            formData.append('image_base64', base64ToSend);
        }
        if (fileToSend && fileToSend.size < 1900000) {
            formData.append('image_file', fileToSend);
        }
        formData.append('_token', csrfToken);

        try {
            const res = await fetch("{{ route('admin.receipts.price_tracker.upload_image') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });
            const data = await res.json();
            if (data.success && data.url) {
                if (urlInput) {
                    urlInput.value = data.url;
                    urlInput.classList.remove('animate-pulse');
                }
                if (previewImg) previewImg.src = data.url;
                input.value = '';
                showPriceToast('แปลงรูปไอคอนเป็น URL เรียบร้อยแล้ว');
            } else {
                if (urlInput) {
                    urlInput.value = prevUrl;
                    urlInput.classList.remove('animate-pulse');
                }
            }
        } catch (err) {
            console.error('Category upload failed:', err);
            if (urlInput) {
                urlInput.value = prevUrl;
                urlInput.classList.remove('animate-pulse');
            }
        }
    }

    function updateCategoryPreviewFromUrl(url) {
        if (!url) return;
        document.getElementById('create-cat-preview-img').src = url;
    }

    function selectPresetCategoryImage(url) {
        document.getElementById('create-cat-preview-img').src = url;
        document.getElementById('create-cat-image-url').value = url;
        document.getElementById('create-cat-file-input').value = '';
    }

    async function submitCreateCategoryForm(e) {
        e.preventDefault();
        const form = document.getElementById('category-create-form');
        const submitBtn = document.getElementById('btn-save-category');
        const originalBtnHtml = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>⏳ กำลังบันทึก...</span>';

        const formData = new FormData(form);
        const isActiveCheck = document.getElementById('create-cat-is-active');
        if (isActiveCheck) {
            formData.set('is_active', isActiveCheck.checked ? '1' : '0');
        }

        try {
            const res = await fetch("{{ route('admin.categories.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json();
            if (res.ok && data.success) {
                closeCreateCategoryModal();
                showPriceToast(data.message || 'สร้างแถบหมวดหมู่เรียบร้อยแล้ว');
                setTimeout(() => window.location.reload(), 600);
            } else {
                alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกหมวดหมู่ได้'));
            }
        } catch (err) {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    }

    // 10.1 Category 3-Lines (☰) Settings Dropdowns & Edit/Delete
    let categoriesData = @json($categories->keyBy('slug'));

    // Move dropdown to <body> before positioning: parents with backdrop-filter (sticky bar / modal overlay)
    // become the containing block for position:fixed, which made the menu float away from the ☰ button.
    function positionCategoryDropdown(dropdown, btn) {
        if (dropdown.parentElement !== document.body) {
            document.body.appendChild(dropdown);
        }
        const rect = btn.getBoundingClientRect();
        dropdown.style.position = 'fixed';
        dropdown.style.top = `${rect.bottom + 6}px`;
        dropdown.style.left = `${Math.max(12, Math.min(window.innerWidth - 270, rect.right - 256))}px`;
        dropdown.style.zIndex = '999999';
        dropdown.classList.remove('hidden');
    }

    // Close category dropdowns on scroll/resize so they never drift away from the button
    ['scroll', 'resize'].forEach(evt => window.addEventListener(evt, () => {
        ['category-settings-dropdown', 'main-category-settings-dropdown'].forEach(id => {
            document.getElementById(id)?.classList.add('hidden');
        });
    }, { passive: true }));

    function toggleCategorySettingsDropdown(event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        const dropdown = document.getElementById('category-settings-dropdown');
        const btn = document.getElementById('btn-cat-settings-trigger');
        if (!dropdown || !btn) return;

        if (!dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
            return;
        }

        // Close other dropdowns if open
        const mainCatDropdown = document.getElementById('main-category-settings-dropdown');
        if (mainCatDropdown) mainCatDropdown.classList.add('hidden');
        const masterDropdown = document.getElementById('master-options-dropdown');
        if (masterDropdown) masterDropdown.classList.add('hidden');

        positionCategoryDropdown(dropdown, btn);
    }

    function toggleMainCategorySettingsDropdown(event) {
        if (event) {
            event.stopPropagation();
            event.preventDefault();
        }
        const dropdown = document.getElementById('main-category-settings-dropdown');
        const btn = document.getElementById('btn-cat-main-settings-trigger');
        if (!dropdown || !btn) return;

        if (!dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
            return;
        }

        // Close other dropdowns if open
        const catDropdown = document.getElementById('category-settings-dropdown');
        if (catDropdown) catDropdown.classList.add('hidden');
        const masterDropdown = document.getElementById('master-options-dropdown');
        if (masterDropdown) masterDropdown.classList.add('hidden');

        positionCategoryDropdown(dropdown, btn);
    }

    function openEditCategoryFromDropdown() {
        const dropdown = document.getElementById('category-settings-dropdown');
        if (dropdown) dropdown.classList.add('hidden');

        const catSelect = document.getElementById('edit-mat-category');
        const slug = catSelect ? catSelect.value : '';
        if (slug) {
            openEditCategoryModal(slug);
        } else {
            alert('กรุณาเลือกหมวดหมู่ที่ต้องการแก้ไขก่อน');
        }
    }

    function openEditMainCategoryFromDropdown() {
        const dropdown = document.getElementById('main-category-settings-dropdown');
        if (dropdown) dropdown.classList.add('hidden');

        if (currentMatCategory && currentMatCategory !== 'all') {
            openEditCategoryModal(currentMatCategory);
        } else {
            alert('กรุณาเลือกหมวดหมู่ที่ต้องการแก้ไขก่อน');
        }
    }

    function openEditCategoryModal(slug) {
        const modal = document.getElementById('category-edit-modal');
        if (!modal) return;

        let cat = categoriesData[slug];
        if (!cat) {
            // Find from select options
            const opt = document.querySelector(`#edit-mat-category option[value="${slug}"]`);
            if (opt) {
                cat = {
                    name: opt.getAttribute('data-name') || opt.textContent.trim(),
                    slug: slug,
                    icon: opt.getAttribute('data-icon') || '',
                    is_active: opt.getAttribute('data-active') === '1'
                };
            }
        }

        if (!cat) {
            alert('ไม่พบข้อมูลหมวดหมู่นี้');
            return;
        }

        document.getElementById('edit-cat-target-slug').value = slug;
        document.getElementById('edit-cat-name').value = cat.name || '';
        document.getElementById('edit-cat-slug').value = cat.slug || slug;

        const resolvedIcon = cat.icon
            ? (cat.icon.startsWith('http') ? cat.icon : `/${cat.icon}`)
            : "{{ asset('assets/categories/all.png') }}";

        document.getElementById('edit-cat-preview-img').src = resolvedIcon;
        document.getElementById('edit-cat-image-url').value = cat.icon || '';
        document.getElementById('edit-cat-file-input').value = '';

        const checkEl = document.getElementById('edit-cat-is-active');
        if (checkEl) checkEl.checked = !!cat.is_active;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => document.getElementById('edit-cat-name')?.focus(), 60);
    }

    function closeEditCategoryModal() {
        const modal = document.getElementById('category-edit-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function selectEditPresetCategoryImage(url) {
        document.getElementById('edit-cat-preview-img').src = url;
        document.getElementById('edit-cat-image-url').value = url;
        document.getElementById('edit-cat-file-input').value = '';
    }

    function updateEditCategoryPreviewFromUrl(url) {
        if (!url) return;
        document.getElementById('edit-cat-preview-img').src = url;
    }

    async function previewEditCategoryUploadFile(input) {
        if (!input.files || !input.files[0]) return;
        const originalFile = input.files[0];

        const urlInput = document.getElementById('edit-cat-image-url');
        const prevUrl = urlInput ? urlInput.value : '';
        if (urlInput) {
            urlInput.value = 'กำลังแปลงรูปเป็น URL...';
            urlInput.classList.add('animate-pulse');
        }

        const compressed = await compressImageFile(originalFile, 800, 0.88);
        const fileToSend = compressed.file || originalFile;
        const base64ToSend = compressed.base64;

        const previewImg = document.getElementById('edit-cat-preview-img');
        if (previewImg && base64ToSend) {
            previewImg.src = base64ToSend;
        }

        const formData = new FormData();
        if (base64ToSend) {
            formData.append('image_base64', base64ToSend);
        }
        if (fileToSend && fileToSend.size < 1900000) {
            formData.append('image_file', fileToSend);
        }
        formData.append('_token', csrfToken);

        try {
            const res = await fetch("{{ route('admin.receipts.price_tracker.upload_image') }}", {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });
            const data = await res.json();
            if (data.success && data.url) {
                if (urlInput) {
                    urlInput.value = data.url;
                    urlInput.classList.remove('animate-pulse');
                }
                if (previewImg) previewImg.src = data.url;
                input.value = '';
                showPriceToast('แปลงรูปไอคอนเป็น URL เรียบร้อยแล้ว');
            } else {
                if (urlInput) {
                    urlInput.value = prevUrl;
                    urlInput.classList.remove('animate-pulse');
                }
            }
        } catch (err) {
            console.error('Category upload failed:', err);
            if (urlInput) {
                urlInput.value = prevUrl;
                urlInput.classList.remove('animate-pulse');
            }
        }
    }

    async function submitEditCategoryForm(e) {
        e.preventDefault();
        const form = document.getElementById('category-edit-form');
        const submitBtn = document.getElementById('btn-update-category');
        const targetSlug = document.getElementById('edit-cat-target-slug').value;
        if (!targetSlug) return;

        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>⏳ กำลังบันทึก...</span>';

        const formData = new FormData(form);
        const isActiveCheck = document.getElementById('edit-cat-is-active');
        if (isActiveCheck) {
            formData.set('is_active', isActiveCheck.checked ? '1' : '0');
        }

        const updateUrl = `{{ url('/admin/categories') }}/${encodeURIComponent(targetSlug)}/update`;

        try {
            const res = await fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json();
            if (res.ok && data.success && data.category) {
                const cat = data.category;
                const oldSlug = data.category.old_slug || targetSlug;
                const newSlug = cat.slug;

                // 1. Update categoriesData
                delete categoriesData[oldSlug];
                categoriesData[newSlug] = cat;

                // 2. Update select options in #edit-mat-category
                const opt = document.querySelector(`#edit-mat-category option[value="${oldSlug}"]`);
                if (opt) {
                    opt.value = newSlug;
                    opt.textContent = cat.name;
                    opt.setAttribute('data-name', cat.name);
                    opt.setAttribute('data-icon', cat.icon);
                    opt.setAttribute('data-active', cat.is_active ? '1' : '0');
                }

                // 3. Update category pill button
                const pillBtn = document.querySelector(`.cat-pill-btn[data-cat="${oldSlug}"]`);
                if (pillBtn) {
                    pillBtn.setAttribute('data-cat', newSlug);
                    pillBtn.setAttribute('data-cat-name', cat.name);
                    pillBtn.setAttribute('data-active', cat.is_active ? '1' : '0');
                    pillBtn.setAttribute('onclick', `filterMaterialsByCategory('${newSlug}')`);

                    const img = pillBtn.querySelector('img');
                    if (img && cat.icon) img.src = cat.icon;

                    const labelSpan = pillBtn.querySelector('span.tracking-tight');
                    if (labelSpan) labelSpan.textContent = cat.name;
                }

                // 4. If this category was currently active in view, update active slug & title
                if (currentMatCategory === oldSlug) {
                    currentMatCategory = newSlug;
                    const headingEl = document.getElementById('section-heading-text');
                    if (headingEl) {
                        headingEl.textContent = currentItemsFilter === 'today'
                            ? `วัตถุดิบวันนี้: ${cat.name}`
                            : `หมวดหมู่: ${cat.name}`;
                    }
                    updateCategoryApprovalButtonUI(cat.is_active);
                }

                closeEditCategoryModal();
                showPriceToast(data.message || 'บันทึกการแก้ไขหมวดหมู่สำเร็จแล้ว');
            } else {
                alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกการแก้ไขหมวดหมู่ได้'));
            }
        } catch (err) {
            console.error('Update category error:', err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }
    }

    function confirmDeleteCategoryFromDropdown() {
        const dropdown = document.getElementById('category-settings-dropdown');
        if (dropdown) dropdown.classList.add('hidden');

        const catSelect = document.getElementById('edit-mat-category');
        const slug = catSelect ? catSelect.value : '';
        if (slug) {
            executeDeleteCategory(slug);
        } else {
            alert('กรุณาเลือกหมวดหมู่ที่ต้องการลบก่อน');
        }
    }

    function confirmDeleteMainCategoryFromDropdown() {
        const dropdown = document.getElementById('main-category-settings-dropdown');
        if (dropdown) dropdown.classList.add('hidden');

        if (currentMatCategory && currentMatCategory !== 'all') {
            executeDeleteCategory(currentMatCategory);
        } else {
            alert('กรุณาเลือกหมวดหมู่ที่ต้องการลบก่อน');
        }
    }

    function confirmDeleteCurrentEditCategory() {
        const targetSlug = document.getElementById('edit-cat-target-slug')?.value;
        if (targetSlug) {
            executeDeleteCategory(targetSlug);
        }
    }

    async function executeDeleteCategory(slug) {
        if (!slug || slug === 'all') {
            alert('ไม่สามารถลบหมวดหมู่หลักนี้ได้');
            return;
        }

        let catName = slug;
        if (categoriesData[slug] && categoriesData[slug].name) {
            catName = categoriesData[slug].name;
        } else {
            const opt = document.querySelector(`#edit-mat-category option[value="${slug}"]`);
            if (opt) catName = opt.textContent.trim();
        }

        const confirmMsg = `คุณแน่ใจหรือไม่ว่าต้องการลบหมวดหมู่ "${catName}" ออกจากระบบ?\n(วัตถุดิบและสินค้าในหมวดนี้จะถูกย้ายไปยังหมวดอื่นโดยอัตโนมัติ เพื่อป้องกันข้อมูลสูญหาย)`;
        if (!confirm(confirmMsg)) return;

        const deleteUrl = `{{ url('/admin/categories') }}/${encodeURIComponent(slug)}/delete`;

        try {
            const res = await fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();
            if (res.ok && data.success) {
                // 1. Remove from categoriesData
                delete categoriesData[slug];

                // 2. Remove option from #edit-mat-category
                const opt = document.querySelector(`#edit-mat-category option[value="${slug}"]`);
                if (opt) opt.remove();

                // 3. Remove pill from category bar
                const pillBtn = document.querySelector(`.cat-pill-btn[data-cat="${slug}"]`);
                if (pillBtn) pillBtn.remove();

                // 4. If current view was this category, switch back to 'all'
                if (currentMatCategory === slug) {
                    filterMaterialsByCategory('all');
                }

                closeEditCategoryModal();
                showPriceToast(data.message || `ลบหมวดหมู่ "${catName}" เรียบร้อยแล้ว`);
            } else {
                alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถลบหมวดหมู่ได้'));
            }
        } catch (err) {
            console.error('Delete category error:', err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        let savedCols = 3;
        try {
            const raw = localStorage.getItem('mala_price_tracker_grid_cols');
            if (raw === '4') savedCols = 4;
        } catch (e) {}
        setPriceGridColumns(savedCols);
        applyMaterialFilters();

        // Listen for real-time inventory adjustments and receipt scans across tabs
        if ('BroadcastChannel' in window) {
            try {
                const invChannel = new BroadcastChannel('mala_inventory_sync_channel');
                invChannel.onmessage = () => refreshPriceTrackerData();

                const recChannel = new BroadcastChannel('mala_receipt_sync_channel');
                recChannel.onmessage = () => refreshPriceTrackerData();
            } catch (e) {}
        }

        setInterval(() => {
            if (!document.hidden) {
                refreshPriceTrackerData();
            }
        }, 25000);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                refreshPriceTrackerData();
            }
        });
    });
</script>
@endsection
