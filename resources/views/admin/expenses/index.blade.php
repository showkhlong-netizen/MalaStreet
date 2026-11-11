@extends('layouts.app', ['title' => 'บันทึกฟิกคอส & รายรับรายจ่าย - หม่าล่าสตรีท'])

@section('content')
<div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full space-y-6">
    <!-- Header with Date Picker -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center text-2xl">
                💰
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-800">ระบบบันทึกฟิกคอส & รายรับรายจ่ายทั่วไป</h1>
                <p class="text-xs text-slate-400">ควบคุมค่าเช่าที่หน้าร้าน, ค่าแรงพนักงาน, ค่าแก๊ส, ค่าน้ำไฟ และคำนวณงบกำไรสุทธิ</p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.expenses.index') }}" class="flex items-center space-x-2">
            <label class="text-xs font-bold text-slate-600">วันที่:</label>
            <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                class="text-xs font-bold px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-1 focus:ring-rose-500">
        </form>
    </div>

    <!-- P&L Financial Statement Card (งบกำไรขาดทุนประจำวัน) -->
    <div class="bg-gradient-to-br from-slate-900 via-rose-950 to-slate-900 text-white p-6 rounded-3xl shadow-xl">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div>
                <span class="text-xs font-bold text-rose-300 uppercase tracking-widest block">งบกำไรขาดทุนประจำวันที่ {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</span>
                <div class="flex items-baseline space-x-3 mt-1">
                    <span class="text-4xl font-black {{ $netProfit >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        ฿{{ number_format($netProfit, 2) }}
                    </span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $netProfit >= 0 ? 'bg-emerald-950 text-emerald-300 border border-emerald-500/40' : 'bg-rose-950 text-rose-300 border border-rose-500/40' }}">
                        อัตรากำไร {{ $profitMargin }}%
                    </span>
                </div>
            </div>

            <!-- Flow breakdown -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs w-full lg:w-auto">
                <div class="p-3 bg-white/5 rounded-2xl border border-white/10">
                    <span class="text-slate-400 block text-[11px]">1. ยอดขายรวม (Revenue)</span>
                    <span class="text-base font-black text-white mt-1 block">฿{{ number_format($totalRevenue, 2) }}</span>
                </div>

                <div class="p-3 bg-white/5 rounded-2xl border border-white/10">
                    <span class="text-slate-400 block text-[11px]">2. ค่าซื้อวัตถุดิบ (COGS)</span>
                    <span class="text-base font-black text-rose-300 mt-1 block">-฿{{ number_format($cogs, 2) }}</span>
                </div>

                <div class="p-3 bg-white/5 rounded-2xl border border-white/10">
                    <span class="text-slate-400 block text-[11px]">3. ฟิกคอส (Fixed Cost)</span>
                    <span class="text-base font-black text-amber-300 mt-1 block">-฿{{ number_format($totalFixedCosts, 2) }}</span>
                </div>

                <div class="p-3 bg-white/5 rounded-2xl border border-white/10">
                    <span class="text-slate-400 block text-[11px]">4. ค่าใช้จ่ายอื่นๆ</span>
                    <span class="text-base font-black text-amber-200 mt-1 block">-฿{{ number_format($totalVariableExpenses, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form to Add Expense & List -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Quick Add Expense Form -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-extrabold text-sm text-slate-800">➕ บันทึกค่าใช้จ่าย / ฟิกคอส</h3>

            <form action="{{ route('admin.expenses.store') }}" method="POST" class="space-y-3.5">
                @csrf
                <input type="hidden" name="expense_date" value="{{ $selectedDate }}">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">ชื่อรายการ:</label>
                    <input type="text" name="title" placeholder="เช่น ค่าเช่าล็อคตลาด, ค่าจ้างย่าง, ค่าแก๊ส" required
                        class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-1 focus:ring-rose-500">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">ประเภท:</label>
                        <select name="type" required class="w-full text-xs px-2.5 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                            <option value="fixed_cost">ฟิกคอส (Fixed Cost)</option>
                            <option value="variable_expense">ค่าใช้จ่ายผันแปร</option>
                            <option value="daily_operating">ค่าใช้จ่ายทั่วไป</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">หมวดหมู่:</label>
                        <select name="category" required class="w-full text-xs px-2.5 py-2 bg-slate-50 border border-slate-300 rounded-xl">
                            <option value="rent">ค่าเช่าที่ / ล็อค</option>
                            <option value="labor">ค่าแรง / พนักงาน</option>
                            <option value="utilities">ค่าน้ำ / ค่าไฟ / ค่าแก๊ส</option>
                            <option value="supplies">ถุงหิ้ว / ไม้เสียบ / บรรจุภัณฑ์</option>
                            <option value="ingredients">เครื่องปรุง / ซอส</option>
                            <option value="other">อื่นๆ</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">จำนวนเงิน (บาท):</label>
                    <input type="number" step="0.5" name="amount" placeholder="0.00" required
                        class="w-full text-sm font-black text-rose-600 px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-1 focus:ring-rose-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">หมายเหตุ:</label>
                    <input type="text" name="notes" placeholder="เช่น จ่ายรายวัน, โอนผ่านพร้อมเพย์"
                        class="w-full text-xs px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl focus:outline-none focus:ring-1 focus:ring-rose-500">
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md transition-all">
                    บันทึกรายการ
                </button>
            </form>
        </div>

        <!-- Right: Daily Expenses List -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-extrabold text-sm text-slate-800">
                        รายการรายจ่ายประจำวันที่ {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
                    </h3>
                    <span class="text-xs font-bold text-rose-600">รวม ฿{{ number_format($totalExpenses, 2) }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase border-b border-slate-100 font-bold">
                            <tr>
                                <th class="py-3 px-4">รายการ</th>
                                <th class="py-3 px-4">ประเภท</th>
                                <th class="py-3 px-4">หมวดหมู่</th>
                                <th class="py-3 px-4 text-right">จำนวนเงิน</th>
                                <th class="py-3 px-4 text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($dailyExpenses as $exp)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-800 block">{{ $exp->title }}</span>
                                        @if($exp->notes)
                                            <span class="text-[10px] text-slate-400">{{ $exp->notes }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $exp->type === 'fixed_cost' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $exp->type === 'fixed_cost' ? 'ฟิกคอส' : 'ทั่วไป' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 font-medium">
                                        {{ match($exp->category) {
                                            'rent' => '🏠 ค่าเช่าที่',
                                            'labor' => '👷 ค่าแรงพนักงาน',
                                            'utilities' => '⚡ ค่าน้ำ/ไฟ/แก๊ส',
                                            'supplies' => '📦 บรรจุภัณฑ์/ไม้',
                                            'ingredients' => '🧂 ซอส/เครื่องเทศ',
                                            default => '📁 อื่นๆ'
                                        } }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-black text-slate-900">
                                        ฿{{ number_format($exp->amount, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <form action="{{ route('admin.expenses.destroy', $exp->id) }}" method="POST" onsubmit="return confirm('ต้องการลบรายการนี้ใช่หรือไม่?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-600 font-bold text-xs p-1">
                                                🗑️
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">ยังไม่มีรายการค่าใช้จ่ายในวันนี้</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer summary -->
            <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-between items-center text-xs text-slate-500">
                <span>ฟิกคอส: ฿{{ number_format($totalFixedCosts, 2) }} | ผันแปร: ฿{{ number_format($totalVariableExpenses, 2) }}</span>
                <span>รวมรายจ่ายทั้งเดือนนี้: <strong>฿{{ number_format($monthExpenses, 2) }}</strong></span>
            </div>
        </div>
    </div>
</div>
@endsection

