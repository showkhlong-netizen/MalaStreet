@extends('layouts.app', ['title' => 'สรุปยอดไม้คงเหลือ & ตรวจนับสต็อก - หม่าล่าสตรีท'])

@section('content')
@php
    // Helper to resolve clean 1:1 product thumbnail for each Mala skewer product
    $resolveProductThumb = function (\App\Models\Product $prod) {
        $name = $prod->name ?? '';
        if (str_contains($name, 'เนื้อ')) {
            return asset('assets/ingredients/beef-ribeye-sliced.png');
        }
        if (str_contains($name, 'หมูสามชั้น') || str_contains($name, 'เบคอน')) {
            return asset('assets/ingredients/pork-belly-sliced.png');
        }
        if (str_contains($name, 'ลูกชิ้น')) {
            return asset('assets/ingredients/fishball-phuket.png');
        }
        if (str_contains($name, 'เต้าหู้')) {
            return asset('assets/ingredients/tofu-fried-roll.png');
        }
        if (str_contains($name, 'เห็ด')) {
            return asset('assets/ingredients/mushroom-fresh.png');
        }
        if (str_contains($name, 'ผัก')) {
            return asset('assets/ingredients/vegetable-greens.png');
        }
        if (str_contains($name, 'เอ็นไก่') || str_contains($name, 'ไก่')) {
            return asset('assets/ingredients/chicken-tendon.png');
        }
        if (str_contains($name, 'ไส้กรอก')) {
            return asset('assets/ingredients/sausage-smoked.png');
        }
        if (!empty($prod->image_url)) {
            return asset($prod->image_url);
        }
        return asset('assets/menu/beef-premium.png');
    };

    $productsCatalog = collect($inventoryRecords)->map(function ($item) use ($resolveProductThumb) {
        $p = $item['product'];
        $r = $item['record'];
        return [
            'id' => $p->id,
            'name' => $p->name,
            'category' => $p->category?->name ?? '-',
            'stock_sticks' => (int) $r->expected_closing_sticks,
            'price_per_stick' => (float) $p->price_per_stick,
            'thumb' => $resolveProductThumb($p),
        ];
    })->values();
@endphp

