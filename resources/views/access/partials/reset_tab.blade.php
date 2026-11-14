{{-- =======================================================================
     แท็บ: ระบบรีเซ็ทข้อมูล & ถังสำรอง 30 วัน (สำหรับหม่าล่าสตรีท)
     ขั้นตอนใช้งาน 3 ขั้นตอน:
       1) เลือกสาขาที่จะลบ (ทุกสาขา หรือ เลือกเฉพาะบางสาขา รองรับสาขาใหม่ในอนาคตอัตโนมัติ)
       2) เลือกช่วงวันที่ต้องการลบ (เฉพาะวันนี้ / เลือกวันที่ / ลบทั้งหมดทุกวัน)
       3) กดปุ่มหมวดที่ต้องการลบ (สำรองข้อมูลไว้กู้คืนได้นาน 30 วันอัตโนมัติ)
     ======================================================================= --}}
<div id="malaResetHubRoot" class="space-y-6">

    {{-- แถบหัวข้อหลัก + ปุ่มกู้คืนด่วนทั้งหมด --}}
    <div class="bg-gradient-to-r from-rose-600 to-amber-600 rounded-2xl p-6 text-white shadow-lg flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 bg-white/20 px-3 py-1 rounded-full text-xs font-bold mb-2">
                <span>🛡️ ปลอดภัย 100% • สำรองข้อมูลอัตโนมัติ 30 วัน</span>
            </div>
            <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight">ระบบรีเซ็ทข้อมูลร้านหม่าล่าสตรีท</h2>
            <p class="text-white/90 text-sm md:text-base mt-1">
                เลือกลบเฉพาะบางสาขาหรือทุกสาขาได้ง่ายๆ ใน 3 ขั้นตอน หากกดลบผิดสามารถกดปุ่มกู้คืนกลับมาได้ทันทีภายใน 30 วัน
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('access.reset.export-customers') }}"
               class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 text-white font-bold px-4 py-3 rounded-xl text-sm border border-white/30 transition">
                📥 สำรองข้อมูลสมาชิก (.CSV)
            </a>
            <button type="button" onclick="MalaResetHub.openRestoreAllModal()"
                    class="inline-flex items-center gap-2 bg-white text-rose-700 hover:bg-rose-50 font-extrabold px-5 py-3 rounded-xl text-sm shadow transition cursor-pointer">
                ⏪ กู้คืนข้อมูลทั้งหมดในคลิกเดียว
            </button>
        </div>
    </div>

    {{-- ขั้นตอนที่ 1: เลือกสาขาที่ต้องการลบ --}}
    <div class="bg-white rounded-2xl border-2 border-slate-200 p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <span class="w-9 h-9 rounded-xl bg-rose-600 text-white font-black text-lg flex items-center justify-center">1</span>
            <div>
                <h3 class="text-lg md:text-xl font-extrabold text-slate-900">เลือกสาขาที่ต้องการรีเซ็ทข้อมูล</h3>
                <p class="text-sm text-slate-500">เลือก "ทุกสาขา" หรือคลิกเลือกเฉพาะบางสาขาที่ต้องการลบ (เมื่อเพิ่มสาขาใหม่ ปุ่มสาขาจะเพิ่มให้อัตโนมัติ)</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3" id="malaResetBranchPills">
            <button type="button"
                    data-branch-id="ALL"
                    onclick="MalaResetHub.toggleBranch('ALL')"
                    class="mala-branch-pill active px-5 py-3.5 rounded-xl font-extrabold text-base border-2 border-rose-600 bg-rose-600 text-white shadow-sm transition cursor-pointer">
                🌐 ทุกสาขารวมกัน
            </button>
            @foreach(($resetBranches ?? []) as $b)
                <button type="button"
                        data-branch-id="{{ $b->id }}"
                        data-branch-name="{{ $b->name }}"
                        onclick="MalaResetHub.toggleBranch({{ $b->id }})"
                        class="mala-branch-pill px-5 py-3.5 rounded-xl font-bold text-base border-2 border-slate-200 bg-slate-50 text-slate-700 hover:border-rose-400 transition cursor-pointer">
                    🏪 {{ $b->name }}
                    @if(($b->status ?? 'ACTIVE') !== 'ACTIVE')
                        <span class="text-xs text-slate-400">(ปิดชั่วคราว)</span>
                    @endif
                </button>
            @endforeach
        </div>
        <div class="mt-3 text-sm font-bold text-rose-600" id="malaSelectedBranchSummary">
            📌 กำลังเลือก: ทุกสาขารวมกัน
        </div>
    </div>

    {{-- ขั้นตอนที่ 2: เลือกช่วงวันที่ต้องการลบ --}}
    <div class="bg-white rounded-2xl border-2 border-slate-200 p-6 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <span class="w-9 h-9 rounded-xl bg-amber-500 text-white font-black text-lg flex items-center justify-center">2</span>
            <div>
                <h3 class="text-lg md:text-xl font-extrabold text-slate-900">เลือกช่วงเวลาของข้อมูลที่จะลบ</h3>
                <p class="text-sm text-slate-500">เลือกลบเฉพาะรายการที่เปิดวันนี้ เลือกเฉพาะวันที่ต้องการ หรือลบทั้งหมดทุกวัน</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <button type="button" data-date-mode="TODAY" onclick="MalaResetHub.setDateMode('TODAY')"
                    class="mala-date-pill active p-4 rounded-xl border-2 border-amber-500 bg-amber-50 text-left transition cursor-pointer">
                <div class="text-base font-extrabold text-slate-900">📅 ลบเฉพาะข้อมูล "วันนี้"</div>
                <div class="text-xs text-slate-600 mt-1">ลบเฉพาะรายการที่ทำในวันนี้ ข้อมูลวันก่อนหน้าปลอดภัย</div>
            </button>

            <button type="button" data-date-mode="CUSTOM" onclick="MalaResetHub.setDateMode('CUSTOM')"
                    class="mala-date-pill p-4 rounded-xl border-2 border-slate-200 bg-slate-50 text-left transition cursor-pointer">
                <div class="text-base font-extrabold text-slate-900">🗓️ เลือกวันที่ต้องการลบเอง</div>
                <div class="mt-2">
                    <input type="date" id="malaResetCustomDate" value="{{ now()->toDateString() }}"
                           onchange="MalaResetHub.setDateMode('CUSTOM')"
                           class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-bold text-slate-800">
                </div>
            </button>

            <button type="button" data-date-mode="ALL" onclick="MalaResetHub.setDateMode('ALL')"
                    class="mala-date-pill p-4 rounded-xl border-2 border-slate-200 bg-slate-50 text-left transition cursor-pointer">
                <div class="text-base font-extrabold text-rose-700">🧹 ลบทั้งหมดทุกวัน (ตั้งแต่วันแรก)</div>
                <div class="text-xs text-slate-600 mt-1">ล้างข้อมูลสะสมทั้งหมดของสาขาที่เลือกไว้</div>
            </button>
        </div>
    </div>

    {{-- ขั้นตอนที่ 3: เลือกหมวดข้อมูลที่ต้องการรีเซ็ท --}}
    <div class="bg-white rounded-2xl border-2 border-slate-200 p-6 shadow-sm">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl bg-emerald-600 text-white font-black text-lg flex items-center justify-center">3</span>
                <div>
                    <h3 class="text-lg md:text-xl font-extrabold text-slate-900">กดปุ่มหมวดข้อมูลที่ต้องการลบ</h3>
                    <p class="text-sm text-slate-500">ตัวเลขในแต่ละการ์ดจะแสดงจำนวนรายการจริงของสาขาที่คุณเลือกในขั้นตอนที่ 1</p>
                </div>
            </div>
            <button type="button" onclick="MalaResetHub.loadStats()"
                    class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm cursor-pointer">
                🔄 รีเฟรชยอดล่าสุด
            </button>
        </div>

        <div id="malaScopeCardsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- การ์ดหมวดหมู่จะถูกสร้างอัตโนมัติด้วย JS จาก /access/reset/stats --}}
        </div>
    </div>

    {{-- ส่วนที่ 4: ถังสำรองฉุกเฉิน 30 วัน (กู้คืน หรือ ลบถาวร) --}}
    <div class="bg-white rounded-2xl border-2 border-slate-200 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg md:text-xl font-extrabold text-slate-900">📦 ถังสำรองข้อมูล 30 วัน (กดกู้คืนได้ตลอดเวลา)</h3>
                <p class="text-sm text-slate-500">รายการที่ถูกรีเซ็ทจะถูกเก็บแยกไว้ที่นี่นาน 30 วัน ไม่ปะปนกับหน้าขายหน้าร้าน</p>
            </div>
        </div>

        <div id="malaSnapshotsList" class="space-y-3">
            {{-- รายการถังสำรองจะถูกเติมด้วย JS --}}
        </div>
    </div>
