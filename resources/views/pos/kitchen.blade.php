@extends('layouts.app', ['title' => 'จอครัว (KDS) - ร้านหม่าล่าสตรีท'])

@section('content')
<div class="flex-1 bg-slate-100/70 text-slate-800 p-4 sm:p-6 lg:p-8 overflow-y-auto">
    <div class="max-w-7xl mx-auto space-y-6">
        
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
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs w-full sm:w-auto justify-between sm:justify-end">
                <div class="px-3.5 py-2 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700 font-medium flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>รอปรุง: <strong class="text-slate-900 font-black text-sm">{{ $activeOrders->count() }}</strong> บิล</span>
                </div>
                <div class="px-3.5 py-2 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700 font-medium flex items-center space-x-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>เสร็จวันนี้: <strong class="text-slate-900 font-black text-sm">{{ $completedCount }}</strong> บิล</span>
                </div>
                <button onclick="window.location.reload()" 
                    class="py-2 px-3.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition-all shadow-xs flex items-center space-x-1.5 cursor-pointer active:scale-95" title="รีเฟรชออเดอร์">
                    <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>รีเฟรช</span>
                </button>

                <!-- แถบหน้าต่างแชทลูกค้า (ฝั่งแอดมิน) - ตั้งฝั่งขวาสุดของแถบหน้าจอครัว -->
                <a href="{{ route('kitchen.chat') }}" 
                    class="py-2 px-4 rounded-2xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white font-black transition-all shadow-md shadow-rose-950/20 flex items-center space-x-2 shrink-0 group active:scale-95 border border-rose-400/40" 
                    title="เปิดหน้าต่างแชทลูกค้า (ฝั่งแอดมิน/จอครัว) จับคู่ IP และชื่อออร์เดอร์">
                    <div class="relative flex items-center justify-center">
                        <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-emerald-400 border border-white animate-pulse"></span>
                    </div>
                    <span>แชทลูกค้า (แอดมิน)</span>
                    @if(!empty($unreadChatCount) && $unreadChatCount > 0)
                        <span class="text-[10px] bg-amber-400 text-slate-950 font-black px-1.5 py-0.2 rounded-full animate-bounce shadow-2xs">
                            {{ $unreadChatCount }}
                        </span>
                    @endif
                </a>
            </div>
        </div>

        <!-- 2. Active Orders Grid (Light Theme, Luxury Modern Cards) -->
        @if($activeOrders->isEmpty())
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
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($activeOrders as $order)
                    @php
                        $isOnline = $order->order_type === 'online';
                        $statusBorder = match($order->status) {
                            'pending', 'confirmed' => 'border-t-4 border-t-amber-500',
                            'cooking' => 'border-t-4 border-t-rose-600 ring-2 ring-rose-500/10',
                            'ready' => 'border-t-4 border-t-emerald-500 ring-2 ring-emerald-500/10',
                            default => 'border-t-4 border-t-slate-400'
                        };
                    @endphp

                    <div class="bg-white rounded-3xl border border-slate-200/90 {{ $statusBorder }} shadow-sm hover:shadow-md transition-all flex flex-col justify-between overflow-hidden" 
                         id="order-card-{{ $order->id }}">
                        
                        <div class="p-6">
                            <!-- Card Header -->
                            <div class="flex justify-between items-start border-b border-slate-100 pb-4 mb-4">
                                <div>
                                    <div class="flex items-center space-x-2.5">
                                        <span class="text-4xl font-black text-slate-900 tracking-tight">
                                            {{ $order->queue_number }}
                                        </span>
                                        <span class="text-[11px] font-extrabold px-2.5 py-1 rounded-xl border flex items-center space-x-1.5 {{ $isOnline ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-200' }}">
                                            @if($isOnline)
                                                <!-- Monochrome Phone SVG -->
                                                <svg class="w-3.5 h-3.5 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                </svg>
                                                <span>สั่งออนไลน์ (รับเอง)</span>
                                            @else
                                                <!-- Monochrome Store SVG -->
                                                <svg class="w-3.5 h-3.5 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                                </svg>
                                                <span>หน้าร้าน</span>
                                            @endif
                                        </span>
                                    </div>

                                    <div class="text-xs font-bold text-slate-800 mt-1.5 flex items-center space-x-1">
                                        <span>{{ $order->customer_name }}</span>
                                        @if($order->customer_phone)
                                            <span class="text-slate-400 font-normal">({{ $order->customer_phone }})</span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        @if($order->ip_address)
                                            <span class="text-[9.5px] font-mono text-slate-500 bg-slate-100 px-1.5 py-0.2 rounded border border-slate-200">
                                                🌐 IP: {{ $order->ip_address }}
                                            </span>
                                        @endif
                                        <a href="{{ route('kitchen.chat', ['order_no' => $order->order_no]) }}" 
                                           class="inline-flex items-center gap-1 text-[10.5px] font-bold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-2 py-0.5 rounded-lg border border-rose-200 transition active:scale-95" 
                                           title="เปิดแชทกับลูกค้ารายนี้ (จับคู่ IP และชื่อออร์เดอร์)">
                                            <svg class="w-3 h-3 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                            </svg>
                                            <span>แชท</span>
                                        </a>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <span class="inline-flex items-center space-x-1 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $order->status === 'ready' ? 'bg-emerald-100 text-emerald-800' : ($order->status === 'cooking' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800') }}">
                                        @if($order->status === 'cooking')
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse mr-1"></span>
                                        @endif
                                        <span>
                                            {{ match($order->status) {
                                                'pending' => 'รอยืนยัน',
                                                'confirmed' => 'รับออเดอร์',
                                                'cooking' => 'กำลังปรุง',
                                                'ready' => 'พร้อมรับ',
                                                default => $order->status
                                            } }}
                                        </span>
                                    </span>
                                    <div class="text-[11px] text-slate-400 font-medium mt-1">
                                        {{ $order->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>

                            <!-- Pickup Time Notice (If any) -->
                            @if($order->pickup_time)
                                <div class="mb-3.5 p-2.5 rounded-2xl bg-amber-50/80 border border-amber-200/70 text-xs text-amber-950 flex items-center justify-between">
                                    <div class="flex items-center space-x-1.5 font-medium">
                                        <!-- Monochrome Clock SVG -->
                                        <svg class="w-4 h-4 text-amber-700 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>เวลานัดรับ:</span>
                                    </div>
                                    <strong class="font-bold text-amber-900">{{ $order->pickup_time }}</strong>
                                </div>
                            @endif

                            @if($order->notes)
                                <div class="mb-3.5 p-2.5 rounded-2xl bg-rose-50/70 border border-rose-100 text-xs text-rose-950 flex items-start space-x-2">
                                    <svg class="w-4 h-4 text-rose-600 stroke-current shrink-0 mt-0.5" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                    <div>
                                        <span class="font-bold text-rose-700">หมายเหตุ:</span>
                                        <span class="text-rose-900 ml-1">{{ $order->notes }}</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Items List (Clean Light Theme Cards, Easy to Read) -->
                            <div class="space-y-2.5 my-3">
                                @foreach($order->items as $item)
                                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-start justify-between gap-3">
                                        <div class="space-y-1.5 flex-1">
                                            <div class="font-bold text-sm text-slate-900 flex items-center">
                                                <span class="text-rose-600 font-black text-base mr-1.5">{{ $item->quantity_sticks }}x</span> 
                                                <span>{{ $item->product_name }}</span>
                                            </div>
                                            
                                            <!-- Monochrome Cooking Style & Spiciness Badges -->
                                            <div class="flex flex-wrap gap-1.5">
                                                @if($item->cooking_type === 'grill')
                                                    @if($item->grill_style === 'brush')
                                                        <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-900 border border-amber-200">
                                                            <!-- Monochrome Brush SVG -->
                                                            <svg class="w-3.5 h-3.5 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                            <span>ย่างทาซอส</span>
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-900 border border-rose-200">
                                                            <!-- Monochrome Dip Bowl SVG -->
                                                            <svg class="w-3.5 h-3.5 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a7 7 0 01-7 7m7-7a7 7 0 00-7-7m-7 7a7 7 0 007 7m-7-7a7 7 0 017-7"/></svg>
                                                            <span>ย่างจุ่มซอส</span>
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-red-50 text-red-900 border border-red-200">
                                                        <!-- Monochrome Soup Bowl SVG -->
                                                        <svg class="w-3.5 h-3.5 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 009-9H3a9 9 0 009 9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v2m4-3v3m4-2v2"/></svg>
                                                        <span>
                                                            {{ match($item->soup_style) {
                                                                'mala_rich' => 'ซุปหม่าล่าน้ำข้น',
                                                                'tomyum_rich' => 'ซุปต้มยำน้ำข้น',
                                                                'black_soup' => 'ซุปน้ำดำญี่ปุ่น',
                                                                default => 'ต้มน้ำซุป'
                                                            } }}
                                                        </span>
                                                    </span>
                                                @endif

                                                <!-- Monochrome Spiciness Badge with Single-Color SVG Icons -->
                                                <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg text-xs font-bold bg-white border border-slate-200">
                                                    @if($item->spiciness_level === 'hot')
                                                        <span class="flex items-center space-x-1 text-rose-600 font-extrabold">
                                                            <svg class="w-3.5 h-3.5 fill-rose-600 stroke-rose-600 shrink-0" viewBox="0 0 24 24"><path d="M12 2C11.5 2 11 2.2 10.6 2.6L9.2 4H7C5.3 4 4 5.3 4 7C4 8.7 5.3 10 7 10V11C7 16 10 21 16 22C16.6 22 17 21.6 17 21C17 20.4 16.6 20 16 20C11.8 19.2 9.2 15.6 9 11.7C10.1 11.9 11.2 12 12 12C16.4 12 20 8.4 20 4C20 3.4 19.8 2.8 19.4 2.4C19 2 18.4 1.8 17.8 1.9C16.8 2.1 15.6 2.7 14.5 3.5C13.8 2.6 12.9 2 12 2Z"/></svg>
                                                            <span>เผ็ดมาก</span>
                                                        </span>
                                                    @elseif($item->spiciness_level === 'medium')
                                                        <span class="flex items-center space-x-1 text-amber-600 font-bold">
                                                            <svg class="w-3.5 h-3.5 fill-amber-500 stroke-amber-500 shrink-0" viewBox="0 0 24 24"><path d="M12 2C11.5 2 11 2.2 10.6 2.6L9.2 4H7C5.3 4 4 5.3 4 7C4 8.7 5.3 10 7 10V11C7 16 10 21 16 22C16.6 22 17 21.6 17 21C17 20.4 16.6 20 16 20C11.8 19.2 9.2 15.6 9 11.7C10.1 11.9 11.2 12 12 12C16.4 12 20 8.4 20 4C20 3.4 19.8 2.8 19.4 2.4C19 2 18.4 1.8 17.8 1.9C16.8 2.1 15.6 2.7 14.5 3.5C13.8 2.6 12.9 2 12 2Z"/></svg>
                                                            <span>เผ็ดกลาง</span>
                                                        </span>
                                                    @elseif($item->spiciness_level === 'mild')
                                                        <span class="flex items-center space-x-1 text-slate-700 font-semibold">
                                                            <svg class="w-3.5 h-3.5 text-slate-400 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" /></svg>
                                                            <span>เผ็ดน้อย</span>
                                                        </span>
                                                    @else
                                                        <span class="flex items-center space-x-1 text-slate-400">
                                                            <svg class="w-3.5 h-3.5 text-slate-400 stroke-current shrink-0" fill="none" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6"/></svg>
                                                            <span>ไม่เผ็ด</span>
                                                        </span>
                                                    @endif
                                                </span>
                                            </div>

                                            @if($item->special_instructions)
                                                <div class="text-[11px] text-amber-950 bg-amber-50/80 px-2.5 py-1.5 rounded-xl border border-amber-200/60 mt-1 flex items-start space-x-1.5">
                                                    <svg class="w-3.5 h-3.5 text-amber-700 stroke-current shrink-0 mt-0.5" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                                    </svg>
                                                    <span>{{ $item->special_instructions }}</span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="text-right text-xs font-black text-slate-500 shrink-0">
                                            {{ $item->quantity_sticks }} ไม้
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- 3. Action Buttons & Details Modal Trigger -->
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
                                    class="flex-1 py-3 rounded-2xl bg-rose-600 hover:bg-rose-500 active:scale-[0.99] text-white text-xs font-bold shadow-md shadow-rose-600/20 transition-all flex items-center justify-center space-x-1.5">
                                    <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                                    </svg>
                                    <span>เริ่มย่าง/ต้ม</span>
                                </button>
                            @elseif($order->status === 'cooking')
                                <button onclick="updateStatus({{ $order->id }}, 'ready')"
                                    class="flex-1 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 active:scale-[0.99] text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center space-x-1.5">
                                    <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    <span>พร้อมรับแล้ว</span>
                                </button>
                            @elseif($order->status === 'ready')
                                <button onclick="updateStatus({{ $order->id }}, 'completed')"
                                    class="flex-1 py-3 rounded-2xl bg-slate-900 hover:bg-slate-800 active:scale-[0.99] text-white text-xs font-bold shadow-xs transition-all flex items-center justify-center space-x-1.5">
                                    <svg class="w-4 h-4 stroke-current" fill="none" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>ส่งมอบอาหารแล้ว</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
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
<script>
    // All active orders data for modal preview
    const activeOrdersMap = @json($activeOrders->keyBy('id'));

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

    async function updateStatus(orderId, nextStatus) {
        try {
            const res = await fetch(`/pos/orders/${orderId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: nextStatus })
            });

            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert('เกิดข้อผิดพลาดในการอัปเดตสถานะ');
            }
        } catch (err) {
            console.error(err);
            alert('ข้อผิดพลาดเครือข่าย');
        }
    }

    // Auto-refresh every 12 seconds to fetch incoming orders
    setTimeout(() => {
        window.location.reload();
    }, 12000);
</script>
@endpush
@endsection
