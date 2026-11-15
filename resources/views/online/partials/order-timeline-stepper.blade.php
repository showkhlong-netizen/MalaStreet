@php
    $isCompact = ($variant ?? 'strip') === 'strip';
    $status = $order->status ?? 'confirmed';
    $stage = match ($status) {
        'pending', 'confirmed' => 1,
        'cooking' => 2,
        'ready' => 3,
        'completed' => 4,
        'cancelled' => 0,
        default => 1,
    };
    $titleText = match ($status) {
        'pending' => 'รอร้านค้ายืนยันออเดอร์',
        'confirmed' => 'ร้านรับออเดอร์แล้ว',
        'cooking' => 'กำลังปิ้งย่างในครัว',
        'ready' => 'อาหารเสร็จแล้ว พร้อมรับ!',
        'completed' => 'ส่งมอบอาหารเรียบร้อย',
        'cancelled' => 'ยกเลิกออเดอร์แล้ว',
        default => 'กำลังดำเนินการ',
    };
    $subtitleText = match ($status) {
        'pending' => 'กำลังดำเนินการ...',
        'confirmed' => 'กำลังเตรียมวัตถุดิบและคิวเตาย่าง...',
        'cooking' => 'กำลังย่างเตาถ่านและปรุงตามสั่ง...',
        'ready' => 'แจ้งคิว ' . ($order->queue_number ?? '') . ' รับอาหารที่หน้าร้านได้เลย',
        'completed' => 'ขอบพระคุณที่อุดหนุนหม่าล่าสตรีท',
        'cancelled' => 'กรุณาสอบถามแอดมินในแชท',
        default => 'กำลังดำเนินการ...',
    };
@endphp

<div data-order-timeline>
    <div data-tl-title class="{{ $isCompact ? 'text-[14.5px]' : 'text-[20px]' }} font-extrabold leading-tight {{ $status === 'cancelled' ? 'text-slate-500' : 'text-[#D90012]' }}">
        {{ $titleText }}
    </div>
    <div data-tl-subtitle class="{{ $isCompact ? 'text-[10.5px]' : 'text-[12px]' }} text-slate-500 mt-0.5 font-medium">
        {{ $subtitleText }}
    </div>

    <div class="flex items-center {{ $isCompact ? 'mt-2.5 mb-2' : 'mt-4 mb-3.5' }}">
        {{-- Step 1: Shop / Order Received (M) --}}
        <div data-tl-step="1" class="{{ $isCompact ? 'w-5 h-5 text-[10px]' : 'w-6 h-6 text-[11px]' }} rounded-[5px] flex items-center justify-center font-black shrink-0 transition-colors {{ $stage >= 1 ? 'bg-[#D90012] text-white shadow-xs shadow-rose-500/30' : 'bg-slate-200 text-slate-400' }}">
            M
        </div>

        <div data-tl-line="2" class="flex-1 h-[2.5px] rounded-full mx-1.5 transition-colors {{ $stage >= 2 ? 'bg-[#D90012]' : 'bg-slate-200' }}"></div>

        {{-- Step 2: Cooking Pot with Steam --}}
        <div data-tl-step="2" class="shrink-0 transition-colors {{ $stage >= 2 ? 'text-[#D90012]' : 'text-slate-300' }}">
            <svg class="{{ $isCompact ? 'w-[19px] h-[19px]' : 'w-[22px] h-[22px]' }}" viewBox="0 0 24 24" fill="currentColor">
                <path d="M8.5 2.5a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5a.75.75 0 01.75-.75zm3.5-.75a.75.75 0 01.75.75v2a.75.75 0 01-1.5 0v-2a.75.75 0 01.75-.75zm3.5.75a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5a.75.75 0 01.75-.75z"/>
                <path d="M4 9.25A1.25 1.25 0 015.25 8h13.5a1.25 1.25 0 010 2.5H5.25A1.25 1.25 0 014 9.25z"/>
                <path d="M3 11.5h1.5v.5h15v-.5H21a1 1 0 011 1v.5a1 1 0 01-1 1h-1v2.75A3.25 3.25 0 0116.75 20H7.25A3.25 3.25 0 014 16.75V14H3a1 1 0 01-1-1v-.5a1 1 0 011-1z"/>
            </svg>
        </div>

        <div data-tl-line="3" class="flex-1 h-[2.5px] rounded-full mx-1.5 transition-colors {{ $stage >= 3 ? 'bg-[#D90012]' : 'bg-slate-200' }}"></div>

        {{-- Step 3: Rider / Delivery Scooter --}}
        <div data-tl-step="3" class="shrink-0 transition-colors {{ $stage >= 3 ? 'text-[#D90012]' : 'text-slate-300' }}">
            <svg class="{{ $isCompact ? 'w-[20px] h-[20px]' : 'w-[23px] h-[23px]' }}" viewBox="0 0 24 24" fill="currentColor">
                <path d="M16.5 6a1.5 1.5 0 00-1.5 1.5V9h2.25l1.1 2.2A3.75 3.75 0 0015 14.5h-1.25l-1.4-4.2A2 2 0 0010.45 9H6.5A1.5 1.5 0 005 10.5v2.25A3.75 3.75 0 002.25 16.5a3.25 3.25 0 006.45.5h6.6a3.25 3.25 0 006.45-.5 3.75 3.75 0 00-1.6-3.07L18.4 7.75A1.5 1.5 0 0017 6.75V6h-.5zm-11 12.25a1.75 1.75 0 110-3.5 1.75 1.75 0 010 3.5zm13 0a1.75 1.75 0 110-3.5 1.75 1.75 0 010 3.5z"/>
                <rect x="4" y="6" width="5.5" height="2.4" rx="0.8"/>
            </svg>
        </div>

        <div data-tl-line="4" class="flex-1 h-[2.5px] rounded-full mx-1.5 transition-colors {{ $stage >= 4 ? 'bg-[#D90012]' : 'bg-slate-200' }}"></div>

        {{-- Step 4: Location Pin --}}
        <div data-tl-step="4" class="shrink-0 transition-colors {{ $stage >= 4 ? 'text-[#D90012]' : 'text-slate-300' }}">
            <svg class="{{ $isCompact ? 'w-[18px] h-[18px]' : 'w-[21px] h-[21px]' }}" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M12 2.25c-4.142 0-7.5 3.28-7.5 7.326 0 4.92 6.082 10.973 7.015 11.87a.7.7 0 00.97 0C13.418 20.55 19.5 14.497 19.5 9.576 19.5 5.53 16.142 2.25 12 2.25zm0 9.826a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" clip-rule="evenodd"/>
            </svg>
        </div>
    </div>

    <div class="flex items-center justify-between gap-2">
        <span class="{{ $isCompact ? 'text-[10.5px]' : 'text-[12px]' }} text-slate-500 font-medium">
            Order no. {{ $order->order_no }}
        </span>
        @if($isCompact)
            <span class="text-[10.5px] font-bold text-[#D90012] flex items-center gap-0.5 shrink-0">
                <span>ดูรายการเต็ม</span>
                <span>›</span>
            </span>
        @endif
    </div>
</div>
