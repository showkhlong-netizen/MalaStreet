<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\User;
use App\Support\ThaiDate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccessControlController extends Controller
{
    /**
     * Display the Access Control & Permissions Interface (การเข้าถึง & สิทธิ์การใช้งาน).
     * Matches the design and features in Image 2.
     */
    public function index(Request $request): View
    {
        $currentTab = $request->query('tab', 'permissions'); // 'users', 'permissions', 'autologout', 'register', 'reset'
        $selectedUserId = $request->query('user_id');

        // Initial default mock users (Matches Image 2)
        $defaultMockUsers = [
            [
                'id' => 'usr_001',
                'name' => 'อัฐฮา บู...',
                'full_name' => 'อัฐฮา บูเก๊ะซามี (แอดมินระบบ)',
                'email' => 'autha@malastreet.com',
                'phone' => '081-111-2222',
                'role' => 'Admin',
                'role_label' => 'ผู้ดูแลระบบสูงสุด',
                'initials' => 'OU',
                'color' => 'bg-blue-600',
                'status' => 'offline',
                'status_label' => 'ออฟไลน์',
                'branch' => 'สาขายะลา',
                'is_test' => true,
            ],
            [
                'id' => 'usr_002',
                'name' => 'แอดมิน...',
                'full_name' => 'แอดมินหน้าร้าน สาขาหลัก',
                'email' => 'admin.front@malastreet.com',
                'phone' => '081-222-3333',
                'role' => 'Admin',
                'role_label' => 'ผู้จัดการร้าน',
                'initials' => 'AD',
                'color' => 'bg-emerald-600',
                'status' => 'offline',
                'status_label' => 'ออฟไลน์',
                'branch' => 'สาขายะลา',
                'is_test' => true,
            ],
            [
                'id' => 'usr_003',
                'name' => 'fakkih',
                'full_name' => 'Fakkih มะดีเยาะ (แคชเชียร์ 1)',
                'email' => 'fakkih@malastreet.com',
                'phone' => '081-333-4444',
                'role' => 'Admin',
                'role_label' => 'แคชเชียร์หน้าร้าน',
                'initials' => 'FA',
                'color' => 'bg-slate-900',
                'status' => 'offline',
                'status_label' => 'ออฟไลน์',
                'branch' => 'สาขายะลา',
                'is_test' => true,
            ],
            [
                'id' => 'usr_004',
                'name' => 'KRDN S...',
                'full_name' => 'KRDN Somchai (หัวหน้าเชฟ)',
                'email' => 'krdn@malastreet.com',
                'phone' => '081-444-5555',
                'role' => 'Admin',
                'role_label' => 'หัวหน้าเชฟปิ้งย่าง',
                'initials' => 'KS',
                'color' => 'bg-purple-600',
                'status' => 'offline',
                'status_label' => 'ออฟไลน์',
                'branch' => 'สาขาปัตตานี',
                'is_test' => true,
            ],
            [
                'id' => 'usr_005',
                'name' => 'Show K...',
                'full_name' => 'Show Kitima (ฝ่ายบริการ)',
                'email' => 'show1@malastreet.com',
                'phone' => '081-555-6666',
                'role' => 'Admin',
                'role_label' => 'พนักงานบริการ',
                'initials' => 'SK',
                'color' => 'bg-amber-600',
                'status' => 'offline',
                'status_label' => 'ออฟไลน์',
                'branch' => 'สาขายะลา',
                'is_test' => true,
            ],
            [
                'id' => 'usr_006',
                'name' => 'Show K... 2',
                'full_name' => 'Show K. (พาร์ทไทม์วันหยุด)',
                'email' => 'show2@malastreet.com',
                'phone' => '081-666-7777',
                'role' => 'Admin',
                'role_label' => 'พนักงานพาร์ทไทม์',
                'initials' => 'SK',
                'color' => 'bg-rose-600',
                'status' => 'offline',
                'status_label' => 'ออฟไลน์',
                'branch' => 'สาขาปัตตานี',
                'is_test' => true,
            ],
            [
                'id' => 'usr_test_admin',
                'name' => 'Test',
                'full_name' => 'Test (Admin บัญชีทดสอบระบบ)',
                'email' => 'admin@malastreet.com',
                'phone' => '080-000-0000',
                'role' => 'Admin',
                'role_label' => 'ผู้ดูแลระบบ (Active)',
                'initials' => 'TE',
                'color' => 'bg-sky-600',
                'status' => 'online',
                'status_label' => 'ใช้งานอยู่',
                'branch' => 'ทุกสาขา',
                'is_test' => true,
            ],
        ];

        // Check if test accounts have been cleared
        $isTestCleared = (bool) Setting::get('access_test_accounts_cleared', false);
        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true);

        if ($isTestCleared) {
            // ONLY show real accounts created by the user!
            if (!empty($savedUsers)) {
                $users = $savedUsers;
            } else {
                // Real primary accounts including Arena (Matching Image 2)
                $users = [
                    [
                        'id' => 'usr_arena_employee',
                        'name' => 'อารีนา เจ๊ะอามะ',
                        'full_name' => 'อารีนา เจ๊ะอามะ',
                        'email' => 'arena@malastreet.com',
                        'phone' => '082-345-6789',
                        'role' => 'Employee',
                        'role_label' => 'พนักงาน',
                        'position' => 'พนง. บริการประจำ',
                        'initials' => 'อ',
                        'color' => 'bg-[#2563EB]',
                        'avatar' => null,
                        'status' => 'online',
                        'status_label' => 'ใช้งานอยู่',
                        'branch' => 'สาขายะลา',
                        'is_test' => false,
                        'created_at' => now()->toDateTimeString(),
                    ],
                    [
                        'id' => 'usr_main_admin',
                        'name' => 'Admin (เจ้าของร้าน)',
                        'full_name' => 'ผู้ดูแลระบบสูงสุด ร้านหม่าล่าสตรีท',
                        'email' => 'admin@malastreet.com',
                        'phone' => '081-999-9999',
                        'role' => 'Admin',
                        'role_label' => 'ผู้ดูแลระบบ (Admin)',
                        'position' => 'ผู้จัดการร้าน',
                        'initials' => 'AD',
                        'color' => 'bg-rose-600',
                        'avatar' => null,
                        'status' => 'online',
                        'status_label' => 'ใช้งานอยู่',
                        'branch' => 'ทุกสาขา',
                        'is_test' => false,
                        'created_at' => now()->toDateTimeString(),
                    ],
                ];
            }
        } else {
            // Real primary accounts
            $realBaseUsers = [
                [
                    'id' => 'usr_arena_employee',
                    'name' => 'อารีนา เจ๊ะอามะ',
                    'full_name' => 'อารีนา เจ๊ะอามะ',
                    'email' => 'arena@malastreet.com',
                    'phone' => '082-345-6789',
                    'role' => 'Employee',
                    'role_label' => 'พนักงาน',
                    'position' => 'พนง. บริการประจำ',
                    'initials' => 'อ',
                    'color' => 'bg-[#2563EB]',
                    'avatar' => null,
                    'status' => 'online',
                    'status_label' => 'ใช้งานอยู่',
                    'branch' => 'สาขายะลา',
                    'is_test' => false,
                    'created_at' => now()->toDateTimeString(),
                ]
            ];
            if (!empty($savedUsers)) {
                $users = array_merge($savedUsers, $defaultMockUsers);
            } else {
                $users = array_merge($realBaseUsers, $defaultMockUsers);
            }
        }

        // Resolve current active user and real online sessions
        $currentActor = AuditLog::resolveCurrentUserActor();
        $currentAuthId = \Illuminate\Support\Facades\Auth::id();
        $currentEmail = strtolower($currentActor['email'] ?? \Illuminate\Support\Facades\Auth::user()?->email ?? '');
        $currentName = strtolower($currentActor['name'] ?? \Illuminate\Support\Facades\Auth::user()?->name ?? '');
        $currentUserId = (string) ($currentActor['id'] ?? ($currentAuthId ? 'usr_' . $currentAuthId : ''));

        $activeEmails = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                $recentSessionUserIds = \Illuminate\Support\Facades\DB::table('sessions')
                    ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
                    ->whereNotNull('user_id')
                    ->pluck('user_id')
                    ->toArray();
                if (!empty($recentSessionUserIds)) {
                    $activeEmails = User::whereIn('id', $recentSessionUserIds)
                        ->pluck('email')
                        ->map(fn($e) => strtolower($e))
                        ->toArray();
                }
            }
        } catch (\Throwable $e) {}

        if ($currentEmail) {
            $activeEmails[] = $currentEmail;
        }

        // Dynamically compute real status for each user
        foreach ($users as &$u) {
            $uEmail = strtolower($u['email'] ?? '');
            $uName = strtolower($u['name'] ?? '');
            $uId = (string) ($u['id'] ?? '');

            $isCurrent = ($currentUserId && $uId === $currentUserId)
                || ($currentEmail && $uEmail === $currentEmail)
                || ($currentName && $uName === $currentName);

            $isOnline = $isCurrent || in_array($uEmail, $activeEmails);

            $u['is_current'] = $isCurrent;
            $u['status'] = $isOnline ? 'online' : 'offline';
            $u['status_label'] = $isCurrent ? 'ใช้งานอยู่ (คุณ)' : ($isOnline ? 'ออนไลน์' : 'ออฟไลน์');
        }
        unset($u);

        // Active selected user (sync with logged-in user if user_id not explicitly requested)
        if ($selectedUserId) {
            $selectedUser = collect($users)->firstWhere('id', $selectedUserId) ?? ($users[0] ?? null);
        } else {
            $actor = $currentActor;
            $selectedUser = collect($users)->first(function ($u) use ($actor) {
                return ($u['id'] ?? '') === ($actor['id'] ?? '')
                    || strtolower($u['email'] ?? '') === strtolower($actor['email'] ?? '')
                    || ($u['name'] ?? '') === ($actor['name'] ?? '');
            }) ?? ($users[0] ?? null);
        }

        // Modules & Granular Permission Capabilities for Mala Street System
        $permissionModules = [
            [
                'id' => 'branches',
                'name' => 'สาขาที่อนุญาต (Allowed Branches)',
                'badge' => '2 สาขา (สลับได้)',
                'icon' => '🏢',
                'items' => [
                    [
                        'id' => 'branch_yala',
                        'name' => 'สาขายะลา',
                        'description' => '123 ถ.สุขยางค์ ต.สะเตง อ.เมือง จ.ยะลา • ศูนย์บริการหลัก & คลังสต็อกสินค้า',
                        'icon' => '📍',
                        'actions' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true, 'switch' => true],
                        'available_actions' => ['view', 'add', 'edit', 'delete', 'switch']
                    ],
                    [
                        'id' => 'branch_pattani',
                        'name' => 'สาขาปัตตานี',
                        'description' => '456 ถ.นาเกลือ ต.สะบารัง อ.เมือง จ.ปัตตานี • ศูนย์บริการและจุดรับคืนอุปกรณ์',
                        'icon' => '📍',
                        'actions' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true, 'switch' => true],
                        'available_actions' => ['view', 'add', 'edit', 'delete', 'switch']
                    ],
                ],
                'alert_card' => '🛡️ ทดสอบการแจ้งเตือนสิทธิ์สาขา (Test Popup Alert)'
            ],
            [
                'id' => 'dashboard',
                'name' => 'ภาพรวม (Dashboard)',
                'badge' => '4 หน้าต่างย่อย',
                'icon' => '📊',
                'items' => [
                    [
                        'id' => 'dash_kpi',
                        'name' => 'สรุปสถิติยอดจอง & รายได้ประจำวัน (KPI Cards)',
                        'description' => 'การ์ดแสดงตัวเลขรายได้, ออเดอร์รออนุมัติ, คิวงานวันนี้',
                        'icon' => '📈',
                        'actions' => ['view' => true],
                        'available_actions' => ['view']
                    ],
                    [
                        'id' => 'dash_charts',
                        'name' => 'กราฟแนวโน้มยอดขาย & การเช่า (Sales Trend Charts)',
                        'description' => 'กราฟวิเคราะห์ยอดเช่าตามช่วงเวลาและหมวดหมู่',
                        'icon' => '📉',
                        'actions' => ['view' => true, 'export' => true],
                        'available_actions' => ['view', 'export']
                    ],
                    [
                        'id' => 'dash_recent_orders',
                        'name' => 'รายการคิวจองล่าสุด & สถิติอุปกรณ์ยอดนิยม',
                        'description' => 'ตารางแสดงออเดอร์เรียลไทม์ และอุปกรณ์ที่ถูกเช่าบ่อยที่สุด',
                        'icon' => '📋',
                        'actions' => ['view' => true],
                        'available_actions' => ['view']
                    ],
                    [
                        'id' => 'dash_branch_filter',
                        'name' => 'ตัวกรองสถิติแยกตามสาขา (Branch Filter Control)',
                        'description' => 'สลับดูกราฟและตัวเลขของแต่ละสาขา',
                        'icon' => '🎛️',
                        'actions' => ['view' => true, 'switch' => true],
                        'available_actions' => ['view', 'switch']
                    ],
                ]
            ],
            [
                'id' => 'pos',
                'name' => 'แคชเชียร์หน้าร้าน & ขายสินค้า (POS Cashier)',
                'badge' => '4 ระบบย่อย',
                'icon' => '🛒',
                'items' => [
                    [
                        'id' => 'pos_sale',
                        'name' => 'เปิดหน้าขาย POS & จิ้มเลือกเมนูอาหาร',
                        'description' => 'สร้างออเดอร์ใหม่, สแกนสินค้า, คำนวณยอดเงินรวม',
                        'icon' => '⚡',
                        'actions' => ['view' => true, 'add' => true],
                        'available_actions' => ['view', 'add']
                    ],
                    [
                        'id' => 'pos_payment',
                        'name' => 'คิดเงิน & รับชำระ & พิมพ์ใบเสร็จ',
                        'description' => 'รับเงินสด, โอนเงินผ่าน QR, ปริ้นท์ใบเสร็จให้ลูกค้า',
                        'icon' => '💳',
                        'actions' => ['view' => true, 'edit' => true],
                        'available_actions' => ['view', 'edit']
                    ],
                    [
                        'id' => 'pos_void',
                        'name' => 'ยกเลิกบิล & คืนเงิน (Void / Refund)',
                        'description' => 'ยกเลิกออเดอร์ที่ชำระแล้ว และคืนยอดเงิน',
                        'icon' => '↩️',
                        'actions' => ['edit' => true, 'delete' => true],
                        'available_actions' => ['edit', 'delete']
                    ],
                    [
                        'id' => 'pos_discount',
                        'name' => 'ปรับปรุงส่วนลด & ราคาพิเศษหน้าร้าน',
                        'description' => 'ให้ส่วนลดพิเศษหรือตัดราคาพิเศษสำหรับลูกค้าประจำ',
                        'icon' => '🏷️',
                        'actions' => ['edit' => true],
                        'available_actions' => ['edit']
                    ],
                ]
            ],
            [
                'id' => 'kitchen',
                'name' => 'จอทำอาหารในครัว (Kitchen Display / KDS)',
                'badge' => '3 ระบบย่อย',
                'icon' => '🍳',
                'items' => [
                    [
                        'id' => 'kitchen_queue',
                        'name' => 'ดูคิวออเดอร์ปิ้งย่างในครัว',
                        'description' => 'แสดงรายการอาหารและระดับความเผ็ดแบบเรียลไทม์',
                        'icon' => '🍢',
                        'actions' => ['view' => true],
                        'available_actions' => ['view']
                    ],
                    [
                        'id' => 'kitchen_status',
                        'name' => 'อัปเดตสถานะ กำลังทำ / เสร็จแล้ว',
                        'description' => 'เปลี่ยนสถานะคิวอาหารและแจ้งเตือนพนักงานเสิร์ฟ',
                        'icon' => '✅',
                        'actions' => ['edit' => true],
                        'available_actions' => ['edit']
                    ],
                    [
                        'id' => 'kitchen_cancel',
                        'name' => 'ยกเลิกคิวอาหาร / แจ้งวัตถุดิบหมด',
                        'description' => 'ยกเลิกรายการเมื่อวัตถุดิบหมดระหว่างปรุง',
                        'icon' => '❌',
                        'actions' => ['delete' => true],
                        'available_actions' => ['delete']
                    ],
                ]
            ],
            [
                'id' => 'online_orders',
                'name' => 'สั่งออนไลน์ลูกค้า (Online Orders)',
                'badge' => '3 ระบบย่อย',
                'icon' => '📱',
                'items' => [
                    [
                        'id' => 'online_view',
                        'name' => 'ดูรายการออเดอร์ออนไลน์จากลูกค้า',
                        'description' => 'ออเดอร์ที่สั่งผ่านหน้าเว็บ หรือสแกน QR โต๊ะอาหาร',
                        'icon' => '🛵',
                        'actions' => ['view' => true],
                        'available_actions' => ['view']
                    ],
                    [
                        'id' => 'online_accept',
                        'name' => 'กดยืนยันรับออเดอร์ / ปฏิเสธออเดอร์',
                        'description' => 'รับออเดอร์เข้าครัวและแจ้งเตือนสถานะลูกค้า',
                        'icon' => '🔔',
                        'actions' => ['edit' => true],
                        'available_actions' => ['edit']
                    ],
                    [
                        'id' => 'online_toggle',
                        'name' => 'ควบคุมเปิด-ปิดรับออเดอร์ (GO LIVE Mode)',
                        'description' => 'เปิดรับออเดอร์จริง หรือสลับกลับเป็นโหมดพรีวิว',
                        'icon' => '🚀',
                        'actions' => ['edit' => true],
                        'available_actions' => ['edit']
                    ],
                ]
            ],
            [
                'id' => 'price_tracker',
                'name' => 'ระบบตรวจจับราคาขึ้น-ลง & ใบเสร็จ OCR',
                'badge' => '3 ระบบย่อย',
                'icon' => '📈',
                'items' => [
                    [
                        'id' => 'pt_ocr',
                        'name' => 'สแกนใบเสร็จด้วย AI OCR อัตโนมัติ',
                        'description' => 'ถ่ายภาพบิลวัตถุดิบจากแม็คโคร/ตลาดสด เพื่อดึงราคาอัตโนมัติ',
                        'icon' => '🧾',
                        'actions' => ['view' => true, 'add' => true],
                        'available_actions' => ['view', 'add']
                    ],
                    [
                        'id' => 'pt_trends',
                        'name' => 'ดูกราฟและดัชนีราคาขึ้น-ลงวัตถุดิบ',
                        'description' => 'ตรวจจับความผันผวนของราคาเนื้อวัว, ผักสด, ลูกชิ้น',
                        'icon' => '📊',
                        'actions' => ['view' => true, 'export' => true],
                        'available_actions' => ['view', 'export']
                    ],
                    [
                        'id' => 'pt_manage',
                        'name' => 'แก้ไข / ลบ ประวัติราคาและใบเสร็จ',
                        'description' => 'ปรับปรุงข้อมูลใบเสร็จที่บันทึกผิดพลาด',
                        'icon' => '✏️',
                        'actions' => ['edit' => true, 'delete' => true],
                        'available_actions' => ['edit', 'delete']
                    ],
                ]
            ],
            [
                'id' => 'inventory',
                'name' => 'สต็อกไม้คงเหลือ (Inventory Control)',
                'badge' => '3 ระบบย่อย',
                'icon' => '🍢',
                'items' => [
                    [
                        'id' => 'inv_view',
                        'name' => 'ดูยอดไม้คงเหลือ ยกมา บวกวันนี้ หักยอดขาย',
                        'description' => 'สรุปจำนวนไม้คงเหลือและมูลค่าสต็อกประจำวัน',
                        'icon' => '📦',
                        'actions' => ['view' => true],
                        'available_actions' => ['view']
                    ],
                    [
                        'id' => 'inv_adjust',
                        'name' => 'ปรับยอดสต็อกไม้ / เติมไม้เข้าหน้าร้าน',
                        'description' => 'บันทึกรับไม้เสียบใหม่เข้าตู้แช่หน้าร้าน',
                        'icon' => '➕',
                        'actions' => ['add' => true, 'edit' => true],
                        'available_actions' => ['add', 'edit']
                    ],
                    [
                        'id' => 'inv_loss',
                        'name' => 'บันทึกไม้ชำรุด / เสียหาย / ไม้หัก',
                        'description' => 'ตัดสต็อกไม้ที่ตกพื้น เสียหาย หรือทิ้ง',
                        'icon' => '🗑️',
                        'actions' => ['edit' => true, 'delete' => true],
                        'available_actions' => ['edit', 'delete']
                    ],
                ]
            ],
            [
                'id' => 'expenses',
                'name' => 'ฟิกคอส & กำไร (Fixed Costs & Profit)',
                'badge' => '2 ระบบย่อย',
                'icon' => '💰',
                'items' => [
                    [
                        'id' => 'exp_view',
                        'name' => 'ดูสรุปผลกำไรสุทธิ & ต้นทุนคงที่',
                        'description' => 'รายงานผลประกอบการ รายรับ รายจ่าย กำไรสุทธิ',
                        'icon' => '🪙',
                        'actions' => ['view' => true, 'export' => true],
                        'available_actions' => ['view', 'export']
                    ],
                    [
                        'id' => 'exp_manage',
                        'name' => 'บันทึกรายจ่ายฟิกคอส (ค่าเช่า, น้ำไฟ, เงินเดือน)',
                        'description' => 'จัดการรายการค่าใช้จ่ายคงที่ของร้านประจำเดือน',
                        'icon' => '📝',
                        'actions' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true],
                        'available_actions' => ['view', 'add', 'edit', 'delete']
                    ],
                ]
            ],
            [
                'id' => 'employees',
                'name' => 'ระบบจัดการพนักงาน (Employees & Attendance)',
                'badge' => '4 ระบบย่อย',
                'icon' => '👥',
                'items' => [
                    [
                        'id' => 'emp_clock',
                        'name' => 'สแกนใบหน้าเข้า-ออกงาน & บันทึกภาพถ่าย',
                        'description' => 'ลงเวลาทำงานด้วยกล้อง AI และตรวจสอบพิกัด GPS',
                        'icon' => '📸',
                        'actions' => ['view' => true, 'add' => true],
                        'available_actions' => ['view', 'add']
                    ],
                    [
                        'id' => 'emp_photos',
                        'name' => 'ดูและเปิด URL ภาพถ่ายสแกนหน้า',
                        'description' => 'เปิดดูภาพหลักฐานการลงเวลาย้อนหลังและแชร์ URL',
                        'icon' => '🔗',
                        'actions' => ['view' => true, 'export' => true],
                        'available_actions' => ['view', 'export']
                    ],
                    [
                        'id' => 'emp_payroll',
                        'name' => 'สรุปยอดเงินเดือน & ใบจ่ายเงินเดือน (Payslip)',
                        'description' => 'ดูรายละเอียดเงินเดือน ค่าล่วงเวลา OT รายการหัก',
                        'icon' => '🧾',
                        'actions' => ['view' => true, 'export' => true, 'edit' => true],
                        'available_actions' => ['view', 'export', 'edit']
                    ],
                    [
                        'id' => 'emp_geofence',
                        'name' => 'ปักหมุดพิกัดร้าน & ตั้งค่ารัศมี Geofence',
                        'description' => 'เลือกหมุดแผนที่และกำหนดขอบเขตระยะการเช็คอิน',
                        'icon' => '📍',
                        'actions' => ['view' => true, 'edit' => true],
                        'available_actions' => ['view', 'edit']
                    ],
                ]
            ],
        ];

        // Retrieve saved user permissions
        $savedPermissionsKey = "permissions_user_" . ($selectedUser['id'] ?? 'default');
        $userPermissions = json_decode(Setting::get($savedPermissionsKey, '{}'), true);
        if (empty($userPermissions) && !empty($selectedUser['role'])) {
            $userPermissions = $this->getDefaultPermissionsForRole($selectedUser['role']);
        }

        // Retrieve Employee Extended Data (Schedule, Profile, Payroll, Leaves - Matching Image 2)
        $selectedUserId = $selectedUser['id'] ?? null;
        $empKey = $selectedUserId ? "employee_data_{$selectedUserId}" : null;
        $activeEmployeeData = $empKey ? json_decode(Setting::get($empKey, 'null'), true) : null;

        if (!$activeEmployeeData) {
            $isEmpAdmin = (str_contains(strtolower($selectedUser['role_label'] ?? ''), 'admin') || str_contains(strtolower($selectedUser['role'] ?? ''), 'admin'));
            $empName = $selectedUser['name'] ?? 'พนักงาน';
            $activeEmployeeData = [
                'schedule' => [
                    'days' => ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัส', 'ศุกร์'],
                    'week_filter' => 'all',
                    'time_mode' => 'same',
                    'start_time' => '09:00 AM',
                    'end_time' => '07:00 PM',
                    'notes' => 'ทำงานเฉพาะเสาร์-อาทิตย์, เข้างานตามกะคอนเทนต์',
                ],
                'profile' => [
                    'name' => $empName,
                    'full_name' => $selectedUser['full_name'] ?? $empName,
                    'nickname' => $empName,
                    'position' => $selectedUser['position'] ?? ($isEmpAdmin ? 'ผู้ดูแลระบบ (Admin)' : 'พนง. บริการประจำ'),
                    'job_title' => $isEmpAdmin ? 'ผู้ดูแลระบบ' : 'พนักงานบริการประจำ',
                    'phone' => $selectedUser['phone'] ?? '',
                    'email' => $selectedUser['email'] ?? '',
                    'branch' => $selectedUser['branch'] ?? 'สาขายะลา',
                    'role' => $selectedUser['role'] ?? 'Employee',
                ],
                'payroll' => [
                    'base_salary' => $isEmpAdmin ? 0 : 15000,
                    'ot_rate' => $isEmpAdmin ? 0 : 60,
                    'ot_amount' => 0,
                    'allowance' => $isEmpAdmin ? 0 : 1000,
                    'deduction' => $isEmpAdmin ? 0 : 750,
                    'bank_name' => 'ธนาคารกสิกรไทย',
                    'bank_account' => $isEmpAdmin ? '-' : '123-4-56789-0',
                ],
                'leave' => [
                    'sick_leave' => 30,
                    'personal_leave' => 6,
                    'annual_leave' => 6,
                    'late_grace_minutes' => 15,
                ],
                'more_info' => [
                    'id_card' => '',
                    'start_date' => '2025-01-01',
                    'emergency_contact' => '',
                    'notes' => $isEmpAdmin ? 'ผู้ดูแลระบบร้านหม่าล่าสตรีท' : 'พนักงานบริการประจำ',
                ],
                'wage_kpi' => [
                    'wage_type' => 'daily',
                    'daily_wage' => $isEmpAdmin ? 0 : 250,
                    'regular_ot_rate' => $isEmpAdmin ? 0 : 50,
                    'ot_per_time' => '',
                    'ot_delivery' => '',
                    'ot_content' => '',
                    'kpi_enabled' => true,
                    'kpi_amount' => $isEmpAdmin ? 0 : 2000,
                    'kpi_rounds' => 3,
                    'kpi_period_name' => 'สัปดาห์',
                    'show_in_evaluation' => false,
                ]
            ];
        }

        // Retrieve auto-logout and security settings
        $savedSettings = json_decode(Setting::get('access_auto_logout_settings', '{}'), true) ?: [];
        $autoLogoutSettings = array_merge([
            'timeout_minutes' => (int) Setting::get('access_auto_logout_minutes', 15),
            'timeout_action' => 'lock_screen', // 'lock_screen', 'full_logout', 'quick_pin'
            'exempt_kitchen' => true,
            'exempt_customer' => true,
            'lock_on_blur' => false,
            'warning_countdown' => true,
            'require_pin_shift_close' => true,
            'audit_log' => true,
        ], $savedSettings);

        $defaultRolePermissions = $this->getDefaultPermissionsForRole($selectedUser['role'] ?? 'Employee');

        // Fetch Audit Logs (seed initial system audit logs if empty so Audit tab is immediately populated)
        $auditLogs = collect();
        try {
            if (AuditLog::count() === 0) {
                AuditLog::record(
                    'access',
                    'update',
                    'เปิดใช้งานระบบบันทึกประวัติการทำงาน (Audit Trail & Real-Time Sync พ.ศ. 2569)',
                    'ระบบการเข้าถึง & ความปลอดภัย',
                    'setting',
                    'audit_init'
                );
            }
            $auditLogs = AuditLog::orderByDesc('id')->limit(120)->get();
        } catch (\Throwable $e) {
            $auditLogs = collect();
        }

        return view('admin.access.index', compact(
            'currentTab',
            'users',
            'selectedUser',
            'permissionModules',
            'userPermissions',
            'defaultRolePermissions',
            'isTestCleared',
            'activeEmployeeData',
            'autoLogoutSettings',
            'auditLogs'
        ));
    }

    /**
     * Save updated permissions matrix for a user.
     */
    public function save(Request $request): JsonResponse
    {
        $userId = $request->input('user_id');
        $permissions = $request->input('permissions', []);

        if (empty($userId)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบรหัสผู้ใช้งาน'], 422);
        }

        $key = "permissions_user_{$userId}";
        Setting::set($key, json_encode($permissions), 'permissions');

        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true);
        $targetUser = collect($savedUsers)->firstWhere('id', $userId);
        $targetUserName = $targetUser['name'] ?? $userId;

        AuditLog::record(
            'access',
            'update',
            "อัปเดตสิทธิ์การเข้าถึงของ {$targetUserName} (" . count($permissions) . " โมดูล)",
            $targetUserName,
            'permission',
            (string) $userId
        );

        return response()->json([
            'success' => true,
            'message' => 'บันทึกการเปลี่ยนแปลงสิทธิ์การเข้าถึงเรียบร้อยแล้ว!',
            'user_id' => $userId,
            'permissions_count' => count($permissions)
        ]);
    }

    /**
     * Save employee schedule and profile data (Image 2 - ข้อมูล & สิทธิการทำงานพนักงาน).
     */
    public function saveEmployeeData(Request $request): JsonResponse
    {
        $userId = $request->input('user_id');
        if (empty($userId)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบรหัสผู้ใช้งาน'], 422);
        }

        $schedule = $request->input('schedule', []);
        $profile = $request->input('profile', []);
        $payroll = $request->input('payroll', []);
        $leave = $request->input('leave', []);
        $moreInfo = $request->input('more_info', []);
        $wageKpi = $request->input('wage_kpi', []);

        $employeeData = [
            'schedule' => $schedule,
            'profile' => $profile,
            'payroll' => $payroll,
            'leave' => $leave,
            'more_info' => $moreInfo,
            'wage_kpi' => $wageKpi,
            'updated_at' => now()->toDateTimeString(),
        ];

        Setting::set("employee_data_{$userId}", json_encode($employeeData), 'employees');

        // Update password in Laravel users table if provided
        if (!empty($profile['password']) && !empty($profile['email'])) {
            try {
                User::where('email', strtolower(trim($profile['email'])))
                    ->update(['password' => Hash::make($profile['password'])]);
            } catch (\Throwable $e) {
                // Non-blocking
            }
        }

        // Update name or position in access_users_list if modified
        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true);
        $updated = false;
        $targetUserName = $profile['name'] ?? $userId;
        foreach ($savedUsers as &$u) {
            if ($u['id'] === $userId) {
                if (!empty($profile['name'])) $u['name'] = $profile['name'];
                if (!empty($profile['full_name'])) $u['full_name'] = $profile['full_name'];
                if (!empty($profile['position'])) $u['position'] = $profile['position'];
                if (!empty($profile['phone'])) $u['phone'] = $profile['phone'];
                if (!empty($profile['email'])) $u['email'] = $profile['email'];
                if (!empty($profile['branch'])) $u['branch'] = $profile['branch'];
                $targetUserName = $u['name'] ?? $targetUserName;
                $updated = true;
                break;
            }
        }
        if ($updated) {
            Setting::set('access_users_list', json_encode($savedUsers), 'access');
        }

        AuditLog::record(
            'access',
            'update',
            "บันทึกข้อมูลตารางงาน/เงินเดือน/โปรไฟล์พนักงาน: {$targetUserName}",
            $targetUserName,
            'employee_data',
            (string) $userId
        );

        return response()->json([
            'success' => true,
            'message' => 'บันทึกข้อมูลตารางงานและสิทธิการทำงานพนักงานเรียบร้อยแล้ว!',
            'data' => $employeeData,
        ]);
    }

    /**
     * Store new real staff, admin, or customer user.
     * Supports image upload, real email & password, phone, role, branch, and test accounts purge.
     */
    public function storeUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|string|in:Admin,Employee,Customer',
            'branch' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:4|max:100',
            'avatar' => 'nullable|image|max:5120', // max 5MB
        ]);

        $name = trim($request->input('name'));
        $email = strtolower(trim($request->input('email')));
        $phone = trim($request->input('phone') ?? '-');
        $role = $request->input('role', 'Employee');
        $branch = $request->input('branch', 'ทุกสาขา');
        $password = $request->input('password', 'password123');
        $clearTestAccounts = $request->boolean('clear_test_accounts', true);

        // Process profile avatar upload
        $avatarUrl = null;
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $file = $request->file('avatar');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'user_' . time() . '_' . Str::random(6) . '.' . $extension;
            $destinationPath = public_path('uploads/users');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            $avatarUrl = asset('uploads/users/' . $filename);
        }

        // Color & Initials
        $initials = mb_substr($name, 0, 2);
        $roleColors = [
            'Admin' => 'bg-rose-600',
            'Employee' => 'bg-blue-600',
            'Customer' => 'bg-emerald-600',
        ];
        $roleLabels = [
            'Admin' => 'ผู้ดูแลระบบ (Admin)',
            'Employee' => 'พนักงานร้าน (Employee)',
            'Customer' => 'ลูกค้า (Customer)',
        ];

        // 1. Create or update real User record in Laravel users table
        try {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                ]
            );
        } catch (\Throwable $e) {
            // Non-blocking fallback
        }

        $userId = 'usr_' . time() . '_' . Str::lower(Str::random(4));
        $newUser = [
            'id' => $userId,
            'name' => $name,
            'full_name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'role_label' => $roleLabels[$role] ?? $role,
            'initials' => $initials,
            'color' => $roleColors[$role] ?? 'bg-indigo-600',
            'avatar' => $avatarUrl,
            'status' => 'offline',
            'status_label' => 'ออฟไลน์',
            'branch' => $branch,
            'is_test' => false,
            'created_at' => now()->toDateTimeString(),
        ];

        // 2. Retrieve existing saved users and prepend the new user
        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true);
        array_unshift($savedUsers, $newUser);

        // 3. Mark test accounts as cleared if requested
        if ($clearTestAccounts) {
            Setting::set('access_test_accounts_cleared', '1', 'access');
        }

        Setting::set('access_users_list', json_encode($savedUsers), 'access');

        // 4. Set initial preset permissions according to role
        $initialPermissions = $this->getDefaultPermissionsForRole($role);
        Setting::set('permissions_user_' . $userId, json_encode($initialPermissions), 'access');

        // 5. Initialize employee extended data (Schedule, Profile, Payroll, Leaves)
        $isNewUserAdmin = ($role === 'Admin');
        $initialEmployeeData = [
            'schedule' => [
                'days' => ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัส', 'ศุกร์'],
                'week_filter' => 'all',
                'time_mode' => 'same',
                'start_time' => '09:00 AM',
                'end_time' => '07:00 PM',
                'notes' => 'ทำงานเฉพาะเสาร์-อาทิตย์, เข้างานตามกะคอนเทนต์',
            ],
            'profile' => [
                'name' => $name,
                'full_name' => $name,
                'nickname' => $name,
                'position' => $isNewUserAdmin ? 'ผู้ดูแลระบบ (Admin)' : 'พนง. บริการประจำ',
                'job_title' => $isNewUserAdmin ? 'ผู้ดูแลระบบ' : 'พนักงานบริการประจำ',
                'phone' => $phone,
                'email' => $email,
                'branch' => $branch,
                'role' => $role,
            ],
            'payroll' => [
                'base_salary' => $isNewUserAdmin ? 0 : 15000,
                'ot_rate' => $isNewUserAdmin ? 0 : 60,
                'ot_amount' => 0,
                'allowance' => $isNewUserAdmin ? 0 : 1000,
                'deduction' => $isNewUserAdmin ? 0 : 750,
                'bank_name' => 'ธนาคารกสิกรไทย',
                'bank_account' => $isNewUserAdmin ? '-' : '123-4-56789-0',
            ],
            'leave' => [
                'sick_leave' => 30,
                'personal_leave' => 6,
                'annual_leave' => 6,
                'late_grace_minutes' => 15,
            ],
            'more_info' => [
                'id_card' => '',
                'start_date' => date('Y-m-d'),
                'emergency_contact' => '',
                'notes' => $isNewUserAdmin ? 'ผู้ดูแลระบบร้านหม่าล่าสตรีท' : 'พนักงานบริการประจำ',
            ],
            'wage_kpi' => [
                'wage_type' => 'daily',
                'daily_wage' => $isNewUserAdmin ? 0 : 250,
                'regular_ot_rate' => $isNewUserAdmin ? 0 : 50,
                'ot_per_time' => '',
                'ot_delivery' => '',
                'ot_content' => '',
                'kpi_enabled' => true,
                'kpi_amount' => $isNewUserAdmin ? 0 : 2000,
                'kpi_rounds' => 3,
                'kpi_period_name' => 'สัปดาห์',
                'show_in_evaluation' => false,
            ],
            'attendance' => [
                'work_days' => $isNewUserAdmin ? 0 : 26,
                'work_hours' => $isNewUserAdmin ? '0 ชม.' : '208 ชม.',
                'ot_hours' => '0 ชม.',
                'leave_days' => 0,
                'rate' => $isNewUserAdmin ? '0%' : '100%',
                'grade' => 'A',
            ],
            'updated_at' => now()->toDateTimeString(),
        ];
        Setting::set('employee_data_' . $userId, json_encode($initialEmployeeData), 'employees');

        AuditLog::record(
            'access',
            'create',
            "สร้างบัญชีผู้ใช้งานจริงใหม่: {$name} ({$roleLabels[$role]}) สาขา: {$branch}",
            $name,
            'user',
            (string) $userId
        );

        return response()->json([
            'success' => true,
            'message' => "สร้างบัญชีจริง '{$name}' ({$roleLabels[$role]}) เรียบร้อยแล้ว! สามารถเข้าใช้งานได้ทันที",
            'user' => $newUser,
            'test_cleared' => $clearTestAccounts,
        ]);
    }

    /**
     * Delete a single user account.
     */
    public function deleteUser(string $id): JsonResponse
    {
        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true);
        $deletedUser = collect($savedUsers)->firstWhere('id', $id);
        $deletedName = $deletedUser['name'] ?? $id;

        $savedUsers = array_values(array_filter($savedUsers, fn($u) => ($u['id'] ?? '') !== $id));
        Setting::set('access_users_list', json_encode($savedUsers), 'access');

        // If it was one of the default mock accounts, mark test accounts as cleared
        if (str_starts_with($id, 'usr_00') || $id === 'usr_test_admin') {
            Setting::set('access_test_accounts_cleared', '1', 'access');
        }

        AuditLog::record(
            'access',
            'delete',
            "ลบบัญชีผู้ใช้งาน: {$deletedName}",
            $deletedName,
            'user',
            (string) $id
        );

        return response()->json([
            'success' => true,
            'message' => 'ลบบัญชีผู้ใช้งานเรียบร้อยแล้ว!',
        ]);
    }

    /**
     * Toggle active/inactive status for a user.
     */
    public function toggleStatus(Request $request, string $id): JsonResponse
    {
        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true) ?: [];
        $updated = false;
        $newStatus = 'offline';
        $targetName = $id;

        foreach ($savedUsers as &$u) {
            if ($u['id'] === $id) {
                $currentStatus = $u['status'] ?? 'offline';
                $newStatus = ($currentStatus === 'online') ? 'offline' : 'online';
                $u['status'] = $newStatus;
                $u['status_label'] = ($newStatus === 'online') ? 'ใช้งานอยู่' : 'ออฟไลน์';
                $targetName = $u['name'] ?? $id;
                $updated = true;
                break;
            }
        }
        unset($u);

        if ($updated) {
            Setting::set('access_users_list', json_encode($savedUsers), 'access');

            AuditLog::record(
                'access',
                'status',
                "เปลี่ยนสถานะผู้ใช้งาน {$targetName} เป็น " . ($newStatus === 'online' ? 'ใช้งานอยู่' : 'ออฟไลน์'),
                $targetName,
                ['user_id' => $id, 'status' => $newStatus]
            );
        }

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => "เปลี่ยนสถานะของ {$targetName} เป็น " . ($newStatus === 'online' ? 'ใช้งานอยู่' : 'ออฟไลน์') . ' เรียบร้อยแล้ว',
        ]);
    }

    /**
     * Clear all test accounts completely.
     */
    public function clearTestUsers(): JsonResponse
    {
        Setting::set('access_test_accounts_cleared', '1', 'access');

        AuditLog::record(
            'access',
            'delete',
            'ล้างบัญชีทดสอบทั้งหมด เหลือเฉพาะบัญชีผู้ใช้งานจริง',
            'บัญชีทดสอบทั้งหมด',
            'user',
            'test_users'
        );

        return response()->json([
            'success' => true,
            'message' => 'ลบบัญชีทดสอบทั้งหมด (ตามภาพที่ 2) เรียบร้อยแล้ว! ระบบจะแสดงเฉพาะบัญชีจริงเท่านั้น',
        ]);
    }

    /**
     * Update access control general settings (Auto-logout, security policies, backup, etc.).
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $settings = $request->input('settings', []);
        $autoLogoutMinutes = isset($settings['timeout_minutes']) 
            ? (int)$settings['timeout_minutes'] 
            : (int)$request->input('auto_logout_minutes', 15);
        $backupDays = (int) ($settings['backup_days'] ?? $request->input('backup_days', 30));

        Setting::set('access_auto_logout_minutes', (string)$autoLogoutMinutes, 'access');
        Setting::set('access_backup_days', (string)$backupDays, 'access');
        
        if (!empty($settings)) {
            Setting::set('access_auto_logout_settings', json_encode($settings), 'access');
        }

        $label = $autoLogoutMinutes === 0 
            ? 'ปิดใช้งาน' 
            : ($autoLogoutMinutes >= 60 ? ($autoLogoutMinutes / 60) . ' ชม.' : $autoLogoutMinutes . ' นาที');

        AuditLog::record(
            'access',
            'update',
            "อัปเดตตั้งค่าระบบเช็คเอาท์อัตโนมัติ ({$label})",
            "Auto-Logout: {$label}",
            'setting',
            'access_auto_logout'
        );

        return response()->json([
            'success' => true,
            'message' => 'บันทึกการตั้งค่าระบบตัดการเชื่อมต่ออัตโนมัติเรียบร้อยแล้ว!',
            'minutes' => $autoLogoutMinutes,
            'label' => $label,
            'settings' => $settings,
        ]);
    }

    /**
     * Generate default permission preset based on role.
     */
    protected function getDefaultPermissionsForRole(string $role): array
    {
        if ($role === 'Admin') {
            return [
                'branch_yala' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true, 'switch' => true],
                'branch_pattani' => ['view' => true, 'add' => true, 'edit' => true, 'delete' => true, 'switch' => true],
                'dash_kpi' => ['view' => true],
                'dash_charts' => ['view' => true, 'export' => true],
                'dash_recent_orders' => ['view' => true],
                'dash_branch_filter' => ['view' => true, 'switch' => true],
                'pos_sale' => ['view' => true, 'add' => true],
                'pos_payment' => ['view' => true, 'edit' => true],
                'pos_void' => ['edit' => true, 'delete' => true],
                'pos_discount' => ['edit' => true],
                'kitchen_queue' => ['view' => true],
                'kitchen_status' => ['edit' => true],
                'kitchen_history' => ['view' => true],
                'online_orders' => ['view' => true, 'edit' => true],
                'online_cancel' => ['delete' => true],
                'online_menu' => ['edit' => true],
                'price_tracker_dash' => ['view' => true],
                'price_tracker_scan' => ['add' => true],
                'price_tracker_edit' => ['edit' => true],
                'price_tracker_history' => ['view' => true, 'export' => true],
                'inv_stock_list' => ['view' => true],
                'inv_stock_adjust' => ['add' => true, 'edit' => true],
                'inv_stock_reconcile' => ['edit' => true],
                'exp_fixed_cost' => ['view' => true],
                'exp_daily_record' => ['add' => true, 'edit' => true],
                'exp_delete' => ['delete' => true],
                'emp_clock_in' => ['view' => true, 'add' => true],
                'emp_payroll' => ['view' => true, 'export' => true, 'edit' => true],
                'emp_geofence' => ['view' => true, 'edit' => true],
            ];
        }

        if ($role === 'Employee') {
            return [
                'branch_yala' => ['view' => true, 'switch' => true],
                'branch_pattani' => ['view' => true, 'switch' => true],
                'dash_kpi' => ['view' => true],
                'pos_sale' => ['view' => true, 'add' => true],
                'pos_payment' => ['view' => true],
                'kitchen_queue' => ['view' => true],
                'kitchen_status' => ['edit' => true],
                'online_orders' => ['view' => true],
                'inv_stock_list' => ['view' => true],
                'emp_clock_in' => ['view' => true, 'add' => true],
                'emp_payroll' => ['view' => true],
            ];
        }

        // Customer
        return [
            'online_orders' => ['view' => true, 'add' => true],
        ];
    }
}

