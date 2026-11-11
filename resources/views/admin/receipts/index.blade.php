@extends('layouts.app', ['title' => 'ระบบสแกนใบเสร็จ & แปลงต้นทุนต่อไม้ - หม่าล่าสตรีท'])

@section('content')
@php
    // Prepare RawMaterials lookup map for instant client-side Yield & Unit auto-fill
    $rawMaterialsMap = $rawMaterials->map(fn ($m) => [
        'id' => $m->id,
        'name' => $m->name,
        'scan_names' => is_array($m->scan_names) ? $m->scan_names : (json_decode($m->scan_names ?? '[]', true) ?: []),
        'purchase_unit' => $m->purchase_unit ?: 'แพ็ก',
        'yield_sticks_per_unit' => (float) ($m->yield_sticks_per_unit ?: 10),
        'latest_cost_price' => (float) ($m->latest_cost_price ?: 0),
        'default_vendor' => $m->default_vendor ?: '',
    ])->values();

    $vendorPresets = collect(['สยามแม็คโคร', 'โลตัส เอ็กซ์เพรส', 'มายด์ฟู้ด', 'เบทาโกร ช็อป', 'ฟาร์มโคขุนกำแพงแสน', 'ตลาดเยาวราช', 'ตลาดสี่มุมเมือง'])
        ->merge($rawMaterials->pluck('default_vendor')->filter())
        ->merge($receipts->pluck('vendor_name')->filter())
        ->unique()
        ->values();

    // Map existing receipts for 5*8 slip preview and scanned image viewer
    $receiptsMap = $receipts->map(function ($r) {
        $allImgs = ($r->parsed_data && isset($r->parsed_data['images']) && is_array($r->parsed_data['images']))
            ? $r->parsed_data['images']
            : ($r->image_path ? [$r->image_path] : []);

        $pDate = $r->purchase_date;
        $thaiYear = $pDate ? ($pDate->year > 2500 ? $pDate->year : $pDate->year + 543) : 2569;
        $dateThai = $pDate ? $pDate->format('d/m/') . $thaiYear : '25/10/2569';

        return [
            'id' => $r->id,
            'receipt_no' => $r->receipt_no ?: '-',
            'vendor_name' => $r->vendor_name ?: 'ไม่ระบุร้านค้า',
            'purchase_date' => $r->purchase_date ? $r->purchase_date->format('d/m/Y') : '',
            'purchase_date_thai' => $dateThai,
            'purchase_time' => $r->created_at ? $r->created_at->format('H:i น.') : '17:25 น.',
            'total_amount' => (float) $r->total_amount,
            'images' => array_values(array_map(function ($img) {
                if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, 'data:image')) {
                    return $img;
                }
                return asset(ltrim($img, '/'));
            }, $allImgs)),
            'items' => $r->items->map(function ($it) {
                return [
                    'id' => $it->id,
                    'item_name' => $it->item_name,
                    'quantity' => (float) $it->quantity,
                    'unit' => $it->unit,
                    'unit_price' => (float) $it->unit_price,
                    'total_price' => (float) ($it->total_price ?: ($it->quantity * $it->unit_price)),
                    'yield_sticks_per_unit' => (float) $it->yield_sticks_per_unit,
                    'total_yield_sticks' => (int) $it->total_yield_sticks,
                    'cost_per_stick' => (float) $it->cost_per_stick,
                ];
            })->values(),
        ];
    })->keyBy('id');
@endphp

<style>
    :root {
        --ms-primary-red: #E90032;
        --ms-bright-red: #F50043;
        --ms-deep-red: #790000;
        --ms-dark-burgundy: #430000;
        --ms-text-main: #101828;
        --ms-text-secondary: #66758F;
        --ms-bg-soft: #F7F8FB;
        --ms-border-light: #E5EAF1;
        --ms-soft-pink: #FFF0F2;
        --ms-success-green: #159A59;
        --ms-warning-yellow: #F5B800;
        --ms-orange: #F07A00;
    }

    .receipts-main-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        align-items: start;
        width: 100%;
    }
    .receipts-col-left {
        width: 100%;
        min-width: 0;
    }
    .receipts-col-right {
        width: 100%;
        min-width: 0;
    }
    @media (min-width: 1024px) {
        .receipts-main-grid {
            display: grid !important;
            grid-template-columns: minmax(0, 70fr) minmax(0, 30fr) !important;
            gap: 24px !important;
            align-items: start !important;
        }
        .receipts-col-right {
            position: sticky !important;
            top: 80px !important;
            max-height: calc(100vh - 96px) !important;
            overflow-y: auto !important;
        }
    }

    .receipts-col-right::-webkit-scrollbar,
    #items-rows-container::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }
    .receipts-col-right::-webkit-scrollbar-track,
    #items-rows-container::-webkit-scrollbar-track {
        background: transparent;
    }
    .receipts-col-right::-webkit-scrollbar-thumb,
    #items-rows-container::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 9999px;
    }
    .receipts-col-right::-webkit-scrollbar-thumb:hover,
    #items-rows-container::-webkit-scrollbar-thumb:hover {
        background: #94A3B8;
    }
</style>

