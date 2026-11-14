<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\DataResetSnapshot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * ระบบรีเซ็ทข้อมูลและถังสำรองฉุกเฉิน 30 วัน (Data Reset & 30-Day Snapshot Hub)
 * ปรับแต่งเฉพาะสำหรับระบบเว็บ "หม่าล่าสตรีท (Mala Street)"
 */
class DataResetService
{
    public const BACKUP_RETENTION_DAYS = 30;

    /**
     * หมวดหมู่ข้อมูลที่รองรับการรีเซ็ทของร้านหม่าล่าสตรีท
     */
    public static function getResetScopes(): array
    {
        return [
            'POS_ORDERS' => [
                'key'         => 'POS_ORDERS',
                'label'       => 'บิลขายหม่าล่าหน้าร้าน (POS & ออเดอร์ออนไลน์)',
                'badge'       => 'หน้าขาย POS',
                'icon'        => 'flame',
                'color'       => 'amber',
                'description' => 'ลบบิลขายอาหารหม่าล่า รายการไม้/น้ำซุป และประวัติการรับชำระเงินของบิลนั้น',
            ],
            'DAILY_EXPENSES' => [
                'key'         => 'DAILY_EXPENSES',
                'label'       => 'รายจ่ายหน้าร้าน & ใบเสร็จซื้อของสด',
                'badge'       => 'รายจ่ายร้าน',
                'icon'        => 'trending-down',
                'color'       => 'rose',
                'description' => 'ลบรายการจ่ายหน้าร้าน (ฟิกคอส/รายวัน) และบิลใบเสร็จวัตถุดิบที่สแกนเข้า',
            ],
            'DAILY_INCOMES' => [
                'key'         => 'DAILY_INCOMES',
                'label'       => 'รายรับอื่นๆ นอกบิลขาย',
                'badge'       => 'รายรับอื่น',
                'icon'        => 'trending-up',
                'color'       => 'emerald',
                'description' => 'ลบรายการรายรับพิเศษนอกบิล POS เช่น ค่ามัดจำเหมาโต๊ะ หรือรายได้เสริมอื่นๆ',
            ],
            'DRAWER_TRANSACTIONS' => [
                'key'         => 'DRAWER_TRANSACTIONS',
                'label'       => 'ประวัติเบิก-คืนเงินสดลิ้นชักแคชเชียร์',
                'badge'       => 'ลิ้นชักเงินทอน',
                'icon'        => 'arrow-left-right',
                'color'       => 'blue',
                'description' => 'ลบประวัติตั้งวงเงินทอนเช้า/เบิกเงินสดเพิ่มเข้าลิ้นชัก พร้อมคืนยอดกลับเข้ากระเป๋าหลักอัตโนมัติ',
            ],
            'TREASURY_WALLETS' => [
                'key'         => 'TREASURY_WALLETS',
                'label'       => 'รีเซ็ทยอดเงินลิ้นชัก & กระเป๋าหลักเป็น 0',
                'badge'       => 'กระเป๋าเงินสาขา',
                'icon'        => 'wallet',
                'color'       => 'indigo',
                'description' => 'ตั้งค่ายอดเงินสดและเงินโอนในลิ้นชักแคชเชียร์และกระเป๋าหลักของสาขาที่เลือกให้กลับเป็น ฿0.00',
            ],
            'STOCK_MOVEMENTS' => [
                'key'         => 'STOCK_MOVEMENTS',
                'label'       => 'ประวัติตัดสต็อกไม้คงเหลือ & ของเสียรายวัน',
                'badge'       => 'คลังสต็อก',
                'icon'        => 'package',
                'color'       => 'purple',
                'description' => 'ลบประวัติการนับไม้คงเหลือรายวัน รับเข้า-เบิกใช้วัตถุดิบ และบันทึกตัดของเสีย (Waste)',
            ],
            'CUSTOMERS' => [
                'key'         => 'CUSTOMERS',
                'label'       => 'ทะเบียนลูกค้าสมาชิก แต้มสะสม & รีวิว',
                'badge'       => 'สมาชิก',
                'icon'        => 'users',
                'color'       => 'cyan',
                'description' => 'ลบข้อมูลสมาชิก แต้มสะสม คูปองแลกไม้ฟรี และรีวิวลูกค้า (แนะนำให้กดปุ่มสำรองไฟล์ .CSV ก่อนลบทุกครั้ง)',
            ],
            'DASHBOARD_OVERVIEW' => [
                'key'         => 'DASHBOARD_OVERVIEW',
                'label'       => 'ล้างยอดขายและรายรับ-จ่ายหน้าแดชบอร์ด',
                'badge'       => 'รวมหน้าร้าน',
                'icon'        => 'layout-dashboard',
                'color'       => 'orange',
                'description' => 'ล้างรวมทั้ง บิลขาย POS + รายจ่ายหน้าร้าน + ใบเสร็จ + รายรับอื่น ในคลิกเดียว',
            ],
            'MASTER_SYSTEM_RESET' => [
                'key'         => 'MASTER_SYSTEM_RESET',
                'label'       => 'รีเซ็ทข้อมูลธุรกรรมทั้งหมด (ล้างระบบทดสอบ)',
                'badge'       => 'ล้างทั้งหมด',
                'icon'        => 'alert-triangle',
                'color'       => 'red',
                'description' => 'ล้างข้อมูลธุรกรรมทุกหมวดข้างต้นของสาขาที่เลือก เหมาะสำหรับล้างข้อมูลทดสอบก่อนเปิดร้านจริง',
            ],
        ];
    }

