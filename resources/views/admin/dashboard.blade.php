@extends('layouts.app', ['title' => 'แดชบอร์ดภาพรวมร้าน - หม่าล่าสตรีท'])

@section('content')
<div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gradient-to-r from-slate-900 via-rose-950 to-slate-900 p-6 rounded-3xl text-white shadow-xl">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 rounded-2xl bg-rose-600/30 border border-rose-500/40 flex items-center justify-center text-4xl shadow-inner">
                🍢
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-2xl font-black tracking-tight">ร้านหม่าล่าสตรีท (Mala Street)</h1>
                    <span class="bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">POS & Backoffice</span>
                </div>
                <p class="text-xs text-rose-300 mt-1">สรุปยอดขายหน้าร้าน, ออเดอร์ออนไลน์, ต้นทุนไม้, และสต็อกคงเหลือประจำวัน</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- 1. ปุ่มเปิดรับออเดอร์ออนไลน์จริง (GO LIVE) หรือ สลับโหมดพรีวิว (ย้ายขึ้นมาจากแถบเหลืองเดิม) -->
            <form action="{{ route('admin.settings.toggle_online') }}" method="POST" class="inline-block m-0">
                @csrf
                @if(($onlineOrderStatus ?? 'preview') === 'live')
                    <input type="hidden" name="mode" value="preview">
                    <button type="submit" class="py-2.5 px-4 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-md shadow-amber-600/30 flex items-center space-x-1.5 transition-all cursor-pointer">
                        <span>🟡</span>
                        <span>สลับกลับเป็นโหมดพรีวิว</span>
                    </button>
                @else
                    <input type="hidden" name="mode" value="live">
                    <button type="submit" class="py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/30 flex items-center space-x-1.5 transition-all cursor-pointer">
                        <span>🚀</span>
                        <span>กดเปิดรับออเดอร์ออนไลน์จริง (GO LIVE)</span>
                    </button>
                @endif
            </form>

            <!-- 2. แถบฟิกคอสและกำไร (ย้ายมาอยู่ด้วยกันตามสั่ง) -->
            <a href="{{ route('admin.expenses.index') }}" 
               class="py-2.5 px-4 rounded-xl bg-slate-800/90 hover:bg-slate-700 text-white font-bold text-xs shadow-md border border-slate-700/80 flex items-center space-x-1.5 transition-all cursor-pointer">
                <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>ฟิกคอส &amp; กำไร</span>
            </a>
        </div>
    </div>



    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- 1. Total Sales -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase">ยอดขายวันนี้</span>
            <div class="my-2">
                <span class="text-2xl font-black text-slate-900">฿{{ number_format($totalSalesToday, 0) }}</span>
            </div>
            <div class="text-[11px] text-slate-500 flex justify-between">
                <span>POS: ฿{{ number_format($posSalesToday, 0) }}</span>
                <span class="text-emerald-600 font-bold">App: ฿{{ number_format($onlineSalesToday, 0) }}</span>
            </div>
        </div>

        <!-- 2. Sticks Sold -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase">ขายไปแล้ว</span>
            <div class="my-2">
                <span class="text-2xl font-black text-rose-600">{{ number_format($totalSticksSold) }}</span>
                <span class="text-xs text-slate-400 font-bold">ไม้</span>
            </div>
            <span class="text-[11px] text-slate-400">จาก {{ $todayOrders->count() }} ออเดอร์</span>
        </div>

        <!-- 3. Current Stock Sticks -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase">สต็อกไม้คงเหลือ</span>
            <div class="my-2">
                <span class="text-2xl font-black text-amber-600">{{ number_format($totalSticksInStock) }}</span>
                <span class="text-xs text-slate-400 font-bold">ไม้</span>
            </div>
            <a href="{{ route('admin.inventory.index') }}" class="text-[11px] text-rose-600 font-semibold hover:underline">
                ตรวจนับสต็อก →
            </a>
        </div>

        <!-- 4. Today Purchases (COGS) -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase">ซื้อวัตถุดิบวันนี้</span>
            <div class="my-2">
                <span class="text-2xl font-black text-slate-800">฿{{ number_format($todayReceiptsCost, 0) }}</span>
            </div>
            <a href="{{ route('admin.receipts.index') }}" class="text-[11px] text-slate-500 hover:text-rose-600">
                สแกนใบเสร็จ →
            </a>
        </div>

        <!-- 5. Fixed Costs & Expenses -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase">ฟิกคอส & รายจ่าย</span>
            <div class="my-2">
                <span class="text-2xl font-black text-slate-800">฿{{ number_format($todayExpenses, 0) }}</span>
            </div>
            <a href="{{ route('admin.expenses.index') }}" class="text-[11px] text-slate-500 hover:text-rose-600">
                จัดการรายจ่าย →
            </a>
        </div>

        <!-- 6. Estimated Net Profit -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between {{ $netProfitToday >= 0 ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50/50 border-rose-200' }}">
            <span class="text-xs font-bold text-slate-600 uppercase">กำไรสุทธิวันนี้ (Est.)</span>
            <div class="my-2">
                <span class="text-2xl font-black {{ $netProfitToday >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                    ฿{{ number_format($netProfitToday, 0) }}
                </span>
            </div>
            <span class="text-[11px] {{ $netProfitToday >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-semibold">
                {{ $netProfitToday >= 0 ? 'กำไรเป็นบวก' : 'ยังไม่คุ้มฟิกคอส' }}
            </span>
        </div>
    </div>

    <!-- Two Columns: Price Fluctuation Alerts & Low Stock Skewers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card 1: ระบบตรวจจับราคาขึ้นลงของต้นทุน (Price Fluctuations) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <span class="text-xl">📈</span>
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-800">ระบบตรวจจับราคาขึ้น-ลงของต้นทุน</h3>
                        <p class="text-[11px] text-slate-400">เปรียบเทียบราคาล่าสุดกับราคาเดิม พร้อมอ้างอิงร้านค้าที่ซื้อ</p>
                    </div>
                </div>
                <a href="{{ route('admin.receipts.price_tracker') }}" class="text-xs font-bold text-rose-600 hover:underline">
                    ดูทั้งหมด →
                </a>
            </div>

            <div class="space-y-3">
                @forelse($recentPriceAlerts as $alert)
                    @php
                        $isUp = $alert->price_change_amount > 0;
                        $isDown = $alert->price_change_amount < 0;
                    @endphp
                    <div class="p-3 rounded-2xl border border-slate-100 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">{{ $isUp ? '🔺' : ($isDown ? '🔻' : '➖') }}</span>
                            <div>
                                <h4 class="font-bold text-xs text-slate-800">{{ $alert->rawMaterial->name }}</h4>
                                <span class="text-[11px] text-slate-500">
                                    ซื้อจาก: <strong>{{ $alert->vendor_name }}</strong> (บิล: {{ $alert->invoice_number ?: '-' }})
                                </span>
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-xs font-bold text-slate-900">
                                @if($alert->previous_cost_price)
                                    <span class="text-slate-400 line-through text-[11px]">฿{{ number_format($alert->previous_cost_price, 2) }}</span> → 
                                @endif
                                <span>฿{{ number_format($alert->purchase_unit_price, 2) }}</span>
                            </div>
                            <div class="text-[10px] font-bold {{ $isUp ? 'text-rose-600' : ($isDown ? 'text-emerald-600' : 'text-slate-500') }}">
                                {{ $isUp ? '+' : '' }}{{ number_format($alert->price_change_amount, 2) }} บ. 
                                ({{ $isUp ? '+' : '' }}{{ number_format($alert->price_change_percent, 1) }}%)
                            </div>
                            <span class="text-[9px] text-slate-400">ต้นทุน: ฿{{ number_format($alert->cost_per_stick, 2) }}/ไม้</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">ยังไม่มีประวัติการเปลี่ยนแปลงราคา</p>
                @endforelse
            </div>
        </div>

        <!-- Card 2: สรุปยอดไม้คงเหลือ & เตือนไม้ใกล้หมด (Low Stock Alert) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <span class="text-xl">🍢</span>
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-800">สถานะสต็อกไม้คงเหลือ (Reconciliation)</h3>
                        <p class="text-[11px] text-slate-400">ยกมาจากเมื่อวาน + ซื้อเติมวันนี้ - หักยอดขาย</p>
                    </div>
                </div>
                <a href="{{ route('admin.inventory.index') }}" class="text-xs font-bold text-rose-600 hover:underline">
                    ตารางสต็อกรวม →
                </a>
            </div>

            <div class="space-y-3">
                @forelse($lowStockProducts as $prod)
                    <div class="p-3 rounded-2xl border border-amber-200/60 bg-amber-50/50 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">⚠️</span>
                            <div>
                                <h4 class="font-bold text-xs text-slate-800">{{ $prod->name }}</h4>
                                <span class="text-[11px] text-slate-500">หมวดหมู่: {{ $prod->category->name }} • ราคา ฿{{ number_format($prod->price_per_stick, 0) }}/ไม้</span>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-xs font-extrabold px-2.5 py-1 rounded-full {{ $prod->stock_sticks <= 5 ? 'bg-rose-100 text-rose-700 font-black' : 'bg-amber-100 text-amber-800' }}">
                                เหลือ {{ $prod->stock_sticks }} ไม้
                            </span>
                            <span class="block text-[10px] text-slate-400 mt-1">แนะนำเสียบเพิ่ม</span>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-emerald-700 bg-emerald-50 rounded-2xl border border-emerald-200 font-medium">
                        ✅ สต็อกไม้ทุกรายการเพียงพอสำหรับการขายวันนี้
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Orders Stream -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="font-extrabold text-sm text-slate-800">ออเดอร์ล่าสุดของร้าน (ทั้งหน้าร้าน & ออนไลน์)</h3>
            <span class="text-xs text-slate-400">อัปเดตอัตโนมัติ</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="text-slate-500 bg-slate-50 uppercase border-y border-slate-100 font-bold">
                    <tr>
                        <th class="py-3 px-3">คิว</th>
                        <th class="py-3 px-3">ประเภท</th>
                        <th class="py-3 px-3">ลูกค้า</th>
                        <th class="py-3 px-3">รายการที่สั่ง</th>
                        <th class="py-3 px-3">ยอดเงิน</th>
                        <th class="py-3 px-3">วิธีชำระ</th>
                        <th class="py-3 px-3">สถานะ</th>
                        <th class="py-3 px-3 text-right">เวลา</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentOrders as $order)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-3 font-black text-sm {{ $order->order_type === 'online' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $order->queue_number }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $order->order_type === 'online' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $order->order_type === 'online' ? '📱 สั่งออนไลน์' : '🛒 หน้าร้าน' }}
                                </span>
                            </td>
                            <td class="py-3 px-3 font-semibold text-slate-800">
                                {{ $order->customer_name }}
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ $order->items->sum('quantity_sticks') }} ไม้ 
                                <span class="text-[10px] text-slate-400">({{ $order->items->pluck('product_name')->take(2)->join(', ') }}...)</span>
                            </td>
                            <td class="py-3 px-3 font-black text-slate-900">
                                ฿{{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="text-[11px] font-semibold text-slate-600">
                                    {{ $order->payment_method === 'cash' ? '💵 เงินสด' : '📱 พร้อมเพย์' }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $order->status === 'completed' ? 'bg-slate-100 text-slate-600' : ($order->status === 'ready' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ match($order->status) {
                                        'pending' => 'รอยืนยัน',
                                        'confirmed' => 'รับแล้ว',
                                        'cooking' => 'กำลังปรุง',
                                        'ready' => 'พร้อมรับ',
                                        'completed' => 'ส่งมอบแล้ว',
                                        default => $order->status
                                    } }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right text-slate-400 text-[11px]">
                                {{ $order->created_at->format('H:i') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

