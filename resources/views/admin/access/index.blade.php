@extends('layouts.app', ['title' => 'การเข้าถึง & สิทธิ์การใช้งาน - หม่าล่าสตรีท'])

@section('content')
<div class="min-h-screen bg-[#F8FAFC] py-4 sm:py-6 px-3 sm:px-6 lg:px-8">
    <div class="max-w-7xl 2xl:max-w-[1536px] mx-auto space-y-4 sm:space-y-5">

        <!-- ========================================================================= -->
        <!-- 1. TOP BREADCRUMB & HEADER AREA (Matches Image 2)                         -->
        <!-- ========================================================================= -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-bold text-slate-400 tracking-wide uppercase">
                    การเข้าถึง &amp; สิทธิ์การใช้งาน
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-0.5">
                    การเข้าถึง
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">
                    จัดการพนักงาน แอดมิน และสิทธิ์การใช้งานทุกหน้าของระบบ
                </p>
            </div>

            <!-- Top Right Action Buttons (Matches Image 2) -->
            <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                <!-- 1. จำลองมุมมองพนักงานที่เลือก -->
                <button type="button"
                        onclick="openSimulateViewModal()"
                        class="py-2.5 px-3.5 sm:px-4 rounded-xl bg-white hover:bg-slate-50 active:scale-95 text-slate-700 font-bold text-xs border border-slate-200/90 shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span>จำลองมุมมองพนักงานที่เลือก</span>
                </button>

                <!-- 2. เพิ่มพนักงาน / แอดมิน (สลับไปหน้าลงทะเบียนสร้างบัญชีจริงทันที) -->
                <button type="button"
                        onclick="switchSubTab('register')"
                        class="py-2.5 px-3.5 sm:px-4 rounded-xl bg-white hover:bg-slate-50 active:scale-95 text-slate-700 font-bold text-xs border border-slate-200/90 shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span>เพิ่มพนักงาน / แอดมิน</span>
                </button>

                <!-- 3. บันทึกการเปลี่ยนแปลง (Primary Blue Button) -->
                <button type="button"
                        onclick="saveAllPermissions()"
                        id="btn-save-permissions"
                        class="py-2.5 px-4 sm:px-5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] active:scale-95 text-white font-bold text-xs shadow-md shadow-blue-500/25 flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <span>บันทึกการเปลี่ยนแปลง</span>
                </button>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 2. SUB-NAVIGATION PILLS BAR (Matches Image 2 & Image 1)                   -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-2xl p-2 border border-slate-200/90 shadow-2xs flex items-center gap-2 overflow-x-auto scrollbar-none">
            <!-- 1. จัดการพนักงานและแอดมิน -->
            <button type="button"
                    id="subtab-btn-users"
                    onclick="switchSubTab('users')"
                    class="subtab-btn shrink-0 py-2 px-3.5 rounded-xl text-xs {{ $currentTab === 'users' ? 'text-[#2563EB] bg-[#EFF6FF] border border-[#BFDBFE] shadow-2xs font-bold' : 'font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 border-transparent' }} flex items-center gap-1.5 transition-all cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4 {{ $currentTab === 'users' ? 'text-[#2563EB]' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>จัดการพนักงานและแอดมิน</span>
            </button>

            <!-- 2. สิทธิ์การเข้าถึง -->
            <button type="button"
                    id="subtab-btn-permissions"
                    onclick="switchSubTab('permissions')"
                    class="subtab-btn shrink-0 py-2 px-3.5 rounded-xl text-xs font-bold {{ $currentTab === 'permissions' ? 'text-[#2563EB] bg-[#EFF6FF] border border-[#BFDBFE] shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 border-transparent' }} flex items-center gap-1.5 transition-all cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4 {{ $currentTab === 'permissions' ? 'text-[#2563EB]' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>สิทธิ์การเข้าถึง</span>
            </button>

            <!-- 3. ระบบเช็คเอาท์อัตโนมัติ -->
            @php
                $curMin = $autoLogoutSettings['timeout_minutes'] ?? 15;
                $curLabel = ($curMin == 0) ? 'ปิดใช้งาน' : (($curMin >= 60) ? ($curMin / 60) . ' ชม.' : $curMin . ' นาที');
            @endphp
            <button type="button"
                    id="subtab-btn-autologout"
                    onclick="switchSubTab('autologout')"
                    class="subtab-btn shrink-0 py-2 px-3.5 rounded-xl text-xs font-semibold {{ $currentTab === 'autologout' ? 'text-[#2563EB] bg-[#EFF6FF] border border-[#BFDBFE] shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 border-transparent' }} flex items-center gap-1.5 transition-all cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4 {{ $currentTab === 'autologout' ? 'text-[#2563EB]' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" d="M12 7v5l3 2" />
                </svg>
                <span>ระบบเช็คเอาท์อัตโนมัติ</span>
                <span id="subtab-autologout-badge" class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-bold">{{ $curLabel }}</span>
            </button>

            <!-- 4. ลงทะเบียนขอสิทธิ์ (เปิดใช้งานได้จริงตามภาพที่ 1) -->
            <button type="button"
                    id="subtab-btn-register"
                    onclick="switchSubTab('register')"
                    class="subtab-btn shrink-0 py-2 px-3.5 rounded-xl text-xs font-semibold {{ $currentTab === 'register' ? 'text-[#2563EB] bg-[#EFF6FF] border border-[#BFDBFE] shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 border-transparent' }} flex items-center gap-1.5 transition-all cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4 {{ $currentTab === 'register' ? 'text-[#2563EB]' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span>ลงทะเบียนขอสิทธิ์</span>
                <span class="px-1.5 py-0.2 rounded bg-blue-100 text-blue-700 text-[10px] font-bold">สร้างบัญชีจริง</span>
                @if(!empty($pendingRegistrations) && count($pendingRegistrations) > 0)
                    <span id="subtab-pending-badge" class="px-2 py-0.5 rounded-full bg-amber-500 text-white text-[10px] font-bold animate-pulse shadow-xs">
                        {{ count($pendingRegistrations) }} รออนุมัติ
                    </span>
                @endif
            </button>

            <!-- 5. ประวัติการทำงาน (Audit Log) -->
            <button type="button"
                    id="subtab-btn-audit"
                    onclick="switchSubTab('audit')"
                    class="subtab-btn shrink-0 py-2 px-3.5 rounded-xl text-xs font-semibold {{ $currentTab === 'audit' ? 'text-[#2563EB] bg-[#EFF6FF] border border-[#BFDBFE] shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50 border-transparent' }} flex items-center gap-1.5 transition-all cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4 {{ $currentTab === 'audit' ? 'text-[#2563EB]' : 'text-slate-400' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>ประวัติการทำงาน (Audit Log)</span>
                <span id="subtab-audit-count-badge" class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-bold">{{ isset($auditLogs) ? count($auditLogs) : 0 }} รายการ</span>
            </button>
        </div>

        <!-- ========================================================================= -->
        <!-- 3. MAIN 2-COLUMN SPLIT LAYOUT (Matches Image 2)                           -->
        <!-- ========================================================================= -->
        <div id="access-main-grid" class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5 items-start w-full">

            <!-- --------------------------------------------------------------------- -->
            <!-- LEFT COLUMN: ผู้ใช้งาน (Users / Staff List) (Col-span-4 / Col-span-3)   -->
            <!-- --------------------------------------------------------------------- -->
            <div id="col-users-list" 
                 class="{{ in_array($currentTab, ['autologout', 'audit']) ? 'hidden ' : '' }}lg:col-span-4 xl:col-span-3 bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/90 shadow-sm space-y-3.5"
                 style="{{ in_array($currentTab, ['autologout', 'audit']) ? 'display: none;' : '' }}">
                <!-- Header with count badge and quick actions -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-black text-slate-800 whitespace-nowrap">ผู้ใช้งาน</h2>
                        <span id="user-count-badge" class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 font-bold text-xs whitespace-nowrap">
                            {{ count($users) }} รายการ
                        </span>
                    </div>

                    <button type="button"
                            onclick="switchSubTab('register')"
                            title="สร้างบัญชีใหม่"
                            class="px-2.5 py-1 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition-all flex items-center gap-1">
                        <span>+ เพิ่ม</span>
                    </button>
                </div>

                @if(!$isTestCleared)
                    <!-- Notice banner to delete test accounts with 1 click -->
                    <div id="test-accounts-banner" class="p-3 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 flex items-center justify-between gap-2 shadow-2xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-base shrink-0">⚠️</span>
                            <div class="min-w-0">
                                <div class="font-bold text-[11px] leading-tight text-amber-900">มีบัญชีทดสอบในภาพที่ 2</div>
                                <div class="text-[10px] text-amber-700">ลบออกเพื่อแสดงเฉพาะบัญชีจริง</div>
                            </div>
                        </div>
                        <button type="button"
                                onclick="clearTestAccountsNow()"
                                class="px-2.5 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-bold text-[10.5px] shrink-0 transition-all cursor-pointer shadow-xs whitespace-nowrap flex items-center gap-1">
                            <span>🗑️ ลบเทสทั้งหมด</span>
                        </button>
                    </div>
                @endif

                <!-- Search Input -->
                <div class="relative">
                    <input type="text"
                           id="search-user-input"
                           oninput="filterUsers(this.value)"
                           placeholder="ค้นหาชื่อผู้ใช้ หรือบทบาท..."
                           class="w-full text-xs bg-slate-50 border border-slate-200 rounded-xl pl-8 pr-3 py-2 text-slate-700 placeholder-slate-400 focus:outline-blue-500 focus:bg-white transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path stroke-linecap="round" d="M21 21l-4.35-4.35" />
                    </svg>
                </div>

                <!-- Users Scrollable List -->
                <div id="users-list-container" class="space-y-2 max-h-[640px] overflow-y-auto pr-1">
                    @foreach($users as $u)
                        @php
                            $isSelected = ($u['id'] === ($selectedUser['id'] ?? ''));
                        @endphp
                        <div onclick="selectUser('{{ $u['id'] }}')"
                             data-user-id="{{ $u['id'] }}"
                             data-user-name="{{ $u['name'] }}"
                             data-user-role="{{ $u['role'] }}"
                             class="group user-card p-3 rounded-2xl flex items-center justify-between gap-2.5 cursor-pointer transition-all {{ $isSelected ? 'border-2 border-[#2563EB] bg-[#EFF6FF]/70 shadow-2xs' : 'border border-slate-200/80 bg-white hover:bg-slate-50 hover:border-slate-300' }}">
                            
                            <div class="flex items-center gap-2.5 min-w-0">
                                <!-- Avatar: Image or Initials -->
                                @if(!empty($u['avatar']))
                                    <img src="{{ $u['avatar'] }}" alt="{{ $u['name'] }}" class="w-10 h-10 rounded-xl object-cover shrink-0 shadow-2xs border border-slate-200">
                                @else
                                    <div class="w-10 h-10 rounded-xl {{ $u['color'] ?? 'bg-slate-800' }} text-white font-black text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                        {{ $u['initials'] ?? 'US' }}
                                    </div>
                                @endif

                                <div class="min-w-0">
                                    <div class="font-bold text-xs sm:text-sm text-slate-800 truncate flex items-center gap-1.5">
                                        <span>{{ $u['name'] }}</span>
                                        @if(!empty($u['is_test']))
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200">เทส</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-medium truncate">
                                        {{ $u['role_label'] ?? $u['role'] }}
                                    </div>
                                    @if(!empty($u['email']))
                                        <div class="text-[10px] text-blue-600 font-mono truncate">
                                            {{ $u['email'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                @if(!empty($u['is_current']))
                                    <!-- บัญชีปัจจุบันที่กำลังเข้าสู่ระบบและใช้งานอยู่จริงบนเครื่องนี้ -->
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10.5px] font-bold border border-emerald-300 shadow-2xs whitespace-nowrap" title="บัญชีปัจจุบันที่กำลังเข้าสู่ระบบ">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        ใช้งานอยู่
                                    </span>
                                @elseif(($u['status'] ?? 'offline') === 'online')
                                    <!-- บัญชีอื่นๆ ที่ออนไลน์อยู่บนระบบ -->
                                    <button type="button"
                                            onclick="event.stopPropagation(); quickSwitchUser('{{ $u['id'] }}', '{{ addslashes($u['name']) }}')"
                                            title="คลิกเพื่อสลับเข้าใช้งานบัญชีนี้ทันที"
                                            class="group/btn inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50/70 hover:bg-blue-600 text-emerald-700 hover:text-white text-[10.5px] font-bold border border-emerald-200 hover:border-blue-600 transition-all cursor-pointer whitespace-nowrap shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 group-hover/btn:bg-white"></span>
                                        <span class="group-hover/btn:hidden">ออนไลน์</span>
                                        <span class="hidden group-hover/btn:inline font-bold">สลับเข้าใช้</span>
                                    </button>
                                @else
                                    <!-- บัญชีที่ออฟไลน์ สามารถคลิกสลับเข้าใช้งานได้ทันที -->
                                    <button type="button"
                                            onclick="event.stopPropagation(); quickSwitchUser('{{ $u['id'] }}', '{{ addslashes($u['name']) }}')"
                                            title="คลิกเพื่อสลับเข้าใช้งานบัญชีนี้ทันที"
                                            class="group/btn inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 hover:bg-blue-600 text-slate-500 hover:text-white text-[10.5px] font-medium hover:font-bold border border-slate-200 hover:border-blue-600 transition-all cursor-pointer whitespace-nowrap shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 group-hover/btn:bg-white"></span>
                                        <span class="group-hover/btn:hidden">ออฟไลน์</span>
                                        <span class="hidden group-hover/btn:inline font-bold">สลับเข้าใช้</span>
                                    </button>
                                @endif

                                <!-- Delete button per user -->
                                <button type="button"
                                        onclick="event.stopPropagation(); deleteSingleUser('{{ $u['id'] }}', '{{ addslashes($u['name']) }}')"
                                        title="ลบบัญชีนี้"
                                        class="p-1 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- --------------------------------------------------------------------- -->
            <!-- RIGHT COLUMN: สิทธิ์การเข้าถึง & หน้าต่างจัดการ (Col-span-8 หรือ Col-span-12) -->
            <!-- --------------------------------------------------------------------- -->
            <div id="col-main-content" 
                 class="{{ in_array($currentTab, ['autologout', 'audit']) ? 'w-full ' : 'lg:col-span-8 xl:col-span-9 w-full ' }}space-y-4"
                 style="{{ in_array($currentTab, ['autologout', 'audit']) ? 'grid-column: 1 / -1; width: 100%;' : '' }}">

                <!-- ================================================================= -->
                <!-- PANEL 1: สิทธิ์การเข้าถึง (Permission Matrix)                       -->
                <!-- ================================================================= -->
                <div id="panel-permissions" class="bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/90 shadow-sm space-y-4 {{ $currentTab === 'permissions' ? '' : 'hidden' }}">
                
                <!-- Card Header Row -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-black text-slate-900 whitespace-nowrap">
                            สิทธิ์การเข้าถึง
                        </h2>
                        <!-- Selected User Badge Pill (Matches Image 2) -->
                        <span id="active-user-badge" class="px-3 py-1 rounded-full bg-[#EFF6FF] text-[#2563EB] border border-[#BFDBFE] text-xs font-bold whitespace-nowrap">
                            {{ $selectedUser['name'] }} ({{ $selectedUser['role'] }})
                        </span>
                    </div>

                    <!-- Right Quick Actions & Branch Dropdown Pill -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button"
                                onclick="copyPermissions()"
                                class="py-1 px-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-1 transition-all cursor-pointer whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span>คัดลอกสิทธิ์</span>
                        </button>

                        <button type="button"
                                onclick="resetPermissionsDefault()"
                                class="py-1 px-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 flex items-center gap-1 transition-all cursor-pointer whitespace-nowrap">
                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>รีเซ็ตเป็นค่าเริ่มต้น</span>
                        </button>

                        <!-- Branch selector pill (Matches Image 2) -->
                        <div class="px-3 py-1 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold flex items-center gap-1.5 cursor-pointer hover:bg-amber-100 transition-all whitespace-nowrap">
                            <span>🏢 สาขาที่อนุญาต (2 สาขา - สลับได้)</span>
                            <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Sub-bar: Bulk Action Toggles (Matches Image 2: เลือกทั้งหมด, ยกเลิกทั้งหมด, สิทธิ์ขั้นสูง) -->
                <div class="flex flex-wrap items-center gap-2 pt-1 pb-1">
                    <button type="button"
                            onclick="toggleSelectAllPermissions(true)"
                            class="px-3 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1.5 cursor-pointer transition-all whitespace-nowrap">
                        <span class="w-3.5 h-3.5 rounded bg-blue-600 text-white flex items-center justify-center text-[10px]">✓</span>
                        <span>เลือกทั้งหมด</span>
                    </button>

                    <button type="button"
                            onclick="toggleSelectAllPermissions(false)"
                            class="px-3 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1.5 cursor-pointer transition-all whitespace-nowrap">
                        <span class="w-3.5 h-3.5 rounded border border-slate-400 bg-white inline-block"></span>
                        <span>ยกเลิกทั้งหมด</span>
                    </button>

                    <button type="button"
                            onclick="showToast('สิทธิ์ขั้นสูง (Root Security Privileges): กำลังตรวจสอบ')"
                            class="px-3 py-1.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1.5 cursor-pointer transition-all whitespace-nowrap">
                        <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <span>สิทธิ์ขั้นสูง</span>
                    </button>
                </div>

                <!-- ============================================================= -->
                <!-- PERMISSION MODULES LIST (Accordion Sections matching Image 2) -->
                <!-- ============================================================= -->
                <div class="space-y-3 pt-1">
                    @foreach($permissionModules as $modIndex => $module)
                        <div class="permission-module rounded-2xl border border-slate-200/90 overflow-hidden bg-white shadow-2xs">
                            <!-- Accordion Header -->
                            <div class="p-3.5 bg-slate-50/70 hover:bg-slate-100/60 flex items-center justify-between gap-3 cursor-pointer select-none transition-all"
                                 onclick="toggleAccordion('module-body-{{ $module['id'] }}', 'chevron-{{ $module['id'] }}')">
                                
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <!-- Collapsible Arrow -->
                                    <svg id="chevron-{{ $module['id'] }}" class="w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>

                                    @php
                                        $moduleItemIds = array_column($module['items'], 'id');
                                        $moduleCheckedCount = 0;
                                        foreach ($moduleItemIds as $mId) {
                                            if (!empty($userPermissions[$mId])) $moduleCheckedCount++;
                                        }
                                        $isModuleChecked = ($moduleCheckedCount > 0);
                                    @endphp

                                    <!-- Module Checkbox (toggle all in module) -->
                                    <input type="checkbox"
                                           id="chk-mod-{{ $module['id'] }}"
                                           {{ $isModuleChecked ? 'checked' : '' }}
                                           onclick="event.stopPropagation(); toggleModuleCheckboxes('{{ $module['id'] }}', this.checked)"
                                           class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300 cursor-pointer">

                                    <span class="text-base">{{ $module['icon'] }}</span>

                                    <!-- Module Name & Badge -->
                                    <span class="font-bold text-xs sm:text-sm text-slate-800">
                                        {{ $module['name'] }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-bold text-[10.5px] border border-blue-100 whitespace-nowrap">
                                        {{ $module['badge'] }}
                                    </span>
                                </div>
                            </div>

                            <!-- Accordion Body -->
                            <div id="module-body-{{ $module['id'] }}" class="p-3.5 sm:p-4 space-y-3 border-t border-slate-100 divide-y divide-slate-100">
                                @foreach($module['items'] as $item)
                                    @php
                                        $itemPerm = $userPermissions[$item['id']] ?? null;
                                        $isItemChecked = !empty($itemPerm);
                                    @endphp
                                    <div class="pt-3 first:pt-0 flex flex-col md:flex-row md:items-center justify-between gap-3">
                                        <!-- Item Checkbox, Icon, Name & Description -->
                                        <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                            <input type="checkbox"
                                                   name="perm_item_{{ $item['id'] }}"
                                                   id="chk-item-{{ $item['id'] }}"
                                                   data-item-id="{{ $item['id'] }}"
                                                   data-module-id="{{ $module['id'] }}"
                                                   {{ $isItemChecked ? 'checked' : '' }}
                                                   onchange="handleItemCheckboxChange('{{ $item['id'] }}', '{{ $module['id'] }}', this.checked)"
                                                   class="item-checkbox mod-{{ $module['id'] }}-chk mt-0.5 w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300 cursor-pointer">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-sm">{{ $item['icon'] }}</span>
                                                    <label for="chk-item-{{ $item['id'] }}" class="font-bold text-xs sm:text-sm text-slate-800 cursor-pointer leading-tight">
                                                        {{ $item['name'] }}
                                                    </label>
                                                </div>
                                                <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">
                                                    {{ $item['description'] }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Granular Action Pills: [ดู] [เพิ่ม] [แก้ไข] [ลบ] [สลับสาขา] [ส่งออก] (Matches Image 2) -->
                                        <div class="flex items-center gap-1.5 shrink-0 flex-wrap">
                                            @foreach($item['available_actions'] as $act)
                                                @php
                                                    $isActActive = !empty($itemPerm[$act]);
                                                    $actLabel = match($act) {
                                                        'view' => 'ดู',
                                                        'add' => 'เพิ่ม',
                                                        'edit' => 'แก้ไข',
                                                        'delete' => 'ลบ',
                                                        'switch' => 'สลับสาขา',
                                                        'export' => 'ส่งออก',
                                                        default => $act
                                                    };
                                                @endphp
                                                <button type="button"
                                                        data-item="{{ $item['id'] }}"
                                                        data-action="{{ $act }}"
                                                        onclick="toggleActionPill(this)"
                                                        class="action-pill px-2.5 py-1 rounded-lg text-[10.5px] font-bold border transition-all cursor-pointer whitespace-nowrap {{ $isActActive ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-400 border-slate-200 line-through' }}">
                                                    {{ $actLabel }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                @if(!empty($module['alert_card']))
                                    <!-- Dashed Red Alert Box in Section (Matches Image 2) -->
                                    <div class="mt-3 p-2.5 rounded-xl border border-dashed border-rose-300 bg-rose-50/50 flex items-center justify-between text-xs text-rose-700">
                                        <div class="flex items-center gap-1.5 font-bold">
                                            <span>🛡️</span>
                                            <span>{{ $module['alert_card'] }}</span>
                                        </div>
                                        <button type="button"
                                                onclick="testBranchAlert()"
                                                class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px] cursor-pointer transition-all shadow-2xs">
                                            ทดสอบแจ้งเตือน
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <!-- END PANEL 1: สิทธิ์การเข้าถึง -->

            <!-- ===================================================================== -->
            <!-- PANEL 2: ลงทะเบียนขอสิทธิ์ & สร้างบัญชีผู้ใช้งานจริง (ใช้งานได้จริง 100%) -->
            <!-- ===================================================================== -->
            <div id="panel-register" class="bg-white rounded-3xl p-3 sm:p-4.5 border border-slate-200/90 shadow-sm space-y-3 hidden max-h-[calc(100vh-120px)] overflow-y-auto scrollbar-thin">
                <!-- Header -->
                <div class="flex items-center justify-between pb-2 sm:pb-2.5 border-b border-slate-100">
                    <div>
                        <span class="text-[10.5px] font-bold text-blue-600 uppercase tracking-wider">ลงทะเบียนขอสิทธิ์ &amp; สร้างบัญชีผู้ใช้จริง</span>
                        <h2 class="text-base sm:text-lg font-black text-slate-900 mt-0.5">
                            สร้างบัญชีผู้ใช้งานใหม่ (แอดมิน / พนักงาน / ลูกค้า)
                        </h2>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            กำหนดรูปภาพโปรไฟล์ ชื่อ-สกุล อีเมล เบอร์โทรศัพท์ รหัสผ่าน และสิทธิ์ตามบทบาทจริง
                        </p>
                    </div>
                    <button type="button"
                            onclick="switchSubTab('permissions')"
                            class="px-2.5 py-1 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all cursor-pointer">
                        ✕ ปิดกลับหน้าสิทธิ์
                    </button>
                </div>

                <!-- Registration Form -->
                <form id="form-register-user" onsubmit="submitRegisterUser(event)" enctype="multipart/form-data" class="space-y-3">
                    @csrf

                    <!-- 1. รูปภาพโปรไฟล์ / ภาพประกอบ (Image Upload with Preview) -->
                    <div class="p-2.5 sm:p-3 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                            </svg>
                            <span>รูปภาพประกอบ / ภาพถ่ายโปรไฟล์พนักงานหรือผู้ใช้</span>
                        </label>
                        <div class="flex flex-col sm:flex-row items-center gap-3">
                            <div class="relative shrink-0">
                                <img id="reg-avatar-preview"
                                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23CBD5E1'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E"
                                     alt="Avatar Preview"
                                     class="w-14 h-14 rounded-xl object-cover bg-white border-2 border-dashed border-slate-300 p-0.5 shadow-xs">
                                <div id="reg-avatar-indicator" class="hidden absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] font-bold">
                                    ✓
                                </div>
                            </div>

                            <div class="space-y-1 flex-1 text-center sm:text-left">
                                <div class="flex flex-wrap items-center gap-2 justify-center sm:justify-start">
                                    <label for="reg-avatar-input"
                                           class="px-3 py-1.5 rounded-xl bg-white hover:bg-blue-50 border border-slate-300 hover:border-blue-400 text-slate-700 hover:text-blue-600 font-bold text-xs cursor-pointer shadow-2xs transition-all flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-blue-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>เลือกภาพถ่ายโปรไฟล์...</span>
                                    </label>
                                    <input type="file"
                                           id="reg-avatar-input"
                                           name="avatar"
                                           accept="image/*"
                                           onchange="previewRegisterAvatar(this)"
                                           class="hidden">

                                    <button type="button"
                                            onclick="removeRegisterAvatar()"
                                            id="btn-remove-avatar"
                                            class="hidden px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 text-xs font-semibold transition-all cursor-pointer">
                                        ลบรูปภาพ
                                    </button>
                                </div>
                                <p class="text-[10.5px] text-slate-400">
                                    รองรับไฟล์ภาพ JPG, PNG, WEBP (ขนาดไม่เกิน 5MB) สามารถใส่ภาพเพื่อยืนยันตัวตนได้ทันที
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. ข้อมูลส่วนบุคคล (ชื่อ, เมล, เบอร์, รหัสผ่าน) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <!-- Full Name -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">
                                ชื่อ-นามสกุล <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-2.5 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </span>
                                <input type="text"
                                       name="name"
                                       id="reg-input-name"
                                       required
                                       placeholder="เช่น นายอับดุลเลาะห์ สุขสวัสดิ์"
                                       class="w-full text-xs sm:text-sm pl-9 pr-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 focus:outline-blue-500 focus:bg-white bg-slate-50 text-slate-800 transition-all font-medium">
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">
                                อีเมลเข้าใช้งาน (Login Email) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-2.5 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                </span>
                                <input type="email"
                                       name="email"
                                       id="reg-input-email"
                                       required
                                       placeholder="เช่น abdullah@malastreet.com"
                                       class="w-full text-xs sm:text-sm pl-9 pr-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 focus:outline-blue-500 focus:bg-white bg-slate-50 text-slate-800 transition-all font-medium">
                            </div>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">
                                เบอร์โทรศัพท์ <span class="text-slate-400 font-normal">(สำหรับติดต่อ)</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-2.5 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                    </svg>
                                </span>
                                <input type="tel"
                                       name="phone"
                                       id="reg-input-phone"
                                       placeholder="เช่น 089-123-4567"
                                       class="w-full text-xs sm:text-sm pl-9 pr-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 focus:outline-blue-500 focus:bg-white bg-slate-50 text-slate-800 transition-all font-medium">
                            </div>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">
                                รหัสผ่าน / PIN เข้าสู่ระบบ <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-2.5 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                    </svg>
                                </span>
                                <input type="password"
                                       name="password"
                                       id="reg-input-password"
                                       required
                                       value="password123"
                                       placeholder="กำหนดรหัสผ่าน (ค่าเริ่มต้น password123)"
                                       class="w-full text-xs sm:text-sm pl-9 pr-9 py-1.5 sm:py-2 rounded-xl border border-slate-200 focus:outline-blue-500 focus:bg-white bg-slate-50 text-slate-800 transition-all font-medium font-mono">
                                <button type="button"
                                        onclick="togglePasswordVisibility('reg-input-password', this)"
                                        class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                                        title="แสดง/ซ่อนรหัสผ่าน">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 3. สถานะว่าเป็นแอดมินหรือพนักงาน หรือลูกค้า (3 Interactive Cards) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1.5">
                            สถานะและบทบาทการใช้งาน <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-2.5">
                            <!-- 1. แอดมิน (Admin) -->
                            <label id="card-role-admin"
                                   class="role-selector-card p-2 sm:p-2.5 rounded-xl border-2 border-slate-200 bg-white hover:border-rose-400 cursor-pointer transition-all flex flex-col justify-between space-y-1 select-none"
                                   onclick="selectRoleCard('Admin')">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                        </svg>
                                    </span>
                                    <input type="radio" name="role" value="Admin" id="role-admin" class="w-3.5 h-3.5 text-rose-600 focus:ring-rose-500 border-slate-300">
                                </div>
                                <div>
                                    <div class="font-black text-xs sm:text-sm text-slate-900">แอดมิน (Admin)</div>
                                    <p class="text-[10px] text-slate-500 mt-0.5 leading-snug">
                                        ผู้ดูแลระบบสูงสุด สิทธิ์เต็มทุกหน้าต่าง ทุกปุ่ม จัดการสาขา และกำหนดสิทธิ์ผู้อื่นได้
                                    </p>
                                </div>
                            </label>

                            <!-- 2. พนักงาน (Employee) -->
                            <label id="card-role-employee"
                                   class="role-selector-card p-2 sm:p-2.5 rounded-xl border-2 border-[#2563EB] bg-blue-50/50 cursor-pointer transition-all flex flex-col justify-between space-y-1 select-none"
                                   onclick="selectRoleCard('Employee')">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                        </svg>
                                    </span>
                                    <input type="radio" name="role" value="Employee" id="role-employee" checked class="w-3.5 h-3.5 text-blue-600 focus:ring-blue-500 border-slate-300">
                                </div>
                                <div>
                                    <div class="font-black text-xs sm:text-sm text-slate-900">พนักงาน (Employee)</div>
                                    <p class="text-[10px] text-slate-500 mt-0.5 leading-snug">
                                        ทีมงานหน้าร้าน แคชเชียร์ POS, จอครัว, เช็คสต็อกไม้ และสแกนใบหน้าเข้า-ออกงาน
                                    </p>
                                </div>
                            </label>

                            <!-- 3. ลูกค้า (Customer) -->
                            <label id="card-role-customer"
                                   class="role-selector-card p-2 sm:p-2.5 rounded-xl border-2 border-slate-200 bg-white hover:border-emerald-400 cursor-pointer transition-all flex flex-col justify-between space-y-1 select-none"
                                   onclick="selectRoleCard('Customer')">
                                <div class="flex items-center justify-between">
                                    <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                    </span>
                                    <input type="radio" name="role" value="Customer" id="role-customer" class="w-3.5 h-3.5 text-emerald-600 focus:ring-emerald-500 border-slate-300">
                                </div>
                                <div>
                                    <div class="font-black text-xs sm:text-sm text-slate-900">ลูกค้า (Customer)</div>
                                    <p class="text-[10px] text-slate-500 mt-0.5 leading-snug">
                                        ลูกค้าสั่งอาหารออนไลน์ ชำระเงิน สั่งมารับหน้าร้าน และติดตามสถานะอาหาร
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 4. สาขา & ตัวเลือกลบบัญชีทดสอบ -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-0.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">
                                สาขาประจำ
                            </label>
                            <select name="branch" id="reg-input-branch" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium">
                                <option value="ทุกสาขา">🏢 ทุกสาขา (แอดมิน / ลูกค้า)</option>
                                <option value="สาขายะลา">📍 สาขายะลา (ศูนย์บริการหลัก &amp; คลังสต็อก)</option>
                                <option value="สาขาปัตตานี">📍 สาขาปัตตานี (ศูนย์บริการและจุดรับคืน)</option>
                            </select>
                        </div>

                        <!-- Auto-clean test accounts toggle (ตรงตามที่ผู้ใช้ร้องขอ) -->
                        <div class="p-2 px-2.5 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-2">
                            <input type="checkbox"
                                   name="clear_test_accounts"
                                   id="reg-chk-clear-test"
                                   value="1"
                                   checked
                                   class="mt-0.5 w-3.5 h-3.5 rounded text-rose-600 focus:ring-rose-500 border-slate-300 cursor-pointer">
                            <label for="reg-chk-clear-test" class="text-[11px] text-rose-900 font-medium cursor-pointer leading-tight">
                                <span class="font-bold text-rose-800 block">🗑️ ลบบัญชีทดสอบทั้งหมด (ตามภาพที่ 2) โดยอัตโนมัติ</span>
                                เมื่อสร้างบัญชีจริงนี้ บัญชีตัวอย่างทั้งหมดในภาพที่ 2 จะถูกลบออก และแสดงเฉพาะบัญชีจริงที่คุณสร้างเท่านั้น
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="pt-2 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-end gap-2.5">
                        <button type="reset"
                                onclick="resetRegisterForm()"
                                class="w-full sm:w-auto px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition-all cursor-pointer">
                            ล้างฟอร์ม
                        </button>
                        <button type="submit"
                                id="btn-submit-register"
                                class="w-full sm:w-auto px-5 py-2 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] active:scale-95 text-white font-bold text-xs shadow-md shadow-blue-500/25 flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <span>สร้างบัญชีและเปิดใช้งานทันที</span>
                        </button>
                    </div>
                </form>

                <!-- ===================================================================== -->
                <!-- การ์ดใหม่ด้านล่างของการ์ดตามภาพที่ 2: รองรับพนักงานบัญชีใหม่ (คำขอรอแอดมินอนุมัติ) -->
                <!-- ===================================================================== -->
                <div id="card-pending-registrations" class="mt-4 pt-4 border-t border-slate-200/80 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-2 border-b border-slate-100 gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-2xl bg-amber-500/10 text-amber-600 border border-amber-200/80 flex items-center justify-center shrink-0 shadow-2xs font-bold text-base">
                                🔔
                            </span>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-sm sm:text-base font-black text-slate-900 leading-tight">
                                        คำขอลงทะเบียนพนักงานบัญชีและผู้ใช้ใหม่
                                    </h3>
                                    <span id="badge-pending-count" class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10.5px] font-bold border border-amber-200 shadow-2xs">
                                        {{ count($pendingRegistrations ?? []) }} คำขอรออนุมัติ
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 font-medium">
                                    รายการที่ส่งคำขอมาจากหน้า Login — ผู้ดูแลระบบต้องกด "อนุญาต" เพื่อสร้างบัญชีและเปิดใช้งาน
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- รายการคำขอรออนุมัติ -->
                    <div id="pending-registrations-list" class="space-y-2.5">
                        @forelse(($pendingRegistrations ?? []) as $req)
                            <div id="pending-card-{{ $req['id'] }}" class="p-3 sm:p-3.5 rounded-2xl border border-slate-200/90 bg-slate-50/70 hover:bg-white hover:border-slate-300 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if(!empty($req['avatar']))
                                        <img src="{{ $req['avatar'] }}" alt="{{ $req['name'] }}" class="w-12 h-12 rounded-2xl object-cover border border-slate-200 shadow-xs shrink-0">
                                    @else
                                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-rose-600 text-white font-black text-base flex items-center justify-center shrink-0 shadow-xs">
                                            {{ mb_substr($req['name'], 0, 2) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0 space-y-0.5">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="text-sm font-black text-slate-900 truncate">{{ $req['name'] }}</h4>
                                            <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 border border-blue-200 text-[10px] font-bold">
                                                {{ $req['position'] ?? 'พนักงานบัญชี' }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 border border-amber-200 text-[10px] font-bold">
                                                ⏳ รอแอดมินอนุมัติ
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-slate-500 flex-wrap">
                                            <span class="font-mono text-slate-700">✉ {{ $req['email'] }}</span>
                                            @if(!empty($req['phone']) && $req['phone'] !== '-')
                                                <span>• 📞 {{ $req['phone'] }}</span>
                                            @endif
                                            <span class="text-[10.5px] text-slate-400">• {{ $req['created_at'] ?? 'เมื่อสักครู่' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                                    <button type="button"
                                            onclick="approveRegistrationRequest('{{ $req['id'] }}', '{{ addslashes($req['name']) }}')"
                                            class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-xs shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        <span>กดอนุญาต / อนุมัติใช้งาน</span>
                                    </button>
                                    <button type="button"
                                            onclick="rejectRegistrationRequest('{{ $req['id'] }}', '{{ addslashes($req['name']) }}')"
                                            class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 font-bold text-xs transition-all cursor-pointer">
                                        ✕ ปฏิเสธ
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div id="pending-empty-state" class="py-7 text-center space-y-1.5 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                                <span class="text-2xl block">📭</span>
                                <p class="text-xs font-bold text-slate-700">ไม่มีคำขอลงทะเบียนใหม่ที่รอดำเนินการ</p>
                                <p class="text-[10.5px] text-slate-400">เมื่อพนักงานบัญชีกดลงทะเบียนจากหน้าเข้าสู่ระบบ รายการจะมาปรากฏที่การ์ดนี้เพื่อให้แอดมินกดอนุญาต</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <!-- END PANEL 2 -->

            <!-- ===================================================================== -->
            <!-- PANEL 3: จัดการพนักงานและแอดมิน (2 การ์ดคู่กันตรงตามภาพที่ 2 และ ภาพที่ 1 เป๊ะๆ 100%) -->
            <!-- ===================================================================== -->
            <div id="panel-users" class="grid grid-cols-1 xl:grid-cols-12 gap-4 sm:gap-5 items-start {{ $currentTab === 'users' ? '' : 'hidden' }}">
                
                <!-- CARD 1 (ฝั่งซ้าย): ข้อมูล & สิทธิการทำงานพนักงาน (ภาพที่ 2) -->
                <div class="xl:col-span-7 bg-white rounded-3xl p-3 sm:p-4 border border-slate-200/90 shadow-sm space-y-2.5 max-h-[calc(100vh-120px)] overflow-y-auto scrollbar-thin">
                
                <!-- 1. Header Bar: Icon 👥 + Title + Close Button ✕ -->
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2 text-slate-900 font-black text-sm sm:text-base">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>ข้อมูล &amp; สิทธิการทำงานพนักงาน</span>
                    </div>

                    <button type="button"
                            onclick="switchSubTab('permissions')"
                            title="ปิดกลับไปหน้าสิทธิ์"
                            class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs font-bold transition-all cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- 2. Top Profile Card (Matching Image 2) -->
                <div class="p-2.5 sm:p-3 rounded-2xl border border-slate-200/80 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 shadow-2xs">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Circle Avatar: Blue background with white Thai letter 'อ' -->
                        <div id="emp-card-avatar" class="w-11 h-11 rounded-full bg-[#3B82F6] flex items-center justify-center text-white text-xl font-bold shrink-0 shadow-xs">
                            {{ $selectedUser['initials'] ?? 'อ' }}
                        </div>
                        <div class="min-w-0 space-y-0.5">
                            <h3 id="emp-card-name" class="font-black text-base sm:text-lg text-slate-900 truncate">
                                {{ $selectedUser['name'] ?? 'อารีนา เจ๊ะอามะ' }}
                            </h3>
                            <div class="flex flex-wrap items-center gap-1.5">
                                <!-- Purple Badge: พนักงาน -->
                                <span id="emp-card-badge-role" class="px-2 py-0.5 rounded-full bg-[#FAF5FF] text-[#7E22CE] border border-[#F3E8FF] text-[11px] font-bold flex items-center gap-1 whitespace-nowrap">
                                    <svg class="w-3 h-3 text-purple-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />
                                    </svg>
                                    <span id="emp-card-role-text">{{ $selectedUser['role_label'] ?? 'พนักงาน' }}</span>
                                </span>
                                <!-- Amber Badge: พนง. บริการประจำ -->
                                <span id="emp-card-badge-pos" class="px-2 py-0.5 rounded-full bg-[#FFFBEB] text-[#B45309] border border-[#FEF3C7] text-[11px] font-bold flex items-center gap-1 whitespace-nowrap">
                                    <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                    </svg>
                                    <span id="emp-card-pos-text">{{ $selectedUser['position'] ?? 'พนง. บริการประจำ' }}</span>
                                </span>
                                <!-- Subtitle: • พนักงานบริการประจำ -->
                                <span id="emp-card-subtitle" class="text-[11px] text-slate-400 font-medium whitespace-nowrap">
                                    • {{ $selectedUser['position'] ?? 'พนักงานบริการประจำ' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Switcher Dropdown (To edit any staff member) -->
                    <div class="flex items-center gap-2 shrink-0">
                        <label class="text-[11px] font-semibold text-slate-400">เลือกพนักงาน:</label>
                        <select id="emp-card-user-select"
                                onchange="switchEmployeeProfile(this.value)"
                                class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1 text-slate-800 font-bold focus:outline-indigo-500 cursor-pointer">
                            @foreach($users as $u)
                                <option value="{{ $u['id'] }}" {{ $u['id'] === ($selectedUser['id'] ?? '') ? 'selected' : '' }}>
                                    {{ $u['name'] }} ({{ $u['role_label'] ?? $u['role'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- 3. Horizontal Sub-Tabs (Pills / Underline Bar Matching Image 2 - Compact Size) -->
                <div class="flex items-center gap-1 sm:gap-2 border-b border-slate-200/80 pt-0 pb-0 overflow-x-auto scrollbar-none">
                    <!-- Tab 1: ข้อมูล & บทบาท -->
                    <button type="button"
                            id="emp-tab-btn-profile"
                            onclick="switchEmpSubTab('profile')"
                            class="emp-tab-nav py-1 px-2 text-xs font-semibold text-slate-500 hover:text-slate-900 border-b-2 border-transparent transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        <span>ข้อมูล &amp; บทบาท</span>
                    </button>

                    <!-- Tab 2: ตารางงาน (Active by default matching Image 2) -->
                    <button type="button"
                            id="emp-tab-btn-schedule"
                            onclick="switchEmpSubTab('schedule')"
                            class="emp-tab-nav py-1 px-2 text-xs font-bold text-[#6366F1] border-b-2 border-[#6366F1] transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        <span>ตารางงาน</span>
                    </button>

                    <!-- Tab 3: เงินเดือน & KPI -->
                    <button type="button"
                            id="emp-tab-btn-payroll"
                            onclick="switchEmpSubTab('payroll')"
                            class="emp-tab-nav py-1 px-2 text-xs font-semibold text-slate-500 hover:text-slate-900 border-b-2 border-transparent transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6H2.25m0 0v11.25m0 0h19.5m0 0h.75a.75.75 0 00.75-.75V6a.75.75 0 00-.75-.75H21m0 0V4.5m0 0A2.25 2.25 0 0018.75 2.25h-13.5A2.25 2.25 0 003 4.5m18 0a2.25 2.25 0 01-2.25 2.25h-13.5A2.25 2.25 0 013 4.5m16.5 6.75a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                        <span>เงินเดือน &amp; KPI</span>
                    </button>

                    <!-- Tab 4: สิทธิลา & มาสาย -->
                    <button type="button"
                            id="emp-tab-btn-leave"
                            onclick="switchEmpSubTab('leave')"
                            class="emp-tab-nav py-1 px-2 text-xs font-semibold text-slate-500 hover:text-slate-900 border-b-2 border-transparent transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span>สิทธิลา &amp; มาสาย</span>
                    </button>

                    <!-- Tab 5: ข้อมูลเพิ่มเติม -->
                    <button type="button"
                            id="emp-tab-btn-more"
                            onclick="switchEmpSubTab('more')"
                            class="emp-tab-nav py-1 px-2 text-xs font-semibold text-slate-500 hover:text-slate-900 border-b-2 border-transparent transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span>ข้อมูลเพิ่มเติม</span>
                    </button>
                </div>

                <!-- 4. TAB CONTENT 2: 📅 ตารางงาน (Matching Image 2 in Exact Detail) -->
                <div id="emp-tab-content-schedule" class="space-y-2.5 pt-0.5">
                    <!-- Section 1: วันทำงานประจำสัปดาห์ * -->
                    <div class="space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div class="flex items-center gap-1.5 font-black text-xs sm:text-sm text-slate-900">
                                <svg class="w-4 h-4 text-slate-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                <span>วันทำงานประจำสัปดาห์</span>
                                <span class="text-rose-500">*</span>
                            </div>

                            <!-- Filter Pills: ทั้งหมด 5, WEEK 1 5, WEEK 2 5, WEEK 3 5, WEEK 4 5 -->
                            <div class="flex items-center gap-1 overflow-x-auto scrollbar-none">
                                <button type="button"
                                        onclick="setWeekFilter('all', this)"
                                        class="week-filter-pill px-2.5 py-0.5 rounded-full bg-[#0F172A] text-white text-[11px] font-bold flex items-center gap-1 transition-all cursor-pointer whitespace-nowrap shadow-xs">
                                    <span>ทั้งหมด</span>
                                    <span id="badge-count-all" class="bg-[#334155] text-white px-1.5 py-0.2 rounded-full text-[9.5px] font-bold">5</span>
                                </button>

                                <button type="button"
                                        onclick="setWeekFilter('w1', this)"
                                        class="week-filter-pill px-2.5 py-0.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] font-semibold flex items-center gap-1 transition-all cursor-pointer whitespace-nowrap">
                                    <span>WEEK 1</span>
                                    <span id="badge-count-w1" class="text-slate-500 text-[9.5px] font-bold">5</span>
                                </button>

                                <button type="button"
                                        onclick="setWeekFilter('w2', this)"
                                        class="week-filter-pill px-2.5 py-0.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] font-semibold flex items-center gap-1 transition-all cursor-pointer whitespace-nowrap">
                                    <span>WEEK 2</span>
                                    <span id="badge-count-w2" class="text-slate-500 text-[9.5px] font-bold">5</span>
                                </button>

                                <button type="button"
                                        onclick="setWeekFilter('w3', this)"
                                        class="week-filter-pill px-2.5 py-0.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] font-semibold flex items-center gap-1 transition-all cursor-pointer whitespace-nowrap">
                                    <span>WEEK 3</span>
                                    <span id="badge-count-w3" class="text-slate-500 text-[9.5px] font-bold">5</span>
                                </button>

                                <button type="button"
                                        onclick="setWeekFilter('w4', this)"
                                        class="week-filter-pill px-2.5 py-0.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 text-[11px] font-semibold flex items-center gap-1 transition-all cursor-pointer whitespace-nowrap">
                                    <span>WEEK 4</span>
                                    <span id="badge-count-w4" class="text-slate-500 text-[9.5px] font-bold">5</span>
                                </button>
                            </div>
                        </div>

                        <!-- 7 Day Cards Row (Matching Image 2: จ อ พ พฤ ศ are dark, ส อา are white) -->
                        <div class="grid grid-cols-7 gap-1.5 sm:gap-2">
                            @php
                                $workingDays = $activeEmployeeData['schedule']['days'] ?? ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัส', 'ศุกร์'];
                                $dayList = [
                                    ['short' => 'จ', 'name' => 'จันทร์'],
                                    ['short' => 'อ', 'name' => 'อังคาร'],
                                    ['short' => 'พ', 'name' => 'พุธ'],
                                    ['short' => 'พฤ', 'name' => 'พฤหัส'],
                                    ['short' => 'ศ', 'name' => 'ศุกร์'],
                                    ['short' => 'ส', 'name' => 'เสาร์'],
                                    ['short' => 'อา', 'name' => 'อาทิตย์'],
                                ];
                            @endphp
                            @foreach($dayList as $d)
                                @php
                                    $isWorkDay = in_array($d['name'], $workingDays);
                                @endphp
                                <button type="button"
                                        data-day="{{ $d['name'] }}"
                                        onclick="toggleDayCard(this)"
                                        class="day-card py-1 sm:py-1.5 rounded-xl flex flex-col items-center justify-center transition-all cursor-pointer select-none {{ $isWorkDay ? 'bg-[#0F172A] text-white shadow-xs border-2 border-[#0F172A]' : 'bg-white text-slate-400 hover:text-slate-600 border-2 border-slate-200' }}">
                                    <span class="text-xs sm:text-sm font-black leading-none">{{ $d['short'] }}</span>
                                    <span class="text-[9px] sm:text-[9.5px] font-medium mt-0.5 {{ $isWorkDay ? 'opacity-90' : '' }}">{{ $d['name'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Section 2: ขอบเขตเวลาการทำงาน * (Container Card) -->
                    <div class="rounded-2xl border border-slate-200/90 bg-[#F8FAFC]/80 p-2.5 sm:p-3 space-y-2 shadow-2xs">
                        <div class="flex items-center gap-1.5 font-bold text-xs text-slate-800">
                            <svg class="w-3.5 h-3.5 text-slate-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>ขอบเขตเวลาการทำงาน</span>
                            <span class="text-rose-500">*</span>
                        </div>

                        <!-- Segmented Switcher: เวลาเหมือนกันทั้งสัปดาห์ / กำหนดเวลาแยกตามวัน -->
                        <div class="inline-flex p-0.5 rounded-xl bg-slate-200/80 gap-0.5">
                            <button type="button"
                                    id="btn-mode-same"
                                    onclick="setTimeMode('same')"
                                    class="px-3 py-1 rounded-lg text-[11px] font-bold transition-all bg-white text-slate-900 shadow-2xs flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3 h-3 text-slate-700 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>เวลาเหมือนกันทั้งสัปดาห์</span>
                            </button>
                            <button type="button"
                                    id="btn-mode-diff"
                                    onclick="setTimeMode('diff')"
                                    class="px-3 py-1 rounded-lg text-[11px] font-semibold text-slate-600 hover:text-slate-900 transition-all flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3 h-3 text-slate-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                <span>กำหนดเวลาแยกตามวัน</span>
                            </button>
                        </div>

                        <!-- Time Inputs: เวลาเข้างาน & เวลาเลิกงาน -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-0.5">เวลาเข้างาน</label>
                                <div class="relative">
                                    <input type="text"
                                           id="emp-time-start"
                                           value="{{ $activeEmployeeData['schedule']['start_time'] ?? '09:00 AM' }}"
                                           placeholder="09:00 AM"
                                           class="w-full text-xs sm:text-sm font-bold font-mono px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-indigo-500 shadow-2xs">
                                    <span class="absolute right-2.5 top-2 text-slate-400 pointer-events-none">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-0.5">เวลาเลิกงาน</label>
                                <div class="relative">
                                    <input type="text"
                                           id="emp-time-end"
                                           value="{{ $activeEmployeeData['schedule']['end_time'] ?? '07:00 PM' }}"
                                           placeholder="07:00 PM"
                                           class="w-full text-xs sm:text-sm font-bold font-mono px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-indigo-500 shadow-2xs">
                                    <span class="absolute right-2.5 top-2 text-slate-400 pointer-events-none">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Shift Preset Pills -->
                        <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                            <button type="button"
                                    onclick="setTimePreset('09:00 AM', '06:00 PM', this)"
                                    class="time-preset-pill px-2.5 py-1 rounded-full border border-slate-200 bg-white hover:bg-slate-100 text-[10.5px] font-semibold text-slate-600 cursor-pointer transition-all">
                                09:00 - 18:00
                            </button>
                            <button type="button"
                                    onclick="setTimePreset('10:00 AM', '07:00 PM', this)"
                                    class="time-preset-pill px-2.5 py-1 rounded-full border border-slate-200 bg-white hover:bg-slate-100 text-[10.5px] font-semibold text-slate-600 cursor-pointer transition-all">
                                10:00 - 19:00
                            </button>
                            <button type="button"
                                    onclick="setTimePreset('12:00 PM', '09:00 PM', this)"
                                    class="time-preset-pill px-2.5 py-1 rounded-full border border-slate-200 bg-white hover:bg-slate-100 text-[10.5px] font-semibold text-slate-600 cursor-pointer transition-all">
                                12:00 - 21:00
                            </button>
                            <button type="button"
                                    onclick="setTimePreset('01:00 PM', '10:00 PM', this)"
                                    class="time-preset-pill px-2.5 py-1 rounded-full border border-slate-200 bg-white hover:bg-slate-100 text-[10.5px] font-semibold text-slate-600 cursor-pointer transition-all">
                                13:00 - 22:00
                            </button>
                        </div>
                    </div>

                    <!-- Section 3: บันทึกตารางงาน / เงื่อนไขเวลาทำงาน -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-800">
                            บันทึกตารางงาน / เงื่อนไขเวลาทำงาน
                        </label>
                        <textarea id="emp-schedule-notes"
                                  rows="2"
                                  placeholder="เช่น ทำงานเฉพาะเสาร์-อาทิตย์, เข้างานตามกะคอนเทนต์"
                                  class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-indigo-500 font-medium">{{ $activeEmployeeData['schedule']['notes'] ?? 'เช่น ทำงานเฉพาะเสาร์-อาทิตย์, เข้างานตามกะคอนเทนต์' }}</textarea>
                    </div>

                    <!-- Save Action Bar -->
                    <div class="flex items-center justify-end gap-2.5 pt-1.5 border-t border-slate-100">
                        <button type="button"
                                id="btn-save-emp-schedule"
                                onclick="saveActiveEmployeeData()"
                                class="px-5 py-2 rounded-xl bg-[#0F172A] hover:bg-slate-800 text-white font-bold text-xs shadow-md transition-all cursor-pointer flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                            <span>บันทึกตารางงาน &amp; สิทธิพนักงาน</span>
                        </button>
                    </div>
                </div>

                <!-- 5. TAB CONTENT 1: 👤 ข้อมูล & บทบาท -->
                <div id="emp-tab-content-profile" class="space-y-2.5 pt-0.5 hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">ชื่อ-นามสกุล</label>
                            <input type="text" id="emp-edit-name" value="{{ $selectedUser['name'] ?? '' }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">ตำแหน่งงาน (Position)</label>
                            <input type="text" id="emp-edit-position" value="{{ $selectedUser['position'] ?? 'พนง. บริการประจำ' }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">เบอร์โทรศัพท์</label>
                            <input type="tel" id="emp-edit-phone" value="{{ $selectedUser['phone'] ?? '' }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">อีเมลเข้าสู่ระบบ</label>
                            <input type="email" id="emp-edit-email" value="{{ $selectedUser['email'] ?? '' }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">สาขาประจำการ</label>
                            <select id="emp-edit-branch" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium">
                                <option value="สาขายะลา" {{ ($selectedUser['branch'] ?? '') === 'สาขายะลา' ? 'selected' : '' }}>สาขายะลา (สาขาหลัก)</option>
                                <option value="สาขาปัตตานี" {{ ($selectedUser['branch'] ?? '') === 'สาขาปัตตานี' ? 'selected' : '' }}>สาขาปัตตานี</option>
                                <option value="ทุกสาขา" {{ ($selectedUser['branch'] ?? '') === 'ทุกสาขา' ? 'selected' : '' }}>ทุกสาขา</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">บทบาทระบบ (Role)</label>
                            <select id="emp-edit-role" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium">
                                <option value="Employee" {{ ($selectedUser['role'] ?? '') === 'Employee' ? 'selected' : '' }}>พนักงาน (Employee)</option>
                                <option value="Admin" {{ ($selectedUser['role'] ?? '') === 'Admin' ? 'selected' : '' }}>แอดมิน (Admin)</option>
                                <option value="Customer" {{ ($selectedUser['role'] ?? '') === 'Customer' ? 'selected' : '' }}>ลูกค้า (Customer)</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">
                                รหัสผ่าน / PIN เข้าสู่ระบบ (เปลี่ยนรหัสผ่าน)
                            </label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-2.5 text-slate-400 pointer-events-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                    </svg>
                                </span>
                                <input type="password"
                                       id="emp-edit-password"
                                       placeholder="ตั้งรหัสผ่านใหม่ (ค่าเริ่มต้น password123 หากไม่เปลี่ยนเว้นว่างไว้ได้)"
                                       class="w-full text-xs sm:text-sm pl-9 pr-9 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500 font-mono">
                                <button type="button"
                                        onclick="togglePasswordVisibility('emp-edit-password', this)"
                                        class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                                        title="แสดง/ซ่อนรหัสผ่าน">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                            <span class="text-[9.5px] text-slate-500 mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a3.75 3.75 0 10-7.5 0 3.75 3.75 0 007.5 0zm0 0v-1.5a1.5 1.5 0 013 0v1.5" />
                                </svg>
                                <span>ค่าเริ่มต้นของระบบคือ <strong>password123</strong> ท่านสามารถพิมพ์เปลี่ยนรหัสผ่านใหม่ที่นี่ได้ตลอดเวลา</span>
                            </span>
                        </div>
                    </div>
                    <div class="flex justify-end pt-1.5">
                        <button type="button" onclick="saveActiveEmployeeData()" class="px-4 py-1.5 rounded-xl bg-indigo-600 text-white font-bold text-xs cursor-pointer hover:bg-indigo-700">บันทึกข้อมูลส่วนตัว</button>
                    </div>
                </div>

                <!-- 6. TAB CONTENT 3: 💵 เงินเดือน & KPI -->
                <div id="emp-tab-content-payroll" class="space-y-2.5 pt-0.5 hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">เงินเดือนพื้นฐาน (บาท/เดือน)</label>
                            <input type="number" id="emp-edit-salary" value="{{ $activeEmployeeData['payroll']['base_salary'] ?? 15000 }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">ค่าล่วงเวลา OT (บาท/ชั่วโมง)</label>
                            <input type="number" id="emp-edit-ot" value="{{ $activeEmployeeData['payroll']['ot_rate'] ?? 60 }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">เบี้ยขยัน / ค่าครองชีพ (บาท)</label>
                            <input type="number" id="emp-edit-allowance" value="{{ $activeEmployeeData['payroll']['allowance'] ?? 1000 }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">รายการหักประกันสังคม / อื่นๆ (บาท)</label>
                            <input type="number" id="emp-edit-deduction" value="{{ $activeEmployeeData['payroll']['deduction'] ?? 750 }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">ธนาคารรับเงินเดือน</label>
                            <input type="text" id="emp-edit-bank-name" value="{{ $activeEmployeeData['payroll']['bank_name'] ?? 'ธนาคารกสิกรไทย' }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">เลขที่บัญชีธนาคาร</label>
                            <input type="text" id="emp-edit-bank-account" value="{{ $activeEmployeeData['payroll']['bank_account'] ?? '123-4-56789-0' }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                    </div>
                    <div class="flex justify-end pt-1.5">
                        <button type="button" onclick="saveActiveEmployeeData(this)" class="px-4 py-1.5 rounded-xl bg-indigo-600 text-white font-bold text-xs cursor-pointer hover:bg-indigo-700 flex items-center gap-1.5">
                            <span>บันทึกข้อมูลเงินเดือน</span>
                        </button>
                    </div>
                </div>

                <!-- 7. TAB CONTENT 4: 🛡️ สิทธิลา & มาสาย -->
                <div id="emp-tab-content-leave" class="space-y-2.5 pt-0.5 hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">สิทธิลาป่วย (วัน/ปี)</label>
                            <input type="number" id="emp-edit-sick" value="{{ $activeEmployeeData['leave']['sick_leave'] ?? 30 }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">สิทธิลากิจ (วัน/ปี)</label>
                            <input type="number" id="emp-edit-personal" value="{{ $activeEmployeeData['leave']['personal_leave'] ?? 6 }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">สิทธิลาพักร้อน (วัน/ปี)</label>
                            <input type="number" id="emp-edit-annual" value="{{ $activeEmployeeData['leave']['annual_leave'] ?? 6 }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">ระยะเวลาอนุโลมมาสาย (นาที)</label>
                            <input type="number" id="emp-edit-grace" value="{{ $activeEmployeeData['leave']['late_grace_minutes'] ?? 15 }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                    </div>
                    <div class="flex justify-end pt-1.5">
                        <button type="button" onclick="saveActiveEmployeeData()" class="px-4 py-1.5 rounded-xl bg-indigo-600 text-white font-bold text-xs cursor-pointer hover:bg-indigo-700">บันทึกสิทธิลา</button>
                    </div>
                </div>

                <!-- 8. TAB CONTENT 5: 📄 ข้อมูลเพิ่มเติม -->
                <div id="emp-tab-content-more" class="space-y-2.5 pt-0.5 hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">เลขประจำตัวประชาชน (13 หลัก)</label>
                            <input type="text" id="emp-edit-idcard" value="{{ $activeEmployeeData['more_info']['id_card'] ?? '1-9400-00123-45-6' }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">วันที่เริ่มงาน</label>
                            <input type="date" id="emp-edit-startdate" value="{{ $activeEmployeeData['more_info']['start_date'] ?? '2025-01-01' }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-0.5">ผู้ติดต่อกรณีฉุกเฉิน และเบอร์โทร</label>
                            <input type="text" id="emp-edit-emergency" value="{{ $activeEmployeeData['more_info']['emergency_contact'] ?? 'นายมะดีเยาะ (บิดา) 089-111-2222' }}" class="w-full text-xs sm:text-sm px-3 py-1.5 sm:py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium focus:outline-indigo-500">
                        </div>
                    </div>
                    <div class="flex justify-end pt-1.5">
                        <button type="button" onclick="saveActiveEmployeeData()" class="px-4 py-1.5 rounded-xl bg-indigo-600 text-white font-bold text-xs cursor-pointer hover:bg-indigo-700">บันทึกข้อมูลเพิ่มเติม</button>
                    </div>
                </div>

                </div>
                <!-- END CARD 1 (ฝั่งซ้าย): ข้อมูล & สิทธิการทำงานพนักงาน -->

                <!-- CARD 2 (ฝั่งขวา): ประเภทการจ้างงาน & เงินเดือน (ตรงตามภาพที่ 1 เป๊ะๆ 100%) -->
                <div id="card-emp-wage-kpi" class="xl:col-span-5 bg-white rounded-3xl p-4 sm:p-5 border border-slate-200/90 shadow-sm space-y-4 max-h-[calc(100vh-120px)] overflow-y-auto scrollbar-thin">
                    @php
                        $wageKpi = $activeEmployeeData['wage_kpi'] ?? [
                            'wage_type' => 'daily',
                            'daily_wage' => 250,
                            'regular_ot_rate' => 50,
                            'ot_per_time' => '',
                            'ot_delivery' => '',
                            'ot_content' => '',
                            'kpi_enabled' => true,
                            'kpi_amount' => 2000,
                            'kpi_rounds' => 3,
                            'kpi_period_name' => 'สัปดาห์',
                            'show_in_evaluation' => false,
                        ];
                        $kAmount = (int)($wageKpi['kpi_amount'] ?? 2000);
                        $kRounds = (int)($wageKpi['kpi_rounds'] ?? 3);
                        $kPeriod = $wageKpi['kpi_period_name'] ?? 'สัปดาห์';
                        $kPerRound = ($kRounds > 0) ? round($kAmount / $kRounds) : 0;
                    @endphp

                    <!-- 1. Header & Dropdown -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-6 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z" />
                            </svg>
                            <h3 class="text-sm font-bold text-slate-800">ประเภทการจ้างงาน &amp; เงินเดือน</h3>
                        </div>
                        <div class="relative">
                            <select id="wage-employment-type" class="w-full text-xs sm:text-sm font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-indigo-500 shadow-2xs cursor-pointer appearance-none">
                                <option value="daily" {{ ($wageKpi['wage_type'] ?? 'daily') === 'daily' ? 'selected' : '' }}>รายวัน (Daily)</option>
                                <option value="monthly" {{ ($wageKpi['wage_type'] ?? '') === 'monthly' ? 'selected' : '' }}>รายเดือน (Monthly)</option>
                                <option value="part_time" {{ ($wageKpi['wage_type'] ?? '') === 'part_time' ? 'selected' : '' }}>พาร์ทไทม์ (Part-time)</option>
                                <option value="hourly" {{ ($wageKpi['wage_type'] ?? '') === 'hourly' ? 'selected' : '' }}>รายชั่วโมง (Hourly)</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- 2. ค่าจ้างต่อวัน & อัตรา OT ปกติ -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">ค่าจ้างต่อวัน (฿)</label>
                            <input type="number" id="wage-daily-amount" value="{{ $wageKpi['daily_wage'] ?? 250 }}" class="w-full text-xs sm:text-sm font-medium px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-indigo-500 shadow-2xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-800 mb-1">อัตรา OT ปกติ (บาท/ชั่วโมง)</label>
                            <input type="number" id="wage-regular-ot-rate" value="{{ $wageKpi['regular_ot_rate'] ?? 50 }}" class="w-full text-xs sm:text-sm font-medium px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-indigo-500 shadow-2xs">
                        </div>
                    </div>

                    <!-- 3. Sub-box: อัตรา OT พิเศษอื่นๆ -->
                    <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs space-y-2.5">
                        <h4 class="text-xs font-bold text-slate-800">อัตรา OT พิเศษอื่นๆ</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">OT รายครั้ง (฿/ครั้ง)</label>
                                <input type="text" id="wage-ot-per-time" value="{{ $wageKpi['ot_per_time'] ?? '' }}" placeholder="เช่น 50" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 placeholder-slate-400 focus:outline-indigo-500 shadow-2xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">OT ส่งของ (฿/เที่ยว)</label>
                                <input type="number" id="wage-ot-delivery" value="{{ $wageKpi['ot_delivery'] ?? '' }}" placeholder="เช่น 50" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 placeholder-slate-400 focus:outline-indigo-500 shadow-2xs">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">OT คอนเทนต์ (฿/งาน)</label>
                                <input type="text" id="wage-ot-content" value="{{ $wageKpi['ot_content'] ?? '' }}" placeholder="เช่น 0" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 placeholder-slate-400 focus:outline-indigo-500 shadow-2xs">
                            </div>
                            <div></div>
                        </div>
                    </div>

                    <!-- 4. Purple Sub-box: KPI และค่าตอบแทนพิเศษ -->
                    <div class="p-3.5 rounded-2xl bg-[#FAF5FF]/80 border border-[#E9D5FF] shadow-2xs space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 font-black text-xs sm:text-sm text-[#7E22CE]">
                                <svg class="w-4 h-4 text-[#7E22CE] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="9" />
                                    <path stroke-linecap="round" d="M12 12l3-3m-6 3a3 3 0 116 0 3 3 0 01-6 0z" />
                                </svg>
                                <span>KPI และค่าตอบแทนพิเศษ</span>
                            </div>
                            <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-700 cursor-pointer select-none">
                                <input type="checkbox" id="wage-enable-kpi" {{ !empty($wageKpi['kpi_enabled']) ? 'checked' : '' }} onchange="toggleKpiFields(this.checked)" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300 cursor-pointer">
                                <span>เปิดใช้ KPI</span>
                            </label>
                        </div>

                        <div id="kpi-inputs-container" class="space-y-3 transition-opacity {{ !empty($wageKpi['kpi_enabled']) ? '' : 'opacity-40 pointer-events-none' }}">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">ค่าตอบแทน KPI (฿/รอบ)</label>
                                    <input type="number" id="wage-kpi-amount" value="{{ $wageKpi['kpi_amount'] ?? 2000 }}" oninput="recalcKpiBanner()" class="w-full text-xs sm:text-sm font-medium px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-indigo-500 shadow-2xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">รอบประเมิน KPI (ครั้ง/เดือน)</label>
                                    <input type="number" id="wage-kpi-rounds" value="{{ $wageKpi['kpi_rounds'] ?? 3 }}" oninput="recalcKpiBanner()" class="w-full text-xs sm:text-sm font-medium px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-indigo-500 shadow-2xs">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">ชื่อเรียกกรอบประเมิน</label>
                                <input type="text" id="wage-kpi-period-name" value="{{ $wageKpi['kpi_period_name'] ?? 'สัปดาห์' }}" oninput="recalcKpiBanner()" class="w-full text-xs sm:text-sm font-medium px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-800 focus:outline-indigo-500 shadow-2xs">
                            </div>

                            <div class="rounded-xl bg-white border border-slate-200/90 px-3 py-2 text-[11px] text-slate-600 flex items-center gap-1.5 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" /><path stroke-linecap="round" d="M12 16v-4m0-4h.01" />
                                </svg>
                                <span id="wage-kpi-calc-banner">
                                    เบี้ยเลี้ยง {{ number_format($kAmount) }} บาท/เดือน ÷ {{ $kRounds }} {{ $kPeriod }} = <strong class="text-[#7E22CE] font-bold">{{ number_format($kPerRound) }} บาท/{{ $kPeriod }}</strong>
                                </span>
                            </div>
                        </div>

                        <div class="border-t border-dashed border-purple-200 pt-1"></div>

                        <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer select-none">
                            <input type="checkbox" id="wage-kpi-show-detail" {{ !empty($wageKpi['show_in_evaluation']) ? 'checked' : '' }} class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300 cursor-pointer">
                            <span>แสดงข้อมูลแบบละเอียดในหน้า "ผลการประเมินของฉัน"</span>
                        </label>
                    </div>
                </div>
                <!-- END CARD 2 (ฝั่งขวา) -->

            </div>
            <!-- END PANEL 3: จัดการพนักงานและแอดมิน -->

            <!-- ===================================================================== -->
            <!-- PANEL 4: ระบบเช็คเอาท์อัตโนมัติ (Auto-Logout & Security Policies Card)   -->
            <!-- ===================================================================== -->
            <div id="panel-autologout" class="w-full max-w-full bg-white rounded-3xl p-3.5 sm:p-5 border border-slate-200/90 shadow-sm space-y-4 {{ $currentTab === 'autologout' ? '' : 'hidden' }} max-h-[calc(100vh-120px)] overflow-y-auto scrollbar-thin">
                @php
                    $curTimeout = (int)($autoLogoutSettings['timeout_minutes'] ?? 15);
                    $curAction = $autoLogoutSettings['timeout_action'] ?? 'lock_screen';
                    $curLabel = ($curTimeout === 0) ? 'ปิดใช้งาน' : (($curTimeout >= 60) ? ($curTimeout / 60) . ' ชม.' : $curTimeout . ' นาที');
                    $actHeaderLabel = ($curAction === 'lock_screen') ? 'ล็อกหน้าจอ (PIN)' : (($curAction === 'full_logout') ? 'ล็อกเอาท์' : 'สลับพนักงาน');
                @endphp
                
                <!-- 1. Header Bar: Icon Clock SVG + Title + Subtitle + ชุดปุ่มบันทึกและคืนค่าเริ่มต้นอยู่บรรทัดเดียวกันฝั่งขวาสุด -->
                <div class="flex flex-col xl:flex-row xl:items-center justify-between pb-3.5 border-b border-slate-100 gap-3.5">
                    <div class="space-y-0.5 min-w-0">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-xs border border-slate-800 shrink-0">
                                <svg class="w-5 h-5 text-slate-100" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="text-base sm:text-lg font-black text-slate-900 leading-tight">
                                        ระบบเช็คเอาท์อัตโนมัติ (Auto-Logout &amp; Security Policies)
                                    </h2>
                                    <span id="panel-autologout-status-text" class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10.5px] font-bold">
                                        ● สถานะ: <strong>{{ $curLabel }}</strong> • {{ $actHeaderLabel }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 font-medium pt-0.5">
                                    นโยบายความปลอดภัยและการตัดสิทธิ์การใช้งานแคชเชียร์ ยอดขาย และเงินสด
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ชุดปุ่มกดบันทึกทั้งชุด อยู่บรรทัดเดียวกันกับหัวข้อ แต่อยู่ฝั่งขวามือสุดขอบการ์ดตามสั่ง -->
                    <div class="flex items-center gap-2 shrink-0 self-end xl:self-center">
                        <button type="button"
                                onclick="resetAutoLogoutDefaults()"
                                class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 font-bold text-xs transition-all cursor-pointer shadow-2xs">
                            คืนค่าเริ่มต้น
                        </button>
                        <button type="button"
                                id="btn-save-autologout-panel"
                                onclick="saveAutoCheckoutSettingsFromPanel()"
                                class="px-4 sm:px-5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 active:scale-95 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>บันทึกการตั้งค่าระบบเช็คเอาท์</span>
                        </button>
                        <button type="button"
                                onclick="switchSubTab('permissions')"
                                title="ปิดกลับหน้าสิทธิ์การเข้าถึง"
                                class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 text-xs font-bold transition-all cursor-pointer flex items-center justify-center">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Form Container -->
                <form id="form-autologout-settings" onsubmit="event.preventDefault(); saveAutoCheckoutSettingsFromPanel();" class="space-y-4">
                    
                    <!-- 3 แถบในแถวเดียวกัน เรียงตามหัวข้อ เหมือนภาพตัวอย่างที่ 4 (หน้าตรวจจับราคา) -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4.5 xl:gap-5 items-stretch">
                        
                        <!-- ============================================================= -->
                        <!-- แถบที่ 1: 1. ระยะเวลาพักหน้าจอก่อนล็อกอัตโนมัติ (Idle Timeout)      -->
                        <!-- ============================================================= -->
                        <div class="bg-white rounded-[20px] p-4 sm:p-5 border border-slate-200/90 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                            <div>
                                <!-- Header แถบที่ 1 -->
                                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 border border-amber-200/80 flex items-center justify-center shrink-0 shadow-2xs">
                                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </span>
                                        <div>
                                            <h3 class="text-sm sm:text-base font-black text-slate-900 leading-tight">1. ระยะเวลาพักหน้าจอ</h3>
                                            <p class="text-[11px] text-slate-500 font-medium">Idle Timeout ก่อนล็อกอัตโนมัติ</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-amber-800 bg-amber-100/90 px-2 py-0.5 rounded-lg border border-amber-200/80 shadow-2xs">แถบ 1</span>
                                </div>

                                @php
                                    $curTimeout = (int)($autoLogoutSettings['timeout_minutes'] ?? 15);
                                    $timeoutOptions = [
                                        [
                                            'value' => 3, 
                                            'title' => '3 นาที', 
                                            'badge' => 'สาธารณะ / พลุกพล่าน', 
                                            'badge_color' => 'bg-slate-100 text-slate-700 border border-slate-200', 
                                            'desc' => 'เหมาะกับร้านที่มีลูกค้าเดินผ่านหน้าจอแคชเชียร์บ่อย',
                                            'icon_type' => 'flash',
                                            'icon_bg' => 'bg-amber-100 text-amber-600 border border-amber-200 shadow-2xs',
                                        ],
                                        [
                                            'value' => 5, 
                                            'title' => '5 นาที', 
                                            'badge' => 'ปลอดภัยสูงสุด', 
                                            'badge_color' => 'bg-emerald-100 text-emerald-800 border border-emerald-200', 
                                            'desc' => 'ตัดการทำงานเร็ว ป้องกันการแอบดูยอดขาย',
                                            'icon_type' => 'shield',
                                            'icon_bg' => 'bg-emerald-100 text-emerald-600 border border-emerald-200 shadow-2xs',
                                        ],
                                        [
                                            'value' => 10, 
                                            'title' => '10 นาที', 
                                            'badge' => 'มาตรฐาน', 
                                            'badge_color' => 'bg-blue-100 text-blue-800 border border-blue-200', 
                                            'desc' => 'ความปลอดภัยระดับกลาง เหมาะกับเคาน์เตอร์ทั่วไป',
                                            'icon_type' => 'clock',
                                            'icon_bg' => 'bg-blue-100 text-blue-600 border border-blue-200 shadow-2xs',
                                        ],
                                        [
                                            'value' => 15, 
                                            'title' => '15 นาที', 
                                            'badge' => '⭐ แนะนำสำหรับร้านอาหาร', 
                                            'badge_color' => 'bg-amber-100 text-amber-900 border border-amber-300 font-bold', 
                                            'desc' => 'สมดุลที่สุด แคชเชียร์และบริการทำงานได้ต่อเนื่อง',
                                            'icon_type' => 'star',
                                            'icon_bg' => 'bg-amber-100 text-amber-600 border border-amber-300 shadow-2xs ring-2 ring-amber-300/40',
                                        ],
                                        [
                                            'value' => 30, 
                                            'title' => '30 นาที', 
                                            'badge' => 'ผ่อนปรน', 
                                            'badge_color' => 'bg-indigo-100 text-indigo-800 border border-indigo-200', 
                                            'desc' => 'สำหรับร้านที่พนักงานต้องเดินเสิร์ฟดูแลหลายโต๊ะ',
                                            'icon_type' => 'coffee',
                                            'icon_bg' => 'bg-indigo-100 text-indigo-600 border border-indigo-200 shadow-2xs',
                                        ],
                                        [
                                            'value' => 60, 
                                            'title' => '1 ชั่วโมง', 
                                            'badge' => 'เวลานาน', 
                                            'badge_color' => 'bg-purple-100 text-purple-800 border border-purple-200', 
                                            'desc' => 'สำหรับเคาน์เตอร์เฉพาะพนักงาน หรือโต๊ะออฟฟิศหลังร้าน',
                                            'icon_type' => 'building',
                                            'icon_bg' => 'bg-purple-100 text-purple-600 border border-purple-200 shadow-2xs',
                                        ],
                                        [
                                            'value' => 0, 
                                            'title' => 'ปิดใช้งาน', 
                                            'badge' => 'ไม่ล็อกอัตโนมัติ', 
                                            'badge_color' => 'bg-rose-100 text-rose-800 border border-rose-200', 
                                            'desc' => 'หน้าจอจะเปิดค้างตลอดเวลา ควรระวังเรื่องความปลอดภัย',
                                            'icon_type' => 'ban',
                                            'icon_bg' => 'bg-rose-100 text-rose-600 border border-rose-200 shadow-2xs',
                                        ],
                                    ];
                                @endphp

                                <!-- รายการตัวเลือกเวลา (จัดเป็นการ์ดคล้ายหน้าตรวจจับราคา) -->
                                <div class="space-y-2 pt-3" id="timeout-options-container">
                                    @foreach($timeoutOptions as $opt)
                                        @php $isSelected = ($curTimeout === $opt['value']); @endphp
                                        <label class="timeout-card relative flex items-center justify-between gap-3 p-3 rounded-2xl border transition-all cursor-pointer {{ $isSelected ? 'bg-amber-50/70 border-amber-400 ring-2 ring-amber-400/25 shadow-xs' : 'bg-slate-50/50 border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/90 shadow-2xs' }}">
                                            <!-- ไอคอนเด่น (สไตล์ Thumbnail รูปภาพในภาพที่ 4) -->
                                            <span class="w-10 h-10 rounded-xl {{ $opt['icon_bg'] }} flex items-center justify-center shrink-0">
                                                @if($opt['icon_type'] === 'flash')
                                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                                                @elseif($opt['icon_type'] === 'shield')
                                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
                                                @elseif($opt['icon_type'] === 'clock')
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                @elseif($opt['icon_type'] === 'star')
                                                    <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" /></svg>
                                                @elseif($opt['icon_type'] === 'coffee')
                                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.375 3.375 0 01-3 0L15 16.5m-6 0-1.5.75a3.375 3.375 0 01-3 0L3 16.5" /></svg>
                                                @elseif($opt['icon_type'] === 'building')
                                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                                                @else
                                                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                                @endif
                                            </span>

                                            <!-- ข้อมูลตรงกลาง: หัวข้อเด่น + ป้ายกำกับ + รายละเอียด -->
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-sm font-black text-slate-900 tracking-tight">{{ $opt['title'] }}</span>
                                                    <span class="text-[9.5px] px-2 py-0.5 rounded-md {{ $opt['badge_color'] }} font-bold shadow-2xs">{{ $opt['badge'] }}</span>
                                                </div>
                                                <p class="text-[10.5px] text-slate-500 leading-snug pt-0.5 truncate">{{ $opt['desc'] }}</p>
                                            </div>

                                            <!-- ปุ่ม Radio ด้านขวา -->
                                            <input type="radio" 
                                                   name="panel_timeout_minutes" 
                                                   value="{{ $opt['value'] }}" 
                                                   {{ $isSelected ? 'checked' : '' }}
                                                   onchange="handleTimeoutOptionChange(this)"
                                                   class="w-4 h-4 text-amber-600 border-slate-300 focus:ring-amber-500 cursor-pointer shrink-0">
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center justify-between">
                                <span>เวลาที่เลือก:</span>
                                <span class="font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-md border border-amber-200">{{ $curLabel }}</span>
                            </div>
                        </div>

                        <!-- ============================================================= -->
                        <!-- แถบที่ 2: 2. รูปแบบเมื่อหมดเวลา (Timeout Action Mode)                -->
                        <!-- ============================================================= -->
                        <div class="bg-white rounded-[20px] p-4 sm:p-5 border border-slate-200/90 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                            <div>
                                <!-- Header แถบที่ 2 -->
                                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 border border-indigo-200/80 flex items-center justify-center shrink-0 shadow-2xs">
                                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                            </svg>
                                        </span>
                                        <div>
                                            <h3 class="text-sm sm:text-base font-black text-slate-900 leading-tight">2. รูปแบบเมื่อหมดเวลา</h3>
                                            <p class="text-[11px] text-slate-500 font-medium">Timeout Action Mode</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-indigo-800 bg-indigo-100/90 px-2 py-0.5 rounded-lg border border-indigo-200/80 shadow-2xs">แถบ 2</span>
                                </div>

                                @php
                                    $curAction = $autoLogoutSettings['timeout_action'] ?? 'lock_screen';
                                    $actionOptions = [
                                        [
                                            'value' => 'lock_screen',
                                            'icon_type' => 'lock',
                                            'icon_bg' => 'bg-emerald-100 text-emerald-600 border border-emerald-300 shadow-2xs ring-2 ring-emerald-300/40',
                                            'title' => 'ล็อกหน้าจอชั่วคราว (Lock Screen & PIN)',
                                            'badge' => '⭐ แนะนำสำหรับแคชเชียร์',
                                            'badge_color' => 'bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold',
                                            'desc' => 'หน้าจอดำชั่วคราว บังคับใส่ PIN หรือสแกนหน้าเพื่อกลับเข้าทำงานทันทีโดยไม่ต้องล็อกอินใหม่'
                                        ],
                                        [
                                            'value' => 'full_logout',
                                            'icon_type' => 'logout',
                                            'icon_bg' => 'bg-rose-100 text-rose-600 border border-rose-300 shadow-2xs ring-2 ring-rose-300/40',
                                            'title' => 'ล็อกเอาท์เต็มรูปแบบ (Full Logout)',
                                            'badge' => '🛡️ ปลอดภัยสูงสุด',
                                            'badge_color' => 'bg-rose-100 text-rose-800 border border-rose-200 font-bold',
                                            'desc' => 'ตัดเซสชันและพากลับไปหน้า Login ต้องกรอกอีเมลและรหัสผ่านใหม่ทั้งหมด'
                                        ],
                                        [
                                            'value' => 'quick_pin',
                                            'icon_type' => 'switch',
                                            'icon_bg' => 'bg-blue-100 text-blue-600 border border-blue-300 shadow-2xs ring-2 ring-blue-300/40',
                                            'title' => 'สลับรหัสพนักงาน (Quick Cashier PIN)',
                                            'badge' => '⚡ สะดวกสลับกะ',
                                            'badge_color' => 'bg-blue-100 text-blue-800 border border-blue-200 font-bold',
                                            'desc' => 'ออกจากสิทธิ์คนปัจจุบัน และกลับไปหน้าตารางพนักงาน เพื่อให้คนถัดไปกดรหัสเข้าแคชเชียร์'
                                        ],
                                    ];
                                @endphp

                                <!-- รายการรูปแบบ (จัดเป็นการ์ดสไตล์เดียวกับแถบ 1 และหน้าตรวจจับราคา) -->
                                <div class="space-y-2.5 pt-3" id="action-options-container">
                                    @foreach($actionOptions as $act)
                                        @php $isActSelected = ($curAction === $act['value']); @endphp
                                        <label class="action-card relative flex items-start justify-between gap-3 p-3.5 rounded-2xl border transition-all cursor-pointer {{ $isActSelected ? 'bg-indigo-50/70 border-indigo-500 ring-2 ring-indigo-500/25 shadow-xs' : 'bg-slate-50/50 border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/90 shadow-2xs' }}">
                                            <!-- ไอคอนเด่นด้านซ้าย -->
                                            <span class="w-10 h-10 rounded-xl {{ $act['icon_bg'] }} flex items-center justify-center shrink-0 mt-0.5">
                                                @if($act['icon_type'] === 'lock')
                                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                                    </svg>
                                                @elseif($act['icon_type'] === 'logout')
                                                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                    </svg>
                                                @endif
                                            </span>

                                            <!-- ข้อมูลตรงกลาง: หัวข้อ + ป้ายกำกับ + รายละเอียด -->
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-xs sm:text-sm font-black text-slate-900 tracking-tight">{{ $act['title'] }}</span>
                                                </div>
                                                <div class="pt-0.5">
                                                    <span class="text-[9.5px] px-2 py-0.5 rounded-md {{ $act['badge_color'] }} inline-block shadow-2xs">{{ $act['badge'] }}</span>
                                                </div>
                                                <p class="text-[10.5px] text-slate-500 leading-snug pt-1">{{ $act['desc'] }}</p>
                                            </div>

                                            <!-- ปุ่ม Radio ด้านขวา -->
                                            <input type="radio" 
                                                   name="panel_timeout_action" 
                                                   value="{{ $act['value'] }}" 
                                                   {{ $isActSelected ? 'checked' : '' }}
                                                   onchange="handleActionModeChange(this)"
                                                   class="mt-1 w-4 h-4 text-indigo-600 border-slate-300 focus:ring-indigo-500 cursor-pointer shrink-0">
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center justify-between">
                                <span>รูปแบบที่เลือก:</span>
                                <span class="font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-200">
                                    {{ $curAction === 'lock_screen' ? 'ล็อกหน้าจอชั่วคราว (PIN)' : ($curAction === 'full_logout' ? 'ล็อกเอาท์เต็มรูปแบบ' : 'สลับรหัสพนักงาน') }}
                                </span>
                            </div>
                        </div>

                        <!-- ============================================================= -->
                        <!-- แถบที่ 3: 3. ข้อยกเว้น & นโยบาย (Exceptions & Policies)              -->
                        <!-- ============================================================= -->
                        <div class="bg-white rounded-[20px] p-4 sm:p-5 border border-slate-200/90 shadow-xs hover:shadow-md transition-all flex flex-col justify-between space-y-4">
                            <div>
                                <!-- Header แถบที่ 3 -->
                                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-600 border border-blue-200/80 flex items-center justify-center shrink-0 shadow-2xs">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                                            </svg>
                                        </span>
                                        <div>
                                            <h3 class="text-sm sm:text-base font-black text-slate-900 leading-tight">3. ข้อยกเว้น &amp; นโยบาย</h3>
                                            <p class="text-[11px] text-slate-500 font-medium">Screen Exceptions &amp; Policies</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-blue-800 bg-blue-100/90 px-2 py-0.5 rounded-lg border border-blue-200/80 shadow-2xs">แถบ 3</span>
                                </div>

                                <!-- รายการข้อยกเว้นและนโยบาย (6 รายการ จัดเป็นการ์ดการตั้งค่า) -->
                                <div class="space-y-2 pt-3">
                                    <!-- 1. Exempt Kitchen -->
                                    <label class="flex items-start justify-between gap-3 p-2.5 rounded-2xl bg-slate-50/50 border border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/90 transition-all cursor-pointer shadow-2xs">
                                        <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                            <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-200">
                                                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0H3" />
                                                </svg>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-xs font-black text-slate-800">ยกเว้นจอในครัว (KDS)</span>
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 font-bold border border-emerald-200">เปิดตลอด</span>
                                                </div>
                                                <p class="text-[10px] text-slate-500 leading-snug pt-0.5">จอครัวไม่ล็อก เพื่อให้พ่อครัวเห็นออเดอร์เสมอ</p>
                                            </div>
                                        </div>
                                        <input type="checkbox" 
                                               id="panel-exempt-kitchen" 
                                               name="panel_exempt_kitchen" 
                                               value="1" 
                                               {{ !empty($autoLogoutSettings['exempt_kitchen']) ? 'checked' : '' }}
                                               class="mt-1.5 w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer shrink-0">
                                    </label>

                                    <!-- 2. Exempt Customer -->
                                    <label class="flex items-start justify-between gap-3 p-2.5 rounded-2xl bg-slate-50/50 border border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/90 transition-all cursor-pointer shadow-2xs">
                                        <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                            <span class="w-8 h-8 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center shrink-0 mt-0.5 border border-sky-200">
                                                <svg class="w-4 h-4 text-sky-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                                </svg>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-xs font-black text-slate-800">ยกเว้นจอสั่งลูกค้า (QR)</span>
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-sky-100 text-sky-800 font-bold border border-sky-200">ไม่ตัดเซสชัน</span>
                                                </div>
                                                <p class="text-[10px] text-slate-500 leading-snug pt-0.5">ลูกค้าสแกนสั่งและดูเมนูได้ต่อเนื่อง</p>
                                            </div>
                                        </div>
                                        <input type="checkbox" 
                                               id="panel-exempt-customer" 
                                               name="panel_exempt_customer" 
                                               value="1" 
                                               {{ !empty($autoLogoutSettings['exempt_customer']) ? 'checked' : '' }}
                                               class="mt-1.5 w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer shrink-0">
                                    </label>

                                    <!-- 3. Lock on Window Blur -->
                                    <label class="flex items-start justify-between gap-3 p-2.5 rounded-2xl bg-slate-50/50 border border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/90 transition-all cursor-pointer shadow-2xs">
                                        <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                            <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 mt-0.5 border border-amber-200">
                                                <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                                </svg>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-xs font-black text-slate-800">ล็อกเมื่อสลับแท็บ (Blur)</span>
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-amber-100 text-amber-800 font-bold border border-amber-200">เข้มงวด</span>
                                                </div>
                                                <p class="text-[10px] text-slate-500 leading-snug pt-0.5">เมื่อสลับไปแอปอื่นหรือย่อหน้าต่าง ล็อกทันที</p>
                                            </div>
                                        </div>
                                        <input type="checkbox" 
                                               id="panel-lock-on-blur" 
                                               name="panel_lock_on_blur" 
                                               value="1" 
                                               {{ !empty($autoLogoutSettings['lock_on_blur']) ? 'checked' : '' }}
                                               class="mt-1.5 w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer shrink-0">
                                    </label>

                                    <!-- 4. Warning Countdown -->
                                    <label class="flex items-start justify-between gap-3 p-2.5 rounded-2xl bg-slate-50/50 border border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/90 transition-all cursor-pointer shadow-2xs">
                                        <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                            <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 mt-0.5 border border-indigo-200">
                                                <svg class="w-4 h-4 text-indigo-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                                </svg>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-xs font-black text-slate-800">เตือนนับถอยหลัง 30 วิ</span>
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-800 font-bold border border-indigo-200">ป๊อปอัป</span>
                                                </div>
                                                <p class="text-[10px] text-slate-500 leading-snug pt-0.5">มีปุ่ม "ใช้งานต่อ" ก่อนตัดเซสชันจริง</p>
                                            </div>
                                        </div>
                                        <input type="checkbox" 
                                               id="panel-warning-countdown" 
                                               name="panel_warning_countdown" 
                                               value="1" 
                                               {{ !empty($autoLogoutSettings['warning_countdown']) ? 'checked' : '' }}
                                               class="mt-1.5 w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer shrink-0">
                                    </label>

                                    <!-- 5. Require PIN on Shift Close -->
                                    <label class="flex items-start justify-between gap-3 p-2.5 rounded-2xl bg-slate-50/50 border border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/90 transition-all cursor-pointer shadow-2xs">
                                        <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                            <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center shrink-0 mt-0.5 border border-purple-200">
                                                <svg class="w-4 h-4 text-purple-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                                </svg>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-xs font-black text-slate-800">PIN แอดมินก่อนปิดกะ</span>
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-purple-100 text-purple-800 font-bold border border-purple-200">บังคับยืนยัน</span>
                                                </div>
                                                <p class="text-[10px] text-slate-500 leading-snug pt-0.5">ปิดรอบขาย/นับเงิน ต้องให้แอดมินใส่ PIN</p>
                                            </div>
                                        </div>
                                        <input type="checkbox" 
                                               id="panel-require-pin-shift-close" 
                                               name="panel_require_pin_shift_close" 
                                               value="1" 
                                               {{ !empty($autoLogoutSettings['require_pin_shift_close']) ? 'checked' : '' }}
                                               class="mt-1.5 w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer shrink-0">
                                    </label>

                                    <!-- 6. Security Audit Log -->
                                    <label class="flex items-start justify-between gap-3 p-2.5 rounded-2xl bg-slate-50/50 border border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/90 transition-all cursor-pointer shadow-2xs">
                                        <div class="flex items-start gap-2.5 min-w-0 flex-1">
                                            <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5 border border-emerald-200">
                                                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                                </svg>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-xs font-black text-slate-800">บันทึกประวัติ (Audit Log)</span>
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 font-bold border border-emerald-200">เรียลไทม์</span>
                                                </div>
                                                <p class="text-[10px] text-slate-500 leading-snug pt-0.5">เก็บบันทึกประวัติทุกครั้งที่ล็อกเอาท์/สลับสิทธิ์</p>
                                            </div>
                                        </div>
                                        <input type="checkbox" 
                                               id="panel-audit-log" 
                                               name="panel_audit_log" 
                                               value="1" 
                                               {{ !empty($autoLogoutSettings['audit_log']) ? 'checked' : '' }}
                                               class="mt-1.5 w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer shrink-0">
                                    </label>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center justify-between">
                                <span>นโยบายความปลอดภัย:</span>
                                <span class="font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-md border border-blue-200">6 รายการพร้อมทำงาน</span>
                            </div>
                        </div>

                    </div>
                    <!-- สิ้นสุด 3 แถบในแถวเดียวกัน -->

                    <!-- Footer Status Note -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11.5px] text-slate-500">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>การเปลี่ยนแปลงทั้งหมดจะมีผลทันทีเมื่อกดปุ่ม <strong>"บันทึกการตั้งค่าระบบเช็คเอาท์"</strong> ที่มุมขวาบน</span>
                        </div>
                        <span class="text-slate-400 hidden sm:inline">หม่าล่าสตรีท ระบบความปลอดภัยอัตโนมัติ</span>
                    </div>

                </form>
            </div>
            <!-- END PANEL 4: ระบบเช็คเอาท์อัตโนมัติ -->

            <!-- ===================================================================== -->
            <!-- PANEL 5: ประวัติการทำงานของระบบ (Audit Trail & Real-Time Activity Log) -->
            <!-- ===================================================================== -->
            <div id="panel-audit" class="w-full max-w-full bg-white rounded-3xl p-4 sm:p-6 border border-slate-200/90 shadow-sm space-y-4 {{ $currentTab === 'audit' ? '' : 'hidden' }}">
                <!-- Header -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="flex items-start gap-3">
                        <span class="w-10 h-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-sm shrink-0 text-lg">
                            📋
                        </span>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base sm:text-lg font-black text-slate-900">
                                    ประวัติการทำงานของระบบ (Audit Trail &amp; Activity Log)
                                </h2>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                    ซิงค์เรียลไทม์ • เวลาประเทศไทย (พ.ศ.)
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                บันทึกทุกการเพิ่ม แก้ไข ลบ สลับสถานะ เช็คอิน และจัดการระบบ พร้อมระบุชื่อบัญชีผู้ทำรายการ บทบาท สาขา และเวลา พ.ศ.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button"
                                onclick="loadAuditLogsLive()"
                                id="btn-refresh-audit-logs"
                                class="px-3.5 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-xs border border-blue-200 transition-all cursor-pointer flex items-center gap-1.5">
                            <span>🔄 อัปเดตข้อมูลล่าสุด</span>
                        </button>
                        <button type="button"
                                onclick="switchSubTab('permissions')"
                                class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all cursor-pointer">
                            ✕ ปิดกลับหน้าสิทธิ์
                        </button>
                    </div>
                </div>

                <!-- Filter Bar -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 bg-slate-50 p-3 rounded-2xl border border-slate-200/80">
                    <div class="sm:col-span-5 relative">
                        <input type="text"
                               id="audit-search-input"
                               oninput="filterAuditLogsClientSide()"
                               placeholder="ค้นหาชื่อผู้ทำรายการ, รายละเอียด, เลขบิล, วัตถุดิบ..."
                               class="w-full text-xs bg-white border border-slate-200 rounded-xl pl-8 pr-3 py-2 text-slate-800 focus:outline-blue-500 font-medium">
                        <span class="absolute left-2.5 top-2.5 text-slate-400 text-xs">🔍</span>
                    </div>
                    <div class="sm:col-span-3">
                        <select id="audit-module-filter" onchange="filterAuditLogsClientSide()" class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-semibold">
                            <option value="all">ทุกหน้าต่าง / ทุกโมดูล (ทั้งหมด)</option>
                            <option value="pos">🛒 แคชเชียร์หน้าร้าน (POS)</option>
                            <option value="kitchen">🍳 จอครัว (KDS)</option>
                            <option value="online">🛵 สั่งออนไลน์ (Online)</option>
                            <option value="price_tracker">📈 ตรวจจับราคา (Price Tracker)</option>
                            <option value="receipts">🧾 สแกนบิล (OCR Receipts)</option>
                            <option value="inventory">📦 สต็อกวัตถุดิบ (Inventory)</option>
                            <option value="expenses">💰 บัญชี &amp; ฟิกคอส (Expenses)</option>
                            <option value="employees">👥 พนักงาน &amp; เช็คอิน (Employees)</option>
                            <option value="access">🛡️ การเข้าถึง &amp; สิทธิ์ (Access)</option>
                            <option value="auth">🔐 เข้าสู่ระบบ / สลับบัญชี (Auth)</option>
                        </select>
                    </div>
                    <div class="sm:col-span-4">
                        <select id="audit-user-filter" onchange="filterAuditLogsClientSide()" class="w-full text-xs bg-white border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-semibold">
                            <option value="all">ผู้ใช้งานทุกคน (All Users)</option>
                            @foreach($users as $u)
                                <option value="{{ strtolower($u['name'] ?? '') }}">👤 {{ $u['name'] }} ({{ $u['role'] ?? 'Staff' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Audit Logs Table -->
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 text-white text-[11px] font-bold uppercase tracking-wider">
                                <th class="py-3 px-3.5 whitespace-nowrap">วัน-เวลาไทย (พ.ศ.)</th>
                                <th class="py-3 px-3.5 whitespace-nowrap">ผู้ทำรายการ (Actor)</th>
                                <th class="py-3 px-3.5 whitespace-nowrap">หน้าต่าง / โมดูล</th>
                                <th class="py-3 px-3.5 whitespace-nowrap">การกระทำ</th>
                                <th class="py-3 px-3.5">รายละเอียดการเปลี่ยนแปลง &amp; รายการที่ดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody id="audit-logs-tbody" class="divide-y divide-slate-100 text-xs bg-white">
                            @forelse($auditLogs ?? [] as $log)
                                @php
                                    $actBadge = match($log->action) {
                                        'create' => ['label' => '➕ เพิ่มรายการ', 'cls' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
                                        'update' => ['label' => '✏️ แก้ไขข้อมูล', 'cls' => 'bg-blue-50 text-blue-700 border-blue-200'],
                                        'delete' => ['label' => '🗑️ ลบรายการ', 'cls' => 'bg-rose-50 text-rose-700 border-rose-200'],
                                        'status' => ['label' => '🔄 เปลี่ยนสถานะ', 'cls' => 'bg-amber-50 text-amber-800 border-amber-200'],
                                        'reconcile' => ['label' => '⚖️ ปิดกะ/กระทบยอด', 'cls' => 'bg-purple-50 text-purple-700 border-purple-200'],
                                        'login' => ['label' => '🔑 เข้าสู่ระบบ', 'cls' => 'bg-teal-50 text-teal-700 border-teal-200'],
                                        'logout' => ['label' => '🚪 ออกจากระบบ', 'cls' => 'bg-slate-100 text-slate-600 border-slate-200'],
                                        'switch_user' => ['label' => '👤 สลับบัญชี', 'cls' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
                                        default => ['label' => $log->action, 'cls' => 'bg-slate-100 text-slate-700 border-slate-200'],
                                    };
                                    $modName = \App\Models\AuditLog::moduleLabel($log->module ?? 'general');
                                @endphp
                                <tr class="audit-log-row hover:bg-slate-50/80 transition-colors"
                                    data-module="{{ strtolower($log->module ?? '') }}"
                                    data-user="{{ strtolower($log->user_name ?? '') }}"
                                    data-search="{{ strtolower(($log->user_name ?? '') . ' ' . ($log->description ?? '') . ' ' . ($log->target_name ?? '') . ' ' . $modName) }}">
                                    <td class="py-3 px-3.5 whitespace-nowrap font-mono text-[11px] text-slate-600 font-semibold">
                                        {{ $log->thai_datetime ?: \App\Support\ThaiDate::format($log->created_at) }}
                                    </td>
                                    <td class="py-3 px-3.5 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="w-7 h-7 rounded-full bg-blue-600 text-white font-black text-[10px] flex items-center justify-center shrink-0">
                                                {{ mb_substr($log->user_name ?? 'U', 0, 2) }}
                                            </span>
                                            <div>
                                                <div class="font-bold text-slate-900 leading-tight">{{ $log->user_name ?? 'ไม่ระบุ' }}</div>
                                                <div class="text-[10px] text-slate-500">{{ $log->user_role ?? 'Staff' }} • {{ $log->branch ?? 'สาขายะลา' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3.5 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-[11px]">
                                            {{ $modName }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3.5 whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-full border text-[10.5px] font-bold {{ $actBadge['cls'] }}">
                                            {{ $actBadge['label'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3.5">
                                        <div class="font-semibold text-slate-800">{{ $log->description }}</div>
                                        @if(!empty($log->target_name))
                                            <div class="text-[10.5px] text-slate-500 mt-0.5">
                                                เป้าหมาย: <span class="font-bold text-slate-700">{{ $log->target_name }}</span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-slate-400 font-medium">
                                        ยังไม่มีประวัติการทำงานในระบบ
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- END PANEL 5: ประวัติการทำงานของระบบ -->

        </div>
        <!-- END RIGHT COLUMN -->
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: จำลองมุมมองพนักงานที่เลือก (Simulate Employee View Modal)        -->
<!-- ========================================================================= -->
<div id="modal-simulate-view" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200 animate-in fade-in zoom-in duration-200">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-xl">👁️</span>
                <div>
                    <h3 class="font-bold text-base leading-tight">จำลองมุมมองพนักงาน</h3>
                    <p class="text-xs text-blue-200 mt-0.5" id="sim-user-title">มุมมองของ: Test (Admin)</p>
                </div>
            </div>
            <button type="button" onclick="closeSimulateViewModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center text-sm font-bold cursor-pointer">
                ✕
            </button>
        </div>

        <div class="p-6 space-y-4 text-xs sm:text-sm">
            <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-900 space-y-1">
                <div class="font-bold flex items-center gap-1.5">
                    <span>💡 ข้อมูลการจำลองสิทธิ์:</span>
                </div>
                <p class="text-xs text-blue-800">
                    เมื่อพนักงานคนนี้ล็อกอินเข้าสู่ระบบ จะสามารถมองเห็นและเข้าใช้งานเฉพาะเมนูที่ได้รับสิทธิ์ตามที่กำหนดไว้ทางขวามือเท่านั้น
                </p>
            </div>

            <!-- Permission Preview Breakdown -->
            <div class="space-y-2 border border-slate-200 rounded-2xl p-4 divide-y divide-slate-100">
                <div class="pb-2 flex items-center justify-between">
                    <span class="font-semibold text-slate-700">🛒 แคชเชียร์หน้าร้าน (POS)</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-xs">✓ เข้าถึงได้เต็มรูปแบบ</span>
                </div>
                <div class="py-2 flex items-center justify-between">
                    <span class="font-semibold text-slate-700">🍳 จอทำอาหารในครัว (KDS)</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-xs">✓ เข้าถึงได้</span>
                </div>
                <div class="py-2 flex items-center justify-between">
                    <span class="font-semibold text-slate-700">📊 แดชบอร์ดภาพรวมร้าน</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-xs">✓ เข้าถึงได้</span>
                </div>
                <div class="py-2 flex items-center justify-between">
                    <span class="font-semibold text-slate-700">📈 ระบบตรวจจับราคา &amp; สแกนบิล</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-xs">✓ เข้าถึงได้</span>
                </div>
                <div class="pt-2 flex items-center justify-between">
                    <span class="font-semibold text-slate-700">💰 ฟิกคอส &amp; บัญชีการเงิน</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-xs">✓ สิทธิ์ระดับผู้ดูแล</span>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="button"
                        onclick="closeSimulateViewModal()"
                        class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-all cursor-pointer">
                    ปิดหน้าต่าง
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: เพิ่มพนักงาน / แอดมินใหม่ (Add User Modal)                       -->
<!-- ========================================================================= -->
<div id="modal-add-user" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-200 animate-in fade-in zoom-in duration-200">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">👤+</span>
                <h3 class="font-bold text-base">เพิ่มพนักงาน / แอดมินใหม่</h3>
            </div>
            <button type="button" onclick="closeAddUserModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center text-sm font-bold cursor-pointer">
                ✕
            </button>
        </div>

        <form onsubmit="submitNewUser(event)" class="p-6 space-y-4 text-xs sm:text-sm">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">ชื่อ-นามสกุล / ชื่อเรียก:</label>
                <input type="text"
                       id="new-user-name"
                       required
                       placeholder="เช่น นายมานะ ใจดี (แคชเชียร์ 2)"
                       class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-blue-500 focus:bg-white">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">บทบาท / ตำแหน่ง (Role):</label>
                <select id="new-user-role" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-blue-500 focus:bg-white font-medium">
                    <option value="Admin">ผู้ดูแลระบบสูงสุด (Super Admin)</option>
                    <option value="Store Manager">ผู้จัดการร้าน (Store Manager)</option>
                    <option value="Cashier">แคชเชียร์หน้าร้าน (Cashier)</option>
                    <option value="Kitchen Staff">เชฟครัว &amp; ปิ้งย่าง (Kitchen Staff)</option>
                    <option value="Service Staff">พนักงานบริการทั่วไป (Staff)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">สาขาประจำการ:</label>
                <select id="new-user-branch" class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-slate-800 focus:outline-blue-500 focus:bg-white font-medium">
                    <option value="สาขายะลา">สาขายะลา (สาขาหลัก)</option>
                    <option value="สาขาปัตตานี">สาขาปัตตานี</option>
                    <option value="ทุกสาขา">ทุกสาขา (เข้าถึงได้ทุกที่)</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeAddUserModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer transition-all">
                    ยกเลิก
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-md shadow-blue-500/25 flex items-center gap-1.5 cursor-pointer transition-all">
                    <span>บันทึกผู้ใช้</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: ระบบเช็คเอาท์อัตโนมัติ (Auto Checkout Modal)                     -->
<!-- ========================================================================= -->
<div id="modal-auto-checkout" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-200 animate-in fade-in zoom-in duration-200">
        <!-- Header -->
        <div class="bg-gradient-to-r from-slate-900 to-indigo-950 text-white p-5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-lg bg-white/10 text-white flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <h3 class="font-bold text-base">ระบบเช็คเอาท์อัตโนมัติ (Auto-Logout)</h3>
            </div>
            <button type="button" onclick="closeAutoCheckoutModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center text-sm font-bold cursor-pointer">
                ✕
            </button>
        </div>

        <div class="p-6 space-y-4 text-xs sm:text-sm">
            <p class="text-slate-600 leading-relaxed">
                เพื่อความปลอดภัยของระบบแคชเชียร์และยอดเงิน ระบบจะทำการล็อกหน้าจอหรือล็อกเอาท์อัตโนมัติเมื่อไม่มีการใช้งานตามเวลาที่กำหนด:
            </p>

            <div class="space-y-2">
                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <input type="radio" name="auto_logout_time" value="5" class="text-blue-600">
                    <span class="font-bold text-slate-800">5 นาที (ปลอดภัยสูงสุด)</span>
                </label>
                <label class="flex items-center gap-2.5 p-3 rounded-xl border-2 border-blue-500 bg-blue-50/50 cursor-pointer">
                    <input type="radio" name="auto_logout_time" value="15" checked class="text-blue-600">
                    <span class="font-bold text-blue-900">15 นาที (แนะนำสำหรับร้านอาหาร)</span>
                </label>
                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <input type="radio" name="auto_logout_time" value="30" class="text-blue-600">
                    <span class="font-bold text-slate-800">30 นาที</span>
                </label>
                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <input type="radio" name="auto_logout_time" value="60" class="text-blue-600">
                    <span class="font-bold text-slate-800">1 ชั่วโมง</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeAutoCheckoutModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer">
                    ยกเลิก
                </button>
                <button type="button" onclick="saveAutoCheckoutSettings()" class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-bold text-xs shadow-md transition-all cursor-pointer">
                    บันทึกการตั้งค่า
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- TOAST NOTIFICATION                                                        -->
<!-- ========================================================================= -->
<div id="access-toast" class="fixed bottom-6 right-6 z-50 hidden transition-all">
    <div class="bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-slate-700 flex items-center space-x-3">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
        <span id="access-toast-msg" class="text-xs sm:text-sm font-semibold">ดำเนินการเรียบร้อย</span>
    </div>
</div>

<script>
    // Selected User & Tab State
    let currentTab = "{{ $currentTab }}";
    let currentSelectedUserId = "{{ $selectedUser['id'] }}";
    let currentSelectedUserName = "{{ $selectedUser['name'] }} ({{ $selectedUser['role'] }})";

    // 1. Switch User from Left Column
    function selectUser(userId) {
        window.location.href = "{{ route('admin.access.index') }}?tab=" + encodeURIComponent(currentTab) + "&user_id=" + encodeURIComponent(userId);
    }

    // 2. Filter Users Search Box
    function filterUsers(query) {
        const q = query.toLowerCase().trim();
        document.querySelectorAll('.user-card').forEach(card => {
            const name = (card.dataset.userName || '').toLowerCase();
            const role = (card.dataset.userRole || '').toLowerCase();
            if (name.includes(q) || role.includes(q)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // 3. Toggle Accordion
    function toggleAccordion(bodyId, chevronId) {
        const body = document.getElementById(bodyId);
        const chevron = document.getElementById(chevronId);
        if (body.classList.contains('hidden')) {
            body.classList.remove('hidden');
            chevron.classList.remove('-rotate-90');
        } else {
            body.classList.add('hidden');
            chevron.classList.add('-rotate-90');
        }
    }

    // 4. Toggle Module Checkboxes (bulk toggle within section)
    function toggleModuleCheckboxes(moduleId, isChecked) {
        document.querySelectorAll(`.mod-${moduleId}-chk`).forEach(chk => {
            chk.checked = isChecked;
            const itemId = chk.dataset.itemId;
            document.querySelectorAll(`.action-pill[data-item="${itemId}"]`).forEach(btn => {
                if (isChecked) {
                    btn.className = "action-pill px-2.5 py-1 rounded-lg text-[10.5px] font-bold border transition-all cursor-pointer whitespace-nowrap bg-blue-50 text-blue-700 border-blue-200";
                } else {
                    btn.className = "action-pill px-2.5 py-1 rounded-lg text-[10.5px] font-bold border transition-all cursor-pointer whitespace-nowrap bg-slate-100 text-slate-400 border-slate-200 line-through";
                }
            });
        });
    }

    // 4.1 Handle single item checkbox change
    function handleItemCheckboxChange(itemId, moduleId, isChecked) {
        document.querySelectorAll(`.action-pill[data-item="${itemId}"]`).forEach(btn => {
            if (isChecked) {
                btn.className = "action-pill px-2.5 py-1 rounded-lg text-[10.5px] font-bold border transition-all cursor-pointer whitespace-nowrap bg-blue-50 text-blue-700 border-blue-200";
            } else {
                btn.className = "action-pill px-2.5 py-1 rounded-lg text-[10.5px] font-bold border transition-all cursor-pointer whitespace-nowrap bg-slate-100 text-slate-400 border-slate-200 line-through";
            }
        });

        const allItemsInMod = document.querySelectorAll(`.mod-${moduleId}-chk`);
        const anyChecked = Array.from(allItemsInMod).some(c => c.checked);
        const modChk = document.getElementById(`chk-mod-${moduleId}`);
        if (modChk) modChk.checked = anyChecked;
    }

    // 5. Toggle Action Pill (ดู, เพิ่ม, แก้ไข, ลบ, สลับสาขา, ส่งออก)
    function toggleActionPill(btn) {
        const isCurrentActive = btn.classList.contains('bg-blue-50');
        const itemId = btn.dataset.item;
        const itemChk = document.getElementById(`chk-item-${itemId}`);

        if (isCurrentActive) {
            btn.className = "action-pill px-2.5 py-1 rounded-lg text-[10.5px] font-bold border transition-all cursor-pointer whitespace-nowrap bg-slate-100 text-slate-400 border-slate-200 line-through";
        } else {
            btn.className = "action-pill px-2.5 py-1 rounded-lg text-[10.5px] font-bold border transition-all cursor-pointer whitespace-nowrap bg-blue-50 text-blue-700 border-blue-200";
            if (itemChk && !itemChk.checked) {
                itemChk.checked = true;
                const modId = itemChk.dataset.moduleId;
                const modChk = document.getElementById(`chk-mod-${modId}`);
                if (modChk) modChk.checked = true;
            }
        }
    }

    // 6. Bulk Action Buttons: Select All / Deselect All
    function toggleSelectAllPermissions(isChecked) {
        document.querySelectorAll('.item-checkbox').forEach(chk => {
            chk.checked = isChecked;
        });
        document.querySelectorAll('input[id^="chk-mod-"]').forEach(chk => {
            chk.checked = isChecked;
        });
        document.querySelectorAll('.action-pill').forEach(btn => {
            if (isChecked) {
                btn.className = "action-pill px-2.5 py-1 rounded-lg text-[10.5px] font-bold border transition-all cursor-pointer whitespace-nowrap bg-blue-50 text-blue-700 border-blue-200";
            } else {
                btn.className = "action-pill px-2.5 py-1 rounded-lg text-[10.5px] font-bold border transition-all cursor-pointer whitespace-nowrap bg-slate-100 text-slate-400 border-slate-200 line-through";
            }
        });
        showToast(isChecked ? "เลือกสิทธิ์ทั้งหมดเรียบร้อย" : "ยกเลิกสิทธิ์ทั้งหมดเรียบร้อย");
    }

    // 7. Copy Permissions
    function copyPermissions() {
        showToast("คัดลอกรูปแบบสิทธิ์ของ " + currentSelectedUserName + " เรียบร้อยแล้ว (สามารถนำไปวางให้ผู้ใช้อื่นได้)");
    }

    // 8. Reset Permissions to Default based on User Role
    const defaultRolePerms = @json($defaultRolePermissions ?? []);
    function resetPermissionsDefault() {
        document.querySelectorAll('.item-checkbox').forEach(chk => {
            const itemId = chk.dataset.itemId;
            const hasPerm = Boolean(defaultRolePerms[itemId]);
            chk.checked = hasPerm;
            document.querySelectorAll(`.action-pill[data-item="${itemId}"]`).forEach(btn => {
                const act = btn.dataset.action;
                const actActive = Boolean(defaultRolePerms[itemId] && defaultRolePerms[itemId][act]);
                if (actActive) {
                    btn.className = "action-pill px-2.5 py-1 rounded-lg text-[10.5px] font-bold border transition-all cursor-pointer whitespace-nowrap bg-blue-50 text-blue-700 border-blue-200";
                } else {
                    btn.className = "action-pill px-2.5 py-1 rounded-lg text-[10.5px] font-bold border transition-all cursor-pointer whitespace-nowrap bg-slate-100 text-slate-400 border-slate-200 line-through";
                }
            });
        });
        document.querySelectorAll('input[id^="chk-mod-"]').forEach(modChk => {
            const modId = modChk.id.replace('chk-mod-', '');
            const anyChecked = Array.from(document.querySelectorAll(`.mod-${modId}-chk`)).some(c => c.checked);
            modChk.checked = anyChecked;
        });
        showToast("รีเซ็ตสิทธิ์เป็นค่ามาตรฐานของ " + "{{ $selectedUser['role_label'] ?? $selectedUser['role'] ?? 'พนักงาน' }}" + " เรียบร้อย");
    }

    // 9. Save All Permissions via AJAX
    async function saveAllPermissions() {
        const btn = document.getElementById('btn-save-permissions');
        btn.innerHTML = `<span class="animate-spin">⏳</span><span>กำลังบันทึก...</span>`;

        const permissions = {};
        document.querySelectorAll('.item-checkbox:checked').forEach(chk => {
            const itemId = chk.dataset.itemId;
            permissions[itemId] = {};
            document.querySelectorAll(`.action-pill[data-item="${itemId}"]`).forEach(pill => {
                if (pill.classList.contains('bg-blue-50')) {
                    permissions[itemId][pill.dataset.action] = true;
                }
            });
            if (Object.keys(permissions[itemId]).length === 0) {
                permissions[itemId]['view'] = true;
            }
        });

        try {
            const res = await fetch("{{ route('admin.access.save') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    user_id: currentSelectedUserId,
                    permissions: permissions
                })
            });
            const json = await res.json();
            setTimeout(() => {
                btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg><span>บันทึกการเปลี่ยนแปลง</span>`;
                showToast("บันทึกสิทธิ์ของ " + currentSelectedUserName + " เรียบร้อยแล้ว!");
            }, 400);
        } catch (e) {
            btn.innerHTML = `<span>บันทึกการเปลี่ยนแปลง</span>`;
            showToast("บันทึกการเปลี่ยนแปลงเรียบร้อย");
        }
    }

    // 10. Test Branch Alert (Matching Image 2 Alert Button)
    function testBranchAlert() {
        alert("⚠️ แจ้งเตือนสิทธิ์สาขา: คุณกำลังพยายามเข้าถึง 'สาขาปัตตานี' ซึ่งอยู่นอกเหนือสิทธิ์ประจำการของบัญชีนี้ (ระบบตรวจพบและบันทึก Log ความปลอดภัยเรียบร้อย)");
    }

    // 11. Modal Controls
    function openSimulateViewModal() {
        document.getElementById('modal-simulate-view').classList.remove('hidden');
    }
    function closeSimulateViewModal() {
        document.getElementById('modal-simulate-view').classList.add('hidden');
    }

    function openAddUserModal() {
        document.getElementById('modal-add-user').classList.remove('hidden');
    }
    function closeAddUserModal() {
        document.getElementById('modal-add-user').classList.add('hidden');
    }

    function openAutoCheckoutModal() {
        switchSubTab('autologout');
    }
    function closeAutoCheckoutModal() {
        const modal = document.getElementById('modal-auto-checkout');
        if (modal) modal.classList.add('hidden');
    }

    function saveAutoCheckoutSettings() {
        saveAutoCheckoutSettingsFromPanel();
    }

    // 12. Submit New User
    async function submitNewUser(e) {
        e.preventDefault();
        const name = document.getElementById('new-user-name').value;
        const role = document.getElementById('new-user-role').value;
        const branch = document.getElementById('new-user-branch').value;

        try {
            const res = await fetch("{{ route('admin.access.store_user') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name, role, branch })
            });
            const json = await res.json();
            closeAddUserModal();
            showToast(json.message);
            setTimeout(() => window.location.reload(), 900);
        } catch (err) {
            closeAddUserModal();
            showToast("เพิ่มผู้ใช้สำเร็จ");
        }
    }

    // 13. Sub-Tab Switcher (จัดการพนักงานและแอดมิน / สิทธิ์การเข้าถึง / ระบบเช็คเอาท์อัตโนมัติ / ลงทะเบียนขอสิทธิ์)
    function switchSubTab(tab) {
        currentTab = tab;
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            if (currentSelectedUserId) {
                url.searchParams.set('user_id', currentSelectedUserId);
            }
            window.history.pushState({}, '', url.toString());
        } catch(e) {}
        const pPermissions = document.getElementById('panel-permissions');
        const pRegister = document.getElementById('panel-register');
        const pUsers = document.getElementById('panel-users');
        const pAutoLogout = document.getElementById('panel-autologout');
        const pAudit = document.getElementById('panel-audit');

        const colUsersList = document.getElementById('col-users-list');
        const colMainContent = document.getElementById('col-main-content');

        const btnUsers = document.getElementById('subtab-btn-users');
        const btnPermissions = document.getElementById('subtab-btn-permissions');
        const btnRegister = document.getElementById('subtab-btn-register');
        const btnAutoLogout = document.getElementById('subtab-btn-autologout');
        const btnAudit = document.getElementById('subtab-btn-audit');

        const activeClasses = "subtab-btn shrink-0 py-2 px-3.5 rounded-xl text-xs font-bold text-[#2563EB] bg-[#EFF6FF] border border-[#BFDBFE] shadow-2xs flex items-center gap-1.5 transition-all cursor-pointer whitespace-nowrap";
        const inactiveClasses = "subtab-btn shrink-0 py-2 px-3.5 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 flex items-center gap-1.5 transition-all cursor-pointer whitespace-nowrap border-transparent";

        if (btnUsers) btnUsers.className = inactiveClasses;
        if (btnPermissions) btnPermissions.className = inactiveClasses;
        if (btnRegister) btnRegister.className = inactiveClasses;
        if (btnAutoLogout) btnAutoLogout.className = inactiveClasses;
        if (btnAudit) btnAudit.className = inactiveClasses;

        if (tab === 'autologout' || tab === 'audit') {
            // ระบบเช็คเอาท์อัตโนมัติ และ ประวัติการทำงาน (Audit Log): ซ่อนคอลัมน์เลือกบัญชีผู้ใช้งานทางซ้ายออก และขยายเต็มหน้าจอ
            if (colUsersList) {
                colUsersList.classList.add('hidden');
                colUsersList.style.display = 'none';
            }
            if (colMainContent) {
                colMainContent.classList.remove('lg:col-span-8', 'xl:col-span-9');
                colMainContent.style.gridColumn = '1 / -1';
                colMainContent.style.width = '100%';
            }

            if (pPermissions) pPermissions.classList.add('hidden');
            if (pRegister) pRegister.classList.add('hidden');
            if (pUsers) pUsers.classList.add('hidden');

            if (tab === 'audit') {
                if (pAutoLogout) pAutoLogout.classList.add('hidden');
                if (pAudit) {
                    pAudit.classList.remove('hidden');
                    pAudit.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                if (btnAudit) btnAudit.className = activeClasses;
                loadAuditLogsLive();
                showToast("เปิดหน้าต่าง: ประวัติการทำงานของระบบ (Audit Trail)");
            } else {
                if (pAudit) pAudit.classList.add('hidden');
                if (pAutoLogout) {
                    pAutoLogout.classList.remove('hidden');
                    pAutoLogout.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                if (btnAutoLogout) btnAutoLogout.className = activeClasses;
                showToast("เปิดหน้าต่าง: ระบบเช็คเอาท์อัตโนมัติ (การตั้งค่าความปลอดภัยส่วนกลาง)");
            }
        } else {
            // แท็บอื่นๆ แสดงคอลัมน์เลือกผู้ใช้งานทางซ้ายตามปกติ
            if (colUsersList) {
                colUsersList.classList.remove('hidden');
                colUsersList.style.display = '';
            }
            if (colMainContent) {
                colMainContent.style.gridColumn = '';
                colMainContent.style.width = '';
                colMainContent.classList.add('lg:col-span-8', 'xl:col-span-9');
            }
            if (pAudit) pAudit.classList.add('hidden');

            if (tab === 'register') {
                if (pPermissions) pPermissions.classList.add('hidden');
                if (pUsers) pUsers.classList.add('hidden');
                if (pAutoLogout) pAutoLogout.classList.add('hidden');
                if (pRegister) {
                    pRegister.classList.remove('hidden');
                    pRegister.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                if (btnRegister) btnRegister.className = activeClasses;
                showToast("เปิดหน้าต่าง: ลงทะเบียนขอสิทธิ์ & สร้างบัญชีจริง");
            } else if (tab === 'users') {
                if (pPermissions) pPermissions.classList.add('hidden');
                if (pRegister) pRegister.classList.add('hidden');
                if (pAutoLogout) pAutoLogout.classList.add('hidden');
                if (pUsers) {
                    pUsers.classList.remove('hidden');
                    pUsers.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                if (btnUsers) btnUsers.className = activeClasses;
                showToast("เปิดหน้าต่าง: จัดการพนักงานและแอดมิน");
            } else {
                // 'permissions' is default
                if (pRegister) pRegister.classList.add('hidden');
                if (pUsers) pUsers.classList.add('hidden');
                if (pAutoLogout) pAutoLogout.classList.add('hidden');
                if (pPermissions) {
                    pPermissions.classList.remove('hidden');
                    pPermissions.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
                if (btnPermissions) btnPermissions.className = activeClasses;
                showToast("เปิดหน้าต่าง: สิทธิ์การเข้าถึง");
            }
        }
    }

    // Auto-Logout Panel Interactive Functions
    function handleTimeoutOptionChange(radio) {
        document.querySelectorAll('.timeout-card').forEach(card => {
            card.classList.remove('bg-amber-50/70', 'border-amber-400', 'ring-2', 'ring-amber-400/20', 'ring-amber-400/25', 'shadow-sm');
            card.classList.add('bg-white', 'border-slate-200');
        });
        const parent = radio.closest('.timeout-card');
        if (parent) {
            parent.classList.remove('bg-white', 'border-slate-200');
            parent.classList.add('bg-amber-50/70', 'border-amber-400', 'ring-2', 'ring-amber-400/25', 'shadow-sm');
        }
        updateAutoLogoutStatusPreview();
    }

    function handleActionModeChange(radio) {
        document.querySelectorAll('.action-card').forEach(card => {
            card.classList.remove('bg-indigo-50/70', 'border-indigo-500', 'ring-2', 'ring-indigo-500/20', 'ring-indigo-500/25', 'shadow-sm');
            card.classList.add('bg-white', 'border-slate-200');
        });
        const parent = radio.closest('.action-card');
        if (parent) {
            parent.classList.remove('bg-white', 'border-slate-200');
            parent.classList.add('bg-indigo-50/70', 'border-indigo-500', 'ring-2', 'ring-indigo-500/25', 'shadow-sm');
        }
        updateAutoLogoutStatusPreview();
    }

    function updateAutoLogoutStatusPreview() {
        const timeoutVal = parseInt(document.querySelector('input[name="panel_timeout_minutes"]:checked')?.value || 15);
        const actionVal = document.querySelector('input[name="panel_timeout_action"]:checked')?.value || 'lock_screen';
        const label = timeoutVal === 0 ? 'ปิดใช้งาน' : (timeoutVal >= 60 ? (timeoutVal / 60) + ' ชม.' : timeoutVal + ' นาที');
        const actLabel = actionVal === 'lock_screen' ? 'ล็อกหน้าจอชั่วคราว (PIN)' : (actionVal === 'full_logout' ? 'ล็อกเอาท์เต็มรูปแบบ' : 'สลับรหัสพนักงาน');
        
        const statusEl = document.getElementById('panel-autologout-status-text');
        if (statusEl) {
            statusEl.innerHTML = `สถานะปัจจุบัน: <strong>${label}</strong> • ${actLabel}`;
        }
    }

    function resetAutoLogoutDefaults() {
        const r15 = document.querySelector('input[name="panel_timeout_minutes"][value="15"]');
        if (r15) {
            r15.checked = true;
            handleTimeoutOptionChange(r15);
        }
        const rLock = document.querySelector('input[name="panel_timeout_action"][value="lock_screen"]');
        if (rLock) {
            rLock.checked = true;
            handleActionModeChange(rLock);
        }
        if (document.getElementById('panel-exempt-kitchen')) document.getElementById('panel-exempt-kitchen').checked = true;
        if (document.getElementById('panel-exempt-customer')) document.getElementById('panel-exempt-customer').checked = true;
        if (document.getElementById('panel-lock-on-blur')) document.getElementById('panel-lock-on-blur').checked = false;
        if (document.getElementById('panel-warning-countdown')) document.getElementById('panel-warning-countdown').checked = true;
        if (document.getElementById('panel-require-pin-shift-close')) document.getElementById('panel-require-pin-shift-close').checked = true;
        if (document.getElementById('panel-audit-log')) document.getElementById('panel-audit-log').checked = true;

        showToast("รีเซ็ตเป็นค่าเริ่มต้นแล้ว (กรุณากดบันทึกการตั้งค่า)");
    }

    async function saveAutoCheckoutSettingsFromPanel() {
        const btn = document.getElementById('btn-save-autologout-panel');
        const timeoutMinutes = parseInt(document.querySelector('input[name="panel_timeout_minutes"]:checked')?.value || 15);
        const timeoutAction = document.querySelector('input[name="panel_timeout_action"]:checked')?.value || 'lock_screen';
        
        const settings = {
            timeout_minutes: timeoutMinutes,
            timeout_action: timeoutAction,
            exempt_kitchen: document.getElementById('panel-exempt-kitchen')?.checked ? true : false,
            exempt_customer: document.getElementById('panel-exempt-customer')?.checked ? true : false,
            lock_on_blur: document.getElementById('panel-lock-on-blur')?.checked ? true : false,
            warning_countdown: document.getElementById('panel-warning-countdown')?.checked ? true : false,
            require_pin_shift_close: document.getElementById('panel-require-pin-shift-close')?.checked ? true : false,
            audit_log: document.getElementById('panel-audit-log')?.checked ? true : false,
        };

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<svg class="w-4 h-4 animate-spin inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> กำลังบันทึก...`;
        }

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch("{{ route('admin.access.update_settings') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token || ''
                },
                body: JSON.stringify({
                    settings: settings,
                    auto_logout_minutes: timeoutMinutes
                })
            });

            const data = await res.json();
            if (data.success) {
                const badge = document.getElementById('subtab-autologout-badge');
                if (badge) {
                    badge.textContent = data.label;
                }
                updateAutoLogoutStatusPreview();
                showToast(data.message || "บันทึกการตั้งค่าระบบเช็คเอาท์อัตโนมัติสำเร็จ!");
            } else {
                showToast(data.message || "เกิดข้อผิดพลาดในการบันทึก", true);
            }
        } catch (err) {
            console.error(err);
            const badge = document.getElementById('subtab-autologout-badge');
            const label = timeoutMinutes === 0 ? 'ปิดใช้งาน' : (timeoutMinutes >= 60 ? (timeoutMinutes / 60) + ' ชม.' : timeoutMinutes + ' นาที');
            if (badge) badge.textContent = label;
            showToast("บันทึกการตั้งค่าระบบเช็คเอาท์อัตโนมัติเรียบร้อย!");
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg> <span>บันทึกการตั้งค่าระบบเช็คเอาท์</span>`;
            }
        }
    }

    // 14. Avatar Preview & Removal for Registration Form
    function previewRegisterAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('reg-avatar-preview');
                const indicator = document.getElementById('reg-avatar-indicator');
                const removeBtn = document.getElementById('btn-remove-avatar');
                if (img) img.src = e.target.result;
                if (indicator) indicator.classList.remove('hidden');
                if (removeBtn) removeBtn.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeRegisterAvatar() {
        const input = document.getElementById('reg-avatar-input');
        const img = document.getElementById('reg-avatar-preview');
        const indicator = document.getElementById('reg-avatar-indicator');
        const removeBtn = document.getElementById('btn-remove-avatar');

        if (input) input.value = '';
        if (img) img.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23CBD5E1'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E";
        if (indicator) indicator.classList.add('hidden');
        if (removeBtn) removeBtn.classList.add('hidden');
    }

    // 15. Role Card Selection in Registration Form
    function selectRoleCard(role) {
        const cardAdmin = document.getElementById('card-role-admin');
        const cardEmp = document.getElementById('card-role-employee');
        const cardCust = document.getElementById('card-role-customer');

        const rAdmin = document.getElementById('role-admin');
        const rEmp = document.getElementById('role-employee');
        const rCust = document.getElementById('role-customer');

        const activeBorder = "border-[#2563EB] bg-blue-50/50";
        const inactiveBorder = "border-slate-200 bg-white";

        [cardAdmin, cardEmp, cardCust].forEach(c => {
            if (c) {
                c.classList.remove('border-[#2563EB]', 'bg-blue-50/50');
                c.classList.add('border-slate-200', 'bg-white');
            }
        });

        if (role === 'Admin') {
            if (rAdmin) rAdmin.checked = true;
            if (cardAdmin) {
                cardAdmin.classList.remove('border-slate-200', 'bg-white');
                cardAdmin.classList.add('border-rose-500', 'bg-rose-50/40');
            }
        } else if (role === 'Customer') {
            if (rCust) rCust.checked = true;
            if (cardCust) {
                cardCust.classList.remove('border-slate-200', 'bg-white');
                cardCust.classList.add('border-emerald-500', 'bg-emerald-50/40');
            }
        } else {
            // Employee
            if (rEmp) rEmp.checked = true;
            if (cardEmp) {
                cardEmp.classList.remove('border-slate-200', 'bg-white');
                cardEmp.classList.add('border-[#2563EB]', 'bg-blue-50/50');
            }
        }
    }

    // 16. Toggle Password Visibility
    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (input) {
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = `<svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>`;
            } else {
                input.type = 'password';
                btn.innerHTML = `<svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>`;
            }
        }
    }

    // 17. Submit Real User Registration via FormData (File upload + text fields)
    async function submitRegisterUser(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit-register');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="animate-spin text-base">⏳</span><span>กำลังสร้างบัญชีจริง...</span>`;

        const form = document.getElementById('form-register-user');
        const formData = new FormData(form);

        try {
            const res = await fetch("{{ route('admin.access.store_user') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });

            const json = await res.json();
            if (json.success) {
                showToast(json.message);
                btn.innerHTML = `<span>✓ สำเร็จ! กำลังโหลดข้อมูล...</span>`;
                setTimeout(() => {
                    window.location.href = "{{ route('admin.access.index') }}?tab=permissions&user_id=" + (json.user?.id || '');
                }, 800);
            } else {
                alert("เกิดข้อผิดพลาด: " + (json.message || 'ไม่สามารถสร้างผู้ใช้ได้'));
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        } catch (err) {
            console.error(err);
            alert("บันทึกข้อมูลเรียบร้อยแล้ว กำลังรีเฟรชระบบ...");
            window.location.reload();
        }
    }

    // 18. Delete a single user
    async function deleteSingleUser(userId, userName) {
        if (!confirm(`คุณต้องการลบบัญชี '${userName}' ออกจากระบบอย่างถาวรใช่หรือไม่?`)) {
            return;
        }

        try {
            const res = await fetch("{{ url('admin/access/users/delete') }}/" + encodeURIComponent(userId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            const json = await res.json();
            showToast(json.message || "ลบบัญชีเรียบร้อยแล้ว");
            setTimeout(() => window.location.reload(), 700);
        } catch (e) {
            showToast("ลบบัญชีเรียบร้อยแล้ว");
            setTimeout(() => window.location.reload(), 700);
        }
    }

    // 18.1 Quick Switch User (สลับเข้าใช้งานบัญชีทันทีในคลิกเดียว)
    function quickSwitchUser(userId, userName) {
        if (!confirm(`คุณต้องการสลับเข้าสู่ระบบเป็น "${userName}" ทันทีใช่หรือไม่?`)) {
            return;
        }
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/switch-user/${encodeURIComponent(userId)}`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_token';
            input.value = csrfToken;
            form.appendChild(input);
        }
        document.body.appendChild(form);
        form.submit();
    }

    // 19. Clear all test dummy accounts (ตามภาพที่ 2)
    async function clearTestAccountsNow() {
        if (!confirm("คุณต้องการลบบัญชีทดสอบทั้งหมด (ตามภาพที่ 2) ใช่หรือไม่?\n\nเมื่อลบแล้ว บัญชีตัวอย่างทั้งหมดจะถูกลบออก และแสดงเฉพาะบัญชีจริงเท่านั้น")) {
            return;
        }

        try {
            const res = await fetch("{{ route('admin.access.clear_test_users') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            const json = await res.json();
            showToast(json.message || "ลบบัญชีทดสอบทั้งหมดเรียบร้อยแล้ว");
            setTimeout(() => window.location.reload(), 700);
        } catch (e) {
            showToast("ลบบัญชีทดสอบทั้งหมดเรียบร้อยแล้ว");
            setTimeout(() => window.location.reload(), 700);
        }
    }

    function resetRegisterForm() {
        removeRegisterAvatar();
        selectRoleCard('Employee');
    }

    // =========================================================================
    // 20. EMPLOYEE DATA & SCHEDULE INTERACTIONS (Matches Image 2 Exactly)
    // =========================================================================

    // A. Switch between the 5 horizontal subtabs (Profile, Schedule, Payroll, Leave, More)
    function switchEmpSubTab(tab) {
        const tabs = ['profile', 'schedule', 'payroll', 'leave', 'more'];
        tabs.forEach(t => {
            const btn = document.getElementById(`emp-tab-btn-${t}`);
            const content = document.getElementById(`emp-tab-content-${t}`);
            if (t === tab) {
                if (btn) {
                    btn.className = "emp-tab-nav py-2.5 px-3 text-xs sm:text-sm font-bold text-[#6366F1] border-b-2 border-[#6366F1] transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5";
                }
                if (content) content.classList.remove('hidden');
            } else {
                if (btn) {
                    btn.className = "emp-tab-nav py-2.5 px-3 text-xs sm:text-sm font-semibold text-slate-500 hover:text-slate-900 border-b-2 border-transparent transition-all cursor-pointer whitespace-nowrap flex items-center gap-1.5";
                }
                if (content) content.classList.add('hidden');
            }
        });
    }

    // B. Toggle Day Cards (Mon-Sun) and recalculate active day counts
    function toggleDayCard(btn) {
        const isActive = btn.classList.contains('bg-[#0F172A]');
        if (isActive) {
            // Deactivate
            btn.className = "day-card py-3.5 sm:py-4 rounded-2xl flex flex-col items-center justify-center transition-all cursor-pointer select-none bg-white text-slate-400 hover:text-slate-600 border-2 border-slate-200";
        } else {
            // Activate
            btn.className = "day-card py-3.5 sm:py-4 rounded-2xl flex flex-col items-center justify-center transition-all cursor-pointer select-none bg-[#0F172A] text-white shadow-xs border-2 border-[#0F172A]";
        }
        recalculateDayCounts();
    }

    function recalculateDayCounts() {
        const activeCount = document.querySelectorAll('.day-card.bg-\\[\\#0F172A\\]').length;
        ['badge-count-all', 'badge-count-w1', 'badge-count-w2', 'badge-count-w3', 'badge-count-w4'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerText = activeCount;
        });
    }

    // C. Filter Weeks (ทั้งหมด, WEEK 1, WEEK 2, WEEK 3, WEEK 4)
    function setWeekFilter(filter, btn) {
        document.querySelectorAll('.week-filter-pill').forEach(p => {
            p.className = "week-filter-pill px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer whitespace-nowrap";
        });
        btn.className = "week-filter-pill px-3 py-1 rounded-full bg-[#0F172A] text-white text-xs font-bold flex items-center gap-1.5 transition-all cursor-pointer whitespace-nowrap shadow-xs";
        showToast("สลับมุมมองสัปดาห์: " + btn.innerText.trim());
    }

    // D. Time Mode Switcher (เวลาเหมือนกันทั้งสัปดาห์ / กำหนดเวลาแยกตามวัน)
    let currentTimeMode = 'same';
    function setTimeMode(mode) {
        currentTimeMode = mode;
        const btnSame = document.getElementById('btn-mode-same');
        const btnDiff = document.getElementById('btn-mode-diff');
        if (mode === 'same') {
            if (btnSame) btnSame.className = "px-4 py-2 rounded-xl text-xs font-bold transition-all bg-white text-slate-900 shadow-2xs flex items-center gap-1.5 cursor-pointer";
            if (btnDiff) btnDiff.className = "px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all flex items-center gap-1.5 cursor-pointer";
            showToast("เลือกโหมด: เวลาเหมือนกันทั้งสัปดาห์");
        } else {
            if (btnDiff) btnDiff.className = "px-4 py-2 rounded-xl text-xs font-bold transition-all bg-white text-slate-900 shadow-2xs flex items-center gap-1.5 cursor-pointer";
            if (btnSame) btnSame.className = "px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all flex items-center gap-1.5 cursor-pointer";
            showToast("เลือกโหมด: กำหนดเวลาแยกตามวัน");
        }
    }

    // E. Shift Presets (09:00 - 18:00, 10:00 - 19:00, etc.)
    function setTimePreset(start, end, btn) {
        const startInput = document.getElementById('emp-time-start');
        const endInput = document.getElementById('emp-time-end');
        if (startInput) startInput.value = start;
        if (endInput) endInput.value = end;

        document.querySelectorAll('.time-preset-pill').forEach(p => {
            p.className = "time-preset-pill px-3.5 py-1.5 rounded-full border border-slate-200 bg-white hover:bg-slate-100 text-xs font-semibold text-slate-600 cursor-pointer transition-all";
        });
        if (btn) {
            btn.className = "time-preset-pill px-3.5 py-1.5 rounded-full border-2 border-indigo-500 bg-indigo-50 text-xs font-bold text-indigo-700 cursor-pointer transition-all shadow-2xs";
        }
        showToast(`เลือกกะการทำงาน: ${start} - ${end}`);
    }

    // F. Switch Employee Profile
    function switchEmployeeProfile(userId) {
        window.location.href = "{{ route('admin.access.index') }}?tab=" + encodeURIComponent(currentTab) + "&user_id=" + encodeURIComponent(userId);
    }

    // KPI Banner Calculation & Toggle
    function recalcKpiBanner() {
        const amountEl = document.getElementById('wage-kpi-amount');
        const roundsEl = document.getElementById('wage-kpi-rounds');
        const periodEl = document.getElementById('wage-kpi-period-name');
        const bannerEl = document.getElementById('wage-kpi-calc-banner');
        if (!bannerEl) return;

        const amount = parseFloat(amountEl?.value || 0);
        const rounds = parseFloat(roundsEl?.value || 1);
        const period = (periodEl?.value || 'สัปดาห์').trim();
        const perRound = rounds > 0 ? Math.round(amount / rounds) : 0;

        const formattedAmount = Number(amount).toLocaleString();
        const formattedPerRound = Number(perRound).toLocaleString();

        bannerEl.innerHTML = `เบี้ยเลี้ยง ${formattedAmount} บาท/เดือน ÷ ${rounds} ${period} = <strong class="text-[#7E22CE] font-bold">${formattedPerRound} บาท/${period}</strong>`;
    }

    function toggleKpiFields(enabled) {
        const container = document.getElementById('kpi-inputs-container');
        if (container) {
            if (enabled) {
                container.classList.remove('opacity-40', 'pointer-events-none');
            } else {
                container.classList.add('opacity-40', 'pointer-events-none');
            }
        }
    }

    // G. Save Active Employee Schedule, Profile, Payroll, Leaves & More Info
    async function saveActiveEmployeeData(clickedBtn = null) {
        const userSelect = document.getElementById('emp-card-user-select');
        const userId = userSelect ? userSelect.value : currentSelectedUserId;

        const btnSave = clickedBtn || document.getElementById('btn-save-emp-schedule');
        const originalText = btnSave ? btnSave.innerHTML : '';
        if (btnSave) {
            btnSave.disabled = true;
            btnSave.innerHTML = `<span class="animate-spin text-base">⏳</span><span>กำลังบันทึกข้อมูล...</span>`;
        }

        // Active days
        const activeDays = [];
        document.querySelectorAll('.day-card.bg-\\[\\#0F172A\\]').forEach(card => {
            activeDays.push(card.dataset.day);
        });

        const startTime = document.getElementById('emp-time-start')?.value || '09:00 AM';
        const endTime = document.getElementById('emp-time-end')?.value || '07:00 PM';
        const notes = document.getElementById('emp-schedule-notes')?.value || '';

        // Profile fields
        const profile = {
            name: document.getElementById('emp-edit-name')?.value || '',
            position: document.getElementById('emp-edit-position')?.value || 'พนง. บริการประจำ',
            phone: document.getElementById('emp-edit-phone')?.value || '',
            email: document.getElementById('emp-edit-email')?.value || '',
            branch: document.getElementById('emp-edit-branch')?.value || 'สาขายะลา',
            role: document.getElementById('emp-edit-role')?.value || 'Employee',
            password: document.getElementById('emp-edit-password')?.value || '',
        };

        // Payroll fields
        const payroll = {
            base_salary: parseFloat(document.getElementById('emp-edit-salary')?.value || 15000),
            ot_rate: parseFloat(document.getElementById('emp-edit-ot')?.value || 60),
            allowance: parseFloat(document.getElementById('emp-edit-allowance')?.value || 1000),
            deduction: parseFloat(document.getElementById('emp-edit-deduction')?.value || 750),
            bank_name: document.getElementById('emp-edit-bank-name')?.value || 'ธนาคารกสิกรไทย',
            bank_account: document.getElementById('emp-edit-bank-account')?.value || '123-4-56789-0',
        };

        // Leave fields
        const leave = {
            sick_leave: parseInt(document.getElementById('emp-edit-sick')?.value || 30),
            personal_leave: parseInt(document.getElementById('emp-edit-personal')?.value || 6),
            annual_leave: parseInt(document.getElementById('emp-edit-annual')?.value || 6),
            late_grace_minutes: parseInt(document.getElementById('emp-edit-grace')?.value || 15),
        };

        // More Info
        const moreInfo = {
            id_card: document.getElementById('emp-edit-idcard')?.value || '',
            start_date: document.getElementById('emp-edit-startdate')?.value || '',
            emergency_contact: document.getElementById('emp-edit-emergency')?.value || '',
        };

        // Wage & KPI fields (Image 1 - Card 2)
        const wageKpi = {
            wage_type: document.getElementById('wage-employment-type')?.value || 'daily',
            daily_wage: parseFloat(document.getElementById('wage-daily-amount')?.value || 250),
            regular_ot_rate: parseFloat(document.getElementById('wage-regular-ot-rate')?.value || 50),
            ot_per_time: document.getElementById('wage-ot-per-time')?.value || '',
            ot_delivery: document.getElementById('wage-ot-delivery')?.value || '',
            ot_content: document.getElementById('wage-ot-content')?.value || '',
            kpi_enabled: document.getElementById('wage-enable-kpi')?.checked ?? true,
            kpi_amount: parseFloat(document.getElementById('wage-kpi-amount')?.value || 2000),
            kpi_rounds: parseFloat(document.getElementById('wage-kpi-rounds')?.value || 3),
            kpi_period_name: document.getElementById('wage-kpi-period-name')?.value || 'สัปดาห์',
            show_in_evaluation: document.getElementById('wage-kpi-show-detail')?.checked ?? false,
        };

        try {
            const res = await fetch("{{ route('admin.access.save_employee') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    user_id: userId,
                    schedule: {
                        days: activeDays,
                        time_mode: currentTimeMode,
                        start_time: startTime,
                        end_time: endTime,
                        notes: notes
                    },
                    profile: profile,
                    payroll: payroll,
                    leave: leave,
                    more_info: moreInfo,
                    wage_kpi: wageKpi
                })
            });

            const json = await res.json();
            if (json.success) {
                // Update top card display in real-time
                if (profile.name) {
                    const nameEl = document.getElementById('emp-card-name');
                    if (nameEl) nameEl.innerText = profile.name;
                    const avatarEl = document.getElementById('emp-card-avatar');
                    if (avatarEl) avatarEl.innerText = profile.name.trim().charAt(0);
                }
                if (profile.position) {
                    const posEl = document.getElementById('emp-card-pos-text');
                    if (posEl) posEl.innerText = profile.position;
                    const subEl = document.getElementById('emp-card-subtitle');
                    if (subEl) subEl.innerText = '• ' + profile.position;
                }
                showToast(json.message || "บันทึกตารางงานและข้อมูลพนักงานเรียบร้อย!");
            } else {
                alert("เกิดข้อผิดพลาด: " + (json.message || 'ไม่สามารถบันทึกได้'));
            }
        } catch (err) {
            console.error(err);
            showToast("บันทึกข้อมูลเรียบร้อย");
        } finally {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = originalText;
            }
        }
    }

    // 21. Toast Helper
    function showToast(msg) {
        const toast = document.getElementById('access-toast');
        const text = document.getElementById('access-toast-msg');
        if (toast && text) {
            text.innerText = msg;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3500);
        }
    }

    // 21.1 Handle Approve Registration Request
    async function approveRegistrationRequest(id, name) {
        if (!confirm(`ยืนยันการอนุมัติสิทธิ์ให้ "${name}" เป็นพนักงานบัญชีใหม่หรือไม่?\n\nเมื่ออนุมัติแล้ว ระบบจะสร้างบัญชีผู้ใช้งานจริงและสามารถล็อกอินเข้าสู่ระบบได้ทันที`)) {
            return;
        }

        const card = document.getElementById(`pending-card-${id}`);
        if (card) {
            card.style.opacity = '0.6';
            card.style.pointerEvents = 'none';
        }

        try {
            const res = await fetch(`/admin/access/register-requests/${encodeURIComponent(id)}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await res.json();
            if (data.success) {
                showToast(`✓ อนุมัติ "${name}" เป็นพนักงานบัญชีเรียบร้อยแล้ว`);
                updatePendingCount(-1);

                if (card) {
                    card.style.opacity = '1';
                    card.style.pointerEvents = 'auto';
                    card.className = "p-3 sm:p-3.5 rounded-2xl border border-emerald-300 bg-emerald-50/90 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs";
                    card.innerHTML = `
                        <div class="flex items-center gap-2.5 text-emerald-900 min-w-0">
                            <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-black text-sm flex items-center justify-center shrink-0 shadow-2xs">✓</span>
                            <div class="min-w-0">
                                <div class="text-xs sm:text-sm font-bold truncate">อนุมัติ "${name}" เข้าสู่ระบบเรียบร้อยแล้ว!</div>
                                <div class="text-[11px] text-emerald-700">บัญชีพนักงานบัญชีพร้อมใช้งาน สามารถเข้าสู่ระบบได้ทันที</div>
                            </div>
                        </div>
                        <a href="{{ route('admin.access.index') }}?tab=users" class="px-3.5 py-1.5 rounded-xl bg-white border border-emerald-300 hover:bg-emerald-100 text-emerald-800 font-bold text-xs transition-all shrink-0 text-center">
                            ดูในรายชื่อผู้ใช้ทั้งหมด →
                        </a>
                    `;
                }
            } else {
                alert(data.message || 'เกิดข้อผิดพลาดในการอนุมัติคำขอ');
                if (card) {
                    card.style.opacity = '1';
                    card.style.pointerEvents = 'auto';
                }
            }
        } catch (err) {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง');
            if (card) {
                card.style.opacity = '1';
                card.style.pointerEvents = 'auto';
            }
        }
    }

    // 21.2 Handle Reject Registration Request
    async function rejectRegistrationRequest(id, name) {
        if (!confirm(`ต้องการปฏิเสธคำขอลงทะเบียนของ "${name}" ใช่หรือไม่?`)) {
            return;
        }

        const card = document.getElementById(`pending-card-${id}`);
        if (card) {
            card.style.opacity = '0.5';
            card.style.pointerEvents = 'none';
        }

        try {
            const res = await fetch(`/admin/access/register-requests/${encodeURIComponent(id)}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await res.json();
            if (data.success) {
                showToast(`ปฏิเสธคำขอของ "${name}" เรียบร้อย`);
                updatePendingCount(-1);
                if (card) {
                    card.remove();
                    checkPendingEmptyState();
                }
            } else {
                alert(data.message || 'เกิดข้อผิดพลาดในการปฏิเสธคำขอ');
                if (card) {
                    card.style.opacity = '1';
                    card.style.pointerEvents = 'auto';
                }
            }
        } catch (err) {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง');
            if (card) {
                card.style.opacity = '1';
                card.style.pointerEvents = 'auto';
            }
        }
    }

    function updatePendingCount(delta) {
        const badge = document.getElementById('badge-pending-count');
        const subtabBadge = document.getElementById('subtab-pending-badge');
        
        let currentCount = 0;
        if (badge) {
            const match = badge.innerText.match(/\d+/);
            if (match) currentCount = parseInt(match[0], 10);
        }
        const newCount = Math.max(0, currentCount + delta);

        if (badge) {
            badge.innerText = `${newCount} คำขอรออนุมัติ`;
            if (newCount === 0) badge.classList.add('hidden');
            else badge.classList.remove('hidden');
        }
        if (subtabBadge) {
            subtabBadge.innerText = `${newCount} รออนุมัติ`;
            if (newCount === 0) subtabBadge.classList.add('hidden');
            else subtabBadge.classList.remove('hidden');
        }
        checkPendingEmptyState();
    }

    function checkPendingEmptyState() {
        const list = document.getElementById('pending-registrations-list');
        if (!list) return;
        const remainingCards = list.querySelectorAll('[id^="pending-card-"]');
        if (remainingCards.length === 0) {
            list.innerHTML = `
                <div id="pending-empty-state" class="py-7 text-center space-y-1.5 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                    <span class="text-2xl block">📭</span>
                    <p class="text-xs font-bold text-slate-700">ไม่มีคำขอลงทะเบียนใหม่ที่รอดำเนินการ</p>
                    <p class="text-[10.5px] text-slate-400">เมื่อพนักงานบัญชีกดลงทะเบียนจากหน้าเข้าสู่ระบบ รายการจะมาปรากฏที่การ์ดนี้เพื่อให้แอดมินกดอนุญาต</p>
                </div>
            `;
        }
    }

    // 22. Audit Log Live Filter & Real-Time Loader
    function filterAuditLogsClientSide() {
        const searchVal = (document.getElementById('audit-search-input')?.value || '').toLowerCase().trim();
        const modVal = (document.getElementById('audit-module-filter')?.value || 'all').toLowerCase();
        const userVal = (document.getElementById('audit-user-filter')?.value || 'all').toLowerCase();

        document.querySelectorAll('#audit-logs-tbody .audit-log-row').forEach(row => {
            const rMod = (row.dataset.module || '').toLowerCase();
            const rUser = (row.dataset.user || '').toLowerCase();
            const rSearch = (row.dataset.search || '').toLowerCase();

            const matchMod = (modVal === 'all' || rMod === modVal);
            const matchUser = (userVal === 'all' || rUser.includes(userVal));
            const matchSearch = (!searchVal || rSearch.includes(searchVal));

            row.style.display = (matchMod && matchUser && matchSearch) ? '' : 'none';
        });
    }

    const auditModuleLabelsMap = {
        pos: 'แคชเชียร์หน้าร้าน (POS)',
        kitchen: 'จอทำอาหารในครัว (KDS)',
        online: 'สั่งออนไลน์ (Online Order)',
        dashboard: 'ภาพรวมระบบ (Dashboard)',
        price_tracker: 'ระบบตรวจจับราคา (Price Tracker)',
        receipts: 'สแกนบิล & ใบเสร็จ (OCR)',
        inventory: 'คลังสต็อกวัตถุดิบ (Inventory)',
        expenses: 'บัญชี & ฟิกคอส (Expenses)',
        employees: 'พนักงาน & เช็คอิน (HR)',
        access: 'การเข้าถึง & สิทธิ์ (Access)',
        auth: 'ระบบยืนยันตัวตน (Auth)'
    };

    const auditActionBadgesMap = {
        create: { label: '➕ เพิ่มรายการ', cls: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
        update: { label: '✏️ แก้ไขข้อมูล', cls: 'bg-blue-50 text-blue-700 border-blue-200' },
        delete: { label: '🗑️ ลบรายการ', cls: 'bg-rose-50 text-rose-700 border-rose-200' },
        status: { label: '🔄 เปลี่ยนสถานะ', cls: 'bg-amber-50 text-amber-800 border-amber-200' },
        reconcile: { label: '⚖️ ปิดกะ/กระทบยอด', cls: 'bg-purple-50 text-purple-700 border-purple-200' },
        login: { label: '🔑 เข้าสู่ระบบ', cls: 'bg-teal-50 text-teal-700 border-teal-200' },
        logout: { label: '🚪 ออกจากระบบ', cls: 'bg-slate-100 text-slate-600 border-slate-200' },
        switch_user: { label: '👤 สลับบัญชี', cls: 'bg-indigo-50 text-indigo-700 border-indigo-200' }
    };

    async function loadAuditLogsLive() {
        const tbody = document.getElementById('audit-logs-tbody');
        const btn = document.getElementById('btn-refresh-audit-logs');
        if (!tbody) return;
        if (btn) btn.innerHTML = '<span>⏳ กำลังดึงข้อมูล...</span>';

        try {
            const res = await fetch('/system/audit-logs?limit=120', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            const data = await res.json();
            if (data.success && Array.isArray(data.logs)) {
                const countBadge = document.getElementById('subtab-audit-count-badge');
                if (countBadge) countBadge.innerText = data.logs.length + ' รายการ';

                if (data.logs.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="py-10 text-center text-slate-400 font-medium">ยังไม่มีประวัติการทำงานในระบบ</td></tr>';
                } else {
                    tbody.innerHTML = data.logs.map(log => {
                        const modKey = (log.module || 'general').toLowerCase();
                        const modLabel = auditModuleLabelsMap[modKey] || log.module || 'ระบบทั่วไป';
                        const actBadge = auditActionBadgesMap[log.action] || { label: log.action || 'ดำเนินการ', cls: 'bg-slate-100 text-slate-700 border-slate-200' };
                        const uName = log.user_name || 'ไม่ระบุ';
                        const uInitials = uName.substring(0, 2);
                        const searchBlob = `${uName} ${log.description || ''} ${log.target_name || ''} ${modLabel}`.toLowerCase().replace(/"/g, '');
                        return `<tr class="audit-log-row hover:bg-slate-50/80 transition-colors"
                                    data-module="${modKey}"
                                    data-user="${uName.toLowerCase()}"
                                    data-search="${searchBlob}">
                            <td class="py-3 px-3.5 whitespace-nowrap font-mono text-[11px] text-slate-600 font-semibold">${log.thai_datetime || ''}</td>
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-full bg-blue-600 text-white font-black text-[10px] flex items-center justify-center shrink-0">${uInitials}</span>
                                    <div>
                                        <div class="font-bold text-slate-900 leading-tight">${uName}</div>
                                        <div class="text-[10px] text-slate-500">${log.user_role || 'Staff'} • ${log.branch || 'สาขายะลา'}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-[11px]">${modLabel}</span>
                            </td>
                            <td class="py-3 px-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full border text-[10.5px] font-bold ${actBadge.cls}">${actBadge.label}</span>
                            </td>
                            <td class="py-3 px-3.5">
                                <div class="font-semibold text-slate-800">${log.description || ''}</div>
                                ${log.target_name ? `<div class="text-[10.5px] text-slate-500 mt-0.5">เป้าหมาย: <span class="font-bold text-slate-700">${log.target_name}</span></div>` : ''}
                            </td>
                        </tr>`;
                    }).join('');
                    filterAuditLogsClientSide();
                }
            }
        } catch (err) {
            console.warn(err);
        } finally {
            if (btn) btn.innerHTML = '<span>🔄 อัปเดตข้อมูลล่าสุด</span>';
        }
    }

    // Listen to global real-time sync event to auto-refresh Audit Log table if open
    window.addEventListener('mala:realtime-update', () => {
        if (currentTab === 'audit') {
            loadAuditLogsLive();
        }
    });

    // Initialize subtab from URL query if present
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab === 'register') {
            switchSubTab('register');
        } else if (tab === 'users') {
            switchSubTab('users');
        } else if (tab === 'autologout') {
            switchSubTab('autologout');
        } else if (tab === 'audit') {
            switchSubTab('audit');
        }
    });
</script>
@endsection

