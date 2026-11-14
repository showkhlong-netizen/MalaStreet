<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>เข้าสู่ระบบการทำงาน - หม่าล่าสตรีท (Mala Street POS)</title>

    <!-- Non-blocking async Google Font (Instant first paint using system Thai UI fonts even on slow internet) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&display=swap"></noscript>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            min-height: 100vh;
            font-family: 'Prompt', 'Noto Sans Thai', 'Leelawadee UI', 'Tahoma', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        body {
            background: linear-gradient(135deg, #020617 0%, #0f172a 55%, #450a0a 100%);
            color: #1e293b;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 12px;
            position: relative;
        }
        ::selection { background: #dc2626; color: #fff; }

        /* Atmospheric Background */
        .bg-glows {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }
        .bg-glow-1 {
            position: absolute;
            top: -110px;
            right: -110px;
            width: 260px;
            height: 260px;
            background: rgba(220, 38, 38, 0.15);
            border-radius: 9999px;
            filter: blur(55px);
        }
        .bg-glow-2 {
            position: absolute;
            bottom: -110px;
            left: -110px;
            width: 260px;
            height: 260px;
            background: rgba(217, 119, 6, 0.10);
            border-radius: 9999px;
            filter: blur(55px);
        }

        /* Main Container (~30% smaller than original 448px -> 314px) */
        .login-container {
            width: 100%;
            max-width: 314px;
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* Brand Header (~30% smaller) */
        .brand-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            color: #ffffff;
            margin-bottom: 2px;
        }
        .brand-icon {
            width: 32px;
            height: 32px;
            border-radius: 11px;
            background: linear-gradient(135deg, #dc2626 0%, #e11d48 55%, #f59e0b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 14px rgba(127, 29, 29, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 16px;
            line-height: 1;
            flex-shrink: 0;
        }
        .brand-title {
            font-size: 13.5px;
            font-weight: 700;
            letter-spacing: -0.01em;
            line-height: 1.2;
        }
        .brand-subtitle {
            font-size: 8.5px;
            color: #fca5a5;
            font-weight: 500;
            letter-spacing: 0.02em;
        }

        /* White Login Card (~30% smaller paddings & radii) */
        .login-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 18px 20px;
            box-shadow: 0 20px 38px -10px rgba(0, 0, 0, 0.45);
            border: 1px solid rgba(226, 232, 240, 0.9);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .card-title {
            font-size: 15.5px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
            text-align: left;
        }

        /* Alerts */
        .alert-timeout {
            padding: 9px 11px;
            border-radius: 11px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .alert-timeout-icon {
            width: 18px;
            height: 18px;
            border-radius: 6px;
            background: rgba(253, 230, 138, 0.8);
            color: #78350f;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .alert-timeout p {
            font-size: 10px;
            font-weight: 600;
            line-height: 1.45;
        }
        .alert-error {
            padding: 9px 11px;
            border-radius: 11px;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #9f1239;
            font-size: 10.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .alert-info {
            padding: 8px 11px;
            border-radius: 11px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #334155;
            font-size: 10.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Form Elements (~30% smaller) */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .field-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
            text-align: left;
        }
        .field-label {
            display: block;
            font-size: 10.5px;
            font-weight: 700;
            color: #1e293b;
        }
        .field-input-wrap {
            position: relative;
        }
        .field-input {
            width: 100%;
            padding: 8px 11px;
            border-radius: 11px;
            border: 1.5px solid #e2e8f0;
            color: #0f172a;
            font-family: inherit;
            font-size: 11px;
            font-weight: 600;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            background: #ffffff;
        }
        .field-input.has-toggle {
            padding-right: 34px;
        }
        .field-input:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
        }
        .field-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }
        .pwd-toggle-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: 0;
            color: #94a3b8;
            cursor: pointer;
            padding: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pwd-toggle-btn:hover { color: #334155; }

        .remember-row {
            display: flex;
            align-items: center;
            padding-top: 1px;
        }
        .remember-label {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 10.5px;
            color: #475569;
            font-weight: 500;
            cursor: pointer;
            user-select: none;
        }
        .remember-checkbox {
            width: 13px;
            height: 13px;
            accent-color: #dc2626;
            cursor: pointer;
        }

        /* Primary Submit Button (~30% smaller) */
        .btn-submit {
            width: 100%;
            padding: 9px 12px;
            border-radius: 11px;
            border: 0;
            background: linear-gradient(90deg, #dc2626 0%, #e11d48 50%, #d97706 100%);
            color: #ffffff;
            font-family: inherit;
            font-size: 11.5px;
            font-weight: 700;
            box-shadow: 0 6px 14px rgba(220, 38, 38, 0.28);
            cursor: pointer;
            transition: transform 0.1s ease, filter 0.15s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-submit:hover { filter: brightness(0.95); }
        .btn-submit:active { transform: scale(0.98); }
        .btn-submit:disabled { opacity: 0.75; cursor: wait; }

        .divider {
            border-top: 1px solid #f1f5f9;
            margin-top: 2px;
        }

        /* Secondary Register Section (~30% smaller) */
        .register-section {
            display: flex;
            flex-direction: column;
            gap: 7px;
            text-align: center;
        }
        .register-hint {
            font-size: 10px;
            color: #64748b;
            font-weight: 500;
        }
        .btn-register {
            width: 100%;
            padding: 8px 10px;
            border-radius: 11px;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: #ffffff;
            color: #1e293b;
            font-family: inherit;
            font-size: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            transition: background-color 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
        }
        .btn-register:hover { background: #f8fafc; border-color: #cbd5e1; }
        .btn-register:active { transform: scale(0.98); }
        .btn-register-icon { color: #d97706; font-weight: 700; font-size: 11px; }

        /* Footer (~30% smaller) */
        .login-footer {
            text-align: center;
            font-size: 8.5px;
            color: #64748b;
            font-weight: 500;
        }

        /* Register Modal Styles (Self-contained, zero external dependencies) */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 50;
            background: rgba(2, 6, 23, 0.75);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 14px;
            overflow-y: auto;
        }
        .modal-backdrop.is-open { display: flex; }
        .modal-card {
            position: relative;
            background: #ffffff;
            border-radius: 20px;
            max-width: 400px;
            width: 100%;
            padding: 18px 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid #f1f5f9;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin: auto;
        }
        .modal-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
            gap: 10px;
        }
        .modal-header-left { display: flex; align-items: center; gap: 10px; }
        .modal-header-icon {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(45deg, #f59e0b, #e11d48);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 16px;
            font-weight: 700;
        }
        .modal-title { font-size: 14px; font-weight: 700; color: #0f172a; line-height: 1.25; }
        .modal-subtitle { font-size: 10.5px; color: #64748b; font-weight: 500; margin-top: 2px; }
        .modal-close-btn {
            width: 28px;
            height: 28px;
            border-radius: 9999px;
            border: 0;
            background: #f1f5f9;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .modal-close-btn:hover { background: #e2e8f0; color: #1e293b; }

        .avatar-box {
            padding: 10px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .avatar-box-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 700;
            color: #1e293b;
        }
        .avatar-badge-pill {
            font-size: 9.5px;
            color: #4f46e5;
            font-weight: 600;
            background: #eef2ff;
            padding: 2px 7px;
            border-radius: 9999px;
            border: 1px solid #e0e7ff;
        }
        .avatar-row { display: flex; align-items: center; gap: 10px; }
        .avatar-preview-wrap { position: relative; flex-shrink: 0; }
        .avatar-preview-img {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            object-fit: cover;
            background: #ffffff;
            border: 1.5px dashed #cbd5e1;
            padding: 2px;
            display: block;
        }
        .avatar-check-badge {
            position: absolute;
            bottom: -3px;
            right: -3px;
            width: 16px;
            height: 16px;
            border-radius: 9999px;
            background: #10b981;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            font-weight: 700;
        }
        .btn-pick-photo {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 9px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #334155;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-pick-photo:hover { background: #f1f5f9; }
        .avatar-hint { font-size: 9.5px; color: #94a3b8; line-height: 1.3; margin-top: 3px; }

        .modal-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        @media (max-width: 480px) { .modal-grid-2 { grid-template-columns: 1fr; } }

        .modal-input {
            width: 100%;
            padding: 8px 10px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            font-family: inherit;
            font-size: 11.5px;
            font-weight: 600;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
        }
        .modal-input:focus { border-color: #3b82f6; background: #ffffff; }

        .modal-security-note {
            padding: 9px 10px;
            border-radius: 12px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            font-size: 10px;
            color: #78350f;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            line-height: 1.4;
        }
        .modal-actions { display: flex; align-items: center; justify-content: flex-end; gap: 8px; padding-top: 4px; }
        .btn-cancel {
            padding: 8px 12px;
            border-radius: 10px;
            border: 0;
            background: #f1f5f9;
            color: #334155;
            font-family: inherit;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
        }
        .btn-cancel:hover { background: #e2e8f0; }
        .btn-modal-submit {
            padding: 8px 14px;
            border-radius: 10px;
            border: 0;
            background: linear-gradient(90deg, #dc2626, #e11d48, #d97706);
            color: #ffffff;
            font-family: inherit;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .modal-alert { padding: 9px 11px; border-radius: 11px; font-size: 11px; font-weight: 700; }
        .modal-alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .modal-alert-error { background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; }
        .hidden { display: none !important; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { display: inline-block; animation: spin 1s linear infinite; }
    </style>
</head>
<body>

    <!-- Background Atmospheric Elements -->
    <div class="bg-glows" aria-hidden="true">
        <div class="bg-glow-1"></div>
        <div class="bg-glow-2"></div>
    </div>

    <!-- Main Container (30% smaller, original structure preserved) -->
    <div class="login-container">

        <!-- Mala Street Brand Logo & Subtitle -->
        <div class="brand-header">
            <div class="brand-icon">🍢</div>
            <div>
                <div class="brand-title">หม่าล่าสตรีท</div>
                <div class="brand-subtitle">Mala Street POS &amp; Cloud System</div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- LOGIN CARD                                                        -->
        <!-- ================================================================= -->
        <div class="login-card">

            <h1 class="card-title">เข้าสู่ระบบการทำงาน</h1>

            <!-- Yellow Warning Banner (Timeout / Auto-Logout) -->
            @if(request('reason') === 'timeout' || session('timeout_alert'))
                <div class="alert-timeout">
                    <span class="alert-timeout-icon">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    <p>คุณถูกออกจากระบบอัตโนมัติเนื่องจากไม่มีการเคลื่อนไหวเกินกำหนด เพื่อความปลอดภัยของข้อมูล</p>
                </div>
            @endif

            <!-- Error Alert -->
            @if(session('error'))
                <div class="alert-error">
                    <svg width="13" height="13" fill="none" stroke="#e11d48" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Info Alert -->
            @if(session('info'))
                <div class="alert-info">
                    <span>ℹ️</span>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form action="{{ route('login.submit') }}" method="POST" class="login-form" id="login-main-form">
                @csrf
                <input type="hidden" name="redirect" value="{{ request('redirect', old('redirect')) }}">

                <!-- Field 1: อีเมลผู้ใช้งาน -->
                <div class="field-group">
                    <label for="login-email" class="field-label">อีเมลผู้ใช้งาน</label>
                    <input type="email"
                           id="login-email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="กรอกอีเมลเข้าสู่ระบบ"
                           required
                           autofocus
                           class="field-input">
                </div>

                <!-- Field 2: รหัสผ่าน -->
                <div class="field-group">
                    <label for="login-password" class="field-label">รหัสผ่าน</label>
                    <div class="field-input-wrap">
                        <input type="password"
                               id="login-password"
                               name="password"
                               placeholder="•••••••••"
                               required
                               class="field-input has-toggle">
                        <button type="button"
                                onclick="togglePasswordVisibility()"
                                title="แสดง/ซ่อนรหัสผ่าน"
                                class="pwd-toggle-btn">
                            <span id="eye-icon" style="display:flex;align-items:center;justify-content:center;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Checkbox: จดจำการเข้าสู่ระบบ -->
                <div class="remember-row">
                    <label class="remember-label">
                        <input type="checkbox" id="login-remember" name="remember" value="1" class="remember-checkbox">
                        <span>จดจำการเข้าสู่ระบบ</span>
                    </label>
                </div>

                <!-- Primary Submit Button -->
                <button type="submit" id="btn-login-submit" class="btn-submit">
                    <span>เข้าสู่ระบบ</span>
                </button>

                <!-- Divider -->
                <div class="divider"></div>

                <!-- Secondary Option -->
                <div class="register-section">
                    <p class="register-hint">พนักงานใหม่ที่ยังไม่มีบัญชีเข้าใช้งาน?</p>
                    <button type="button" onclick="openRegisterModal()" class="btn-register">
                        <span class="btn-register-icon">👤+</span>
                        <span>กดลงทะเบียน เพื่อสร้างอีเมลพนักงานบัญชีใหม่</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer Info -->
        <div class="login-footer">
            <span>© {{ date('Y') }} หม่าล่าสตรีท ระบบจัดการสิทธิ์และการเข้าถึงที่ปลอดภัย</span>
        </div>

    </div>

    <!-- ===================================================================== -->
    <!-- ป๊อปอัปขอลงทะเบียนสร้างอีเมลพนักงานบัญชีใหม่ (บีบอัดภาพและรอแอดมินอนุมัติ) -->
    <!-- ===================================================================== -->
    <div id="modal-register-request" class="modal-backdrop">
        <div class="modal-card">

            <!-- Header -->
            <div class="modal-header">
                <div class="modal-header-left">
                    <span class="modal-header-icon">👤+</span>
                    <div>
                        <h3 class="modal-title">ลงทะเบียนสร้างบัญชีใหม่</h3>
                        <p class="modal-subtitle">กรอกข้อมูลเพื่อสร้างบัญชีและรหัสผ่าน (พร้อมเข้าใช้งานได้ทันที)</p>
                    </div>
                </div>
                <button type="button" onclick="closeRegisterModal()" class="modal-close-btn">✕</button>
            </div>

            <!-- Form -->
            <form id="form-popup-register" onsubmit="submitPopupRegister(event)" class="login-form">
                @csrf

                <!-- 1. ภาพถ่ายโปรไฟล์ -->
                <div class="avatar-box">
                    <div class="avatar-box-top">
                        <span>📷 ภาพถ่ายโปรไฟล์พนักงาน</span>
                        <span class="avatar-badge-pill">บีบอัดเป็น URL อัตโนมัติ</span>
                    </div>
                    <div class="avatar-row">
                        <div class="avatar-preview-wrap">
                            <img id="popup-avatar-preview"
                                 src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23CBD5E1'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E"
                                 alt="Avatar Preview"
                                 class="avatar-preview-img">
                            <div id="popup-avatar-badge" class="avatar-check-badge hidden">✓</div>
                        </div>
                        <div style="flex:1">
                            <label for="popup-avatar-input" class="btn-pick-photo">
                                <span>+ เลือกรูปภาพ...</span>
                            </label>
                            <input type="file" id="popup-avatar-input" accept="image/*" onchange="compressAndPreviewAvatar(this)" class="hidden">
                            <input type="hidden" id="popup-avatar-base64" name="avatar_base64">
                            <p class="avatar-hint">ระบบจะบีบอัดภาพให้อัตโนมัติเป็น URL ไม่กินเนื้อที่ และโหลดรวดเร็ว</p>
                        </div>
                    </div>
                </div>

                <!-- 2. ชื่อ-สกุล -->
                <div class="field-group">
                    <label for="popup-reg-name" class="field-label">ชื่อ-สกุล หรือชื่อเรียก <span style="color:#f43f5e">*</span></label>
                    <input type="text" id="popup-reg-name" name="name" required placeholder="เช่น วาสนา บัญชีหน้าร้าน หรือ สมชาย" class="modal-input">
                </div>

                <!-- 3. อีเมลผู้ใช้งาน -->
                <div class="field-group">
                    <label for="popup-reg-email" class="field-label">อีเมลผู้ใช้งาน (Email) <span style="color:#f43f5e">*</span></label>
                    <input type="email" id="popup-reg-email" name="email" required placeholder="เช่น wasana.account@malastreet.com" class="modal-input">
                </div>

                <!-- 4. รหัสผ่านที่จะตั้ง -->
                <div class="field-group">
                    <label for="popup-reg-password" class="field-label">พาสเวิร์ดที่จะตั้ง (Password) <span style="color:#f43f5e">*</span></label>
                    <div class="field-input-wrap">
                        <input type="password" id="popup-reg-password" name="password" required placeholder="กำหนดรหัสผ่าน / PIN ได้ง่ายๆ (เช่น 1122, 1234)" class="modal-input" style="padding-right:34px;">
                        <button type="button" onclick="togglePopupPasswordVisibility()" class="pwd-toggle-btn">
                            <span id="popup-eye-icon">👁️</span>
                        </button>
                    </div>
                </div>

                <!-- 5. เบอร์โทรศัพท์ & ตำแหน่งงาน -->
                <div class="modal-grid-2">
                    <div class="field-group">
                        <label for="popup-reg-phone" class="field-label">เบอร์โทรศัพท์</label>
                        <input type="tel" id="popup-reg-phone" name="phone" placeholder="เช่น 081-234-5678" class="modal-input">
                    </div>
                    <div class="field-group">
                        <label for="popup-reg-position" class="field-label">ตำแหน่งงาน / แผนก</label>
                        <select id="popup-reg-position" name="position" class="modal-input" style="cursor:pointer;">
                            <option value="พนักงานบัญชี" selected>พนักงานบัญชี (Accountant)</option>
                            <option value="ผู้ดูแลระบบ (Admin)">ผู้ดูแลระบบ / ผู้ช่วยแอดมิน (Admin)</option>
                            <option value="พนักงานบริการหน้าร้าน">พนักงานบริการหน้าร้าน (Service)</option>
                            <option value="พนักงานครัว">พนักงานครัว (Kitchen)</option>
                        </select>
                    </div>
                </div>

                <!-- กล่องข้อความแจ้งเตือนเงื่อนไข -->
                <div class="modal-security-note">
                    <span style="font-size:14px;flex-shrink:0;">🛡️</span>
                    <div><strong>สร้างและใช้งานได้ทันที:</strong> เมื่อกดลงทะเบียนแล้ว <strong>ระบบจะบันทึกข้อมูลและรหัสผ่านทันที</strong> สามารถใช้อีเมลและรหัสผ่านนี้เข้าสู่ระบบได้เลย</div>
                </div>

                <!-- Response Alert Area -->
                <div id="popup-register-alert" class="modal-alert hidden"></div>

                <!-- Action Buttons -->
                <div class="modal-actions">
                    <button type="button" onclick="closeRegisterModal()" class="btn-cancel">ยกเลิก</button>
                    <button type="submit" id="btn-popup-submit-register" class="btn-modal-submit">
                        <span>✓</span>
                        <span>กดลงทะเบียน และสร้างบัญชีใช้งานทันที</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            // Instant submit feedback on slow networks & prevent duplicate submissions
            const form = document.getElementById('login-main-form');
            const submitBtn = document.getElementById('btn-login-submit');
            if (form && submitBtn) {
                form.addEventListener('submit', function () {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spin">⏳</span><span>กำลังเข้าสู่ระบบ...</span>';
                });
            }
        })();

        function togglePasswordVisibility() {
            const pwd = document.getElementById('login-password');
            const icon = document.getElementById('eye-icon');
            if (!pwd || !icon) return;
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.innerHTML = '<svg width="14" height="14" fill="none" stroke="#334155" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>';
            } else {
                pwd.type = 'password';
                icon.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>';
            }
        }

        function togglePopupPasswordVisibility() {
            const pwd = document.getElementById('popup-reg-password');
            const icon = document.getElementById('popup-eye-icon');
            if (!pwd || !icon) return;
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.textContent = '🙈';
            } else {
                pwd.type = 'password';
                icon.textContent = '👁️';
            }
        }

        function openRegisterModal() {
            const modal = document.getElementById('modal-register-request');
            if (modal) modal.classList.add('is-open');
        }

        function closeRegisterModal() {
            const modal = document.getElementById('modal-register-request');
            if (modal) modal.classList.remove('is-open');
        }

        function compressAndPreviewAvatar(input) {
            if (!input.files || !input.files[0]) return;
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = new Image();
                img.onload = function () {
                    const canvas = document.createElement('canvas');
                    const maxDim = 360;
                    let w = img.width, h = img.height;
                    if (w > h && w > maxDim) { h = Math.round((h * maxDim) / w); w = maxDim; }
                    else if (h >= w && h > maxDim) { w = Math.round((w * maxDim) / h); h = maxDim; }
                    canvas.width = w;
                    canvas.height = h;
                    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                    const dataUrl = canvas.toDataURL('image/jpeg', 0.82);
                    document.getElementById('popup-avatar-preview').src = dataUrl;
                    document.getElementById('popup-avatar-base64').value = dataUrl;
                    document.getElementById('popup-avatar-badge').classList.remove('hidden');
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }

        async function submitPopupRegister(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-popup-submit-register');
            const alertBox = document.getElementById('popup-register-alert');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spin">⏳</span><span>กำลังส่งคำขอลงทะเบียน...</span>';
            if (alertBox) alertBox.classList.add('hidden');

            const form = document.getElementById('form-popup-register');
            const formData = new FormData(form);

            try {
                const res = await fetch("{{ route('register.request') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const json = await res.json();
                if (json.success) {
                    if (alertBox) {
                        alertBox.className = 'modal-alert modal-alert-success';
                        alertBox.innerHTML = '✅ ' + json.message;
                    }
                    btn.innerHTML = '<span>✓ สร้างบัญชีสำเร็จ พร้อมเข้าใช้งานทันที!</span>';
                    if (json.email) {
                        const loginEmail = document.getElementById('login-email');
                        const loginPwd = document.getElementById('login-password');
                        if (loginEmail) loginEmail.value = json.email;
                        if (loginPwd) loginPwd.value = '';
                    }
                    form.reset();
                    document.getElementById('popup-avatar-preview').src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23CBD5E1'%3E%3Cpath d='M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'/%3E%3C/svg%3E";
                    document.getElementById('popup-avatar-badge').classList.add('hidden');

                    setTimeout(function () {
                        closeRegisterModal();
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        if (alertBox) alertBox.classList.add('hidden');
                        document.getElementById('login-password')?.focus();
                    }, 1800);
                } else {
                    if (alertBox) {
                        alertBox.className = 'modal-alert modal-alert-error';
                        alertBox.innerHTML = '⚠️ ' + (json.message || 'เกิดข้อผิดพลาดในการลงทะเบียน');
                    }
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                if (alertBox) {
                    alertBox.className = 'modal-alert modal-alert-error';
                    alertBox.innerHTML = '⚠️ เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง';
                }
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        // Remember email ONLY on this device when "จดจำการเข้าสู่ระบบ" is checked
        (function () {
            const STORAGE_KEY = 'malastreet_saved_email';
            const emailInput = document.getElementById('login-email');
            const rememberCheckbox = document.getElementById('login-remember');
            const loginForm = emailInput ? emailInput.closest('form') : null;

            if (emailInput && rememberCheckbox && !emailInput.value) {
                try {
                    const savedEmail = localStorage.getItem(STORAGE_KEY);
                    if (savedEmail) {
                        emailInput.value = savedEmail;
                        rememberCheckbox.checked = true;
                    }
                } catch (e) {}
            }

            if (loginForm && emailInput && rememberCheckbox) {
                loginForm.addEventListener('submit', function () {
                    try {
                        if (rememberCheckbox.checked && emailInput.value.trim()) {
                            localStorage.setItem(STORAGE_KEY, emailInput.value.trim());
                        } else {
                            localStorage.removeItem(STORAGE_KEY);
                        }
                    } catch (e) {}
                });
            }
        })();
    </script>
</body>
</html>
