<!DOCTYPE html>
<html lang="th" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>สถานะออเดอร์ {{ $order->queue_number }} - หม่าล่าสตรีท</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('fonts/mala-street-font.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex justify-center bg-slate-900 selection:bg-emerald-500 selection:text-white">
    <div class="w-full max-w-md bg-white min-h-screen flex flex-col shadow-2xl relative">
        <!-- Grab Top Bar -->
        <header class="bg-slate-900 text-white px-4 py-3 flex items-center justify-between">
            <a href="{{ route('online.index') }}" class="text-xs text-slate-300 hover:text-white flex items-center space-x-1">
                <span>← สั่งเพิ่ม</span>
            </a>
            <span class="text-xs font-bold text-emerald-400">Grab-Style Live Tracker</span>
            <span class="w-5"></span>
        </header>

        <div class="p-5 flex-1 space-y-6">
            <!-- Queue Banner -->
            <div class="p-6 bg-gradient-to-tr from-emerald-600 to-teal-700 rounded-3xl text-white text-center shadow-xl shadow-emerald-700/30 relative overflow-hidden">
                <div class="text-xs font-bold text-emerald-100 uppercase tracking-widest">หมายเลขคิวรับอาหารของคุณ</div>
                <div class="text-6xl font-black my-2 tracking-tight" id="live-queue-tag">{{ $order->queue_number }}</div>
                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-white/20 backdrop-blur-xs">
                    <span class="w-2 h-2 mr-1.5 bg-emerald-200 rounded-full animate-ping"></span>
                    <span id="live-status-text">
                        {{ match($order->status) {
                            'pending' => 'รอร้านค้ายืนยันคำสั่งซื้อ',
                            'confirmed' => 'ร้านค้ารับออเดอร์แล้ว',
                            'cooking' => '🔥 กำลังย่าง/ต้มในครัว',
                            'ready' => '🎉 อาหารเสร็จแล้ว พร้อมรับที่หน้าร้าน!',
                            'completed' => '✅ ส่งมอบอาหารเรียบร้อยแล้ว',
                            default => $order->status
                        } }}
                    </span>
                </div>
                <div class="text-xs text-emerald-100 mt-2">
                    ผู้สั่ง: <strong>{{ $order->customer_name }}</strong> • เวลานัดรับ: <strong>{{ $order->pickup_time }}</strong>
                </div>
            </div>

            <!-- Grab Step Progress Bar -->
            <div class="bg-slate-50 p-5 rounded-3xl border border-slate-200 space-y-4">
                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">ขั้นตอนการจัดเตรียมอาหาร:</h4>
                
                <div class="space-y-4">
                    <!-- Step 1 -->
                    <div class="flex items-center space-x-3 text-xs" id="step-1">
                        <div class="w-8 h-8 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center shrink-0">✓</div>
                        <div>
                            <span class="font-bold text-slate-800 block">1. ได้รับคำสั่งซื้อแล้ว</span>
                            <span class="text-[11px] text-slate-400">ชำระเงินผ่าน PromptPay เรียบร้อย</span>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    @php
                        $isCooking = in_array($order->status, ['confirmed', 'cooking', 'ready', 'completed']);
                    @endphp
                    <div class="flex items-center space-x-3 text-xs" id="step-2">
                        <div class="w-8 h-8 rounded-full {{ $isCooking ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-400' }} font-bold flex items-center justify-center shrink-0">
                            {{ $isCooking ? '✓' : '2' }}
                        </div>
                        <div>
                            <span class="font-bold text-slate-800 block">2. ร้านค้ากำลังปรุงอาหาร</span>
                            <span class="text-[11px] text-slate-400">กำลังย่างเตาถ่าน ทา/จุ่มซอส และต้มซุปตามสั่ง</span>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    @php
                        $isReady = in_array($order->status, ['ready', 'completed']);
                    @endphp
                    <div class="flex items-center space-x-3 text-xs" id="step-3">
                        <div class="w-8 h-8 rounded-full {{ $isReady ? 'bg-emerald-600 text-white animate-bounce' : 'bg-slate-200 text-slate-400' }} font-bold flex items-center justify-center shrink-0">
                            {{ $isReady ? '🔔' : '3' }}
                        </div>
                        <div>
                            <span class="font-bold text-slate-800 block">3. อาหารเสร็จแล้ว (พร้อมรับ)</span>
                            <span class="text-[11px] text-slate-400">แจ้งหมายเลขคิว {{ $order->queue_number }} ที่หน้าร้านเพื่อรับอาหาร</span>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    @php
                        $isCompleted = $order->status === 'completed';
                    @endphp
                    <div class="flex items-center space-x-3 text-xs" id="step-4">
                        <div class="w-8 h-8 rounded-full {{ $isCompleted ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-400' }} font-bold flex items-center justify-center shrink-0">
                            {{ $isCompleted ? '✓' : '4' }}
                        </div>
                        <div>
                            <span class="font-bold text-slate-800 block">4. ส่งมอบเรียบร้อย</span>
                            <span class="text-[11px] text-slate-400">ขอบพระคุณที่อุดหนุนหม่าล่าสตรีท</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Store Location & Contact -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">📍 จุดรับอาหารหน้าร้าน:</span>
                    <span class="text-rose-600 font-bold">ร้านหม่าล่าสตรีท</span>
                </div>
                <p class="text-[11px] text-slate-500">ซอยสตรีทฟู้ด หน้าตลาด ล็อค 12 (แสดงหน้าจอนี้แก่พนักงาน)</p>
                <div class="pt-2 border-t border-slate-200 flex justify-between items-center text-xs">
                    <span class="text-slate-600">เบอร์โทรติดต่อร้าน:</span>
                    <a href="tel:0891234567" class="font-bold text-emerald-600">📞 089-123-4567</a>
                </div>
            </div>

            <!-- Order Details -->
            <div class="p-4 bg-white rounded-2xl border border-slate-200 space-y-2 text-xs">
                <div class="flex justify-between items-center font-bold text-slate-800 border-b border-slate-100 pb-2">
                    <span>รายการที่สั่ง ({{ $order->items->sum('quantity_sticks') }} ไม้)</span>
                    <span class="text-rose-600 text-sm">฿{{ number_format($order->total_amount, 2) }}</span>
                </div>

                <div class="space-y-1.5 pt-1">
                    @foreach($order->items as $item)
                        <div class="flex justify-between text-slate-600 text-xs">
                            <div>
                                <span class="font-bold text-slate-800">{{ $item->quantity_sticks }}x</span> {{ $item->product_name }}
                                <div class="text-[10px] text-slate-400">
                                    {{ $item->cooking_type === 'grill' ? ($item->grill_style === 'brush' ? 'ย่างทา' : 'ย่างจุ่ม') : 'ต้มซุป' }} • 
                                    {{ match($item->spiciness_level) { 'mild'=>'เผ็ดน้อย', 'medium'=>'เผ็ดกลาง', 'hot'=>'เผ็ดมาก', default=>'ไม่เผ็ด' } }}
                                </div>
                            </div>
                            <span class="font-bold">฿{{ number_format($item->subtotal, 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Real-time polling to update status
        setInterval(async () => {
            try {
                const res = await fetch("{{ route('online.status', ['order_no' => $order->order_no]) }}");
                const data = await res.json();
                
                if (data.status) {
                    let text = data.status;
                    if (data.status === 'cooking') text = '🔥 กำลังย่าง/ต้มในครัว';
                    if (data.status === 'ready') text = '🎉 อาหารเสร็จแล้ว พร้อมรับที่หน้าร้าน!';
                    if (data.status === 'completed') text = '✅ ส่งมอบอาหารเรียบร้อยแล้ว';
                    
                    document.getElementById('live-status-text').innerText = text;
                    
                    if (data.status === 'ready' || data.status === 'completed') {
                        // Refresh page to update progress steps
                        window.location.reload();
                    }
                }
            } catch (e) {
                console.error(e);
            }
        }, 5000);
    </script>
    @endpush
</body>
</html>