</div>

{{-- Modal ยืนยัน 2 ชั้น (ใช้ร่วมกันทั้ง รีเซ็ท / กู้คืน / ลบถาวร) --}}
<div id="malaResetConfirmModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
        <h4 id="malaModalTitle" class="text-xl font-extrabold text-slate-900">ยืนยันการทำรายการ</h4>
        <p id="malaModalSubtitle" class="text-sm text-slate-600 mt-1"></p>

        <div class="mt-4 space-y-3">
            <div class="flex gap-2">
                <button type="button" id="malaModeCodeBtn" onclick="MalaResetHub.setConfirmMode('code')"
                        class="flex-1 py-2 rounded-xl font-bold text-sm bg-rose-600 text-white cursor-pointer">
                    พิมพ์คำว่า RESET
                </button>
                <button type="button" id="malaModePassBtn" onclick="MalaResetHub.setConfirmMode('password')"
                        class="flex-1 py-2 rounded-xl font-bold text-sm bg-slate-100 text-slate-700 cursor-pointer">
                    ใช้รหัสผ่าน Admin
                </button>
            </div>

            <input type="text" id="malaConfirmInput" placeholder="พิมพ์คำว่า RESET ที่นี่..."
                   class="w-full rounded-xl border-2 border-slate-300 px-4 py-3 text-base font-extrabold text-center focus:border-rose-500 focus:outline-none">

            <input type="text" id="malaReasonInput" placeholder="หมายเหตุเพิ่มเติม (ไม่บังคับ)"
                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <button type="button" onclick="MalaResetHub.closeModal()"
                    class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm cursor-pointer">
                ยกเลิก
            </button>
            <button type="button" id="malaModalSubmitBtn" onclick="MalaResetHub.submitModal()"
                    class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-sm shadow cursor-pointer">
                ยืนยันทำรายการ
            </button>
        </div>
    </div>
