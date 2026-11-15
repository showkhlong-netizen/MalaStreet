<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Expense;
use App\Models\InventoryDaily;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Store Overview & Executive Dashboard.
     */
    public function index(): View
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Orders summary today
        $todayOrders = Order::whereDate('created_at', $today)->get();
        $todayPaidOrders = $todayOrders->where('payment_status', 'paid');
        $posOrders = $todayOrders->where('order_type', 'pos');
        $onlineOrders = $todayOrders->where('order_type', 'online');

        // Row 1: Financial KPIs
        $cashSalesToday = $todayPaidOrders->where('payment_method', 'cash')->sum('total_amount');
        $cashOrdersCount = $todayPaidOrders->where('payment_method', 'cash')->count();

        $transferSalesToday = $todayPaidOrders->whereIn('payment_method', ['promptpay', 'transfer', 'qr'])->sum('total_amount');
        $transferOrdersCount = $todayPaidOrders->whereIn('payment_method', ['promptpay', 'transfer', 'qr'])->count();

        $onlineSalesToday = $onlineOrders->where('payment_status', 'paid')->sum('total_amount');
        $onlineOrdersCount = $onlineOrders->where('payment_status', 'paid')->count();

        $appOrderTypes = ['app', 'grab', 'lineman', 'shopee', 'delivery_app'];
        $appSalesToday = $todayPaidOrders->filter(function (Order $order) use ($appOrderTypes) {
            return in_array($order->order_type, $appOrderTypes, true)
                || $order->payment_method === 'app'
                || ($order->order_type === 'online' && in_array($order->payment_method, ['promptpay', 'transfer', 'qr'], true));
        })->sum('total_amount');
        $appOrdersCount = $todayPaidOrders->filter(function (Order $order) use ($appOrderTypes) {
            return in_array($order->order_type, $appOrderTypes, true)
                || $order->payment_method === 'app'
                || ($order->order_type === 'online' && in_array($order->payment_method, ['promptpay', 'transfer', 'qr'], true));
        })->count();

        $totalSalesToday = $todayPaidOrders->sum('total_amount');
        $posSalesToday = $posOrders->where('payment_status', 'paid')->sum('total_amount');

        // Today's Purchases / Ingestion Costs (from Receipts) + Fixed/Variable Expenses
        $todayReceiptsCost = Receipt::whereDate('purchase_date', $today)->sum('total_amount');
        $todayExpenses = Expense::whereDate('expense_date', $today)->sum('amount');
        $totalExpensesToday = $todayReceiptsCost + $todayExpenses;

        // Est. Net Profit (kept for compatibility)
        $netProfitToday = $totalSalesToday - $todayReceiptsCost - $todayExpenses;

        // Row 2: Skewers, Stock, Waste, Yesterday Carryover, Sauce & Staff Withdrawals
        $totalSticksSold = OrderItem::whereHas('order', function ($query) use ($today) {
            $query->whereDate('created_at', $today)->where('payment_status', 'paid');
        })->sum('quantity_sticks');

        $totalSticksInStock = Product::sum('stock_sticks');

        $todayInventoryRows = InventoryDaily::whereDate('record_date', $today)->get();
        $wasteSticksToday = (int) $todayInventoryRows->sum('waste_sticks');

        $yesterdayCarryoverSticks = (int) $todayInventoryRows->sum('opening_sticks');
        if ($yesterdayCarryoverSticks === 0) {
            $yesterdayRows = InventoryDaily::whereDate('record_date', $yesterday)->get();
            $yesterdayCarryoverSticks = (int) $yesterdayRows->sum(function (InventoryDaily $row) {
                return $row->actual_closing_sticks ?? $row->expected_closing_sticks ?? 0;
            });
        }

        // Sauce sold today (quantity + total money)
        $sauceItemsQuery = OrderItem::whereHas('order', function ($query) use ($today) {
            $query->whereDate('created_at', $today)->where('payment_status', 'paid');
        })->where(function ($query) {
            $query->where('product_name', 'like', '%น้ำจิ้ม%')
                ->orWhereHas('product.category', function ($catQuery) {
                    $catQuery->where('name', 'like', '%น้ำจิ้ม%')
                        ->orWhere('slug', 'like', '%sauce%');
                });
        });
        $sauceSoldCount = (int) (clone $sauceItemsQuery)->sum('quantity_sticks');
        $sauceSalesAmount = (float) (clone $sauceItemsQuery)->sum('subtotal');

        // Staff withdrawals today (orders/notes marked as เบิก)
        $staffWithdrawOrders = $todayOrders->filter(function (Order $order) {
            $haystack = mb_strtolower(($order->order_type ?? '') . ' ' . ($order->customer_name ?? '') . ' ' . ($order->notes ?? ''), 'UTF-8');
            return $order->order_type === 'staff_withdraw' || str_contains($haystack, 'เบิก');
        });
        $staffWithdrawCount = $staffWithdrawOrders->count();
        $staffWithdrawSticks = 0;
        if ($staffWithdrawCount > 0) {
            $staffWithdrawSticks = (int) OrderItem::whereIn('order_id', $staffWithdrawOrders->pluck('id'))->sum('quantity_sticks');
        }

        // Recent Price Alerts / Fluctuations (ไส้กรอกเมื่อวาน 60 วันนี้ 65)
        $recentPriceAlerts = PriceHistory::with('rawMaterial')->orderByDesc('recorded_at')->orderByDesc('id')->take(4)->get();

        // Recent Orders
        $recentOrders = Order::with('items')->orderByDesc('created_at')->take(6)->get();

        // Low stock products warning (< 10 sticks)
        $lowStockProducts = Product::where('stock_sticks', '<=', 10)->get();

        // Online Status (preview vs live)
        $onlineOrderStatus = Setting::get('online_order_status', 'preview');

        return view('admin.dashboard', compact(
            'cashSalesToday',
            'cashOrdersCount',
            'transferSalesToday',
            'transferOrdersCount',
            'onlineSalesToday',
            'onlineOrdersCount',
            'appSalesToday',
            'appOrdersCount',
            'totalSalesToday',
            'posSalesToday',
            'totalExpensesToday',
            'todayReceiptsCost',
            'todayExpenses',
            'todayOrders',
            'totalSticksSold',
            'totalSticksInStock',
            'wasteSticksToday',
            'yesterdayCarryoverSticks',
            'sauceSoldCount',
            'sauceSalesAmount',
            'staffWithdrawCount',
            'staffWithdrawSticks',
            'netProfitToday',
            'recentPriceAlerts',
            'recentOrders',
            'lowStockProducts',
            'onlineOrderStatus'
        ));
    }

    /**
     * Toggle online ordering mode between 'preview' and 'live'.
     */
    public function toggleOnlineMode(Request $request): RedirectResponse
    {
        $targetMode = $request->input('mode', 'preview');
        Setting::set('online_order_status', $targetMode);

        $msg = $targetMode === 'live' 
            ? 'เปิดระบบสั่งออนไลน์รับออเดอร์จริงเรียบร้อยแล้ว!' 
            : 'เปลี่ยนระบบสั่งออนไลน์เป็น "โหมดพรีวิว (Preview Mode)" เรียบร้อยแล้ว';

        AuditLog::record(
            'dashboard',
            'update',
            $msg,
            "Online Order Mode: {$targetMode}",
            'setting',
            'online_order_status'
        );

        return redirect()->back()->with('success', $msg);
    }
}
