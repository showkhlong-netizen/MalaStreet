<!DOCTYPE html>
<html lang="th" class="min-h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ระบบ POS ร้านหม่าล่าสตรีท - Mala Street' }}</title>
    
    <!-- Google Fonts: Prompt 400, 500, 600, 700 (Mala Street 4-Weights System) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('fonts/mala-street-font.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col text-slate-800 antialiased selection:bg-rose-500 selection:text-white bg-slate-50">
    <!-- Main Top Navigation (Sleek, Prominent Monochrome Icons, Text Underneath) -->
    <header class="bg-slate-950 text-white shadow-lg sticky top-0 z-40 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand / Logo -->
                <div class="flex items-center space-x-3 shrink-0">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 group">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-rose-600 to-amber-500 flex items-center justify-center shadow-lg shadow-rose-900/40 group-hover:scale-105 transition-transform text-white">
                            <!-- Chili Pepper SVG -->
                            <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                                <path d="M12 2C11.5 2 11 2.2 10.6 2.6L9.2 4H7C5.3 4 4 5.3 4 7C4 8.7 5.3 10 7 10V11C7 16 10 21 16 22C16.6 22 17 21.6 17 21C17 20.4 16.6 20 16 20C11.8 19.2 9.2 15.6 9 11.7C10.1 11.9 11.2 12 12 12C16.4 12 20 8.4 20 4C20 3.4 19.8 2.8 19.4 2.4C19 2 18.4 1.8 17.8 1.9C16.8 2.1 15.6 2.7 14.5 3.5C13.8 2.6 12.9 2 12 2Z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="font-extrabold text-base text-white tracking-wide block leading-tight">หม่าล่าสตรีท</span>
                            <span class="text-[11px] text-rose-400 font-medium block">Mala Street POS & Cloud</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Tabs: Icons Prominent, Text Underneath -->
                <nav class="hidden lg:flex items-center space-x-1.5 xl:space-x-2.5">
                    <!-- 1. แคชเชียร์หน้าร้าน -->
                    <a href="{{ route('pos.index') }}" 
                       class="group flex flex-col items-center justify-center px-3.5 py-1.5 rounded-2xl transition-all duration-150 {{ request()->routeIs('pos.index') ? 'bg-rose-600 text-white shadow-md shadow-rose-900/40 ring-1 ring-rose-400/30' : 'text-slate-400 hover:text-white hover:bg-slate-900/90' }}">
                        <svg class="w-6 h-6 transition-transform group-hover:scale-110 {{ request()->routeIs('pos.index') ? 'text-white' : 'text-slate-300 group-hover:text-white' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="text-[10.5px] font-medium mt-1 leading-none tracking-tight whitespace-nowrap">แคชเชียร์หน้าร้าน</span>
                    </a>

                    <!-- 2. จอครัว (KDS) -->
                    <a href="{{ route('pos.kitchen') }}" 
                       class="group flex flex-col items-center justify-center px-3.5 py-1.5 rounded-2xl transition-all duration-150 {{ request()->routeIs('pos.kitchen') ? 'bg-rose-600 text-white shadow-md shadow-rose-900/40 ring-1 ring-rose-400/30' : 'text-slate-400 hover:text-white hover:bg-slate-900/90' }}">
                        <svg class="w-6 h-6 transition-transform group-hover:scale-110 {{ request()->routeIs('pos.kitchen') ? 'text-white' : 'text-slate-300 group-hover:text-white' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
                        </svg>
                        <span class="text-[10.5px] font-medium mt-1 leading-none tracking-tight whitespace-nowrap">จอครัว (KDS)</span>
                    </a>

                    <!-- 3. สั่งออนไลน์ (Grab-Style) -->
                    <a href="{{ route('online.index') }}" target="_blank" 
                       class="group flex flex-col items-center justify-center px-3.5 py-1.5 rounded-2xl transition-all duration-150 text-amber-300 hover:text-white hover:bg-slate-900/90 border border-amber-500/30">
                        <svg class="w-6 h-6 text-amber-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <div class="flex items-center space-x-1 mt-1 leading-none">
                            <span class="text-[10.5px] font-medium tracking-tight whitespace-nowrap">สั่งออนไลน์</span>
                            <span class="text-[8.5px] bg-amber-500 text-slate-950 px-1 py-0.2 rounded font-black tracking-tighter">พรีวิว</span>
                        </div>
                    </a>

                    <!-- 3.1 แชทลูกค้า (Shopee-Style Customer Chat) -->
                    <a href="{{ route('online.chat') }}" target="_blank" 
                       class="group flex flex-col items-center justify-center px-3.5 py-1.5 rounded-2xl transition-all duration-150 {{ request()->routeIs('online.chat*') ? 'bg-rose-600 text-white shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-900/90 border border-slate-700/60' }}"
                       title="เปิดหน้าแชทลูกค้า (Mobile View)">
                        <div class="relative">
                            <svg class="w-6 h-6 text-sky-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-emerald-400 rounded-full border-2 border-slate-950"></span>
                        </div>
                        <div class="flex items-center space-x-1 mt-1 leading-none">
                            <span class="text-[10.5px] font-medium tracking-tight whitespace-nowrap">แชทลูกค้า</span>
                            <span class="text-[8.5px] bg-sky-500 text-slate-950 px-1 py-0.2 rounded font-black tracking-tighter">Live</span>
                        </div>
                    </a>

                    <!-- 3.2 แต้มสะสม & รีวิว (Loyalty Points: ครบ 5 ฟรี 1 ไม้) -->
                    <a href="{{ route('online.rewards') }}" target="_blank" 
                       class="group flex flex-col items-center justify-center px-3.5 py-1.5 rounded-2xl transition-all duration-150 {{ request()->routeIs('online.rewards*') ? 'bg-rose-600 text-white shadow-md' : 'text-slate-300 hover:text-white hover:bg-slate-900/90 border border-slate-700/60' }}"
                       title="เปิดหน้าเช็คแต้มสะสม (Mobile View)">
                        <svg class="w-6 h-6 text-rose-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                        <div class="flex items-center space-x-1 mt-1 leading-none">
                            <span class="text-[10.5px] font-medium tracking-tight whitespace-nowrap">แต้มสะสม</span>
                            <span class="text-[8.5px] bg-rose-500 text-white px-1 py-0.2 rounded font-black tracking-tighter">5=ฟรี1</span>
                        </div>
                    </a>

                    <div class="h-8 w-px bg-slate-800/80 mx-1"></div>

                    <!-- 4. ภาพรวมร้าน (มีปุ่ม GO LIVE และ ฟิกคอส & กำไร ด้านใน) -->
                    <a href="{{ route('admin.dashboard') }}" 
                       class="group flex flex-col items-center justify-center px-3.5 py-1.5 rounded-2xl transition-all duration-150 {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.expenses.*') ? 'bg-slate-900 text-amber-400 border border-amber-500/40 shadow-xs' : 'text-slate-400 hover:text-white hover:bg-slate-900/90' }}">
                        <svg class="w-6 h-6 transition-transform group-hover:scale-110 {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.expenses.*') ? 'text-amber-400' : 'text-slate-300 group-hover:text-white' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z" />
                        </svg>
                        <span class="text-[10.5px] font-medium mt-1 leading-none tracking-tight whitespace-nowrap">ภาพรวมร้าน</span>
                    </a>

                    <!-- 5. ตรวจจับราคา (มีปุ่ม แสกนใบเสร็จเพิ่ม & สต็อกไม้คงเหลือ ด้านใน) -->
                    <a href="{{ route('admin.receipts.price_tracker') }}" 
                       class="group flex flex-col items-center justify-center px-3.5 py-1.5 rounded-2xl transition-all duration-150 {{ request()->routeIs('admin.receipts.*') || request()->routeIs('admin.inventory.*') ? 'bg-slate-900 text-amber-400 border border-amber-500/40 shadow-xs' : 'text-slate-400 hover:text-white hover:bg-slate-900/90' }}">
                        <svg class="w-6 h-6 transition-transform group-hover:scale-110 {{ request()->routeIs('admin.receipts.*') || request()->routeIs('admin.inventory.*') ? 'text-amber-400' : 'text-slate-300 group-hover:text-white' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <span class="text-[10.5px] font-medium mt-1 leading-none tracking-tight whitespace-nowrap">ตรวจจับราคา</span>
                    </a>

                    <!-- 9. พนักงาน (สแกนหน้าเข้างาน & สรุปเงินเดือน) -->
                    <a href="{{ route('admin.employees.index') }}" 
                       class="group flex flex-col items-center justify-center px-3.5 py-1.5 rounded-2xl transition-all duration-150 {{ request()->routeIs('admin.employees.*') ? 'bg-slate-900 text-rose-400 border border-rose-500/40 shadow-xs' : 'text-slate-400 hover:text-white hover:bg-slate-900/90' }}">
                        <svg class="w-6 h-6 transition-transform group-hover:scale-110 {{ request()->routeIs('admin.employees.*') ? 'text-rose-400' : 'text-slate-300 group-hover:text-white' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="text-[10.5px] font-medium mt-1 leading-none tracking-tight whitespace-nowrap">พนักงาน</span>
                    </a>

                    <!-- 10. การเข้าถึง (จัดการพนักงาน แอดมิน และสิทธิ์การใช้งาน) -->
                    <a href="{{ route('admin.access.index') }}" 
                       class="group flex flex-col items-center justify-center px-3.5 py-1.5 rounded-2xl transition-all duration-150 {{ request()->routeIs('admin.access.*') ? 'bg-slate-900 text-blue-400 border border-blue-500/40 shadow-xs' : 'text-slate-400 hover:text-white hover:bg-slate-900/90' }}">
                        <svg class="w-6 h-6 transition-transform group-hover:scale-110 {{ request()->routeIs('admin.access.*') ? 'text-blue-400' : 'text-slate-300 group-hover:text-white' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span class="text-[10.5px] font-medium mt-1 leading-none tracking-tight whitespace-nowrap">การเข้าถึง</span>
                    </a>
                </nav>

                <!-- User Profile Pill & Actions (Replaces static spot from Image 2) -->
                @if(!empty($currentUser))
                    <div class="relative shrink-0" id="user-nav-dropdown-wrapper">
                        <button type="button" 
                                onclick="toggleUserDropdown()" 
                                class="flex items-center gap-2.5 py-1 px-2.5 sm:px-3 rounded-2xl bg-slate-900/95 hover:bg-slate-800 active:scale-95 border border-slate-700/80 text-white transition-all cursor-pointer shadow-xs group">
                            
                            <!-- Profile Picture or Initials with Online Green Indicator -->
                            <div class="relative shrink-0">
                                @if(!empty($currentUser['avatar']))
                                    <img src="{{ $currentUser['avatar'] }}" 
                                         alt="{{ $currentUser['name'] }}" 
                                         class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover ring-2 ring-emerald-400 shadow-xs">
                                @else
                                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $currentUser['color'] ?? 'bg-rose-600' }} text-white font-bold text-xs flex items-center justify-center ring-2 ring-emerald-400 shadow-xs">
                                        {{ $currentUser['initials'] ?? 'AD' }}
                                    </div>
                                @endif
                                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-400 ring-2 ring-slate-950"></span>
                            </div>

                            <!-- Name & Role / Branch -->
                            <div class="text-left hidden sm:block">
                                <div class="text-xs font-bold text-white group-hover:text-amber-300 transition-colors flex items-center gap-1.5 leading-tight">
                                    <span class="truncate max-w-[130px]">{{ $currentUser['name'] }}</span>
                                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-white transition-transform duration-200" id="user-dropdown-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                                <div class="text-[10px] text-emerald-400 font-semibold flex items-center gap-1">
                                    <span>{{ $currentUser['role_label'] ?? $currentUser['role'] ?? 'ผู้ดูแล' }}</span>
                                    <span class="text-slate-600">•</span>
                                    <span class="text-slate-400 truncate max-w-[80px]">{{ $currentUser['branch'] ?? 'ทุกสาขา' }}</span>
                                </div>
                            </div>

                            <svg class="w-3.5 h-3.5 text-slate-400 sm:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="user-nav-dropdown-menu" class="hidden absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-2xl border border-slate-200/90 py-2 z-50 animate-in fade-in zoom-in-95 duration-150 text-left">
                            <!-- Dropdown User Header -->
                            <div class="px-4 py-3 border-b border-slate-100 flex items-center gap-3">
                                @if(!empty($currentUser['avatar']))
                                    <img src="{{ $currentUser['avatar'] }}" alt="{{ $currentUser['name'] }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-emerald-400 shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-full {{ $currentUser['color'] ?? 'bg-rose-600' }} text-white font-bold text-sm flex items-center justify-center shrink-0">
                                        {{ $currentUser['initials'] ?? 'AD' }}
                                    </div>
                                @endif
                                <div class="overflow-hidden">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ $currentUser['name'] }}</p>
                                    <p class="text-[11px] text-slate-500 truncate">{{ $currentUser['email'] }}</p>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-bold mt-1 border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $currentUser['role_label'] ?? $currentUser['role'] ?? 'ผู้ดูแล' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Menu Links -->
                            <div class="p-1 space-y-0.5 text-xs font-semibold text-slate-700">
                                <a href="{{ route('admin.access.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-100 transition-colors group">
                                    <svg class="w-4 h-4 text-slate-500 group-hover:text-slate-900 transition-colors shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <span>การเข้าถึง &amp; สิทธิ์การใช้งาน</span>
                                </a>
                                <a href="{{ route('admin.employees.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-100 transition-colors group">
                                    <svg class="w-4 h-4 text-slate-500 group-hover:text-slate-900 transition-colors shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span>จัดการข้อมูลพนักงาน</span>
                                </a>
                                <a href="{{ route('admin.access.index', ['tab' => 'autologout']) }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-100 transition-colors group">
                                    <svg class="w-4 h-4 text-slate-500 group-hover:text-slate-900 transition-colors shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="9" />
                                        <path stroke-linecap="round" d="M12 7v5l3 2" />
                                    </svg>
                                    <span>ระบบเช็คเอาท์อัตโนมัติ</span>
                                </a>
                            </div>

                            <!-- Logout Action -->
                            <div class="pt-1 border-t border-slate-100 p-1">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50 font-bold text-xs transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <span>ออกจากระบบ (Logout)</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Guest Mode: Show Login Button -->
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-gradient-to-r from-red-600 to-rose-700 hover:from-red-700 hover:to-rose-800 text-white shadow-md shadow-red-900/20 transition-all cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <span>เข้าสู่ระบบ</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Mobile sub-navigation bar -->
        <div class="lg:hidden flex overflow-x-auto py-2 px-3 space-x-2 border-t border-slate-800 bg-slate-950 text-xs scrollbar-none">
            <a href="{{ route('pos.index') }}" class="shrink-0 px-3 py-1.5 rounded-xl {{ request()->routeIs('pos.index') ? 'bg-rose-600 text-white font-bold' : 'text-slate-400' }}">แคชเชียร์</a>
            <a href="{{ route('pos.kitchen') }}" class="shrink-0 px-3 py-1.5 rounded-xl {{ request()->routeIs('pos.kitchen') ? 'bg-rose-600 text-white font-bold' : 'text-slate-400' }}">จอครัว</a>
            <a href="{{ route('online.index') }}" class="shrink-0 px-3 py-1.5 rounded-xl text-amber-400 border border-amber-500/30">สั่งออนไลน์</a>
            <a href="{{ route('admin.dashboard') }}" class="shrink-0 px-3 py-1.5 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400' }}">ภาพรวม</a>
            <a href="{{ route('admin.receipts.price_tracker') }}" class="shrink-0 px-3 py-1.5 rounded-xl {{ request()->routeIs('admin.receipts.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400' }}">ตรวจจับราคา</a>
            <a href="{{ route('admin.inventory.index') }}" class="shrink-0 px-3 py-1.5 rounded-xl {{ request()->routeIs('admin.inventory.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400' }}">สต็อกไม้</a>
            <a href="{{ route('admin.employees.index') }}" class="shrink-0 px-3 py-1.5 rounded-xl {{ request()->routeIs('admin.employees.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-400' }}">พนักงาน</a>
            <a href="{{ route('admin.access.index') }}" class="shrink-0 px-3 py-1.5 rounded-xl {{ request()->routeIs('admin.access.*') ? 'bg-blue-600 text-white font-bold' : 'text-slate-400' }}">การเข้าถึง</a>
        </div>
    </header>

    <!-- Flash message alerts -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-sm font-bold">&times;</button>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-xs">
                <div class="flex items-center space-x-2 mb-1">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-sm font-bold">กรุณาตรวจสอบข้อมูล:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5 ml-6">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Content slot -->
    <main class="flex-1 flex flex-col">
        @yield('content')
    </main>

    <footer class="bg-slate-950 border-t border-slate-900 text-slate-400 text-xs py-4 text-center">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-2">
            <div>
                © {{ date('Y') }} ร้านหม่าล่าสตรีท (Mala Street POS). ระบบย่าง ทา/จุ่ม ต้มน้ำซุป 3 สไตล์ สแกนบิลคำนวณต้นทุนต่อไม้
            </div>
            <div class="flex items-center space-x-3 text-slate-400">
                <span>PHP 8.3 & Laravel 13</span>
                <span>•</span>
                <span>Tailwind CSS v4</span>
                <span>•</span>
                <span>PostgreSQL (Neon)</span>
            </div>
        </div>
    </footer>

    <!-- Auto-Logout Inactivity Warning Modal -->
    <div id="modal-idle-countdown" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 text-center space-y-4 shadow-2xl border border-slate-200 animate-in fade-in zoom-in duration-200">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center mx-auto border border-amber-200 shadow-2xs">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900">แจ้งเตือนระบบตัดการทำงาน</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    ไม่มีการเคลื่อนไหวของหน้าจอ ระบบจะทำการออกจากระบบอัตโนมัติในอีก
                </p>
                <div class="mt-3">
                    <span id="idle-countdown-seconds" class="inline-block px-4 py-1.5 rounded-2xl bg-amber-100 text-amber-900 font-black text-2xl border border-amber-300">
                        30
                    </span>
                    <span class="text-xs text-slate-500 font-bold ml-1">วินาที</span>
                </div>
            </div>
            <div class="flex items-center gap-2 pt-2">
                <button type="button" 
                        onclick="resetUserInactivityTimer()" 
                        class="w-full py-2.5 rounded-xl bg-gradient-to-r from-red-600 via-rose-600 to-amber-600 hover:from-red-700 hover:to-amber-700 text-white font-bold text-xs shadow-md shadow-red-500/20 cursor-pointer transition-all active:scale-95">
                    ใช้งานต่อ (ต่อเวลา)
                </button>
            </div>
        </div>
    </div>

    <!-- Global Layout Scripts (User Dropdown & Auto-Logout Inactivity Sync) -->
    <script>
        // 1. User Nav Dropdown Toggle
        function toggleUserDropdown() {
            const menu = document.getElementById('user-nav-dropdown-menu');
            const arrow = document.getElementById('user-dropdown-arrow');
            if (menu) {
                menu.classList.toggle('hidden');
                if (arrow) {
                    arrow.classList.toggle('rotate-180');
                }
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('user-nav-dropdown-wrapper');
            const menu = document.getElementById('user-nav-dropdown-menu');
            const arrow = document.getElementById('user-dropdown-arrow');
            if (wrapper && menu && !wrapper.contains(e.target)) {
                menu.classList.add('hidden');
                if (arrow) arrow.classList.remove('rotate-180');
            }
        });

        // 2. Global Auto-Logout / Inactivity System (Synced with Access Control Settings)
        (function initAutoLogoutSystem() {
            @php
                $isLoggedIn = !empty($currentUser);
                $timeoutMinutes = (int)($globalAutoLogoutSettings['timeout_minutes'] ?? 15);
                $timeoutAction = $globalAutoLogoutSettings['timeout_action'] ?? 'full_logout';
                $exemptKitchen = !empty($globalAutoLogoutSettings['exempt_kitchen']);
                $exemptCustomer = !empty($globalAutoLogoutSettings['exempt_customer']);
                $lockOnBlur = !empty($globalAutoLogoutSettings['lock_on_blur']);
                $warningCountdown = !empty($globalAutoLogoutSettings['warning_countdown']);
                
                $isKitchen = request()->routeIs('pos.kitchen');
                $isCustomer = request()->routeIs('online.*');
            @endphp

            const config = {
                isLoggedIn: {{ $isLoggedIn ? 'true' : 'false' }},
                timeoutMinutes: {{ $timeoutMinutes }},
                timeoutAction: "{{ $timeoutAction }}",
                exemptKitchen: {{ $exemptKitchen ? 'true' : 'false' }},
                exemptCustomer: {{ $exemptCustomer ? 'true' : 'false' }},
                lockOnBlur: {{ $lockOnBlur ? 'true' : 'false' }},
                warningCountdown: {{ $warningCountdown ? 'true' : 'false' }},
                isKitchen: {{ $isKitchen ? 'true' : 'false' }},
                isCustomer: {{ $isCustomer ? 'true' : 'false' }},
                logoutUrl: "{{ route('logout', ['reason' => 'timeout']) }}",
                loginUrl: "{{ route('login', ['reason' => 'timeout']) }}"
            };

            // If not logged in, or timeout is 0 (disabled), or route is exempt -> skip
            if (!config.isLoggedIn || config.timeoutMinutes <= 0) return;
            if (config.isKitchen && config.exemptKitchen) return;
            if (config.isCustomer && config.exemptCustomer) return;

            let totalMs = config.timeoutMinutes * 60 * 1000;
            let warningMs = 30 * 1000; // 30 seconds warning
            let lastActivity = Date.now();
            let isWarningShowing = false;

            function recordActivity() {
                lastActivity = Date.now();
                if (isWarningShowing) {
                    hideWarningModal();
                }
            }

            window.resetUserInactivityTimer = function() {
                recordActivity();
            };

            const activityEvents = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
            activityEvents.forEach(evt => {
                window.addEventListener(evt, recordActivity, { passive: true });
            });

            function showWarningModal(secondsLeft) {
                const modal = document.getElementById('modal-idle-countdown');
                const secSpan = document.getElementById('idle-countdown-seconds');
                if (modal && secSpan) {
                    secSpan.textContent = Math.max(1, Math.round(secondsLeft));
                    modal.classList.remove('hidden');
                    isWarningShowing = true;
                }
            }

            function hideWarningModal() {
                const modal = document.getElementById('modal-idle-countdown');
                if (modal) {
                    modal.classList.add('hidden');
                    isWarningShowing = false;
                }
            }

            function triggerTimeoutAction() {
                if (config.timeoutAction === 'full_logout' || config.timeoutAction === 'lock_screen') {
                    window.location.href = config.logoutUrl;
                } else if (config.timeoutAction === 'quick_pin') {
                    window.location.href = "{{ route('admin.access.index', ['tab' => 'users']) }}";
                } else {
                    window.location.href = config.logoutUrl;
                }
            }

            // Lock on window blur if enabled
            if (config.lockOnBlur) {
                window.addEventListener('blur', function() {
                    triggerTimeoutAction();
                });
            }

            // Check loop every 1000ms
            setInterval(function() {
                const elapsed = Date.now() - lastActivity;
                const remaining = totalMs - elapsed;

                if (remaining <= 0) {
                    triggerTimeoutAction();
                } else if (remaining <= warningMs && config.warningCountdown) {
                    showWarningModal(remaining / 1000);
                } else {
                    if (isWarningShowing) {
                        hideWarningModal();
                    }
                }
            }, 1000);
        })();

        // Global Fetch Interceptor for 401 Unauthenticated / Session Expiry
        (function() {
            if (typeof window.fetch !== 'function') return;
            const originalFetch = window.fetch;
            window.fetch = async function(resource, init) {
                init = init || {};
                if (!init.credentials) {
                    init.credentials = 'same-origin';
                }
                try {
                    const response = await originalFetch.call(this, resource, init);
                    if (response && response.status === 401) {
                        const path = window.location.pathname;
                        if (!path.startsWith('/order') && !path.startsWith('/login')) {
                            console.warn('Session expired or unauthenticated. Redirecting to login...');
                            alert('เซสชันการเข้าสู่ระบบหมดอายุ กรุณาเข้าสู่ระบบใหม่อีกครั้ง');
                            window.location.href = "{{ route('login') }}?redirect=" + encodeURIComponent(window.location.pathname + window.location.search);
                        }
                    }
                    return response;
                } catch (err) {
                    throw err;
                }
            };
        })();
    </script>

    @stack('scripts')
</body>
</html>