</div>

<script>
window.MalaResetHub = {
    selectedBranches: 'ALL',
    dateMode: 'TODAY',
    confirmMode: 'code',
    pendingAction: null,

    init() {
        this.loadStats();
    },

    toggleBranch(branchId) {
        if (branchId === 'ALL') {
            this.selectedBranches = 'ALL';
        } else {
            if (this.selectedBranches === 'ALL') {
                this.selectedBranches = [branchId];
            } else {
                const idx = this.selectedBranches.indexOf(branchId);
                if (idx >= 0) {
                    this.selectedBranches.splice(idx, 1);
                } else {
                    this.selectedBranches.push(branchId);
                }
                if (this.selectedBranches.length === 0) {
                    this.selectedBranches = 'ALL';
                }
            }
        }
        this.renderBranchPills();
        this.loadStats();
    },

    renderBranchPills() {
        const pills = document.querySelectorAll('.mala-branch-pill');
        const names = [];
        pills.forEach(btn => {
            const bid = btn.getAttribute('data-branch-id');
            const isSelected = (this.selectedBranches === 'ALL' && bid === 'ALL')
                || (Array.isArray(this.selectedBranches) && this.selectedBranches.includes(Number(bid)));

            if (isSelected) {
                btn.className = 'mala-branch-pill active px-5 py-3.5 rounded-xl font-extrabold text-base border-2 border-rose-600 bg-rose-600 text-white shadow-sm transition cursor-pointer';
                if (bid !== 'ALL') names.push(btn.getAttribute('data-branch-name'));
            } else {
                btn.className = 'mala-branch-pill px-5 py-3.5 rounded-xl font-bold text-base border-2 border-slate-200 bg-slate-50 text-slate-700 hover:border-rose-400 transition cursor-pointer';
            }
        });

        const summaryEl = document.getElementById('malaSelectedBranchSummary');
        if (summaryEl) {
            summaryEl.textContent = this.selectedBranches === 'ALL'
                ? '📌 กำลังเลือก: ทุกสาขารวมกัน'
                : '📌 กำลังเลือกเฉพาะสาขา: ' + names.join(', ');
        }
    },

    setDateMode(mode) {
        this.dateMode = mode;
        document.querySelectorAll('.mala-date-pill').forEach(btn => {
            const m = btn.getAttribute('data-date-mode');
            btn.className = (m === mode)
                ? 'mala-date-pill active p-4 rounded-xl border-2 border-amber-500 bg-amber-50 text-left transition cursor-pointer'
                : 'mala-date-pill p-4 rounded-xl border-2 border-slate-200 bg-slate-50 text-left transition cursor-pointer';
        });
    },

    getDateFilterValue() {
        if (this.dateMode === 'TODAY') return 'TODAY';
        if (this.dateMode === 'CUSTOM') {
            return document.getElementById('malaResetCustomDate')?.value || 'TODAY';
        }
        return 'ALL';
    },

    async loadStats() {
        try {
            const params = new URLSearchParams();
            if (Array.isArray(this.selectedBranches) && this.selectedBranches.length > 0) {
                params.set('branch_ids', this.selectedBranches.join(','));
            } else {
                params.set('branch_id', 'ALL');
            }

            const res = await fetch(`{{ route('access.reset.stats') }}?` + params.toString(), {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (!data.success) return;

            this.renderScopeCards(data.stats || {});
            this.renderSnapshots(data.snapshots || []);
        } catch (err) {
            console.warn('MalaResetHub.loadStats error:', err);
        }
    },

    renderScopeCards(stats) {
        const grid = document.getElementById('malaScopeCardsGrid');
        if (!grid) return;

        grid.innerHTML = Object.values(stats).map(item => `
            <div class="rounded-2xl border-2 border-slate-200 hover:border-rose-400 p-5 flex flex-col justify-between bg-white shadow-sm transition">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">${item.badge}</span>
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg">วันนี้: ${Number(item.today_count || 0).toLocaleString()} รายการ</span>
                    </div>
                    <h4 class="text-lg font-extrabold text-slate-900">${item.label}</h4>
                    <p class="text-xs text-slate-500 mt-1">${item.description}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-black text-slate-900">${Number(item.count || 0).toLocaleString()} <span class="text-xs font-bold text-slate-500">รายการ</span></div>
                        <div class="text-xs font-bold text-slate-500">ยอดรวม ฿${Number(item.total_amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}</div>
                    </div>
                    <button type="button"
                            onclick="MalaResetHub.openResetModal('${item.key}', '${item.label}')"
                            class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-sm shadow transition cursor-pointer">
                        🗑️ ลบหมวดนี้
                    </button>
                </div>
            </div>
        `).join('');
    },

    renderSnapshots(snapshots) {
        const container = document.getElementById('malaSnapshotsList');
        if (!container) return;

        if (!snapshots.length) {
            container.innerHTML = `<div class="p-6 text-center text-slate-400 font-bold">ยังไม่มีประวัติการรีเซ็ทข้อมูลในถังสำรอง 30 วัน</div>`;
            return;
        }

        container.innerHTML = snapshots.map(s => `
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-black text-slate-900 text-base">${s.scope_label}</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold ${s.status === 'RESTORED' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-800'}">
                            ${s.status === 'RESTORED' ? '✅ กู้คืนแล้ว' : `⏳ เหลือเวลากู้คืน ${s.days_left} วัน`}
                        </span>
                    </div>
                    <div class="text-xs text-slate-600 mt-1">
                        รหัส: <b>${s.batch_number}</b> • จำนวน <b>${Number(s.records_count).toLocaleString()} รายการ</b> (฿${Number(s.total_amount).toLocaleString()}) • ลบเมื่อ ${s.created_at_fmt}
                    </div>
                    <div class="text-xs text-slate-500 mt-0.5">${s.summary_notes || ''}</div>
                </div>
                <div class="flex items-center gap-2">
                    ${s.status !== 'RESTORED' ? `
                        <button type="button" onclick="MalaResetHub.openRestoreModal(${s.id}, '${s.scope_label}')"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow cursor-pointer">
                            ⏪ กู้คืนข้อมูลชุดนี้
                        </button>
                    ` : ''}
                    <button type="button" onclick="MalaResetHub.openPurgeModal(${s.id}, '${s.scope_label}')"
                            class="px-3 py-2 rounded-xl bg-slate-200 hover:bg-rose-100 hover:text-rose-700 text-slate-700 font-bold text-xs cursor-pointer">
                        🗑️ ลบถาวรจากถังสำรอง
                    </button>
                </div>
            </div>
        `).join('');
    },

    setConfirmMode(mode) {
        this.confirmMode = mode;
        const input = document.getElementById('malaConfirmInput');
        const codeBtn = document.getElementById('malaModeCodeBtn');
        const passBtn = document.getElementById('malaModePassBtn');
        if (mode === 'password') {
            input.type = 'password';
            input.placeholder = 'กรอกรหัสผ่าน Admin ที่นี่...';
            if (passBtn) passBtn.className = 'flex-1 py-2 rounded-xl font-bold text-sm bg-rose-600 text-white cursor-pointer';
            if (codeBtn) codeBtn.className = 'flex-1 py-2 rounded-xl font-bold text-sm bg-slate-100 text-slate-700 cursor-pointer';
        } else {
            input.type = 'text';
            input.placeholder = 'พิมพ์คำว่า RESET ที่นี่...';
            if (codeBtn) codeBtn.className = 'flex-1 py-2 rounded-xl font-bold text-sm bg-rose-600 text-white cursor-pointer';
            if (passBtn) passBtn.className = 'flex-1 py-2 rounded-xl font-bold text-sm bg-slate-100 text-slate-700 cursor-pointer';
        }
        input.value = '';
    },

    openResetModal(scopeKey, label) {
        this.pendingAction = { type: 'reset', scopeKey, label };
        document.getElementById('malaModalTitle').textContent = `ยืนยันการลบ: ${label}`;
        document.getElementById('malaModalSubtitle').textContent = 'ข้อมูลที่ลบจะถูกเก็บไว้ในถังสำรอง 30 วัน สามารถกดกู้คืนได้ตลอดเวลา';
        this.showModal();
    },

    openRestoreModal(snapshotId, label) {
        this.pendingAction = { type: 'restore', snapshotId, label };
        document.getElementById('malaModalTitle').textContent = `กู้คืนข้อมูล: ${label}`;
        document.getElementById('malaModalSubtitle').textContent = 'ข้อมูลทั้งหมดในชุดนี้จะถูกนำกลับเข้าสู่ระบบหน้าร้านทันที';
        this.showModal();
    },

    openRestoreAllModal() {
        this.pendingAction = { type: 'restore_all' };
        document.getElementById('malaModalTitle').textContent = 'กู้คืนข้อมูลทั้งหมดในถังสำรอง 30 วัน';
        document.getElementById('malaModalSubtitle').textContent = 'ระบบจะกู้คืนทุกรายการที่ยังไม่ได้กู้คืนกลับเข้าสู่หน้าร้านในคลิกเดียว';
        this.showModal();
    },

    openPurgeModal(snapshotId, label) {
        this.pendingAction = { type: 'purge', snapshotId, label };
        document.getElementById('malaModalTitle').textContent = `ลบถาวรจากถังสำรอง: ${label}`;
        document.getElementById('malaModalSubtitle').textContent = 'ลบเฉพาะไฟล์สำรองชุดนี้ออกจากถัง 30 วัน (ไม่กระทบข้อมูลที่ใช้งานอยู่หน้าร้าน)';
        this.showModal();
    },

    showModal() {
        this.setConfirmMode('code');
        const modal = document.getElementById('malaResetConfirmModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    },

    closeModal() {
        const modal = document.getElementById('malaResetConfirmModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    },

    async submitModal() {
        if (!this.pendingAction) return;
        const confirmValue = document.getElementById('malaConfirmInput').value.trim();
        const reason = document.getElementById('malaReasonInput').value.trim();
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

        let url = '';
        let method = 'POST';
        const body = {
            confirm_mode: this.confirmMode,
            confirm_value: confirmValue,
            reason: reason,
        };

        if (this.pendingAction.type === 'reset') {
            url = `{{ route('access.reset.execute') }}`;
            body.scope_key = this.pendingAction.scopeKey;
            body.date_filter = this.getDateFilterValue();
            if (Array.isArray(this.selectedBranches)) {
                body.branch_ids = this.selectedBranches;
            } else {
                body.branch_id = 'ALL';
            }
        } else if (this.pendingAction.type === 'restore') {
            url = `/access/reset/${this.pendingAction.snapshotId}/restore`;
        } else if (this.pendingAction.type === 'restore_all') {
            url = `{{ route('access.reset.restore-all') }}`;
        } else if (this.pendingAction.type === 'purge') {
            url = `/access/reset/${this.pendingAction.snapshotId}/purge`;
            method = 'DELETE';
        }

        const res = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify(body),
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
            alert(data.message || 'เกิดข้อผิดพลาด กรุณาตรวจสอบข้อมูลยืนยัน');
            return;
        }

        this.closeModal();
        alert(data.message);
        this.loadStats();
    }
};

document.addEventListener('DOMContentLoaded', () => MalaResetHub.init());
</script>
