<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>เข้าสู่ระบบการทำงาน - หม่าล่าสตรีท (Mala Street POS)</title>

    <!-- Google Fonts: Prompt & Sarabun -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Prompt', 'Sarabun', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            500: '#f43f5e',
                            600: '#e11d48',
                            700: '#be123c',
                            800: '#9f1239',
                            900: '#881337',
                            950: '#4c0519',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Prompt', 'Sarabun', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-red-950 text-slate-800 flex flex-col items-center justify-center p-4 sm:p-6 relative antialiased selection:bg-red-600 selection:text-white">

    <!-- Background Atmospheric Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-red-600/15 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-amber-600/10 rounded-full blur-3xl"></div>
    </div>

    <!-- Main Container -->
    <div class="w-full max-w-md relative z-10 space-y-4">

        <!-- Mala Street Brand Logo & Subtitle -->
        <div class="flex items-center justify-center gap-3 text-white mb-2">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-red-600 via-rose-600 to-amber-500 flex items-center justify-center shadow-lg shadow-red-900/40 border border-white/20">
                <span class="text-2xl">🍢</span>
            </div>
            <div>
                <h2 class="text-lg font-black tracking-tight leading-tight">หม่าล่าสตรีท</h2>
                <p class="text-[11px] text-red-300 font-medium tracking-wide">Mala Street POS &amp; Cloud System</p>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- LOGIN CARD (Matches Image 3 Exactly)                              -->
        <!-- ================================================================= -->
        <div class="bg-white rounded-[28px] sm:rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-200/90 animate-in fade-in zoom-in duration-200 space-y-5">
            
            <!-- Card Title (Matches Image 3) -->
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight text-left">
                เข้าสู่ระบบการทำงาน
            </h1>

            <!-- Yellow Warning Banner (Shows on timeout or when redirected from auto-logout) -->
            @if(request('reason') === 'timeout' || session('timeout_alert'))
                <div class="p-3.5 sm:p-4 rounded-2xl bg-[#FFFBEB] border border-[#FDE68A] text-[#92400E] flex items-start gap-3 shadow-2xs animate-in slide-in-from-top-2 duration-200">
                    <span class="w-6 h-6 rounded-lg bg-amber-200/80 text-amber-900 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <p class="text-xs sm:text-[13px] font-bold leading-relaxed">
                        คุณถูกออกจากระบบอัตโนมัติเนื่องจากไม่มีการเคลื่อนไหวเกินกำหนด เพื่อความปลอดภัยของข้อมูล
                    </p>
                </div>
            @endif

            <!-- General Error / Status Alert -->
            @if(session('error'))
                <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="p-3 rounded-2xl bg-slate-100 border border-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-2">
                    <span>ℹ️</span>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Field 1: อีเมลผู้ใช้งาน (Matches Image 3) -->
                <div class="space-y-1.5 text-left">
                    <label class="block text-xs sm:text-sm font-bold text-slate-800">
                        อีเมลผู้ใช้งาน
                    </label>
                    <input type="email" 
                           id="login-email"
                           name="email" 
                           value="{{ old('email', request('email', 'salaemaemunib@gmail.com')) }}" 
                           placeholder="กรอกอีเมลเข้าสู่ระบบ" 
                           required 
                           autofocus
                           class="w-full px-4 py-3 sm:py-3.5 rounded-2xl border-2 border-slate-200 focus:border-red-600 focus:ring-4 focus:ring-red-500/10 text-slate-900 text-sm font-semibold transition-all outline-none placeholder:text-slate-400 placeholder:font-normal">
                </div>

                <!-- Field 2: รหัสผ่าน (Matches Image 3 with Eye toggle) -->
                <div class="space-y-1.5 text-left">
                    <label class="block text-xs sm:text-sm font-bold text-slate-800">
                        รหัสผ่าน
                    </label>
                    <div class="relative">
                        <input type="password" 
                               id="login-password" 
                               name="password" 
                               value="password123"
                               placeholder="•••••••••" 
                               required 
                               class="w-full px-4 py-3 sm:py-3.5 rounded-2xl border-2 border-slate-200 focus:border-red-600 focus:ring-4 focus:ring-red-500/10 text-slate-900 text-sm font-semibold transition-all outline-none placeholder:text-slate-400 pr-12">
                        <button type="button" 
                                onclick="togglePasswordVisibility()" 
                                title="แสดง/ซ่อนรหัสผ่าน"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition-colors p-1 cursor-pointer">
                            <span id="eye-icon" class="flex items-center justify-center">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Checkbox: จดจำการเข้าสู่ระบบ (Matches Image 3) -->
                <div class="flex items-center justify-between pt-0.5">
                    <label class="flex items-center gap-2.5 text-xs sm:text-sm text-slate-600 font-medium cursor-pointer select-none">
                        <input type="checkbox" 
                               name="remember" 
                               value="1"
                               checked 
                               class="w-4 h-4 rounded text-red-600 border-slate-300 focus:ring-red-500 cursor-pointer">
                        <span>จดจำการเข้าสู่ระบบ</span>
                    </label>
                </div>

                <!-- Primary Submit Button: เข้าสู่ระบบ (Themed with Mala Street brand colors) -->
                <button type="submit" 
                        class="w-full py-3.5 sm:py-4 rounded-2xl bg-gradient-to-r from-red-600 via-rose-600 to-amber-600 hover:from-red-700 hover:to-amber-700 active:scale-[0.98] text-white font-black text-sm sm:text-base shadow-lg shadow-red-600/30 transition-all cursor-pointer flex items-center justify-center gap-2">
                    <span>เข้าสู่ระบบ</span>
                </button>

                <!-- Divider (Matches Image 3) -->
                <div class="border-t border-slate-100 pt-3"></div>

                <!-- Secondary Option (Matches Image 3) -->
                <div class="space-y-2.5 text-center">
                    <p class="text-xs sm:text-sm text-slate-500 font-medium">
                        พนักงานใหม่ที่ยังไม่มีบัญชีเข้าใช้งาน?
                    </p>
                    
                    <a href="{{ route('admin.access.index', ['tab' => 'register']) }}" 
                       class="w-full py-3 sm:py-3.5 px-4 rounded-2xl border border-slate-200/90 bg-white hover:bg-slate-50 active:scale-[0.98] text-slate-800 font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition-all cursor-pointer shadow-2xs hover:border-slate-300">
                        <span class="text-amber-600 font-black text-sm">👤+</span>
                        <span>กดลงทะเบียน เพื่อสร้างอีเมลพนักงานบัญชีใหม่</span>
                    </a>
                </div>

            </form>

        </div>

        <!-- Quick 1-Click Account Switcher for Testing / Convenience -->
        @if(!empty($quickUsers))
            <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl p-3 border border-slate-800/80 text-white space-y-2">
                <div class="flex items-center justify-between text-[11px] text-slate-400 px-1">
                    <span class="font-bold flex items-center gap-1.5">
                        <span>⚡</span>
                        <span>เลือกบัญชีที่บันทึกไว้เข้าสู่ระบบทันที:</span>
                    </span>
                    <span class="text-[10px] text-slate-500">(รหัสผ่าน: password123)</span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    @foreach($quickUsers as $qu)
                        <button type="button" 
                                onclick="fillQuickUser('{{ $qu['email'] }}')" 
                                class="flex items-center gap-2 p-2 rounded-xl bg-slate-800/90 hover:bg-slate-700 border border-slate-700/60 text-left transition-all cursor-pointer group">
                            @if(!empty($qu['avatar']))
                                <img src="{{ $qu['avatar'] }}" alt="{{ $qu['name'] }}" class="w-6 h-6 rounded-full object-cover shrink-0">
                            @else
                                <div class="w-6 h-6 rounded-full {{ $qu['color'] ?? 'bg-rose-600' }} text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                                    {{ $qu['initials'] ?? 'US' }}
                                </div>
                            @endif
                            <div class="overflow-hidden">
                                <p class="text-xs font-bold text-white group-hover:text-amber-300 truncate leading-tight">{{ $qu['name'] }}</p>
                                <p class="text-[9.5px] text-slate-400 truncate">{{ $qu['role_label'] ?? $qu['role'] }}</p>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Footer Info -->
        <div class="text-center text-[11px] text-slate-500 font-medium">
            <span>© {{ date('Y') }} หม่าล่าสตรีท ระบบจัดการสิทธิ์และการเข้าถึงที่ปลอดภัย</span>
        </div>

    </div>

    <!-- Interactive Script -->
    <script>
        function togglePasswordVisibility() {
            const pwd = document.getElementById('login-password');
            const icon = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.innerHTML = `<svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>`;
            } else {
                pwd.type = 'password';
                icon.innerHTML = `<svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>`;
            }
        }

        function fillQuickUser(email) {
            const emailInput = document.getElementById('login-email');
            const pwdInput = document.getElementById('login-password');
            if (emailInput) emailInput.value = email;
            if (pwdInput) pwdInput.value = 'password123';
        }
    </script>
</body>
</html>

