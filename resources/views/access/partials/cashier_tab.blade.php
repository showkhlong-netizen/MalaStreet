@php
    $pp = $promptPaySettings ?? \App\Http\Controllers\PosController::getStorePromptPaySettings();
    $wh = $webhookConfig ?? \App\Http\Controllers\PaymentWebhookController::getWebhookConfig();
@endphp

<div class="space-y-5">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-lg font-black text-slate-900">จัดการแคชเชียร์ &amp; QR พร้อมเพย์</h2>
                <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10.5px] font-bold">ซิงค์ POS + สั่งออนไลน์</span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                อัปโหลดภาพ QR พร้อมเพย์ แก้ไขชื่อร้าน เลขบัญชี ข้อความ และเก็บคลังภาพ QR ทุกใบไว้สลับกลับมาใช้งานได้ทันที
            </p>
        </div>
        <a href="{{ route('pos.index') }}" target="_blank"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition-all shrink-0">
            <span>เปิดหน้าแคชเชียร์ (POS)</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
    </div>

    <!-- Main 2-Column Editor & Live Preview -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">
        <!-- Left: Form & Upload -->
        <div class="xl:col-span-7 space-y-4">
            <!-- Upload QR Image Box -->
            <div class="rounded-2xl border-2 border-dashed border-sky-300 bg-sky-50/50 p-4 space-y-2.5">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-black text-sky-950">1. อัปโหลดภาพ QR พร้อมเพย์ (ระบบเก็บภาพขึ้นฐานข้อมูลถาวร)</span>
                    <span class="text-[10.5px] font-bold text-sky-700 bg-white px-2 py-0.5 rounded-full border border-sky-200">PNG / JPG</span>
                </div>
                <label class="flex flex-col sm:flex-row items-center justify-center gap-3 p-3.5 rounded-xl bg-white border border-sky-200 hover:border-sky-400 cursor-pointer transition-all">
                    <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>
                    <div class="text-center sm:text-left">
                        <div class="text-xs font-bold text-slate-800">คลิกเพื่อเลือกภาพ QR Code จากเครื่อง</div>
                        <div class="text-[11px] text-slate-500">ถอดรหัส EMVCo อัตโนมัติ + เก็บภาพต้นฉบับเข้าคลัง แม้สลับไปใช้ QR ใหม่ภาพเดิมก็ไม่หาย</div>
                    </div>
                    <input type="file" id="cashier-qr-file-input" accept="image/*" class="hidden" onchange="handleCashierQrUpload(this)">
                </label>
                <div id="cashier-qr-decode-status" class="text-[11px] font-bold hidden"></div>
            </div>

            <!-- Store & Account Free-Edit Fields -->
            <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 space-y-3 text-xs">
                <div class="font-black text-slate-800 text-xs">2. แก้ไขข้อมูลร้านค้า &amp; รายละเอียดบัญชี (อิสระทุกช่อง)</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">ชื่อเรียก QR นี้ (ในคลัง)</label>
                        <input type="text" id="cashier-qr-label" value="{{ $pp['account_no'] ?? '' }}"
                               placeholder="เช่น กสิกรไทย สาขายะลา / บัญชีหลัก"
                               class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">ชื่อร้านที่แสดง</label>
                        <input type="text" id="cashier-store-name" value="{{ $pp['store_name'] ?? 'ร้านหม่าล่าสตรีท (Mala Street)' }}"
                               placeholder="เช่น ร้านหม่าล่าสตรีท"
                               class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">เลขบัญชี / ธนาคารที่แสดง</label>
                        <input type="text" id="cashier-account-no" value="{{ $pp['account_no'] ?? '' }}"
                               placeholder="เช่น 098-1-53225-2 (กสิกรไทย)"
                               class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">ชื่อบัญชีที่แสดง</label>
                        <input type="text" id="cashier-account-name" value="{{ $pp['account_name'] ?? '' }}"
                               placeholder="เช่น มูนีบ สและแม"
                               class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">เบอร์โทรติดต่อร้าน</label>
                        <input type="text" id="cashier-store-phone" value="{{ $pp['store_phone'] ?? '089-123-4567' }}"
                               placeholder="เช่น 089-123-4567"
                               class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">ข้อความหมายเหตุใต้ QR</label>
                        <input type="text" id="cashier-receipt-note" value="{{ $pp['receipt_note'] ?? '' }}"
                               placeholder="สแกนได้ทุกแอปธนาคารไทย ยอดเงินจะขึ้นตรงอัตโนมัติ"
                               class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 focus:border-blue-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">รหัส QR (EMVCo Payload) หรือเบอร์พร้อมเพย์</label>
                    <textarea id="cashier-qr-payload" rows="2" oninput="refreshCashierLiveQrPreview()"
                              class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 font-mono text-[10.5px] text-slate-700 focus:border-blue-500 focus:outline-none">{{ $pp['payload'] ?? '' }}</textarea>
                </div>

                <div class="pt-1 flex flex-wrap items-center gap-2">
                    <button type="button" id="btn-cashier-save-qr" onclick="saveCashierQrSettings()"
                            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-extrabold shadow-sm cursor-pointer transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span>บันทึก &amp; เก็บภาพ QR เข้าระบบ</span>
                    </button>
                    <button type="button" onclick="refreshCashierLiveQrPreview()"
                            class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold cursor-pointer transition-all">
                        อัปเดตตัวอย่าง QR
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: Live Locked-Amount QR Preview -->
        <div class="xl:col-span-5 space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-xs text-center space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-black text-slate-800">ตัวอย่าง QR ล็อคยอดเงิน</span>
                    <div class="flex items-center gap-1.5 text-xs">
                        <span class="text-slate-500 font-bold">ยอดทดสอบ ฿</span>
                        <input type="number" id="cashier-preview-amount" value="35" min="1" step="0.01" oninput="refreshCashierLiveQrPreview()"
                               class="w-20 px-2 py-1 rounded-lg border border-slate-200 text-center font-mono font-bold text-slate-900">
                    </div>
                </div>

                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 inline-flex flex-col items-center">
                    <img id="cashier-live-qr-img" src="" alt="QR Preview" class="w-44 h-44 object-contain rounded-xl bg-white p-1.5 border border-slate-200">
                    <div class="mt-2 text-xs font-black text-slate-900" id="cashier-preview-store-name">{{ $pp['store_name'] }}</div>
                    <div class="text-[11px] font-mono font-bold text-slate-700" id="cashier-preview-account-no">{{ $pp['account_no'] }}</div>
                    <div class="text-[11px] text-slate-500" id="cashier-preview-account-name">ชื่อบัญชี: {{ $pp['account_name'] }}</div>
                </div>

                <div id="cashier-uploaded-thumb-wrap" class="{{ empty($pp['active_image']) ? 'hidden' : '' }} pt-2 border-t border-slate-100">
                    <div class="text-[11px] font-bold text-slate-500 mb-1.5">ภาพต้นฉบับที่อัปโหลดไว้ (บันทึกถาวรในระบบ)</div>
                    <img id="cashier-uploaded-thumb" src="{{ $pp['active_image'] ?? '' }}" alt="Uploaded QR" class="h-24 mx-auto rounded-xl border border-slate-200 object-contain bg-slate-50 p-1">
                </div>
            </div>

            <!-- Android Webhook Box -->
            <div class="rounded-2xl bg-emerald-50/70 border border-emerald-200 p-4 space-y-2 text-xs">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-extrabold text-emerald-900">🤖 Webhook ยืนยันเงินเข้าอัตโนมัติ (Android)</span>
                    <button type="button" onclick="testCashierWebhook()" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold cursor-pointer">ทดสอบ</button>
                </div>
                <div class="space-y-1.5 text-[11px]">
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-slate-600 shrink-0">URL:</span>
                        <input type="text" id="cashier-wh-url" readonly value="{{ $wh['webhook_url'] }}" class="flex-1 px-2 py-1 rounded-lg bg-white border border-emerald-200 font-mono text-[10px]">
                        <button type="button" onclick="copyCashierText('cashier-wh-url')" class="px-2 py-1 rounded-lg bg-white border border-emerald-200 text-emerald-800 font-bold text-[10px] cursor-pointer">คัดลอก</button>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-slate-600 shrink-0">Secret:</span>
                        <input type="text" id="cashier-wh-secret" readonly value="{{ $wh['secret'] }}" class="flex-1 px-2 py-1 rounded-lg bg-white border border-emerald-200 font-mono text-[10px]">
                        <button type="button" onclick="copyCashierText('cashier-wh-secret')" class="px-2 py-1 rounded-lg bg-white border border-emerald-200 text-emerald-800 font-bold text-[10px] cursor-pointer">คัดลอก</button>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-slate-600 shrink-0">Body:</span>
                        <input type="text" id="cashier-wh-macrodroid-body" readonly value='{"secret":"{{ $wh['secret'] }}","message":"[not_title] [notification]"}' class="flex-1 px-2 py-1 rounded-lg bg-white border border-emerald-200 font-mono text-[10px]">
                        <button type="button" onclick="copyCashierText('cashier-wh-macrodroid-body')" class="px-2 py-1 rounded-lg bg-emerald-600 text-white font-bold text-[10px] cursor-pointer">คัดลอกไปวางเลย</button>
                    </div>
                    <div id="cashier-wh-status" class="text-[10.5px] text-emerald-800 font-medium leading-relaxed">
                        @if(!empty($wh['last_received_at']))
                            ล่าสุด: {{ $wh['last_received_at'] }} (฿{{ $wh['last_amount'] ?: '-' }})
                            @if(!empty($wh['last_matched_order_no'])) • จับคู่บิล {{ $wh['last_matched_order_no'] }} @endif
                            @if(!empty($wh['last_message']))
                                <span class="block text-[10px] text-slate-500 truncate">ข้อความ: {{ $wh['last_message'] }}</span>
                            @endif
                        @else
                            พร้อมรับแจ้งเตือนจาก K PLUS / LINE / NEXT บนมือถือ Android
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Saved QR History Gallery -->
    <div class="pt-3 border-t border-slate-200 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-black text-slate-900">คลังภาพ QR พร้อมเพย์ที่บันทึกไว้ทั้งหมด</h3>
                <p class="text-[11px] text-slate-500">ทุกภาพและรหัส QR ที่เคยอัปโหลดจะถูกเก็บไว้ที่นี่ สามารถกดสลับกลับมาใช้งานหรือเจน QR ใหม่ได้ตลอดเวลา</p>
            </div>
            <span id="cashier-history-count" class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-bold">
                {{ count($pp['history'] ?? []) }} รายการ
            </span>
        </div>

        <div id="cashier-qr-history-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
            <!-- Rendered by JS from CASHIER_QR_SETTINGS -->
        </div>
    </div>
</div>
<script src="{{ asset('js/jsQR.js') }}"></script>