<div class="min-h-screen bg-[#F7F8FB] pb-14">
    <!-- 1. HERO BANNER — RECEIPT SCANNER & YIELD CONVERSION -->
    <section class="relative w-full overflow-hidden bg-gradient-to-r from-[#430000] via-[#790000] to-[#3B0000] border-b border-[#5A060C] pt-5 pb-11 sm:pt-6 sm:pb-12">
        <!-- Warm radial glow -->
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_62%_50%,rgba(245,0,67,0.32),transparent_62%)]"></div>

        <!-- Sizzling Mala Skewers Bowl Artwork in Center-Right background -->
        <div class="pointer-events-none hidden lg:flex absolute inset-y-0 right-[26%] xl:right-[28%] items-center justify-center opacity-95 select-none">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 w-20 bg-gradient-to-r from-[#5D0000] to-transparent z-10"></div>
                <div class="absolute inset-y-0 right-0 w-20 bg-gradient-to-l from-[#550000] to-transparent z-10"></div>
                <img
                    src="{{ asset('assets/brand/hero-mala-bowl-clean.png') }}"
                    alt="หม่าล่าสตรีท"
                    class="h-[165px] w-auto object-cover"
                    onerror="this.style.display='none'"
                >
            </div>
        </div>

        <div class="relative z-10 max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <!-- Left: Icon + Title + Subtitle -->
                <div class="flex items-start sm:items-center gap-4 max-w-2xl">
                    <div class="w-14 h-14 sm:w-15 sm:h-15 rounded-[18px] bg-[#FFF0F2] text-[#E90032] border border-white/90 shadow-lg flex items-center justify-center shrink-0">
                        <svg class="w-7 h-7 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-[22px] sm:text-[27px] font-bold text-white tracking-tight leading-snug drop-shadow-xs">
                            ระบบสแกนใบเสร็จ & แปลงต้นทุนต่อไม้
                        </h1>
                        <p class="text-[12.5px] sm:text-[13.5px] text-rose-100/90 font-normal mt-0.5 leading-relaxed">
                            สแกนใบเสร็จด้วยกล้องมือถือ/อัปโหลด, ตรวจจับเลขออเดอร์, อัปเดตราคาต้นทุน, และล็อกสูตรแปลงแพ็กเป็นไม้
                        </p>
                    </div>
                </div>

                <!-- Right: Action Button -->
                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <a
                        href="{{ route('admin.receipts.price_tracker') }}"
                        class="h-[46px] px-5 rounded-[16px] bg-white hover:bg-[#FFF0F2] text-[#101828] hover:text-[#E90032] text-[13.5px] font-semibold shadow-md inline-flex items-center justify-center gap-2 transition-all"
                    >
                        <svg class="w-4 h-4 text-[#E90032] stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <span>ตรวจจับราคาขึ้นลง</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- MAIN CONTENT CONTAINER (70% Left / 30% Right Layout) -->
    <div class="max-w-[1720px] mx-auto px-3 sm:px-5 lg:px-6 -mt-6 relative z-20 pb-12">
        <div class="receipts-main-grid">
            
            <!-- LEFT COLUMN (70%): ส่วนที่อยู่เดิม ขยับมาชิดซ้าย -->
            <div class="receipts-col-left space-y-5">
                <!-- 2. SUMMARY STATISTICS CARDS (3 Columns matching Reference Image 2) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
            <!-- Card 1: ต้นทุนซื้อวัตถุดิบวันนี้ -->
            <div onclick="setDateFilterPill('today')" class="bg-white rounded-[22px] px-5 py-4 sm:px-6 sm:py-5 border border-[#E5EAF1] shadow-sm hover:shadow-md hover:border-slate-300 transition-all cursor-pointer flex items-center gap-4 active:scale-[0.99]" title="คลิกเพื่อดูกรองบิลวันนี้">
                <div class="w-13 h-13 rounded-2xl bg-[#FFF0F2] text-[#E90032] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[13px] font-semibold text-[#101828] block">ต้นทุนซื้อวัตถุดิบวันนี้</span>
                    <div id="kpi-today-cost" class="text-[28px] sm:text-[32px] font-bold text-[#101828] leading-tight my-0.5 tracking-tight">
                        ฿{{ number_format($todayCost, 2) }}
                    </div>
                    <span class="text-[11.5px] text-[#66758F] font-normal block">ยอดบิลที่บันทึกแล้วในวันนี้</span>
                </div>
            </div>

            <!-- Card 2: ยอดซื้อวัตถุดิบเดือนนี้ -->
            <div onclick="setDateFilterPill('this_month')" class="bg-white rounded-[22px] px-5 py-4 sm:px-6 sm:py-5 border border-[#E5EAF1] shadow-sm hover:shadow-md hover:border-[#E90032]/40 transition-all cursor-pointer flex items-center gap-4 active:scale-[0.99]" title="คลิกเพื่อดูกรองบิลเดือนนี้">
                <div class="w-13 h-13 rounded-2xl bg-[#FFF0F2] text-[#E90032] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[13px] font-semibold text-[#101828] block">ยอดซื้อวัตถุดิบเดือนนี้</span>
                    <div id="kpi-month-cost" class="text-[28px] sm:text-[32px] font-bold text-[#E90032] leading-tight my-0.5 tracking-tight">
                        ฿{{ number_format($thisMonthCost, 2) }}
                    </div>
                    <span class="text-[11.5px] text-[#66758F] font-normal block">รวมซัพพลายเออร์ทุกร้าน</span>
                </div>
            </div>

            <!-- Card 3: วัตถุดิบที่มีสูตรล็อกไม้ -->
            <div onclick="window.location.href='{{ route('admin.receipts.price_tracker') }}'" class="bg-white rounded-[22px] px-5 py-4 sm:px-6 sm:py-5 border border-[#E5EAF1] shadow-sm hover:shadow-md hover:border-amber-400 transition-all cursor-pointer flex items-center gap-4 active:scale-[0.99]" title="คลิกเพื่อดูรายการสูตรล็อกไม้ & ติดตามราคาวัตถุดิบ">
                <div class="w-13 h-13 rounded-2xl bg-[#FFF0F2] text-[#E90032] flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[13px] font-semibold text-[#101828] block">วัตถุดิบที่มีสูตรล็อกไม้</span>
                    <div id="kpi-materials-count" class="text-[28px] sm:text-[32px] font-bold text-[#F07A00] leading-tight my-0.5 tracking-tight">
                        {{ $rawMaterials->count() }} รายการ
                    </div>
                    <span class="text-[11.5px] text-[#66758F] font-normal block">สูตรแปลงแพ็ก/กก. เป็นจำนวนไม้</span>
                </div>
            </div>
        </div>

        <!-- 2.5 BAR: ตัวเลือกช่วงวันที่ & สรุปรายการรับซื้อประจำวัน (ตามภาพที่ 1 และ 2 แถบเดียวกัน) -->
        <div class="bg-white rounded-[22px] border border-[#E5EAF1] p-3.5 sm:px-5 sm:py-3.5 shadow-sm flex flex-col xl:flex-row xl:items-center justify-between gap-3 relative z-30">
            <!-- ฝั่งซ้าย: ตามภาพที่ 1 (ไอคอนปฏิทิน + วันที่ภาษาไทย + สรุปรายการรับซื้อประจำวัน) -->
            <div class="flex items-center gap-3 shrink-0">
                <div class="w-10 h-10 rounded-2xl bg-white border border-amber-300 text-amber-500 shadow-2xs flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-500 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="3" ry="3"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h3 id="date-bar-main-title" class="text-[14.5px] sm:text-[15.5px] font-extrabold text-[#101828] tracking-tight leading-snug">
                        วันเสาร์ที่ 7 พฤศจิกายน 2569
                    </h3>
                    <p id="date-bar-sub-title" class="text-[11px] sm:text-[11.5px] text-[#66758F] font-medium leading-tight mt-0.5">
                        สรุปรายการรับซื้อประจำวัน
                    </p>
                </div>
            </div>

            <!-- ฝั่งขวา: ตามภาพที่ 2 (ปุ่มนำทาง < > + ปุ่มเลือกวันที่พร้อม Dropdown + ปุ่ม Pill 7 ช่วงเวลา) -->
            <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap xl:justify-end relative">
                <!-- Navigation Group: < [ 📅 3 วันล่าสุด ⌃ ] > -->
                <div class="inline-flex items-center gap-1 relative" id="date-picker-dropdown-container">
                    <!-- Left Arrow < -->
                    <button
                        type="button"
                        onclick="shiftDateFilter(-1)"
                        class="w-8 h-8 rounded-xl bg-white hover:bg-slate-100 active:scale-95 border border-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs shadow-2xs transition-all cursor-pointer"
                        title="ช่วงเวลาก่อนหน้า"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <!-- Active Date Dropdown Toggle Button [ 📅 3 วันล่าสุด ⌃ ] -->
                    <button
                        type="button"
                        id="btn-date-picker-toggle"
                        onclick="toggleDatePickerDropdown(event)"
                        class="h-8 px-3 rounded-xl bg-amber-50/70 hover:bg-amber-100/80 border border-amber-400 text-amber-950 font-bold text-xs shadow-2xs inline-flex items-center gap-1.5 transition-all cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5 text-amber-600 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span id="date-picker-active-label">3 วันล่าสุด</span>
                        <svg id="date-picker-arrow-icon" class="w-3 h-3 text-amber-800 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Right Arrow > -->
                    <button
                        type="button"
                        onclick="shiftDateFilter(1)"
                        class="w-8 h-8 rounded-xl bg-white hover:bg-slate-100 active:scale-95 border border-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs shadow-2xs transition-all cursor-pointer"
                        title="ช่วงเวลาถัดไป"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <!-- Interactive Date Picker Dropdown Popup (ตรงตามภาพที่ 2 เป๊ะๆ) -->
                    <div id="date-picker-dropdown" class="hidden absolute top-full left-0 sm:left-auto sm:right-0 mt-2 z-50 bg-white rounded-2xl border border-slate-200/90 shadow-2xl p-4 w-[310px] sm:w-[330px]">
                        <!-- Top Tabs: รายวัน / เลือกช่วงวันที่ -->
                        <div class="grid grid-cols-2 p-1 bg-slate-100 rounded-xl mb-3 text-xs font-bold text-slate-600">
                            <button type="button" id="tab-calendar-single" onclick="setCalendarMode('single')" class="py-1.5 rounded-lg transition-all text-slate-900 bg-white shadow-2xs cursor-pointer">
                                รายวัน
                            </button>
                            <button type="button" id="tab-calendar-range" onclick="setCalendarMode('range')" class="py-1.5 rounded-lg transition-all text-slate-500 hover:text-slate-900 cursor-pointer">
                                เลือกช่วงวันที่
                            </button>
                        </div>

                        <!-- Calendar Header: Month Year + Prev / Next -->
                        <div class="flex items-center justify-between mb-3 px-1">
                            <span id="calendar-month-year-label" class="font-bold text-xs text-slate-800">
                                November 2026 (พฤศจิกายน 2569)
                            </span>
                            <div class="flex items-center gap-1">
                                <button type="button" onclick="navigateCalendarMonth(-1)" class="w-6 h-6 rounded-lg hover:bg-slate-100 text-slate-600 flex items-center justify-center cursor-pointer">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <button type="button" onclick="navigateCalendarMonth(1)" class="w-6 h-6 rounded-lg hover:bg-slate-100 text-slate-600 flex items-center justify-center cursor-pointer">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Weekday Headers -->
                        <div class="grid grid-cols-7 gap-1 text-center text-[10.5px] font-semibold text-slate-400 mb-1">
                            <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                        </div>

                        <!-- Days Grid (dynamically rendered) -->
                        <div id="calendar-days-grid" class="grid grid-cols-7 gap-1 text-xs text-center"></div>

                        <!-- Dropdown Footer: Clear | Today -->
                        <div class="flex items-center justify-between border-t border-slate-100 pt-2.5 mt-3 text-xs font-bold">
                            <button type="button" onclick="clearCustomDateFilter()" class="text-blue-600 hover:text-blue-800 cursor-pointer">
                                Clear
                            </button>
                            <button type="button" onclick="selectTodayInCalendar()" class="text-blue-600 hover:text-blue-800 cursor-pointer">
                                Today
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Quick Filter Pills (7 Buttons) -->
                <div class="inline-flex items-center gap-1 sm:gap-1.5 flex-wrap">
                    <button type="button" onclick="setDateFilterPill('today')" id="pill-today" class="date-filter-pill px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 transition-all cursor-pointer shadow-2xs">
                        วันนี้
                    </button>
                    <button type="button" onclick="setDateFilterPill('3days')" id="pill-3days" class="date-filter-pill active px-3 py-1.5 rounded-xl text-xs font-bold border border-[#0F172A] bg-[#0F172A] text-white transition-all cursor-pointer shadow-xs">
                        3 วันล่าสุด
                    </button>
                    <button type="button" onclick="setDateFilterPill('7days')" id="pill-7days" class="date-filter-pill px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 transition-all cursor-pointer shadow-2xs">
                        7 วันล่าสุด
                    </button>
                    <button type="button" onclick="setDateFilterPill('15days')" id="pill-15days" class="date-filter-pill px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 transition-all cursor-pointer shadow-2xs">
                        15 วันล่าสุด
                    </button>
                    <button type="button" onclick="setDateFilterPill('30days')" id="pill-30days" class="date-filter-pill px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 transition-all cursor-pointer shadow-2xs">
                        30 วันล่าสุด
                    </button>
                    <button type="button" onclick="setDateFilterPill('this_month')" id="pill-this_month" class="date-filter-pill px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 transition-all cursor-pointer shadow-2xs">
                        ทั้งเดือน
                    </button>
                    <button type="button" onclick="setDateFilterPill('all')" id="pill-all" class="date-filter-pill px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 transition-all cursor-pointer shadow-2xs">
                        ทั้งหมด
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. RECEIPT HISTORY SECTION & TABLE -->
        <section class="bg-white rounded-[22px] border border-[#E5EAF1] shadow-sm overflow-hidden">
            <!-- Section Header -->
            <div class="px-5 py-4 sm:px-6 border-b border-[#E5EAF1] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#FFF0F2] text-[#E90032] flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2.1" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-[17px] sm:text-[18px] font-bold text-[#101828] tracking-tight">
                        ประวัติใบเสร็จสั่งซื้อวัตถุดิบ
                    </h2>
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-[#159A59] border border-emerald-200/70 text-[11px] font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#159A59] animate-pulse"></span>
                        <span>ซิงก์อัตโนมัติ</span>
                    </span>
                </div>

                <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end flex-wrap">
                    <!-- Quick Search Input (ตรงตามภาพหน้าแคชเชียร์เป๊ะๆ) -->
                    <div class="relative flex-1 sm:w-56 sm:flex-initial">
                        <input
                            type="text"
                            id="receipt-history-search"
                            oninput="filterReceiptHistoryRows()"
                            placeholder="ค้นหาเมนูหม่าล่า..."
                            class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200/90 rounded-full text-[12.5px] text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-[#E50914] shadow-2xs"
                        >
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- 3 / 4 Items per Row Toggle (ตรงตามภาพหน้าแคชเชียร์เป๊ะๆ) -->
                    <div class="flex items-center bg-white border border-slate-200/90 rounded-full p-0.5 shadow-2xs shrink-0" title="เลือกการแสดงผลจำนวนรายการต่อแถว">
                        <!-- 1. สี่เหลี่ยม 3 จุด (3 รายการต่อแถว) -->
                        <button type="button" onclick="setReceiptGridColumns(3)" id="btn-grid-3"
                                class="grid-toggle-btn px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all text-slate-500 hover:text-slate-800 hover:bg-slate-100 cursor-pointer"
                                title="แสดง 3 รายการต่อแถว">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                <rect x="2" y="7" width="4" height="6" rx="1"/>
                                <rect x="8" y="7" width="4" height="6" rx="1"/>
                                <rect x="14" y="7" width="4" height="6" rx="1"/>
                            </svg>
                            <span class="text-[11px] leading-none">3</span>
                        </button>

                        <!-- 2. สี่เหลี่ยม 4 จุด (4 รายการต่อแถว) -->
                        <button type="button" onclick="setReceiptGridColumns(4)" id="btn-grid-4"
                                class="grid-toggle-btn active px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all bg-[#E50914] text-white shadow-2xs cursor-pointer"
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

                    <span id="receipts-total-badge" class="text-xs font-semibold text-[#66758F] bg-[#F7F8FB] border border-[#E5EAF1] px-3 py-1.5 rounded-full shrink-0">
                        รวม {{ $receipts->total() }} บิล
                    </span>
                </div>
            </div>

            <!-- Receipt Slips Grid (สลิปแสกนรายการสินค้า - จัดแสดง 4 ใบต่อแถว) -->
            <div class="p-4 sm:p-5 bg-slate-50/50">
                <div class="text-center font-bold text-[13.5px] text-[#475467] mb-4 pb-2 border-b border-[#E5EAF1] tracking-wide">
                    สลิปแสกนรายการสินค้า
                </div>
                <div id="receipts-table-body" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-4 gap-4 items-start">
                    @forelse($receipts as $receipt)
                        @php
                            $allImgs = ($receipt->parsed_data && isset($receipt->parsed_data['images']) && is_array($receipt->parsed_data['images']))
                                ? $receipt->parsed_data['images']
                                : ($receipt->image_path ? [$receipt->image_path] : []);
                            $allImgUrls = array_values(array_map(function ($img) {
                                if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, 'data:image')) {
                                    return $img;
                                }
                                return asset(ltrim($img, '/'));
                            }, $allImgs));
                            $searchKey = mb_strtolower(($receipt->receipt_no ?: '') . ' ' . ($receipt->vendor_name ?: '') . ' ' . $receipt->items->pluck('item_name')->implode(' '));
                        @endphp
                        <div id="receipt-card-{{ $receipt->id }}" class="receipt-history-row receipt-summary-card bg-white rounded-2xl border border-slate-200/90 shadow-xs hover:shadow-md transition-shadow overflow-hidden flex flex-col text-left w-full" data-search="{{ $searchKey }}" data-date="{{ $receipt->purchase_date ? $receipt->purchase_date->format('Y-m-d') : '' }}" data-created-date="{{ $receipt->created_at ? $receipt->created_at->format('Y-m-d') : '' }}" data-amount="{{ (float) $receipt->total_amount }}">
                            <!-- Card Top Bar -->
                            <div class="px-3 py-2 bg-slate-100 border-b border-slate-200 flex items-center justify-between text-slate-800">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <div class="w-5.5 h-5.5 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-amber-600 shadow-2xs shrink-0">
                                        <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-[11.5px] font-bold text-slate-800 leading-tight truncate">ใบเสร็จรับเงิน (5*8)</h4>
                                        <p class="text-[9px] text-slate-500 font-medium leading-tight truncate">บิลสลิปมาตรฐานหม่าล่าสตรีท</p>
                                    </div>
                                </div>
                                <button type="button" onclick="executeReceiptSlipPrintFor({{ $receipt->id }})" class="w-6 h-6 rounded-lg hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center font-bold text-xs transition cursor-pointer shrink-0" title="สั่งพิมพ์สลิป 5*8">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Mode Switcher Tabs -->
                            <div class="px-2.5 py-1.5 bg-slate-200/60 border-b border-slate-200 flex items-center justify-between text-xs gap-1">
                                <div class="flex items-center gap-1 bg-white p-0.5 rounded-lg border border-slate-200 shadow-2xs">
                                    <button type="button" id="tabBtnSlip-{{ $receipt->id }}" onclick="switchInlineReceiptCardTab({{ $receipt->id }}, 'slip')" class="px-2 py-0.5 rounded-md font-bold text-[10px] transition flex items-center gap-1 bg-amber-600 text-white shadow-2xs cursor-pointer">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>บิล (5*8)</span>
                                    </button>
                                    @if(count($allImgUrls) > 0)
                                        <button type="button" id="tabBtnImage-{{ $receipt->id }}" onclick="switchInlineReceiptCardTab({{ $receipt->id }}, 'image')" class="px-2 py-0.5 rounded-md font-bold text-[10px] text-slate-600 hover:text-slate-900 transition flex items-center gap-1 cursor-pointer">
                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span>ภาพ ({{ count($allImgUrls) }})</span>
                                        </button>
                                    @endif
                                </div>
                                <div class="text-[9.5px] font-mono font-semibold text-slate-600 truncate ml-1">
                                    {{ $receipt->receipt_no ?: '-' }}
                                </div>
                            </div>

                            <!-- View 1: Slip Paper (Already Open!) -->
                            <div id="inlineSlipView-{{ $receipt->id }}" class="p-2 sm:p-2.5 bg-slate-100 flex items-center justify-center">
                                <div id="inlineSlipPrintableArea-{{ $receipt->id }}" class="bg-white p-3 rounded-lg border border-slate-300 shadow-2xs w-full relative font-sans text-slate-900 text-xs">
                                    <!-- Black Triangle Corner -->
                                    <div style="position: absolute; top: 0; right: 0; width: 0; height: 0; border-top: 32px solid #000; border-left: 32px solid transparent;"></div>

                                    <!-- Header info -->
                                    <div class="text-center mt-1.5 leading-tight">
                                        <div class="text-[13px] font-bold text-black">{{ $receipt->vendor_name ?: 'หม่าล่าสตรีท' }}</div>
                                        <div class="text-[10.5px] text-slate-700 font-semibold mt-0.5">หม่าล่าสตรีท (จัดซื้อวัตถุดิบ)</div>
                                    </div>

                                    <div class="border-t border-black my-1.5"></div>

                                    <div class="text-[11.5px] font-bold text-black">ใบเสร็จรับเงิน</div>
                                    <div class="text-[10.5px] font-mono font-semibold text-slate-800">{{ $receipt->receipt_no ?: '-' }}</div>

                                    <div class="border-t border-dashed border-slate-400 my-1"></div>

                                    <div class="flex justify-between text-[10.5px] mb-0.5">
                                        <span class="text-slate-600">พนักงานจัดซื้อ</span>
                                        <span class="font-bold text-black">{{ $receipt->parsed_data['buyer_name'] ?? 'หม่าล่าสตรีท' }}</span>
                                    </div>
                                    <div class="flex justify-between text-[10.5px]">
                                        <span class="text-slate-600">วันที่</span>
                                        <span class="font-mono text-black">{{ $receipt->purchase_date->format('d/m/Y') }}</span>
                                    </div>

                                    <div class="border-t border-black my-1"></div>

                                    <!-- Scrollable Items List -->
                                    <div class="max-h-[200px] overflow-y-auto pr-1 space-y-1 my-1">
                                        @foreach($receipt->items as $it)
                                            @php
                                                $qVal = (float) $it->quantity;
                                                $qFormatted = 'x' . (floor($qVal) == $qVal ? (int)$qVal : rtrim(rtrim(number_format($qVal, 2), '0'), '.'));
                                                $rawTotal = (float) ($it->total_price ?: ($it->unit_price * $it->quantity));
                                                $rawUnit = (float) ($it->unit_price ?: ($qVal > 0 ? ($rawTotal / $qVal) : $rawTotal));
                                                $uPriceFormatted = (floor($rawUnit) == $rawUnit) ? number_format($rawUnit, 0) : number_format($rawUnit, 2);
                                                $subtotalFormatted = (floor($rawTotal) == $rawTotal) ? number_format($rawTotal, 0) : number_format($rawTotal, 2);
                                            @endphp
                                            <div class="flex justify-between items-baseline text-[11px] leading-tight">
                                                <span class="w-6.5 font-mono text-left shrink-0 text-slate-800 font-bold">{{ $qFormatted }}</span>
                                                <span class="flex-1 min-w-0 px-1 text-black font-medium truncate" title="{{ $it->item_name }}">
                                                    {{ $it->item_name }}
                                                    @if($it->total_yield_sticks)
                                                        <span class="text-[9px] text-slate-500">({{ $it->total_yield_sticks }} ไม้)</span>
                                                    @endif
                                                </span>
                                                <span class="min-w-[34px] font-mono text-right shrink-0 text-slate-800 font-normal pr-1.5 whitespace-nowrap">{{ $uPriceFormatted }}</span>
                                                <span class="min-w-[38px] font-mono text-right shrink-0 text-black font-semibold whitespace-nowrap">{{ $subtotalFormatted }}</span>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="border-t border-dashed border-slate-400 my-1"></div>

                                    <!-- Totals -->
                                    <div class="flex justify-between items-baseline text-[11px] mt-1">
                                        <div>
                                            <span class="text-slate-600 text-[10.5px]">จำนวนรวม</span>
                                            <span class="font-mono font-bold text-black ml-1 text-[11px]">{{ number_format($receipt->items->sum('total_yield_sticks'), 2) }}</span>
                                        </div>
                                        <div class="w-[60%] text-right space-y-0.5">
                                            <div class="flex justify-between border-b border-black pb-0.5">
                                                <span class="text-slate-600 text-[10.5px]">รวมเป็นเงิน</span>
                                                <span class="font-mono text-black font-semibold text-[11px]">{{ number_format($receipt->total_amount, 0) }}</span>
                                            </div>
                                            <div class="flex justify-between border-b-2 border-double border-black pt-0.5 pb-0.5 font-bold">
                                                <span class="text-black text-[10.5px]">รวมทั้งสิ้น</span>
                                                <span class="font-mono text-black text-[11px]">{{ number_format($receipt->total_amount, 0) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- View 2: Scanned Images (Inside Card - 1:1 Aspect Ratio Window) -->
                            @if(count($allImgUrls) > 0)
                                <div id="inlineImageView-{{ $receipt->id }}" class="hidden p-2.5 bg-slate-900 flex flex-col items-center">
                                    @if(count($allImgUrls) > 1)
                                        <div class="flex items-center gap-1.5 mb-2 overflow-x-auto w-full justify-center">
                                            @foreach($allImgUrls as $idx => $imgUrl)
                                                <button
                                                    type="button"
                                                    onclick="setInlineCardImage({{ $receipt->id }}, '{{ $imgUrl }}', {{ $idx + 1 }})"
                                                    class="px-2 py-0.5 rounded text-[10px] font-bold {{ $idx === 0 ? 'bg-amber-600 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }} inline-img-btn-{{ $receipt->id }} cursor-pointer"
                                                >
                                                    📷 รูป {{ $idx + 1 }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- 1:1 Aspect Ratio Image Frame (หน้าต่างแสดงเป็นภาพ 1:1 กดเข้ามาดูได้) -->
                                    <div
                                        class="w-full aspect-square rounded-xl overflow-hidden bg-slate-950 flex items-center justify-center relative border border-slate-700 group cursor-pointer shadow-inner"
                                        onclick="openReceiptImageViewer(document.getElementById('inlineImgDisplay-{{ $receipt->id }}').src, '{{ $receipt->receipt_no ?: '-' }}', 'รูปภาพใบเสร็จ')"
                                        title="คลิกเพื่อกดเข้ามาดูภาพขนาดใหญ่"
                                    >
                                        <img
                                            id="inlineImgDisplay-{{ $receipt->id }}"
                                            src="{{ $allImgUrls[0] }}"
                                            alt="ภาพใบเสร็จ {{ $receipt->receipt_no }}"
                                            class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300"
                                            onerror="this.onerror=null; this.src='{{ asset('assets/brand/hero-mala-bowl-clean.png') }}';"
                                        >
                                        <!-- Overlay hint on hover: คลิกดูภาพขยาย -->
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1.5 text-white text-xs font-semibold backdrop-blur-2xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                                            </svg>
                                            <span>กดเข้ามาดูภาพ</span>
                                        </div>
                                        <!-- 1:1 Badge -->
                                        <div class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-black/60 text-[9px] font-mono text-slate-300 pointer-events-none">
                                            1:1
                                        </div>
                                    </div>

                                    <!-- Action Bar: กดดูภาพ & โหลดออกมาเป็น JPG -->
                                    <div class="flex items-center justify-between w-full mt-2 gap-1.5">
                                        <button
                                            type="button"
                                            onclick="openReceiptImageViewer(document.getElementById('inlineImgDisplay-{{ $receipt->id }}').src, '{{ $receipt->receipt_no ?: '-' }}', 'รูปภาพใบเสร็จ')"
                                            class="flex-1 py-1.5 px-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-semibold text-[10.5px] inline-flex items-center justify-center gap-1 transition border border-slate-700 cursor-pointer"
                                            title="กดเข้ามาดูภาพขนาดใหญ่"
                                        >
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <span>กดดูภาพ</span>
                                        </button>

                                        <a
                                            id="inlineImgDownload-{{ $receipt->id }}"
                                            href="{{ $allImgUrls[0] }}"
                                            download="receipt-{{ $receipt->receipt_no ?: $receipt->id }}.jpg"
                                            onclick="downloadReceiptImage(event, this.href, 'receipt-{{ $receipt->receipt_no ?: $receipt->id }}.jpg')"
                                            class="flex-1 py-1.5 px-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-[10.5px] inline-flex items-center justify-center gap-1 transition shadow-xs cursor-pointer"
                                            title="ดาวน์โหลดรูปภาพใบเสร็จออกมาเป็น JPG"
                                        >
                                            <svg class="w-3 h-3 stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            <span>โหลด JPG</span>
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <!-- Card Bottom Bar -->
                            <div class="px-2.5 py-2 bg-white border-t border-slate-200 flex items-center justify-between gap-1.5 mt-auto">
                                <button type="button" onclick="openReceiptSlipConfigModal()" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 font-bold rounded-lg text-[10px] transition flex items-center gap-1 cursor-pointer">
                                    <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                                    </svg>
                                    <span>ตั้งค่าบิล</span>
                                </button>
                                <button
                                    type="button"
                                    onclick="openDeleteReceiptModal({{ $receipt->id }}, '{{ addslashes($receipt->receipt_no ?: ('#' . $receipt->id)) }}', '{{ addslashes($receipt->vendor_name) }}', '{{ number_format($receipt->total_amount, 2) }}')"
                                    class="px-2.5 py-1 bg-[#E50914] hover:bg-[#C8102E] text-white font-bold rounded-lg text-[10px] transition shadow-xs flex items-center gap-1 cursor-pointer"
                                    title="ลบรายการใบเสร็จนี้"
                                >
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>ลบรายการ</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-[#66758F] text-xs">
                            ยังไม่มีประวัติใบเสร็จ สามารถแนบรูปภาพและเริ่มสแกนบันทึกบิลแรกได้จากการ์ดสแกนใบเสร็จฝั่งขวา
                        </div>
                    @endforelse
                </div>
            </div>

            @if($receipts->hasPages())
                <div class="p-4 border-t border-[#E5EAF1]">
                    {{ $receipts->links() }}
                </div>
            @endif
        </section>
            </div> <!-- Close Left Column (70%) -->

            <!-- RIGHT COLUMN (30%): การ์ดสแกนใบเสร็จซื้อของ & แปลงสูตรไม้ (เรียบร้อย กะทัดรัด ไม่กระจัดกระจาย) -->
            <div class="receipts-col-right">
                <div id="scan-card" class="bg-white rounded-[22px] border border-[#E5EAF1] shadow-md overflow-hidden flex flex-col">
                    <!-- 4.1 Card Header -->
                    <div class="bg-white border-b border-[#E5EAF1] px-4 py-3 sm:px-4.5 sm:py-3.5 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-[#FFF0F2] border border-rose-100 flex items-center justify-center text-[#E90032] shrink-0">
                                <svg class="w-5 h-5 stroke-current" fill="none" stroke-width="2.1" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-[14.5px] text-[#101828] tracking-tight leading-snug truncate">
                                    สแกนใบเสร็จซื้อของ & แปลงสูตรไม้
                                </h3>
                                <p class="text-[10.5px] text-[#66758F] font-normal truncate">
                                    ระบบอ่านรายการอัตโนมัติ และคำนวณต้นทุนต่อไม้
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 4.2 Card Form Body -->
                    <form id="receipt-form" onsubmit="submitReceiptForm(event)" class="p-3.5 sm:p-4 space-y-3 text-xs">
                        <!-- Datalist of all RawMaterials for autocomplete (Including scan_names aliases) -->
                        <datalist id="raw-materials-datalist">
                            @foreach($rawMaterials as $rm)
                                <option value="{{ $rm->name }}">{{ $rm->purchase_unit }} ({{ number_format($rm->yield_sticks_per_unit, 0) }} ไม้/หน่วย)</option>
                                @php
                                    $snsList = is_array($rm->scan_names) ? $rm->scan_names : (json_decode($rm->scan_names ?? '[]', true) ?: []);
                                @endphp
                                @foreach($snsList as $sn)
                                    @php
                                        $snName = is_array($sn) ? ($sn['name'] ?? '') : (string) $sn;
                                        $snVendor = is_array($sn) ? ($sn['vendor'] ?? '') : '';
                                    @endphp
                                    @if(!empty(trim($snName)))
                                        <option value="{{ trim($snName) }}">{{ $rm->name }} (ชื่อสำหรับสแกน{{ $snVendor ? ' • ' . $snVendor : '' }})</option>
                                    @endif
                                @endforeach
                            @endforeach
                        </datalist>

                        <!-- AI Vision Status Panel -->
                        <div class="p-2.5 sm:p-3 bg-gradient-to-r from-[#0F172A] via-[#1B1947] to-[#1E293B] rounded-xl text-white shadow-xs space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="flex h-2 w-2 relative shrink-0">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $hasGeminiKey ? 'bg-emerald-400 opacity-75' : 'bg-amber-400 opacity-75' }}"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 {{ $hasGeminiKey ? 'bg-[#159A59]' : 'bg-[#F5B800]' }}"></span>
                                    </span>
                                    <span class="text-[12px] font-bold tracking-tight text-white truncate flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-[#F5B800] fill-current shrink-0" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                        <span>Google Gemini AI</span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span id="ai-key-status-badge" class="text-[10px] font-semibold px-2 py-0.5 rounded-full inline-flex items-center gap-1 {{ $hasGeminiKey ? 'bg-[#159A59]/25 text-emerald-300 border border-emerald-500/40' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                                        <span class="w-1 h-1 rounded-full {{ $hasGeminiKey ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                                        <span>{{ $hasGeminiKey ? 'พร้อมสแกนไทย' : 'ต้องการ Key' }}</span>
                                    </span>
                                    <button
                                        type="button"
                                        onclick="toggleApiKeyDrawer()"
                                        class="px-2 py-1 rounded-lg bg-white/10 hover:bg-white/20 active:scale-95 text-[10.5px] font-semibold text-[#F5B800] inline-flex items-center gap-1 transition-all cursor-pointer border border-white/10"
                                    >
                                        <svg class="w-3 h-3 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                        <span id="btn-toggle-key-text">{{ $hasGeminiKey ? 'แก้ Key' : 'ใส่ Key' }}</span>
                                    </button>
                                </div>
                            </div>
                            <div class="text-[10px] text-slate-300 leading-tight">
                                ถอดรหัสข้อความไทย/อังกฤษ คมชัด แม่นยำ (เฉลี่ย 2-4 วิ | ไม่เกิน 30 วิ)
                            </div>
                        </div>

                        <!-- Collapsible API Key Drawer -->
                        <div id="gemini-key-drawer" class="hidden p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-2">
                            <div class="flex items-center justify-between flex-wrap gap-1">
                                <span class="text-[11px] font-bold text-amber-950">ใส่ Gemini API Key:</span>
                                <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener" class="text-[10px] font-semibold text-[#E90032] hover:underline">
                                    ขอรับ Key ฟรี &rarr;
                                </a>
                            </div>
                            <div class="flex gap-1.5">
                                <input
                                    type="password"
                                    id="gemini-key-input"
                                    placeholder="วาง API Key เช่น AIzaSy..."
                                    class="flex-1 text-xs px-2.5 py-1.5 bg-white border border-amber-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-amber-500 font-mono"
                                >
                                <button
                                    type="button"
                                    onclick="saveGeminiApiKey()"
                                    id="btn-save-key"
                                    class="px-3 py-1.5 bg-amber-600 hover:bg-amber-500 active:scale-95 text-white text-xs font-bold rounded-lg shadow-2xs transition-all shrink-0 cursor-pointer"
                                >
                                    บันทึก
                                </button>
                            </div>
                            <p class="text-[9.5px] text-amber-800 leading-tight">
                                * บันทึกลงฝั่ง Server (.env) อย่างปลอดภัย
                            </p>
                        </div>

                        <!-- 1. Receipt Image Upload Box (Dashed Border) -->
                        <div class="p-3 bg-[#F8FAFC] rounded-xl border border-dashed border-[#CBD5E1] space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-[#101828]">แนบรูปถ่ายใบเสร็จ</span>
                                <span class="text-[10px] text-[#66758F]">แนบได้หลายรูป/บิล</span>
                            </div>

                            <!-- Dual Action Buttons: Camera vs Album in 2 Equal Columns -->
                            <div class="grid grid-cols-2 gap-2">
                                <label class="h-9 px-2 rounded-xl bg-[#E90032] hover:bg-[#F50043] active:scale-95 text-white text-xs font-semibold cursor-pointer transition-all shadow-2xs flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="truncate">ถ่ายรูปกล้อง</span>
                                    <input type="file" id="modal-camera-file" accept="image/jpeg,image/png,image/webp" capture="environment" onchange="handlePhotoCapture(event)" class="hidden">
                                </label>

                                <label class="h-9 px-2 rounded-xl bg-[#0F172A] hover:bg-[#1E293B] active:scale-95 text-white text-xs font-semibold cursor-pointer transition-all shadow-2xs flex items-center justify-center gap-1.5">
                                    <svg class="w-4 h-4 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="truncate">เลือกอัลบั้ม</span>
                                    <input type="file" id="modal-receipt-file" accept="image/jpeg,image/png,image/webp" multiple onchange="handlePhotoCapture(event)" class="hidden">
                                </label>
                            </div>

                            <!-- Multi-Photo Thumbnail Strip -->
                            <div id="photos-strip-container" class="hidden space-y-1.5">
                                <div class="flex items-center justify-between text-[11px] text-[#101828] font-semibold">
                                    <span>รูปที่แนบ <strong id="photos-count-badge" class="text-[#E90032]">(0 รูป)</strong>:</span>
                                    <span class="text-[10px] text-[#66758F]">คลิกดูรูป / × เพื่อลบ</span>
                                </div>
                                <div id="photos-strip" class="flex gap-2 overflow-x-auto pb-1"></div>
                            </div>

                            <!-- Empty Photo Prompt -->
                            <div id="photos-empty-prompt" class="py-2.5 px-3 bg-white rounded-xl border border-[#E5EAF1] text-center">
                                <p class="text-[11.5px] font-semibold text-[#101828]">ยังไม่มีรูปถ่ายใบเสร็จ</p>
                                <p class="text-[10px] text-[#66758F] mt-0.5">
                                    กด <strong>"ถ่ายรูปกล้อง"</strong> หรือ <strong>"เลือกอัลบั้ม"</strong> ด้านบนเพื่อเริ่ม
                                </p>
                            </div>

                            <!-- AI Vision Scan Button -->
                            <div id="scan-action-container" class="pt-0.5">
                                <button
                                    type="button"
                                    id="btn-run-ocr"
                                    onclick="scanAllReceiptPhotos()"
                                    disabled
                                    class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-[#FB7185] via-[#F43F5E] to-[#FBBF24] hover:from-[#E90032] hover:to-[#F59E0B] active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold shadow-sm flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                                >
                                    <svg class="w-3.5 h-3.5 stroke-current shrink-0" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <span id="btn-scan-text">⚡ เริ่มสแกนบิลด้วย AI Vision</span>
                                </button>
                            </div>

                            <!-- Live OCR Status Banner -->
                            <div id="ocr-status-banner" class="hidden p-2.5 rounded-xl border text-xs flex items-start gap-2">
                                <div id="ocr-status-icon" class="shrink-0 mt-0.5"></div>
                                <div class="min-w-0">
                                    <strong class="font-bold text-xs block truncate" id="ocr-status-title"></strong>
                                    <span class="text-[10.5px] block leading-tight" id="ocr-status-desc"></span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Receipt Header Fields -->
                        <div class="space-y-2">
                            <!-- Row 1: Receipt No & Purchase Date in 2 columns -->
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="rec-no" class="block text-[11px] font-bold text-[#101828] mb-1">เลขที่ใบเสร็จ:</label>
                                    <input
                                        type="text"
                                        id="rec-no"
                                        value=""
                                        placeholder="เช่น MK-20261106-9912"
                                        required
                                        class="w-full h-9 text-xs px-2.5 bg-[#F8FAFC] border border-[#CBD5E1] rounded-xl focus:bg-white focus:outline-none focus:border-[#E90032] font-semibold text-[#101828]"
                                    >
                                </div>
                                <div>
                                    <label for="rec-date" class="block text-[11px] font-bold text-[#101828] mb-1">วันที่ซื้อ:</label>
                                    <input
                                        type="date"
                                        id="rec-date"
                                        value="{{ date('Y-m-d') }}"
                                        required
                                        class="w-full h-9 text-xs px-2 bg-[#F8FAFC] border border-[#CBD5E1] rounded-xl focus:bg-white focus:outline-none focus:border-[#E90032] font-semibold text-[#101828]"
                                    >
                                </div>
                            </div>

                            <!-- Row 2: Vendor Name full width -->
                            <div>
                                <label for="rec-vendor" class="block text-[11px] font-bold text-[#101828] mb-1">ชื่อร้าน / ซัพพลายเออร์:</label>
                                <input
                                    type="text"
                                    id="rec-vendor"
                                    value=""
                                    list="vendor-presets"
                                    placeholder="เช่น สยามแม็คโคร, โลตัส, ตลาดสด"
                                    required
                                    class="w-full h-9 text-xs px-2.5 bg-[#F8FAFC] border border-[#CBD5E1] rounded-xl focus:bg-white focus:outline-none focus:border-[#E90032] font-semibold text-[#101828]"
                                >
                                <datalist id="vendor-presets">
                                    @foreach($vendorPresets as $vp)
                                        <option value="{{ $vp }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>

                        <!-- 3. Line Items & Yield Conversion Section -->
                        <div class="space-y-1.5 pt-1">
                            <div class="flex justify-between items-center">
                                <label class="text-[11.5px] font-bold text-[#101828] flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-[#66758F] stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a7 7 0 01-7 7m7-7a7 7 0 00-7-7m-7 7a7 7 0 007 7m-7-7a7 7 0 017-7"/>
                                    </svg>
                                    <span>รายการสินค้า & แปลงสูตรไม้:</span>
                                </label>
                                <button
                                    type="button"
                                    onclick="addNewItemRow()"
                                    class="text-xs text-[#E90032] font-bold hover:underline inline-flex items-center gap-0.5 cursor-pointer"
                                >
                                    <span>+ เพิ่มรายการ</span>
                                </button>
                            </div>

                            <div class="space-y-2 max-h-[300px] overflow-y-auto pr-0.5" id="items-rows-container">
                                <!-- Clean Initial Empty State Box -->
                                <div id="items-empty-state" class="p-4 text-center border border-dashed border-[#CBD5E1] rounded-xl bg-[#F8FAFC]">
                                    <svg class="w-6 h-6 mx-auto mb-1 text-slate-300 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    <p class="text-xs font-bold text-[#101828]">ยังไม่มีรายการสินค้า</p>
                                    <p class="text-[10px] text-[#66758F] mt-0.5">
                                        ถ่ายรูปเพื่อสแกนอัตโนมัติ หรือกด <strong>"+ เพิ่มรายการ"</strong>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Total Summary Card -->
                        <div class="p-3 bg-[#F8FAFC] border border-[#E5EAF1] rounded-xl flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-[#101828] block">ยอดรวมทั้งบิล (฿):</span>
                                <span class="text-[10px] text-[#66758F]">คำนวณตามจำนวนชิ้น x ราคาซื้อ</span>
                                <span id="total-mismatch-warning" class="hidden text-[10px] font-semibold text-amber-700 mt-0.5 block"></span>
                            </div>
                            <input
                                type="number"
                                id="rec-total"
                                step="0.01"
                                min="0"
                                value="0.00"
                                required
                                class="w-32 h-10 text-right font-extrabold text-xl px-2.5 bg-white border-2 border-rose-300 rounded-xl text-[#E90032] focus:outline-none focus:border-[#E90032] shadow-2xs shrink-0"
                            >
                        </div>

                        <!-- 5. Card Footer Action Buttons -->
                        <div class="grid grid-cols-3 gap-2 pt-1">
                            <button
                                type="button"
                                onclick="requestCloseScanModal()"
                                class="py-2.5 rounded-xl bg-white hover:bg-slate-50 border border-[#CBD5E1] text-xs font-bold text-[#101828] transition-colors cursor-pointer text-center"
                            >
                                ยกเลิก
                            </button>
                            <button
                                type="submit"
                                id="btn-save-receipt"
                                class="col-span-2 py-2.5 rounded-xl bg-[#E90032] hover:bg-[#F50043] active:scale-[0.99] text-white text-xs font-bold shadow-md shadow-[#E90032]/25 flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                            >
                                <svg class="w-4 h-4 stroke-current shrink-0" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                </svg>
                                <span class="truncate">บันทึกใบเสร็จ & แปลงไม้</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div> <!-- Close Right Column (30%) -->

        </div> <!-- Close receipts-main-grid -->
    </div> <!-- Close max-w-[1720px] -->
</div> <!-- Close min-h-screen -->

<!-- ========================================================================= -->
<!-- 5. POPUP: พรีวิวใบเสร็จรับเงินขนาด 5*8 (ตามภาพตัวอย่างที่ 2 เป๊ะๆ) -->
<!-- ========================================================================= -->
<div id="receiptSlipModal5x8" class="hidden fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5" onclick="if(event.target === this) closeReceiptSlipModal()">
    <div class="bg-white rounded-2xl border border-slate-200/90 w-full max-w-md overflow-hidden shadow-2xl flex flex-col max-h-[95vh] animate-in fade-in zoom-in-95 duration-150">
        
        <!-- Modal Top Bar (ตามภาพที่ 2 เป๊ะๆ) -->
        <div class="px-4 py-3 bg-slate-100 border-b border-slate-200 flex items-center justify-between text-slate-800 shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-amber-600 shadow-2xs">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800" id="receiptModalMainTitle">ใบเสร็จรับเงิน (บิลขนาด 5*8)</h3>
                    <p class="text-[10px] text-slate-600 font-medium" id="receiptModalSubTitle">รูปแบบบิลสลิปมาตรฐานหม่าล่าสตรีท</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5">
                <button type="button" onclick="executeReceiptSlipPrint()" class="w-8 h-8 rounded-xl hover:bg-slate-200/80 text-slate-500 hover:text-slate-800 flex items-center justify-center font-bold text-xs transition cursor-pointer" title="สั่งพิมพ์สลิป 5*8">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z" />
                    </svg>
                </button>
                <button type="button" onclick="closeReceiptSlipModal()" class="w-8 h-8 rounded-xl hover:bg-slate-200/80 text-slate-400 hover:text-slate-800 flex items-center justify-center font-bold text-base transition cursor-pointer" title="ปิดหน้าต่าง">✕</button>
            </div>
        </div>

        <!-- Mode Switcher Tabs (สลับดู สลิป 5*8 หรือ ภาพที่สแกนบิล) -->
        <div class="px-4 py-1.5 bg-slate-200/60 border-b border-slate-200 flex items-center justify-between text-xs shrink-0">
            <div class="flex items-center gap-1 bg-white p-0.5 rounded-xl border border-slate-200 shadow-2xs">
                <button type="button" id="tabBtnSlip" onclick="switchReceiptModalView('slip')" class="px-3 py-1 rounded-lg font-bold text-[11.5px] transition flex items-center gap-1.5 bg-amber-600 text-white shadow-2xs cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>บิลสลิป (5*8)</span>
                </button>
                <button type="button" id="tabBtnImage" onclick="switchReceiptModalView('image')" class="px-3 py-1 rounded-lg font-bold text-[11.5px] text-slate-600 hover:text-slate-900 transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span id="tabBtnImageLabel">ภาพที่สแกนบิล</span>
                </button>
            </div>
            <div id="receiptDocBadge" class="text-[11px] font-mono font-semibold text-slate-600">
                <!-- Document Ref -->
            </div>
        </div>

        <!-- View 1: Receipt Slip Preview Area (Exact Style of Image 2) -->
        <div id="receiptSlipSection" class="p-4 sm:p-6 overflow-y-auto bg-slate-100 flex items-center justify-center flex-1 min-h-[380px]">
            <div id="receiptSlipPrintableArea" class="bg-white p-5 rounded-lg border border-slate-300 shadow-md w-full max-w-[340px] relative font-sans text-slate-900 text-xs">
                <!-- Populated dynamically via JS -->
            </div>
        </div>

        <!-- View 2: Scanned Images Area (ภาพที่แสกนบินจะอยู่ข้างใน) -->
        <div id="receiptImageSection" class="hidden p-4 overflow-y-auto bg-slate-900 flex-1 flex flex-col items-center justify-center min-h-[380px]">
            <!-- Thumbnail selection buttons for multiple pages -->
            <div id="receiptImagesThumbStrip" class="flex items-center justify-center gap-2 mb-3 w-full flex-wrap"></div>
            <!-- Main Photo -->
            <div class="relative max-w-full max-h-[58vh] flex items-center justify-center">
                <img id="receiptModalScannedImg" src="" alt="ภาพใบเสร็จที่สแกน" class="max-w-full max-h-[56vh] object-contain rounded-lg shadow-2xl border border-slate-700 bg-black/40">
            </div>
            <div class="mt-2.5 flex items-center justify-between w-full text-xs text-slate-300 px-2">
                <span id="scannedImgCaption" class="truncate font-medium text-slate-400"></span>
                <a id="scannedImgOpenTab" href="#" target="_blank" rel="noopener" class="text-amber-400 hover:text-amber-300 underline shrink-0 inline-flex items-center gap-1 font-semibold">
                    <span>เปิดภาพเต็มในแท็บใหม่ ↗</span>
                </a>
            </div>
        </div>

        <!-- Modal Actions Bottom Bar (ตามภาพตัวอย่างที่ 2 เป๊ะๆ + เปลี่ยนเป็นเปิดดูบิล) -->
        <div class="px-4 py-3 bg-white border-t border-slate-200 flex items-center justify-between gap-2 shrink-0">
            <button type="button" onclick="openReceiptSlipConfigModal()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                </svg>
                <span>ตั้งค่าโครงสร้างบิล</span>
            </button>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeReceiptSlipModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer">
                    ปิด
                </button>
                <button type="button" id="btnToggleSlipOrImage" onclick="toggleReceiptModalView()" class="px-5 py-2 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span id="btnToggleSlipOrImageText">เปิดดูบิล</span>
                </button>
            </div>
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- 6. POPUP: ตั้งค่าโครงสร้างใบเสร็จรับเงิน (5*8) -->
<!-- ========================================================================= -->
<div id="receiptSlipConfigModal" class="hidden fixed inset-0 z-[100000] bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5" onclick="if(event.target === this) closeReceiptSlipConfigModal()">
    <div class="bg-white rounded-3xl border border-slate-200/90 w-full max-w-4xl overflow-hidden shadow-2xl flex flex-col max-h-[92vh] animate-in fade-in zoom-in-95 duration-150">
        
        <!-- Header -->
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between text-slate-800 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/10 border border-amber-500/25 flex items-center justify-center text-amber-600 shadow-2xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">ตั้งค่าโครงสร้างใบเสร็จรับเงิน (บิล 5*8)</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">ปรับแต่งข้อมูลหัวบิล สาขา ข้อความหมายเหตุ และการแสดงผลในสลิป</p>
                </div>
            </div>
            <button type="button" onclick="closeReceiptSlipConfigModal()" class="w-8 h-8 rounded-xl hover:bg-slate-200/70 text-slate-400 hover:text-slate-800 flex items-center justify-center font-bold text-base transition cursor-pointer" title="ปิดหน้าต่าง">✕</button>
        </div>

        <!-- Body: 2 Columns Side-by-Side -->
        <div class="p-5 sm:p-6 overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-start">
                
                <!-- Left Column: Header Info & Elements Toggle -->
                <div class="space-y-4">
                    <div class="bg-slate-50/80 rounded-2xl p-4 border border-slate-200/80 space-y-3 shadow-2xs">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-200/70">
                            <span class="text-base">🏢</span>
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wide">ข้อมูลหัวบิล & สาขา</h4>
                        </div>

                        <div>
                            <label class="flex items-center justify-between text-[11px] font-bold text-slate-700 mb-1">
                                <span>ชื่อร้าน / หัวกระดาษ</span>
                                <span class="text-[10px] text-slate-400 font-normal">(เว้นว่างได้)</span>
                            </label>
                            <input type="text" id="rcfg_shopName" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:border-amber-500 outline-none transition" placeholder="เช่น หม่าล่าสตรีท">
                        </div>

                        <div>
                            <label class="flex items-center justify-between text-[11px] font-bold text-slate-700 mb-1">
                                <span>ชื่อสาขา / แผนกจัดซื้อ</span>
                                <span class="text-[10px] text-slate-400 font-normal">(เว้นว่างได้)</span>
                            </label>
                            <input type="text" id="rcfg_branchName" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:border-amber-500 outline-none transition" placeholder="เช่น สาขาหลัก (จัดซื้อวัตถุดิบ)">
                        </div>

                        <div>
                            <label class="flex items-center justify-between text-[11px] font-bold text-slate-700 mb-1">
                                <span>เบอร์โทรศัพท์</span>
                                <span class="text-[10px] text-slate-400 font-normal">(เว้นว่างได้)</span>
                            </label>
                            <input type="text" id="rcfg_phone" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-800 focus:border-amber-500 outline-none transition" placeholder="เช่น 081-234-5678">
                        </div>

                        <div>
                            <label class="flex items-center justify-between text-[11px] font-bold text-slate-700 mb-1">
                                <span>ชื่อพนักงาน / ผู้บันทึก</span>
                            </label>
                            <input type="text" id="rcfg_staffName" class="w-full px-3.5 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:border-amber-500 outline-none transition" placeholder="เช่น หม่าล่าสตรีท">
                        </div>
                    </div>

                    <!-- Elements Toggle -->
                    <div class="bg-slate-50/80 rounded-2xl p-4 border border-slate-200/80 space-y-3 shadow-2xs">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-200/70">
                            <span class="text-base">⚙️</span>
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wide">องค์ประกอบที่เปิด/ปิดในสลิป</h4>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <label class="flex items-center gap-2.5 p-2.5 bg-white rounded-xl border border-slate-200 hover:border-amber-400 transition cursor-pointer shadow-2xs">
                                <input type="checkbox" id="rcfg_showBlackCorner" checked class="rounded text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-semibold text-slate-700">มุมริบบิ้นสีดำ</span>
                            </label>

                            <label class="flex items-center gap-2.5 p-2.5 bg-white rounded-xl border border-slate-200 hover:border-amber-400 transition cursor-pointer shadow-2xs">
                                <input type="checkbox" id="rcfg_showPrintTime" checked class="rounded text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-semibold text-slate-700">วันเวลาที่พิมพ์</span>
                            </label>

                            <label class="flex items-center gap-2.5 p-2.5 bg-white rounded-xl border border-slate-200 hover:border-amber-400 transition cursor-pointer shadow-2xs">
                                <input type="checkbox" id="rcfg_showStaff" checked class="rounded text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-semibold text-slate-700">แสดงผู้บันทึก</span>
                            </label>

                            <label class="flex items-center gap-2.5 p-2.5 bg-white rounded-xl border border-slate-200 hover:border-amber-400 transition cursor-pointer shadow-2xs">
                                <input type="checkbox" id="rcfg_showDate" checked class="rounded text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-semibold text-slate-700">แสดงวันที่</span>
                            </label>

                            <label class="flex items-center gap-2.5 p-2.5 bg-white rounded-xl border border-slate-200 hover:border-amber-400 transition cursor-pointer shadow-2xs sm:col-span-2">
                                <input type="checkbox" id="rcfg_showPaymentMethod" checked class="rounded text-amber-600 focus:ring-amber-500">
                                <span class="text-xs font-semibold text-slate-700">แสดงวิธีชำระ (เงินสด)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Footer Notes & Terms -->
                <div class="space-y-4">
                    <div class="bg-slate-50/80 rounded-2xl p-4 border border-slate-200/80 space-y-3.5 shadow-2xs">
                        <div class="flex items-center gap-2 pb-2 border-b border-slate-200/70">
                            <span class="text-base">📝</span>
                            <h4 class="text-xs font-black text-slate-800 uppercase tracking-wide">ข้อความท้ายใบเสร็จ & เงื่อนไข</h4>
                        </div>

                        <input type="hidden" id="rcfg_customerCheckText" value="">
                        <input type="hidden" id="rcfg_noteText" value="">
                        <input type="hidden" id="rcfg_thankYouText" value="">

                        <div class="mt-2 p-3 bg-amber-50/70 border border-amber-200/80 rounded-xl flex items-start gap-2.5 text-[11px] text-amber-900 leading-relaxed">
                            <span class="text-amber-600 shrink-0 text-sm">💡</span>
                            <span>การตั้งค่านี้จะถูกบันทึกลงในบราวเซอร์ และนำไปใช้กับการแสดงผลสลิปบิล 5*8 และการสั่งพิมพ์ทันที</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200/80 flex items-center justify-between gap-3 shrink-0">
            <button type="button" onclick="resetReceiptSlipConfig()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-rose-50 hover:border-rose-300 hover:text-rose-700 text-xs font-bold text-slate-600 transition shadow-2xs cursor-pointer">
                <span>↺</span>
                <span>คืนค่าเริ่มต้น</span>
            </button>
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="closeReceiptSlipConfigModal()" class="px-4 py-2 bg-white hover:bg-slate-200 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 transition cursor-pointer">
                    ยกเลิก
                </button>
                <button type="button" onclick="saveReceiptSlipConfig()" class="px-5 py-2 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer flex items-center gap-1.5">
                    <span>💾</span>
                    <span>บันทึกการตั้งค่า</span>
                </button>
            </div>
        </div>

    </div>
</div>

<!-- 6.1 POPUP: ยืนยันการลบรายการใบเสร็จ (Delete Confirmation Modal) -->
<div
    id="delete-receipt-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/65 backdrop-blur-xs p-4 overflow-y-auto"
    onclick="if(event.target === this) closeDeleteReceiptModal()"
>
    <div class="bg-white rounded-[24px] max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-150">
        <!-- Modal Header -->
        <div class="px-5 py-4 bg-gradient-to-r from-[#4A0408] via-[#8B0912] to-[#4A0408] text-white flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center text-white shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-sm font-bold leading-snug">ยืนยันการลบรายการใบเสร็จ</h3>
                    <p class="text-[11px] text-rose-100/80 font-normal">การดำเนินการนี้ไม่สามารถยกเลิกย้อนหลังได้</p>
                </div>
            </div>
            <button
                type="button"
                onclick="closeDeleteReceiptModal()"
                class="w-7 h-7 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center shrink-0 cursor-pointer transition"
            >
                <svg class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-5 space-y-4">
            <!-- Receipt Info Card -->
            <div class="p-3.5 rounded-2xl bg-rose-50/70 border border-rose-200/80 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">เลขที่ใบเสร็จ:</span>
                    <strong id="delete-modal-receipt-no" class="font-mono font-bold text-slate-800"></strong>
                </div>
                <div class="flex items-center justify-between text-xs border-t border-rose-200/50 pt-2">
                    <span class="text-slate-500 font-medium">ร้านค้า / ซัพพลายเออร์:</span>
                    <strong id="delete-modal-vendor-name" class="font-bold text-slate-800"></strong>
                </div>
                <div class="flex items-center justify-between text-xs border-t border-rose-200/50 pt-2">
                    <span class="text-slate-500 font-medium">ยอดเงินรวม:</span>
                    <strong id="delete-modal-total-amount" class="text-sm font-black text-[#E50914]"></strong>
                </div>
            </div>

            <!-- Warning message -->
            <div class="flex items-start gap-2.5 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
                <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="leading-relaxed">
                    คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้? เมื่อลบแล้วข้อมูลสินค้าและประวัติราคาที่บันทึกจากบิลนี้จะถูกลบออกจากระบบ
                </p>
            </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
            <button
                type="button"
                onclick="closeDeleteReceiptModal()"
                class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold cursor-pointer transition shadow-2xs"
            >
                ยกเลิก
            </button>
            <button
                type="button"
                id="btn-confirm-delete-receipt"
                onclick="confirmDeleteReceipt()"
                class="px-4 py-2 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-bold shadow-md shadow-rose-900/20 inline-flex items-center gap-1.5 cursor-pointer transition"
            >
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>ยืนยันลบรายการ</span>
            </button>
        </div>
    </div>
</div>

<!-- Hidden Iframe for Receipt Slip Printing -->
<iframe id="receiptSlipPrintFrame" style="position:fixed;right:0;bottom:0;width:0;height:0;border:0;opacity:0;pointer-events:none;" src="about:blank"></iframe>

<!-- 7. BACKWARD-COMPATIBLE RECEIPT IMAGE VIEWER MODAL -->
<div
    id="receipt-image-viewer-modal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 backdrop-blur-xs p-4"
    onclick="if(event.target === this) closeReceiptImageViewer()"
>
    <div class="bg-white rounded-[24px] max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden shadow-2xl border border-slate-200">
        <div class="px-5 py-3.5 bg-[#0F172A] text-white flex items-center justify-between">
            <div>
                <h4 id="viewer-modal-title" class="text-sm font-bold">ภาพใบเสร็จ</h4>
                <p id="viewer-modal-subtitle" class="text-[11px] text-slate-300"></p>
            </div>
            <div class="flex items-center gap-2">
                <a
                    id="viewer-modal-download"
                    href="#"
                    onclick="downloadReceiptImage(event, this.href, (document.getElementById('viewer-modal-title')?.textContent || 'receipt').replace(/[^a-zA-Z0-9_-]/g, '_') + '.jpg')"
                    class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold text-white inline-flex items-center gap-1.5 shadow-xs transition cursor-pointer"
                    title="ดาวน์โหลดรูปภาพเป็น JPG"
                >
                    <svg class="w-3.5 h-3.5 stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>โหลด JPG</span>
                </a>
                <a
                    id="viewer-modal-open-tab"
                    href="#"
                    target="_blank"
                    rel="noopener"
                    class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-semibold text-white inline-flex items-center gap-1 transition"
                >
                    <span>เปิดแท็บใหม่ ↗</span>
                </a>
                <button
                    type="button"
                    onclick="closeReceiptImageViewer()"
                    class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/25 text-white flex items-center justify-center cursor-pointer transition"
                >
                    ✕
                </button>
            </div>
        </div>
        <div class="p-4 bg-slate-900 flex-1 overflow-auto flex items-center justify-center">
            <img id="viewer-modal-img" src="" alt="Receipt Image" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-lg">
        </div>
    </div>
</div>

@push('scripts')
<script>
    const rawMaterialsCatalog = @json($rawMaterialsMap);
    let receiptsMap = @json($receiptsMap);
    let receiptPhotos = [];
    let isScanOrSaveBusy = false;

    // Active Slip Modal State
    let currentSlipReceipt = null;
    let currentSlipModalView = 'slip'; // 'slip' or 'image'
    let currentScannedImageIndex = 0;

    // Default 5*8 Slip Configuration (ตามแบบ Image 2)
    const DEFAULT_RECEIPT_SLIP_CONFIG = {
        shopName: 'หม่าล่าสตรีท',
        branchName: 'สาขาหลัก (จัดซื้อวัตถุดิบ)',
        phone: '',
        staffName: 'หม่าล่าสตรีท',
        showBlackCorner: true,
        showPrintTime: true,
        showStaff: true,
        showDate: true,
        showPaymentMethod: true,
        customerCheckText: '',
        noteText: '',
        thankYouText: ''
    };

    function getReceiptSlipConfig() {
        try {
            const raw = localStorage.getItem('mala_receipt_slip_config');
            if (raw) {
                const parsed = JSON.parse(raw);
                parsed.customerCheckText = '';
                parsed.noteText = '';
                parsed.thankYouText = '';
                return Object.assign({}, DEFAULT_RECEIPT_SLIP_CONFIG, parsed);
            }
        } catch (e) {
            console.error(e);
        }
        return Object.assign({}, DEFAULT_RECEIPT_SLIP_CONFIG);
    }

    function saveReceiptSlipConfig() {
        const cfg = {
            shopName: (document.getElementById('rcfg_shopName')?.value || '').trim(),
            branchName: (document.getElementById('rcfg_branchName')?.value || '').trim(),
            phone: (document.getElementById('rcfg_phone')?.value || '').trim(),
            staffName: (document.getElementById('rcfg_staffName')?.value || '').trim(),
            showBlackCorner: document.getElementById('rcfg_showBlackCorner')?.checked !== false,
            showPrintTime: document.getElementById('rcfg_showPrintTime')?.checked !== false,
            showStaff: document.getElementById('rcfg_showStaff')?.checked !== false,
            showDate: document.getElementById('rcfg_showDate')?.checked !== false,
            showPaymentMethod: document.getElementById('rcfg_showPaymentMethod')?.checked !== false,
            customerCheckText: (document.getElementById('rcfg_customerCheckText')?.value || '').trim(),
            noteText: (document.getElementById('rcfg_noteText')?.value || '').trim(),
            thankYouText: (document.getElementById('rcfg_thankYouText')?.value || '').trim(),
        };

        try {
            localStorage.setItem('mala_receipt_slip_config', JSON.stringify(cfg));
        } catch (e) {
            console.error(e);
        }

        closeReceiptSlipConfigModal();
        if (currentSlipReceipt) {
            renderReceiptSlipContent(currentSlipReceipt);
        }
        refreshAllInlineReceiptCards();
    }

    function refreshAllInlineReceiptCards() {
        for (const id in receiptsMap) {
            const area = document.getElementById('inlineSlipPrintableArea-' + id);
            if (area) area.innerHTML = buildReceiptSlipHtml(receiptsMap[id]);
        }
    }

    function resetReceiptSlipConfig() {
        if (!confirm('ต้องการคืนค่าเริ่มต้นโครงสร้างใบเสร็จรับเงินหรือไม่?')) return;
        try {
            localStorage.removeItem('mala_receipt_slip_config');
        } catch (e) {}
        loadReceiptSlipConfigForm();
        if (currentSlipReceipt) {
            renderReceiptSlipContent(currentSlipReceipt);
        }
        refreshAllInlineReceiptCards();
    }

    function loadReceiptSlipConfigForm() {
        const cfg = getReceiptSlipConfig();
        const setVal = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.value = (typeof val === 'string') ? val : '';
        };
        const setCheck = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.checked = !!val;
        };

        setVal('rcfg_shopName', cfg.shopName);
        setVal('rcfg_branchName', cfg.branchName);
        setVal('rcfg_phone', cfg.phone);
        setVal('rcfg_staffName', cfg.staffName);
        setVal('rcfg_customerCheckText', cfg.customerCheckText);
        setVal('rcfg_noteText', cfg.noteText);
        setVal('rcfg_thankYouText', cfg.thankYouText);

        setCheck('rcfg_showBlackCorner', cfg.showBlackCorner !== false);
        setCheck('rcfg_showPrintTime', cfg.showPrintTime !== false);
        setCheck('rcfg_showStaff', cfg.showStaff !== false);
        setCheck('rcfg_showDate', cfg.showDate !== false);
        setCheck('rcfg_showPaymentMethod', cfg.showPaymentMethod !== false);
    }

    function openReceiptSlipConfigModal() {
        loadReceiptSlipConfigForm();
        const modal = document.getElementById('receiptSlipConfigModal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeReceiptSlipConfigModal() {
        const modal = document.getElementById('receiptSlipConfigModal');
        if (modal) modal.classList.add('hidden');
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Generate exact 5*8 slip paper HTML
    function buildReceiptSlipHtml(rec, isPrint = false) {
        const cfg = getReceiptSlipConfig();

        let dateStr = rec.purchase_date_thai || rec.purchase_date || '25/10/2569';
        let timeStr = rec.purchase_time || '17:25 น.';

        const shopName = (cfg.shopName && cfg.shopName.trim()) ? cfg.shopName.trim() : 'หม่าล่าสตรีท';
        const branchName = (cfg.branchName && cfg.branchName.trim()) ? cfg.branchName.trim() : 'สาขาหลัก (จัดซื้อวัตถุดิบ)';
        const supplierInfo = rec.vendor_name ? `ร้านค้า: ${rec.vendor_name}` : '';
        const staffName = (cfg.staffName && cfg.staffName.trim()) ? cfg.staffName.trim() : 'หม่าล่าสตรีท';

        const items = rec.items || [];
        const itemsRows = items.map(it => {
            const rawQty = parseFloat(it.quantity || 0);
            const qtyFormatted = 'x' + (Number.isInteger(rawQty) ? rawQty : parseFloat(rawQty.toFixed(2)));
            const rawSubtotal = parseFloat(it.total_price || (it.unit_price * it.quantity) || 0);
            const subtotalFormatted = (Math.abs(rawSubtotal % 1) < 0.001) ? Math.round(rawSubtotal).toLocaleString('en-US') : rawSubtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const rawUnitPrice = parseFloat(it.unit_price || (rawQty > 0 ? (rawSubtotal / rawQty) : rawSubtotal) || 0);
            const unitPriceFormatted = (Math.abs(rawUnitPrice % 1) < 0.001) ? Math.round(rawUnitPrice).toLocaleString('en-US') : rawUnitPrice.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            const sticksLabel = it.total_yield_sticks ? `(${it.total_yield_sticks} ไม้)` : '';
            const fSize = isPrint ? '12.5px' : '11px';
            const fQtyW = isPrint ? '32px' : '26px';
            const fUnitW = isPrint ? '42px' : '36px';
            const fTotalW = isPrint ? '46px' : '40px';
            return `
                <div style="display: flex; justify-content: space-between; align-items: baseline; font-size: ${fSize}; line-height: 1.45; margin-bottom: 2px;">
                    <span style="min-width: ${fQtyW}; font-family: 'Courier New', monospace; font-size: ${fSize}; color: #000; text-align: left; white-space: nowrap; font-weight: bold;">${qtyFormatted}</span>
                    <span style="flex: 1; min-width: 0; padding: 0 4px; color: #000; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(it.item_name)}">
                        ${escapeHtml(it.item_name)}
                        ${sticksLabel ? `<span style="font-size: ${isPrint ? '10px' : '9px'}; color: #555; font-weight: normal; margin-left: 2px;">${sticksLabel}</span>` : ''}
                    </span>
                    <span style="min-width: ${fUnitW}; text-align: right; font-family: 'Courier New', monospace; font-size: ${fSize}; color: #000; white-space: nowrap; padding-right: 6px;">${unitPriceFormatted}</span>
                    <span style="min-width: ${fTotalW}; text-align: right; font-family: 'Courier New', monospace; font-size: ${fSize}; color: #000; white-space: nowrap;">${subtotalFormatted}</span>
                </div>
            `;
        }).join('');

        const totalSticks = items.reduce((sum, it) => sum + (parseInt(it.total_yield_sticks) || 0), 0);
        const totalAmountNum = Math.round(parseFloat(rec.total_amount || 0)).toLocaleString('en-US');

        const hasFooterContent = (cfg.showPrintTime !== false);

        const cornerSize = isPrint ? '44px' : '32px';
        const topMargin = isPrint ? '24px' : '14px';
        const titleSize = isPrint ? '14px' : '12px';
        const shopSize = isPrint ? '16px' : '13.5px';
        const bodySize = isPrint ? '12px' : '10.5px';
        const scrollStyle = isPrint ? 'max-height: none; overflow: visible;' : 'max-height: 200px; overflow-y: auto; padding-right: 2px;';

        return `
            <!-- Black Triangle Corner -->
            ${cfg.showBlackCorner !== false ? `
            <div style="position: absolute; top: 0; right: 0; width: 0; height: 0; border-top: ${cornerSize} solid #000; border-left: ${cornerSize} solid transparent;"></div>
            ` : ''}

            <!-- Shop Info Header -->
            <div style="text-align: center; margin-top: ${topMargin}; line-height: 1.45;">
                ${shopName ? `
                <div style="font-size: ${shopSize}; font-weight: bold; color: #000; letter-spacing: -0.2px;">${escapeHtml(shopName)}</div>
                ` : ''}
                ${branchName ? `
                <div style="font-size: ${bodySize}; color: #111; margin-top: 1px; font-weight: 600;">${escapeHtml(branchName)}</div>
                ` : ''}
                ${supplierInfo ? `<div style="font-size: ${bodySize}; margin-top: 2px; color: #222;">${escapeHtml(supplierInfo)}</div>` : ''}
                ${cfg.phone && cfg.phone.trim() ? `<div style="font-size: ${bodySize}; color: #222;">โทร. ${escapeHtml(cfg.phone.trim())}</div>` : ''}
            </div>

            <!-- Solid Divider Line -->
            <div style="border-top: 1.5px solid #000; margin: 8px 0 6px;"></div>

            <!-- Receipt Title & Document Reference Number -->
            <div style="font-size: ${titleSize}; font-weight: bold; color: #000;">ใบเสร็จรับเงิน</div>
            <div style="font-size: ${bodySize}; font-family: 'Courier New', monospace; color: #111; margin-top: 1px; font-weight: 600;">${escapeHtml(rec.receipt_no || '-')}</div>

            <!-- Dashed Divider Line -->
            <div style="border-top: 1px dashed #555; margin: 6px 0;"></div>

            <!-- Staff & Date -->
            ${cfg.showStaff !== false ? `
            <div style="display: flex; justify-content: space-between; font-size: ${bodySize}; margin-bottom: 2px;">
                <span style="color: #000;">พนักงานจัดซื้อ</span>
                <span style="font-weight: bold; color: #000;">${escapeHtml(staffName)}</span>
            </div>
            ` : ''}
            ${cfg.showDate !== false ? `
            <div style="display: flex; justify-content: space-between; font-size: ${bodySize};">
                <span style="color: #000;">วันที่</span>
                <span style="font-family: 'Courier New', monospace; color: #000; font-size: ${bodySize};">${escapeHtml(dateStr)}</span>
            </div>
            ` : ''}

            <!-- Solid Divider Line -->
            <div style="border-top: 1.5px solid #000; margin: 6px 0 4px;"></div>

            <!-- Items Rows -->
            <div class="receipt-slip-items-scroll" style="margin: 4px 0 6px; ${scrollStyle}">
                ${itemsRows || '<div style="text-align:center;color:#666;padding:8px 0;">ไม่มีรายการสินค้า</div>'}
            </div>

            <!-- Dashed Divider Line -->
            <div style="border-top: 1px dashed #555; margin: 6px 0 4px;"></div>

            <!-- Total Weight & Grand Total -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; font-size: ${bodySize}; margin-top: 3px;">
                <div>
                    <span style="color: #000;">จำนวนรวม</span>
                    <span style="font-family: 'Courier New', monospace; font-weight: bold; margin-left: 4px; color: #000; font-size: ${bodySize};">${totalSticks.toFixed(2)}</span>
                </div>
                <div style="width: 62%; text-align: right;">
                    <div style="display: flex; justify-content: space-between; padding-bottom: 3px; border-bottom: 1.5px solid #000;">
                        <span style="color: #000;">รวมเป็นเงิน</span>
                        <span style="font-family: 'Courier New', monospace; color: #000; font-size: ${bodySize};">${totalAmountNum}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 4px; padding-bottom: 2px; border-bottom: 3px double #000; font-weight: bold;">
                        <span style="color: #000;">รวมทั้งสิ้น</span>
                        <span style="font-family: 'Courier New', monospace; font-size: ${isPrint ? '13px' : '11.5px'}; color: #000;">${totalAmountNum}</span>
                    </div>
                    ${cfg.showPaymentMethod !== false ? `
                    <div style="display: flex; justify-content: space-between; font-size: 10px; color: #222; margin-top: 3px;">
                        <span>วิธีชำระ</span>
                        <span style="font-weight: 600;">เงินสด</span>
                    </div>
                    ` : ''}
                </div>
            </div>

            <!-- ข้อความท้ายใบเสร็จ (ตัดข้อความหมายเหตุและขอบคุณออกตามภาพที่ 2) -->
            ${hasFooterContent ? `
            <div style="border-top: 1px dashed #555; margin: 8px 0 6px;"></div>
            <div style="text-align: center; font-size: 10px; line-height: 1.4; color: #111;">
                ${cfg.showPrintTime !== false ? `
                <div style="margin-top: 2px; font-size: 9px; color: #555; font-family: 'Courier New', monospace;">
                    พิมพ์ : ${escapeHtml(dateStr)} ${escapeHtml(timeStr)}
                </div>
                ` : ''}
            </div>
            ` : ''}
        `;
    }

    function renderReceiptSlipContent(rec) {
        const area = document.getElementById('receiptSlipPrintableArea');
        if (area) {
            area.innerHTML = buildReceiptSlipHtml(rec);
        }
        const badge = document.getElementById('receiptDocBadge');
        if (badge) {
            badge.textContent = rec.receipt_no ? `เลขที่: ${rec.receipt_no}` : '';
        }
    }

    // Render scanned images inside the modal (ภาพที่แสกนบินจะอยู่ข้างใน)
    function renderReceiptScannedImages(rec, selectedIndex = 0) {
        const images = rec.images || [];
        const thumbStrip = document.getElementById('receiptImagesThumbStrip');
        const imgEl = document.getElementById('receiptModalScannedImg');
        const capEl = document.getElementById('scannedImgCaption');
        const tabEl = document.getElementById('scannedImgOpenTab');
        const tabLabel = document.getElementById('tabBtnImageLabel');

        if (tabLabel) {
            tabLabel.textContent = images.length > 0 ? `ภาพที่สแกน (${images.length})` : 'ภาพที่สแกนบิล';
        }

        if (images.length === 0) {
            if (thumbStrip) {
                thumbStrip.innerHTML = '';
                thumbStrip.classList.add('hidden');
            }
            if (imgEl) {
                imgEl.src = '';
                imgEl.classList.add('hidden');
            }
            if (capEl) capEl.textContent = 'ไม่มีไฟล์รูปภาพที่สแกนสำหรับใบเสร็จนี้';
            if (tabEl) tabEl.classList.add('hidden');
            return;
        }

        currentScannedImageIndex = Math.min(Math.max(0, selectedIndex), images.length - 1);
        const curImg = images[currentScannedImageIndex];

        if (imgEl) {
            imgEl.src = curImg;
            imgEl.classList.remove('hidden');
        }
        if (capEl) {
            capEl.textContent = `รูปที่ ${currentScannedImageIndex + 1} จากทั้งหมด ${images.length} รูป (${rec.receipt_no || '-'})`;
        }
        if (tabEl) {
            tabEl.href = curImg;
            tabEl.classList.remove('hidden');
        }

        if (thumbStrip) {
            if (images.length > 1) {
                thumbStrip.innerHTML = images.map((img, idx) => `
                    <button
                        type="button"
                        onclick="selectScannedImage(${idx})"
                        class="px-2.5 py-1 rounded-lg text-xs font-bold transition cursor-pointer flex items-center gap-1.5 ${idx === currentScannedImageIndex ? 'bg-amber-600 text-white shadow-xs' : 'bg-slate-800 text-slate-300 hover:bg-slate-700'}"
                    >
                        <span>📷 รูปที่ ${idx + 1}</span>
                    </button>
                `).join('');
                thumbStrip.classList.remove('hidden');
            } else {
                thumbStrip.innerHTML = '';
                thumbStrip.classList.add('hidden');
            }
        }
    }

    function selectScannedImage(idx) {
        if (!currentSlipReceipt) return;
        renderReceiptScannedImages(currentSlipReceipt, idx);
    }

    function switchReceiptModalView(view) {
        currentSlipModalView = view;
        const slipSection = document.getElementById('receiptSlipSection');
        const imgSection = document.getElementById('receiptImageSection');
        const tabSlip = document.getElementById('tabBtnSlip');
        const tabImage = document.getElementById('tabBtnImage');
        const actionBtnText = document.getElementById('btnToggleSlipOrImageText');

        if (view === 'image') {
            if (slipSection) slipSection.classList.add('hidden');
            if (imgSection) imgSection.classList.remove('hidden');
            if (tabSlip) {
                tabSlip.className = 'px-3 py-1 rounded-lg font-bold text-[11.5px] text-slate-600 hover:text-slate-900 transition flex items-center gap-1.5 cursor-pointer';
            }
            if (tabImage) {
                tabImage.className = 'px-3 py-1 rounded-lg font-bold text-[11.5px] transition flex items-center gap-1.5 bg-amber-600 text-white shadow-2xs cursor-pointer';
            }
            if (actionBtnText) {
                actionBtnText.textContent = 'ดูสลิปบิล (5*8)';
            }
        } else {
            if (slipSection) slipSection.classList.remove('hidden');
            if (imgSection) imgSection.classList.add('hidden');
            if (tabSlip) {
                tabSlip.className = 'px-3 py-1 rounded-lg font-bold text-[11.5px] transition flex items-center gap-1.5 bg-amber-600 text-white shadow-2xs cursor-pointer';
            }
            if (tabImage) {
                tabImage.className = 'px-3 py-1 rounded-lg font-bold text-[11.5px] text-slate-600 hover:text-slate-900 transition flex items-center gap-1.5 cursor-pointer';
            }
            if (actionBtnText) {
                actionBtnText.textContent = 'เปิดดูบิล';
            }
        }
    }

    function executeReceiptSlipPrintFor(receiptId) {
        const rec = receiptsMap[receiptId];
        if (!rec) return;
        currentSlipReceipt = rec;
        executeReceiptSlipPrint();
    }

    function switchInlineReceiptCardTab(receiptId, view) {
        const slipView = document.getElementById('inlineSlipView-' + receiptId);
        const imgView = document.getElementById('inlineImageView-' + receiptId);
        const tabSlip = document.getElementById('tabBtnSlip-' + receiptId);
        const tabImg = document.getElementById('tabBtnImage-' + receiptId);

        if (view === 'image') {
            if (slipView) slipView.classList.add('hidden');
            if (imgView) imgView.classList.remove('hidden');
            if (tabSlip) {
                tabSlip.className = 'px-2.5 py-0.5 rounded-lg font-bold text-[11px] text-slate-600 hover:text-slate-900 transition flex items-center gap-1 cursor-pointer';
            }
            if (tabImg) {
                tabImg.className = 'px-2.5 py-0.5 rounded-lg font-bold text-[11px] transition flex items-center gap-1 bg-amber-600 text-white shadow-2xs cursor-pointer';
            }
        } else {
            if (slipView) slipView.classList.remove('hidden');
            if (imgView) imgView.classList.add('hidden');
            if (tabSlip) {
                tabSlip.className = 'px-2.5 py-0.5 rounded-lg font-bold text-[11px] transition flex items-center gap-1 bg-amber-600 text-white shadow-2xs cursor-pointer';
            }
            if (tabImg) {
                tabImg.className = 'px-2.5 py-0.5 rounded-lg font-bold text-[11px] text-slate-600 hover:text-slate-900 transition flex items-center gap-1 cursor-pointer';
            }
        }
    }

    function setInlineCardImage(receiptId, imgSrc, imgIndex) {
        const imgEl = document.getElementById('inlineImgDisplay-' + receiptId);
        const downloadEl = document.getElementById('inlineImgDownload-' + receiptId);
        if (imgEl) imgEl.src = imgSrc;
        if (downloadEl) downloadEl.href = imgSrc;

        const btns = document.querySelectorAll('.inline-img-btn-' + receiptId);
        btns.forEach((btn, idx) => {
            if (idx + 1 === imgIndex) {
                btn.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-amber-600 text-white inline-img-btn-' + receiptId + ' cursor-pointer';
            } else {
                btn.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 hover:bg-slate-700 inline-img-btn-' + receiptId + ' cursor-pointer';
            }
        });
        switchInlineReceiptCardTab(receiptId, 'image');
    }

    // Toggle button in bottom bar: switches between slip view and scanned images view
    function toggleReceiptModalView() {
        if (currentSlipModalView === 'slip') {
            switchReceiptModalView('image');
        } else {
            switchReceiptModalView('slip');
        }
    }

    // -------------------------------------------------------------
    // Receipt Deletion Modal & Real Handler (ลบรายการใบเสร็จ)
    // -------------------------------------------------------------
    let pendingDeleteReceiptId = null;

    function openDeleteReceiptModal(id, receiptNo, vendorName, totalAmount) {
        pendingDeleteReceiptId = id;
        const modal = document.getElementById('delete-receipt-modal');
        if (!modal) return;

        document.getElementById('delete-modal-receipt-no').textContent = receiptNo || `#${id}`;
        document.getElementById('delete-modal-vendor-name').textContent = vendorName || 'ไม่ระบุ';
        document.getElementById('delete-modal-total-amount').textContent = `฿${totalAmount}`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteReceiptModal() {
        const modal = document.getElementById('delete-receipt-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        pendingDeleteReceiptId = null;
    }

    async function confirmDeleteReceipt() {
        if (!pendingDeleteReceiptId) return;
        const btn = document.getElementById('btn-confirm-delete-receipt');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<span class="inline-block animate-spin mr-1">↻</span> <span>กำลังลบ...</span>`;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        try {
            const res = await fetch(`/admin/receipts/${pendingDeleteReceiptId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await res.json();
            if (data.success) {
                const card = document.getElementById(`receipt-card-${pendingDeleteReceiptId}`);
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        card.remove();
                        window.location.reload();
                    }, 300);
                } else {
                    window.location.reload();
                }
            } else {
                alert(data.message || 'เกิดข้อผิดพลาดในการลบรายการ');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>ยืนยันลบรายการ</span>
                    `;
                }
            }
        } catch (err) {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>ยืนยันลบรายการ</span>
                `;
            }
        }
    }

    function openReceiptSlipModal(receiptId, initialView = 'slip', imageIndex = 0) {
        const rec = receiptsMap[receiptId];
        if (!rec) {
            alert('ไม่พบข้อมูลใบเสร็จนี้');
            return;
        }
        currentSlipReceipt = rec;

        renderReceiptSlipContent(rec);
        renderReceiptScannedImages(rec, imageIndex);
        switchReceiptModalView(initialView === 'image' ? 'image' : 'slip');

        const modal = document.getElementById('receiptSlipModal5x8');
        if (modal) modal.classList.remove('hidden');
    }

    function closeReceiptSlipModal() {
        const modal = document.getElementById('receiptSlipModal5x8');
        if (modal) modal.classList.add('hidden');
        currentSlipReceipt = null;
    }

    function buildReceiptSlipPrintHtml(rec) {
        const inner = buildReceiptSlipHtml(rec, true);
        const title = escapeHtml(rec.receipt_no || '');
        const style = [
            '@page { size: 5in 8in; margin: 6mm 8mm; }',
            '* { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }',
            'html, body { margin: 0; padding: 0; background: #fff; color: #000; font-family: \'Sarabun\', \'Tahoma\', \'Arial\', sans-serif; font-size: 13px; line-height: 1.4; }',
            '.receipt-container { width: 100%; max-width: 340px; margin: 0 auto; position: relative; padding: 10px; }',
            '.receipt-slip-items-scroll { max-height: none !important; overflow: visible !important; padding-right: 0 !important; }'
        ].join('\n');

        return '<!DOCTYPE html><html lang="th">' +
            '<' + 'head>' +
            '<meta charset="UTF-8">' +
            '<title>ใบเสร็จรับเงิน - ' + title + '</title>' +
            '<' + 'style>' + style + '<' + '/style>' +
            '<' + '/head>' +
            '<body>' +
            '<div class="receipt-container">' + inner + '</div>' +
            '<' + '/body>' +
            '<' + '/html>';
    }

    function executeReceiptSlipPrint() {
        if (!currentSlipReceipt) return;
        const frame = document.getElementById('receiptSlipPrintFrame');
        if (!frame) return;

        const contentHtml = buildReceiptSlipPrintHtml(currentSlipReceipt);
        const doc = frame.contentWindow.document;
        doc.open();
        doc.write(contentHtml);
        doc.close();

        setTimeout(() => {
            try {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            } catch (e) {
                console.error('Receipt print error:', e);
            }
        }, 150);
    }

    // Cross-tab BroadcastChannel for real-time synchronization
    const receiptSyncChannel = typeof BroadcastChannel !== 'undefined'
        ? new BroadcastChannel('mala_receipts_sync_channel')
        : null;

    if (receiptSyncChannel) {
        receiptSyncChannel.onmessage = (ev) => {
            if (ev.data && ev.data.type === 'RECEIPT_SAVED') {
                refreshReceiptsLiveState();
            }
        };
    }

    function openScanModal() {
        const card = document.getElementById('scan-card');
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            card.classList.add('ring-2', 'ring-[#E90032]');
            setTimeout(() => card.classList.remove('ring-2', 'ring-[#E90032]'), 1500);
        }
    }

    function resetScanForm() {
        receiptPhotos = [];
        renderPhotosStrip();
        const recNo = document.getElementById('rec-no');
        if (recNo) recNo.value = '';
        const recVendor = document.getElementById('rec-vendor');
        if (recVendor) recVendor.value = '';
        const recDate = document.getElementById('rec-date');
        if (recDate) recDate.value = "{{ date('Y-m-d') }}";
        const rowsContainer = document.getElementById('items-rows-container');
        if (rowsContainer) {
            rowsContainer.innerHTML = `
                <div id="items-empty-state" class="p-6 text-center border-2 border-dashed border-[#E5EAF1] rounded-2xl bg-[#F8FAFC]/70">
                    <svg class="w-8 h-8 mx-auto mb-1.5 text-slate-300 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <p class="text-[12.5px] font-bold text-[#101828]">ยังไม่มีรายการสินค้า</p>
                    <p class="text-[11px] text-[#66758F] mt-0.5">
                        ถ่ายรูปใบเสร็จเพื่อสแกนอัตโนมัติ หรือกดปุ่ม <strong>"+ เพิ่มรายการ"</strong> ด้านบนเพื่อระบุเอง
                    </p>
                </div>
            `;
        }
        const total = document.getElementById('rec-total');
        if (total) total.value = '0.00';
        const banner = document.getElementById('ocr-status-banner');
        if (banner) banner.classList.add('hidden');
    }

    function hasUnsavedReceiptDraft() {
        const hasPhotos = receiptPhotos.length > 0;
        const hasItems = document.querySelectorAll('.item-row').length > 0;
        const recNo = (document.getElementById('rec-no')?.value || '').trim();
        return hasPhotos || hasItems || recNo.length > 0;
    }

    function requestCloseScanModal() {
        if (isScanOrSaveBusy) return;
        if (hasUnsavedReceiptDraft()) {
            if (!confirm('คุณมีข้อมูลใบเสร็จที่ยังไม่ได้บันทึก ต้องการยกเลิกและล้างข้อมูลนี้หรือไม่?')) {
                return;
            }
        }
        resetScanForm();
    }

    function closeScanModal() {
        resetScanForm();
    }

    // Close modal with Escape key when not busy
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const configModal = document.getElementById('receiptSlipConfigModal');
            if (configModal && !configModal.classList.contains('hidden')) {
                closeReceiptSlipConfigModal();
                return;
            }
            const slipModal = document.getElementById('receiptSlipModal5x8');
            if (slipModal && !slipModal.classList.contains('hidden')) {
                closeReceiptSlipModal();
                return;
            }
            const viewerModal = document.getElementById('receipt-image-viewer-modal');
            if (viewerModal && !viewerModal.classList.contains('hidden')) {
                closeReceiptImageViewer();
                return;
            }
        }
    });

    // Receipt Image Lightbox Viewer
    function openReceiptImageViewer(imgUrl, receiptNo, label) {
        if (!imgUrl) return;
        const modal = document.getElementById('receipt-image-viewer-modal');
        if (!modal) return;
        const imgEl = document.getElementById('viewer-modal-img');
        if (imgEl) imgEl.src = imgUrl;
        const titleEl = document.getElementById('viewer-modal-title');
        if (titleEl) titleEl.textContent = `ใบเสร็จเลขที่: ${receiptNo || '-'}`;
        const subtitleEl = document.getElementById('viewer-modal-subtitle');
        if (subtitleEl) subtitleEl.textContent = label || 'รูปภาพใบเสร็จสแกน';
        const openTabEl = document.getElementById('viewer-modal-open-tab');
        if (openTabEl) openTabEl.href = imgUrl;
        const downloadBtn = document.getElementById('viewer-modal-download');
        if (downloadBtn) {
            downloadBtn.href = imgUrl;
            const safeNo = (receiptNo || 'image').replace(/[^a-zA-Z0-9_-]/g, '_');
            downloadBtn.setAttribute('data-filename', `receipt-${safeNo}.jpg`);
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeReceiptImageViewer() {
        const modal = document.getElementById('receipt-image-viewer-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Download receipt image as JPG
    async function downloadReceiptImage(event, url, filename) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        if (!url || url === '#' || url.startsWith('javascript:')) return;
        const targetFilename = filename || 'receipt.jpg';
        const finalFilename = targetFilename.toLowerCase().endsWith('.jpg') ? targetFilename : (targetFilename + '.jpg');
        try {
            const res = await fetch(url, { mode: 'cors' });
            if (!res.ok) throw new Error('Fetch failed with status ' + res.status);
            const blob = await res.blob();
            const imageBlob = (blob.type && blob.type.startsWith('image/')) ? blob : new Blob([blob], { type: 'image/jpeg' });
            const blobUrl = URL.createObjectURL(imageBlob);
            const a = document.createElement('a');
            a.href = blobUrl;
            a.download = finalFilename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            setTimeout(() => URL.revokeObjectURL(blobUrl), 1500);
        } catch (e) {
            console.warn('Direct blob download error, falling back to anchor trigger:', e);
            const a = document.createElement('a');
            a.href = url;
            a.download = finalFilename;
            a.target = '_blank';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    }

    // Expand/Collapse long receipt items list
    function toggleExtraReceiptItems(receiptId, btnEl, hiddenCount) {
        const items = document.querySelectorAll(`.extra-receipt-item-${receiptId}`);
        if (!items.length) return;
        const isHidden = items[0].classList.contains('hidden');
        items.forEach(el => el.classList.toggle('hidden', !isHidden));
        btnEl.innerHTML = isHidden
            ? '<span>ย่อรายการ ⌃</span>'
            : `<span>ดูเพิ่มเติมอีก ${hiddenCount} รายการ ⌄</span>`;
    }

    // 3 / 4 Cards per Row Grid Toggle (ตรงตามหน้าแคชเชียร์)
    let currentReceiptGridCols = 4;
    function setReceiptGridColumns(cols) {
        currentReceiptGridCols = cols;
        try { localStorage.setItem('mala_receipts_grid_cols', cols); } catch (e) {}

        const grid = document.getElementById('receipts-table-body');
        const btn3 = document.getElementById('btn-grid-3');
        const btn4 = document.getElementById('btn-grid-4');

        if (!grid || !btn3 || !btn4) return;

        if (cols === 3) {
            grid.className = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-4 transition-all duration-200 items-start';
            btn3.className = 'grid-toggle-btn active px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all bg-[#E50914] text-white shadow-2xs cursor-pointer';
            btn4.className = 'grid-toggle-btn px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all text-slate-500 hover:text-slate-800 hover:bg-slate-100 cursor-pointer';
        } else {
            grid.className = 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-4 gap-4 transition-all duration-200 items-start';
            btn4.className = 'grid-toggle-btn active px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all bg-[#E50914] text-white shadow-2xs cursor-pointer';
            btn3.className = 'grid-toggle-btn px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all text-slate-500 hover:text-slate-800 hover:bg-slate-100 cursor-pointer';
        }
    }

    // =========================================================================
    // UNIFIED DATE FILTER & CALENDAR CONTROLLER (ตามภาพที่ 1 และ 2 แถบเดียวกัน)
    // =========================================================================
    const THAI_DAY_NAMES = ['วันอาทิตย์', 'วันจันทร์', 'วันอังคาร', 'วันพุธ', 'วันพฤหัสบดี', 'วันศุกร์', 'วันเสาร์'];
    const THAI_MONTH_NAMES = [
        'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];
    const WESTERN_MONTH_NAMES = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    // System today's date from backend (2026-11-07)
    const SYSTEM_TODAY_STR = "{{ date('Y-m-d') }}";
    
    function parseDateYMD(str) {
        if (!str) return new Date();
        const parts = str.split('-');
        return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
    }

    function formatDateToYMD(d) {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    }

    function formatThaiDateLongStr(d) {
        const dayName = THAI_DAY_NAMES[d.getDay()];
        const day = d.getDate();
        const mName = THAI_MONTH_NAMES[d.getMonth()];
        const y = d.getFullYear() + 543;
        return `${dayName}ที่ ${day} ${mName} ${y}`;
    }

    function formatThaiDateShortStr(d) {
        const day = String(d.getDate()).padStart(2, '0');
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const y = d.getFullYear() + 543;
        return `${day}/${m}/${y}`;
    }

    let dateFilterState = {
        type: 'all', // 'today', '3days', '7days', '15days', '30days', 'this_month', 'all', 'custom'
        startDate: null, // 'YYYY-MM-DD'
        endDate: null,   // 'YYYY-MM-DD'
        calendarMode: 'single', // 'single' or 'range'
        calYear: 2026,
        calMonth: 10, // November (0-indexed)
        rangeStart: null,
        rangeEnd: null
    };

    function initDateFilterBar() {
        const todayObj = parseDateYMD(SYSTEM_TODAY_STR);
        dateFilterState.calYear = todayObj.getFullYear();
        dateFilterState.calMonth = todayObj.getMonth();
        
        // Start with 'ทั้งหมด' active by default so all saved receipts are immediately visible
        setDateFilterPill('all');

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const container = document.getElementById('date-picker-dropdown-container');
            const dropdown = document.getElementById('date-picker-dropdown');
            if (container && dropdown && !dropdown.classList.contains('hidden')) {
                if (!container.contains(e.target)) {
                    closeDatePickerDropdown();
                }
            }
        });
    }

    function setDateFilterPill(type) {
        dateFilterState.type = type;
        const todayObj = parseDateYMD(SYSTEM_TODAY_STR);

        if (type === 'today') {
            dateFilterState.startDate = SYSTEM_TODAY_STR;
            dateFilterState.endDate = SYSTEM_TODAY_STR;
        } else if (type === '3days') {
            const startObj = new Date(todayObj);
            startObj.setDate(startObj.getDate() - 2);
            dateFilterState.startDate = formatDateToYMD(startObj);
            dateFilterState.endDate = SYSTEM_TODAY_STR;
        } else if (type === '7days') {
            const startObj = new Date(todayObj);
            startObj.setDate(startObj.getDate() - 6);
            dateFilterState.startDate = formatDateToYMD(startObj);
            dateFilterState.endDate = SYSTEM_TODAY_STR;
        } else if (type === '15days') {
            const startObj = new Date(todayObj);
            startObj.setDate(startObj.getDate() - 14);
            dateFilterState.startDate = formatDateToYMD(startObj);
            dateFilterState.endDate = SYSTEM_TODAY_STR;
        } else if (type === '30days') {
            const startObj = new Date(todayObj);
            startObj.setDate(startObj.getDate() - 29);
            dateFilterState.startDate = formatDateToYMD(startObj);
            dateFilterState.endDate = SYSTEM_TODAY_STR;
        } else if (type === 'this_month') {
            const firstDay = new Date(todayObj.getFullYear(), todayObj.getMonth(), 1);
            const lastDay = new Date(todayObj.getFullYear(), todayObj.getMonth() + 1, 0);
            dateFilterState.startDate = formatDateToYMD(firstDay);
            dateFilterState.endDate = formatDateToYMD(lastDay);
        } else if (type === 'all') {
            dateFilterState.startDate = null;
            dateFilterState.endDate = null;
        }

        updateDateFilterUI();
    }

    function shiftDateFilter(direction) {
        // direction: -1 (previous) or 1 (next)
        if (dateFilterState.type === 'all') {
            setDateFilterPill('today');
            shiftDateFilter(direction);
            return;
        }

        if (dateFilterState.type === 'today' || (dateFilterState.type === 'custom' && dateFilterState.startDate === dateFilterState.endDate)) {
            const cur = parseDateYMD(dateFilterState.startDate);
            cur.setDate(cur.getDate() + direction);
            const ymd = formatDateToYMD(cur);
            dateFilterState.startDate = ymd;
            dateFilterState.endDate = ymd;
            dateFilterState.type = (ymd === SYSTEM_TODAY_STR) ? 'today' : 'custom';
        } else {
            const s = parseDateYMD(dateFilterState.startDate);
            const e = parseDateYMD(dateFilterState.endDate);
            let diffDays = Math.round((e - s) / (1000 * 60 * 60 * 24)) + 1;
            if (diffDays <= 0) diffDays = 1;

            if (dateFilterState.type === 'this_month') {
                s.setMonth(s.getMonth() + direction);
                const firstDay = new Date(s.getFullYear(), s.getMonth(), 1);
                const lastDay = new Date(s.getFullYear(), s.getMonth() + 1, 0);
                dateFilterState.startDate = formatDateToYMD(firstDay);
                dateFilterState.endDate = formatDateToYMD(lastDay);
            } else {
                s.setDate(s.getDate() + (direction * diffDays));
                e.setDate(e.getDate() + (direction * diffDays));
                dateFilterState.startDate = formatDateToYMD(s);
                dateFilterState.endDate = formatDateToYMD(e);
                dateFilterState.type = 'custom';
            }
        }

        updateDateFilterUI();
    }

    function toggleDatePickerDropdown(e) {
        if (e) e.stopPropagation();
        const dropdown = document.getElementById('date-picker-dropdown');
        const arrow = document.getElementById('date-picker-arrow-icon');
        if (!dropdown) return;
        const isHidden = dropdown.classList.contains('hidden');
        if (isHidden) {
            dropdown.classList.remove('hidden');
            if (arrow) arrow.classList.add('rotate-180');
            renderCalendarDays();
        } else {
            closeDatePickerDropdown();
        }
    }

    function closeDatePickerDropdown() {
        const dropdown = document.getElementById('date-picker-dropdown');
        const arrow = document.getElementById('date-picker-arrow-icon');
        if (dropdown) dropdown.classList.add('hidden');
        if (arrow) arrow.classList.remove('rotate-180');
    }

    function setCalendarMode(mode) {
        dateFilterState.calendarMode = mode;
        dateFilterState.rangeStart = null;
        dateFilterState.rangeEnd = null;

        const singleBtn = document.getElementById('tab-calendar-single');
        const rangeBtn = document.getElementById('tab-calendar-range');
        if (mode === 'single') {
            if (singleBtn) {
                singleBtn.className = 'py-1.5 rounded-lg transition-all text-slate-900 bg-white shadow-2xs cursor-pointer';
            }
            if (rangeBtn) {
                rangeBtn.className = 'py-1.5 rounded-lg transition-all text-slate-500 hover:text-slate-900 cursor-pointer';
            }
        } else {
            if (rangeBtn) {
                rangeBtn.className = 'py-1.5 rounded-lg transition-all text-slate-900 bg-white shadow-2xs cursor-pointer';
            }
            if (singleBtn) {
                singleBtn.className = 'py-1.5 rounded-lg transition-all text-slate-500 hover:text-slate-900 cursor-pointer';
            }
        }
        renderCalendarDays();
    }

    function navigateCalendarMonth(step) {
        dateFilterState.calMonth += step;
        if (dateFilterState.calMonth < 0) {
            dateFilterState.calMonth = 11;
            dateFilterState.calYear--;
        } else if (dateFilterState.calMonth > 11) {
            dateFilterState.calMonth = 0;
            dateFilterState.calYear++;
        }
        renderCalendarDays();
    }

    function renderCalendarDays() {
        const label = document.getElementById('calendar-month-year-label');
        if (label) {
            const wMonth = WESTERN_MONTH_NAMES[dateFilterState.calMonth];
            const thMonth = THAI_MONTH_NAMES[dateFilterState.calMonth];
            const thYear = dateFilterState.calYear + 543;
            label.textContent = `${wMonth} ${dateFilterState.calYear} (${thMonth} ${thYear})`;
        }

        const grid = document.getElementById('calendar-days-grid');
        if (!grid) return;
        grid.innerHTML = '';

        const year = dateFilterState.calYear;
        const month = dateFilterState.calMonth;
        const firstDayOfWeek = new Date(year, month, 1).getDay(); // 0 = Sun
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        // Leading blanks
        for (let i = 0; i < firstDayOfWeek; i++) {
            const blank = document.createElement('div');
            blank.className = 'h-8';
            grid.appendChild(blank);
        }

        const todayYMD = SYSTEM_TODAY_STR;

        for (let d = 1; d <= daysInMonth; d++) {
            const cellDate = new Date(year, month, d);
            const cellYMD = formatDateToYMD(cellDate);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = d;

            let isSelected = false;
            let inRange = false;

            if (dateFilterState.calendarMode === 'single') {
                if (dateFilterState.startDate === cellYMD && dateFilterState.endDate === cellYMD) {
                    isSelected = true;
                } else if (dateFilterState.type === 'today' && cellYMD === todayYMD) {
                    isSelected = true;
                }
            } else {
                // Range mode
                const rStart = dateFilterState.rangeStart || dateFilterState.startDate;
                const rEnd = dateFilterState.rangeEnd || dateFilterState.endDate;
                if (rStart === cellYMD || rEnd === cellYMD) {
                    isSelected = true;
                } else if (rStart && rEnd && cellYMD > rStart && cellYMD < rEnd) {
                    inRange = true;
                }
            }

            if (isSelected) {
                btn.className = 'w-8 h-8 rounded-full bg-[#2563EB] text-white font-bold flex items-center justify-center shadow-xs mx-auto cursor-pointer transition-all';
            } else if (inRange) {
                btn.className = 'w-full h-8 bg-blue-50 text-blue-900 font-semibold flex items-center justify-center cursor-pointer transition-all';
            } else {
                const isToday = (cellYMD === todayYMD);
                btn.className = `w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center mx-auto cursor-pointer transition-all ${isToday ? 'font-bold text-[#2563EB] ring-1 ring-[#2563EB]/40' : 'text-slate-700'}`;
            }

            btn.onclick = (e) => {
                e.stopPropagation();
                handleCalendarDayClick(cellYMD);
            };
            grid.appendChild(btn);
        }
    }

    function handleCalendarDayClick(ymd) {
        if (dateFilterState.calendarMode === 'single') {
            dateFilterState.type = (ymd === SYSTEM_TODAY_STR) ? 'today' : 'custom';
            dateFilterState.startDate = ymd;
            dateFilterState.endDate = ymd;
            updateDateFilterUI();
            closeDatePickerDropdown();
        } else {
            // Range Mode
            if (!dateFilterState.rangeStart || (dateFilterState.rangeStart && dateFilterState.rangeEnd)) {
                dateFilterState.rangeStart = ymd;
                dateFilterState.rangeEnd = null;
                renderCalendarDays();
            } else {
                if (ymd < dateFilterState.rangeStart) {
                    dateFilterState.rangeEnd = dateFilterState.rangeStart;
                    dateFilterState.rangeStart = ymd;
                } else {
                    dateFilterState.rangeEnd = ymd;
                }
                dateFilterState.type = 'custom';
                dateFilterState.startDate = dateFilterState.rangeStart;
                dateFilterState.endDate = dateFilterState.rangeEnd;
                updateDateFilterUI();
                closeDatePickerDropdown();
            }
        }
    }

    function clearCustomDateFilter() {
        setDateFilterPill('all');
        closeDatePickerDropdown();
    }

    function selectTodayInCalendar() {
        const todayObj = parseDateYMD(SYSTEM_TODAY_STR);
        dateFilterState.calYear = todayObj.getFullYear();
        dateFilterState.calMonth = todayObj.getMonth();
        setDateFilterPill('today');
        closeDatePickerDropdown();
    }

    function updateDateFilterUI() {
        const pills = ['today', '3days', '7days', '15days', '30days', 'this_month', 'all'];
        pills.forEach(p => {
            const btn = document.getElementById(`pill-${p}`);
            if (btn) {
                if (dateFilterState.type === p) {
                    btn.className = 'date-filter-pill active px-3 py-1.5 rounded-xl text-xs font-bold border border-[#0F172A] bg-[#0F172A] text-white transition-all cursor-pointer shadow-xs';
                } else {
                    btn.className = 'date-filter-pill px-3 py-1.5 rounded-xl text-xs font-semibold border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 transition-all cursor-pointer shadow-2xs';
                }
            }
        });

        const mainTitle = document.getElementById('date-bar-main-title');
        const subTitle = document.getElementById('date-bar-sub-title');
        const activeLabel = document.getElementById('date-picker-active-label');

        if (dateFilterState.type === 'today') {
            const d = parseDateYMD(dateFilterState.startDate);
            if (mainTitle) mainTitle.textContent = formatThaiDateLongStr(d);
            if (subTitle) subTitle.textContent = 'สรุปรายการรับซื้อประจำวัน';
            if (activeLabel) activeLabel.textContent = 'วันนี้';
        } else if (dateFilterState.type === '3days') {
            const d = parseDateYMD(dateFilterState.endDate);
            if (mainTitle) mainTitle.textContent = formatThaiDateLongStr(d);
            if (subTitle) subTitle.textContent = 'สรุปรายการรับซื้อ 3 วันล่าสุด';
            if (activeLabel) activeLabel.textContent = '3 วันล่าสุด';
        } else if (dateFilterState.type === '7days') {
            if (mainTitle) mainTitle.textContent = 'ช่วงเวลา 7 วันล่าสุด';
            if (subTitle) subTitle.textContent = `สรุปรายการรับซื้อ (${formatThaiDateShortStr(parseDateYMD(dateFilterState.startDate))} - ${formatThaiDateShortStr(parseDateYMD(dateFilterState.endDate))})`;
            if (activeLabel) activeLabel.textContent = '7 วันล่าสุด';
        } else if (dateFilterState.type === '15days') {
            if (mainTitle) mainTitle.textContent = 'ช่วงเวลา 15 วันล่าสุด';
            if (subTitle) subTitle.textContent = `สรุปรายการรับซื้อ (${formatThaiDateShortStr(parseDateYMD(dateFilterState.startDate))} - ${formatThaiDateShortStr(parseDateYMD(dateFilterState.endDate))})`;
            if (activeLabel) activeLabel.textContent = '15 วันล่าสุด';
        } else if (dateFilterState.type === '30days') {
            if (mainTitle) mainTitle.textContent = 'ช่วงเวลา 30 วันล่าสุด';
            if (subTitle) subTitle.textContent = `สรุปรายการรับซื้อ (${formatThaiDateShortStr(parseDateYMD(dateFilterState.startDate))} - ${formatThaiDateShortStr(parseDateYMD(dateFilterState.endDate))})`;
            if (activeLabel) activeLabel.textContent = '30 วันล่าสุด';
        } else if (dateFilterState.type === 'this_month') {
            const d = parseDateYMD(dateFilterState.startDate);
            const mName = THAI_MONTH_NAMES[d.getMonth()];
            const y = d.getFullYear() + 543;
            if (mainTitle) mainTitle.textContent = `ประจำเดือน ${mName} ${y}`;
            if (subTitle) subTitle.textContent = 'สรุปรายการรับซื้อทั้งเดือน';
            if (activeLabel) activeLabel.textContent = 'ทั้งเดือน';
        } else if (dateFilterState.type === 'all') {
            if (mainTitle) mainTitle.textContent = formatThaiDateLongStr(parseDateYMD(SYSTEM_TODAY_STR));
            if (subTitle) subTitle.textContent = 'สรุปรายการรับซื้อประจำวัน';
            if (activeLabel) activeLabel.textContent = 'ทั้งหมด';
        } else if (dateFilterState.type === 'custom') {
            if (dateFilterState.startDate === dateFilterState.endDate) {
                const d = parseDateYMD(dateFilterState.startDate);
                if (mainTitle) mainTitle.textContent = formatThaiDateLongStr(d);
                if (subTitle) subTitle.textContent = 'สรุปรายการรับซื้อประจำวัน';
                if (activeLabel) activeLabel.textContent = formatThaiDateShortStr(d);
            } else {
                const s = parseDateYMD(dateFilterState.startDate);
                const e = parseDateYMD(dateFilterState.endDate);
                if (mainTitle) mainTitle.textContent = `${formatThaiDateShortStr(s)} - ${formatThaiDateShortStr(e)}`;
                if (subTitle) subTitle.textContent = 'สรุปรายการรับซื้อตามช่วงเวลาที่เลือก';
                if (activeLabel) activeLabel.textContent = `${formatThaiDateShortStr(s)} - ${formatThaiDateShortStr(e)}`;
            }
        }

        applyReceiptDateFilter();
        renderCalendarDays();
    }

    function applyReceiptDateFilter() {
        const q = (document.getElementById('receipt-history-search')?.value || '').trim().toLowerCase();
        const rows = document.querySelectorAll('.receipt-history-row');
        let visibleCount = 0;
        let visibleCost = 0;

        rows.forEach(row => {
            const rowDate = row.getAttribute('data-date') || '';
            const rowCreatedDate = row.getAttribute('data-created-date') || '';
            const rowSearch = (row.getAttribute('data-search') || '').toLowerCase();
            const rowAmount = parseFloat(row.getAttribute('data-amount') || 0);

            let matchesDate = true;
            if (dateFilterState.type !== 'all') {
                const checkInRange = (d) => {
                    if (!d) return false;
                    if (dateFilterState.startDate && d < dateFilterState.startDate) return false;
                    if (dateFilterState.endDate && d > dateFilterState.endDate) return false;
                    return true;
                };

                const dateMatches = checkInRange(rowDate);
                const createdMatches = checkInRange(rowCreatedDate);

                if (!dateMatches && !createdMatches) {
                    matchesDate = false;
                }
            }

            let matchesSearch = true;
            if (q.length > 0) {
                matchesSearch = rowSearch.includes(q);
            }

            if (matchesDate && matchesSearch) {
                row.style.display = '';
                visibleCount++;
                visibleCost += rowAmount;
            } else {
                row.style.display = 'none';
            }
        });

        const totalBadge = document.getElementById('receipts-total-badge');
        if (totalBadge) {
            totalBadge.textContent = `รวม ${visibleCount} บิล`;
        }

        let emptyEl = document.getElementById('receipts-filtered-empty');
        const tableBody = document.getElementById('receipts-table-body');
        if (visibleCount === 0 && tableBody) {
            if (!emptyEl) {
                emptyEl = document.createElement('div');
                emptyEl.id = 'receipts-filtered-empty';
                emptyEl.className = 'col-span-full py-12 text-center text-[#66758F] text-xs bg-slate-50/70 rounded-2xl border border-dashed border-slate-200 my-2';
                tableBody.appendChild(emptyEl);
            }
            emptyEl.innerHTML = `
                <svg class="w-8 h-8 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <p class="font-bold text-slate-700">ไม่พบรายการใบเสร็จในช่วงเวลาที่เลือก</p>
                <p class="text-[11px] text-slate-400 mt-0.5">ลองเลือกช่วงเวลาอื่น เช่น "ทั้งหมด" หรือ "30 วันล่าสุด"</p>
                <button type="button" onclick="setDateFilterPill('all')" class="mt-2.5 px-3.5 py-1.5 rounded-xl bg-[#0F172A] hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition cursor-pointer">ดูรายการใบเสร็จทั้งหมด</button>
            `;
            emptyEl.style.display = '';
        } else if (emptyEl) {
            emptyEl.style.display = 'none';
        }

        const kpiToday = document.getElementById('kpi-today-cost');
        if (kpiToday) {
            if (dateFilterState.type === 'today') {
                kpiToday.textContent = '฿' + visibleCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else if (dateFilterState.type === 'all') {
                const defaultCost = {{ (float) $todayCost }};
                const displayVal = defaultCost > 0 ? defaultCost : visibleCost;
                kpiToday.textContent = '฿' + displayVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                kpiToday.textContent = '฿' + visibleCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        }

        const kpiMonth = document.getElementById('kpi-month-cost');
        if (kpiMonth && dateFilterState.type === 'this_month') {
            kpiMonth.textContent = '฿' + visibleCost.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    // Filter Receipt History Table with Search
    function filterReceiptHistoryRows() {
        applyReceiptDateFilter();
    }

    // Toggle API Key Configuration Drawer
    function toggleApiKeyDrawer() {
        const drawer = document.getElementById('gemini-key-drawer');
        drawer.classList.toggle('hidden');
        if (!drawer.classList.contains('hidden')) {
            document.getElementById('gemini-key-input').focus();
        }
    }

    // Save Gemini API Key via AJAX
    async function saveGeminiApiKey() {
        const input = document.getElementById('gemini-key-input');
        const key = input.value.trim();
        if (!key) {
            alert('กรุณากรอก Gemini API Key');
            return;
        }

        const btn = document.getElementById('btn-save-key');
        btn.disabled = true;
        btn.innerText = 'กำลังบันทึก...';

        try {
            const res = await fetch("{{ route('admin.receipts.save_gemini_key') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ gemini_api_key: key })
            });

            const data = await res.json();
            if (data.success) {
                alert(data.message);
                input.value = '';
                document.getElementById('gemini-key-drawer').classList.add('hidden');
                const badge = document.getElementById('ai-key-status-badge');
                badge.className = 'text-[11px] font-semibold px-2.5 py-0.5 rounded-full inline-flex items-center gap-1 bg-[#159A59]/25 text-emerald-300 border border-emerald-500/40';
                badge.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span><span>พร้อมสแกนข้อความไทย</span>';
                document.getElementById('btn-toggle-key-text').innerText = 'แก้ไข API Key';
            } else {
                alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกได้'));
            }
        } catch (err) {
            console.error('Save API key error:', err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        } finally {
            btn.disabled = false;
            btn.innerText = 'บันทึก Key';
        }
    }

    // Smart High-Fidelity Canvas Compression (1600px Max, Quality 0.90, Sharp Contrast)
    function compressImageForOcr(file) {
        return new Promise((resolve, reject) => {
            if (!file.type.startsWith('image/')) {
                return reject(new Error('รองรับเฉพาะไฟล์รูปภาพ JPEG, PNG หรือ WebP เท่านั้น'));
            }
            if (file.size > 15 * 1024 * 1024) {
                return reject(new Error('ขนาดไฟล์รูปภาพต้องไม่เกิน 15 MB'));
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    const maxDim = 1600;

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
                    const ctx = canvas.getContext('2d');

                    ctx.filter = 'contrast(1.15) brightness(1.02)';
                    ctx.drawImage(img, 0, 0, width, height);

                    const base64 = canvas.toDataURL('image/jpeg', 0.90);
                    const sizeKb = Math.round((base64.length * 0.75) / 1024);
                    const originalKb = Math.round(file.size / 1024);

                    resolve({
                        name: file.name,
                        base64: base64,
                        sizeKb: sizeKb,
                        originalKb: originalKb,
                        width: width,
                        height: height
                    });
                };
                img.onerror = () => reject(new Error('ไม่สามารถอ่านไฟล์รูปภาพนี้ได้'));
                img.src = e.target.result;
            };
            reader.onerror = () => reject(new Error('ไม่สามารถอ่านไฟล์รูปภาพนี้ได้'));
            reader.readAsDataURL(file);
        });
    }

    // Handle photo capture from camera or gallery
    async function handlePhotoCapture(event) {
        const files = Array.from(event.target.files || []);
        if (files.length === 0) return;

        const scanBtnText = document.getElementById('btn-scan-text');
        scanBtnText.innerText = '⏳ กำลังบีบอัดและปรับความคมชัดของรูปภาพ...';

        try {
            for (const file of files) {
                const compressed = await compressImageForOcr(file);
                const isDuplicate = receiptPhotos.some(p => p.sizeKb === compressed.sizeKb && p.name === compressed.name);
                if (!isDuplicate) {
                    receiptPhotos.push({
                        id: Date.now() + Math.random().toString(36).substr(2, 9),
                        name: compressed.name,
                        base64: compressed.base64,
                        sizeKb: compressed.sizeKb,
                        originalKb: compressed.originalKb
                    });
                }
            }
            renderPhotosStrip();
        } catch (err) {
            console.error('Error compressing receipt photo:', err);
            alert(err.message || 'เกิดข้อผิดพลาดในการประมวลผลรูปภาพ กรุณาลองใหม่อีกครั้ง');
        } finally {
            event.target.value = '';
            scanBtnText.innerText = '⚡ เริ่มสแกนบิลด้วย AI Vision (เฉลี่ย 2-4 วิ | ไม่เกิน 30 วิ)';
        }
    }

    // Render multi-photo thumbnail strip
    function renderPhotosStrip() {
        const stripContainer = document.getElementById('photos-strip-container');
        const emptyPrompt = document.getElementById('photos-empty-prompt');
        const strip = document.getElementById('photos-strip');
        const countBadge = document.getElementById('photos-count-badge');
        const scanBtn = document.getElementById('btn-run-ocr');

        if (receiptPhotos.length === 0) {
            stripContainer.classList.add('hidden');
            emptyPrompt.classList.remove('hidden');
            scanBtn.disabled = true;
            return;
        }

        stripContainer.classList.remove('hidden');
        emptyPrompt.classList.add('hidden');
        scanBtn.disabled = false;
        countBadge.innerText = `(แนบแล้ว ${receiptPhotos.length} รูป)`;

        strip.innerHTML = '';
        receiptPhotos.forEach((photo, idx) => {
            const card = document.createElement('div');
            card.className = 'relative shrink-0 w-24 rounded-2xl bg-white border border-[#E5EAF1] overflow-hidden shadow-xs group';
            card.innerHTML = `
                <img src="${photo.base64}" alt="Receipt Photo ${idx + 1}" onclick="openReceiptImageViewer('${photo.base64}', 'บิลร่าง', 'รูปที่ ${idx + 1} (${photo.sizeKb} KB)')" class="w-24 h-24 object-cover cursor-zoom-in">
                <div class="p-1 text-center bg-[#F8FAFC] border-t border-[#E5EAF1]">
                    <span class="block text-[10px] font-bold text-[#101828] truncate px-1">รูปที่ ${idx + 1}</span>
                    <span class="block text-[9.5px] text-[#159A59] font-mono font-bold">${photo.sizeKb} KB</span>
                </div>
                <button type="button" onclick="removePhoto('${photo.id}')" 
                        class="absolute top-1 right-1 w-6 h-6 rounded-full bg-[#E90032] text-white flex items-center justify-center text-xs font-bold shadow-md hover:bg-rose-700 transition-colors cursor-pointer" title="ลบรูปนี้">
                    &times;
                </button>
            `;
            strip.appendChild(card);
        });
    }

    function removePhoto(id) {
        receiptPhotos = receiptPhotos.filter(p => p.id !== id);
        renderPhotosStrip();
    }

    // FAST AI VISION SCAN FUNCTION
    async function scanAllReceiptPhotos() {
        if (receiptPhotos.length === 0) {
            alert('กรุณาถ่ายรูปหรือเลือกภาพใบเสร็จอย่างน้อย 1 รูป');
            return;
        }

        isScanOrSaveBusy = true;
        const scanBtn = document.getElementById('btn-run-ocr');
        const scanBtnText = document.getElementById('btn-scan-text');
        scanBtn.disabled = true;

        const statusBanner = document.getElementById('ocr-status-banner');
        statusBanner.classList.add('hidden');

        const startMs = Date.now();
        const timerInterval = setInterval(() => {
            const sec = ((Date.now() - startMs) / 1000).toFixed(1);
            scanBtnText.innerText = `🤖 AI Vision กำลังอ่านบิล... (${sec} วิ / ไม่เกิน 30 วิ)`;
        }, 100);

        try {
            const res = await fetch("{{ route('admin.receipts.scan_ocr') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    images: receiptPhotos.map(p => p.base64),
                    photos_count: receiptPhotos.length
                })
            });

            clearInterval(timerInterval);
            const elapsedSec = ((Date.now() - startMs) / 1000).toFixed(1);
            const data = await res.json();

            if (data.need_api_key) {
                document.getElementById('gemini-key-drawer').classList.remove('hidden');
                document.getElementById('gemini-key-input').focus();

                statusBanner.className = 'p-3.5 rounded-2xl border text-xs flex items-start gap-2.5 bg-amber-50 border-amber-300 text-amber-950 shadow-xs';
                document.getElementById('ocr-status-icon').innerHTML = '<span class="text-lg shrink-0">🔑</span>';
                document.getElementById('ocr-status-title').innerText = 'ต้องการ Gemini API Key เพื่อเปิดใช้งานระบบ AI';
                document.getElementById('ocr-status-desc').innerText = 'กรุณาวาง Gemini API Key ในช่องด้านบน แล้วกดบันทึกเพื่อเริ่มสแกน';
                statusBanner.classList.remove('hidden');
                return;
            }

            if (data.success && data.items && data.items.length > 0) {
                if (data.receipt_no) document.getElementById('rec-no').value = data.receipt_no;
                if (data.vendor_name) document.getElementById('rec-vendor').value = data.vendor_name;
                if (data.purchase_date) document.getElementById('rec-date').value = data.purchase_date;

                const container = document.getElementById('items-rows-container');
                container.innerHTML = '';

                data.items.forEach(item => {
                    renderItemRow(item);
                });

                const ocrTotal = parseFloat(data.total_amount || 0);
                calculateItemRow(null, ocrTotal);

                statusBanner.className = 'p-3.5 rounded-2xl border text-xs flex items-start gap-2.5 bg-emerald-50 border-emerald-300 text-emerald-950 shadow-xs';
                document.getElementById('ocr-status-icon').innerHTML = '<svg class="w-5 h-5 text-[#159A59] stroke-current shrink-0 mt-0.5" fill="none" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
                document.getElementById('ocr-status-title').innerText = `⚡ สแกนสำเร็จด้วย AI Vision! (ตรวจพบ ${data.items.length} รายการ | ใช้เวลา ${elapsedSec} วิ)`;
                document.getElementById('ocr-status-desc').innerText = `อ่านข้อมูลบิล ${data.receipt_no || ''} จากร้าน ${data.vendor_name || 'ซัพพลายเออร์'} และแปลงสูตรไม้เรียบร้อยแล้ว กรุณาตรวจสอบก่อนกดบันทึก`;
                statusBanner.classList.remove('hidden');
            } else {
                statusBanner.className = 'p-3.5 rounded-2xl border text-xs flex items-start gap-2.5 bg-amber-50 border-amber-300 text-amber-950 shadow-xs';
                document.getElementById('ocr-status-icon').innerHTML = '<svg class="w-5 h-5 text-amber-600 stroke-current shrink-0 mt-0.5" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
                document.getElementById('ocr-status-title').innerText = 'ไม่พบข้อความรายการสินค้าที่ชัดเจนในภาพ';
                document.getElementById('ocr-status-desc').innerText = data.message || 'กรุณาถ่ายภาพให้ชัดเจนขึ้น หรือกดปุ่ม "+ เพิ่มรายการ" ด้านล่างเพื่อระบุเองได้ทันที';
                statusBanner.classList.remove('hidden');

                if (data.receipt_no) document.getElementById('rec-no').value = data.receipt_no;
                if (data.vendor_name) document.getElementById('rec-vendor').value = data.vendor_name;
            }
        } catch (err) {
            clearInterval(timerInterval);
            console.error('Scan OCR error:', err);
            statusBanner.className = 'p-3.5 rounded-2xl border text-xs flex items-start gap-2.5 bg-rose-50 border-rose-300 text-rose-950 shadow-xs';
            document.getElementById('ocr-status-icon').innerHTML = '<svg class="w-5 h-5 text-[#E90032] stroke-current shrink-0 mt-0.5" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';
            document.getElementById('ocr-status-title').innerText = 'ระบบสแกนไม่สามารถอ่านภาพได้ในขณะนี้';
            document.getElementById('ocr-status-desc').innerText = 'อาจเกิดจากการเชื่อมต่อขัดข้อง หรือภาพมีขนาดใหญ่เกินไป ท่านสามารถกดปุ่ม "+ เพิ่มรายการ" ด้านล่างเพื่อระบุเองได้ทันที';
            statusBanner.classList.remove('hidden');
        } finally {
            isScanOrSaveBusy = false;
            clearInterval(timerInterval);
            scanBtn.disabled = false;
            scanBtnText.innerText = '⚡ เริ่มสแกนบิลด้วย AI Vision อีกครั้ง (ไม่เกิน 30 วิ)';
        }
    }

    // Helper to find matching raw material by primary name OR any scan_names alias
    function findMatchingRawMaterial(queryName) {
        if (!queryName) return null;
        const val = String(queryName).trim().toLowerCase();
        if (!val) return null;

        // 1. Exact match with primary name
        let matched = rawMaterialsCatalog.find(m => m.name.toLowerCase() === val);
        if (matched) return matched;

        // 2. Exact match with any scan_names alias
        matched = rawMaterialsCatalog.find(m => {
            if (m.scan_names && Array.isArray(m.scan_names)) {
                return m.scan_names.some(sn => {
                    const s = (typeof sn === 'object' && sn !== null ? String(sn.name || '') : String(sn || '')).trim().toLowerCase();
                    return s && s === val;
                });
            }
            return false;
        });
        if (matched) return matched;

        // 3. Substring match (either query contains candidate or vice-versa)
        matched = rawMaterialsCatalog.find(m => {
            if (val.includes(m.name.toLowerCase()) || m.name.toLowerCase().includes(val)) return true;
            if (m.scan_names && Array.isArray(m.scan_names)) {
                return m.scan_names.some(sn => {
                    const s = (typeof sn === 'object' && sn !== null ? String(sn.name || '') : String(sn || '')).trim().toLowerCase();
                    return s && (val.includes(s) || s.includes(val));
                });
            }
            return false;
        });
        return matched || null;
    }

    // Match typed ingredient name with existing RawMaterial catalog to auto-fill Yield & Unit
    function handleIngredientNameChange(inputEl) {
        const val = (inputEl.value || '').trim().toLowerCase();
        const row = inputEl.closest('.item-row');
        if (!row || !val) {
            calculateItemRow(inputEl);
            return;
        }

        const matched = findMatchingRawMaterial(val);
        if (matched) {
            const unitSelect = row.querySelector('.item-unit');
            const yieldInput = row.querySelector('.item-yield');
            const priceInput = row.querySelector('.item-price');
            const matchBadge = row.querySelector('.item-match-badge');

            if (unitSelect && matched.purchase_unit) {
                const baseUnit = matched.purchase_unit.includes('กก') || matched.purchase_unit.includes('กิโล')
                    ? 'กก.'
                    : (matched.purchase_unit.includes('ถุง') ? 'ถุง' : 'แพ็ก');
                unitSelect.value = baseUnit;
            }
            if (yieldInput && matched.yield_sticks_per_unit > 0) {
                yieldInput.value = matched.yield_sticks_per_unit;
            }
            if (priceInput && (parseFloat(priceInput.value) || 0) === 0 && matched.latest_cost_price > 0) {
                priceInput.value = matched.latest_cost_price;
            }
            if (matchBadge) {
                matchBadge.textContent = `✓ ตรงกับ: ${matched.name}`;
                matchBadge.className = 'item-match-badge text-[10px] font-semibold text-[#159A59] bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200';
            }
        }
        calculateItemRow(inputEl);
    }

    // Render single item row into container
    function renderItemRow(item = {}) {
        const container = document.getElementById('items-rows-container');
        const emptyState = document.getElementById('items-empty-state');
        if (emptyState) emptyState.remove();

        const name = item.item_name || '';
        const qty = Math.max(0.01, parseFloat(item.quantity) || 1);
        const unit = item.unit || 'แพ็ก';
        const price = Math.max(0, parseFloat(item.unit_price) || 0);

        // Check if ingredient matches our DB catalog (by primary name or scan_names alias)
        const matched = findMatchingRawMaterial(name);
        const resolvedName = matched ? matched.name : name;
        const yieldSticks = Math.max(0.01, parseFloat(item.yield_sticks_per_unit) || (matched ? matched.yield_sticks_per_unit : 10));
        const totalSticks = item.total_yield_sticks || Math.round(qty * yieldSticks);
        const costStick = item.cost_per_stick || (yieldSticks > 0 ? (price / yieldSticks) : 0);
        const addInv = item.add_to_inventory !== undefined ? item.add_to_inventory : true;

        const rowDiv = document.createElement('div');
        rowDiv.className = 'item-row p-3 bg-white rounded-xl border border-[#CBD5E1] shadow-2xs space-y-2 text-xs';
        rowDiv.innerHTML = `
            <div class="space-y-1.5">
                <div>
                    <div class="flex items-center justify-between mb-0.5">
                        <label class="text-[11px] font-bold text-[#101828]">ชื่อสินค้า / วัตถุดิบ:</label>
                        <span class="item-match-badge text-[9.5px] font-semibold ${matched ? 'text-[#159A59] bg-emerald-50 border-emerald-200' : 'text-[#66758F] bg-slate-100 border-slate-200'} px-2 py-0.2 rounded-full border">
                            ${matched ? '✓ จับคู่สูตรเดิม' : 'กำหนดสูตรใหม่'}
                        </span>
                    </div>
                    <input type="text" list="raw-materials-datalist" class="item-name w-full text-xs px-2.5 py-1.5 bg-[#F8FAFC] focus:bg-white border border-[#CBD5E1] rounded-lg font-bold text-[#101828] focus:outline-none focus:border-[#E90032]" 
                        value="${name}" placeholder="พิมพ์หรือเลือกวัตถุดิบ..." required oninput="handleIngredientNameChange(this)">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10.5px] font-bold text-[#101828] mb-0.5">จำนวนซื้อ:</label>
                        <div class="flex gap-1">
                            <input type="number" step="0.01" min="0.01" class="item-qty w-14 text-xs px-1.5 py-1.5 bg-[#F8FAFC] focus:bg-white border border-[#CBD5E1] rounded-lg font-bold text-center text-[#101828]" 
                                value="${qty}" required oninput="calculateItemRow(this)">
                            <select class="item-unit flex-1 text-xs px-1 py-1.5 bg-[#F8FAFC] focus:bg-white border border-[#CBD5E1] rounded-lg font-semibold text-[#101828]">
                                <option value="แพ็ก" ${unit === 'แพ็ก' ? 'selected' : ''}>แพ็ก</option>
                                <option value="กก." ${unit === 'กก.' ? 'selected' : ''}>กก.</option>
                                <option value="ถุง" ${unit === 'ถุง' ? 'selected' : ''}>ถุง</option>
                                <option value="ชิ้น" ${unit === 'ชิ้น' ? 'selected' : ''}>ชิ้น</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10.5px] font-bold text-[#101828] mb-0.5">ราคาซื้อ/หน่วย (฿):</label>
                        <input type="number" step="0.01" min="0" class="item-price w-full text-xs px-2 py-1.5 bg-[#F8FAFC] focus:bg-white border border-[#CBD5E1] rounded-lg font-bold text-[#E90032]" 
                            value="${price}" required oninput="calculateItemRow(this)">
                    </div>
                </div>
            </div>

            <!-- Yield Formula Output -->
            <div class="p-2 bg-[#FFF4D6]/80 rounded-lg border border-[#FDE2A7] text-[11px] space-y-1.5">
                <div class="flex items-center justify-between gap-1">
                    <span class="font-bold text-[#9A5B00]">⚡ 1 หน่วยแปลงได้:</span>
                    <div class="flex items-center gap-1">
                        <input type="number" step="0.01" min="0.01" class="item-yield w-14 text-xs px-1.5 py-0.5 bg-white border border-amber-300 rounded-md font-bold text-center text-[#101828]" 
                            value="${yieldSticks}" required oninput="calculateItemRow(this)">
                        <span class="text-[#9A5B00] font-bold">ไม้</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-[11px] pt-0.5">
                    <span class="text-[#101828]">รวม: <strong class="item-total-sticks text-[#9A5B00] font-bold">${totalSticks}</strong> ไม้</span>
                    <span class="text-[#E90032] font-bold">ต้นทุน: <strong class="item-cost-stick">฿${costStick.toFixed(2)}</strong>/ไม้</span>
                </div>

                <div class="flex items-center justify-between border-t border-amber-200/60 pt-1">
                    <label class="flex items-center gap-1 text-[10px] text-[#101828] font-semibold cursor-pointer">
                        <input type="checkbox" class="item-add-inv rounded text-[#E90032]" ${addInv ? 'checked' : ''}>
                        <span>นำเข้าสต็อกไม้ทันที</span>
                    </label>
                    <button type="button" onclick="removeRow(this)" class="px-2 py-0.5 rounded bg-white/90 hover:bg-rose-100 text-slate-500 hover:text-[#E90032] font-bold text-xs flex items-center gap-0.5 cursor-pointer" title="ลบรายการนี้">&times; ลบ</button>
                </div>
            </div>
        `;
        container.appendChild(rowDiv);
    }

    function addNewItemRow() {
        renderItemRow({
            item_name: '',
            quantity: 1,
            unit: 'แพ็ก',
            unit_price: 0,
            yield_sticks_per_unit: 10,
            total_yield_sticks: 10,
            cost_per_stick: 0,
            add_to_inventory: true
        });
        calculateItemRow();
    }

    function removeRow(btn) {
        const row = btn.closest('.item-row');
        if (row) {
            row.remove();
            calculateItemRow();
        }

        const remainingRows = document.querySelectorAll('.item-row');
        if (remainingRows.length === 0) {
            const container = document.getElementById('items-rows-container');
            container.innerHTML = `
                <div id="items-empty-state" class="p-6 text-center border-2 border-dashed border-[#E5EAF1] rounded-2xl bg-[#F8FAFC]/70">
                    <svg class="w-8 h-8 mx-auto mb-1.5 text-slate-300 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <p class="text-[12.5px] font-bold text-[#101828]">ยังไม่มีรายการสินค้า</p>
                    <p class="text-[11px] text-[#66758F] mt-0.5">ถ่ายรูปใบเสร็จเพื่อสแกนอัตโนมัติ หรือกดปุ่ม <strong>"+ เพิ่มรายการ"</strong> ด้านบนเพื่อระบุเอง</p>
                </div>
            `;
            document.getElementById('rec-total').value = '0.00';
            document.getElementById('total-mismatch-warning')?.classList.add('hidden');
        }
    }

    // Recalculate yields and grand total safely (preventing division by zero or negative values)
    function calculateItemRow(input, ocrReportedTotal = null) {
        if (input) {
            const row = input.closest('.item-row');
            if (row) {
                const qty = Math.max(0, parseFloat(row.querySelector('.item-qty').value) || 0);
                const price = Math.max(0, parseFloat(row.querySelector('.item-price').value) || 0);
                const yieldPerUnit = Math.max(0.01, parseFloat(row.querySelector('.item-yield').value) || 1);

                const totalSticks = Math.round(qty * yieldPerUnit);
                const costPerStick = yieldPerUnit > 0 ? (price / yieldPerUnit) : 0;

                row.querySelector('.item-total-sticks').innerText = totalSticks;
                row.querySelector('.item-cost-stick').innerText = `฿${costPerStick.toFixed(2)}`;
            }
        }

        let grand = 0;
        document.querySelectorAll('.item-row').forEach(r => {
            const q = Math.max(0, parseFloat(r.querySelector('.item-qty').value) || 0);
            const p = Math.max(0, parseFloat(r.querySelector('.item-price').value) || 0);
            grand += (q * p);
        });

        const totalInput = document.getElementById('rec-total');
        const mismatchWarn = document.getElementById('total-mismatch-warning');

        if (ocrReportedTotal !== null && ocrReportedTotal > 0 && Math.abs(ocrReportedTotal - grand) >= 1) {
            totalInput.value = ocrReportedTotal.toFixed(2);
            if (mismatchWarn) {
                mismatchWarn.textContent = `⚠️ ยอดรวมตามบิล (฿${ocrReportedTotal.toFixed(2)}) ต่างจากผลรวมรายการ (฿${grand.toFixed(2)}) กรุณาตรวจสอบ`;
                mismatchWarn.classList.remove('hidden');
            }
        } else {
            totalInput.value = grand.toFixed(2);
            if (mismatchWarn) mismatchWarn.classList.add('hidden');
        }
    }

    // Real-time refresh of KPI cards & receipt count without page reload
    async function refreshReceiptsLiveState() {
        try {
            const res = await fetch("{{ route('admin.receipts.index') }}?json=1", {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data || !data.success) return;

            const todayEl = document.getElementById('kpi-today-cost');
            if (todayEl) todayEl.textContent = `฿${Number(data.today_cost || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

            const monthEl = document.getElementById('kpi-month-cost');
            if (monthEl) monthEl.textContent = `฿${Number(data.this_month_cost || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

            const matEl = document.getElementById('kpi-materials-count');
            if (matEl) matEl.textContent = `${data.materials_count || 0} รายการ`;

            const badgeEl = document.getElementById('receipts-total-badge');
            if (badgeEl) badgeEl.textContent = `รวม ${data.receipts_total || 0} บิล`;

            if (Array.isArray(data.receipts)) {
                const baseUrl = "{{ url('/') }}";
                data.receipts.forEach(r => {
                    if (Array.isArray(r.images)) {
                        r.images = r.images.map(img => {
                            if (!img) return img;
                            if (img.startsWith('http://') || img.startsWith('https://') || img.startsWith('data:image')) return img;
                            return baseUrl + '/' + img.replace(/^\/+/, '');
                        });
                    }
                    receiptsMap[r.id] = r;
                });
            }
        } catch (err) {
            // Silent background sync
        }
    }

    // Submit Receipt Form
    async function submitReceiptForm(e) {
        e.preventDefault();
        if (isScanOrSaveBusy) return;

        const rows = document.querySelectorAll('.item-row');
        if (rows.length === 0) {
            alert('กรุณาเพิ่มรายการสินค้าอย่างน้อย 1 รายการก่อนบันทึก');
            return;
        }

        isScanOrSaveBusy = true;
        const btn = document.getElementById('btn-save-receipt');
        btn.disabled = true;
        btn.innerHTML = '<span>⏳ กำลังบันทึกข้อมูลและอัปเดตราคาต้นทุน...</span>';

        const items = [];
        rows.forEach(row => {
            items.push({
                item_name: row.querySelector('.item-name').value.trim(),
                quantity: Math.max(0.01, parseFloat(row.querySelector('.item-qty').value) || 1),
                unit: row.querySelector('.item-unit').value,
                unit_price: Math.max(0, parseFloat(row.querySelector('.item-price').value) || 0),
                yield_sticks_per_unit: Math.max(0.01, parseFloat(row.querySelector('.item-yield').value) || 10),
                add_to_inventory: row.querySelector('.item-add-inv').checked
            });
        });

        const payload = {
            receipt_no: document.getElementById('rec-no').value.trim() || ('REC-' + Date.now()),
            vendor_name: document.getElementById('rec-vendor').value.trim(),
            purchase_date: document.getElementById('rec-date').value,
            total_amount: Math.max(0, parseFloat(document.getElementById('rec-total').value) || 0),
            images: receiptPhotos.map(p => p.base64),
            items: items
        };

        try {
            const res = await fetch("{{ route('admin.receipts.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (res.ok && data.success) {
                if (receiptSyncChannel) {
                    receiptSyncChannel.postMessage({ type: 'RECEIPT_SAVED', receipt_id: data.receipt_id });
                }
                receiptPhotos = [];
                closeScanModal();
                window.location.reload();
            } else {
                alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกได้'));
            }
        } catch (err) {
            console.error('Save receipt error:', err);
            alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาตรวจสอบการเชื่อมต่อ');
        } finally {
            isScanOrSaveBusy = false;
            btn.disabled = false;
            btn.innerHTML = `
                <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                </svg>
                <span>บันทึกใบเสร็จ & อัปเดตราคาต้นทุน</span>
            `;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        refreshAllInlineReceiptCards();
        initDateFilterBar();
        try {
            const savedCols = parseInt(localStorage.getItem('mala_receipts_grid_cols')) || 4;
            setReceiptGridColumns(savedCols);
        } catch (e) {}
        setInterval(refreshReceiptsLiveState, 20000);
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                refreshReceiptsLiveState();
            }
        });
    });
</script>
@endpush
@endsection
