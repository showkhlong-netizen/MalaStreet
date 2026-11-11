<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * Display fixed costs and expenses overview with P&L statement.
     */
    public function index(Request $request): View
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $date = Carbon::parse($selectedDate);

        // Expenses for selected date
        $dailyExpenses = Expense::whereDate('expense_date', $date)->orderByDesc('id')->get();
        $fixedCosts = $dailyExpenses->where('type', 'fixed_cost');
        $variableExpenses = $dailyExpenses->where('type', '!=', 'fixed_cost');

        $totalFixedCosts = $fixedCosts->sum('amount');
        $totalVariableExpenses = $variableExpenses->sum('amount');
        $totalExpenses = $dailyExpenses->sum('amount');

        // Revenue for selected date
        $totalRevenue = Order::whereDate('created_at', $date)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        // Ingredients / Raw material purchases for selected date
        $cogs = Receipt::whereDate('purchase_date', $date)->sum('total_amount');

        // Net Profit Calculation
        $netProfit = $totalRevenue - $cogs - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 1) : 0;

        // Month to date summary
        $monthExpenses = Expense::whereMonth('expense_date', $date->month)
            ->whereYear('expense_date', $date->year)
            ->sum('amount');
        $monthRevenue = Order::whereMonth('created_at', $date->month)
            ->whereYear('created_at', $date->year)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        return view('admin.expenses.index', compact(
            'dailyExpenses',
            'fixedCosts',
            'variableExpenses',
            'totalFixedCosts',
            'totalVariableExpenses',
            'totalExpenses',
            'totalRevenue',
            'cogs',
            'netProfit',
            'profitMargin',
            'monthExpenses',
            'monthRevenue',
            'selectedDate'
        ));
    }

    /**
     * Store a new expense entry (Fixed cost, rent, utilities, labor, etc.).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'type' => 'required|in:fixed_cost,variable_expense,income,daily_operating',
            'category' => 'required|in:rent,labor,utilities,supplies,ingredients,other',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        Expense::create($validated);

        return redirect()->route('admin.expenses.index', ['date' => $validated['expense_date']])
            ->with('success', 'บันทึกรายการรายจ่ายเรียบร้อยแล้ว');
    }

    /**
     * Delete an expense entry.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $date = $expense->expense_date->toDateString();
        $expense->delete();

        return redirect()->route('admin.expenses.index', ['date' => $date])
            ->with('success', 'ลบรายการเรียบร้อยแล้ว');
    }
}
