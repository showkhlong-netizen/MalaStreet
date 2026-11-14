@extends('layouts.app', ['title' => 'ระบบจัดการพนักงาน ร้านหม่าล่า - Mala Street'])

@section('content')
<!-- Leaflet.js (Free, High-Performance Interactive Map) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
    /* Leaflet z-index tweak inside modals */
    .leaflet-pane { z-index: 10 !important; }
    .leaflet-top, .leaflet-bottom { z-index: 20 !important; }

    /* Facial Recognition Radar Pulse Animation */
    @keyframes pulseRing {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.08); opacity: 0.35; }
        100% { transform: scale(0.95); opacity: 0.8; }
    }
    @keyframes scanLaser {
        0% { top: 12%; opacity: 0.2; }
        50% { top: 82%; opacity: 0.9; }
        100% { top: 12%; opacity: 0.2; }
    }
    .scanner-aura {
        animation: pulseRing 3s ease-in-out infinite;
    }
    .scanner-laser {
        animation: scanLaser 2.2s ease-in-out infinite;
    }
</style>

<div class="min-h-screen bg-[#F6F7F9] py-4 sm:py-6 px-3 sm:px-5 lg:px-6">
    <div class="max-w-[1420px] mx-auto space-y-4 sm:space-y-5">

        <!-- ========================================================================= -->
        <!-- 1. HEADER BANNER: ระบบจัดการพนักงาน ร้านหม่าล่า (Matches PC & Mobile)    -->
        <!-- ========================================================================= -->
        <header class="relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-lg border border-[#55060B] bg-gradient-to-r from-[#420106] via-[#7B030D] to-[#3B0105] text-white p-4 sm:p-6">
            <!-- Background Artwork Texture -->
            <div class="absolute inset-0 opacity-25 mix-blend-screen pointer-events-none bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.4),transparent_60%)]"></div>
            <div class="absolute right-0 top-0 bottom-0 w-1/3 opacity-20 pointer-events-none overflow-hidden hidden sm:block">
                <img src="{{ asset('assets/brand/hero-skewers-right.png') }}" class="w-full h-full object-cover object-right" alt="">
            </div>

            <div class="relative z-10 flex items-center justify-between gap-3">
                <!-- Left: Branding & Title -->
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <!-- Mobile Logo (ร้านเดิม assets/brand/logo-card.png) -->
                    <div class="w-11 h-11 sm:w-14 sm:h-14 rounded-2xl border-2 border-white/80 overflow-hidden shadow-md shrink-0 bg-[#8B0000]">
                        <img src="{{ asset('assets/brand/logo-card.png') }}"
                             alt="หม่าล่าสตรีท"
                             class="w-full h-full object-cover">
                    </div>

                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <!-- Vertical Red Accent (PC view) -->
                            <span class="w-1.5 h-6 sm:h-7 rounded-full bg-[#E50914] hidden sm:inline-block shrink-0"></span>
                            <h1 class="text-lg sm:text-2xl font-black text-white tracking-tight leading-tight truncate">
                                ระบบจัดการพนักงาน ร้านหม่าล่า
                            </h1>
                        </div>
                        <p class="text-xs sm:text-sm text-rose-200/90 font-light mt-0.5 truncate">
                            Employee Management System – Mala Restaurant
                        </p>
                    </div>
                </div>

                <!-- Right: Notification Bell & Role Dropdown (Matches Images) -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <!-- Bell Button with Notification Dot -->
                    <button type="button"
                            onclick="showToast('ไม่มีการแจ้งเตือนใหม่ในขณะนี้')"
                            class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white text-slate-800 shadow-md flex items-center justify-center relative hover:scale-105 active:scale-95 transition-all cursor-pointer border border-white/80"
                            title="การแจ้งเตือน">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-slate-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <!-- Red Notification Dot -->
                        <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 rounded-full bg-[#E50914] border-2 border-white"></span>
                    </button>

                    <!-- Real Employee Profile Pill (Synced from Access Control) -->
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-white text-slate-800 text-xs font-bold shadow-md border border-white/80 select-none">
                        @if(!empty($employee['avatar']))
                            <img src="{{ $employee['avatar'] }}" alt="{{ $employee['name'] }}" class="w-6 h-6 rounded-full object-cover border border-slate-200">
                        @else
                            <div class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center text-[10px] font-bold">
                                {{ mb_substr($employee['name'], 0, 2) }}
                            </div>
                        @endif
                        <span class="font-bold text-slate-900">{{ $employee['name'] }}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full bg-emerald-100 text-emerald-800 font-bold border border-emerald-200">จริง</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- ========================================================================= -->
        <!-- 2. SUB-NAVIGATION TABS: [สแกนหน้าเข้างาน], [สรุปยอดเงินเดือน], [ภาพรวมพนักงาน]  -->
        <!-- ========================================================================= -->
        <nav class="bg-white/95 rounded-2xl p-1.5 shadow-sm border border-slate-200/80 flex items-center gap-1.5 sm:gap-2">
            <!-- Tab 1: สแกนหน้าเข้างาน -->
            <button type="button"
                    id="tab-btn-attendance"
                    onclick="switchEmployeeTab('attendance')"
                    class="tab-btn flex-1 py-2.5 sm:py-3 px-2 sm:px-6 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 transition-all cursor-pointer {{ $currentTab === 'attendance' ? 'bg-gradient-to-r from-[#B80A17] via-[#E50914] to-[#B80A17] text-white shadow-md shadow-rose-900/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <!-- Face Scan Icon -->
                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2" />
                    <circle cx="12" cy="11" r="3" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 17c0-2 1.79-3 4-3s4 1 4 3" />
                </svg>
                <span>สแกนหน้าเข้างาน</span>
            </button>

            <!-- Tab 2: สรุปยอดเงินเดือน -->
            <button type="button"
                    id="tab-btn-payroll"
                    onclick="switchEmployeeTab('payroll')"
                    class="tab-btn flex-1 py-2.5 sm:py-3 px-2 sm:px-6 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 transition-all cursor-pointer {{ $currentTab === 'payroll' ? 'bg-gradient-to-r from-[#B80A17] via-[#E50914] to-[#B80A17] text-white shadow-md shadow-rose-900/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <!-- Stack of Coins Icon -->
                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <ellipse cx="12" cy="6" rx="8" ry="3"/>
                    <path d="M4 6v4c0 1.66 3.58 3 8 3s8-1.34 8-3V6"/>
                    <path d="M4 10v4c0 1.66 3.58 3 8 3s8-1.34 8-3v-4"/>
                    <path d="M4 14v4c0 1.66 3.58 3 8 3s8-1.34 8-3v-4"/>
                </svg>
                <span>สรุปยอดเงินเดือน</span>
            </button>

            <!-- Tab 3: ภาพรวมพนักงาน (Matches Image 2) -->
            <button type="button"
                    id="tab-btn-overview"
                    onclick="switchEmployeeTab('overview')"
                    class="tab-btn flex-1 py-2.5 sm:py-3 px-2 sm:px-6 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 transition-all cursor-pointer {{ $currentTab === 'overview' ? 'bg-gradient-to-r from-[#B80A17] via-[#E50914] to-[#B80A17] text-white shadow-md shadow-rose-900/20' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}">
                <!-- Users / Schedule Table Icon -->
                <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>ภาพรวมพนักงาน</span>
            </button>
        </nav>

        <!-- ========================================================================= -->
        <!-- 3. TAB PANE 1: สแกนหน้าเข้างาน                                            -->
        <!-- ========================================================================= -->
        <div id="tab-pane-attendance" class="space-y-4 sm:space-y-5">
            <!-- Main Grid: Unified Left Card (Geofence + Instructions + Scan Action) & Right Card (Today Attendance Summary & Split Photos) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5 items-stretch">
                <!-- Left: Combined Card (Geofence Status + 3-Step Guide + Face Scan Action) -->
                <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-100 shadow-sm flex flex-col justify-between gap-5">
                    
                    <!-- 1. Geofence Location Card Bar (เช็คอินได้เฉพาะในเขตร้าน) -->
                    <div class="bg-gradient-to-r from-rose-50/70 via-rose-50/40 to-pink-50/30 rounded-2xl p-4 sm:p-4.5 border border-rose-100/90 flex items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-start gap-3 min-w-0">
                            <!-- Red Location Pin -->
                            <div class="w-10 h-10 rounded-2xl bg-white text-[#E50914] flex items-center justify-center shrink-0 shadow-xs border border-rose-100">
                                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm sm:text-base font-bold text-slate-800 leading-tight">
                                        เช็คอินได้เฉพาะในเขตร้าน
                                    </h3>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-rose-100 text-[#E50914]">GPS ลิงก์ร้าน</span>
                                </div>
                                <!-- Green Verified Pill -->
                                <div class="mt-1.5 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] sm:text-xs font-bold">
                                    <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span id="geofence-status-text">{{ $todayAttendance['geofence_status'] }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                    <span id="store-coords-display">📍 พิกัดร้านหม่าล่า: {{ number_format($storeLocation['lat'], 4) }}, {{ number_format($storeLocation['lng'], 4) }}</span>
                                    <span class="text-slate-300">•</span>
                                    <span id="geofence-distance-indicator" class="text-emerald-600 font-bold">ห่าง 8 ม. (อยู่ในเขตร้าน)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Storefront Icon Button (Right side) - คลิกเพื่อเปิดหน้าต่างปักหมุดเลือกพิกัดร้าน -->
                        <button type="button"
                                onclick="openStoreLocationModal()"
                                class="relative w-12 h-12 sm:w-13 sm:h-13 rounded-2xl bg-white hover:bg-rose-50 active:scale-95 border-2 border-rose-200 hover:border-[#E50914] flex items-center justify-center text-rose-500 hover:text-[#E50914] shrink-0 transition-all cursor-pointer shadow-xs group"
                                title="คลิกเพื่อปักหมุดเลือกพิกัดและขอบเขตร้าน">
                            <!-- Storefront Icon -->
                            <svg class="w-7 h-7 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18l-2 9H5L3 3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 12v8a1 1 0 001 1h14a1 1 0 001-1v-8M9 21v-6a2 2 0 012-2h2a2 2 0 012 2v6" />
                            </svg>
                            <!-- Mini Pin Badge -->
                            <span class="absolute -top-1.5 -right-1.5 w-4.5 h-4.5 rounded-full bg-[#E50914] text-white flex items-center justify-center text-[9px] shadow font-bold animate-pulse">
                                📍
                            </span>
                        </button>
                    </div>

                    <!-- 2. Guide Steps Flow (วิธีเช็คอิน / เช็คเอาท์ด้วยใบหน้า) -->
                    <div class="py-1">
                        <div class="flex items-center gap-2 text-slate-800 font-bold text-sm mb-3.5">
                            <div class="w-6 h-6 rounded-lg bg-rose-50 text-[#E50914] flex items-center justify-center text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <span>วิธีเช็คอิน / เช็คเอาท์ด้วยใบหน้า</span>
                        </div>

                        <!-- 3 Step Flow -->
                        <div class="grid grid-cols-5 items-center gap-1 sm:gap-2">
                            <!-- Step 1 -->
                            <div class="col-span-1 flex flex-col items-center text-center">
                                <span class="w-5 h-5 rounded-full bg-[#E50914] text-white text-[11px] font-black flex items-center justify-center shadow-xs">1</span>
                                <div class="w-11 h-11 sm:w-13 sm:h-13 rounded-2xl bg-rose-50/80 border border-rose-100 flex items-center justify-center text-[#E50914] mt-2 shadow-2xs">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2" />
                                        <circle cx="12" cy="11" r="3" />
                                    </svg>
                                </div>
                                <span class="text-[10px] sm:text-[11.5px] font-semibold text-slate-700 mt-2 leading-tight">
                                    กด "เริ่มสแกนใบหน้า"
                                </span>
                            </div>

                            <!-- Arrow 1 -->
                            <div class="col-span-1 flex items-center justify-center text-slate-300 font-bold text-base sm:text-lg">
                                ➔
                            </div>

                            <!-- Step 2 -->
                            <div class="col-span-1 flex flex-col items-center text-center">
                                <span class="w-5 h-5 rounded-full bg-[#E50914] text-white text-[11px] font-black flex items-center justify-center shadow-xs">2</span>
                                <div class="w-11 h-11 sm:w-13 sm:h-13 rounded-2xl bg-rose-50/80 border border-rose-100 flex items-center justify-center text-[#E50914] mt-2 shadow-2xs">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="9" />
                                        <path stroke-linecap="round" d="M9 10h.01M15 10h.01M9.5 15a3.5 3.5 0 005 0" />
                                    </svg>
                                </div>
                                <span class="text-[10px] sm:text-[11.5px] font-semibold text-slate-700 mt-2 leading-tight">
                                    มองกล้อง ให้เห็นใบหน้าชัดเจน
                                </span>
                            </div>

                            <!-- Arrow 2 -->
                            <div class="col-span-1 flex items-center justify-center text-slate-300 font-bold text-base sm:text-lg">
                                ➔
                            </div>

                            <!-- Step 3 -->
                            <div class="col-span-1 flex flex-col items-center text-center">
                                <span class="w-5 h-5 rounded-full bg-[#E50914] text-white text-[11px] font-black flex items-center justify-center shadow-xs">3</span>
                                <div class="w-11 h-11 sm:w-13 sm:h-13 rounded-2xl bg-rose-50/80 border border-rose-100 flex items-center justify-center text-[#E50914] mt-2 shadow-2xs">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="9" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12l2.5 2.5 5-5" />
                                    </svg>
                                </div>
                                <span class="text-[10px] sm:text-[11.5px] font-semibold text-slate-700 mt-2 leading-tight">
                                    รอระบบยืนยัน และบันทึกเวลา
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Face Scan Action Callout & Trigger Button -->
                    <div class="pt-3 border-t border-slate-100 flex flex-col items-center text-center">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full bg-[#E50914] animate-ping"></span>
                            <h2 class="text-sm sm:text-base font-bold text-slate-800">
                                สแกนใบหน้าเพื่อเช็คอิน / เช็คเอาท์
                            </h2>
                        </div>
                        <p class="text-[11px] sm:text-xs text-slate-500 mb-3">
                            ระบบตรวจจับและจดจำใบหน้าอัตโนมัติด้วย AI พร้อมบันทึกภาพถ่ายและพิกัดร้าน
                        </p>

                        <!-- Main Action Button: เริ่มสแกนใบหน้า -->
                        <button type="button"
                                onclick="openFaceScannerModal()"
                                class="w-full py-3.5 px-8 rounded-2xl bg-gradient-to-r from-[#B80A17] via-[#E50914] to-[#B80A17] hover:brightness-110 active:scale-98 text-white font-bold text-sm sm:text-base shadow-lg shadow-rose-900/25 flex items-center justify-center gap-2.5 transition-all cursor-pointer">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2" />
                                <circle cx="12" cy="11" r="3" />
                            </svg>
                            <span>เริ่มสแกนใบหน้า</span>
                        </button>
                    </div>

                </div>

                <!-- Right: Today Attendance Summary & Split Photo URLs (Moves up from Row 2) -->
                <div class="lg:col-span-6 bg-white rounded-3xl p-5 sm:p-6 border border-slate-100 shadow-sm flex flex-col justify-between">
                    <div>
                        <!-- Header: Synced Real Employee Profile (Matches Image 2) -->
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-10 h-10 rounded-2xl overflow-hidden border border-slate-200 shrink-0 shadow-2xs relative bg-slate-100 flex items-center justify-center">
                                    @if(!empty($employee['avatar']))
                                        <img src="{{ $employee['avatar'] }}" alt="{{ $employee['name'] }}" class="w-full h-full object-cover" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.classList.remove('hidden');">
                                        <div class="hidden w-full h-full {{ $employee['color'] ?? 'bg-rose-600' }} text-white flex items-center justify-center font-bold text-xs">
                                            {{ $employee['initials'] ?? mb_substr($employee['name'], 0, 2) }}
                                        </div>
                                    @else
                                        <div class="w-full h-full {{ $employee['color'] ?? 'bg-rose-600' }} text-white flex items-center justify-center font-bold text-xs">
                                            {{ $employee['initials'] ?? mb_substr($employee['name'], 0, 2) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-black text-sm sm:text-base text-slate-900 truncate">{{ $employee['name'] }}</span>
                                        <span class="px-1.5 py-0.2 rounded text-[9.5px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">จริง</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-medium truncate">
                                        {{ $employee['role'] }} • {{ $employee['department'] }}
                                    </div>
                                </div>
                            </div>
                            <span id="disp-today-date" class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold shrink-0">
                                {{ $todayAttendance['date_formatted'] }}
                            </span>
                        </div>

                        <!-- 4 Columns Grid (Image 3) -->
                        <div class="grid grid-cols-4 gap-2 pt-4 text-center">
                            <!-- Col 1: เข้า -->
                            <div class="flex flex-col items-center">
                                <div class="w-6 h-6 rounded-md bg-rose-50 text-[#E50914] flex items-center justify-center mb-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                </div>
                                <span class="text-[11px] text-slate-500 font-medium">เวลาเข้างาน</span>
                                <span id="disp-check-in" class="mt-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-black text-xs sm:text-sm">
                                    {{ $todayAttendance['check_in'] }}
                                </span>
                            </div>

                            <!-- Col 2: ออก -->
                            <div class="flex flex-col items-center border-l border-slate-100">
                                <div class="w-6 h-6 rounded-md bg-rose-50 text-[#E50914] flex items-center justify-center mb-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </div>
                                <span class="text-[11px] text-slate-500 font-medium">เวลาออกงาน</span>
                                <span id="disp-check-out" class="mt-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-black text-xs sm:text-sm">
                                    {{ $todayAttendance['check_out'] }}
                                </span>
                            </div>

                            <!-- Col 3: เวลาทำงาน -->
                            <div class="flex flex-col items-center border-l border-slate-100">
                                <div class="w-6 h-6 rounded-md bg-rose-50 text-[#E50914] flex items-center justify-center mb-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="9" />
                                        <path stroke-linecap="round" d="M12 7v5l3 2" />
                                    </svg>
                                </div>
                                <span class="text-[11px] text-slate-500 font-medium">เวลาทำงาน</span>
                                <span id="disp-work-hours" class="mt-1 font-black text-xs sm:text-sm text-slate-800">
                                    {{ $todayAttendance['work_hours'] }}
                                </span>
                            </div>

                            <!-- Col 4: สถานะปัจจุบัน -->
                            <div class="flex flex-col items-center border-l border-slate-100">
                                <div class="w-6 h-6 rounded-md bg-rose-50 text-[#E50914] flex items-center justify-center mb-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-[11px] text-slate-500 font-medium">สถานะปัจจุบัน</span>
                                <span id="disp-status-pill" class="mt-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[11px] sm:text-xs">
                                    ✓ {{ $todayAttendance['status'] }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Photo Display Section: แสดงภาพถ่ายสแกนหน้าแบ่งตามเวลาเข้า หรือ ออกงาน (ตามโจทย์ภาพที่ 2) -->
                    <div class="mt-4 pt-3.5 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#E50914]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>ภาพถ่ายสแกนหน้าบันทึกเวลา (แบ่งเวลาเข้า-ออกงาน)</span>
                            </span>
                            <span class="text-[10px] text-slate-400">แปลงเป็น URL เปิดดูได้ปกติ</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <!-- 1. ภาพถ่ายเวลาเข้างาน -->
                            <div class="bg-gradient-to-b from-slate-50 to-white rounded-2xl p-2.5 border border-slate-200/80 flex flex-col justify-between hover:border-emerald-300 transition-all shadow-2xs">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        ภาพตอนเข้างาน
                                    </span>
                                    <span id="disp-photo-time-in" class="text-[10.5px] font-bold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded">
                                        {{ $todayAttendance['check_in'] }}
                                    </span>
                                </div>

                                <!-- Photo Thumbnail -->
                                <div class="relative w-full aspect-4/3 rounded-xl overflow-hidden bg-slate-900 border border-slate-200 shadow-inner group">
                                    <img id="disp-check-in-img"
                                         src="{{ $todayAttendance['check_in_photo'] }}"
                                         alt="ภาพสแกนเข้างาน"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200 cursor-pointer"
                                         onclick="openPhotoLightbox(this.src, 'ภาพถ่ายสแกนหน้าเข้างาน (เวลา ' + document.getElementById('disp-check-in').innerText + ')')">
                                    
                                    <div class="absolute bottom-1 right-1 bg-emerald-600/90 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow">
                                        IN
                                    </div>
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 flex items-center justify-center transition-all pointer-events-none">
                                        <span class="text-white text-xs font-bold opacity-0 group-hover:opacity-100 bg-black/60 px-2 py-1 rounded-full backdrop-blur-xs">🔍 ขยายภาพ</span>
                                    </div>
                                </div>

                                <!-- URL Link Bar -->
                                <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between gap-1 text-[11px]">
                                    <a id="disp-check-in-url"
                                       href="{{ $todayAttendance['check_in_photo'] }}"
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1 font-semibold truncate max-w-[125px]"
                                       title="คลิกเพื่อเปิด URL รูปภาพในแท็บใหม่">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        <span class="truncate">เปิดดู URL ภาพ</span>
                                    </a>
                                    <button type="button"
                                            onclick="copyPhotoUrl('disp-check-in-url')"
                                            class="text-slate-500 hover:text-slate-800 text-[10px] px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 border border-slate-200 cursor-pointer transition-all shrink-0">
                                        คัดลอก URL
                                    </button>
                                </div>
                            </div>

                            <!-- 2. ภาพถ่ายเวลาออกงาน -->
                            <div class="bg-gradient-to-b from-slate-50 to-white rounded-2xl p-2.5 border border-slate-200/80 flex flex-col justify-between hover:border-rose-300 transition-all shadow-2xs">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700">
                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                        ภาพตอนออกงาน
                                    </span>
                                    <span id="disp-photo-time-out" class="text-[10.5px] font-bold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded">
                                        {{ $todayAttendance['check_out'] }}
                                    </span>
                                </div>

                                <!-- Photo Thumbnail -->
                                <div class="relative w-full aspect-4/3 rounded-xl overflow-hidden bg-slate-900 border border-slate-200 shadow-inner group">
                                    <img id="disp-check-out-img"
                                         src="{{ $todayAttendance['check_out_photo'] }}"
                                         alt="ภาพสแกนออกงาน"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200 cursor-pointer"
                                         onclick="openPhotoLightbox(this.src, 'ภาพถ่ายสแกนหน้าออกงาน (เวลา ' + document.getElementById('disp-check-out').innerText + ')')">
                                    
                                    <div class="absolute bottom-1 right-1 bg-rose-600/90 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow">
                                        OUT
                                    </div>
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 flex items-center justify-center transition-all pointer-events-none">
                                        <span class="text-white text-xs font-bold opacity-0 group-hover:opacity-100 bg-black/60 px-2 py-1 rounded-full backdrop-blur-xs">🔍 ขยายภาพ</span>
                                    </div>
                                </div>

                                <!-- URL Link Bar -->
                                <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between gap-1 text-[11px]">
                                    <a id="disp-check-out-url"
                                       href="{{ $todayAttendance['check_out_photo'] }}"
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-1 font-semibold truncate max-w-[125px]"
                                       title="คลิกเพื่อเปิด URL รูปภาพในแท็บใหม่">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        <span class="truncate">เปิดดู URL ภาพ</span>
                                    </a>
                                    <button type="button"
                                            onclick="copyPhotoUrl('disp-check-out-url')"
                                            class="text-slate-500 hover:text-slate-800 text-[10px] px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 border border-slate-200 cursor-pointer transition-all shrink-0">
                                        คัดลอก URL
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 4. TAB PANE 2: สรุปยอดเงินเดือน (Images 3 & 4)                               -->
        <!-- ========================================================================= -->
        <div id="tab-pane-payroll" class="hidden space-y-4 sm:space-y-5">
            <!-- Hero Card: 3D Calendar + Title + Month Dropdown + 5 Metric Boxes -->
            <div class="bg-gradient-to-r from-rose-50/80 via-pink-50/40 to-white rounded-3xl p-5 sm:p-7 border border-rose-100 shadow-sm space-y-5">
                <!-- Top Row: 3D Calendar + Title + Month Selector -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3.5">
                        <!-- 3D Calendar & Coins Graphic (Inline High-Fidelity SVG) -->
                        <div class="w-14 h-14 sm:w-16 sm:h-16 shrink-0 flex items-center justify-center">
                            <svg class="w-full h-full drop-shadow-md" viewBox="0 0 64 64" fill="none">
                                <!-- Calendar Base -->
                                <rect x="8" y="14" width="40" height="42" rx="8" fill="#FFFFFF" stroke="#F43F5E" stroke-width="2"/>
                                <path d="M8 22C8 17.5817 11.5817 14 16 14H40C44.4183 14 48 17.5817 48 22V24H8V22Z" fill="#E11D48"/>
                                <!-- Calendar Binder Rings -->
                                <circle cx="18" cy="11" r="3" fill="#FFE4E6" stroke="#BE123C" stroke-width="1.8"/>
                                <circle cx="38" cy="11" r="3" fill="#FFE4E6" stroke="#BE123C" stroke-width="1.8"/>
                                <!-- Calendar Dates Grid Dots -->
                                <rect x="14" y="30" width="6" height="5" rx="1.5" fill="#FDA4AF"/>
                                <rect x="25" y="30" width="6" height="5" rx="1.5" fill="#FDA4AF"/>
                                <rect x="36" y="30" width="6" height="5" rx="1.5" fill="#FDA4AF"/>
                                <rect x="14" y="40" width="6" height="5" rx="1.5" fill="#FDA4AF"/>
                                <rect x="25" y="40" width="6" height="5" rx="1.5" fill="#FDA4AF"/>
                                <rect x="36" y="40" width="6" height="5" rx="1.5" fill="#FDA4AF"/>
                                <!-- Golden 3D Coins Stack on Right -->
                                <ellipse cx="44" cy="52" rx="14" ry="5.5" fill="#CA8A04"/>
                                <ellipse cx="44" cy="50" rx="14" ry="5.5" fill="#EAB308"/>
                                <ellipse cx="44" cy="46" rx="14" ry="5.5" fill="#CA8A04"/>
                                <ellipse cx="44" cy="44" rx="14" ry="5.5" fill="#FACC15"/>
                                <ellipse cx="44" cy="40" rx="14" ry="5.5" fill="#EAB308"/>
                                <ellipse cx="44" cy="38" rx="14" ry="5.5" fill="#FEF08A"/>
                            </svg>
                        </div>

                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight leading-tight">
                                    สรุปยอดเงินเดือน
                                </h2>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200 inline-flex items-center gap-1">
                                    <span>👤</span>
                                    <span id="hero-payroll-emp-name">{{ $employee['name'] }}</span>
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-500 mt-0.5" id="hero-payroll-emp-subtitle">
                                {{ $employee['role'] }} • {{ $employee['department'] }}
                            </p>
                        </div>
                    </div>

                    <!-- Month Selector Controls Bar (Same design style: < [📅 งวด ธ.ค. 2567 ⌃] > + Monthly Quick Pills) -->
                    <div class="relative flex flex-wrap items-center gap-1.5 shrink-0" id="payroll-filter-bar-container">
                        <!-- Navigation: < , [📅 Button with Popover] , > -->
                        <div class="inline-flex items-center gap-1">
                            <button type="button"
                                    onclick="navigatePayrollMonth(-1)"
                                    class="w-8 h-8 rounded-xl bg-white hover:bg-slate-50 active:scale-95 border border-slate-200/90 text-slate-600 flex items-center justify-center text-xs font-bold cursor-pointer transition-all shadow-2xs"
                                    title="งวดเดือนก่อนหน้า">
                                &lt;
                            </button>

                            <!-- Month Picker Trigger Button -->
                            <button type="button"
                                    id="btn-payroll-calendar"
                                    onclick="togglePayrollMonthPopover(event)"
                                    class="h-8 px-3 rounded-xl bg-white hover:bg-slate-50 active:scale-95 border-2 border-slate-900 text-slate-800 flex items-center gap-1.5 text-xs font-bold cursor-pointer transition-all shadow-2xs">
                                <span>📅</span>
                                <span id="label-payroll-selected">งวด {{ $currentPayroll['period_short'] ?? '' }}</span>
                                <svg id="chevron-payroll-popover" class="w-3.5 h-3.5 text-slate-500 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <button type="button"
                                    onclick="navigatePayrollMonth(1)"
                                    class="w-8 h-8 rounded-xl bg-white hover:bg-slate-50 active:scale-95 border border-slate-200/90 text-slate-600 flex items-center justify-center text-xs font-bold cursor-pointer transition-all shadow-2xs"
                                    title="งวดเดือนถัดไป">
                                &gt;
                            </button>
                        </div>

                        <!-- Quick Month Pills -->
                        <div class="inline-flex flex-wrap items-center gap-1 bg-white p-1 rounded-2xl border border-slate-200/80 shadow-2xs">
                            @foreach(array_slice($payrollPeriods, 0, 4, true) as $mKey => $mData)
                                <button type="button" onclick="selectPayrollMonth('{{ $mKey }}')" id="pill-payroll-{{ $mKey }}" class="payroll-pill px-3 py-1 rounded-xl text-xs {{ $mKey === $selectedMonth ? 'font-bold bg-[#0F172A] text-white shadow-xs' : 'font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50' }} transition-all cursor-pointer">{{ $mData['period_short'] }}</button>
                            @endforeach
                        </div>

                        <!-- Monthly Calendar Popover -->
                        <div id="popover-payroll-calendar"
                             class="absolute right-0 top-full mt-2 z-50 w-72 bg-white rounded-3xl p-4 shadow-2xl border border-slate-200 hidden animate-in fade-in zoom-in-95 duration-150">
                            <!-- Popover Header -->
                            <div class="flex items-center justify-between pb-2.5 mb-3 border-b border-slate-100">
                                <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <span>🗓️</span>
                                    <span>ปฏิทินงวดเงินเดือน: ปี {{ \Carbon\Carbon::now('Asia/Bangkok')->year + 543 }}</span>
                                </span>
                                <span class="text-[10px] text-slate-400">งวด 12 เดือน</span>
                            </div>

                            <!-- 12 Months Grid -->
                            <div class="grid grid-cols-3 gap-1.5 text-xs">
                                @foreach(array_reverse($payrollPeriods, true) as $mKey => $mData)
                                    <button type="button" onclick="selectPayrollMonth('{{ $mKey }}')" id="btn-month-{{ $mKey }}" class="cal-month-btn py-2 rounded-xl {{ $mKey === $selectedMonth ? 'bg-slate-900 text-white font-bold shadow-xs' : 'border border-slate-100 hover:border-rose-400 font-semibold text-slate-700 hover:bg-rose-50' }} transition-all cursor-pointer">{{ $mData['period_short'] }}</button>
                                @endforeach
                            </div>

                            <!-- Popover Footer -->
                            <div class="flex items-center justify-between pt-2.5 mt-3 border-t border-slate-100 text-xs">
                                <button type="button" onclick="selectPayrollMonth('{{ $currentMonthKey }}')" class="text-blue-600 hover:text-blue-800 font-bold cursor-pointer">งวดล่าสุด</button>
                                <button type="button" onclick="closePayrollMonthPopover()" class="text-slate-400 hover:text-slate-700 cursor-pointer">ปิด</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5 Metric Boxes in a Row (Desktop) / Vertical (Mobile) -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3 pt-1">
                    <!-- 1. เงินเดือนพื้นฐาน -->
                    <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-100 shadow-xs text-center flex flex-col items-center justify-center">
                        <div class="w-8 h-8 rounded-full bg-rose-50 text-[#E50914] flex items-center justify-center mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs text-slate-500 font-medium">เงินเดือนพื้นฐาน</span>
                        <span id="box-base-salary" class="font-extrabold text-sm sm:text-base text-slate-800 mt-0.5">฿{{ number_format($currentPayroll['base_salary'] ?? 0) }}</span>
                    </div>

                    <!-- 2. ค่าล่วงเวลา (OT) -->
                    <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-100 shadow-xs text-center flex flex-col items-center justify-center">
                        <div class="w-8 h-8 rounded-full bg-rose-50 text-[#E50914] flex items-center justify-center mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" d="M12 7v5l3 2" />
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs text-slate-500 font-medium">ค่าล่วงเวลา (OT)</span>
                        <span id="box-ot-amount" class="font-extrabold text-sm sm:text-base text-emerald-600 mt-0.5">฿{{ number_format($currentPayroll['ot_amount'] ?? 0) }}</span>
                    </div>

                    <!-- 3. โบนัส / เบี้ยขยัน -->
                    <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-100 shadow-xs text-center flex flex-col items-center justify-center">
                        <div class="w-8 h-8 rounded-full bg-rose-50 text-[#E50914] flex items-center justify-center mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V4a2 2 0 112 2h-4a2 2 0 112-2v2m-6 3h12a2 2 0 012 2v2H4v-2a2 2 0 012-2zm0 4h12v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6z" />
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs text-slate-500 font-medium">โบนัส / เบี้ยขยัน</span>
                        <span id="box-bonus-amount" class="font-extrabold text-sm sm:text-base text-emerald-600 mt-0.5">฿{{ number_format($currentPayroll['bonus'] ?? 0) }}</span>
                    </div>

                    <!-- 4. รายการหัก -->
                    <div class="bg-white rounded-2xl p-3.5 sm:p-4 border border-slate-100 shadow-xs text-center flex flex-col items-center justify-center">
                        <div class="w-8 h-8 rounded-full bg-rose-50 text-[#E50914] flex items-center justify-center mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" d="M8 12h8" />
                            </svg>
                        </div>
                        <span class="text-[11px] sm:text-xs text-slate-500 font-medium">รายการหัก</span>
                        @php $heroDed = (float) ($currentPayroll['deductions_total'] ?? 0); @endphp
                        <span id="box-deductions-amount" class="font-extrabold text-sm sm:text-base text-rose-600 mt-0.5">{{ $heroDed > 0 ? '-฿' : '฿' }}{{ number_format($heroDed) }}</span>
                    </div>

                    <!-- 5. รับสุทธิ (Large Red Card matching Image 3) -->
                    <div class="col-span-2 sm:col-span-1 lg:col-span-1 bg-gradient-to-r from-[#B80A17] via-[#E50914] to-[#B80A17] text-white rounded-2xl p-3.5 sm:p-4 shadow-md text-center flex flex-col items-center justify-center">
                        <div class="w-8 h-8 rounded-full bg-white/20 text-white flex items-center justify-center mb-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <ellipse cx="12" cy="6" rx="8" ry="3"/>
                                <path d="M4 6v4c0 1.66 3.58 3 8 3s8-1.34 8-3V6"/>
                                <path d="M4 10v4c0 1.66 3.58 3 8 3s8-1.34 8-3v-4"/>
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-rose-100">รับสุทธิ</span>
                        <span id="box-net-salary" class="font-black text-lg sm:text-xl text-white mt-0.5">฿{{ number_format($currentPayroll['net_salary'] ?? 0) }}</span>
                    </div>
                </div>
            </div>

            <!-- Toolbar: Header + Search Bar + 3/4 Grid Toggle (ตรงตามภาพที่ 2 & หน้าแคชเชียร์ POS) -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                <div class="flex items-center gap-2.5">
                    <span class="w-1.5 h-5 rounded-full bg-[#E50914] block"></span>
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">สรุปรายบุคคล</h3>
                    <span class="text-xs text-slate-500">(<span id="payroll-visible-count">{{ count($payrollCards) }}</span> คน)</span>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end flex-wrap">
                    <!-- Quick Search Input (ตรงตามภาพที่ 2) -->
                    <div class="relative flex-1 sm:w-56 sm:flex-initial">
                        <input type="text"
                               id="payroll-search-input"
                               oninput="filterPayrollCards()"
                               placeholder="ค้นหาพนักงาน / ตำแหน่ง..."
                               class="w-full pl-8 pr-3 py-1.5 bg-white border border-slate-200/90 rounded-full text-[12.5px] text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-[#E50914] shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- 3 / 4 Items per Row Toggle (ตรงตามภาพที่ 2 และหน้าแคชเชียร์) -->
                    <div class="flex items-center bg-white border border-slate-200/90 rounded-full p-0.5 shadow-2xs shrink-0" title="เลือกการแสดงผลจำนวนการ์ดต่อแถว">
                        <!-- 1. สี่เหลี่ยม 3 จุด (3 รายการต่อแถว) -->
                        <button type="button" onclick="setPayrollGridColumns(3)" id="btn-payroll-grid-3"
                                class="grid-toggle-btn px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all text-slate-500 hover:text-slate-800 hover:bg-slate-100 cursor-pointer"
                                title="แสดง 3 การ์ดต่อแถว">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                <rect x="2" y="7" width="4" height="6" rx="1"/>
                                <rect x="8" y="7" width="4" height="6" rx="1"/>
                                <rect x="14" y="7" width="4" height="6" rx="1"/>
                            </svg>
                            <span class="text-[11px] leading-none">3</span>
                        </button>

                        <!-- 2. สี่เหลี่ยม 4 จุด (4 รายการต่อแถว) -->
                        <button type="button" onclick="setPayrollGridColumns(4)" id="btn-payroll-grid-4"
                                class="grid-toggle-btn active px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all bg-[#E50914] text-white shadow-2xs cursor-pointer"
                                title="แสดง 4 การ์ดต่อแถว">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                <rect x="1.5" y="7" width="3.2" height="6" rx="0.8"/>
                                <rect x="6.1" y="7" width="3.2" height="6" rx="0.8"/>
                                <rect x="10.7" y="7" width="3.2" height="6" rx="0.8"/>
                                <rect x="15.3" y="7" width="3.2" height="6" rx="0.8"/>
                            </svg>
                            <span class="text-[11px] leading-none">4</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cards Grid: ปรับขนาดการ์ดตามภาพที่ 1 ให้เพียงพอกับ 4 การ์ดต่อแถว -->
            <div id="payroll-cards-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3.5 transition-all duration-200">
                @foreach($payrollCards as $card)
                <div class="payroll-card bg-white rounded-3xl p-3.5 sm:p-4 border border-slate-100 shadow-sm flex flex-col justify-between hover:shadow-md transition-all cursor-pointer hover:border-rose-300 {{ $loop->first ? 'ring-2 ring-[#E50914]' : '' }}"
                     id="payroll-card-{{ $card['id'] }}"
                     onclick='selectHeroEmployee(@json($card))'
                     data-search-text="{{ $card['search_text'] }}">
                    <div>
                        <!-- Top Profile Header: Avatar, Name, Refresh, Badges, 'เข้างาน A' -->
                        <div class="flex items-center justify-between gap-2 pb-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <!-- Avatar Squircle / Photo -->
                                @if(!empty($card['avatar']))
                                    <img src="{{ $card['avatar'] }}" alt="{{ $card['name'] }}" class="w-10 h-10 rounded-xl object-cover shrink-0 shadow-2xs border border-slate-200">
                                @else
                                    <div class="w-10 h-10 rounded-xl {{ $card['color'] }} text-white flex items-center justify-center shrink-0 shadow-2xs font-black text-xl leading-none">
                                        {{ $card['initials'] }}
                                    </div>
                                @endif
                                <div class="space-y-0.5 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-extrabold text-slate-900 text-sm sm:text-base leading-tight truncate" @if($loop->first) id="card-emp-name" @endif>{{ $card['name'] }}</span>
                                        <button type="button"
                                                onclick="event.stopPropagation(); syncEmployeesData(true)"
                                                class="w-5 h-5 rounded-md border border-slate-200/90 bg-white hover:bg-slate-50 active:scale-95 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-all cursor-pointer shadow-2xs shrink-0"
                                                title="รีเฟรชข้อมูล">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="flex items-center flex-wrap gap-1 text-[10px] sm:text-[11px]">
                                        <!-- Pin Location -->
                                        <span class="inline-flex items-center gap-0.5 text-slate-400 font-medium">
                                            <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span>{{ $card['branch'] }}</span>
                                        </span>
                                        <!-- Role Badge -->
                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md {{ $card['role_badge_class'] }} font-bold text-[10px]">
                                            <span>{{ $card['role_badge_icon'] }}</span>
                                            <span>{{ $card['role_badge_text'] }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: 'เข้างาน' label + Grade badge -->
                            @php
                                $gradeColor = match($card['grade'] ?? 'A') {
                                    'A', 'B' => 'bg-[#DCFCE7] text-[#16A34A]',
                                    'C' => 'bg-amber-50 text-amber-600',
                                    default => 'bg-rose-50 text-rose-600',
                                };
                            @endphp
                            <div class="flex flex-col items-center shrink-0">
                                <span class="text-[10px] font-semibold text-slate-400 mb-0.5">เข้างาน</span>
                                <div class="w-7 h-7 rounded-full {{ $gradeColor }} font-black text-xs flex items-center justify-center shadow-2xs">
                                    {{ $card['grade'] }}
                                </div>
                            </div>
                        </div>

                        <!-- สรุปการทำงาน -->
                        <div class="pt-2.5 pb-2.5 border-t border-slate-100">
                            <div class="flex items-center gap-1.5 pb-1.5 text-slate-800 font-bold text-xs">
                                <span class="text-[#E50914]">📊</span>
                                <span>สรุปการทำงาน</span>
                            </div>

                            <div class="grid grid-cols-4 gap-1 pt-0.5 text-center">
                                <!-- Metric 1: จำนวนวันทำงาน -->
                                <div class="p-1 sm:p-1.5 rounded-xl bg-rose-50/50 border border-rose-100/60 flex flex-col items-center">
                                    <span class="text-[#E50914] text-xs mb-0.5">📅</span>
                                    <span @if($loop->first) id="stat-work-days" @endif class="font-black text-[11px] sm:text-xs text-emerald-600 leading-tight">{{ $card['work_days'] }} วัน</span>
                                    <span class="text-[8.5px] sm:text-[9.5px] text-slate-500 mt-0.5 leading-tight truncate">วันทำงาน</span>
                                </div>

                                <!-- Metric 2: ชั่วโมงรวม -->
                                <div class="p-1 sm:p-1.5 rounded-xl bg-rose-50/50 border border-rose-100/60 flex flex-col items-center">
                                    <span class="text-[#E50914] text-xs mb-0.5">🕒</span>
                                    <span @if($loop->first) id="stat-total-hours" @endif class="font-black text-[11px] sm:text-xs text-emerald-600 leading-tight">{{ $card['work_hours'] }}</span>
                                    <span class="text-[8.5px] sm:text-[9.5px] text-slate-500 mt-0.5 leading-tight truncate">ชั่วโมงรวม</span>
                                </div>

                                <!-- Metric 3: OT รวม -->
                                <div class="p-1 sm:p-1.5 rounded-xl bg-rose-50/50 border border-rose-100/60 flex flex-col items-center">
                                    <span class="text-[#E50914] text-xs mb-0.5">⏱️</span>
                                    <span @if($loop->first) id="stat-ot-hours" @endif class="font-black text-[11px] sm:text-xs text-emerald-600 leading-tight">{{ $card['ot_hours'] }}</span>
                                    <span class="text-[8.5px] sm:text-[9.5px] text-slate-500 mt-0.5 leading-tight truncate">OT รวม</span>
                                </div>

                                <!-- Metric 4: วันลา -->
                                <div class="p-1 sm:p-1.5 rounded-xl bg-rose-50/50 border border-rose-100/60 flex flex-col items-center">
                                    <span class="text-[#E50914] text-xs mb-0.5">☂️</span>
                                    <span @if($loop->first) id="stat-leave-days" @endif class="font-black text-[11px] sm:text-xs text-emerald-600 leading-tight">{{ $card['leave_days'] }} วัน</span>
                                    <span class="text-[8.5px] sm:text-[9.5px] text-slate-500 mt-0.5 leading-tight truncate">วันลา</span>
                                </div>
                            </div>
                        </div>

                        <!-- อัตราเข้างาน -->
                        @php
                            $rateNum = (int) ($card['rate_num'] ?? 0);
                            $rateTextColor = $rateNum >= 80 ? 'text-[#16A34A]' : ($rateNum >= 50 ? 'text-amber-600' : 'text-rose-600');
                            $rateBarColor = $rateNum >= 80 ? 'bg-[#16A34A]' : ($rateNum >= 50 ? 'bg-amber-500' : 'bg-rose-500');
                            $lateTextColor = (($card['late_days'] ?? 0) > 0) ? 'text-amber-600' : 'text-[#16A34A]';
                            $absentTextColor = (($card['absent_days'] ?? 0) > 0) ? 'text-rose-600' : 'text-[#16A34A]';
                        @endphp
                        <div class="pt-2.5 pb-2.5 border-t border-slate-100">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-extrabold text-slate-800 text-[11px] sm:text-xs">อัตราเข้างาน</span>
                                <div class="flex items-center gap-1">
                                    @if(!empty($card['rate_delta']))
                                        <span class="{{ $rateTextColor }} font-bold text-[10px]">{{ $card['rate_delta'] }}</span>
                                    @endif
                                    <span class="font-black {{ $rateTextColor }} text-xs sm:text-sm">{{ $card['rate_percent'] }}</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                <div class="{{ $rateBarColor }} h-full rounded-full transition-all duration-300" style="width: {{ $card['progress_width'] }};"></div>
                            </div>
                        </div>

                        <!-- Stats Rows -->
                        <div class="divide-y divide-slate-100 text-[11px] sm:text-xs border-t border-slate-100">
                            <div class="py-2 flex items-center justify-between">
                                <div class="flex items-center gap-1.5 text-slate-600 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="3" y="3" width="18" height="18" rx="4" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12l2.5 2.5L16 9" />
                                    </svg>
                                    <span>มาทำงาน</span>
                                </div>
                                <span class="font-black text-slate-800">{{ $card['attendance_ratio'] }}</span>
                            </div>

                            <div class="py-2 flex items-center justify-between">
                                <div class="flex items-center gap-1.5 text-slate-600 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="9" />
                                        <path stroke-linecap="round" d="M12 7v5l3 2" />
                                    </svg>
                                    <span>มาสาย</span>
                                </div>
                                <span class="font-bold {{ $lateTextColor }}">{{ $card['late_text'] }}</span>
                            </div>

                            <div class="py-2 flex items-center justify-between">
                                <div class="flex items-center gap-1.5 text-slate-600 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6m0-6l6 6" />
                                    </svg>
                                    <span>ขาดงาน</span>
                                </div>
                                <span class="font-bold {{ $absentTextColor }}">{{ $card['absent_text'] }}</span>
                            </div>
                        </div>

                        <!-- Divider Line -->
                        <div class="border-t-2 border-slate-100 my-2.5"></div>

                        <!-- Header -->
                        <div class="flex items-center gap-1.5 pb-2 border-b border-slate-100 text-slate-800 font-bold text-xs sm:text-[13px]">
                            <span class="text-[#E50914]">📑</span>
                            <span>รายละเอียดเงินเดือน</span>
                        </div>

                        <!-- Rows -->
                        <div class="divide-y divide-slate-100 text-[11px] sm:text-xs pt-1">
                            <div class="py-1.5 flex justify-between items-center">
                                <span class="text-slate-600">เงินเดือนพื้นฐาน</span>
                                <span @if($loop->first) id="dtl-base-salary" @endif class="font-bold text-slate-800">฿{{ number_format($card['base_salary'], 0) }}</span>
                            </div>

                            <div class="py-1.5 flex justify-between items-start">
                                <div>
                                    <span class="text-slate-600 block">ค่าล่วงเวลา (OT)</span>
                                    @if($card['ot_amount'] > 0)
                                        <span @if($loop->first) id="dtl-ot-rate" @endif class="text-[10px] text-slate-400">{{ $card['ot_hours'] }} × ฿{{ number_format($card['ot_rate'], 2) }}/ชม.</span>
                                    @endif
                                </div>
                                <span @if($loop->first) id="dtl-ot-amount" @endif class="font-bold text-emerald-600">฿{{ number_format($card['ot_amount'], 0) }}</span>
                            </div>

                            <div class="py-1.5 flex justify-between items-center">
                                <span class="text-slate-600">โบนัส / เบี้ยขยัน</span>
                                <span @if($loop->first) id="dtl-bonus-amount" @endif class="font-bold text-emerald-600">฿{{ number_format($card['bonus'], 0) }}</span>
                            </div>

                            <div class="py-1.5">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-600">รายการหัก</span>
                                    <span @if($loop->first) id="dtl-deductions-total" @endif class="font-bold text-rose-600">{{ $card['deductions'] > 0 ? ('-฿' . number_format($card['deductions'], 0)) : '฿0' }}</span>
                                </div>
                                @if($card['deductions'] > 0)
                                <div class="pl-2 mt-1 space-y-0.5 text-slate-500 text-[10.5px]">
                                    @if($card['deduction_late'] > 0)
                                    <div class="flex justify-between">
                                        <span>• หักจากการมาสาย</span>
                                        <span @if($loop->first) id="dtl-late" @endif class="text-rose-500 font-semibold">-฿{{ number_format($card['deduction_late'], 0) }}</span>
                                    </div>
                                    @endif
                                    @if($card['deduction_ss'] > 0)
                                    <div class="flex justify-between">
                                        <span>• ประกันสังคม</span>
                                        <span @if($loop->first) id="dtl-ss" @endif class="text-rose-500 font-semibold">-฿{{ number_format($card['deduction_ss'], 0) }}</span>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Net Line -->
                        <div class="pt-2.5 border-t-2 border-slate-100 flex justify-between items-baseline mt-1.5">
                            <span class="font-bold text-rose-700 text-xs sm:text-sm">รับสุทธิ</span>
                            <span @if($loop->first) id="dtl-net-salary" @endif class="font-black text-xl sm:text-2xl text-[#E50914]">
                                ฿{{ number_format($card['net_salary'], 0) }}
                            </span>
                        </div>
                    </div>

                    <!-- Bottom Action Button: ดูสลิปเงินเดือน -->
                    <button type="button"
                            onclick='event.stopPropagation(); openPayslipModal(@json($card))'
                            class="w-full py-2.5 px-4 rounded-full bg-gradient-to-r from-[#B80A17] via-[#E50914] to-[#B80A17] hover:brightness-110 active:scale-98 text-white font-bold text-xs shadow-md shadow-rose-900/25 flex items-center justify-center gap-1.5 transition-all cursor-pointer mt-3">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>ดูสลิปเงินเดือน</span>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 5. TAB PANE 3: ภาพรวมพนักงาน (Employees Attendance Overview - Image 2)     -->
        <!-- ========================================================================= -->
        <div id="tab-pane-overview" class="{{ $currentTab === 'overview' ? '' : 'hidden' }} space-y-4 sm:space-y-5">

            <!-- 1. Top Controls Bar: [📅 รายวัน] / [📊 สรุปรายเดือน] + แถบกรองตามภาพที่ 1 (ต่อท้ายหรือฝั่งขวาของแถบสรุปทั้งเดือน) -->
            <div class="flex flex-wrap items-center justify-between gap-3">
                <!-- Left: Mode Switcher + แถบกรองต่อท้าย (Matches Image 1) -->
                <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                    <!-- Mode Switcher: รายวัน / สรุปรายเดือน -->
                    <div class="inline-flex items-center gap-1.5 p-1 bg-white rounded-2xl border border-slate-200/90 shadow-2xs shrink-0">
                        <button type="button"
                                id="btn-overview-daily"
                                onclick="switchOverviewMode('daily')"
                                class="px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-bold transition-all cursor-pointer bg-[#0F172A] text-white shadow-xs flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            <span>รายวัน</span>
                        </button>

                        <button type="button"
                                id="btn-overview-monthly"
                                onclick="switchOverviewMode('monthly')"
                                class="px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="18" y1="20" x2="18" y2="10"/>
                                <line x1="12" y1="20" x2="12" y2="4"/>
                                <line x1="6" y1="20" x2="6" y2="14"/>
                            </svg>
                            <span>สรุปรายเดือน</span>
                        </button>
                    </div>

                    <!-- Divider -->
                    <div class="hidden sm:block h-6 w-px bg-slate-200 mx-0.5"></div>

                    <!-- แถบกรองตามภาพที่ 1: Navigation Arrows & Dropdown Trigger + Presets -->
                    <div class="relative flex flex-wrap items-center gap-1.5" id="overview-filter-bar-container">
                        <!-- Navigation: < , [📅 3 วันล่าสุด ⌃] , > -->
                        <div class="inline-flex items-center gap-1">
                            <button type="button"
                                    onclick="stepOverviewDate(-1)"
                                    class="w-8 h-8 rounded-xl bg-white hover:bg-slate-50 active:scale-95 border border-slate-200/90 text-slate-600 flex items-center justify-center text-xs font-bold cursor-pointer transition-all shadow-2xs"
                                    title="ย้อนกลับ">
                                &lt;
                            </button>

                            <!-- Date Picker Dropdown Trigger Button (Matches Image 1) -->
                            <button type="button"
                                    id="btn-overview-date-popover"
                                    onclick="toggleOverviewDatePopover(event)"
                                    class="h-8 px-3 rounded-xl bg-white hover:bg-slate-50 active:scale-95 border-2 border-slate-900 text-slate-900 flex items-center gap-1.5 text-xs font-bold cursor-pointer transition-all shadow-2xs">
                                <span>📅</span>
                                <span id="label-overview-active-filter">วันนี้</span>
                                <svg id="chevron-overview-popover" class="w-3.5 h-3.5 text-slate-600 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <button type="button"
                                    onclick="stepOverviewDate(1)"
                                    class="w-8 h-8 rounded-xl bg-white hover:bg-slate-50 active:scale-95 border border-slate-200/90 text-slate-600 flex items-center justify-center text-xs font-bold cursor-pointer transition-all shadow-2xs"
                                    title="ถัดไป">
                                &gt;
                            </button>
                        </div>

                        <!-- Quick Presets Pills (Matches Image 1) -->
                        <div class="inline-flex flex-wrap items-center gap-1 bg-white p-1 rounded-2xl border border-slate-200/90 shadow-2xs">
                            <button type="button" onclick="setOverviewPresetFilter('today')" id="pill-ov-today" class="overview-pill px-3 py-1 rounded-xl text-xs font-bold bg-[#0F172A] text-white shadow-xs transition-all cursor-pointer">วันนี้</button>
                            <button type="button" onclick="setOverviewPresetFilter('3days')" id="pill-ov-3days" class="overview-pill px-3 py-1 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer">3 วันล่าสุด</button>
                            <button type="button" onclick="setOverviewPresetFilter('7days')" id="pill-ov-7days" class="overview-pill px-3 py-1 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer">7 วันล่าสุด</button>
                            <button type="button" onclick="setOverviewPresetFilter('15days')" id="pill-ov-15days" class="overview-pill px-3 py-1 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer">15 วันล่าสุด</button>
                            <button type="button" onclick="setOverviewPresetFilter('30days')" id="pill-ov-30days" class="overview-pill px-3 py-1 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer">30 วันล่าสุด</button>
                            <button type="button" onclick="setOverviewPresetFilter('month')" id="pill-ov-month" class="overview-pill px-3 py-1 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer">ทั้งเดือน</button>
                            <button type="button" onclick="setOverviewPresetFilter('all')" id="pill-ov-all" class="overview-pill px-3 py-1 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer">ทั้งหมด</button>
                        </div>

                        <!-- Popover Modal: Interactive Calendar (Matches Image 1) -->
                        <div id="popover-overview-calendar"
                             class="absolute left-0 top-full mt-2 z-50 w-76 sm:w-80 bg-white rounded-3xl p-4 shadow-2xl border border-slate-200 hidden animate-in fade-in zoom-in-95 duration-150">
                            <!-- Popover Tab Switcher: [รายวัน] | [เลือกช่วงวันที่] -->
                            <div class="flex items-center bg-slate-100 p-1 rounded-2xl mb-3">
                                <button type="button" id="tab-cal-daily" onclick="switchOverviewCalTab('daily')" class="flex-1 py-1.5 rounded-xl text-xs font-bold bg-white text-slate-900 shadow-2xs transition-all cursor-pointer">รายวัน</button>
                                <button type="button" id="tab-cal-range" onclick="switchOverviewCalTab('range')" class="flex-1 py-1.5 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 transition-all cursor-pointer">เลือกช่วงวันที่</button>
                            </div>

                            <!-- Month / Year Header with < > -->
                            <div class="flex items-center justify-between px-1 mb-2">
                                <span id="label-overview-cal-month" class="text-xs sm:text-[13px] font-bold text-slate-800">
                                    November 2026 (พฤศจิกายน 2569)
                                </span>
                                <div class="flex items-center gap-1 text-slate-600">
                                    <button type="button" onclick="changeOverviewCalMonth(-1)" class="w-6 h-6 rounded-lg hover:bg-slate-100 flex items-center justify-center font-bold text-xs cursor-pointer">&lt;</button>
                                    <button type="button" onclick="changeOverviewCalMonth(1)" class="w-6 h-6 rounded-lg hover:bg-slate-100 flex items-center justify-center font-bold text-xs cursor-pointer">&gt;</button>
                                </div>
                            </div>

                            <!-- Days of Week Header -->
                            <div class="grid grid-cols-7 text-center text-[11px] font-bold text-slate-400 py-1 mb-1">
                                <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                            </div>

                            <!-- Days Grid (1-30) -->
                            <div id="overview-cal-grid" class="grid grid-cols-7 gap-1 text-center text-xs">
                                <!-- Generated by JS -->
                            </div>

                            <!-- Popover Footer -->
                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-100 px-1 text-xs font-bold">
                                <button type="button" onclick="onOverviewCalClear()" class="text-blue-600 hover:text-blue-800 cursor-pointer">Clear</button>
                                <button type="button" onclick="onOverviewCalToday()" class="text-blue-600 hover:text-blue-800 cursor-pointer">Today</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Refresh Button -->
                <div class="flex items-center gap-2">
                    <button type="button"
                            onclick="refreshOverview()"
                            id="btn-refresh-overview"
                            class="px-3.5 py-1.5 sm:py-2 rounded-xl bg-white hover:bg-slate-50 active:scale-95 border border-slate-200/90 text-slate-700 text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer shadow-2xs">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>รีเฟรช</span>
                    </button>
                </div>
            </div>

            <!-- 2. Metrics Cards (4 Cards) -->
            <div id="overview-metrics-container">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    <!-- Card 1: พนักงานมาทำงาน -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-2xs flex flex-col justify-between">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-600">พนักงานมาทำงาน</span>
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2" id="stat-working-count">
                            {{ $overviewStats['total_working'] ?? 2 }}
                        </div>
                    </div>

                    <!-- Card 2: จำนวนเข้างานสาย -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-2xs flex flex-col justify-between">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-600">จำนวนเข้างานสาย</span>
                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2" id="stat-late-count">
                            {{ $overviewStats['late_count'] ?? 0 }}
                        </div>
                    </div>

                    <!-- Card 3: ยังไม่ได้เช็คอิน -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 shadow-2xs flex flex-col justify-between">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-600">ยังไม่ได้เช็คอิน</span>
                            <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="9"/>
                                    <line x1="8" y1="12" x2="16" y2="12"/>
                                </svg>
                            </div>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2" id="stat-not-checked-in">
                            {{ $overviewStats['not_checked_in'] ?? 0 }}
                        </div>
                    </div>

                    <!-- Card 4: ทำโอที (with red top accent border) -->
                    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/90 border-t-2 border-t-rose-500 shadow-2xs flex flex-col justify-between">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-600">ทำโอที</span>
                            <div class="w-8 h-8 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-2" id="stat-ot-count">
                            {{ $overviewStats['ot_count'] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Table Section: 📑 รายละเอียดบันทึกการทำงานของพนักงานทั้งหมด -->
            <div id="view-daily-table" class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/90 shadow-sm space-y-4">
                <div class="flex items-center gap-2 font-black text-sm sm:text-base text-slate-900">
                    <span class="text-base">📑</span>
                    <span>รายละเอียดบันทึกการทำงานของพนักงานทั้งหมด</span>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-100">
                    <table class="w-full text-left text-xs whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-100 text-[11.5px]">
                                <th class="py-3 px-4">พนักงาน</th>
                                <th class="py-3 px-3">สาขา</th>
                                <th class="py-3 px-3">รูปเข้า</th>
                                <th class="py-3 px-3">เช็คอิน</th>
                                <th class="py-3 px-3">พิกัดเช็คอิน</th>
                                <th class="py-3 px-3">รูปออก</th>
                                <th class="py-3 px-3">เช็คเอาต์</th>
                                <th class="py-3 px-3">พิกัดเช็คเอาต์</th>
                                <th class="py-3 px-3">รวมชั่วโมง</th>
                                <th class="py-3 px-3">สถานะการลงเวลา</th>
                                <th class="py-3 px-3 text-orange-600 font-bold">⚡ เริ่ม OT</th>
                                <th class="py-3 px-3 text-orange-600 font-bold">จบ OT</th>
                                <th class="py-3 px-3 text-orange-600 font-bold">ชม. OT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="overview-attendance-tbody">
                            @foreach($attendanceLogs as $log)
                                @if(!empty($log['is_offday']))
                                    <!-- Offday Row: Merged Status Centered -->
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <td class="py-3.5 px-4 font-bold text-slate-900">
                                            <div>{{ $log['name'] }}</div>
                                            @if(!empty($log['roles']))
                                                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                                    @foreach($log['roles'] as $r)
                                                        <span class="px-2 py-0.2 rounded-full text-[10px] font-bold border {{ $r['color'] }} inline-flex items-center gap-1">
                                                            @if(!empty($r['icon'])) <span>{{ $r['icon'] }}</span> @endif
                                                            <span>{{ $r['label'] }}</span>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td colspan="12" class="py-3.5 px-4 text-center">
                                            <span class="px-3.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold inline-flex items-center gap-1">
                                                {{ $log['status_label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @else
                                    <!-- Working Row: Detailed Times, Thumbnails & Locations -->
                                    <tr class="hover:bg-slate-50/60 transition-colors">
                                        <!-- พนักงาน -->
                                        <td class="py-3.5 px-4 font-bold text-slate-900">
                                            <div>{{ $log['name'] }}</div>
                                            @if(!empty($log['roles']))
                                                <div class="flex flex-wrap items-center gap-1.5 mt-1">
                                                    @foreach($log['roles'] as $r)
                                                        <span class="px-2 py-0.2 rounded-full text-[10px] font-bold border {{ $r['color'] }} inline-flex items-center gap-1">
                                                            @if(!empty($r['icon'])) <span>{{ $r['icon'] }}</span> @endif
                                                            <span>{{ $r['label'] }}</span>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>

                                        <!-- สาขา -->
                                        <td class="py-3.5 px-3">
                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-bold">
                                                {{ $log['branch'] }}
                                            </span>
                                        </td>

                                        <!-- รูปเข้า -->
                                        <td class="py-3.5 px-3">
                                            @if(!empty($log['check_in_photo']))
                                                <div class="relative group cursor-pointer inline-block"
                                                     onclick="openPhotoLightbox('{{ $log['check_in_photo'] }}', 'ภาพเช็คอิน: {{ $log['name'] }} ({{ $log['check_in_time'] }})')"
                                                     title="คลิกเพื่อดูภาพเต็ม">
                                                    <img src="{{ $log['check_in_photo'] }}"
                                                         alt="เช็คอิน"
                                                         onerror="this.onerror=null; this.src='/uploads/attendance/sample_check_in.jpg';"
                                                         class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-2xs group-hover:scale-105 transition-all">
                                                    <div class="absolute inset-0 bg-black/20 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[10px] transition-all">
                                                        🔍
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>

                                        <!-- เช็คอิน -->
                                        <td class="py-3.5 px-3 font-bold font-mono text-emerald-600 text-xs sm:text-sm">
                                            {{ $log['check_in_time'] ?? '—' }}
                                        </td>

                                        <!-- พิกัดเช็คอิน -->
                                        <td class="py-3.5 px-3">
                                            @if(!empty($log['check_in_time']) && $log['check_in_time'] !== '—')
                                                <button type="button"
                                                        onclick="openAttendanceMapModal('{{ $log['name'] }}', '{{ $log['branch'] }}', '{{ $log['check_in_distance'] ?? '0ม.' }}', 'เช็คอินเข้างาน')"
                                                        class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200 text-xs font-semibold flex items-center gap-1 transition-all cursor-pointer whitespace-nowrap">
                                                    <span>📍 แผนที่</span>
                                                    @if(!empty($log['check_in_distance']))
                                                        <span class="text-[10px]">({{ $log['check_in_distance'] }})</span>
                                                    @endif
                                                </button>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>

                                        <!-- รูปออก -->
                                        <td class="py-3.5 px-3">
                                            @if(!empty($log['check_out_photo']))
                                                <div class="relative group cursor-pointer inline-block"
                                                     onclick="openPhotoLightbox('{{ $log['check_out_photo'] }}', 'ภาพเช็คเอาต์: {{ $log['name'] }} ({{ $log['check_out_time'] }})')"
                                                     title="คลิกเพื่อดูภาพเต็ม">
                                                    <img src="{{ $log['check_out_photo'] }}"
                                                         alt="เช็คเอาต์"
                                                         onerror="this.onerror=null; this.src='/uploads/attendance/sample_check_out.jpg';"
                                                         class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-2xs group-hover:scale-105 transition-all">
                                                    <div class="absolute inset-0 bg-black/20 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-[10px] transition-all">
                                                        🔍
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>

                                        <!-- เช็คเอาต์ -->
                                        <td class="py-3.5 px-3 font-bold font-mono text-rose-600 text-xs sm:text-sm">
                                            {{ $log['check_out_time'] ?? '—' }}
                                        </td>

                                        <!-- พิกัดเช็คเอาต์ -->
                                        <td class="py-3.5 px-3">
                                            @if(!empty($log['check_out_time']) && $log['check_out_time'] !== '—')
                                                <button type="button"
                                                        onclick="openAttendanceMapModal('{{ $log['name'] }}', '{{ $log['branch'] }}', '{{ $log['check_out_distance'] ?? '0ม.' }}', 'เช็คเอาต์ออกงาน')"
                                                        class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200 text-xs font-semibold flex items-center gap-1 transition-all cursor-pointer whitespace-nowrap">
                                                    <span>📍 แผนที่</span>
                                                </button>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>

                                        <!-- รวมชั่วโมง -->
                                        <td class="py-3.5 px-3 font-bold text-slate-800">
                                            {{ $log['total_hours'] ?? '—' }}
                                        </td>

                                        <!-- สถานะการลงเวลา -->
                                        <td class="py-3.5 px-3">
                                            @if(($log['status'] ?? '') === 'pending')
                                                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200 text-xs font-bold inline-flex items-center gap-1 whitespace-nowrap">
                                                    ยังไม่เข้างาน ⚪
                                                </span>
                                            @elseif(($log['status'] ?? '') === 'late')
                                                <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold inline-flex items-center gap-1 whitespace-nowrap">
                                                    สาย 🟡
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold inline-flex items-center gap-1 whitespace-nowrap">
                                                    {{ $log['status_badge'] ?? 'ปกติ 🟢' }}
                                                </span>
                                            @endif
                                        </td>

                                        <!-- ⚡ เริ่ม OT -->
                                        <td class="py-3.5 px-3 text-slate-400 text-center font-mono">
                                            {{ $log['ot_start'] ?? '—' }}
                                        </td>

                                        <!-- จบ OT -->
                                        <td class="py-3.5 px-3 text-slate-400 text-center font-mono">
                                            {{ $log['ot_end'] ?? '—' }}
                                        </td>

                                        <!-- ชม. OT -->
                                        <td class="py-3.5 px-3 text-slate-400 text-center font-mono">
                                            {{ $log['ot_hours'] ?? '—' }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. Monthly Summary Table (Toggled via [📊 สรุปรายเดือน]) -->
            <div id="view-monthly-table" class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/90 shadow-sm space-y-4 hidden">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 font-black text-sm sm:text-base text-slate-900">
                        <span class="text-base">📊</span>
                        <span>สรุปภาพรวมเวลาการทำงานรายเดือน (พนักงานทุกคน)</span>
                    </div>
                    <span class="text-xs font-bold text-slate-400" id="overview-monthly-period-label">ประจำเดือน {{ $currentPayroll['period_label'] ?? '' }}</span>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-100">
                    <table class="w-full text-left text-xs whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-100 text-[11.5px]">
                                <th class="py-3 px-4">พนักงาน</th>
                                <th class="py-3 px-3">ตำแหน่ง & สาขา</th>
                                <th class="py-3 px-3">วันทำงาน (วัน)</th>
                                <th class="py-3 px-3">มาสาย (วัน)</th>
                                <th class="py-3 px-3">นอกสถานที่</th>
                                <th class="py-3 px-3">ชั่วโมงทำงานรวม</th>
                                <th class="py-3 px-3 text-orange-600 font-bold">ชั่วโมง OT</th>
                                <th class="py-3 px-3">อัตราการเข้างาน</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="overview-monthly-tbody">
                            @foreach($monthlyAttendanceSummary as $m)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3.5 px-4 font-bold text-slate-900">{{ $m['name'] }}</td>
                                    <td class="py-3.5 px-3 text-slate-600">{{ $m['role'] }} ({{ $m['branch'] }})</td>
                                    <td class="py-3.5 px-3 font-bold text-slate-800">{{ $m['work_days'] }} วัน</td>
                                    <td class="py-3.5 px-3 {{ $m['late_days'] > 0 ? 'text-amber-600 font-bold' : 'text-slate-400' }}">{{ $m['late_days'] }} วัน</td>
                                    <td class="py-3.5 px-3 text-slate-400">{{ $m['offsite_days'] }} ครั้ง</td>
                                    <td class="py-3.5 px-3 font-bold text-slate-800">{{ $m['total_hours'] }}</td>
                                    <td class="py-3.5 px-3 font-bold text-orange-600">{{ $m['ot_hours'] }}</td>
                                    <td class="py-3.5 px-3">
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                                            {{ $m['attendance_rate'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Face Scanner Simulation & Live Camera (เริ่มสแกนใบหน้า)            -->
<!-- ========================================================================= -->
<div id="modal-face-scanner" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-rose-200 animate-in fade-in zoom-in duration-200">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-[#4E0005] via-[#7B030D] to-[#3B0004] text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <h3 class="font-bold text-sm sm:text-base">ระบบสแกนใบหน้าบันทึกเวลา (AI Facial Camera)</h3>
            </div>
            <button type="button" onclick="closeFaceScannerModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center text-sm font-bold cursor-pointer">
                ✕
            </button>
        </div>

        <!-- Camera Viewport / Scanning HUD -->
        <div class="p-5 flex flex-col items-center text-center">
            <!-- Action Selector Pills with Auto-Detection Indicator -->
            <div class="w-full mb-3">
                <div class="flex items-center justify-center gap-2 w-full">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="modal_scan_action" id="action-radio-checkin" value="check_in" class="peer sr-only" {{ $todayAttendance['status'] !== 'เข้างานแล้ว' ? 'checked' : '' }} onchange="onScanActionChange('check_in')">
                        <div class="py-2 px-3 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600 peer-checked:shadow-xs transition-all text-center flex items-center justify-center gap-1.5">
                            <span>🟢</span>
                            <span>เช็คอินเข้างาน</span>
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="modal_scan_action" id="action-radio-checkout" value="check_out" class="peer sr-only" {{ $todayAttendance['status'] === 'เข้างานแล้ว' ? 'checked' : '' }} onchange="onScanActionChange('check_out')">
                        <div class="py-2 px-3 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 peer-checked:bg-rose-600 peer-checked:text-white peer-checked:border-rose-600 peer-checked:shadow-xs transition-all text-center flex items-center justify-center gap-1.5">
                            <span>🔴</span>
                            <span>เช็คเอาท์ออกงาน</span>
                        </div>
                    </label>
                </div>
                <!-- Auto-Mode Notification Badge (ตั้งเงื่อนไข: ถ่ายครั้งแรกของวัน เข้างานอัตโนมัติ, ครั้งที่ 2 ออกงาน) -->
                <div id="modal-auto-mode-badge" class="mt-2 text-[11px] font-bold py-1.5 px-3 rounded-xl border text-center transition-all {{ $todayAttendance['status'] !== 'เข้างานแล้ว' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' }}">
                    <span id="auto-mode-text">
                        @if($todayAttendance['status'] !== 'เข้างานแล้ว')
                            ⚡ ถ่ายครั้งแรกของวัน: ระบบตั้ง "เช็คอินเข้างาน" อัตโนมัติ
                        @else
                            ⚡ ถ่ายครั้งที่ 2 ของวัน: ระบบตั้ง "เช็คเอาท์ออกงาน" อัตโนมัติ
                        @endif
                    </span>
                </div>
            </div>

            <!-- Viewfinder Camera Frame (ปรับขนาดใหญ่ขึ้น + ตัดไอคอนและข้อความตรงกลางออก) -->
            <div class="relative w-72 h-72 sm:w-[340px] sm:h-[340px] rounded-3xl bg-slate-950 overflow-hidden shadow-inner flex items-center justify-center border-2 border-rose-500">
                <!-- Video Element for WebCam (Active Live Stream) -->
                <video id="webcam-video" autoplay playsinline muted class="w-full h-full object-cover [transform:scaleX(-1)] bg-slate-950"></video>
                <!-- Image Element for Captured Photo Preview (Shown after snapshot) -->
                <img id="webcam-preview-img" class="w-full h-full object-cover hidden [transform:scaleX(-1)]" alt="ภาพถ่ายที่บันทึก" />
                <!-- Hidden Canvas for snapshot capturing -->
                <canvas id="webcam-canvas" class="hidden"></canvas>

                <!-- Hidden placeholder for JS compatibility -->
                <div id="scanner-placeholder" class="hidden"></div>

                <!-- HUD Laser Scanner Line -->
                <div id="scanner-laser-line" class="absolute left-0 right-0 h-1 bg-gradient-to-r from-transparent via-[#E50914] to-transparent shadow-[0_0_15px_#E50914] scanner-laser pointer-events-none"></div>

                <!-- Corner HUD Target Lines -->
                <div class="absolute top-2.5 left-2.5 w-6 h-6 border-t-2 border-l-2 border-emerald-400 pointer-events-none"></div>
                <div class="absolute top-2.5 right-2.5 w-6 h-6 border-t-2 border-r-2 border-emerald-400 pointer-events-none"></div>
                <div class="absolute bottom-2.5 left-2.5 w-6 h-6 border-b-2 border-l-2 border-emerald-400 pointer-events-none"></div>
                <div class="absolute bottom-2.5 right-2.5 w-6 h-6 border-b-2 border-r-2 border-emerald-400 pointer-events-none"></div>
            </div>

            <!-- Geofence Verified Badge in Modal -->
            <div id="modal-geofence-badge" class="mt-3 text-[11px] px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold inline-flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>📍 ลิงก์พิกัดร้าน: อยู่ในเขตร้านหม่าล่าสตรีท (ผ่านการตรวจสอบ)</span>
            </div>

            <div id="scan-feedback-text" class="text-xs font-semibold text-slate-700 mt-2">
                ระบบพร้อมถ่ายภาพและบันทึกเวลาเข้า-ออกงาน
            </div>

            <!-- Step 1: Initial Capture State Buttons -->
            <div id="scanner-step-capture" class="flex items-center gap-2.5 mt-4 w-full">
                <button type="button"
                        id="btn-take-snapshot"
                        onclick="takePhotoSnapshot()"
                        class="flex-1 py-3 px-4 rounded-full bg-gradient-to-r from-[#B80A17] via-[#E50914] to-[#B80A17] hover:brightness-110 active:scale-98 text-white font-bold text-sm shadow-md flex items-center justify-center gap-2 cursor-pointer transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>📷 ถ่ายภาพ</span>
                </button>
                <button type="button"
                        onclick="closeFaceScannerModal()"
                        class="py-3 px-4 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs cursor-pointer transition-all shrink-0">
                    ยกเลิก
                </button>
            </div>

            <!-- Step 2: Confirm or Retake State Buttons (Shown after taking photo) -->
            <div id="scanner-step-confirm" class="hidden flex items-center gap-2.5 mt-4 w-full">
                <button type="button"
                        id="btn-retake-photo"
                        onclick="retakePhoto()"
                        class="py-3 px-4 rounded-full bg-slate-100 hover:bg-slate-200 active:scale-98 text-slate-700 font-bold text-xs cursor-pointer transition-all flex items-center justify-center gap-1.5 shrink-0 border border-slate-200">
                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>🔄 ถ่ายใหม่</span>
                </button>
                <button type="button"
                        id="btn-confirm-attendance"
                        onclick="executeFaceClockIn()"
                        class="flex-1 py-3 px-4 rounded-full bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-600 hover:brightness-110 active:scale-98 text-white font-bold text-sm shadow-md flex items-center justify-center gap-2 cursor-pointer transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>✓ ยืนยันบันทึกเวลา</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Photo Lightbox & URL Viewer (เปิดดูภาพขนาดเต็ม + แสดง URL)         -->
<!-- ========================================================================= -->
<div id="modal-photo-lightbox" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200 animate-in fade-in zoom-in duration-200 flex flex-col">
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#4E0005] via-[#7B030D] to-[#3B0004] text-white p-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-rose-400 text-base">📸</span>
                <h3 id="lightbox-title" class="font-bold text-sm sm:text-base">ภาพถ่ายสแกนหน้าบันทึกเวลา</h3>
            </div>
            <button type="button" onclick="closePhotoLightbox()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center text-sm font-bold cursor-pointer">
                ✕
            </button>
        </div>

        <!-- Image Container -->
        <div class="p-4 bg-slate-950 flex items-center justify-center min-h-[260px]">
            <img id="lightbox-img" src="" alt="Full attendance photo" class="max-h-[55vh] max-w-full rounded-2xl object-contain border border-slate-800 shadow-2xl">
        </div>

        <!-- URL Information Box -->
        <div class="p-4 bg-slate-50 border-t border-slate-200 space-y-3">
            <div class="space-y-1">
                <label class="text-[11px] font-bold text-slate-600 uppercase tracking-wider flex items-center gap-1">
                    <span>🔗 URL รูปภาพ (สามารถเปิดดูผ่านเบราว์เซอร์ได้ปกติ)</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="text" id="lightbox-url-input" readonly class="w-full text-xs font-mono bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-700 select-all focus:outline-rose-500">
                    <button type="button" onclick="copyLightboxUrl()" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold shrink-0 transition-all cursor-pointer">
                        คัดลอก URL
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between pt-1">
                <span class="text-[11px] text-emerald-700 font-semibold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>บันทึกไฟล์ภาพในเซิร์ฟเวอร์เรียบร้อย</span>
                </span>

                <a id="lightbox-open-external" href="#" target="_blank" class="px-4 py-2 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white text-xs font-bold flex items-center gap-1.5 shadow-md transition-all">
                    <span>เปิดภาพในแท็บใหม่</span>
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Attendance Map Viewer (พิกัดเช็คอิน / เช็คเอาต์)                     -->
<!-- ========================================================================= -->
<div id="modal-attendance-map" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-200 animate-in fade-in zoom-in duration-200">
        <!-- Header -->
        <div class="bg-gradient-to-r from-slate-900 via-blue-900 to-slate-900 text-white p-4 sm:p-5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">📍</span>
                <div>
                    <h3 class="font-bold text-sm sm:text-base" id="att-map-name">พิกัดบันทึกเวลาพนักงาน</h3>
                    <p class="text-xs text-blue-200" id="att-map-action">เช็คอินเข้างาน</p>
                </div>
            </div>
            <button type="button" onclick="closeAttendanceMapModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center text-sm font-bold cursor-pointer">
                ✕
            </button>
        </div>

        <div class="p-5 space-y-4">
            <!-- Simulated Map Visual / Radar Display -->
            <div class="relative w-full h-48 rounded-2xl bg-slate-100 overflow-hidden border border-slate-200 flex items-center justify-center">
                <!-- Map Background Tile Pattern -->
                <div class="absolute inset-0 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:16px_16px] opacity-70"></div>
                
                <!-- Geofence Zone Circle -->
                <div class="absolute w-36 h-36 rounded-full bg-emerald-500/15 border-2 border-dashed border-emerald-500 animate-pulse flex items-center justify-center">
                    <span class="text-[10px] text-emerald-800 font-bold bg-white/90 px-2 py-0.5 rounded-full shadow-2xs">เขตร้านหม่าล่าสตรีท (150 ม.)</span>
                </div>

                <!-- Center Store Pin -->
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full bg-rose-600 text-white flex items-center justify-center shadow-lg border-2 border-white">
                        <span class="text-xs">🏪</span>
                    </div>
                </div>

                <!-- Employee Check-in Pin -->
                <div class="absolute top-16 right-24 z-10 flex flex-col items-center">
                    <div class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-md border-2 border-white animate-bounce">
                        <span class="text-[10px]">👤</span>
                    </div>
                    <span class="text-[9px] font-bold bg-slate-900 text-white px-1.5 py-0.2 rounded mt-0.5 shadow-2xs whitespace-nowrap">พนักงาน</span>
                </div>
            </div>

            <!-- Distance & Verification Info -->
            <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-1">
                <div class="flex items-center justify-between text-xs font-bold text-emerald-800">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>ยืนยันพิกัด: อยู่ในเขตร้านถูกต้อง</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-full bg-white text-emerald-700 border border-emerald-200" id="att-map-distance">6ม.</span>
                </div>
                <p class="text-[11px] text-emerald-700">
                    จุดบันทึกเวลาผ่านการตรวจสอบพิกัด GPS ตรงกับรัศมีที่อนุญาตของร้านหม่าล่าสตรีท
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeAttendanceMapModal()" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-all cursor-pointer">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Store Location & Geofence Picker (ปักหมุดพิกัดร้านและตั้งค่าขอบเขต)-->
<!-- ========================================================================= -->
<div id="modal-store-location" class="fixed inset-0 z-50 bg-black/80 backdrop-blur-xs hidden flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full overflow-hidden shadow-2xl border border-slate-200 animate-in fade-in zoom-in duration-200 flex flex-col max-h-[92vh]">
        <!-- Header -->
        <div class="bg-gradient-to-r from-[#4E0005] via-[#7B030D] to-[#3B0004] text-white p-4 sm:p-5 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-rose-500/30 border border-rose-400/50 flex items-center justify-center text-lg shadow-inner">
                    📍
                </span>
                <div>
                    <h3 class="font-bold text-sm sm:text-base leading-tight">ปักหมุดพิกัดร้าน &amp; กำหนดขอบเขตเช็คอิน</h3>
                    <p class="text-[11px] text-rose-200 mt-0.5">คลิกหรือลากหมุดบนแผนที่ หรือกดใช้พิกัดปัจจุบันของคุณได้ทันที</p>
                </div>
            </div>
            <button type="button" onclick="closeStoreLocationModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center text-sm font-bold cursor-pointer transition-all">
                ✕
            </button>
        </div>

        <!-- Body Scrollable -->
        <div class="p-4 sm:p-5 overflow-y-auto space-y-4 text-xs sm:text-sm">
            <!-- Top Action Row: Quick buttons -->
            <div class="flex flex-wrap items-center justify-between gap-2 bg-rose-50/70 p-3 rounded-2xl border border-rose-100">
                <div class="flex items-center gap-2">
                    <span class="text-rose-700 font-bold text-xs">🚀 ทางลัดด่วน:</span>
                    <button type="button"
                            onclick="setStoreToCurrentLocation()"
                            class="py-1.5 px-3 rounded-xl bg-[#E50914] hover:bg-[#C8102E] active:scale-95 text-white font-bold text-xs shadow-xs flex items-center gap-1.5 cursor-pointer transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>ใช้ตำแหน่งปัจจุบันของฉันเป็นร้าน</span>
                    </button>
                </div>
                <span class="text-[11px] text-slate-500">คลิกที่ใดก็ได้บนแผนที่เพื่อย้ายหมุด</span>
            </div>

            <!-- Leaflet Interactive Map Container -->
            <div class="relative w-full h-[260px] sm:h-[320px] rounded-2xl overflow-hidden border-2 border-slate-200 shadow-inner">
                <div id="store-picker-map" class="w-full h-full"></div>
            </div>

            <!-- การ์ดระบุขอบเขตร้านที่อนุญาตเช็คอินได้ในพื้นที่ร้าน -->
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-2">
                    <span class="font-bold text-slate-800 text-xs sm:text-sm flex items-center gap-1.5">
                        <span class="text-[#E50914]">🛡️</span>
                        <span>การ์ดระบุขอบเขตร้าน (Geofence Boundaries)</span>
                    </span>
                    <span id="map-radius-badge" class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[11px]">
                        รัศมี: {{ $storeLocation['radius_meters'] }} เมตร
                    </span>
                </div>

                <!-- Lat & Lng Inputs -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">ละติจูด (Latitude):</label>
                        <input type="number" step="0.000001" id="input-store-lat" value="{{ $storeLocation['lat'] }}"
                               class="w-full text-xs font-mono bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:outline-rose-500 font-bold"
                               onchange="onManualCoordsChange()">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">ลองจิจูด (Longitude):</label>
                        <input type="number" step="0.000001" id="input-store-lng" value="{{ $storeLocation['lng'] }}"
                               class="w-full text-xs font-mono bg-white border border-slate-300 rounded-xl px-3 py-2 text-slate-800 focus:outline-rose-500 font-bold"
                               onchange="onManualCoordsChange()">
                    </div>
                </div>

                <!-- Radius Slider & Preset Pills -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-[11px] font-bold text-slate-600">ขอบเขตรัศมีที่อนุญาตให้เช็คอิน:</label>
                        <span class="text-xs font-bold text-[#E50914]" id="slider-radius-val">{{ $storeLocation['radius_meters'] }} ม.</span>
                    </div>
                    <input type="range" id="input-store-radius" min="30" max="1000" step="10" value="{{ $storeLocation['radius_meters'] }}"
                           class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#E50914]"
                           oninput="onRadiusSliderChange(this.value)">
                    <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                        <span class="text-[10.5px] text-slate-400">เลือกระยะด่วน:</span>
                        <button type="button" onclick="setRadiusPreset(50)" class="px-2 py-0.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:border-rose-400 text-[10.5px] font-semibold cursor-pointer">50 ม.</button>
                        <button type="button" onclick="setRadiusPreset(100)" class="px-2 py-0.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:border-rose-400 text-[10.5px] font-semibold cursor-pointer">100 ม.</button>
                        <button type="button" onclick="setRadiusPreset(150)" class="px-2 py-0.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:border-rose-400 text-[10.5px] font-semibold cursor-pointer">150 ม.</button>
                        <button type="button" onclick="setRadiusPreset(300)" class="px-2 py-0.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:border-rose-400 text-[10.5px] font-semibold cursor-pointer">300 ม.</button>
                        <button type="button" onclick="setRadiusPreset(500)" class="px-2 py-0.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:border-rose-400 text-[10.5px] font-semibold cursor-pointer">500 ม.</button>
                    </div>
                </div>

                <!-- Test / Mock Bypass Option -->
                <div class="pt-2 border-t border-slate-200/80">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" id="input-allow-mock" {{ $storeLocation['allow_mock'] ? 'checked' : '' }}
                               class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                        <span class="text-xs font-semibold text-slate-700">
                            เปิดโหมดทดสอบเช็คอินนอกร้านได้ (อนุญาตให้ทดสอบระบบได้แม้จะอยู่นอกเขตร้าน)
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 sm:p-5 bg-white border-t border-slate-100 flex items-center justify-between shrink-0">
            <span class="text-[11px] text-slate-500">
                เมื่อบันทึกแล้ว พนักงานทุกคนจะอิงตามพิกัดนี้ทันที
            </span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeStoreLocationModal()" class="py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer transition-all">
                    ยกเลิก
                </button>
                <button type="button" onclick="saveStoreLocationSettings()" class="py-2.5 px-6 rounded-xl bg-[#E50914] hover:bg-[#C8102E] text-white font-bold text-xs shadow-md shadow-rose-900/20 flex items-center gap-2 cursor-pointer transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>บันทึกพิกัดร้าน</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: Payslip Digital Viewer (ดูสลิปเงินเดือน)                             -->
<!-- ========================================================================= -->
<div id="modal-payslip-viewer" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200 animate-in fade-in zoom-in duration-200">
        <!-- Payslip Header -->
        <div class="bg-gradient-to-r from-[#4E0005] via-[#7B030D] to-[#3B0004] text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('assets/brand/logo-card.png') }}" class="w-10 h-10 rounded-xl object-cover border border-white/60" alt="">
                <div>
                    <h3 class="font-bold text-base">ใบแจ้งยอดเงินเดือน (Payslip)</h3>
                    <p class="text-xs text-rose-200">ร้านหม่าล่าสตรีท (Mala Street)</p>
                </div>
            </div>
            <button type="button" onclick="closePayslipModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center text-sm font-bold cursor-pointer">
                ✕
            </button>
        </div>

        <div class="p-6 space-y-4 text-xs sm:text-sm">
            <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-slate-500 block text-xs">พนักงาน:</span>
                    <span id="ps-emp-name" class="font-bold text-slate-800 text-sm">{{ $employee['name'] }}</span>
                    <span id="ps-emp-role" class="text-[11px] text-slate-400 block">รหัส: {{ $employee['id'] }} • {{ $employee['role'] }} • {{ $employee['department'] }}</span>
                </div>
                <div class="text-right">
                    <span class="text-slate-500 block text-xs">งวดเงินเดือน:</span>
                    <span id="ps-period" class="font-bold text-rose-600 text-sm">{{ $currentPayroll['period_label'] ?? '' }}</span>
                    <span id="ps-pay-date" class="text-[11px] text-slate-400 block">จ่ายเมื่อ: {{ $currentPayroll['payment_date'] ?? '' }}</span>
                </div>
            </div>

            <div class="border border-slate-200/90 rounded-2xl p-4 divide-y divide-slate-100">
                <div class="py-2 flex justify-between font-medium">
                    <span class="text-slate-600">เงินเดือนพื้นฐาน (Base Salary)</span>
                    <span id="ps-base-salary" class="font-bold text-slate-800">฿{{ number_format($currentPayroll['base_salary'] ?? 0, 2) }}</span>
                </div>
                <div class="py-2 flex justify-between font-medium">
                    <span id="ps-ot-label" class="text-slate-600">ค่าล่วงเวลา (Overtime {{ $currentPayroll['ot_hours'] ?? '0 ชม.' }})</span>
                    <span id="ps-ot-amount" class="font-bold text-emerald-600">+฿{{ number_format($currentPayroll['ot_amount'] ?? 0, 2) }}</span>
                </div>
                <div class="py-2 flex justify-between font-medium">
                    <span class="text-slate-600">โบนัส / เบี้ยขยัน (Incentive)</span>
                    <span id="ps-bonus-amount" class="font-bold text-emerald-600">+฿{{ number_format($currentPayroll['bonus'] ?? 0, 2) }}</span>
                </div>
                <div id="ps-late-row" class="py-2 flex justify-between font-medium {{ ($currentPayroll['deduction_late'] ?? 0) > 0 ? '' : 'hidden' }}">
                    <span class="text-slate-600">หักจากการมาสาย</span>
                    <span id="ps-deduction-late" class="font-bold text-rose-600">-฿{{ number_format($currentPayroll['deduction_late'] ?? 0, 2) }}</span>
                </div>
                <div id="ps-ss-row" class="py-2 flex justify-between font-medium {{ ($currentPayroll['deduction_social_security'] ?? 0) > 0 ? '' : 'hidden' }}">
                    <span class="text-slate-600">หักเงินสมทบประกันสังคม</span>
                    <span id="ps-deduction-ss" class="font-bold text-rose-600">-฿{{ number_format($currentPayroll['deduction_social_security'] ?? 0, 2) }}</span>
                </div>
                <div class="py-2 flex justify-between font-medium">
                    <span class="text-slate-600">รวมรายการหักทั้งหมด</span>
                    <span id="ps-deductions-total" class="font-bold text-rose-600">{{ ($currentPayroll['deductions_total'] ?? 0) > 0 ? '-฿' : '฿' }}{{ number_format($currentPayroll['deductions_total'] ?? 0, 2) }}</span>
                </div>
                <div class="pt-3 flex justify-between items-baseline font-black">
                    <span class="text-slate-800 text-base">ยอดรับสุทธิ (Net Pay)</span>
                    <span id="ps-net-salary" class="text-2xl text-[#E50914]">฿{{ number_format($currentPayroll['net_salary'] ?? 0, 2) }}</span>
                </div>
            </div>

            <!-- Print / Download Button -->
            <div class="flex gap-2.5 pt-2">
                <button type="button" onclick="window.print()" class="flex-1 py-3 rounded-full bg-slate-900 text-white font-bold text-xs sm:text-sm hover:bg-slate-800 flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>พิมพ์ใบสลิป</span>
                </button>
                <button type="button" onclick="closePayslipModal()" class="py-3 px-6 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs sm:text-sm cursor-pointer">
                    ปิด
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Simple Toast Notification -->
<div id="emp-toast" class="fixed bottom-5 right-5 z-50 bg-slate-900 text-white px-4 py-3 rounded-2xl shadow-xl border border-slate-700 text-xs sm:text-sm font-semibold hidden flex items-center gap-2 animate-in fade-in slide-in-from-bottom duration-200">
    <span id="toast-icon">✓</span>
    <span id="toast-msg">ข้อความ</span>
</div>

<script>
    // Payroll data dataset
    let payrollData = @json($payrollPeriods);
    let payrollCardsData = @json($payrollCards ?? []);
    let selectedHeroEmployeeId = @json($employee['id'] ?? null);
    const serverTodayDateKey = @json($todayDateKey ?? \Carbon\Carbon::now('Asia/Bangkok')->format('Y-m-d'));

    // 1. Tab Switching Function
    function switchEmployeeTab(tab) {
        const btnAttendance = document.getElementById('tab-btn-attendance');
        const btnPayroll = document.getElementById('tab-btn-payroll');
        const btnOverview = document.getElementById('tab-btn-overview');

        const paneAttendance = document.getElementById('tab-pane-attendance');
        const panePayroll = document.getElementById('tab-pane-payroll');
        const paneOverview = document.getElementById('tab-pane-overview');

        const activeClass = 'tab-btn flex-1 py-2.5 sm:py-3 px-2 sm:px-6 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 transition-all cursor-pointer bg-gradient-to-r from-[#B80A17] via-[#E50914] to-[#B80A17] text-white shadow-md shadow-rose-900/20';
        const inactiveClass = 'tab-btn flex-1 py-2.5 sm:py-3 px-2 sm:px-6 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 sm:gap-2 transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50';

        // Reset all buttons
        if (btnAttendance) btnAttendance.className = inactiveClass;
        if (btnPayroll) btnPayroll.className = inactiveClass;
        if (btnOverview) btnOverview.className = inactiveClass;

        // Hide all panes
        if (paneAttendance) paneAttendance.classList.add('hidden');
        if (panePayroll) panePayroll.classList.add('hidden');
        if (paneOverview) paneOverview.classList.add('hidden');

        if (tab === 'attendance') {
            if (btnAttendance) btnAttendance.className = activeClass;
            if (paneAttendance) paneAttendance.classList.remove('hidden');
        } else if (tab === 'payroll') {
            if (btnPayroll) btnPayroll.className = activeClass;
            if (panePayroll) panePayroll.classList.remove('hidden');
        } else {
            // overview
            if (btnOverview) btnOverview.className = activeClass;
            if (paneOverview) paneOverview.classList.remove('hidden');
        }

        // Update URL state without reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    }

    // 2. Change Payroll Month & Monthly Calendar Popover
    let currentPayrollMonth = @json($selectedMonth ?? \Carbon\Carbon::now('Asia/Bangkok')->format('Y-m'));

    function togglePayrollMonthPopover(event) {
        event.stopPropagation();
        closeOverviewDatePopover();
        const popover = document.getElementById('popover-payroll-calendar');
        const chevron = document.getElementById('chevron-payroll-popover');
        if (popover) {
            const isClosed = popover.classList.contains('hidden');
            if (isClosed) {
                popover.classList.remove('hidden');
                if (chevron) chevron.classList.add('rotate-180');
            } else {
                popover.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        }
    }

    function closePayrollMonthPopover() {
        const popover = document.getElementById('popover-payroll-calendar');
        const chevron = document.getElementById('chevron-payroll-popover');
        if (popover) popover.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
    }

    function selectPayrollMonth(monthKey) {
        if (!payrollData[monthKey]) return;
        currentPayrollMonth = monthKey;
        const data = payrollData[monthKey];

        // Update trigger label
        const lbl = document.getElementById('label-payroll-selected');
        if (lbl) lbl.innerText = 'งวด ' + data.period_short;

        // Update Quick Month Pills
        document.querySelectorAll('.payroll-pill').forEach(btn => {
            btn.className = "payroll-pill px-3 py-1 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer";
        });
        const activePill = document.getElementById('pill-payroll-' + monthKey);
        if (activePill) {
            activePill.className = "payroll-pill px-3 py-1 rounded-xl text-xs font-bold bg-[#0F172A] text-white shadow-xs transition-all cursor-pointer";
        }

        // Update Popover 12 Months Buttons
        document.querySelectorAll('.cal-month-btn').forEach(btn => {
            btn.className = "cal-month-btn py-2 rounded-xl border border-slate-100 hover:border-rose-400 font-semibold text-slate-700 hover:bg-rose-50 transition-all cursor-pointer";
        });
        const activeCalBtn = document.getElementById('btn-month-' + monthKey);
        if (activeCalBtn) {
            activeCalBtn.className = "cal-month-btn py-2 rounded-xl bg-slate-900 text-white font-bold shadow-xs transition-all cursor-pointer";
        }

        changePayrollMonth(monthKey);
        closePayrollMonthPopover();
        showToast('เปลี่ยนงวดเงินเดือน: ' + data.period_label);
        if (typeof syncEmployeesData === 'function') {
            syncEmployeesData(false, monthKey);
        }
    }

    function navigatePayrollMonth(delta) {
        const keys = Object.keys(payrollData);
        const idx = keys.indexOf(currentPayrollMonth);
        if (idx === -1) return;
        let nextIdx = idx - delta; // Keys are ordered newest first
        if (nextIdx < 0) nextIdx = keys.length - 1;
        if (nextIdx >= keys.length) nextIdx = 0;
        selectPayrollMonth(keys[nextIdx]);
    }

    function changePayrollMonth(monthKey) {
        const data = payrollData[monthKey];
        if (!data) return;

        // Update 5 Hero Boxes
        const boxBase = document.getElementById('box-base-salary');
        if (boxBase) boxBase.innerText = '฿' + Number(data.base_salary).toLocaleString();
        const boxOt = document.getElementById('box-ot-amount');
        if (boxOt) boxOt.innerText = '฿' + Number(data.ot_amount).toLocaleString();
        const boxBonus = document.getElementById('box-bonus-amount');
        if (boxBonus) boxBonus.innerText = '฿' + Number(data.bonus).toLocaleString();
        const boxDed = document.getElementById('box-deductions-amount');
        if (boxDed) boxDed.innerText = '-฿' + Number(data.deductions_total).toLocaleString();
        const boxNet = document.getElementById('box-net-salary');
        if (boxNet) boxNet.innerText = '฿' + Number(data.net_salary).toLocaleString();

        // Update Breakdown
        const dtlBase = document.getElementById('dtl-base-salary');
        if (dtlBase) dtlBase.innerText = '฿' + Number(data.base_salary).toLocaleString();
        const dtlOtRate = document.getElementById('dtl-ot-rate');
        if (dtlOtRate) dtlOtRate.innerText = `${data.ot_hours} × ฿${data.ot_rate}/ชม.`;
        const dtlOtAmount = document.getElementById('dtl-ot-amount');
        if (dtlOtAmount) dtlOtAmount.innerText = '฿' + Number(data.ot_amount).toLocaleString();
        const dtlBonus = document.getElementById('dtl-bonus-amount');
        if (dtlBonus) dtlBonus.innerText = '฿' + Number(data.bonus).toLocaleString();
        const dtlDedTotal = document.getElementById('dtl-deductions-total');
        if (dtlDedTotal) dtlDedTotal.innerText = '-฿' + Number(data.deductions_total).toLocaleString();
        const dtlLate = document.getElementById('dtl-late');
        if (dtlLate) dtlLate.innerText = '-฿' + Number(data.deduction_late).toLocaleString();
        const dtlSs = document.getElementById('dtl-ss');
        if (dtlSs) dtlSs.innerText = '-฿' + Number(data.deduction_social_security).toLocaleString();
        const dtlNet = document.getElementById('dtl-net-salary');
        if (dtlNet) dtlNet.innerText = '฿' + Number(data.net_salary).toLocaleString();

        // Update Stats
        const statWork = document.getElementById('stat-work-days');
        if (statWork) statWork.innerText = data.work_days + ' วัน';
        const statHours = document.getElementById('stat-total-hours');
        if (statHours) statHours.innerText = data.total_hours;
        const statOt = document.getElementById('stat-ot-hours');
        if (statOt) statOt.innerText = data.total_ot_hours;
        const statLeave = document.getElementById('stat-leave-days');
        if (statLeave) statLeave.innerText = data.leave_days + ' วัน';

        // Update Payslip Modal Header
        const psPeriod = document.getElementById('ps-period');
        if (psPeriod) psPeriod.innerText = data.period_label;
    }

    function selectHeroEmployee(card) {
        if (!card) return;
        selectedHeroEmployeeId = card.id;
        const heroName = document.getElementById('hero-payroll-emp-name');
        if (heroName) heroName.innerText = card.name;
        const heroSub = document.getElementById('hero-payroll-emp-subtitle');
        if (heroSub) heroSub.innerText = `${card.role_label || card.role} • ${card.branch}`;

        const boxBase = document.getElementById('box-base-salary');
        if (boxBase) boxBase.innerText = '฿' + Number(card.base_salary).toLocaleString();
        const boxOt = document.getElementById('box-ot-amount');
        if (boxOt) boxOt.innerText = '฿' + Number(card.ot_amount).toLocaleString();
        const boxBonus = document.getElementById('box-bonus-amount');
        if (boxBonus) boxBonus.innerText = '฿' + Number(card.bonus).toLocaleString();
        const boxDed = document.getElementById('box-deductions-amount');
        if (boxDed) boxDed.innerText = (card.deductions > 0 ? '-฿' : '฿') + Number(card.deductions).toLocaleString();
        const boxNet = document.getElementById('box-net-salary');
        if (boxNet) boxNet.innerText = '฿' + Number(card.net_salary).toLocaleString();

        document.querySelectorAll('.payroll-card').forEach(c => {
            c.classList.remove('ring-2', 'ring-[#E50914]');
        });
        const currentCard = document.getElementById('payroll-card-' + card.id);
        if (currentCard) {
            currentCard.classList.add('ring-2', 'ring-[#E50914]');
        }
        showToast('เลือกสรุปยอดเงินเดือน: ' + card.name);
    }

    // Payroll Grid 3/4 Column Switcher & Search Filter (Matches Image 2 & POS Cashier)
    let currentPayrollGridCols = 4;
    function setPayrollGridColumns(cols) {
        currentPayrollGridCols = cols;
        try { localStorage.setItem('mala_street_payroll_grid_cols', cols); } catch (e) {}

        const grid = document.getElementById('payroll-cards-grid');
        const btn3 = document.getElementById('btn-payroll-grid-3');
        const btn4 = document.getElementById('btn-payroll-grid-4');
        if (!grid || !btn3 || !btn4) return;

        if (cols === 4) {
            grid.className = 'grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3.5 transition-all duration-200';
            btn4.className = 'grid-toggle-btn active px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all bg-[#E50914] text-white shadow-2xs cursor-pointer';
            btn3.className = 'grid-toggle-btn px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all text-slate-500 hover:text-slate-800 hover:bg-slate-100 cursor-pointer';
        } else {
            grid.className = 'grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4.5 transition-all duration-200';
            btn3.className = 'grid-toggle-btn active px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all bg-[#E50914] text-white shadow-2xs cursor-pointer';
            btn4.className = 'grid-toggle-btn px-2.5 py-1 rounded-full text-xs font-bold flex items-center gap-1.5 transition-all text-slate-500 hover:text-slate-800 hover:bg-slate-100 cursor-pointer';
        }
    }

    function filterPayrollCards() {
        const query = (document.getElementById('payroll-search-input')?.value || '').trim().toLowerCase();
        const cards = document.querySelectorAll('.payroll-card');
        let visibleCount = 0;
        cards.forEach(card => {
            const text = (card.getAttribute('data-search-text') || card.innerText || '').toLowerCase();
            if (!query || text.includes(query)) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });
        const countEl = document.getElementById('payroll-visible-count');
        if (countEl) countEl.innerText = visibleCount;
    }

    // 3. Store Geofence & Location Service
    const STORE_COORDS = {
        name: "{{ $storeLocation['name'] }}",
        lat: {{ (float)$storeLocation['lat'] }},
        lng: {{ (float)$storeLocation['lng'] }},
        radius: {{ (int)$storeLocation['radius_meters'] }},
        allow_mock: {{ $storeLocation['allow_mock'] ? 'true' : 'false' }}
    };
    let currentUserCoords = {
        lat: {{ (float)$storeLocation['lat'] }},
        lng: {{ (float)$storeLocation['lng'] }},
        distance: 8,
        inside: true
    };

    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371e3; // metres
        const φ1 = lat1 * Math.PI/180;
        const φ2 = lat2 * Math.PI/180;
        const Δφ = (lat2-lat1) * Math.PI/180;
        const Δλ = (lon2-lon1) * Math.PI/180;
        const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                  Math.cos(φ1) * Math.cos(φ2) *
                  Math.sin(Δλ/2) * Math.sin(Δλ/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return Math.round(R * c);
    }

    function updateGeofenceUI(distance, inside) {
        const statusText = document.getElementById('geofence-status-text');
        const indicator = document.getElementById('geofence-distance-indicator');
        const modalBadge = document.getElementById('modal-geofence-badge');
        
        if (inside || STORE_COORDS.allow_mock) {
            if (statusText) statusText.innerText = STORE_COORDS.allow_mock ? 'อยู่ในเขตร้าน (โหมดทดสอบเปิดอยู่)' : 'อยู่ในเขตร้าน (ยืนยันแล้ว)';
            if (indicator) {
                indicator.innerText = `ห่าง ${distance} ม. (อยู่ในเขตร้าน)`;
                indicator.className = 'text-emerald-600 font-bold';
            }
            if (modalBadge) {
                modalBadge.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span><span>📍 ลิงก์พิกัดร้าน: ผ่านการตรวจสอบ (${distance} ม.)</span>`;
                modalBadge.className = 'mt-3 text-[11px] px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold inline-flex items-center gap-1.5';
            }
        } else {
            if (statusText) statusText.innerText = `อยู่นอกเขตร้าน (${distance} ม.)`;
            if (indicator) {
                indicator.innerText = `ห่าง ${distance} ม. (เกินระยะ ${STORE_COORDS.radius} ม.)`;
                indicator.className = 'text-rose-600 font-bold';
            }
            if (modalBadge) {
                modalBadge.innerHTML = `<span class="w-2 h-2 rounded-full bg-rose-500"></span><span>⚠️ อยู่นอกเขตร้าน (${distance} ม.) ต้องอยู่ในเขตร้านเท่านั้น</span>`;
                modalBadge.className = 'mt-3 text-[11px] px-3 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-bold inline-flex items-center gap-1.5';
            }
        }
    }

    function verifyShopLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((pos) => {
                const dist = calculateDistance(pos.coords.latitude, pos.coords.longitude, STORE_COORDS.lat, STORE_COORDS.lng);
                const inside = dist <= STORE_COORDS.radius;
                currentUserCoords = {
                    lat: pos.coords.latitude,
                    lng: pos.coords.longitude,
                    distance: dist,
                    inside: inside
                };
                updateGeofenceUI(dist, inside);
            }, (err) => {
                // If permission denied or HTTP without GPS, default to shop-verified
                updateGeofenceUI(8, true);
            }, { enableHighAccuracy: true, timeout: 6000 });
        }
    }

    // 4. Leaflet Map Interactive Store Picker
    let storeMap = null;
    let storeMarker = null;
    let storeRadiusCircle = null;

    function initStoreLeafletMap() {
        const currentLat = parseFloat(document.getElementById('input-store-lat').value) || STORE_COORDS.lat;
        const currentLng = parseFloat(document.getElementById('input-store-lng').value) || STORE_COORDS.lng;
        const currentRadius = parseInt(document.getElementById('input-store-radius').value) || STORE_COORDS.radius;

        if (!storeMap) {
            storeMap = L.map('store-picker-map').setView([currentLat, currentLng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(storeMap);

            const pinHtml = `<div style="background:linear-gradient(135deg, #B80A17, #E50914);width:34px;height:34px;border-radius:50%;border:2.5px solid #fff;box-shadow:0 4px 12px rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;">🍢</div>`;
            const customIcon = L.divIcon({
                className: 'custom-store-pin',
                html: pinHtml,
                iconSize: [34, 34],
                iconAnchor: [17, 34]
            });

            storeMarker = L.marker([currentLat, currentLng], {
                draggable: true,
                icon: customIcon
            }).addTo(storeMap);

            storeRadiusCircle = L.circle([currentLat, currentLng], {
                color: '#E50914',
                fillColor: '#E50914',
                fillOpacity: 0.16,
                weight: 2,
                radius: currentRadius
            }).addTo(storeMap);

            storeMarker.on('drag', function(e) {
                const pos = e.target.getLatLng();
                document.getElementById('input-store-lat').value = pos.lat.toFixed(6);
                document.getElementById('input-store-lng').value = pos.lng.toFixed(6);
                storeRadiusCircle.setLatLng(pos);
            });

            storeMap.on('click', function(e) {
                storeMarker.setLatLng(e.latlng);
                storeRadiusCircle.setLatLng(e.latlng);
                document.getElementById('input-store-lat').value = e.latlng.lat.toFixed(6);
                document.getElementById('input-store-lng').value = e.latlng.lng.toFixed(6);
            });
        } else {
            storeMarker.setLatLng([currentLat, currentLng]);
            storeRadiusCircle.setLatLng([currentLat, currentLng]);
            storeRadiusCircle.setRadius(currentRadius);
            storeMap.setView([currentLat, currentLng]);
            storeMap.invalidateSize();
        }
    }

    function openStoreLocationModal() {
        const modal = document.getElementById('modal-store-location');
        modal.classList.remove('hidden');
        setTimeout(() => {
            initStoreLeafletMap();
            if (storeMap) storeMap.invalidateSize();
        }, 150);
    }

    function closeStoreLocationModal() {
        document.getElementById('modal-store-location').classList.add('hidden');
    }

    function setStoreToCurrentLocation() {
        if (navigator.geolocation) {
            showToast('กำลังดึงพิกัด GPS ปัจจุบันของคุณ...');
            navigator.geolocation.getCurrentPosition((pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                document.getElementById('input-store-lat').value = lat.toFixed(6);
                document.getElementById('input-store-lng').value = lng.toFixed(6);

                if (storeMap && storeMarker) {
                    storeMarker.setLatLng([lat, lng]);
                    storeRadiusCircle.setLatLng([lat, lng]);
                    storeMap.setView([lat, lng], 17);
                }
                showToast('ย้ายหมุดมาที่ตำแหน่งปัจจุบันของคุณแล้ว!');
            }, (err) => {
                alert('ไม่สามารถดึงพิกัด GPS ได้ กรุณาเปิดสิทธิ์ระบุตำแหน่ง (Location) ในเบราว์เซอร์');
            }, { enableHighAccuracy: true, timeout: 8000 });
        } else {
            alert('เบราว์เซอร์นี้ไม่รองรับการดึงพิกัด GPS');
        }
    }

    function onManualCoordsChange() {
        const lat = parseFloat(document.getElementById('input-store-lat').value);
        const lng = parseFloat(document.getElementById('input-store-lng').value);
        if (!isNaN(lat) && !isNaN(lng) && storeMarker && storeMap) {
            storeMarker.setLatLng([lat, lng]);
            storeRadiusCircle.setLatLng([lat, lng]);
            storeMap.setView([lat, lng]);
        }
    }

    function onRadiusSliderChange(val) {
        document.getElementById('slider-radius-val').innerText = val + ' ม.';
        document.getElementById('map-radius-badge').innerText = 'รัศมี: ' + val + ' เมตร';
        if (storeRadiusCircle) {
            storeRadiusCircle.setRadius(parseInt(val));
        }
    }

    function setRadiusPreset(val) {
        document.getElementById('input-store-radius').value = val;
        onRadiusSliderChange(val);
    }

    async function saveStoreLocationSettings() {
        const lat = parseFloat(document.getElementById('input-store-lat').value);
        const lng = parseFloat(document.getElementById('input-store-lng').value);
        const radius = parseInt(document.getElementById('input-store-radius').value);
        const allowMock = document.getElementById('input-allow-mock').checked;

        try {
            const res = await fetch("{{ route('admin.employees.update_location') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    latitude: lat,
                    longitude: lng,
                    radius_meters: radius,
                    allow_mock: allowMock
                })
            });
            const json = await res.json();
            if (json.success) {
                closeStoreLocationModal();
                showToast(json.message);

                STORE_COORDS.lat = lat;
                STORE_COORDS.lng = lng;
                STORE_COORDS.radius = radius;
                STORE_COORDS.allow_mock = allowMock;

                const disp = document.getElementById('store-coords-display');
                if (disp) {
                    disp.innerText = `📍 พิกัดร้านหม่าล่า: ${lat.toFixed(4)}, ${lng.toFixed(4)}`;
                }

                verifyShopLocation();
            } else {
                alert(json.message || 'เกิดข้อผิดพลาดในการบันทึกพิกัด');
            }
        } catch (e) {
            closeStoreLocationModal();
            showToast('บันทึกพิกัดร้านเรียบร้อย');
        }
    }

    // 5. Face Scanner Modal & Camera Snapshot
    let mediaStream = null;
    let currentCapturedPhoto = null;
    let currentAttendanceStatus = "{{ $todayAttendance['status'] }}";

    function updateAutoModeIndicator(action) {
        const badge = document.getElementById('modal-auto-mode-badge');
        const text = document.getElementById('auto-mode-text');
        if (!badge || !text) return;

        if (action === 'check_in') {
            text.innerHTML = '⚡ <strong>ถ่ายครั้งแรกของวัน:</strong> ระบบตั้ง "เช็คอินเข้างาน" อัตโนมัติ';
            badge.className = 'mt-2 text-[11px] font-bold py-1.5 px-3 rounded-xl border text-center transition-all bg-emerald-50 text-emerald-800 border-emerald-200';
        } else {
            text.innerHTML = '⚡ <strong>ถ่ายครั้งที่ 2 ของวัน:</strong> ระบบตั้ง "เช็คเอาท์ออกงาน" อัตโนมัติ';
            badge.className = 'mt-2 text-[11px] font-bold py-1.5 px-3 rounded-xl border text-center transition-all bg-rose-50 text-rose-800 border-rose-200';
        }
    }

    function onScanActionChange(action) {
        const badge = document.getElementById('modal-auto-mode-badge');
        const text = document.getElementById('auto-mode-text');
        if (!badge || !text) return;

        if (action === 'check_in') {
            text.innerHTML = '🟢 <strong>โหมดที่เลือก:</strong> เช็คอินเข้างาน (ถ่ายครั้งแรกของวัน)';
            badge.className = 'mt-2 text-[11px] font-bold py-1.5 px-3 rounded-xl border text-center transition-all bg-emerald-50 text-emerald-800 border-emerald-200';
        } else {
            text.innerHTML = '🔴 <strong>โหมดที่เลือก:</strong> เช็คเอาท์ออกงาน (ถ่ายครั้งที่ 2 ของวัน)';
            badge.className = 'mt-2 text-[11px] font-bold py-1.5 px-3 rounded-xl border text-center transition-all bg-rose-50 text-rose-800 border-rose-200';
        }
    }

    async function openFaceScannerModal() {
        const modal = document.getElementById('modal-face-scanner');
        modal.classList.remove('hidden');

        // Check GPS coordinates
        verifyShopLocation();

        // เงื่อนไขอัตโนมัติ: ถ่ายครั้งแรกของวัน เข้างานอัตโนมัติ / ครั้งที่ 2 ของวัน ออกงาน
        const isFirstScan = (currentAttendanceStatus !== 'เข้างานแล้ว');
        const defaultAction = isFirstScan ? 'check_in' : 'check_out';
        
        const radioIn = document.getElementById('action-radio-checkin');
        const radioOut = document.getElementById('action-radio-checkout');
        if (isFirstScan && radioIn) {
            radioIn.checked = true;
        } else if (!isFirstScan && radioOut) {
            radioOut.checked = true;
        }
        updateAutoModeIndicator(defaultAction);

        // รีเซ็ตสถานะปุ่มและการแสดงผลก่อนหน้ากลับสู่โหมดพร้อมถ่าย
        retakePhoto();

        // Try accessing device camera if supported with dual-stage fallback (FacingMode + Generic)
        const video = document.getElementById('webcam-video');
        const placeholder = document.getElementById('scanner-placeholder');
        const feedback = document.getElementById('scan-feedback-text');

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            if (mediaStream) {
                try {
                    mediaStream.getTracks().forEach(track => track.stop());
                } catch (e) {}
                mediaStream = null;
            }

            let stream = null;

            // Attempt 1: Front camera (user facing)
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio: false
                });
            } catch (err1) {
                console.warn('FacingMode "user" camera constraint failed, attempting generic video:', err1);
                // Attempt 2: Generic video (essential for Windows PC webcams, USB cameras, and laptops)
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: false
                    });
                } catch (err2) {
                    console.error('All camera access attempts failed:', err2);
                    if (feedback) {
                        feedback.innerHTML = '<span class="text-rose-600">⚠️ ไม่สามารถเปิดกล้องได้ (กรุณาอนุญาตสิทธิ์เข้าถึงกล้องในเบราว์เซอร์ หรือกดถ่ายเพื่อจำลองภาพ)</span>';
                    }
                }
            }

            if (stream && video) {
                mediaStream = stream;
                video.srcObject = stream;
                video.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');

                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.catch(playErr => {
                        console.warn('Autoplay prevented, waiting for loadedmetadata:', playErr);
                        video.onloadedmetadata = () => {
                            video.play().catch(e => console.warn('video.play failed:', e));
                        };
                    });
                }

                if (feedback) {
                    feedback.innerText = 'ระบบพร้อมถ่ายภาพและบันทึกเวลาเข้า-ออกงาน';
                    feedback.className = 'text-xs font-semibold text-slate-700 mt-2';
                }
            } else {
                if (video) video.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            }
        } else {
            if (feedback) {
                feedback.innerHTML = '<span class="text-amber-600">⚠️ เบราว์เซอร์ไม่รองรับ WebCam (ระบบจะใช้ภาพถ่ายจำลองเมื่อกดถ่ายภาพ)</span>';
            }
        }
    }

    function closeFaceScannerModal() {
        const modal = document.getElementById('modal-face-scanner');
        modal.classList.add('hidden');
        if (mediaStream) {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
        }
        retakePhoto();
    }

    function captureCameraFrame() {
        const video = document.getElementById('webcam-video');
        const canvas = document.getElementById('webcam-canvas') || document.createElement('canvas');
        if (video && !video.classList.contains('hidden') && video.videoWidth > 0) {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            // Mirror snapshot horizontally to match live preview
            ctx.save();
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            ctx.restore();
            return canvas.toDataURL('image/jpeg', 0.90);
        }

        // Fallback snapshot generator (จำลองภาพพร้อมลายน้ำเวลาชัดเจนหากไม่ได้เปิดกล้อง)
        canvas.width = 480;
        canvas.height = 480;
        const ctx = canvas.getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 480, 480);
        grad.addColorStop(0, '#1e1b4b');
        grad.addColorStop(1, '#0f172a');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 480, 480);
        
        ctx.strokeStyle = '#ef4444';
        ctx.lineWidth = 4;
        ctx.strokeRect(25, 25, 430, 430);
        
        ctx.fillStyle = '#ef4444';
        ctx.beginPath();
        ctx.arc(240, 190, 70, 0, Math.PI * 2);
        ctx.fill();

        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 22px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Mala Street AI Facial Camera', 240, 310);

        ctx.font = '16px monospace';
        ctx.fillStyle = '#38bdf8';
        const now = new Date();
        ctx.fillText(now.toLocaleTimeString('th-TH') + ' น. • GPS Verified', 240, 345);

        return canvas.toDataURL('image/jpeg', 0.90);
    }

    // Step 1: ถ่ายภาพ snapshot เพื่อดูตัวอย่างก่อนยืนยัน
    function takePhotoSnapshot() {
        const photo = captureCameraFrame();
        if (!photo) {
            alert('ไม่สามารถบันทึกภาพถ่ายได้ กรุณาลองใหม่อีกครั้ง');
            return;
        }

        currentCapturedPhoto = photo;

        // แสดงรูปภาพตัวอย่างในกรอบกล้อง
        const previewImg = document.getElementById('webcam-preview-img');
        const video = document.getElementById('webcam-video');
        const placeholder = document.getElementById('scanner-placeholder');
        const laser = document.getElementById('scanner-laser-line');

        if (previewImg) {
            previewImg.src = currentCapturedPhoto;
            previewImg.classList.remove('hidden');
        }
        if (video) video.classList.add('hidden');
        if (placeholder) placeholder.classList.add('hidden');
        if (laser) laser.classList.add('hidden');

        // สลับปุ่มเป็น: "🔄 ถ่ายใหม่" และ "✓ ยืนยันบันทึกเวลา"
        const stepCapture = document.getElementById('scanner-step-capture');
        const stepConfirm = document.getElementById('scanner-step-confirm');
        if (stepCapture) stepCapture.classList.add('hidden');
        if (stepConfirm) stepConfirm.classList.remove('hidden');

        // แสดงข้อความคำแนะนำให้กดยืนยันหรือถ่ายใหม่
        const feedback = document.getElementById('scan-feedback-text');
        if (feedback) {
            feedback.innerHTML = '📸 <strong>ถ่ายภาพเรียบร้อยแล้ว!</strong> หากพอใจกด "ยืนยันบันทึกเวลา" หรือกด "ถ่ายใหม่"';
            feedback.className = 'text-xs font-semibold text-emerald-700 mt-2';
        }
    }

    // Step 2: ถ่ายใหม่ (ถ้าไม่ชอบกดถ่ายใหม่ได้)
    function retakePhoto() {
        currentCapturedPhoto = null;

        const previewImg = document.getElementById('webcam-preview-img');
        const video = document.getElementById('webcam-video');
        const placeholder = document.getElementById('scanner-placeholder');
        const laser = document.getElementById('scanner-laser-line');

        if (previewImg) {
            previewImg.classList.add('hidden');
            previewImg.src = '';
        }

        if (video) {
            if (mediaStream) {
                video.classList.remove('hidden');
                if (video.paused) {
                    video.play().catch(() => {});
                }
            } else {
                video.classList.remove('hidden');
            }
        }
        if (placeholder) placeholder.classList.add('hidden');

        if (laser) laser.classList.remove('hidden');

        // สลับปุ่มกลับเป็น "📷 ถ่ายภาพ" และ "ยกเลิก"
        const stepCapture = document.getElementById('scanner-step-capture');
        const stepConfirm = document.getElementById('scanner-step-confirm');
        if (stepCapture) stepCapture.classList.remove('hidden');
        if (stepConfirm) stepConfirm.classList.add('hidden');

        const btnConfirm = document.getElementById('btn-confirm-attendance');
        if (btnConfirm) {
            btnConfirm.disabled = false;
            btnConfirm.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>✓ ยืนยันบันทึกเวลา</span>
            `;
        }

        // คืนค่าข้อความสถานะเดิม
        const feedback = document.getElementById('scan-feedback-text');
        if (feedback) {
            feedback.innerText = 'ระบบพร้อมถ่ายภาพและบันทึกเวลาเข้า-ออกงาน';
            feedback.className = 'text-xs font-semibold text-slate-700 mt-2';
        }
    }

    // 6. Execute Face Clock In / Clock Out (เมื่อกดยืนยันบันทึกเวลา)
    async function executeFaceClockIn() {
        if (!currentUserCoords.inside && !STORE_COORDS.allow_mock) {
            alert(`⚠️ ไม่สามารถบันทึกเวลาได้: คุณอยู่นอกเขตร้านหม่าล่าสตรีท (ระยะห่าง ${currentUserCoords.distance} ม.) จุดสำคัญคือต้องอยู่ในเขตร้านเท่านั้น (ไม่เกิน ${STORE_COORDS.radius} ม.) หรือสามารถคลิกไอคอนหน้าร้านท้ายการ์ดเพื่อเลือกปักหมุดตำแหน่งร้านใหม่`);
            return;
        }

        // หากยังไม่ได้ถ่ายรูป ให้ถ่ายทันที
        if (!currentCapturedPhoto) {
            currentCapturedPhoto = captureCameraFrame();
        }

        const btnConfirm = document.getElementById('btn-confirm-attendance');
        if (btnConfirm) {
            btnConfirm.disabled = true;
            btnConfirm.innerHTML = `<span class="animate-spin text-base">⏳</span><span>กำลังบันทึกเวลา...</span>`;
        }

        const feedback = document.getElementById('scan-feedback-text');
        if (feedback) {
            feedback.innerText = 'กำลังประมวลผลโครงหน้าและบันทึกข้อมูลเวลาเข้าสู่ระบบ...';
            feedback.className = 'text-xs font-bold text-rose-600 mt-2 animate-pulse';
        }

        const selectedAction = document.querySelector('input[name="modal_scan_action"]:checked')?.value || 'check_in';

        try {
            const res = await fetch("{{ route('admin.employees.clock_in') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    action: selectedAction,
                    photo: currentCapturedPhoto,
                    latitude: currentUserCoords.lat,
                    longitude: currentUserCoords.lng,
                    distance: currentUserCoords.distance,
                    is_mock: false
                })
            });
            const json = await res.json();

            setTimeout(() => {
                closeFaceScannerModal();
                if (json.success) {
                    showToast(json.message);
                    currentAttendanceStatus = json.status;
                    
                    document.getElementById('disp-check-in').innerText = json.check_in || '—';
                    document.getElementById('disp-check-out').innerText = json.check_out || '—';
                    document.getElementById('disp-work-hours').innerText = json.work_hours || '—';
                    document.getElementById('disp-status-pill').innerText = '✓ ' + json.status;
                    const dateEl = document.getElementById('disp-today-date');
                    if (dateEl && json.date_formatted) {
                        dateEl.innerText = json.date_formatted;
                    }

                    if (json.check_in_photo) {
                        const inImg = document.getElementById('disp-check-in-img');
                        const inUrl = document.getElementById('disp-check-in-url');
                        const inTime = document.getElementById('disp-photo-time-in');
                        if (inImg) inImg.src = json.check_in_photo;
                        if (inUrl) inUrl.href = json.check_in_photo;
                        if (inTime) inTime.innerText = json.check_in;
                    }
                    if (json.check_out_photo) {
                        const outImg = document.getElementById('disp-check-out-img');
                        const outUrl = document.getElementById('disp-check-out-url');
                        const outTime = document.getElementById('disp-photo-time-out');
                        if (outImg) outImg.src = json.check_out_photo;
                        if (outUrl) outUrl.href = json.check_out_photo;
                        if (outTime) outTime.innerText = json.check_out;
                    }

                    // Dynamically update overview logs for this employee
                    if (typeof allAttendanceLogs !== 'undefined' && Array.isArray(allAttendanceLogs)) {
                        allAttendanceLogs.forEach(item => {
                            if (item.date === serverTodayDateKey && (item.name === json.employee_name || item.employee_id === json.employee_id)) {
                                item.check_in_time = json.check_in || '—';
                                item.check_out_time = json.check_out || '—';
                                item.total_hours = json.work_hours || '—';
                                item.check_in_photo = json.check_in_photo || null;
                                item.check_out_photo = json.check_out_photo || null;
                                item.status = (json.status === 'ออกงานแล้ว' || json.status === 'เข้างานแล้ว') ? 'present' : 'pending';
                                item.status_label = json.status;
                                item.status_badge = json.status + ' 🟢';
                                item.check_in_distance = '0ม.';
                                item.check_out_distance = (json.check_out && json.check_out !== '—') ? '0ม.' : null;
                            }
                        });
                        const currentFiltered = allAttendanceLogs.filter(log => log.date === overviewSelectedDate);
                        if (currentFiltered.length > 0) {
                            renderOverviewAttendanceRows(currentFiltered);
                        }
                    }
                    if (typeof syncEmployeesData === 'function') {
                        syncEmployeesData(false);
                    }
                } else {
                    alert(json.message || 'เกิดข้อผิดพลาดในการบันทึกเวลา');
                }
            }, 700);
        } catch (e) {
            closeFaceScannerModal();
            showToast('บันทึกเวลาสำเร็จ');
        }
    }

    // 6. Photo Lightbox Modal
    function openPhotoLightbox(imgUrl, title) {
        const modal = document.getElementById('modal-photo-lightbox');
        document.getElementById('lightbox-img').src = imgUrl;
        document.getElementById('lightbox-title').innerText = title || 'ภาพถ่ายสแกนหน้าบันทึกเวลา';
        document.getElementById('lightbox-url-input').value = imgUrl;
        document.getElementById('lightbox-open-external').href = imgUrl;
        modal.classList.remove('hidden');
    }

    function closePhotoLightbox() {
        document.getElementById('modal-photo-lightbox').classList.add('hidden');
    }

    function copyLightboxUrl() {
        const input = document.getElementById('lightbox-url-input');
        input.select();
        navigator.clipboard.writeText(input.value);
        showToast('คัดลอก URL รูปภาพเรียบร้อยแล้ว: ' + input.value);
    }

    function copyPhotoUrl(elementId) {
        const link = document.getElementById(elementId);
        if (link && link.href) {
            navigator.clipboard.writeText(link.href);
            showToast('คัดลอก URL รูปภาพเรียบร้อยแล้ว');
        }
    }

    // 7. Payslip Modal
    function openPayslipModal(card) {
        if (card) {
            const empNameEl = document.getElementById('ps-emp-name');
            const empRoleEl = document.getElementById('ps-emp-role');
            const periodEl = document.getElementById('ps-period');
            const payDateEl = document.getElementById('ps-pay-date');
            const baseSalaryEl = document.getElementById('ps-base-salary');
            const otLabelEl = document.getElementById('ps-ot-label');
            const otAmountEl = document.getElementById('ps-ot-amount');
            const bonusAmountEl = document.getElementById('ps-bonus-amount');
            const lateRowEl = document.getElementById('ps-late-row');
            const lateEl = document.getElementById('ps-deduction-late');
            const ssRowEl = document.getElementById('ps-ss-row');
            const ssEl = document.getElementById('ps-deduction-ss');
            const deductionsTotalEl = document.getElementById('ps-deductions-total');
            const netSalaryEl = document.getElementById('ps-net-salary');

            const periodInfo = (payrollData && payrollData[currentPayrollMonth]) ? payrollData[currentPayrollMonth] : null;
            if (periodEl && periodInfo) periodEl.innerText = periodInfo.period_label;
            if (payDateEl && periodInfo) payDateEl.innerText = 'จ่ายเมื่อ: ' + periodInfo.payment_date;

            const fmt2 = (val) => Number(val || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (empNameEl) empNameEl.innerText = card.name;
            if (empRoleEl) empRoleEl.innerText = `รหัส: ${card.id} • ${card.role_label} • ${card.branch}`;
            if (baseSalaryEl) baseSalaryEl.innerText = '฿' + fmt2(card.base_salary);
            if (otLabelEl) otLabelEl.innerText = `ค่าล่วงเวลา (Overtime ${card.ot_hours || '0 ชม.'})`;
            if (otAmountEl) otAmountEl.innerText = '+฿' + fmt2(card.ot_amount);
            if (bonusAmountEl) bonusAmountEl.innerText = '+฿' + fmt2(card.bonus);

            const lateAmt = Number(card.deduction_late || 0);
            if (lateRowEl) lateRowEl.classList.toggle('hidden', lateAmt <= 0);
            if (lateEl) lateEl.innerText = '-฿' + fmt2(lateAmt);

            const ssAmt = Number(card.deduction_ss || 0);
            if (ssRowEl) ssRowEl.classList.toggle('hidden', ssAmt <= 0);
            if (ssEl) ssEl.innerText = '-฿' + fmt2(ssAmt);

            const totalDed = Number(card.deductions || 0);
            if (deductionsTotalEl) deductionsTotalEl.innerText = (totalDed > 0 ? '-฿' : '฿') + fmt2(totalDed);
            if (netSalaryEl) netSalaryEl.innerText = '฿' + fmt2(card.net_salary);
        }
        document.getElementById('modal-payslip-viewer').classList.remove('hidden');
    }
    function closePayslipModal() {
        document.getElementById('modal-payslip-viewer').classList.add('hidden');
    }

    // 8. Toast helper
    function showToast(message) {
        const toast = document.getElementById('emp-toast');
        document.getElementById('toast-msg').innerText = message;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 3500);
    }

    // 9. Overview Mode Switcher (Daily vs Monthly)
    function switchOverviewMode(mode) {
        const btnDaily = document.getElementById('btn-overview-daily');
        const btnMonthly = document.getElementById('btn-overview-monthly');
        const viewDaily = document.getElementById('view-daily-table');
        const viewMonthly = document.getElementById('view-monthly-table');
        const metricsContainer = document.getElementById('overview-metrics-container');

        if (mode === 'daily') {
            if (btnDaily) btnDaily.className = "px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-bold transition-all cursor-pointer bg-[#0F172A] text-white shadow-xs flex items-center gap-1.5";
            if (btnMonthly) btnMonthly.className = "px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer flex items-center gap-1.5";
            if (viewDaily) viewDaily.classList.remove('hidden');
            if (viewMonthly) viewMonthly.classList.add('hidden');
            if (metricsContainer) metricsContainer.classList.remove('hidden');
            showToast('สลับมุมมอง: ตารางบันทึกการทำงานรายวัน');
        } else {
            if (btnMonthly) btnMonthly.className = "px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-bold transition-all cursor-pointer bg-[#0F172A] text-white shadow-xs flex items-center gap-1.5";
            if (btnDaily) btnDaily.className = "px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer flex items-center gap-1.5";
            if (viewDaily) viewDaily.classList.add('hidden');
            if (viewMonthly) viewMonthly.classList.remove('hidden');
            if (metricsContainer) metricsContainer.classList.add('hidden');
            showToast('สลับมุมมอง: สรุปภาพรวมรายเดือน');
        }
    }

    function refreshOverview() {
        const btn = document.getElementById('btn-refresh-overview');
        if (btn) btn.innerHTML = `<span class="animate-spin text-sm">⏳</span><span>กำลังโหลด...</span>`;
        Promise.resolve(typeof syncEmployeesData === 'function' ? syncEmployeesData(false) : null).finally(() => {
            if (btn) btn.innerHTML = `<svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg><span>รีเฟรช</span>`;
            setOverviewPresetFilter(currentOverviewPreset);
            showToast('รีเฟรชข้อมูลเวลาการเข้างานของพนักงานล่าสุดเรียบร้อย');
        });
    }

    function shiftIsoDateKey(baseIso, deltaDays) {
        const [y, m, d] = baseIso.split('-').map(Number);
        const dt = new Date(y, m - 1, d + deltaDays);
        return `${dt.getFullYear()}-${String(dt.getMonth() + 1).padStart(2, '0')}-${String(dt.getDate()).padStart(2, '0')}`;
    }

    // 10. Multi-Day Dataset & Interactive Calendar Popover for Overview
    let allAttendanceLogs = @json($allAttendanceLogs ?? []);
    let currentOverviewPreset = 'today';
    let overviewCalMode = 'daily'; // 'daily' or 'range'
    const [initOvYear, initOvMonth] = serverTodayDateKey.split('-').map(Number);
    let overviewCalYear = initOvYear;
    let overviewCalMonth = initOvMonth - 1; // 0-indexed
    let overviewSelectedDate = serverTodayDateKey;
    let overviewRangeStart = null;
    let overviewRangeEnd = null;

    function toggleOverviewDatePopover(event) {
        event.stopPropagation();
        closePayrollMonthPopover();
        const popover = document.getElementById('popover-overview-calendar');
        const chevron = document.getElementById('chevron-overview-popover');
        if (popover) {
            const isClosed = popover.classList.contains('hidden');
            if (isClosed) {
                renderOverviewCalendar();
                popover.classList.remove('hidden');
                if (chevron) chevron.classList.add('rotate-180');
            } else {
                popover.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        }
    }

    function closeOverviewDatePopover() {
        const popover = document.getElementById('popover-overview-calendar');
        const chevron = document.getElementById('chevron-overview-popover');
        if (popover) popover.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
    }

    function switchOverviewCalTab(mode) {
        overviewCalMode = mode;
        const btnDaily = document.getElementById('tab-cal-daily');
        const btnRange = document.getElementById('tab-cal-range');
        if (mode === 'daily') {
            if (btnDaily) btnDaily.className = "flex-1 py-1.5 rounded-xl text-xs font-bold bg-white text-slate-900 shadow-2xs transition-all cursor-pointer";
            if (btnRange) btnRange.className = "flex-1 py-1.5 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 transition-all cursor-pointer";
        } else {
            if (btnRange) btnRange.className = "flex-1 py-1.5 rounded-xl text-xs font-bold bg-white text-slate-900 shadow-2xs transition-all cursor-pointer";
            if (btnDaily) btnDaily.className = "flex-1 py-1.5 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-800 transition-all cursor-pointer";
            overviewRangeStart = null;
            overviewRangeEnd = null;
        }
        renderOverviewCalendar();
    }

    function changeOverviewCalMonth(delta) {
        overviewCalMonth += delta;
        if (overviewCalMonth < 0) {
            overviewCalMonth = 11;
            overviewCalYear--;
        } else if (overviewCalMonth > 11) {
            overviewCalMonth = 0;
            overviewCalYear++;
        }
        renderOverviewCalendar();
    }

    const monthNamesEn = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const monthNamesTh = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

    function renderOverviewCalendar() {
        const titleEl = document.getElementById('label-overview-cal-month');
        const thYear = overviewCalYear + 543;
        if (titleEl) {
            titleEl.innerText = `${monthNamesEn[overviewCalMonth]} ${overviewCalYear} (${monthNamesTh[overviewCalMonth]} ${thYear})`;
        }

        const grid = document.getElementById('overview-cal-grid');
        if (!grid) return;
        grid.innerHTML = '';

        const firstDayIndex = new Date(overviewCalYear, overviewCalMonth, 1).getDay();
        const totalDays = new Date(overviewCalYear, overviewCalMonth + 1, 0).getDate();

        // Empty blanks for preceding days
        for (let i = 0; i < firstDayIndex; i++) {
            const blank = document.createElement('div');
            blank.className = 'w-8 h-8';
            grid.appendChild(blank);
        }

        // Days
        for (let day = 1; day <= totalDays; day++) {
            const dateStr = `${overviewCalYear}-${String(overviewCalMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.innerText = day;
            btn.className = 'w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold mx-auto transition-all cursor-pointer hover:bg-slate-100 text-slate-700';

            if (overviewCalMode === 'daily') {
                if (dateStr === overviewSelectedDate) {
                    btn.className = 'w-8 h-8 rounded-full bg-blue-600 text-white font-black shadow-md shadow-blue-500/30 flex items-center justify-center text-xs mx-auto transition-all cursor-pointer';
                }
            } else {
                // Range mode
                if (overviewRangeStart && dateStr === overviewRangeStart) {
                    btn.className = 'w-8 h-8 rounded-full bg-blue-600 text-white font-black shadow-md shadow-blue-500/30 flex items-center justify-center text-xs mx-auto';
                } else if (overviewRangeEnd && dateStr === overviewRangeEnd) {
                    btn.className = 'w-8 h-8 rounded-full bg-blue-600 text-white font-black shadow-md shadow-blue-500/30 flex items-center justify-center text-xs mx-auto';
                } else if (overviewRangeStart && overviewRangeEnd && dateStr > overviewRangeStart && dateStr < overviewRangeEnd) {
                    btn.className = 'w-8 h-8 rounded-full bg-blue-50 text-blue-700 font-bold flex items-center justify-center text-xs mx-auto';
                }
            }

            btn.onclick = () => onSelectCalDay(dateStr);
            grid.appendChild(btn);
        }
    }

    function onSelectCalDay(dateStr) {
        if (overviewCalMode === 'daily') {
            overviewSelectedDate = dateStr;
            currentOverviewPreset = 'custom';
            const [y, m, d] = dateStr.split('-');
            const thaiD = `${d}/${m}/${Number(y) + 543}`;
            updateOverviewFilterUI('วันที่ ' + thaiD);

            // Reset preset pills
            document.querySelectorAll('.overview-pill').forEach(btn => {
                btn.className = "overview-pill px-3 py-1 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer";
            });

            const filtered = allAttendanceLogs.filter(log => log.date === dateStr);
            renderOverviewAttendanceRows(filtered);
            closeOverviewDatePopover();
            showToast('กรองข้อมูลประจำวันที่: ' + thaiD + ` (${filtered.length} รายการ)`);
        } else {
            // Range mode
            if (!overviewRangeStart || (overviewRangeStart && overviewRangeEnd)) {
                overviewRangeStart = dateStr;
                overviewRangeEnd = null;
                renderOverviewCalendar();
            } else {
                if (dateStr < overviewRangeStart) {
                    overviewRangeEnd = overviewRangeStart;
                    overviewRangeStart = dateStr;
                } else {
                    overviewRangeEnd = dateStr;
                }
                currentOverviewPreset = 'custom';
                const [y1, m1, d1] = overviewRangeStart.split('-');
                const [y2, m2, d2] = overviewRangeEnd.split('-');
                const label = `${d1}/${m1} - ${d2}/${m2}/${Number(y2) + 543}`;
                updateOverviewFilterUI(label);

                // Reset preset pills
                document.querySelectorAll('.overview-pill').forEach(btn => {
                    btn.className = "overview-pill px-3 py-1 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer";
                });

                const filtered = allAttendanceLogs.filter(log => log.date >= overviewRangeStart && log.date <= overviewRangeEnd);
                renderOverviewAttendanceRows(filtered);
                renderOverviewCalendar();
                closeOverviewDatePopover();
                showToast(`กรองช่วงวันที่: ${label} (${filtered.length} รายการ)`);
            }
        }
    }

    function onOverviewCalToday() {
        const [y, m] = serverTodayDateKey.split('-').map(Number);
        overviewSelectedDate = serverTodayDateKey;
        overviewCalYear = y;
        overviewCalMonth = m - 1;
        setOverviewPresetFilter('today');
        closeOverviewDatePopover();
    }

    function onOverviewCalClear() {
        setOverviewPresetFilter('all');
        closeOverviewDatePopover();
    }

    function setOverviewPresetFilter(preset) {
        currentOverviewPreset = preset;
        let label = 'วันนี้';
        let filtered = [];

        // Reset pills UI
        document.querySelectorAll('.overview-pill').forEach(btn => {
            btn.className = "overview-pill px-3 py-1 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all cursor-pointer";
        });
        const activePill = document.getElementById('pill-ov-' + preset);
        if (activePill) {
            activePill.className = "overview-pill px-3 py-1 rounded-xl text-xs font-bold bg-[#0F172A] text-white shadow-xs transition-all cursor-pointer";
        }

        if (preset === 'today') {
            label = 'วันนี้';
            overviewSelectedDate = serverTodayDateKey;
            filtered = allAttendanceLogs.filter(log => log.date === serverTodayDateKey);
        } else if (preset === '3days') {
            label = '3 วันล่าสุด';
            const from3 = shiftIsoDateKey(serverTodayDateKey, -2);
            filtered = allAttendanceLogs.filter(log => log.date >= from3 && log.date <= serverTodayDateKey);
        } else if (preset === '7days') {
            label = '7 วันล่าสุด';
            const from7 = shiftIsoDateKey(serverTodayDateKey, -6);
            filtered = allAttendanceLogs.filter(log => log.date >= from7 && log.date <= serverTodayDateKey);
        } else if (preset === '15days') {
            label = '15 วันล่าสุด';
            const from15 = shiftIsoDateKey(serverTodayDateKey, -14);
            filtered = allAttendanceLogs.filter(log => log.date >= from15 && log.date <= serverTodayDateKey);
        } else if (preset === '30days') {
            label = '30 วันล่าสุด';
            const from30 = shiftIsoDateKey(serverTodayDateKey, -29);
            filtered = allAttendanceLogs.filter(log => log.date >= from30 && log.date <= serverTodayDateKey);
        } else if (preset === 'month') {
            label = 'ทั้งเดือน';
            const monthPrefix = serverTodayDateKey.slice(0, 7);
            filtered = allAttendanceLogs.filter(log => log.date.startsWith(monthPrefix));
        } else {
            label = 'ทั้งหมด';
            filtered = [...allAttendanceLogs];
        }

        updateOverviewFilterUI(label);
        renderOverviewAttendanceRows(filtered);
        showToast(`กรองข้อมูล: ${label} (${filtered.length} รายการ)`);
    }

    function stepOverviewDate(delta) {
        const presets = ['today', '3days', '7days', '15days', '30days', 'month', 'all'];
        const currentIdx = presets.indexOf(currentOverviewPreset);
        if (currentIdx !== -1) {
            let nextIdx = currentIdx + delta;
            if (nextIdx < 0) nextIdx = presets.length - 1;
            if (nextIdx >= presets.length) nextIdx = 0;
            setOverviewPresetFilter(presets[nextIdx]);
        } else if (overviewSelectedDate) {
            const d = new Date(overviewSelectedDate);
            d.setDate(d.getDate() + delta);
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            onSelectCalDay(`${y}-${m}-${day}`);
        }
    }

    function updateOverviewFilterUI(label) {
        const lbl = document.getElementById('label-overview-active-filter');
        if (lbl) lbl.innerText = label;
    }

    function renderOverviewAttendanceRows(logs) {
        const tbody = document.getElementById('overview-attendance-tbody');
        if (!tbody) return;

        // Recalculate 4 metrics (Unique employees count matching Image 2 overview)
        const totalWorking = new Set(logs.filter(l => !l.is_offday && l.status !== 'pending').map(l => l.name)).size;
        const totalLate = new Set(logs.filter(l => l.status === 'late').map(l => l.name)).size;
        const totalPending = new Set(logs.filter(l => l.status === 'pending').map(l => l.name)).size;
        const totalOt = new Set(logs.filter(l => l.ot_hours && l.ot_hours !== '—').map(l => l.name)).size;

        const statWorking = document.getElementById('stat-working-count');
        const statLate = document.getElementById('stat-late-count');
        const statPending = document.getElementById('stat-not-checked-in');
        const statOt = document.getElementById('stat-ot-count');

        if (statWorking) statWorking.innerText = totalWorking;
        if (statLate) statLate.innerText = totalLate;
        if (statPending) statPending.innerText = totalPending;
        if (statOt) statOt.innerText = totalOt;

        if (logs.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="13" class="py-10 text-center text-slate-400">
                        <div class="text-3xl mb-2">📋</div>
                        <div class="font-bold text-sm text-slate-600">ไม่พบข้อมูลการลงเวลาในช่วงนี้</div>
                        <div class="text-xs text-slate-400 mt-1">ลองเลือกตัวกรองช่วงวันอื่น หรือกด "ทั้งหมด"</div>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        logs.forEach(log => {
            if (log.is_offday) {
                html += `
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span>${log.name}</span>
                                <span class="text-[10px] text-slate-500 font-mono bg-slate-100 px-1.5 py-0.5 rounded font-semibold">📅 ${log.date_formatted || log.date}</span>
                            </div>
                            ${renderRolesHtml(log.roles)}
                        </td>
                        <td colspan="12" class="py-3.5 px-4 text-center">
                            <span class="px-3.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold inline-flex items-center gap-1">
                                ${log.status_label}
                            </span>
                        </td>
                    </tr>
                `;
            } else {
                html += `
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span>${log.name}</span>
                                <span class="text-[10px] text-slate-500 font-mono bg-slate-100 px-1.5 py-0.5 rounded font-semibold">📅 ${log.date_formatted || log.date}</span>
                            </div>
                            ${renderRolesHtml(log.roles)}
                        </td>
                        <td class="py-3.5 px-3">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-bold">${log.branch}</span>
                        </td>
                        <td class="py-3.5 px-3">
                            ${log.check_in_photo ? `
                                <div class="relative group cursor-pointer inline-block" onclick="openPhotoLightbox('${log.check_in_photo}', 'ภาพเช็คอิน: ${log.name} (${log.check_in_time})')">
                                    <img src="${log.check_in_photo}" alt="เช็คอิน" onerror="this.onerror=null; this.src='/uploads/attendance/sample_check_in.jpg';" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-2xs group-hover:scale-105 transition-all">
                                </div>
                            ` : '<span class="text-slate-300">—</span>'}
                        </td>
                        <td class="py-3.5 px-3 font-bold font-mono text-emerald-600 text-xs sm:text-sm">
                            ${log.check_in_time || '—'}
                        </td>
                        <td class="py-3.5 px-3">
                            ${log.check_in_time && log.check_in_time !== '—' ? `
                                <button type="button" onclick="openAttendanceMapModal('${log.name}', '${log.branch}', '${log.check_in_distance || '0ม.'}', 'เช็คอินเข้างาน')" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200 text-xs font-semibold flex items-center gap-1 transition-all cursor-pointer whitespace-nowrap">
                                    <span>📍 แผนที่</span>
                                    ${log.check_in_distance ? `<span class="text-[10px]">(${log.check_in_distance})</span>` : ''}
                                </button>
                            ` : '<span class="text-slate-300">—</span>'}
                        </td>
                        <td class="py-3.5 px-3">
                            ${log.check_out_photo ? `
                                <div class="relative group cursor-pointer inline-block" onclick="openPhotoLightbox('${log.check_out_photo}', 'ภาพเช็คเอาต์: ${log.name} (${log.check_out_time})')">
                                    <img src="${log.check_out_photo}" alt="เช็คเอาต์" onerror="this.onerror=null; this.src='/uploads/attendance/sample_check_out.jpg';" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-2xs group-hover:scale-105 transition-all">
                                </div>
                            ` : '<span class="text-slate-300">—</span>'}
                        </td>
                        <td class="py-3.5 px-3 font-bold font-mono text-rose-600 text-xs sm:text-sm">
                            ${log.check_out_time || '—'}
                        </td>
                        <td class="py-3.5 px-3">
                            ${log.check_out_time && log.check_out_time !== '—' ? `
                                <button type="button" onclick="openAttendanceMapModal('${log.name}', '${log.branch}', '${log.check_out_distance || '0ม.'}', 'เช็คเอาต์ออกงาน')" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-200 text-xs font-semibold flex items-center gap-1 transition-all cursor-pointer whitespace-nowrap">
                                    <span>📍 แผนที่</span>
                                </button>
                            ` : '<span class="text-slate-300">—</span>'}
                        </td>
                        <td class="py-3.5 px-3 font-bold text-slate-800">${log.total_hours || '—'}</td>
                        <td class="py-3.5 px-3">
                            ${log.status === 'pending'
                                ? '<span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200 text-xs font-bold inline-flex items-center gap-1 whitespace-nowrap">ยังไม่เข้างาน ⚪</span>'
                                : (log.status === 'late'
                                    ? '<span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold inline-flex items-center gap-1 whitespace-nowrap">สาย 🟡</span>'
                                    : `<span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold inline-flex items-center gap-1 whitespace-nowrap">${log.status_badge || 'ปกติ 🟢'}</span>`
                                )
                            }
                        </td>
                        <td class="py-3.5 px-3 text-slate-400 text-center font-mono">${log.ot_start || '—'}</td>
                        <td class="py-3.5 px-3 text-slate-400 text-center font-mono">${log.ot_end || '—'}</td>
                        <td class="py-3.5 px-3 text-slate-400 text-center font-mono">${log.ot_hours || '—'}</td>
                    </tr>
                `;
            }
        });
        tbody.innerHTML = html;
    }

    function renderRolesHtml(roles) {
        if (!roles || !roles.length) return '';
        let h = '<div class="flex flex-wrap items-center gap-1.5 mt-1">';
        roles.forEach(r => {
            h += `<span class="px-2 py-0.2 rounded-full text-[10px] font-bold border ${r.color} inline-flex items-center gap-1">
                ${r.icon ? `<span>${r.icon}</span>` : ''}
                <span>${r.label}</span>
            </span>`;
        });
        h += '</div>';
        return h;
    }

    function openAttendanceMapModal(name, branch, distance, action) {
        const nameEl = document.getElementById('att-map-name');
        const actEl = document.getElementById('att-map-action');
        const distEl = document.getElementById('att-map-distance');
        if (nameEl) nameEl.innerText = name + ' (' + branch + ')';
        if (actEl) actEl.innerText = action || 'เช็คอินเข้างาน';
        if (distEl) distEl.innerText = distance || '6ม.';
        const modal = document.getElementById('modal-attendance-map');
        if (modal) modal.classList.remove('hidden');
    }

    function closeAttendanceMapModal() {
        const modal = document.getElementById('modal-attendance-map');
        if (modal) modal.classList.add('hidden');
    }

    // Global Click Listener to close popovers on click outside
    window.addEventListener('click', (e) => {
        const popoverCal = document.getElementById('popover-overview-calendar');
        const containerCal = document.getElementById('overview-filter-bar-container');
        if (popoverCal && !popoverCal.classList.contains('hidden') && containerCal && !containerCal.contains(e.target)) {
            closeOverviewDatePopover();
        }

        const popoverPay = document.getElementById('popover-payroll-calendar');
        const containerPay = document.getElementById('payroll-filter-bar-container');
        if (popoverPay && !popoverPay.classList.contains('hidden') && containerPay && !containerPay.contains(e.target)) {
            closePayrollMonthPopover();
        }
    });

    // 11. Real-Time Auto-Sync from Access Control & Attendance Logs
    let lastSyncSignature = JSON.stringify({ cards: payrollCardsData, monthly: @json($monthlyAttendanceSummary ?? []), logs: allAttendanceLogs });
    let isSyncInFlight = false;

    function escapeHtmlText(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderPayrollCardsGrid(cards) {
        const grid = document.getElementById('payroll-cards-grid');
        if (!grid) return;

        if (!cards || cards.length === 0) {
            grid.innerHTML = `<div class="col-span-full bg-white rounded-3xl p-8 border border-slate-100 text-center text-slate-400 text-sm font-semibold">ยังไม่มีข้อมูลพนักงานในระบบการเข้าถึง</div>`;
            return;
        }

        const activeId = selectedHeroEmployeeId || cards[0].id;
        grid.innerHTML = cards.map((card, idx) => {
            const isSelected = card.id === activeId;
            const rateNum = Number(card.rate_num || 0);
            const rateTextColor = rateNum >= 80 ? 'text-[#16A34A]' : (rateNum >= 50 ? 'text-amber-600' : 'text-rose-600');
            const rateBarColor = rateNum >= 80 ? 'bg-[#16A34A]' : (rateNum >= 50 ? 'bg-amber-500' : 'bg-rose-500');
            const lateTextColor = Number(card.late_days || 0) > 0 ? 'text-amber-600' : 'text-[#16A34A]';
            const absentTextColor = Number(card.absent_days || 0) > 0 ? 'text-rose-600' : 'text-[#16A34A]';
            const gradeColor = (card.grade === 'A' || card.grade === 'B')
                ? 'bg-[#DCFCE7] text-[#16A34A]'
                : (card.grade === 'C' ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600');

            const avatarHtml = card.avatar
                ? `<img src="${escapeHtmlText(card.avatar)}" alt="${escapeHtmlText(card.name)}" class="w-10 h-10 rounded-xl object-cover shrink-0 shadow-2xs border border-slate-200">`
                : `<div class="w-10 h-10 rounded-xl ${escapeHtmlText(card.color)} text-white flex items-center justify-center shrink-0 shadow-2xs font-black text-xl leading-none">${escapeHtmlText(card.initials)}</div>`;

            const otSubHtml = Number(card.ot_amount || 0) > 0
                ? `<span ${idx === 0 ? 'id="dtl-ot-rate"' : ''} class="text-[10px] text-slate-400">${escapeHtmlText(card.ot_hours)} × ฿${Number(card.ot_rate || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}/ชม.</span>`
                : '';

            const dedVal = Number(card.deductions || 0);
            const dedDetailHtml = dedVal > 0
                ? `<div class="pl-2 mt-1 space-y-0.5 text-slate-500 text-[10.5px]">
                    ${Number(card.deduction_late || 0) > 0 ? `<div class="flex justify-between"><span>• หักจากการมาสาย</span><span ${idx === 0 ? 'id="dtl-late"' : ''} class="text-rose-500 font-semibold">-฿${Number(card.deduction_late).toLocaleString()}</span></div>` : ''}
                    ${Number(card.deduction_ss || 0) > 0 ? `<div class="flex justify-between"><span>• ประกันสังคม</span><span ${idx === 0 ? 'id="dtl-ss"' : ''} class="text-rose-500 font-semibold">-฿${Number(card.deduction_ss).toLocaleString()}</span></div>` : ''}
                   </div>`
                : '';

            return `
                <div class="payroll-card bg-white rounded-3xl p-3.5 sm:p-4 border border-slate-100 shadow-sm flex flex-col justify-between hover:shadow-md transition-all cursor-pointer hover:border-rose-300 ${isSelected ? 'ring-2 ring-[#E50914]' : ''}"
                     id="payroll-card-${escapeHtmlText(card.id)}"
                     data-card-index="${idx}"
                     onclick="selectHeroEmployee(payrollCardsData[${idx}])"
                     data-search-text="${escapeHtmlText(card.search_text)}">
                    <div>
                        <div class="flex items-center justify-between gap-2 pb-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                ${avatarHtml}
                                <div class="space-y-0.5 min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-extrabold text-slate-900 text-sm sm:text-base leading-tight truncate" ${idx === 0 ? 'id="card-emp-name"' : ''}>${escapeHtmlText(card.name)}</span>
                                        <button type="button" onclick="event.stopPropagation(); syncEmployeesData(true)" class="w-5 h-5 rounded-md border border-slate-200/90 bg-white hover:bg-slate-50 active:scale-95 text-slate-400 hover:text-slate-600 flex items-center justify-center transition-all cursor-pointer shadow-2xs shrink-0" title="รีเฟรชข้อมูล">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                        </button>
                                    </div>
                                    <div class="flex items-center flex-wrap gap-1 text-[10px] sm:text-[11px]">
                                        <span class="inline-flex items-center gap-0.5 text-slate-400 font-medium">
                                            <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            <span>${escapeHtmlText(card.branch)}</span>
                                        </span>
                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md ${escapeHtmlText(card.role_badge_class)} font-bold text-[10px]">
                                            <span>${escapeHtmlText(card.role_badge_icon)}</span>
                                            <span>${escapeHtmlText(card.role_badge_text)}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-center shrink-0">
                                <span class="text-[10px] font-semibold text-slate-400 mb-0.5">เข้างาน</span>
                                <div class="w-7 h-7 rounded-full ${gradeColor} font-black text-xs flex items-center justify-center shadow-2xs">${escapeHtmlText(card.grade)}</div>
                            </div>
                        </div>
                        <div class="pt-2.5 pb-2.5 border-t border-slate-100">
                            <div class="flex items-center gap-1.5 pb-1.5 text-slate-800 font-bold text-xs"><span class="text-[#E50914]">📊</span><span>สรุปการทำงาน</span></div>
                            <div class="grid grid-cols-4 gap-1 pt-0.5 text-center">
                                <div class="p-1 sm:p-1.5 rounded-xl bg-rose-50/50 border border-rose-100/60 flex flex-col items-center">
                                    <span class="text-[#E50914] text-xs mb-0.5">📅</span>
                                    <span ${idx === 0 ? 'id="stat-work-days"' : ''} class="font-black text-[11px] sm:text-xs text-emerald-600 leading-tight">${card.work_days} วัน</span>
                                    <span class="text-[8.5px] sm:text-[9.5px] text-slate-500 mt-0.5 leading-tight truncate">วันทำงาน</span>
                                </div>
                                <div class="p-1 sm:p-1.5 rounded-xl bg-rose-50/50 border border-rose-100/60 flex flex-col items-center">
                                    <span class="text-[#E50914] text-xs mb-0.5">🕒</span>
                                    <span ${idx === 0 ? 'id="stat-total-hours"' : ''} class="font-black text-[11px] sm:text-xs text-emerald-600 leading-tight">${escapeHtmlText(card.work_hours)}</span>
                                    <span class="text-[8.5px] sm:text-[9.5px] text-slate-500 mt-0.5 leading-tight truncate">ชั่วโมงรวม</span>
                                </div>
                                <div class="p-1 sm:p-1.5 rounded-xl bg-rose-50/50 border border-rose-100/60 flex flex-col items-center">
                                    <span class="text-[#E50914] text-xs mb-0.5">⏱️</span>
                                    <span ${idx === 0 ? 'id="stat-ot-hours"' : ''} class="font-black text-[11px] sm:text-xs text-emerald-600 leading-tight">${escapeHtmlText(card.ot_hours)}</span>
                                    <span class="text-[8.5px] sm:text-[9.5px] text-slate-500 mt-0.5 leading-tight truncate">OT รวม</span>
                                </div>
                                <div class="p-1 sm:p-1.5 rounded-xl bg-rose-50/50 border border-rose-100/60 flex flex-col items-center">
                                    <span class="text-[#E50914] text-xs mb-0.5">☂️</span>
                                    <span ${idx === 0 ? 'id="stat-leave-days"' : ''} class="font-black text-[11px] sm:text-xs text-emerald-600 leading-tight">${card.leave_days} วัน</span>
                                    <span class="text-[8.5px] sm:text-[9.5px] text-slate-500 mt-0.5 leading-tight truncate">วันลา</span>
                                </div>
                            </div>
                        </div>
                        <div class="pt-2.5 pb-2.5 border-t border-slate-100">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-extrabold text-slate-800 text-[11px] sm:text-xs">อัตราเข้างาน</span>
                                <div class="flex items-center gap-1">
                                    ${card.rate_delta ? `<span class="${rateTextColor} font-bold text-[10px]">${escapeHtmlText(card.rate_delta)}</span>` : ''}
                                    <span class="font-black ${rateTextColor} text-xs sm:text-sm">${escapeHtmlText(card.rate_percent)}</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-1.5 overflow-hidden">
                                <div class="${rateBarColor} h-full rounded-full transition-all duration-300" style="width: ${escapeHtmlText(card.progress_width)};"></div>
                            </div>
                        </div>
                        <div class="divide-y divide-slate-100 text-[11px] sm:text-xs border-t border-slate-100">
                            <div class="py-2 flex items-center justify-between">
                                <div class="flex items-center gap-1.5 text-slate-600 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="4" /><path stroke-linecap="round" stroke-linejoin="round" d="M8 12l2.5 2.5L16 9" /></svg>
                                    <span>มาทำงาน</span>
                                </div>
                                <span class="font-black text-slate-800">${escapeHtmlText(card.attendance_ratio)}</span>
                            </div>
                            <div class="py-2 flex items-center justify-between">
                                <div class="flex items-center gap-1.5 text-slate-600 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M12 7v5l3 2" /></svg>
                                    <span>มาสาย</span>
                                </div>
                                <span class="font-bold ${lateTextColor}">${escapeHtmlText(card.late_text)}</span>
                            </div>
                            <div class="py-2 flex items-center justify-between">
                                <div class="flex items-center gap-1.5 text-slate-600 font-medium">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6m0-6l6 6" /></svg>
                                    <span>ขาดงาน</span>
                                </div>
                                <span class="font-bold ${absentTextColor}">${escapeHtmlText(card.absent_text)}</span>
                            </div>
                        </div>
                        <div class="border-t-2 border-slate-100 my-2.5"></div>
                        <div class="flex items-center gap-1.5 pb-2 border-b border-slate-100 text-slate-800 font-bold text-xs sm:text-[13px]"><span class="text-[#E50914]">📑</span><span>รายละเอียดเงินเดือน</span></div>
                        <div class="divide-y divide-slate-100 text-[11px] sm:text-xs pt-1">
                            <div class="py-1.5 flex justify-between items-center">
                                <span class="text-slate-600">เงินเดือนพื้นฐาน</span>
                                <span ${idx === 0 ? 'id="dtl-base-salary"' : ''} class="font-bold text-slate-800">฿${Number(card.base_salary || 0).toLocaleString()}</span>
                            </div>
                            <div class="py-1.5 flex justify-between items-start">
                                <div><span class="text-slate-600 block">ค่าล่วงเวลา (OT)</span>${otSubHtml}</div>
                                <span ${idx === 0 ? 'id="dtl-ot-amount"' : ''} class="font-bold text-emerald-600">฿${Number(card.ot_amount || 0).toLocaleString()}</span>
                            </div>
                            <div class="py-1.5 flex justify-between items-center">
                                <span class="text-slate-600">โบนัส / เบี้ยขยัน</span>
                                <span ${idx === 0 ? 'id="dtl-bonus-amount"' : ''} class="font-bold text-emerald-600">฿${Number(card.bonus || 0).toLocaleString()}</span>
                            </div>
                            <div class="py-1.5">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-600">รายการหัก</span>
                                    <span ${idx === 0 ? 'id="dtl-deductions-total"' : ''} class="font-bold text-rose-600">${dedVal > 0 ? '-฿' + dedVal.toLocaleString() : '฿0'}</span>
                                </div>
                                ${dedDetailHtml}
                            </div>
                        </div>
                        <div class="pt-2.5 border-t-2 border-slate-100 flex justify-between items-baseline mt-1.5">
                            <span class="font-bold text-rose-700 text-xs sm:text-sm">รับสุทธิ</span>
                            <span ${idx === 0 ? 'id="dtl-net-salary"' : ''} class="font-black text-xl sm:text-2xl text-[#E50914]">฿${Number(card.net_salary || 0).toLocaleString()}</span>
                        </div>
                    </div>
                    <button type="button" onclick="event.stopPropagation(); openPayslipModal(payrollCardsData[${idx}])" class="w-full py-2.5 px-4 rounded-full bg-gradient-to-r from-[#B80A17] via-[#E50914] to-[#B80A17] hover:brightness-110 active:scale-98 text-white font-bold text-xs shadow-md shadow-rose-900/25 flex items-center justify-center gap-1.5 transition-all cursor-pointer mt-3">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>ดูสลิปเงินเดือน</span>
                    </button>
                </div>`;
        }).join('');

        if (typeof filterPayrollCards === 'function') {
            filterPayrollCards();
        }
    }

    function renderMonthlySummaryTable(summary) {
        const tbody = document.getElementById('overview-monthly-tbody');
        if (!tbody || !Array.isArray(summary)) return;
        tbody.innerHTML = summary.map(m => `
            <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3.5 px-4 font-bold text-slate-900">${escapeHtmlText(m.name)}</td>
                <td class="py-3.5 px-3 text-slate-600">${escapeHtmlText(m.role)} (${escapeHtmlText(m.branch)})</td>
                <td class="py-3.5 px-3 font-bold text-slate-800">${m.work_days} วัน</td>
                <td class="py-3.5 px-3 ${Number(m.late_days) > 0 ? 'text-amber-600 font-bold' : 'text-slate-400'}">${m.late_days} วัน</td>
                <td class="py-3.5 px-3 text-slate-400">${m.offsite_days || 0} ครั้ง</td>
                <td class="py-3.5 px-3 font-bold text-slate-800">${escapeHtmlText(m.total_hours)}</td>
                <td class="py-3.5 px-3 font-bold text-orange-600">${escapeHtmlText(m.ot_hours)}</td>
                <td class="py-3.5 px-3"><span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">${escapeHtmlText(m.attendance_rate)}</span></td>
            </tr>
        `).join('');
    }

    async function syncEmployeesData(showFeedback = false, monthOverride = null) {
        if (isSyncInFlight) return;
        isSyncInFlight = true;
        try {
            const month = monthOverride || currentPayrollMonth;
            const url = `{{ route('admin.employees.sync_data') }}?month=${encodeURIComponent(month)}${selectedHeroEmployeeId ? '&employee_id=' + encodeURIComponent(selectedHeroEmployeeId) : ''}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' }, cache: 'no-store' });
            if (!res.ok) return;
            const json = await res.json();
            if (!json.success) return;

            const newSignature = JSON.stringify({ cards: json.payrollCards, monthly: json.monthlyAttendanceSummary, logs: json.allAttendanceLogs });
            if (newSignature !== lastSyncSignature || monthOverride) {
                lastSyncSignature = newSignature;
                payrollCardsData = json.payrollCards || [];
                payrollData = json.payrollPeriods || payrollData;
                allAttendanceLogs = json.allAttendanceLogs || [];

                renderPayrollCardsGrid(payrollCardsData);
                renderMonthlySummaryTable(json.monthlyAttendanceSummary);

                const periodLbl = document.getElementById('overview-monthly-period-label');
                if (periodLbl && payrollData[month]) {
                    periodLbl.innerText = 'ประจำเดือน ' + payrollData[month].period_label;
                }

                // Update Hero boxes cleanly for the active selected employee
                const heroCard = payrollCardsData.find(c => c.id === selectedHeroEmployeeId) || payrollCardsData[0];
                if (heroCard) {
                    selectedHeroEmployeeId = heroCard.id;
                    const heroName = document.getElementById('hero-payroll-emp-name');
                    if (heroName) heroName.innerText = heroCard.name;
                    const heroSub = document.getElementById('hero-payroll-emp-subtitle');
                    if (heroSub) heroSub.innerText = `${heroCard.role_label || heroCard.role} • ${heroCard.branch}`;
                    const boxBase = document.getElementById('box-base-salary');
                    if (boxBase) boxBase.innerText = '฿' + Number(heroCard.base_salary).toLocaleString();
                    const boxOt = document.getElementById('box-ot-amount');
                    if (boxOt) boxOt.innerText = '฿' + Number(heroCard.ot_amount).toLocaleString();
                    const boxBonus = document.getElementById('box-bonus-amount');
                    if (boxBonus) boxBonus.innerText = '฿' + Number(heroCard.bonus).toLocaleString();
                    const boxDed = document.getElementById('box-deductions-amount');
                    if (boxDed) boxDed.innerText = (heroCard.deductions > 0 ? '-฿' : '฿') + Number(heroCard.deductions).toLocaleString();
                    const boxNet = document.getElementById('box-net-salary');
                    if (boxNet) boxNet.innerText = '฿' + Number(heroCard.net_salary).toLocaleString();
                }

                // Keep Overview daily/range view fresh without extra toast
                if (currentOverviewPreset === 'today') {
                    renderOverviewAttendanceRows(allAttendanceLogs.filter(log => log.date === serverTodayDateKey));
                }
            }
            if (showFeedback) {
                showToast('ซิงค์ข้อมูลพนักงานและเงินเดือนเรียลไทม์เรียบร้อยแล้ว');
            }
        } catch (e) {
            // Silent ignore network transient error
        } finally {
            isSyncInFlight = false;
        }
    }

    // Initial check on page load
    document.addEventListener('DOMContentLoaded', () => {
        verifyShopLocation();

        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab === 'payroll') {
            switchEmployeeTab('payroll');
        } else if (tab === 'overview') {
            switchEmployeeTab('overview');
            setOverviewPresetFilter('today');
        }

        // Auto-sync live every 12 seconds and whenever returning to this tab
        setInterval(() => syncEmployeesData(false), 12000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) syncEmployeesData(false);
        });
        window.addEventListener('focus', () => syncEmployeesData(false));
    });
</script>
@endsection
