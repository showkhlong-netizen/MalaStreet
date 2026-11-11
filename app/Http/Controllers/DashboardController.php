<?php

namespace App\Http\Controllers;

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

        // Orders summary today
        $todayOrders = Order::whereDate('created_at', $today)->get();
        $posOrders = $todayOrders->where('order_type', 'pos');
        $onlineOrders = $todayOrders->where('order_type', 'online');

        $totalSalesToday = $todayOrders->where('payment_status', 'paid')->sum('total_amount');
        $posSalesToday = $posOrders->where('payment_status', 'paid')->sum('total_amount');
        $onlineSalesToday = $onlineOrders->where('payment_status', 'paid')->sum('total_amount');

        // Total sticks sold today
        $totalSticksSold = OrderItem::whereHas('order', function ($query) use ($today) {
            $query->whereDate('created_at', $today)->where('payment_status', 'paid');
        })->sum('quantity_sticks');

        // Total sticks in current stock
        $totalSticksInStock = Product::sum('stock_sticks');

        // Today's Purchases / Ingestion Costs (from Receipts)
        $todayReceiptsCost = Receipt::whereDate('purchase_date', $today)->sum('total_amount');

        // Today's Expenses (Fixed costs + Utilities)
        $todayExpenses = Expense::whereDate('expense_date', $today)->sum('amount');

        // Est. Net Profit
        $netProfitToday = $totalSalesToday - $todayReceiptsCost - $todayExpenses;

        // Recent Price Alerts / Fluctuations (ไส้กรอกเมื่อวาน 60 วันนี้ 65)
        $recentPriceAlerts = PriceHistory::with('rawMaterial')->orderByDesc('recorded_at')->orderByDesc('id')->take(4)->get();

        // Recent Orders
        $recentOrders = Order::with('items')->orderByDesc('created_at')->take(6)->get();

        // Low stock products warning (< 10 sticks)
        $lowStockProducts = Product::where('stock_sticks', '<=', 10)->get();

        // Online Status (preview vs live)
        $onlineOrderStatus = Setting::get('online_order_status', 'preview');

        return view('admin.dashboard', compact(
            'totalSalesToday',
            'posSalesToday',
            'onlineSalesToday',
            'todayOrders',
            'totalSticksSold',
            'totalSticksInStock',
            'todayReceiptsCost',
            'todayExpenses',
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

        return redirect()->back()->with('success', $msg);
    }
}