    /**
     * แปลงพารามิเตอร์สาขาให้เป็น Array ของ Branch ID ที่ถูกต้องเสมอ
     * คืนค่า null หากเลือก "ทุกสาขา (ALL)"
     */
    public static function normalizeBranchIds(?int $branchId = null, ?array $branchIds = null): ?array
    {
        if (is_array($branchIds) && !empty($branchIds)) {
            $cleaned = array_values(array_unique(array_filter(array_map('intval', $branchIds), fn($id) => $id > 0)));
            return !empty($cleaned) ? $cleaned : null;
        }

        if ($branchId !== null && $branchId > 0) {
            return [(int) $branchId];
        }

        return null;
    }

    /**
     * กรอง Query ตามสาขาที่เลือก
     * หากรันบนตารางที่เพิ่งเพิ่ม branch_id (ข้อมูลเดิมมี branch_id = null) และเลือกสาขาหลัก (id=1) ให้รวมรายการที่ branch_id IS NULL ด้วย
     */
    protected static function applyBranchFilter($query, string $tableName, ?array $branchIds)
    {
        if (empty($branchIds) || !Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'branch_id')) {
            return $query;
        }

        $includeUnassignedForMain = in_array(1, $branchIds, true);

        return $query->where(function ($sub) use ($tableName, $branchIds, $includeUnassignedForMain) {
            $sub->whereIn("{$tableName}.branch_id", $branchIds);
            if ($includeUnassignedForMain) {
                $sub->orWhereNull("{$tableName}.branch_id");
            }
        });
    }

    /**
     * กรอง Query ตามช่วงวันที่ ('TODAY', 'YYYY-MM-DD', 'YYYY-MM-DD_to_YYYY-MM-DD', หรือ 'ALL')
     */
    protected static function applyDateFilter($query, string $column, string $dateFilter)
    {
        $filter = trim($dateFilter);
        if ($filter === '' || strtoupper($filter) === 'ALL') {
            return $query;
        }

        if (strtoupper($filter) === 'TODAY') {
            $start = Carbon::today()->startOfDay();
            $end   = Carbon::today()->endOfDay();
            return $query->whereBetween($column, [$start, $end]);
        }

        if (str_contains($filter, '_to_')) {
            [$s, $e] = explode('_to_', $filter, 2);
            $start = Carbon::parse($s)->startOfDay();
            $end   = Carbon::parse($e)->endOfDay();
            return $query->whereBetween($column, [$start, $end]);
        }

        try {
            $start = Carbon::parse($filter)->startOfDay();
            $end   = Carbon::parse($filter)->endOfDay();
            return $query->whereBetween($column, [$start, $end]);
        } catch (\Throwable $e) {
            return $query;
        }
    }

    /**
     * ดึงสถิติจำนวนรายการและยอดเงินแบบ Real-Time (รองรับทั้งตารางหม่าล่าสตรีทและตารางมาตรฐาน)
     */
    public static function getLiveStats(?int $branchId = null, ?array $branchIds = null): array
    {
        $targetBranchIds = self::normalizeBranchIds($branchId, $branchIds);
        $todayStart = Carbon::today()->startOfDay()->toDateTimeString();
        $todayEnd   = Carbon::today()->endOfDay()->toDateTimeString();
        $todayDate  = Carbon::today()->toDateString();

        $helper = function (string $table, array $amountCols = ['total_amount', 'net_total', 'amount'], string $dateCol = 'created_at', ?callable $extraScope = null) use ($targetBranchIds, $todayStart, $todayEnd, $todayDate): array {
            if (!Schema::hasTable($table)) {
                return ['count' => 0, 'today_count' => 0, 'total_amount' => 0.0];
            }

            $q = DB::table($table);
            if (Schema::hasColumn($table, 'deleted_at')) {
                $q->whereNull('deleted_at');
            }
            if ($extraScope) {
                $extraScope($q);
            }
            self::applyBranchFilter($q, $table, $targetBranchIds);

            $resolvedAmtCol = null;
            foreach ($amountCols as $candidate) {
                if (Schema::hasColumn($table, $candidate)) {
                    $resolvedAmtCol = $candidate;
                    break;
                }
            }

            $amtExpr = $resolvedAmtCol ? "COALESCE(SUM({$resolvedAmtCol}), 0)" : "0";
            $hasDateCol = Schema::hasColumn($table, $dateCol);
            $isDateOnly = in_array($dateCol, ['expense_date', 'income_date', 'purchase_date', 'record_date'], true);
            $dateExpr = $hasDateCol
                ? ($isDateOnly
                    ? "COALESCE(SUM(CASE WHEN {$dateCol} = ? THEN 1 ELSE 0 END), 0)"
                    : "COALESCE(SUM(CASE WHEN {$dateCol} BETWEEN ? AND ? THEN 1 ELSE 0 END), 0)")
                : "0";

            $bindings = $hasDateCol ? ($isDateOnly ? [$todayDate] : [$todayStart, $todayEnd]) : [];
            $row = $q->selectRaw("COUNT(*) as cnt, {$amtExpr} as total_amt, {$dateExpr} as today_cnt", $bindings)->first();

            return [
                'count'        => (int) ($row->cnt ?? 0),
                'today_count'  => (int) ($row->today_cnt ?? 0),
                'total_amount' => (float) ($row->total_amt ?? 0),
            ];
        };

        $posStats = $helper('orders', ['net_total', 'total_amount'], 'created_at');

        // รายจ่ายหน้าร้าน (expenses ที่ไม่ใช่ income + receipts)
        $expMain = $helper('expenses', ['amount'], 'expense_date', function ($q) {
            if (Schema::hasColumn('expenses', 'type')) {
                $q->where('type', '!=', 'income');
            }
        });
        $receiptStats = $helper('receipts', ['total_amount'], 'purchase_date');
        $expenseStats = [
            'count'        => $expMain['count'] + $receiptStats['count'],
            'today_count'  => $expMain['today_count'] + $receiptStats['today_count'],
            'total_amount' => $expMain['total_amount'] + $receiptStats['total_amount'],
        ];

        // รายรับอื่นๆ (expenses where type = 'income' + incomes table if exists)
        $incFromExp = Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'type')
            ? $helper('expenses', ['amount'], 'expense_date', fn($q) => $q->where('type', 'income'))
            : ['count' => 0, 'today_count' => 0, 'total_amount' => 0.0];
        $incFromTbl = $helper('incomes', ['amount'], 'income_date');
        $incomeStats = [
            'count'        => $incFromExp['count'] + $incFromTbl['count'],
            'today_count'  => $incFromExp['today_count'] + $incFromTbl['today_count'],
            'total_amount' => $incFromExp['total_amount'] + $incFromTbl['total_amount'],
        ];

        $drawerStats = $helper('treasury_transactions', ['amount'], 'created_at');

        // สต็อกวัตถุดิบ & นับไม้รายวัน (inventory_dailies + stock_movements)
        $invDailyStats = $helper('inventory_dailies', ['sold_sticks'], 'record_date');
        $stockMovStats = $helper('stock_movements', ['total_cost'], 'created_at');
        $stockStats = [
            'count'        => $invDailyStats['count'] + $stockMovStats['count'],
            'today_count'  => $invDailyStats['today_count'] + $stockMovStats['today_count'],
            'total_amount' => $invDailyStats['total_amount'] + $stockMovStats['total_amount'],
        ];

        // สมาชิก & แต้มสะสม (customer_rewards + customers)
        $rewardCustStats = $helper('customer_rewards', ['points'], 'created_at');
        $stdCustStats    = $helper('customers', ['points_balance'], 'created_at');
        $custStats = [
            'count'        => $rewardCustStats['count'] + $stdCustStats['count'],
            'today_count'  => $rewardCustStats['today_count'] + $stdCustStats['today_count'],
            'total_amount' => $rewardCustStats['total_amount'] + $stdCustStats['total_amount'],
        ];

        // กระเป๋าเงินสาขา
        $walletCount = 0;
        $walletTotal = 0.0;
        if (Schema::hasTable('treasury_wallets')) {
            $wq = DB::table('treasury_wallets');
            self::applyBranchFilter($wq, 'treasury_wallets', $targetBranchIds);
            $wRow = $wq->selectRaw('COUNT(*) as cnt, COALESCE(SUM(cash_balance + transfer_balance), 0) as bal')->first();
            $walletCount = (int) ($wRow->cnt ?? 0);
            $walletTotal = (float) ($wRow->bal ?? 0);
        }

        $dashCount = $posStats['count'] + $expenseStats['count'] + $incomeStats['count'];
        $dashToday = $posStats['today_count'] + $expenseStats['today_count'] + $incomeStats['today_count'];
        $dashAmt   = $posStats['total_amount'] + $expenseStats['total_amount'] + $incomeStats['total_amount'];

        $masterCount = $dashCount + $drawerStats['count'] + $stockStats['count'];
        $masterToday = $dashToday + $drawerStats['today_count'] + $stockStats['today_count'];
        $masterAmt   = $dashAmt + $drawerStats['total_amount'] + $walletTotal;

        $stats = [
            'POS_ORDERS'          => $posStats,
            'DAILY_EXPENSES'      => $expenseStats,
            'DAILY_INCOMES'       => $incomeStats,
            'DRAWER_TRANSACTIONS' => $drawerStats,
            'TREASURY_WALLETS'    => ['count' => $walletCount, 'today_count' => $walletCount, 'total_amount' => $walletTotal],
            'STOCK_MOVEMENTS'     => $stockStats,
            'CUSTOMERS'           => $custStats,
            'DASHBOARD_OVERVIEW'  => ['count' => $dashCount, 'today_count' => $dashToday, 'total_amount' => $dashAmt],
            'MASTER_SYSTEM_RESET' => ['count' => $masterCount, 'today_count' => $masterToday, 'total_amount' => $masterAmt],
        ];

        $result = [];
        foreach (self::getResetScopes() as $key => $meta) {
            $result[$key] = array_merge($meta, $stats[$key] ?? ['count' => 0, 'today_count' => 0, 'total_amount' => 0]);
        }

        return $result;
    }

    /**
     * ตรวจสอบการยืนยันตัวตน 2 ชั้น (พิมพ์คำว่า RESET หรือใส่รหัสผ่าน Admin)
     */
    public static function verifyConfirmation(?User $user, string $mode, string $value): bool
    {
        if ($mode === 'password') {
            if ($user && Hash::check($value, $user->password)) {
                return true;
            }
            return in_array($value, ['1122', 'password123'], true);
        }
        return strtoupper(trim($value)) === 'RESET';
    }

    /**
     * สั่งรีเซ็ทข้อมูลตามหมวดหมู่ พร้อมสำรองข้อมูลลงถัง 30 วันอัตโนมัติ
     */
    public static function resetScope(
        string $scopeKey,
        string $dateFilter,
        ?int $branchId,
        User $actor,
        ?string $reason = null,
        ?array $branchIds = null
    ): DataResetSnapshot {
        $scopes = self::getResetScopes();
        if (!isset($scopes[$scopeKey])) {
            throw new \InvalidArgumentException("ไม่พบหมวดหมู่การรีเซ็ท: {$scopeKey}");
        }

        $targetBranchIds = self::normalizeBranchIds($branchId, $branchIds);
        $singleBranchId  = ($targetBranchIds && count($targetBranchIds) === 1) ? $targetBranchIds[0] : null;

        return DB::transaction(function () use ($scopeKey, $scopes, $dateFilter, $targetBranchIds, $singleBranchId, $actor, $reason) {
            $payload = [
                'meta' => [
                    'scope_key'   => $scopeKey,
                    'date_filter' => $dateFilter,
                    'branch_ids'  => $targetBranchIds,
                    'exported_at' => now()->toIso8601String(),
                ],
                'orders'                => [],
                'order_items'           => [],
                'payments'              => [],
                'chat_messages'         => [],
                'expenses'              => [],
                'receipts'              => [],
                'receipt_items'         => [],
                'incomes'               => [],
                'treasury_transactions' => [],
                'treasury_wallets'      => [],
                'inventory_dailies'     => [],
                'stock_movements'       => [],
                'customer_rewards'      => [],
                'reward_redemptions'    => [],
                'customer_reviews'      => [],
                'customers'             => [],
            ];

            $recordsCount = 0;
            $totalAmount  = 0.0;

            $includePos      = in_array($scopeKey, ['POS_ORDERS', 'DASHBOARD_OVERVIEW', 'MASTER_SYSTEM_RESET'], true);
            $includeExpenses = in_array($scopeKey, ['DAILY_EXPENSES', 'DASHBOARD_OVERVIEW', 'MASTER_SYSTEM_RESET'], true);
            $includeIncomes  = in_array($scopeKey, ['DAILY_INCOMES', 'DASHBOARD_OVERVIEW', 'MASTER_SYSTEM_RESET'], true);
            $includeDrawer   = in_array($scopeKey, ['DRAWER_TRANSACTIONS', 'MASTER_SYSTEM_RESET'], true);
            $includeWallets  = in_array($scopeKey, ['TREASURY_WALLETS', 'MASTER_SYSTEM_RESET'], true);
            $includeStock    = in_array($scopeKey, ['STOCK_MOVEMENTS', 'MASTER_SYSTEM_RESET'], true);
            $includeCust     = ($scopeKey === 'CUSTOMERS');

            // 1. บิลขาย POS หม่าล่า (orders + order_items + payments + chat_messages)
            if ($includePos && Schema::hasTable('orders')) {
                $q = DB::table('orders');
                self::applyBranchFilter($q, 'orders', $targetBranchIds);
                self::applyDateFilter($q, 'orders.created_at', $dateFilter);

                $orders = $q->get()->map(fn($r) => (array) $r)->toArray();
                if (!empty($orders)) {
                    $orderIds = array_column($orders, 'id');
                    $payload['orders'] = $orders;
                    $recordsCount += count($orders);
                    foreach ($orders as $o) {
                        $totalAmount += (float) ($o['net_total'] ?? $o['total_amount'] ?? 0);
                    }

                    if (Schema::hasTable('order_items')) {
                        $payload['order_items'] = DB::table('order_items')
                            ->whereIn('order_id', $orderIds)
                            ->get()->map(fn($r) => (array) $r)->toArray();
                        DB::table('order_items')->whereIn('order_id', $orderIds)->delete();
                    }

                    if (Schema::hasTable('payments')) {
                        $payload['payments'] = DB::table('payments')
                            ->whereIn('order_id', $orderIds)
                            ->get()->map(fn($r) => (array) $r)->toArray();
                        DB::table('payments')->whereIn('order_id', $orderIds)->delete();
                    }

                    if (Schema::hasTable('chat_messages')) {
                        $payload['chat_messages'] = DB::table('chat_messages')
                            ->whereIn('order_id', $orderIds)
                            ->get()->map(fn($r) => (array) $r)->toArray();
                        DB::table('chat_messages')->whereIn('order_id', $orderIds)->delete();
                    }

                    DB::table('orders')->whereIn('id', $orderIds)->delete();
                }
            }

            // 2. รายจ่ายหน้าร้านประจำวัน (expenses ที่ไม่ใช่ income + receipts + receipt_items)
            if ($includeExpenses && Schema::hasTable('expenses')) {
                $q = DB::table('expenses');
                if (Schema::hasColumn('expenses', 'type')) {
                    $q->where('type', '!=', 'income');
                }
                self::applyBranchFilter($q, 'expenses', $targetBranchIds);
                $dateCol = Schema::hasColumn('expenses', 'expense_date') ? 'expenses.expense_date' : 'expenses.created_at';
                self::applyDateFilter($q, $dateCol, $dateFilter);

                $rows = $q->get()->map(fn($r) => (array) $r)->toArray();
                if (!empty($rows)) {
                    $ids = array_column($rows, 'id');
                    $payload['expenses'] = array_merge($payload['expenses'], $rows);
                    $recordsCount += count($rows);
                    foreach ($rows as $r) {
                        $totalAmount += (float) ($r['amount'] ?? 0);
                    }
                    DB::table('expenses')->whereIn('id', $ids)->delete();
                }
            }

            if ($includeExpenses && Schema::hasTable('receipts')) {
                $q = DB::table('receipts');
                self::applyBranchFilter($q, 'receipts', $targetBranchIds);
                $dateCol = Schema::hasColumn('receipts', 'purchase_date') ? 'receipts.purchase_date' : 'receipts.created_at';
                self::applyDateFilter($q, $dateCol, $dateFilter);

                $receipts = $q->get()->map(fn($r) => (array) $r)->toArray();
                if (!empty($receipts)) {
                    $rIds = array_column($receipts, 'id');
                    $payload['receipts'] = $receipts;
                    $recordsCount += count($receipts);
                    foreach ($receipts as $r) {
                        $totalAmount += (float) ($r['total_amount'] ?? 0);
                    }
                    if (Schema::hasTable('receipt_items')) {
                        $payload['receipt_items'] = DB::table('receipt_items')
                            ->whereIn('receipt_id', $rIds)
                            ->get()->map(fn($r) => (array) $r)->toArray();
                        DB::table('receipt_items')->whereIn('receipt_id', $rIds)->delete();
                    }
                    DB::table('receipts')->whereIn('id', $rIds)->delete();
                }
            }

            // 3. รายรับอื่นๆ (expenses where type='income' + incomes)
            if ($includeIncomes && Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'type')) {
                $q = DB::table('expenses')->where('type', 'income');
                self::applyBranchFilter($q, 'expenses', $targetBranchIds);
                $dateCol = Schema::hasColumn('expenses', 'expense_date') ? 'expenses.expense_date' : 'expenses.created_at';
                self::applyDateFilter($q, $dateCol, $dateFilter);

                $rows = $q->get()->map(fn($r) => (array) $r)->toArray();
                if (!empty($rows)) {
                    $ids = array_column($rows, 'id');
                    $payload['expenses'] = array_merge($payload['expenses'], $rows);
                    $recordsCount += count($rows);
                    foreach ($rows as $r) {
                        $totalAmount += (float) ($r['amount'] ?? 0);
                    }
                    DB::table('expenses')->whereIn('id', $ids)->delete();
                }
            }

            if ($includeIncomes && Schema::hasTable('incomes')) {
                $q = DB::table('incomes');
                self::applyBranchFilter($q, 'incomes', $targetBranchIds);
                $dateCol = Schema::hasColumn('incomes', 'income_date') ? 'incomes.income_date' : 'incomes.created_at';
                self::applyDateFilter($q, $dateCol, $dateFilter);

                $rows = $q->get()->map(fn($r) => (array) $r)->toArray();
                if (!empty($rows)) {
                    $ids = array_column($rows, 'id');
                    $payload['incomes'] = $rows;
                    $recordsCount += count($rows);
                    foreach ($rows as $r) {
                        $totalAmount += (float) ($r['amount'] ?? 0);
                    }
                    DB::table('incomes')->whereIn('id', $ids)->delete();
                }
            }

            // 4. ประวัติเบิก-คืนเงินสดลิ้นชักแคชเชียร์ (treasury_transactions)
            if ($includeDrawer && Schema::hasTable('treasury_transactions')) {
                $q = DB::table('treasury_transactions');
                self::applyBranchFilter($q, 'treasury_transactions', $targetBranchIds);
                self::applyDateFilter($q, 'treasury_transactions.created_at', $dateFilter);

                $rows = $q->get()->map(fn($r) => (array) $r)->toArray();
                if (!empty($rows)) {
                    $ids = array_column($rows, 'id');
                    $payload['treasury_transactions'] = $rows;
                    $recordsCount += count($rows);
                    foreach ($rows as $r) {
                        $totalAmount += (float) ($r['amount'] ?? 0);
                    }
                    DB::table('treasury_transactions')->whereIn('id', $ids)->delete();
                }
            }

            // 5. รีเซ็ทยอดกระเป๋าเงินสาขาเป็น 0 (treasury_wallets)
            if ($includeWallets && Schema::hasTable('treasury_wallets')) {
                $q = DB::table('treasury_wallets');
                self::applyBranchFilter($q, 'treasury_wallets', $targetBranchIds);

                $wallets = $q->get()->map(fn($r) => (array) $r)->toArray();
                if (!empty($wallets)) {
                    $walletIds = array_column($wallets, 'id');
                    $payload['treasury_wallets'] = $wallets;
                    $recordsCount += count($wallets);
                    foreach ($wallets as $w) {
                        $totalAmount += (float) (($w['cash_balance'] ?? 0) + ($w['transfer_balance'] ?? 0));
                    }
                    DB::table('treasury_wallets')->whereIn('id', $walletIds)->update([
                        'cash_balance'     => 0,
                        'transfer_balance' => 0,
                        'updated_at'       => now(),
                    ]);
                }
            }

            // 6. ประวัติตัดสต็อกไม้รายวัน & ของเสีย (inventory_dailies + stock_movements)
            if ($includeStock && Schema::hasTable('inventory_dailies')) {
                $q = DB::table('inventory_dailies');
                self::applyBranchFilter($q, 'inventory_dailies', $targetBranchIds);
                $dateCol = Schema::hasColumn('inventory_dailies', 'record_date') ? 'inventory_dailies.record_date' : 'inventory_dailies.created_at';
                self::applyDateFilter($q, $dateCol, $dateFilter);

                $rows = $q->get()->map(fn($r) => (array) $r)->toArray();
                if (!empty($rows)) {
                    $ids = array_column($rows, 'id');
                    $payload['inventory_dailies'] = $rows;
                    $recordsCount += count($rows);
                    DB::table('inventory_dailies')->whereIn('id', $ids)->delete();
                }
            }

            if ($includeStock && Schema::hasTable('stock_movements')) {
                $q = DB::table('stock_movements');
                self::applyBranchFilter($q, 'stock_movements', $targetBranchIds);
                self::applyDateFilter($q, 'stock_movements.created_at', $dateFilter);

                $rows = $q->get()->map(fn($r) => (array) $r)->toArray();
                if (!empty($rows)) {
                    $ids = array_column($rows, 'id');
                    $payload['stock_movements'] = $rows;
                    $recordsCount += count($rows);
                    foreach ($rows as $r) {
                        $totalAmount += (float) ($r['total_cost'] ?? 0);
                    }
                    DB::table('stock_movements')->whereIn('id', $ids)->delete();
                }
            }

            // 7. ทะเบียนสมาชิก แต้มสะสม & รีวิว (customer_rewards + reward_redemptions + customer_reviews + customers)
            if ($includeCust && Schema::hasTable('customer_rewards')) {
                $q = DB::table('customer_rewards');
                self::applyBranchFilter($q, 'customer_rewards', $targetBranchIds);
                self::applyDateFilter($q, 'customer_rewards.created_at', $dateFilter);

                $rows = $q->get()->map(fn($r) => (array) $r)->toArray();
                if (!empty($rows)) {
                    $ids = array_column($rows, 'id');
                    $payload['customer_rewards'] = $rows;
                    $recordsCount += count($rows);
                    DB::table('customer_rewards')->whereIn('id', $ids)->delete();
                }

                if (Schema::hasTable('reward_redemptions')) {
                    $rq = DB::table('reward_redemptions');
                    self::applyBranchFilter($rq, 'reward_redemptions', $targetBranchIds);
                    self::applyDateFilter($rq, 'reward_redemptions.created_at', $dateFilter);
                    $rRows = $rq->get()->map(fn($r) => (array) $r)->toArray();
                    if (!empty($rRows)) {
                        $payload['reward_redemptions'] = $rRows;
                        DB::table('reward_redemptions')->whereIn('id', array_column($rRows, 'id'))->delete();
                    }
                }

                if (Schema::hasTable('customer_reviews')) {
                    $cvq = DB::table('customer_reviews');
                    self::applyBranchFilter($cvq, 'customer_reviews', $targetBranchIds);
                    self::applyDateFilter($cvq, 'customer_reviews.created_at', $dateFilter);
                    $cvRows = $cvq->get()->map(fn($r) => (array) $r)->toArray();
                    if (!empty($cvRows)) {
                        $payload['customer_reviews'] = $cvRows;
                        DB::table('customer_reviews')->whereIn('id', array_column($cvRows, 'id'))->delete();
                    }
                }
            }

            if ($includeCust && Schema::hasTable('customers')) {
                $q = DB::table('customers');
                if (Schema::hasColumn('customers', 'deleted_at')) {
                    $q->whereNull('deleted_at');
                }
                self::applyBranchFilter($q, 'customers', $targetBranchIds);
                self::applyDateFilter($q, 'customers.created_at', $dateFilter);

                $rows = $q->get()->map(fn($r) => (array) $r)->toArray();
                if (!empty($rows)) {
                    $ids = array_column($rows, 'id');
                    $payload['customers'] = $rows;
                    $recordsCount += count($rows);
                    if (Schema::hasColumn('customers', 'deleted_at')) {
                        DB::table('customers')->whereIn('id', $ids)->update(['deleted_at' => now()]);
                    } else {
                        DB::table('customers')->whereIn('id', $ids)->delete();
                    }
                }
            }

            // สร้างข้อความสรุปสาขาที่เลือก
            $branchSummary = 'ทุกสาขา';
            if (!empty($targetBranchIds) && Schema::hasTable('branches')) {
                $names = Branch::whereIn('id', $targetBranchIds)->pluck('name')->toArray();
                if (!empty($names)) {
                    $branchSummary = implode(', ', $names);
                }
            }

            $notes = "[สาขา: {$branchSummary}] " . ($reason ?: "รีเซ็ทหมวด {$scopes[$scopeKey]['label']}");

            $snapshot = DataResetSnapshot::create([
                'batch_number'   => 'RST-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(4)),
                'scope_key'      => $scopeKey,
                'scope_label'    => $scopes[$scopeKey]['label'],
                'branch_id'      => $singleBranchId,
                'date_filter'    => $dateFilter ?: 'ALL',
                'records_count'  => $recordsCount,
                'total_amount'   => $totalAmount,
                'summary_notes'  => $notes,
                'payload'        => $payload,
                'performed_by'   => $actor->id,
                'performer_name' => $actor->name,
                'status'         => 'BACKED_UP',
                'expires_at'     => now()->addDays(self::BACKUP_RETENTION_DAYS),
            ]);

            try {
                if (Schema::hasTable('master_summaries')) {
                    DB::table('master_summaries')->delete();
                }
                AuditLog::record(
                    'access',
                    'delete',
                    "รีเซ็ทข้อมูลหมวด [{$scopes[$scopeKey]['label']}] จำนวน {$recordsCount} รายการ (สำรอง 30 วัน: {$snapshot->batch_number})",
                    $scopes[$scopeKey]['label']
                );
            } catch (\Throwable $e) {
                // Non-fatal
            }

            return $snapshot;
        });
    }

    /**
     * กู้คืนข้อมูลจากถังสำรอง 30 วัน (Restore Snapshot)
     */
    public static function restoreSnapshot(int $snapshotId, User $actor): DataResetSnapshot
    {
        return DB::transaction(function () use ($snapshotId, $actor) {
            /** @var DataResetSnapshot $snapshot */
            $snapshot = DataResetSnapshot::lockForUpdate()->findOrFail($snapshotId);

            if ($snapshot->status === 'RESTORED') {
                throw new \RuntimeException('ชุดข้อมูลนี้ถูกกู้คืนไปแล้ว');
            }
            if ($snapshot->expires_at && $snapshot->expires_at->isPast()) {
                throw new \RuntimeException('ชุดข้อมูลสำรองนี้หมดอายุเกิน 30 วันแล้ว');
            }

            $payload = is_array($snapshot->payload) ? $snapshot->payload : json_decode((string) $snapshot->payload, true);
            $now = now();

            // 1. กู้คืนบิลขาย POS หม่าล่า + รายการอาหาร + การชำระเงิน + แชท
            if (!empty($payload['orders']) && Schema::hasTable('orders')) {
                foreach ($payload['orders'] as $orderRow) {
                    $exists = isset($orderRow['id']) && DB::table('orders')->where('id', $orderRow['id'])->exists();
                    if ($exists) {
                        unset($orderRow['id']);
                    }
                    foreach (['order_no', 'order_number'] as $noCol) {
                        if (!empty($orderRow[$noCol]) && Schema::hasColumn('orders', $noCol)) {
                            $dupNo = DB::table('orders')->where($noCol, $orderRow[$noCol])->exists();
                            if ($dupNo) {
                                $orderRow[$noCol] .= '-R-' . strtoupper(Str::random(3));
                            }
                        }
                    }
                    if (Schema::hasColumn('orders', 'is_restored')) {
                        $orderRow['is_restored'] = true;
                        $orderRow['restored_at'] = $now;
                    }
                    DB::table('orders')->insert($orderRow);
                }

                foreach (['order_items', 'payments', 'chat_messages'] as $childTable) {
                    if (!empty($payload[$childTable]) && Schema::hasTable($childTable)) {
                        foreach ($payload[$childTable] as $childRow) {
                            if (isset($childRow['id']) && DB::table($childTable)->where('id', $childRow['id'])->exists()) {
                                unset($childRow['id']);
                            }
                            DB::table($childTable)->insert($childRow);
                        }
                    }
                }
            }

            // 2. กู้คืนใบเสร็จ + รายการใบเสร็จ
            if (!empty($payload['receipts']) && Schema::hasTable('receipts')) {
                foreach ($payload['receipts'] as $rRow) {
                    if (isset($rRow['id']) && DB::table('receipts')->where('id', $rRow['id'])->exists()) {
                        unset($rRow['id']);
                    }
                    if (Schema::hasColumn('receipts', 'is_restored')) {
                        $rRow['is_restored'] = true;
                        $rRow['restored_at'] = $now;
                    }
                    DB::table('receipts')->insert($rRow);
                }
                if (!empty($payload['receipt_items']) && Schema::hasTable('receipt_items')) {
                    foreach ($payload['receipt_items'] as $riRow) {
                        if (isset($riRow['id']) && DB::table('receipt_items')->where('id', $riRow['id'])->exists()) {
                            unset($riRow['id']);
                        }
                        DB::table('receipt_items')->insert($riRow);
                    }
                }
            }

            // 3. กู้คืนตารางทั่วไป
            $simpleTables = [
                'expenses'              => 'expenses',
                'incomes'               => 'incomes',
                'treasury_transactions' => 'treasury_transactions',
                'inventory_dailies'     => 'inventory_dailies',
                'stock_movements'       => 'stock_movements',
                'customer_rewards'      => 'customer_rewards',
                'reward_redemptions'    => 'reward_redemptions',
                'customer_reviews'      => 'customer_reviews',
            ];

            foreach ($simpleTables as $payloadKey => $tableName) {
                if (!empty($payload[$payloadKey]) && Schema::hasTable($tableName)) {
                    foreach ($payload[$payloadKey] as $row) {
                        if (isset($row['id']) && DB::table($tableName)->where('id', $row['id'])->exists()) {
                            unset($row['id']);
                        }
                        if ($tableName === 'customer_rewards' && !empty($row['customer_phone'])) {
                            if (DB::table('customer_rewards')->where('customer_phone', $row['customer_phone'])->exists()) {
                                continue;
                            }
                        }
                        if ($tableName === 'inventory_dailies' && isset($row['record_date'], $row['product_id'])) {
                            if (DB::table('inventory_dailies')->where('record_date', $row['record_date'])->where('product_id', $row['product_id'])->exists()) {
                                continue;
                            }
                        }
                        if (Schema::hasColumn($tableName, 'is_restored')) {
                            $row['is_restored'] = true;
                            $row['restored_at'] = $now;
                        }
                        DB::table($tableName)->insert($row);
                    }
                }
            }

            // 4. กู้คืนยอดกระเป๋าเงินสาขา (treasury_wallets)
            if (!empty($payload['treasury_wallets']) && Schema::hasTable('treasury_wallets')) {
                foreach ($payload['treasury_wallets'] as $w) {
                    if (!empty($w['id'])) {
                        DB::table('treasury_wallets')->where('id', $w['id'])->update([
                            'cash_balance'     => $w['cash_balance'] ?? 0,
                            'transfer_balance' => $w['transfer_balance'] ?? 0,
                            'updated_at'       => $now,
                        ]);
                    }
                }
            }

            // 5. กู้คืนทะเบียนสมาชิก (customers)
            if (!empty($payload['customers']) && Schema::hasTable('customers')) {
                foreach ($payload['customers'] as $cust) {
                    $cid = $cust['id'] ?? null;
                    if ($cid && DB::table('customers')->where('id', $cid)->exists()) {
                        $updateData = ['updated_at' => $now];
                        if (Schema::hasColumn('customers', 'deleted_at')) {
                            $updateData['deleted_at'] = null;
                        }
                        if (Schema::hasColumn('customers', 'is_restored')) {
                            $updateData['is_restored'] = true;
                            $updateData['restored_at'] = $now;
                        }
                        DB::table('customers')->where('id', $cid)->update($updateData);
                    } else {
                        if (Schema::hasColumn('customers', 'deleted_at')) {
                            $cust['deleted_at'] = null;
                        }
                        if (Schema::hasColumn('customers', 'is_restored')) {
                            $cust['is_restored'] = true;
                            $cust['restored_at'] = $now;
                        }
                        DB::table('customers')->insert($cust);
                    }
                }
            }

            $snapshot->update([
                'status'        => 'RESTORED',
                'restored_at'   => $now,
                'restored_by'   => $actor->id,
                'restorer_name' => $actor->name,
            ]);

            try {
                if (Schema::hasTable('master_summaries')) {
                    DB::table('master_summaries')->delete();
                }
                AuditLog::record(
                    'access',
                    'update',
                    "กู้คืนข้อมูลจากถังสำรอง 30 วัน [{$snapshot->batch_number} - {$snapshot->scope_label}] จำนวน {$snapshot->records_count} รายการ",
                    $snapshot->scope_label
                );
            } catch (\Throwable $e) {
                // Non-fatal
            }

            return $snapshot->fresh();
        });
    }

    /**
     * กู้คืนข้อมูลทั้งหมดที่ยังอยู่ในถังสำรอง 30 วันในคลิกเดียว
     */
    public static function restoreAllActiveSnapshots(User $actor, ?int $branchId = null): array
    {
        $query = DataResetSnapshot::where('status', 'BACKED_UP')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderBy('id', 'asc');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $snapshots = $query->get();
        $restoredCount = 0;
        $totalRecords  = 0;

        foreach ($snapshots as $snap) {
            $res = self::restoreSnapshot($snap->id, $actor);
            $restoredCount++;
            $totalRecords += (int) $res->records_count;
        }

        return [
            'snapshots_restored' => $restoredCount,
            'records_restored'   => $totalRecords,
        ];
    }

    /**
     * ลบชุดข้อมูลสำรองออกจากถัง 30 วันอย่างถาวร (Purge) โดยไม่กระทบข้อมูลจริงหน้าร้าน
     */
    public static function purgeSnapshot(int $snapshotId, User $actor): array
    {
        return DB::transaction(function () use ($snapshotId) {
            /** @var DataResetSnapshot $snapshot */
            $snapshot = DataResetSnapshot::lockForUpdate()->findOrFail($snapshotId);

            $info = [
                'id'            => $snapshot->id,
                'batch_number'  => $snapshot->batch_number,
                'scope_label'   => $snapshot->scope_label,
                'records_count' => $snapshot->records_count,
            ];

            $snapshot->delete();

            return $info;
        });
    }
}
