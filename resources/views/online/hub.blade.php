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

        /* ซ่อนแถบเลื่อนขึ้นลงข้างๆ ออกทั้งหมด แต่ยังเลื่อนหน้าจอได้ปกติทุกอย่าง */
        html, body {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
        html::-webkit-scrollbar,
        body::-webkit-scrollbar,
        *::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
        }
    </style>
</head>
<body class="bg-[#F4F6F8] text-slate-800 antialiased min-h-screen flex flex-col font-prompt select-none">

    <!-- ========================================================================= -->
    <!-- TOP APPLICATION NAVBAR                                                    -->
    <!-- ========================================================================= -->
    <header class="bg-white border-b border-slate-200/90 text-slate-900 px-4 py-2.5 shrink-0 z-20 shadow-xs">
        <div class="max-w-[1880px] mx-auto flex flex-wrap items-center justify-between gap-3">
            <!-- Left Branding & Title -->
            <div class="flex items-center gap-3">
                <a href="{{ route('pos.index') }}" class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#D90012] to-[#8B000F] flex items-center justify-center shadow-md shadow-rose-950/20 shrink-0 hover:scale-105 transition-transform" title="กลับหน้าแคชเชียร์">
                    <span class="text-white text-lg">🍢</span>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-1.5">
                            <span>หน้าลูกค้า</span>
                            <span class="text-xs font-semibold text-slate-500 font-mono">(Customer 3-in-1 Hub)</span>
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-300">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>ซิงค์ 3 จอเรียลไทม์</span>
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-600 hidden sm:block">รวมระบบสั่งออนไลน์ แชทสดลูกค้า และแต้มสะสม แสดงผลพร้อมกันในหน้าจอเดียว</p>
                </div>
            </div>

            <!-- Right Toolbar Controls -->
            <div class="flex items-center gap-2">
                <button type="button" onclick="refreshAllFrames()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition active:scale-95 border border-slate-200" title="รีเฟรชทั้ง 3 จอ">
                    <svg class="w-3.5 h-3.5 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                    </svg>
                    <span>รีเฟรชทั้งหมด</span>
                </button>

                <button type="button" onclick="toggleFullScreen()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition active:scale-95 border border-slate-200" title="ขยายเต็มหน้าจอ">
                    <svg class="w-3.5 h-3.5 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>
                    </svg>
                    <span class="hidden md:inline">เต็มจอ</span>
                </button>

                <a href="{{ route('pos.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-[#D90012] to-[#B8000F] hover:from-[#B8000F] hover:to-[#900000] text-white text-xs font-bold transition active:scale-95 shadow-sm shadow-rose-950/20">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    <span>กลับ POS</span>
                </a>
            </div>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- 3-PANELS SIDE-BY-SIDE GRID (เรียง 3 แถบพร้อมกัน ฟิตพอดี เล็กลง 10%)       -->
    <!-- ========================================================================= -->
    <main class="flex-1 p-3 sm:p-4 max-w-[1320px] w-full mx-auto flex flex-col justify-center">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6 flex-1 items-start justify-items-center">

            <!-- ================================================================= -->
            <!-- PANEL 1: สั่งออนไลน์ (หน้าสั่งอาหารของลูกค้า)                      -->
            <!-- ================================================================= -->
            <div class="w-full max-w-[387px] flex flex-col items-center">
                <!-- ชื่อหน้าต่างข้อความสีดำเด่น ใหญ่ -->
                <h2 class="text-xl sm:text-2xl font-black text-black tracking-tight leading-none mb-2.5 text-center select-none">
                    1. สั่งออนไลน์
                </h2>
                <!-- Phone Frame (ฟิตพอดี เล็กลง 10%) -->
                <div class="w-full h-[720px] lg:h-[calc((100vh-8.5rem)*0.9)] min-h-[630px] flex flex-col bg-slate-950 rounded-[30px] border-[3px] border-slate-800 shadow-2xl overflow-hidden ring-1 ring-slate-700/60">
                    <iframe id="frame-order" src="{{ route('online.index', ['admin' => 1, 'hub' => 1]) }}" class="w-full h-full border-0 flex-1 bg-white" loading="lazy" title="หน้าสั่งอาหารของลูกค้า"></iframe>
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- PANEL 2: แชทลูกค้า (หน้าระบบแชทสด)                                -->
            <!-- ================================================================= -->
            <div class="w-full max-w-[387px] flex flex-col items-center">
                <!-- ชื่อหน้าต่างข้อความสีดำเด่น ใหญ่ -->
                <h2 class="text-xl sm:text-2xl font-black text-black tracking-tight leading-none mb-2.5 text-center select-none">
                    2. แชทลูกค้า
                </h2>
                <!-- Phone Frame (ฟิตพอดี เล็กลง 10%) -->
                <div class="w-full h-[720px] lg:h-[calc((100vh-8.5rem)*0.9)] min-h-[630px] flex flex-col bg-slate-950 rounded-[30px] border-[3px] border-slate-800 shadow-2xl overflow-hidden ring-1 ring-slate-700/60">
                    <iframe id="frame-chat" src="{{ route('online.chat', ['hub' => 1]) }}" class="w-full h-full border-0 flex-1 bg-white" loading="lazy" title="หน้าระบบแชทสดลูกค้า"></iframe>
                </div>
            </div>

            <!-- ================================================================= -->
            <!-- PANEL 3: แต้มสะสม (หน้าระบบสะสมแต้ม/แลกรางวัล)                   -->
            <!-- ================================================================= -->
            <div class="w-full max-w-[387px] flex flex-col items-center">
                <!-- ชื่อหน้าต่างข้อความสีดำเด่น ใหญ่ -->
                <h2 class="text-xl sm:text-2xl font-black text-black tracking-tight leading-none mb-2.5 text-center select-none">
                    3. แต้มสะสม
                </h2>
                <!-- Phone Frame (ฟิตพอดี เล็กลง 10%) -->
                <div class="w-full h-[720px] lg:h-[calc((100vh-8.5rem)*0.9)] min-h-[630px] flex flex-col bg-slate-950 rounded-[30px] border-[3px] border-slate-800 shadow-2xl overflow-hidden ring-1 ring-slate-700/60">
                    <iframe id="frame-rewards" src="{{ route('online.rewards', ['hub' => 1]) }}" class="w-full h-full border-0 flex-1 bg-white" loading="lazy" title="หน้าระบบสะสมแต้ม"></iframe>
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

