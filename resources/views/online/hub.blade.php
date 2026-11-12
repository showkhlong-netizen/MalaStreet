<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าลูกค้า (Customer Hub 3-in-1) - ร้านหม่าล่าสตรีท</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        prompt: ['Prompt', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            red: '#D90012',
                            dark: '#111317',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Prompt', sans-serif; }
        .hub-frame-wrapper {
            background: #f8fafc;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.04);
        }
        .hub-frame-wrapper iframe {
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
        }
    </style>
</head>
<body class="bg-[#0f1115] text-[#101114] antialiased min-h-screen flex flex-col font-prompt select-none">

    <!-- ========================================================================= -->
    <!-- TOP APPLICATION NAVBAR                                                    -->
    <!-- ========================================================================= -->
    <header class="bg-[#181a20] border-b border-slate-800 text-white px-4 py-2.5 shrink-0 z-20">
        <div class="max-w-[1880px] mx-auto flex flex-wrap items-center justify-between gap-3">
            <!-- Left Branding & Title -->
            <div class="flex items-center gap-3">
                <a href="{{ route('pos.index') }}" class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#D90012] to-[#8B000F] flex items-center justify-center shadow-md shadow-rose-950/40 shrink-0 hover:scale-105 transition-transform" title="กลับหน้าแคชเชียร์">
                    <span class="text-white text-lg">🍢</span>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-base sm:text-lg font-extrabold text-white tracking-tight flex items-center gap-1.5">
                            <span>หน้าลูกค้า</span>
                            <span class="text-xs font-semibold text-slate-400 font-mono">(Customer 3-in-1 Hub)</span>
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>ซิงค์ 3 จอเรียลไทม์</span>
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 hidden sm:block">รวมระบบสั่งออนไลน์ แชทสดลูกค้า และแต้มสะสม แสดงผลพร้อมกันในหน้าจอเดียว</p>
                </div>
            </div>

            <!-- Right Toolbar Controls -->
            <div class="flex items-center gap-2">
                <button type="button" onclick="refreshAllFrames()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium transition active:scale-95 border border-slate-700" title="รีเฟรชทั้ง 3 จอ">
                    <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                    </svg>
                    <span>รีเฟรชทั้งหมด</span>
                </button>

                <button type="button" onclick="toggleFullScreen()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium transition active:scale-95 border border-slate-700" title="ขยายเต็มหน้าจอ">
                    <svg class="w-3.5 h-3.5 text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
                    </svg>
                    <span class="hidden md:inline">เต็มจอ</span>
                </button>

                <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-[#D90012] to-[#B8000F] hover:from-[#B8000F] hover:to-[#900000] text-white text-xs font-bold transition active:scale-95 shadow-sm shadow-rose-950/40">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    <span>กลับ POS</span>
                </a>
            </div>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- 3-PANELS SIDE-BY-SIDE GRID (เรียง 3 แถบพร้อมกันตามตัวอย่างภาพที่ 1)       -->
    <!-- ========================================================================= -->
    <main class="flex-1 p-3 sm:p-4 max-w-[1880px] w-full mx-auto flex flex-col">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3.5 sm:gap-4 flex-1">

            <!-- ================================================================= -->
            <!-- PANEL 1: แท็บ 1: สั่งออนไลน์ (หน้าสั่งอาหารของลูกค้า)              -->
            <!-- ================================================================= -->
            <div class="bg-white rounded-[22px] border-2 border-slate-200/80 shadow-md flex flex-col overflow-hidden transition-all duration-200 hover:border-amber-300">
                <!-- Panel Header (ตรงตามภาพตัวอย่าง) -->
                <div class="px-4 py-3 bg-gradient-to-r from-amber-50/60 via-white to-white border-b border-slate-100 flex items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-amber-100/90 text-amber-700 flex items-center justify-center shrink-0 shadow-2xs">
                            <!-- Phone Ordering Icon -->
                            <svg class="w-5 h-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                <line x1="12" y1="18" x2="12.01" y2="18"></line>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-sm sm:text-[14.5px] font-extrabold text-slate-900 leading-tight truncate">
                                1. สั่งออนไลน์
                            </h2>
                            <p class="text-[11px] text-slate-500 truncate mt-0.5">หน้าสั่งอาหารของลูกค้า (Takeaway & Delivery)</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200 shrink-0">
                        แถบ 1
                    </span>
                </div>

                <!-- Frame Content -->
                <div class="flex-1 hub-frame-wrapper relative min-h-[580px] lg:min-h-[700px] m-2">
                    <iframe id="frame-order" src="{{ route('online.index', ['admin' => 1]) }}" loading="lazy" title="หน้าสั่งอาหารออนไลน์"></iframe>
                </div>

                <!-- Panel Footer Controls -->
                <div class="px-4 py-2 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs shrink-0">
                    <div class="flex items-center gap-1.5 text-slate-600 font-medium text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>แสดงผลสด (โหมด Admin)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="refreshFrame('frame-order')" class="p-1 hover:text-amber-600 text-slate-400 transition" title="รีเฟรชจอนี้">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
                        </button>
                        <a href="{{ route('online.index') }}" target="_blank" class="text-amber-700 hover:text-amber-800 font-bold text-[11px] inline-flex items-center gap-0.5">
                            <span>เปิดแท็บแยก</span>
                            <span>↗</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- PANEL 2: แท็บ 2: แชทลูกค้า (หน้าระบบแชทสด)                        -->
            <!-- ================================================================= -->
            <div class="bg-white rounded-[22px] border-2 border-slate-200/80 shadow-md flex flex-col overflow-hidden transition-all duration-200 hover:border-sky-300">
                <!-- Panel Header (ตรงตามภาพตัวอย่าง) -->
                <div class="px-4 py-3 bg-gradient-to-r from-sky-50/60 via-white to-white border-b border-slate-100 flex items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-sky-100/90 text-sky-700 flex items-center justify-center shrink-0 shadow-2xs">
                            <!-- Chat Bubble Icon -->
                            <svg class="w-5 h-5 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-sm sm:text-[14.5px] font-extrabold text-slate-900 leading-tight truncate">
                                2. แชทลูกค้า
                            </h2>
                            <p class="text-[11px] text-slate-500 truncate mt-0.5">หน้าระบบแชทสด & ตอบอัตโนมัติ AI (Live Chat)</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-100 text-sky-800 border border-sky-200 shrink-0">
                        แถบ 2
                    </span>
                </div>

                <!-- Frame Content -->
                <div class="flex-1 hub-frame-wrapper relative min-h-[580px] lg:min-h-[700px] m-2">
                    <iframe id="frame-chat" src="{{ route('online.chat') }}" loading="lazy" title="หน้าระบบแชทสด"></iframe>
                </div>

                <!-- Panel Footer Controls -->
                <div class="px-4 py-2 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs shrink-0">
                    <div class="flex items-center gap-1.5 text-slate-600 font-medium text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-sky-500 animate-pulse"></span>
                        <span>แชทสดเรียลไทม์ (Live Support)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="refreshFrame('frame-chat')" class="p-1 hover:text-sky-600 text-slate-400 transition" title="รีเฟรชจอนี้">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
                        </button>
                        <a href="{{ route('online.chat') }}" target="_blank" class="text-sky-700 hover:text-sky-800 font-bold text-[11px] inline-flex items-center gap-0.5">
                            <span>เปิดแท็บแยก</span>
                            <span>↗</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- PANEL 3: แท็บ 3: แต้มสะสม (หน้าระบบสะสมแต้ม/แลกรางวัล)            -->
            <!-- ================================================================= -->
            <div class="bg-white rounded-[22px] border-2 border-slate-200/80 shadow-md flex flex-col overflow-hidden transition-all duration-200 hover:border-rose-300">
                <!-- Panel Header (ตรงตามภาพตัวอย่าง) -->
                <div class="px-4 py-3 bg-gradient-to-r from-rose-50/60 via-white to-white border-b border-slate-100 flex items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-rose-100/90 text-rose-700 flex items-center justify-center shrink-0 shadow-2xs">
                            <!-- Gift Box Rewards Icon -->
                            <svg class="w-5 h-5 text-rose-600" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 7h-2.087A3.989 3.989 0 0 0 18 6a4 4 0 0 0-7.071-2.543A3.99 3.99 0 0 0 4.087 7H2a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h1v8a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-8h1a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1zm-6-3a2 2 0 1 1 2 2h-2zm-6 2a2 2 0 1 1 2-2v2zm-5 4h8v2H3zm10 0h8v2h-8zm-8 4h6v7H5zm8 7v-7h6v7z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-sm sm:text-[14.5px] font-extrabold text-slate-900 leading-tight truncate">
                                3. แต้มสะสม
                            </h2>
                            <p class="text-[11px] text-slate-500 truncate mt-0.5">หน้าระบบสะสมแต้ม/แลกรางวัล & รีวิว (5 ฟรี 1)</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200 shrink-0">
                        แถบ 3
                    </span>
                </div>

                <!-- Frame Content -->
                <div class="flex-1 hub-frame-wrapper relative min-h-[580px] lg:min-h-[700px] m-2">
                    <iframe id="frame-rewards" src="{{ route('online.rewards') }}" loading="lazy" title="หน้าระบบสะสมแต้ม"></iframe>
                </div>

                <!-- Panel Footer Controls -->
                <div class="px-4 py-2 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs shrink-0">
                    <div class="flex items-center gap-1.5 text-slate-600 font-medium text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                        <span>ระบบแต้มสมาชิก (Loyalty Hub)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="refreshFrame('frame-rewards')" class="p-1 hover:text-rose-600 text-slate-400 transition" title="รีเฟรชจอนี้">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/></svg>
                        </button>
                        <a href="{{ route('online.rewards') }}" target="_blank" class="text-rose-700 hover:text-rose-800 font-bold text-[11px] inline-flex items-center gap-0.5">
                            <span>เปิดแท็บแยก</span>
                            <span>↗</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Scripts -->
    <script>
        function refreshFrame(frameId) {
            const frame = document.getElementById(frameId);
            if (frame) {
                frame.src = frame.src;
            }
        }

        function refreshAllFrames() {
            refreshFrame('frame-order');
            refreshFrame('frame-chat');
            refreshFrame('frame-rewards');
        }

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.log('Error attempting to enable fullscreen:', err);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    </script>
</body>
</html>