<div class="min-h-screen bg-[#F7F9FC] pb-14">
    <!-- 1. INVENTORY HERO BANNER (Matching Reference Image 2) -->
    <section class="relative w-full overflow-hidden bg-gradient-to-r from-[#340000] via-[#650000] to-[#340000] border-b border-[#520000] pt-5 pb-11 sm:pt-6 sm:pb-12">
        <!-- Warm ambient glow -->
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_60%_50%,rgba(237,0,56,0.32),transparent_62%)]"></div>

        <!-- Sizzling Mala Skewers Bowl Artwork in Center-Right background -->
        <div class="pointer-events-none hidden lg:flex absolute inset-y-0 right-[25%] xl:right-[27%] items-center justify-center opacity-95 select-none">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 w-20 bg-gradient-to-r from-[#580000] to-transparent z-10"></div>
                <div class="absolute inset-y-0 right-0 w-20 bg-gradient-to-l from-[#4F0000] to-transparent z-10"></div>
                <img
                    src="{{ asset('assets/brand/hero-mala-bowl-clean.png') }}"
                    alt="หม่าล่าสตรีท"
                    class="h-[162px] w-auto object-cover"
                    onerror="this.style.display='none'"
                >
            </div>
        </div>

        <div class="relative z-10 max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <!-- Left: Skewer Icon Box + Heading + Formula Subtitle -->
                <div class="flex items-start sm:items-center gap-4 max-w-3xl">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-[20px] bg-[#FFF0F3] text-[#ED0038] border border-white/90 shadow-lg flex items-center justify-center shrink-0">
                        <!-- Vector Skewer Icon -->
                        <svg class="w-8 h-8 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="4" y1="20" x2="20" y2="4"></line>
                            <circle cx="9" cy="15" r="2.3" fill="currentColor" fill-opacity="0.18"></circle>
                            <circle cx="13" cy="11" r="2.3" fill="currentColor" fill-opacity="0.18"></circle>
                            <circle cx="17" cy="7" r="2.3" fill="currentColor" fill-opacity="0.18"></circle>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-[22px] sm:text-[27px] font-bold text-white tracking-tight leading-snug drop-shadow-xs">
                            ระบบสรุปยอดไม้คงเหลือของทุกรายการ (Inventory)
                        </h1>
                        <p class="text-[12.5px] sm:text-[13.5px] text-rose-100/95 font-normal mt-0.5 leading-relaxed">
                            คำนวณ: <strong class="font-semibold text-white">ยกมาจากเมื่อวาน + ซื้อ/เสียบเพิ่มวันนี้ - ยอดขาย = ยอดคงเหลือ</strong>
                            <span class="block sm:inline text-rose-100/85">(เช่น เอ็นไก่ เหลือ 3 จาก 10 ไม้ + ซื้อเพิ่ม 10 ไม้ = รวม 13 ไม้)</span>
                        </p>
                    </div>
                </div>

                <!-- Right: Date Picker + Primary Action Button (+ เสียบเพิ่ม/ตัดของเสีย) -->
                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <form id="inventory-date-form" method="GET" action="{{ route('admin.inventory.index') }}" class="flex items-center">
                        <div class="relative">
                            <input
                                type="date"
                                name="date"
                                id="inventory-selected-date"
                                value="{{ $selectedDate }}"
                                onchange="this.form.submit()"
                                aria-label="วันที่ตรวจนับสต็อก"
                                class="h-[44px] px-4 rounded-2xl bg-white text-[#111827] text-[13.5px] font-bold border border-white shadow-md focus:outline-none focus:ring-2 focus:ring-[#ED0038] cursor-pointer"
                            >
                        </div>
                    </form>

                    <button
                        type="button"
                        onclick="openAdjustModal('add')"
                        class="h-[44px] px-5 rounded-2xl bg-[#ED0038] hover:bg-[#FF174D] active:scale-[0.99] text-white text-[13.5px] font-bold shadow-lg shadow-black/25 border border-white/60 inline-flex items-center justify-center gap-2 transition-all cursor-pointer"
                    >
                        <span class="text-base leading-none font-bold">+</span>
                        <span>เสียบเพิ่ม/ตัดของเสีย</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-20 space-y-6">
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-[#EBFFF3] border border-emerald-300 text-[#079455] text-xs font-bold flex items-center justify-between shadow-xs">
                <span>✓ {{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-950 font-bold">✕</button>
            </div>
        @endif

        <!-- 2. SUMMARY CARDS — 4 COLUMNS ON DESKTOP (Matching Reference Image 2) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: 1. ไม้ยกมาจากเมื่อวาน -->
            <div class="bg-white rounded-[22px] px-5 py-4 border border-[#E2E8F0] shadow-xs flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-[#FFF0F3] text-[#ED0038] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                        <polyline points="2 17 12 22 22 17"></polyline>
                        <polyline points="2 12 12 17 22 12"></polyline>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[12.5px] font-bold text-[#111827] block truncate">1. ไม้ยกมาจากเมื่อวาน</span>
                    <div class="text-[28px] font-bold text-[#111827] leading-tight my-0.5 tracking-tight">
                        <span id="kpi-opening-sticks">{{ number_format($totalOpeningSticks) }}</span>
                        <span class="text-[13px] font-semibold text-[#64748B]">ไม้</span>
                    </div>
                    <span class="text-[11px] text-[#64748B] block">Opening Stock</span>
                </div>
            </div>

            <!-- Card 2: 2. ซื้อของ/เสียบเพิ่มวันนี้ -->
            <div class="bg-white rounded-[22px] px-5 py-4 border border-[#E2E8F0] shadow-xs flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-[#FFF5E7] text-[#EA6B00] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[12.5px] font-bold text-[#111827] block truncate">2. ซื้อของ/เสียบเพิ่มวันนี้</span>
                    <div class="text-[28px] font-bold text-[#EA6B00] leading-tight my-0.5 tracking-tight">
                        +<span id="kpi-added-sticks">{{ number_format($totalAddedSticks) }}</span>
                        <span class="text-[13px] font-semibold text-[#64748B]">ไม้</span>
                    </div>
                    <span class="text-[11px] text-[#64748B] block">จากใบเสร็จ & เสียบหน้าร้าน</span>
                </div>
            </div>

            <!-- Card 3: 3. ยอดขายวันนี้ (POS + APP) -->
            <div class="bg-white rounded-[22px] px-5 py-4 border border-[#E2E8F0] shadow-xs flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-[#FFF0F3] text-[#E80035] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[12.5px] font-bold text-[#111827] block truncate">3. ยอดขายวันนี้ (POS + APP)</span>
                    <div class="text-[28px] font-bold text-[#E80035] leading-tight my-0.5 tracking-tight">
                        -<span id="kpi-sold-sticks">{{ number_format($totalSoldSticks) }}</span>
                        <span class="text-[13px] font-semibold text-[#64748B]">ไม้</span>
                    </div>
                    <span class="text-[11px] text-[#64748B] block">หักออกจากสต็อกอัตโนมัติ</span>
                </div>
            </div>

            <!-- Card 4: 4. ยอดไม้คงเหลือปัจจุบัน -->
            <div class="bg-white rounded-[22px] px-5 py-4 border border-[#E2E8F0] shadow-xs flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-[#EBFFF3] text-[#079455] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[12.5px] font-bold text-[#111827] block truncate">4. ยอดไม้คงเหลือปัจจุบัน</span>
                    <div class="text-[28px] font-bold text-[#079455] leading-tight my-0.5 tracking-tight">
                        <span id="kpi-expected-sticks">{{ number_format($totalExpectedSticks) }}</span>
                        <span class="text-[13px] font-semibold text-[#64748B]">ไม้</span>
                    </div>
                    <span class="text-[11px] text-[#64748B] block">Expected Stock Balance</span>
                </div>
            </div>
        </div>

        <!-- 3. INVENTORY TABLE & END-OF-DAY RECONCILIATION FORM -->
        <form
            id="reconcile-form"
            action="{{ route('admin.inventory.reconcile') }}"
            method="POST"
            onsubmit="submitReconcileForm(event)"
            class="bg-white rounded-[24px] border border-[#E2E8F0] shadow-xs overflow-hidden"
        >
            @csrf
            <input type="hidden" name="date" value="{{ $selectedDate }}">

            <!-- Table Top Header -->
            <div class="px-5 py-4 sm:px-6 border-b border-[#E2E8F0] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="font-bold text-[17px] sm:text-[18.5px] text-[#111827] tracking-tight">
                            ตารางสรุปคงเหลือไม้ประจำวันที่ {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                        </h2>
                        <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#EBFFF3] text-[#079455] border border-emerald-200/70 text-[11px] font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#079455] animate-pulse"></span>
                            <span>ซิงก์สต็อกเรียลไทม์</span>
                        </span>
                    </div>
                    <p class="text-xs text-[#64748B] mt-0.5">
                        กรอกช่อง "นับจริงสิ้นวัน" เพื่อตรวจหาของหาย/ไม้หัก และยกยอดไปวันพรุ่งนี้
                    </p>
                </div>

                <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                    <button
                        type="button"
                        onclick="fillAllActualWithExpected()"
                        title="คัดลอกยอดคงเหลือตามระบบลงในช่องนับจริงที่ยังว่างอยู่"
                        class="py-2.5 px-3.5 rounded-xl bg-[#F7F9FC] hover:bg-slate-200/70 text-[#111827] border border-[#E2E8F0] text-xs font-semibold transition-all cursor-pointer"
                    >
                        ดึงยอดตามระบบทั้งหมด
                    </button>

                    <button
                        type="submit"
                        id="btn-reconcile-top"
                        class="py-2.5 px-5 rounded-xl bg-[#ED0038] hover:bg-[#FF174D] active:scale-[0.99] text-white text-xs sm:text-[13px] font-bold shadow-md shadow-[#ED0038]/25 inline-flex items-center gap-2 transition-all cursor-pointer"
                    >
                        <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                        <span>บันทึกปิดรอบตรวจนับสิ้นวัน</span>
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-[#F8FAFC] text-[#111827] border-b border-[#E2E8F0] font-bold text-[12px]">
                        <tr>
                            <th class="py-3.5 px-5">ชื่อไม้หม่าล่า</th>
                            <th class="py-3.5 px-3">หมวดหมู่</th>
                            <th class="py-3.5 px-3 text-center bg-slate-50/80">ยกมาจากเมื่อวาน</th>
                            <th class="py-3.5 px-3 text-center bg-[#FFF5E7] text-[#EA6B00]">+ ซื้อ/เสียบเพิ่มวันนี้</th>
                            <th class="py-3.5 px-3 text-center bg-[#FFF0F3] text-[#E80035]">- ยอดขายวันนี้</th>
                            <th class="py-3.5 px-3 text-center">- ของเสีย</th>
                            <th class="py-3.5 px-3 text-center bg-[#EBFFF3] text-[#079455] font-bold">= คงเหลือตามระบบ</th>
                            <th class="py-3.5 px-4 text-center bg-[#F0F6FF] text-[#1D4ED8]">
                                <span class="inline-flex items-center justify-center gap-1">
                                    <svg class="w-3.5 h-3.5 stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path stroke-linecap="round" d="m21 21-4.3-4.3"></path>
                                    </svg>
                                    <span>ตรวจนับจริงสิ้นวัน</span>
                                </span>
                            </th>
                            <th class="py-3.5 px-4 text-right">ส่วนต่าง</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E2E8F0]">
                        @foreach($inventoryRecords as $item)
                            @php
                                $prod = $item['product'];
                                $rec = $item['record'];
                                $thumbUrl = $resolveProductThumb($prod);
                                $variance = $rec->actual_closing_sticks !== null ? ($rec->actual_closing_sticks - $rec->expected_closing_sticks) : null;
                            @endphp
                            <tr
                                id="inv-row-{{ $prod->id }}"
                                data-product-id="{{ $prod->id }}"
                                data-expected="{{ (int) $rec->expected_closing_sticks }}"
                                class="hover:bg-slate-50/70 transition-colors"
                            >
                                <!-- 1. ชื่อไม้หม่าล่า (Thumbnail + Name + Price) -->
                                <td class="py-3 px-5">
                                    <div class="flex items-center gap-3">
                                        <img
                                            src="{{ $thumbUrl }}"
                                            alt="{{ $prod->name }}"
                                            loading="lazy"
                                            class="w-11 h-11 rounded-xl object-cover border border-slate-200/90 shadow-2xs shrink-0 bg-slate-100"
                                            onerror="this.onerror=null;this.src='{{ asset('assets/menu/food-fallback.svg') }}';"
                                        >
                                        <div class="min-w-0">
                                            <div class="font-bold text-[#111827] text-[13.5px] leading-snug truncate">
                                                {{ $prod->name }}
                                            </div>
                                            <div class="text-[11px] text-[#64748B] font-normal">
                                                ราคาขาย ฿{{ number_format($prod->price_per_stick, 0) }}/ไม้
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- 2. หมวดหมู่ -->
                                <td class="py-3 px-3 text-[#64748B] font-medium text-[12.5px]">
                                    {{ $prod->category->name }}
                                </td>

                                <!-- 3. ยกมาจากเมื่อวาน -->
                                <td class="py-3 px-3 text-center font-bold text-[13.5px] text-[#111827] bg-slate-50/40" data-role="opening">
                                    {{ $rec->opening_sticks }}
                                </td>

                                <!-- 4. + ซื้อ/เสียบเพิ่มวันนี้ -->
                                <td class="py-3 px-3 text-center font-bold text-[13.5px] text-[#EA6B00] bg-[#FFF5E7]/45" data-role="added">
                                    +{{ $rec->added_sticks }}
                                </td>

                                <!-- 5. - ยอดขายวันนี้ -->
                                <td class="py-3 px-3 text-center font-bold text-[13.5px] text-[#E80035] bg-[#FFF0F3]/45" data-role="sold">
                                    -{{ $rec->sold_sticks }}
                                </td>

                                <!-- 6. - ของเสีย -->
                                <td class="py-3 px-3 text-center font-semibold text-[13px] {{ $rec->waste_sticks > 0 ? 'text-[#E80035] font-bold' : 'text-[#64748B]' }}" data-role="waste">
                                    {{ $rec->waste_sticks }}
                                </td>

                                <!-- 7. = คงเหลือตามระบบ -->
                                <td class="py-3 px-3 text-center font-bold text-[14.5px] text-[#079455] bg-[#EBFFF3]/55" data-role="expected">
                                    {{ $rec->expected_closing_sticks }}
                                </td>

                                <!-- 8. ตรวจนับจริงสิ้นวัน -->
                                <td class="py-2.5 px-4 text-center bg-[#F0F6FF]/50">
                                    <input
                                        type="number"
                                        name="actual_counts[{{ $prod->id }}]"
                                        min="0"
                                        step="1"
                                        data-role="actual-input"
                                        oninput="handleActualCountInput(this)"
                                        value="{{ $rec->actual_closing_sticks !== null ? $rec->actual_closing_sticks : '' }}"
                                        placeholder="{{ $rec->expected_closing_sticks }}"
                                        aria-label="ตรวจนับจริง {{ $prod->name }}"
                                        class="w-24 text-center font-bold text-[13.5px] text-[#111827] py-1.5 px-2.5 bg-white border border-blue-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 shadow-2xs"
                                    >
                                </td>

                                <!-- 9. ส่วนต่าง -->
                                <td class="py-3 px-4 text-right whitespace-nowrap" data-role="variance">
                                    @if($variance !== null)
                                        @if($variance === 0)
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#EBFFF3] text-[#079455] border border-emerald-200">ตรงกัน (0)</span>
                                        @elseif($variance > 0)
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FFF5E7] text-[#EA6B00] border border-amber-200">+{{ $variance }} (ไม้เกิน)</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FFF0F3] text-[#E80035] border border-rose-200">{{ $variance }} (ไม้ขาด)</span>
                                        @endif
                                    @else
                                        <span class="text-[#94A3B8] text-[11.5px] font-medium">รอนับสิ้นวัน</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Table Footer -->
            <div class="p-4 bg-[#F8FAFC] border-t border-[#E2E8F0] flex flex-col sm:flex-row items-center justify-between gap-3">
                <span class="text-xs text-[#64748B]">
                    * เมื่อกดบันทึกปิดรอบตรวจนับสิ้นวัน ระบบจะนำยอดตรวจนับจริงไปตั้งเป็นยอด <strong>"ไม้ยกมาจากเมื่อวาน"</strong> ของวันถัดไปโดยอัตโนมัติ
                </span>
                <button
                    type="submit"
                    id="btn-reconcile-bottom"
                    class="py-2.5 px-5 rounded-xl bg-[#ED0038] hover:bg-[#FF174D] text-white text-xs font-bold shadow-md shadow-[#ED0038]/25 inline-flex items-center gap-2 cursor-pointer transition-all"
                >
                    <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    <span>ยืนยันยอดตรวจนับจริง & ปิดรอบสต็อก</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 4. MULTI-ITEM STOCK ADJUSTMENT MODAL (Matching Reference Image 3 100%)     -->
<!-- ========================================================================= -->
<div
    id="adjust-modal"
    class="fixed inset-0 z-50 bg-slate-950/65 backdrop-blur-xs hidden items-center justify-center p-2 sm:p-4"
    onclick="if(event.target === this) requestCloseAdjustModal()"
>
    <div class="bg-white rounded-[26px] max-w-[650px] w-full shadow-2xl overflow-hidden border border-[#E2E8F0] flex flex-col max-h-[92vh]">
        <!-- 4.1 Modal Header -->
        <div class="px-5 py-4 sm:px-6 sm:py-4.5 border-b border-[#E2E8F0] flex items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-[#FFF0F3] border border-rose-100 text-[#ED0038] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 stroke-current" fill="none" stroke-width="2.1" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-[18px] font-bold text-[#111827] leading-snug tracking-tight">
                        เสียบเพิ่ม / ตัดของเสีย
                    </h3>
                    <p class="text-[12px] text-[#64748B] font-normal mt-0.5">
                        บันทึกการเสียบไม้เพิ่มวันนี้ หรือบันทึกของเสีย/ตัดจำนวนออกจากสต็อก
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="requestCloseAdjustModal()"
                aria-label="ปิดหน้าต่าง"
                class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-[#111827] flex items-center justify-center transition-colors cursor-pointer shrink-0"
            >
                <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- 4.2 Modal Body Form -->
        <form id="adjust-multi-form" onsubmit="submitAdjustMultiForm(event)" class="p-5 sm:p-6 overflow-y-auto space-y-4 flex-1 text-xs">
            <input type="hidden" id="adjust-action-input" name="action" value="add">

            <!-- Mode Tabs: เสียบเพิ่ม (เพิ่มจำนวนไม้) vs ตัดของเสีย (ลดจำนวนไม้) -->
            <div class="grid grid-cols-2 gap-3">
                <button
                    type="button"
                    id="tab-btn-add"
                    onclick="switchAdjustMode('add')"
                    class="py-3 px-4 rounded-2xl bg-[#ED0038] text-white font-bold text-[13px] shadow-sm border border-[#ED0038] inline-flex items-center justify-center gap-2 transition-all cursor-pointer"
                >
                    <svg class="w-4 h-4 stroke-current shrink-0" viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="20" x2="20" y2="4"></line>
                        <circle cx="9.5" cy="14.5" r="2"></circle>
                        <circle cx="14.5" cy="9.5" r="2"></circle>
                    </svg>
                    <span>เสียบเพิ่ม (เพิ่มจำนวนไม้)</span>
                </button>

                <button
                    type="button"
                    id="tab-btn-waste"
                    onclick="switchAdjustMode('waste')"
                    class="py-3 px-4 rounded-2xl bg-white hover:bg-slate-50 text-[#111827] font-bold text-[13px] border border-[#E2E8F0] inline-flex items-center justify-center gap-2 transition-all cursor-pointer"
                >
                    <svg class="w-4 h-4 stroke-current shrink-0" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                    <span>ตัดของเสีย (ลดจำนวนไม้)</span>
                </button>
            </div>

            <!-- Evidence Upload Area (Dashed Border matching Image 3) -->
            <div class="p-4 bg-[#F8FAFC] rounded-[20px] border-2 border-dashed border-[#CBD5E1] space-y-3">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5">
                    <div>
                        <span class="text-[12.5px] font-bold text-[#111827] block">
                            แนบหลักฐาน (รูปภาพ/ใบสั่ง หรือไม่แนบก็ได้)
                        </span>
                        <span class="text-[11px] text-[#64748B] block leading-relaxed">
                            รองรับหลายรูปภาพ เช่น ใบสั่งวัตถุดิบ, รูปการเสียบไม้ หรือหลักฐานของเสีย
                        </span>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                        <label class="flex-1 sm:flex-none py-2 px-3.5 rounded-full bg-[#ED0038] hover:bg-[#FF174D] text-white text-[11.5px] font-bold cursor-pointer inline-flex items-center justify-center gap-1.5 shadow-xs transition-all">
                            <svg class="w-3.5 h-3.5 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>ถ่ายรูปด้วยกล้อง</span>
                            <input type="file" id="adjust-camera-input" accept="image/jpeg,image/png,image/webp" capture="environment" onchange="handleEvidenceFiles(event)" class="hidden">
                        </label>

                        <label class="flex-1 sm:flex-none py-2 px-3.5 rounded-full bg-[#0F172A] hover:bg-[#1E293B] text-white text-[11.5px] font-bold cursor-pointer inline-flex items-center justify-center gap-1.5 shadow-xs transition-all">
                            <svg class="w-3.5 h-3.5 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <span>เลือกจากอัลบั้ม</span>
                            <input type="file" id="adjust-gallery-input" accept="image/jpeg,image/png,image/webp" multiple onchange="handleEvidenceFiles(event)" class="hidden">
                        </label>
                    </div>
                </div>

                <!-- Evidence Thumbnails Strip + [+ เพิ่มรูป] Tile (Matching Image 3) -->
                <div class="flex items-center gap-3 overflow-x-auto pt-1 pb-0.5" id="evidence-preview-row">
                    <div id="evidence-thumbs-list" class="flex items-center gap-2.5"></div>

                    <!-- + เพิ่มรูป Tile -->
                    <label
                        title="เพิ่มรูปหลักฐาน"
                        class="w-20 h-20 rounded-2xl bg-white border border-[#E2E8F0] hover:border-[#ED0038] text-[#111827] hover:text-[#ED0038] flex flex-col items-center justify-center gap-1 shrink-0 cursor-pointer shadow-2xs transition-all"
                    >
                        <span class="text-xl font-normal leading-none">+</span>
                        <span class="text-[11px] font-semibold">เพิ่มรูป</span>
                        <input type="file" accept="image/jpeg,image/png,image/webp" multiple onchange="handleEvidenceFiles(event)" class="hidden">
                    </label>
                </div>
            </div>

            <!-- Date & Global Note Row (2 Columns matching Image 3) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="adjust-date-input" class="block text-[12.5px] font-bold text-[#111827] mb-1">วันที่บันทึก:</label>
                    <input
                        type="date"
                        id="adjust-date-input"
                        name="date"
                        value="{{ $selectedDate }}"
                        required
                        class="w-full h-[42px] px-3.5 rounded-xl bg-[#F8FAFC] border border-[#CBD5E1] text-xs font-bold text-[#111827] focus:bg-white focus:outline-none focus:border-[#ED0038]"
                    >
                </div>

                <div>
                    <label for="adjust-global-note" class="block text-[12.5px] font-bold text-[#111827] mb-1">หมายเหตุ (ถ้ามี):</label>
                    <input
                        type="text"
                        id="adjust-global-note"
                        name="notes"
                        placeholder="เช่น เสียบเพิ่มช่วงเช้า / ของเสียจากการเตรียม"
                        class="w-full h-[42px] px-3.5 rounded-xl bg-[#F8FAFC] border border-[#CBD5E1] text-xs font-medium text-[#111827] placeholder-slate-400 focus:bg-white focus:outline-none focus:border-[#ED0038]"
                    >
                </div>
            </div>

            <!-- Multi-Item Adjustment Table (Matching Image 3) -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-[#FFF5E7] text-[#EA6B00] flex items-center justify-center">
                            <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                            </svg>
                        </span>
                        <h4 class="text-[14px] font-bold text-[#111827]">รายการที่ต้องการบันทึก</h4>
                    </div>

                    <button
                        type="button"
                        onclick="addAdjustItemRow()"
                        class="text-[12.5px] font-bold text-[#ED0038] hover:underline inline-flex items-center gap-1 cursor-pointer"
                    >
                        <span>+ เพิ่มรายการ</span>
                    </button>
                </div>

                <div class="rounded-2xl border border-[#E2E8F0] overflow-hidden bg-[#F8FAFC]">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-[#EEF2F6] text-[#475569] font-bold text-[11.5px] border-b border-[#E2E8F0]">
                                <tr>
                                    <th class="py-2.5 px-3">ชื่อไม้หม่าล่า</th>
                                    <th class="py-2.5 px-2 text-center whitespace-nowrap">ประเภท</th>
                                    <th class="py-2.5 px-2 text-center whitespace-nowrap">จำนวน (ไม้)</th>
                                    <th class="py-2.5 px-2">หมายเหตุ</th>
                                    <th class="py-2.5 px-2 text-center">ลบ</th>
                                </tr>
                            </thead>
                            <tbody id="adjust-items-tbody" class="divide-y divide-[#E2E8F0] bg-white">
                                <!-- Populated dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Total Quantity Summary Bar (Matching Image 3) -->
            <div class="p-3.5 bg-[#F8FAFC] border border-[#E2E8F0] rounded-2xl flex items-center justify-between gap-3">
                <div>
                    <span class="text-[13px] font-bold text-[#111827] block">รวมจำนวนไม้:</span>
                    <span class="text-[11px] text-[#64748B] font-normal">จำนวนไม้ที่บันทึกในครั้งนี้</span>
                </div>

                <div class="min-w-[130px] px-5 py-1.5 rounded-xl bg-white border-2 border-rose-200 text-right shadow-2xs">
                    <span id="adjust-total-sticks" class="text-[20px] font-bold text-[#ED0038]">0</span>
                    <span class="text-[15px] font-bold text-[#ED0038] ml-0.5">ไม้</span>
                </div>
            </div>

            <!-- Modal Footer Buttons -->
            <div class="flex gap-3 pt-1">
                <button
                    type="button"
                    onclick="requestCloseAdjustModal()"
                    class="w-1/3 py-3 rounded-2xl bg-white hover:bg-slate-50 border border-[#CBD5E1] text-[13.5px] font-bold text-[#111827] transition-colors cursor-pointer"
                >
                    ยกเลิก
                </button>

                <button
                    type="submit"
                    id="btn-save-adjust"
                    class="w-2/3 py-3 rounded-2xl bg-[#ED0038] hover:bg-[#FF174D] active:scale-[0.99] text-white text-[13.5px] font-bold shadow-lg shadow-[#ED0038]/25 inline-flex items-center justify-center gap-2 transition-all cursor-pointer"
                >
                    <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    <span id="btn-save-adjust-label">บันทึกการเสียบเพิ่ม / ตัดของเสีย</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Toast Notification -->
<div
    id="inv-toast"
    class="fixed bottom-5 right-5 z-50 hidden items-center gap-2.5 px-4 py-3 rounded-2xl bg-slate-900 text-white text-xs font-semibold shadow-xl border border-slate-700"
>
    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
    <span id="inv-toast-text">บันทึกข้อมูลเรียบร้อยแล้ว</span>
</div>

@push('scripts')
<script>
    const inventoryProductsCatalog = @json($productsCatalog);
    let currentAdjustMode = 'add'; // 'add' | 'waste'
    let adjustEvidencePhotos = [];
    let isAdjustSubmitting = false;

    const invSyncChannel = typeof BroadcastChannel !== 'undefined'
        ? new BroadcastChannel('mala_inventory_sync_channel')
        : null;

    if (invSyncChannel) {
        invSyncChannel.onmessage = (ev) => {
            if (ev.data && ev.data.type === 'INVENTORY_CHANGED') {
                refreshInventoryLiveState();
            }
        };
    }

    function showInvToast(msg) {
        const toast = document.getElementById('inv-toast');
        const textEl = document.getElementById('inv-toast-text');
        if (!toast || !textEl) return;
        textEl.textContent = msg;
        toast.classList.remove('hidden');
        toast.classList.add('flex');
        clearTimeout(window.__invToastTimer);
        window.__invToastTimer = setTimeout(() => {
            toast.classList.add('hidden');
            toast.classList.remove('flex');
        }, 3200);
    }

    // 1. Live Variance Calculation when typing in "ตรวจนับจริงสิ้นวัน"
    function handleActualCountInput(inputEl) {
        const row = inputEl.closest('tr');
        if (!row) return;
        const expected = parseInt(row.getAttribute('data-expected') || '0', 10);
        const rawVal = inputEl.value.trim();
        const varianceCell = row.querySelector('[data-role="variance"]');
        if (!varianceCell) return;

        if (rawVal === '') {
            varianceCell.innerHTML = '<span class="text-[#94A3B8] text-[11.5px] font-medium">รอนับสิ้นวัน</span>';
            return;
        }

        const actual = Math.max(0, parseInt(rawVal, 10) || 0);
        const diff = actual - expected;

        if (diff === 0) {
            varianceCell.innerHTML = '<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#EBFFF3] text-[#079455] border border-emerald-200">ตรงกัน (0)</span>';
        } else if (diff > 0) {
            varianceCell.innerHTML = `<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FFF5E7] text-[#EA6B00] border border-amber-200">+${diff} (ไม้เกิน)</span>`;
        } else {
            varianceCell.innerHTML = `<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#FFF0F3] text-[#E80035] border border-rose-200">${diff} (ไม้ขาด)</span>`;
        }
    }

    function fillAllActualWithExpected() {
        document.querySelectorAll('#reconcile-form tbody tr').forEach(row => {
            const expected = row.getAttribute('data-expected') || '0';
            const input = row.querySelector('[data-role="actual-input"]');
            if (input && input.value.trim() === '') {
                input.value = expected;
                handleActualCountInput(input);
            }
        });
        showInvToast('ดึงยอดคงเหลือตามระบบลงในช่องตรวจนับจริงเรียบร้อยแล้ว กดปุ่มบันทึกปิดรอบเพื่อยืนยัน');
    }

    // 2. Open / Close Multi-Item Stock Adjustment Modal
    function openAdjustModal(defaultMode = 'add') {
        const modal = document.getElementById('adjust-modal');
        if (!modal) return;

        switchAdjustMode(defaultMode);

        // Initialize with 2 default rows if empty (matching Reference Image 3)
        const tbody = document.getElementById('adjust-items-tbody');
        if (tbody && tbody.children.length === 0) {
            const firstProdId = inventoryProductsCatalog[0]?.id || '';
            const secondProdId = inventoryProductsCatalog[2]?.id || inventoryProductsCatalog[1]?.id || firstProdId;
            addAdjustItemRow(firstProdId, 10, '');
            if (secondProdId && secondProdId !== firstProdId) {
                addAdjustItemRow(secondProdId, 20, '');
            }
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function requestCloseAdjustModal() {
        if (isAdjustSubmitting) return;
        closeAdjustModal();
    }

    function closeAdjustModal() {
        const modal = document.getElementById('adjust-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('adjust-modal');
            if (modal && !modal.classList.contains('hidden')) {
                requestCloseAdjustModal();
            }
        }
    });

    // 3. Switch Mode Tab: เสียบเพิ่ม ('add') vs ตัดของเสีย ('waste')
    function switchAdjustMode(mode) {
        currentAdjustMode = mode === 'waste' ? 'waste' : 'add';
        document.getElementById('adjust-action-input').value = currentAdjustMode;

        const btnAdd = document.getElementById('tab-btn-add');
        const btnWaste = document.getElementById('tab-btn-waste');

        if (currentAdjustMode === 'add') {
            btnAdd.className = 'py-3 px-4 rounded-2xl bg-[#ED0038] text-white font-bold text-[13px] shadow-sm border border-[#ED0038] inline-flex items-center justify-center gap-2 transition-all cursor-pointer';
            btnWaste.className = 'py-3 px-4 rounded-2xl bg-white hover:bg-slate-50 text-[#111827] font-bold text-[13px] border border-[#E2E8F0] inline-flex items-center justify-center gap-2 transition-all cursor-pointer';
        } else {
            btnWaste.className = 'py-3 px-4 rounded-2xl bg-[#ED0038] text-white font-bold text-[13px] shadow-sm border border-[#ED0038] inline-flex items-center justify-center gap-2 transition-all cursor-pointer';
            btnAdd.className = 'py-3 px-4 rounded-2xl bg-white hover:bg-slate-50 text-[#111827] font-bold text-[13px] border border-[#E2E8F0] inline-flex items-center justify-center gap-2 transition-all cursor-pointer';
        }

        // Update Type column text in all existing rows
        document.querySelectorAll('.adjust-row-type-label').forEach(el => {
            if (currentAdjustMode === 'add') {
                el.textContent = 'เสียบเพิ่ม';
                el.className = 'adjust-row-type-label text-[12px] font-semibold text-[#111827]';
            } else {
                el.textContent = 'ตัดของเสีย';
                el.className = 'adjust-row-type-label text-[12px] font-semibold text-[#E80035]';
            }
        });
    }

    // 4. Evidence Image Compression & Preview
    function compressEvidenceImage(file) {
        return new Promise((resolve, reject) => {
            if (!file.type.startsWith('image/')) {
                return reject(new Error('รองรับเฉพาะไฟล์รูปภาพ JPEG, PNG หรือ WebP'));
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let w = img.width;
                    let h = img.height;
                    const maxDim = 1280;
                    if (w > maxDim || h > maxDim) {
                        if (w > h) {
                            h = Math.round((h * maxDim) / w);
                            w = maxDim;
                        } else {
                            w = Math.round((w * maxDim) / h);
                            h = maxDim;
                        }
                    }
                    canvas.width = w;
                    canvas.height = h;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, w, h);
                    resolve({
                        id: Date.now() + Math.random().toString(36).slice(2, 8),
                        name: file.name,
                        base64: canvas.toDataURL('image/jpeg', 0.86),
                    });
                };
                img.onerror = () => reject(new Error('ไม่สามารถอ่านไฟล์รูปภาพได้'));
                img.src = e.target.result;
            };
            reader.onerror = () => reject(new Error('ไม่สามารถอ่านไฟล์รูปภาพได้'));
            reader.readAsDataURL(file);
        });
    }

    async function handleEvidenceFiles(event) {
        const files = Array.from(event.target.files || []);
        if (!files.length) return;
        try {
            for (const f of files) {
                if (adjustEvidencePhotos.length >= 10) break;
                const item = await compressEvidenceImage(f);
                adjustEvidencePhotos.push(item);
            }
            renderEvidenceThumbnails();
        } catch (err) {
            alert(err.message || 'เกิดข้อผิดพลาดในการเพิ่มรูปหลักฐาน');
        } finally {
            event.target.value = '';
        }
    }

    function renderEvidenceThumbnails() {
        const listEl = document.getElementById('evidence-thumbs-list');
        if (!listEl) return;
        listEl.innerHTML = adjustEvidencePhotos.map((photo, idx) => `
            <div class="relative w-20 h-20 rounded-2xl overflow-hidden border border-[#E2E8F0] bg-white shadow-xs shrink-0">
                <img src="${photo.base64}" alt="หลักฐาน ${idx + 1}" class="w-full h-full object-cover">
                <button
                    type="button"
                    onclick="removeEvidencePhoto('${photo.id}')"
                    title="ลบรูปนี้"
                    class="absolute top-1 right-1 w-5 h-5 rounded-full bg-slate-900/80 hover:bg-[#ED0038] text-white text-xs font-bold flex items-center justify-center shadow cursor-pointer"
                >
                    &times;
                </button>
            </div>
        `).join('');
    }

    function removeEvidencePhoto(id) {
        adjustEvidencePhotos = adjustEvidencePhotos.filter(p => p.id !== id);
        renderEvidenceThumbnails();
    }

    // 5. Multi-Item Table Rows Management
    function addAdjustItemRow(defaultProductId = '', defaultQty = 10, defaultNote = '') {
        const tbody = document.getElementById('adjust-items-tbody');
        if (!tbody) return;

        const selectedId = defaultProductId || (inventoryProductsCatalog[0]?.id ?? '');
        const optionsHtml = inventoryProductsCatalog.map(p => `
            <option value="${p.id}" ${String(p.id) === String(selectedId) ? 'selected' : ''}>
                ${p.name}
            </option>
        `).join('');

        const typeLabel = currentAdjustMode === 'add' ? 'เสียบเพิ่ม' : 'ตัดของเสีย';
        const typeColor = currentAdjustMode === 'add' ? 'text-[#111827]' : 'text-[#E80035]';
        const placeholderNote = currentAdjustMode === 'add' ? 'เช่น เสียบเพิ่มรอบเช้า' : 'เช่น ไม้หัก/ตกหล่น';

        const tr = document.createElement('tr');
        tr.className = 'adjust-item-row';
        tr.innerHTML = `
            <td class="py-2.5 px-2.5">
                <select class="adjust-item-product w-full h-9 px-2.5 rounded-xl bg-white border border-[#CBD5E1] text-xs font-bold text-[#111827] focus:outline-none focus:border-[#ED0038]">
                    ${optionsHtml}
                </select>
            </td>
            <td class="py-2.5 px-2 text-center whitespace-nowrap">
                <span class="adjust-row-type-label text-[12px] font-semibold ${typeColor}">${typeLabel}</span>
            </td>
            <td class="py-2.5 px-2 text-center whitespace-nowrap">
                <div class="inline-flex items-center rounded-xl border border-[#CBD5E1] bg-white p-0.5">
                    <button
                        type="button"
                        onclick="stepAdjustRowQty(this, -1)"
                        class="w-7 h-7 rounded-lg hover:bg-slate-100 text-[#111827] font-bold text-sm flex items-center justify-center cursor-pointer"
                    >
                        &minus;
                    </button>
                    <input
                        type="number"
                        min="1"
                        step="1"
                        value="${defaultQty}"
                        oninput="recalculateAdjustTotalSticks()"
                        class="adjust-item-qty w-12 text-center font-bold text-xs text-[#111827] focus:outline-none"
                    >
                    <button
                        type="button"
                        onclick="stepAdjustRowQty(this, 1)"
                        class="w-7 h-7 rounded-lg hover:bg-slate-100 text-[#111827] font-bold text-sm flex items-center justify-center cursor-pointer"
                    >
                        +
                    </button>
                </div>
            </td>
            <td class="py-2.5 px-2">
                <input
                    type="text"
                    value="${defaultNote}"
                    placeholder="${placeholderNote}"
                    class="adjust-item-note w-full h-9 px-2.5 rounded-xl bg-[#F8FAFC] border border-[#E2E8F0] text-xs text-[#111827] placeholder-slate-400 focus:bg-white focus:outline-none focus:border-[#ED0038]"
                >
            </td>
            <td class="py-2.5 px-2 text-center">
                <button
                    type="button"
                    onclick="removeAdjustItemRow(this)"
                    title="ลบรายการนี้"
                    class="w-8 h-8 rounded-xl hover:bg-rose-50 text-[#ED0038] inline-flex items-center justify-center transition-colors cursor-pointer"
                >
                    <svg class="w-4 h-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        recalculateAdjustTotalSticks();
    }

    function stepAdjustRowQty(btnEl, delta) {
        const row = btnEl.closest('.adjust-item-row');
        if (!row) return;
        const qtyInput = row.querySelector('.adjust-item-qty');
        if (!qtyInput) return;
        const current = parseInt(qtyInput.value || '0', 10) || 0;
        qtyInput.value = Math.max(1, current + delta);
        recalculateAdjustTotalSticks();
    }

    function removeAdjustItemRow(btnEl) {
        const tbody = document.getElementById('adjust-items-tbody');
        const row = btnEl.closest('.adjust-item-row');
        if (row) row.remove();
        if (tbody && tbody.children.length === 0) {
            addAdjustItemRow('', 10, '');
        } else {
            recalculateAdjustTotalSticks();
        }
    }

    function recalculateAdjustTotalSticks() {
        let total = 0;
        document.querySelectorAll('.adjust-item-qty').forEach(input => {
            const val = parseInt(input.value || '0', 10);
            if (!isNaN(val) && val > 0) {
                total += val;
            }
        });
        const totalEl = document.getElementById('adjust-total-sticks');
        if (totalEl) totalEl.textContent = total.toLocaleString('en-US');
    }

    // 6. Submit Multi-Item Adjustment via AJAX + Real-Time Update
    async function submitAdjustMultiForm(event) {
        event.preventDefault();
        if (isAdjustSubmitting) return;

        const rows = Array.from(document.querySelectorAll('.adjust-item-row'));
        if (rows.length === 0) {
            alert('กรุณาเพิ่มรายการอย่างน้อย 1 รายการ');
            return;
        }

        const items = [];
        for (const r of rows) {
            const pid = r.querySelector('.adjust-item-product')?.value;
            const qty = parseInt(r.querySelector('.adjust-item-qty')?.value || '0', 10);
            const note = (r.querySelector('.adjust-item-note')?.value || '').trim();
            if (!pid || isNaN(qty) || qty < 1) {
                alert('กรุณาระบุจำนวนไม้ให้มากกว่า 0 สำหรับทุกรายการ');
                return;
            }
            items.push({ product_id: parseInt(pid, 10), quantity: qty, note: note });
        }

        isAdjustSubmitting = true;
        const saveBtn = document.getElementById('btn-save-adjust');
        const saveLabel = document.getElementById('btn-save-adjust-label');
        if (saveBtn) saveBtn.disabled = true;
        if (saveLabel) saveLabel.textContent = 'กำลังบันทึก...';

        const payload = {
            date: document.getElementById('adjust-date-input')?.value || '{{ $selectedDate }}',
            action: currentAdjustMode,
            notes: (document.getElementById('adjust-global-note')?.value || '').trim(),
            evidence_images: adjustEvidencePhotos.map(p => p.base64),
            items: items,
        };

        try {
            const res = await fetch("{{ route('admin.inventory.adjust') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json();
            if (!res.ok || !data.success) {
                alert(data.message || 'ไม่สามารถบันทึกข้อมูลได้ กรุณาตรวจสอบอีกครั้ง');
                return;
            }

            // Reset modal state
            adjustEvidencePhotos = [];
            renderEvidenceThumbnails();
            document.getElementById('adjust-global-note').value = '';
            closeAdjustModal();

            await refreshInventoryLiveState();
            if (invSyncChannel) {
                invSyncChannel.postMessage({ type: 'INVENTORY_CHANGED', date: payload.date });
            }
            showInvToast(data.message || 'บันทึกการปรับสต็อกเรียบร้อยแล้ว');
        } catch (err) {
            console.error('Adjust error:', err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        } finally {
            isAdjustSubmitting = false;
            if (saveBtn) saveBtn.disabled = false;
            if (saveLabel) saveLabel.textContent = 'บันทึกการเสียบเพิ่ม / ตัดของเสีย';
        }
    }

    // 7. Submit End-of-Day Reconciliation via AJAX + Real-Time Update
    async function submitReconcileForm(event) {
        event.preventDefault();
        const form = document.getElementById('reconcile-form');
        if (!form) return;

        const formData = new FormData(form);
        const btnTop = document.getElementById('btn-reconcile-top');
        const btnBot = document.getElementById('btn-reconcile-bottom');
        if (btnTop) btnTop.disabled = true;
        if (btnBot) btnBot.disabled = true;

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });
            const data = await res.json();
            if (res.ok && data.success) {
                await refreshInventoryLiveState();
                if (invSyncChannel) {
                    invSyncChannel.postMessage({ type: 'INVENTORY_CHANGED' });
                }
                showInvToast(data.message || 'บันทึกปิดรอบตรวจนับสิ้นวันเรียบร้อยแล้ว');
            } else {
                alert(data.message || 'เกิดข้อผิดพลาดในการบันทึกยอดตรวจนับสิ้นวัน');
            }
        } catch (err) {
            form.submit();
        } finally {
            if (btnTop) btnTop.disabled = false;
            if (btnBot) btnBot.disabled = false;
        }
    }

    // 8. Real-Time Refresh of Summary Cards & Inventory Table Rows
    async function refreshInventoryLiveState() {
        const dateVal = document.getElementById('inventory-selected-date')?.value || '{{ $selectedDate }}';
        try {
            const res = await fetch(`{{ route('admin.inventory.index') }}?date=${encodeURIComponent(dateVal)}&json=1`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data || !data.success) return;

            if (data.summary) {
                document.getElementById('kpi-opening-sticks').textContent = Number(data.summary.opening_sticks || 0).toLocaleString('en-US');
                document.getElementById('kpi-added-sticks').textContent = Number(data.summary.added_sticks || 0).toLocaleString('en-US');
                document.getElementById('kpi-sold-sticks').textContent = Number(data.summary.sold_sticks || 0).toLocaleString('en-US');
                document.getElementById('kpi-expected-sticks').textContent = Number(data.summary.expected_sticks || 0).toLocaleString('en-US');
            }

            if (Array.isArray(data.records)) {
                data.records.forEach(rec => {
                    const row = document.getElementById(`inv-row-${rec.product_id}`);
                    if (!row) return;

                    row.setAttribute('data-expected', String(rec.expected_closing_sticks));

                    const openEl = row.querySelector('[data-role="opening"]');
                    if (openEl) openEl.textContent = rec.opening_sticks;

                    const addEl = row.querySelector('[data-role="added"]');
                    if (addEl) addEl.textContent = `+${rec.added_sticks}`;

                    const soldEl = row.querySelector('[data-role="sold"]');
                    if (soldEl) soldEl.textContent = `-${rec.sold_sticks}`;

                    const wasteEl = row.querySelector('[data-role="waste"]');
                    if (wasteEl) {
                        wasteEl.textContent = rec.waste_sticks;
                        wasteEl.className = `py-3 px-3 text-center font-semibold text-[13px] ${rec.waste_sticks > 0 ? 'text-[#E80035] font-bold' : 'text-[#64748B]'}`;
                    }

                    const expEl = row.querySelector('[data-role="expected"]');
                    if (expEl) expEl.textContent = rec.expected_closing_sticks;

                    const actualInput = row.querySelector('[data-role="actual-input"]');
                    if (actualInput) {
                        actualInput.placeholder = String(rec.expected_closing_sticks);
                        if (document.activeElement !== actualInput && rec.actual_closing_sticks !== null) {
                            actualInput.value = rec.actual_closing_sticks;
                        }
                        handleActualCountInput(actualInput);
                    }
                });
            }
        } catch (err) {
            // Silent background poll
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        setInterval(refreshInventoryLiveState, 15000);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                refreshInventoryLiveState();
            }
        });
    });
</script>
@endpush
@endsection
