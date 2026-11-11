<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>สะสมแต้ม & รีวิวอาหาร - หม่าล่าสตรีท (Mala Street)</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('fonts/mala-street-font.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Confetti JS for Reward Redemption & Review Celebration -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <style>
        body { font-family: 'Prompt', sans-serif; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-screen bg-[#111317] flex justify-center items-start selection:bg-[#E60018] selection:text-white">

    <!-- MOBILE CONTAINER (400 - 430px Frame Layout) -->
    <div class="w-full max-w-[430px] min-h-screen bg-[#F6F7F9] shadow-[0_0_60px_rgba(0,0,0,0.6)] flex flex-col justify-between relative overflow-hidden pb-16">

        <!-- ================================================================= -->
        <!-- 1. TOP HEADER                                                     -->
        <!-- ================================================================= -->
        <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/80 px-3.5 py-2.5 shadow-xs flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0">
                <a href="{{ route('online.index') }}"
                   class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition-all shrink-0 active:scale-95"
                   title="กลับหน้าร้านค้า">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div class="min-w-0 leading-tight">
                    <h1 class="text-sm font-extrabold text-slate-900 truncate">แต้มสะสม & รีวิวอาหาร</h1>
                    <p class="text-[10px] text-amber-700 font-medium">ซื้อ 1 ครั้ง = 1 แต้ม • ครบ 5 ฟรี 1 ไม้</p>
                </div>
            </div>

            <!-- Chat Button Quick Link -->
            <a href="{{ route('online.chat') }}"
               class="w-8 h-8 rounded-full bg-slate-900 hover:bg-black text-white flex items-center justify-center transition-all shrink-0 active:scale-95 shadow-xs"
               title="แชทกับร้านค้า">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
            </a>
        </header>

        <!-- MAIN SCROLLABLE CONTENT -->
        <main class="flex-1 overflow-y-auto px-3.5 py-3 space-y-3.5 hide-scrollbar">

            <!-- ============================================================= -->
            <!-- 2. CUSTOMER PHONE BAR                                         -->
            <!-- ============================================================= -->
            <div class="bg-white rounded-2xl p-2.5 border border-slate-200/90 shadow-2xs flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-xs shrink-0">
                        📱
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] text-slate-400 font-medium">เบอร์โทรสมาชิก</div>
                        <div id="display-phone" class="text-xs font-black text-slate-800 truncate font-mono">
                            {{ $reward->customer_phone }}
                        </div>
                    </div>
                </div>

                <button type="button"
                        onclick="openPhoneLookupModal()"
                        class="text-[10px] font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 px-2.5 py-1 rounded-xl transition-all border border-rose-200 shrink-0 cursor-pointer">
                    เปลี่ยนเบอร์ / เช็คแต้ม
                </button>
            </div>

            <!-- ============================================================= -->
            <!-- 3. DIGITAL STAMP CARD (5 STAMPS = 1 FREE STICK)                -->
            <!-- ============================================================= -->
            @php
                $stamps = $reward->points % 5;
                $completedCards = floor($reward->points / 5);
            @endphp
            <section class="relative rounded-3xl overflow-hidden p-4 text-white shadow-xl bg-gradient-to-br from-[#850308] via-[#A80B12] to-[#400204] border border-rose-900/40">
                <!-- Background Glow -->
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-rose-500/20 rounded-full blur-2xl pointer-events-none"></div>

                <!-- Header of Stamp Card -->
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[10px] text-rose-200 uppercase tracking-widest font-bold">Mala Street Loyalty Card</div>
                        <h2 class="text-base font-black tracking-tight text-white mt-0.5">บัตรสะสมแต้มหม่าล่า</h2>
                    </div>
                    <div class="text-right">
                        <div class="text-[9px] text-rose-200">แต้มคงเหลือ</div>
                        <div id="live-points-count" class="text-2xl font-black text-amber-300 font-mono leading-none">
                            {{ $reward->points }}
                        </div>
                        <div class="text-[9px] text-rose-200 mt-0.5">แต้มพร้อมใช้</div>
                    </div>
                </div>

                <!-- Rule Badge -->
                <div class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-black/30 backdrop-blur-xs text-[10px] text-amber-200 font-bold border border-amber-400/30">
                    <span>✨ สะสมครบ 5 แต้ม = แลกรับหม่าล่าฟรี 1 ไม้!</span>
                </div>

                <!-- 5 Stamp Slots Grid -->
                <div class="mt-4 grid grid-cols-5 gap-1.5">
                    @for($i = 1; $i <= 5; $i++)
                        @php
                            $isStamped = ($reward->points >= $i) || ($stamps >= $i && $reward->points > 0);
                            $isSlot5 = ($i === 5);
                        @endphp
                        <div class="relative flex flex-col items-center justify-center p-2 rounded-2xl border transition-all duration-300 {{ $isStamped ? 'bg-amber-400/20 border-amber-300 text-amber-200 shadow-inner' : 'bg-white/10 border-white/20 text-white/40' }} aspect-square">
                            @if($isSlot5)
                                <!-- Slot 5: Free Stick Reward -->
                                <div class="text-xl {{ $isStamped ? 'scale-110 drop-shadow-[0_0_8px_rgba(251,191,36,0.8)]' : 'opacity-50' }}">🎁</div>
                                <span class="text-[9px] font-black leading-none mt-1 {{ $isStamped ? 'text-amber-300' : 'text-white/60' }}">
                                    ฟรี 1 ไม้!
                                </span>
                            @else
                                <!-- Regular Stamp Slot -->
                                <div class="text-lg {{ $isStamped ? 'scale-110' : 'opacity-40' }}">🍢</div>
                                <span class="text-[9px] font-bold leading-none mt-1 {{ $isStamped ? 'text-amber-300' : 'text-white/60' }}">
                                    ไม้ที่ {{ $i }}
                                </span>
                            @endif

                            @if($isStamped)
                                <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[9px] font-bold shadow-xs">
                                    ✓
                                </span>
                            @endif
                        </div>
                    @endfor
                </div>

                <!-- Progress text -->
                <div class="mt-3 flex items-center justify-between text-[10.5px] text-rose-200">
                    <div>
                        สะสมแต้มทั้งหมด: <strong class="text-white">{{ $reward->total_stamps }} แต้ม</strong>
                    </div>
                    <div>
                        แลกฟรีไปแล้ว: <strong class="text-amber-300">{{ $reward->free_skewers_redeemed }} ไม้</strong>
                    </div>
                </div>

                <!-- Big Redeem Button -->
                <div class="mt-3.5">
                    @if($reward->points >= 5)
                        <button type="button"
                                id="btn-redeem-reward"
                                onclick="redeemFreeStick()"
                                class="w-full py-2.5 px-4 rounded-2xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/40 active:scale-95 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <span class="text-sm">🎉</span>
                            <span>แลกรับหม่าล่าฟรี 1 ไม้ ทันที! (ใช้ 5 แต้ม)</span>
                        </button>
                    @else
                        <button type="button"
                                disabled
                                class="w-full py-2.5 px-4 rounded-2xl bg-white/20 text-white/60 font-bold text-xs cursor-not-allowed flex items-center justify-center gap-1.5 border border-white/10">
                            <span>สะสมครบ 5 แต้มเพื่อแลกฟรี 1 ไม้ (ขาดอีก {{ 5 - ($reward->points % 5) }} แต้ม)</span>
                        </button>
                    @endif
                </div>
            </section>

            <!-- ============================================================= -->
            <!-- 4. ACTIVE REDEEMED VOUCHERS (คูปองที่แลกไว้)                  -->
            <!-- ============================================================= -->
            <section id="vouchers-section" class="{{ $vouchers->isEmpty() ? 'hidden' : 'block' }} space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-extrabold text-slate-900 flex items-center gap-1">
                        <span>🎟️</span>
                        <span>คูปองฟรีหม่าล่าของคุณ (พร้อมใช้งาน)</span>
                    </h3>
                    <span id="voucher-count-badge" class="text-[9.5px] bg-emerald-100 text-emerald-800 font-extrabold px-1.5 py-0.2 rounded-full">
                        {{ $vouchers->count() }} ใบ
                    </span>
                </div>

                <div id="vouchers-list" class="space-y-2">
                    @foreach($vouchers as $v)
                        <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/90 rounded-2xl p-3 flex items-center justify-between gap-2 shadow-2xs">
                            <div class="min-w-0">
                                <div class="text-[11.5px] font-extrabold text-emerald-950 truncate">
                                    {{ $v->reward_title }}
                                </div>
                                <div class="text-[10px] text-emerald-700 flex items-center gap-1.5 mt-0.5">
                                    <span>รหัสคูปอง:</span>
                                    <strong class="font-mono bg-white px-1.5 py-0.2 rounded border border-emerald-300 font-bold tracking-wider select-all">{{ $v->voucher_code }}</strong>
                                </div>
                                <div class="text-[9px] text-slate-400 mt-1">
                                    ใช้ได้ถึง: {{ $v->expires_at ? $v->expires_at->format('d/m/Y') : 'ไม่มีวันหมดอายุ' }}
                                </div>
                            </div>
                            <button type="button"
                                    onclick="copyVoucherCode('{{ $v->voucher_code }}')"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] px-2.5 py-1.5 rounded-xl shadow-xs shrink-0 active:scale-95 transition-all">
                                คัดลอกรหัส
                            </button>
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- ============================================================= -->
            <!-- 5. REVIEW FORM (เขียนรีวิวรับเพิ่มทันที +1 แต้ม)               -->
            <!-- ============================================================= -->
            <section class="bg-white rounded-3xl p-4 border border-rose-200/80 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between gap-1">
                    <div class="flex items-center gap-1.5">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-amber-400 to-rose-500 text-white flex items-center justify-center font-black text-xs shrink-0">
                            ★
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-slate-900 leading-tight">เขียนรีวิวอาหาร</h3>
                            <p class="text-[9.5px] text-slate-500 leading-tight">แชร์ความอร่อยให้เพื่อนๆ ทราบ</p>
                        </div>
                    </div>
                    <span class="bg-gradient-to-r from-rose-600 to-amber-600 text-white text-[9.5px] font-black px-2 py-0.5 rounded-full shadow-2xs animate-pulse">
                        รับทันที +1 แต้ม 🎁
                    </span>
                </div>

                <form id="review-form" onsubmit="handleReviewSubmit(event)" class="mt-3 space-y-2.5">
                    <!-- Rating Stars -->
                    <div>
                        <label class="block text-[10.5px] font-bold text-slate-700 mb-1">ให้คะแนนความพึงพอใจ:</label>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-1" id="star-rating-group">
                                @for($s = 1; $s <= 5; $s++)
                                    <button type="button" onclick="setReviewRating({{ $s }})" class="star-btn text-2xl text-amber-400 transition-transform hover:scale-125 focus:outline-none" data-rating="{{ $s }}">
                                        ★
                                    </button>
                                @endfor
                            </div>
                            <span id="rating-label" class="text-xs font-bold text-amber-600 ml-1">5 ดาว (ประทับใจมาก)</span>
                            <input type="hidden" id="review-rating-input" value="5">
                        </div>
                    </div>

                    <!-- Customer Name & Phone -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">ชื่อของคุณ *</label>
                            <input type="text" id="review-name-input" required placeholder="เช่น คุณเอ" value="{{ $reward->customer_name ?: 'ลูกค้า' }}" class="w-full bg-slate-50 text-slate-900 text-xs px-2.5 py-1.5 rounded-xl border border-slate-200 focus:border-rose-500 focus:bg-white outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">เบอร์โทรศัพท์ (รับแต้ม) *</label>
                            <input type="tel" id="review-phone-input" required placeholder="08x-xxx-xxxx" value="{{ $reward->customer_phone }}" class="w-full bg-slate-50 text-slate-900 text-xs px-2.5 py-1.5 rounded-xl border border-slate-200 focus:border-rose-500 focus:bg-white outline-none font-mono">
                        </div>
                    </div>

                    <!-- Favorite Dishes Quick Selector -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-600 mb-1">เมนูโปรดที่คุณแนะนำ (แตะเลือกได้):</label>
                        <div class="flex flex-wrap gap-1" id="fav-dishes-chips">
                            @foreach(['หมูสามชั้น', 'เอ็นไก่', 'เต้าหู้ชีส', 'เนื้อริบอาย', 'เห็ดเข็มทองพันเบคอน', 'ซุปหม่าล่าเข้มข้น', 'เผ็ดชาสะใจ'] as $dish)
                                <button type="button" onclick="toggleDishChip(this, '{{ $dish }}')" class="dish-chip text-[9.5px] bg-slate-100 hover:bg-rose-50 text-slate-700 px-2 py-0.5 rounded-lg border border-slate-200 transition-all cursor-pointer">
                                    + {{ $dish }}
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" id="fav-dishes-input" value="">
                    </div>

                    <!-- Review Comment Textarea -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-600 mb-0.5">ความคิดเห็นของคุณ *</label>
                        <textarea id="review-comment-input" rows="2" required placeholder="บอกเล่ารสชาติ ความเผ็ดชา หรือความประทับใจ..." class="w-full bg-slate-50 text-slate-900 text-xs p-2.5 rounded-xl border border-slate-200 focus:border-rose-500 focus:bg-white outline-none resize-none"></textarea>
                    </div>

                    <!-- Photo Upload with Client-Side Canvas Compression & URL Conversion -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-600 mb-1">แนบรูปภาพอาหาร (ระบบจะบีบอัดและแปลงเป็น URL):</label>
                        <input type="file" id="review-file-input" accept="image/*" class="hidden" onchange="handleReviewImageSelection(event)">
                        
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="document.getElementById('review-file-input').click()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-3 py-1.5 rounded-xl border border-slate-200 flex items-center gap-1.5 transition-all cursor-pointer active:scale-95">
                                <span>📷</span>
                                <span>เลือกรูปภาพอาหาร</span>
                            </button>
                            <span id="review-img-status" class="text-[9.5px] text-slate-400 truncate max-w-[200px]">ยังไม่ได้เลือกรูป</span>
                        </div>

                        <!-- Preview Thumbnail -->
                        <div id="review-img-preview-box" class="hidden mt-2 relative w-16 h-16 rounded-xl overflow-hidden border border-slate-200 shadow-2xs">
                            <img id="review-img-preview" src="" alt="รีวิวพรีวิว" class="w-full h-full object-cover">
                            <button type="button" onclick="clearReviewImage()" class="absolute top-0 right-0 bg-black/60 text-white w-4 h-4 text-[9px] flex items-center justify-center">✕</button>
                        </div>
                        <input type="hidden" id="review-image-url-input" value="">
                    </div>

                    <!-- Submit Review Button -->
                    <button type="submit" id="btn-submit-review" class="w-full py-2.5 rounded-2xl bg-[#D90012] hover:bg-[#B8000F] active:scale-95 text-white font-black text-xs shadow-md shadow-rose-900/30 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>🚀</span>
                        <span>ส่งรีวิวเพื่อรับ +1 แต้มทันที</span>
                    </button>
                </form>
            </section>

            <!-- ============================================================= -->
            <!-- 6. COMMUNITY REVIEWS FEED                                     -->
            <!-- ============================================================= -->
            <section class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-extrabold text-slate-900">รีวิวจากลูกค้าตัวจริง</h3>
                        <p class="text-[9.5px] text-slate-500">คะแนนเฉลี่ย 4.9 ★ จากลูกค้ากว่า 500+ ท่าน</p>
                    </div>
                    <span class="text-xs font-black text-amber-500 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                        ★ 4.9 / 5.0
                    </span>
                </div>

                <div id="reviews-feed" class="space-y-2">
                    @foreach($reviews as $rev)
                        <div class="bg-white rounded-2xl p-3 border border-slate-200/80 shadow-2xs space-y-1.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-700 flex items-center justify-center text-[10px] font-black">
                                        {{ mb_substr($rev->customer_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-[11px] font-bold text-slate-800 leading-tight">
                                            {{ $rev->customer_name }}
                                        </div>
                                        <div class="text-[9px] text-slate-400">
                                            {{ $rev->created_at ? $rev->created_at->format('d/m/Y') : 'ล่าสุด' }} • <span class="text-emerald-600 font-bold">✓ สั่งซื้อจริง</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-amber-400 text-xs">
                                    {{ str_repeat('★', $rev->rating) }}
                                </div>
                            </div>

                            @if($rev->favorite_dishes)
                                <div class="text-[9.5px] text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md inline-block font-medium">
                                    เมนูโปรด: {{ $rev->favorite_dishes }}
                                </div>
                            @endif

                            <p class="text-xs text-slate-700 leading-relaxed">
                                {{ $rev->comment }}
                            </p>

                            @if($rev->image_url)
                                <div class="mt-1.5 rounded-xl overflow-hidden border border-slate-200 max-w-[200px]">
                                    <img src="{{ $rev->image_url }}" alt="รีวิวรูปภาพ" class="w-full h-28 object-cover cursor-pointer hover:opacity-95" onclick="viewFullImage('{{ $rev->image_url }}')">
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

        </main>

        <!-- ================================================================= -->
        <!-- 7. BOTTOM MOBILE NAVIGATION BAR (3 Clean Tabs)                    -->
        <!-- ================================================================= -->
        <nav class="fixed bottom-0 z-30 w-full max-w-[430px] bg-white/95 backdrop-blur-md border-t border-slate-200 grid grid-cols-3 py-1.5 shadow-lg">
            <a href="{{ route('online.index') }}" class="flex flex-col items-center justify-center text-slate-500 hover:text-slate-900">
                <span class="text-base">🍢</span>
                <span class="text-[9.5px] font-medium mt-0.5">สั่งอาหาร</span>
            </a>
            <a href="{{ route('online.chat') }}" class="flex flex-col items-center justify-center text-slate-500 hover:text-slate-900">
                <span class="text-base">💬</span>
                <span class="text-[9.5px] font-medium mt-0.5">แชทกับร้าน</span>
            </a>
            <a href="{{ route('online.rewards') }}" class="flex flex-col items-center justify-center text-[#D90012] font-bold">
                <span class="text-base">🎁</span>
                <span class="text-[9.5px] mt-0.5">สะสมแต้ม</span>
            </a>
        </nav>

    </div>

    <!-- ===================================================================== -->
    <!-- MODAL 1: PHONE LOOKUP MODAL                                           -->
    <!-- ===================================================================== -->
    <div id="phone-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs hidden items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-5 max-w-xs w-full shadow-2xl relative animate-in fade-in zoom-in-95 duration-150">
            <button type="button" onclick="closePhoneLookupModal()" class="absolute top-3.5 right-3.5 text-slate-400 hover:text-slate-600 text-sm cursor-pointer">✕</button>

            <h3 class="text-sm font-extrabold text-slate-900 mb-1">ตรวจสอบแต้มสะสม</h3>
            <p class="text-xs text-slate-500 mb-3">กรอกเบอร์โทรศัพท์ที่ใช้สั่งซื้ออาหารเพื่อโหลดแต้มของคุณ</p>

            <form onsubmit="handlePhoneLookup(event)" class="space-y-3">
                <input type="tel" id="lookup-phone-input" required placeholder="08x-xxx-xxxx" class="w-full bg-slate-50 text-slate-900 text-sm px-3.5 py-2 rounded-2xl border border-slate-200 focus:border-rose-500 font-mono outline-none">
                <button type="submit" class="w-full py-2.5 rounded-2xl bg-slate-900 hover:bg-black text-white font-bold text-xs shadow-md">
                    ค้นหาแต้มของฉัน
                </button>
            </form>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- MODAL 2: FULLSCREEN IMAGE VIEWER                                      -->
    <!-- ===================================================================== -->
    <div id="image-viewer-modal" class="fixed inset-0 z-50 bg-black/90 hidden items-center justify-center p-4" onclick="closeImageViewer()">
        <img id="image-viewer-img" src="" alt="รูปภาพขนาดเต็ม" class="max-w-full max-h-[85vh] rounded-2xl object-contain shadow-2xl">
        <button type="button" class="absolute top-4 right-4 text-white text-2xl font-bold bg-black/50 w-10 h-10 rounded-full flex items-center justify-center">✕</button>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let currentPhone = "{{ $reward->customer_phone }}";
        let selectedDishes = [];

        // 1. Interactive Star Rating Selector
        function setReviewRating(star) {
            document.getElementById('review-rating-input').value = star;
            const labels = {
                1: '1 ดาว (ต้องปรับปรุง)',
                2: '2 ดาว (พอใช้)',
                3: '3 ดาว (ปานกลาง)',
                4: '4 ดาว (อร่อยดี)',
                5: '5 ดาว (ประทับใจมาก)'
            };
            document.getElementById('rating-label').innerText = labels[star];

            document.querySelectorAll('.star-btn').forEach(btn => {
                const s = parseInt(btn.getAttribute('data-rating'));
                if (s <= star) {
                    btn.classList.add('text-amber-400');
                    btn.classList.remove('text-slate-300');
                } else {
                    btn.classList.remove('text-amber-400');
                    btn.classList.add('text-slate-300');
                }
            });
        }

        // 2. Favorite Dish Chip Toggle
        function toggleDishChip(btn, dish) {
            if (selectedDishes.includes(dish)) {
                selectedDishes = selectedDishes.filter(d => d !== dish);
                btn.classList.remove('bg-rose-600', 'text-white', 'border-rose-600');
                btn.classList.add('bg-slate-100', 'text-slate-700', 'border-slate-200');
                btn.innerText = '+ ' + dish;
            } else {
                selectedDishes.push(dish);
                btn.classList.add('bg-rose-600', 'text-white', 'border-rose-600');
                btn.classList.remove('bg-slate-100', 'text-slate-700', 'border-slate-200');
                btn.innerText = '✓ ' + dish;
            }
            document.getElementById('fav-dishes-input').value = selectedDishes.join(', ');
        }

        // 3. Compress Review Image using HTML5 Canvas & Upload to URL
        async function handleReviewImageSelection(event) {
            const file = event.target.files[0];
            if (!file) return;

            const status = document.getElementById('review-img-status');
            const previewBox = document.getElementById('review-img-preview-box');
            const previewImg = document.getElementById('review-img-preview');

            status.innerText = 'กำลังบีบอัดรูปภาพ...';

            try {
                const compressedBase64 = await compressImageFile(file, 1200, 1200, 0.82);
                previewImg.src = compressedBase64;
                previewBox.classList.remove('hidden');
                status.innerText = 'กำลังแปลงเป็น URL...';

                const response = await fetch("{{ route('online.chat.upload_image') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        image_base64: compressedBase64
                    })
                });

                const data = await response.json();
                if (data.success && data.url) {
                    document.getElementById('review-image-url-input').value = data.url;
                    status.innerText = 'แปลงเป็น URL สำเร็จ ✓';
                } else {
                    alert('อัปโหลดรูปไม่สำเร็จ: ' + (data.message || ''));
                    clearReviewImage();
                }
            } catch (err) {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการบีบอัดรูป: ' + err.message);
                clearReviewImage();
            }
        }

        function compressImageFile(file, maxWidth, maxHeight, quality) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = function (e) {
                    const img = new Image();
                    img.src = e.target.result;
                    img.onload = function () {
                        let w = img.width;
                        let h = img.height;
                        if (w > h) {
                            if (w > maxWidth) {
                                h = Math.round((h * maxWidth) / w);
                                w = maxWidth;
                            }
                        } else {
                            if (h > maxHeight) {
                                w = Math.round((w * maxHeight) / h);
                                h = maxHeight;
                            }
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = w;
                        canvas.height = h;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, w, h);
                        resolve(canvas.toDataURL('image/jpeg', quality));
                    };
                    img.onerror = reject;
                };
                reader.onerror = reject;
            });
        }

        function clearReviewImage() {
            document.getElementById('review-file-input').value = '';
            document.getElementById('review-image-url-input').value = '';
            document.getElementById('review-img-status').innerText = 'ยังไม่ได้เลือกรูป';
            document.getElementById('review-img-preview-box').classList.add('hidden');
        }

        // 4. Submit Review & Earn +1 Point
        async function handleReviewSubmit(event) {
            event.preventDefault();
            const btn = document.getElementById('btn-submit-review');
            btn.disabled = true;
            btn.innerText = 'กำลังบันทึกและเพิ่มแต้ม...';

            const payload = {
                customer_name: document.getElementById('review-name-input').value.trim(),
                customer_phone: document.getElementById('review-phone-input').value.trim(),
                rating: parseInt(document.getElementById('review-rating-input').value),
                comment: document.getElementById('review-comment-input').value.trim(),
                favorite_dishes: document.getElementById('fav-dishes-input').value.trim(),
                image_url: document.getElementById('review-image-url-input').value.trim()
            };

            try {
                const response = await fetch("{{ route('online.rewards.review') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (data.success) {
                    // Confetti Celebration!
                    if (typeof confetti === 'function') {
                        confetti({
                            particleCount: 80,
                            spread: 60,
                            origin: { y: 0.6 }
                        });
                    }

                    alert('🎉 ' + data.message + ' (แต้มของคุณ: ' + data.points + ' แต้ม)');

                    // Reload to update stamp card & reviews feed
                    window.location.href = "{{ route('online.rewards') }}?phone=" + encodeURIComponent(payload.customer_phone);
                } else {
                    alert('เกิดข้อผิดพลาด: ' + (data.message || ''));
                }
            } catch (err) {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการส่งรีวิว: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.innerText = '🚀 ส่งรีวิวเพื่อรับ +1 แต้มทันที';
            }
        }

        // 5. Redeem 5 Points for 1 Free Skewer
        async function redeemFreeStick() {
            if (!confirm('ยืนยันการใช้ 5 แต้มเพื่อแลกรับหม่าล่าฟรี 1 ไม้?')) {
                return;
            }

            const btn = document.getElementById('btn-redeem-reward');
            btn.disabled = true;
            btn.innerText = 'กำลังออกคูปอง...';

            try {
                const response = await fetch("{{ route('online.rewards.redeem') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        phone: currentPhone
                    })
                });

                const data = await response.json();
                if (data.success) {
                    if (typeof confetti === 'function') {
                        confetti({
                            particleCount: 120,
                            spread: 80,
                            origin: { y: 0.5 }
                        });
                    }
                    alert('🎉 ' + data.message + '\nสามารถนำรหัสนี้ไปแสดงที่หน้าร้านหรือใส่ในช่องคูปองสั่งออนไลน์ได้ทันที!');
                    window.location.reload();
                } else {
                    alert(data.message || 'ไม่สามารถแลกรับสิทธิ์ได้');
                }
            } catch (err) {
                console.error(err);
                alert('เกิดข้อผิดพลาด: ' + err.message);
            } finally {
                btn.disabled = false;
            }
        }

        function copyVoucherCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                alert('คัดลอกรหัสคูปอง ' + code + ' เรียบร้อยแล้ว!');
            }).catch(() => {
                prompt('คัดลอกรหัสคูปอง:', code);
            });
        }

        // Phone Lookup Modal
        function openPhoneLookupModal() {
            const modal = document.getElementById('phone-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('lookup-phone-input').focus();
        }

        function closePhoneLookupModal() {
            const modal = document.getElementById('phone-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function handlePhoneLookup(event) {
            event.preventDefault();
            const phone = document.getElementById('lookup-phone-input').value.trim();
            if (!phone) return;
            window.location.href = "{{ route('online.rewards') }}?phone=" + encodeURIComponent(phone);
        }

        function viewFullImage(url) {
            const modal = document.getElementById('image-viewer-modal');
            const img = document.getElementById('image-viewer-img');
            img.src = url;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeImageViewer() {
            const modal = document.getElementById('image-viewer-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</body>
</html>

