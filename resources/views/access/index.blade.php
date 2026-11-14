@extends('layouts.app', ['title' => 'จัดการสิทธิ์แยกสาขา & ระบบรีเซ็ทข้อมูล - หม่าล่าสตรีท'])

@section('content')
<div class="min-h-screen bg-[#F8FAFC] py-4 sm:py-6 px-3 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white rounded-2xl p-4 sm:p-5 border border-slate-200 shadow-2xs">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900">ระบบสิทธิ์การเข้าถึงแยกสาขา &amp; รีเซ็ทข้อมูล (สำรอง 30 วัน)</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">ร้านหม่าล่าสตรีท (Mala Street) • เชื่อมต่อระบบการเข้าถึงหลัก</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.access.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    ← กลับหน้าการเข้าถึงหลัก
                </a>
                <a href="{{ route('admin.access.index', ['tab' => 'reset']) }}" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow transition">
                    🔄 ระบบรีเซ็ทข้อมูล (30 วัน)
                </a>
            </div>
        </div>

        @include('access.partials.reset_tab', ['resetBranches' => $resetBranches ?? collect()])
    </div>
</div>
@endsection
