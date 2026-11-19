<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expense;
use App\Models\InventoryDaily;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MalaStreetSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // 1. Categories
        $catBeef = Category::create(['name' => 'เนื้อโคขุน', 'slug' => 'beef', 'icon' => '🥩', 'sort_order' => 1]);
        $catPorkChicken = Category::create(['name' => 'หมู & ไก่', 'slug' => 'pork-chicken', 'icon' => '🍗', 'sort_order' => 2]);
        $catBallSausage = Category::create(['name' => 'ลูกชิ้น', 'slug' => 'meatball-sausage', 'icon' => '🍢', 'sort_order' => 3]);
        $catVeg = Category::create(['name' => 'ผัก', 'slug' => 'vegetables', 'icon' => '🥬', 'sort_order' => 4]);
        $catTofu = Category::create(['name' => 'เต้าหู้ & เส้น', 'slug' => 'tofu-noodles', 'icon' => '🍥', 'sort_order' => 5]);
        $catSnacks = Category::create(['name' => 'ของทานเล่น', 'slug' => 'snacks', 'icon' => '🍟', 'sort_order' => 6]);
        $catDrink = Category::create(['name' => 'เครื่องดื่ม', 'slug' => 'beverages', 'icon' => '🥤', 'sort_order' => 7]);

        // 2. Raw Materials with Stick Yield
        $rawChickenTendon = RawMaterial::create([
            'name' => 'เอ็นไก่',
            'sku' => 'RAW-CK-TENDON',
            'default_vendor' => 'สยามแม็คโคร สาขาบางกะปิ',
            'purchase_unit' => 'แพ็ก (500g)',
            'yield_sticks_per_unit' => 10,
            'latest_cost_price' => 85.00,
            'cost_per_stick' => 8.50,
            'current_stock_sticks' => 3,
        ]);

        $rawSausage = RawMaterial::create([
            'name' => 'ไส้กรอกไก่รมควัน',
            'sku' => 'RAW-SAUSAGE-SMOKE',
            'default_vendor' => 'โลตัส เอ็กซ์เพรส',
            'purchase_unit' => 'แพ็ก',
            'yield_sticks_per_unit' => 10,
            'latest_cost_price' => 60.00,
            'cost_per_stick' => 6.00,
            'current_stock_sticks' => 10,
        ]);

        $rawFishBall = RawMaterial::create([
            'name' => 'ลูกชิ้นปลาภูเก็ตแท้',
            'sku' => 'RAW-FISH-BALL',
            'default_vendor' => 'สยามแม็คโคร สาขาบางกะปิ',
            'purchase_unit' => 'แพ็ก',
            'yield_sticks_per_unit' => 10,
            'latest_cost_price' => 50.00,
            'cost_per_stick' => 5.00,
            'current_stock_sticks' => 5,
        ]);

        $rawPorkBelly = RawMaterial::create([
            'name' => 'หมูสามชั้นสไลซ์',
            'sku' => 'RAW-PORK-BELLY',
            'default_vendor' => 'เบทาโกร ช็อป',
            'purchase_unit' => 'กิโลกรัม',
            'yield_sticks_per_unit' => 25,
            'latest_cost_price' => 210.00,
            'cost_per_stick' => 8.40,
            'current_stock_sticks' => 30,
        ]);

        $rawBeef = RawMaterial::create([
            'name' => 'เนื้อใบพายโคขุนสไลซ์',
            'sku' => 'RAW-BEEF-SLICED',
            'default_vendor' => 'ฟาร์มโคขุนกำแพงแสน',
            'purchase_unit' => 'กิโลกรัม',
            'yield_sticks_per_unit' => 20,
            'latest_cost_price' => 290.00,
            'cost_per_stick' => 14.50,
            'current_stock_sticks' => 18,
        ]);

        $rawTofuRoll = RawMaterial::create([
            'name' => 'ฟองเต้าหู้ม้วนทอด',
            'sku' => 'RAW-TOFU-ROLL',
            'default_vendor' => 'ตลาดเยาวราช',
            'purchase_unit' => 'แพ็ก (10 ชิ้น)',
            'yield_sticks_per_unit' => 10,
            'latest_cost_price' => 60.00,
            'cost_per_stick' => 6.00,
            'current_stock_sticks' => 22,
        ]);

        $rawMushroom = RawMaterial::create([
            'name' => 'เห็ดออรินจิสด',
            'sku' => 'RAW-MUSHROOM-ERIN',
            'default_vendor' => 'ตลาดสี่มุมเมือง',
            'purchase_unit' => 'แพ็ก (500g)',
            'yield_sticks_per_unit' => 10,
            'latest_cost_price' => 45.00,
            'cost_per_stick' => 4.50,
            'current_stock_sticks' => 15,
        ]);

        // 3. Products (Skewers) — 6 Featured Items from Image 1 + Full Store Menu
        $pBeefFeatured = Product::create([
            'category_id' => $catBeef->id,
            'raw_material_id' => $rawBeef->id,
            'name' => 'เนื้อโคขุนพรีเมียม',
            'description' => 'เนื้อโคขุนคัดพิเศษ นุ่มลิ้น หอมเครื่องเทศเข้มข้น',
            'price_per_stick' => 25.00,
            'stock_sticks' => 24,
            'image_url' => 'assets/menu/beef-premium.png',
            'supports_grill' => true,
            'supports_soup' => true,
            'sort_order' => 1,
        ]);

        $pPorkSliced = Product::create([
            'category_id' => $catPorkChicken->id,
            'raw_material_id' => $rawPorkBelly->id,
            'name' => 'หมูสามชั้นสไลซ์',
            'description' => 'หมูสไลซ์ติดมัน ย่างหอมกรอบ พร้อมพริกหม่าล่า',
            'price_per_stick' => 15.00,
            'stock_sticks' => 35,
            'image_url' => 'assets/menu/pork-wrapped-mushroom.png',
            'supports_grill' => true,
            'supports_soup' => true,
            'sort_order' => 2,
        ]);

        $pMeatballMix = Product::create([
            'category_id' => $catBallSausage->id,
            'raw_material_id' => $rawFishBall->id,
            'name' => 'ลูกชิ้นรวม',
            'description' => 'ลูกชิ้นหลากหลาย กรุบๆ เด้งๆ อร่อยเพลิน',
            'price_per_stick' => 15.00,
            'stock_sticks' => 28,
            'image_url' => 'assets/menu/meatball-assorted.png',
            'supports_grill' => true,
            'supports_soup' => true,
            'sort_order' => 3,
        ]);

        $pTofuMala = Product::create([
            'category_id' => $catTofu->id,
            'raw_material_id' => $rawTofuRoll->id,
            'name' => 'เต้าหู้หม่าล่า',
            'description' => 'เต้าหู้นุ่ม ซึมซับน้ำหม่าล่าเข้มข้น',
            'price_per_stick' => 15.00,
            'stock_sticks' => 25,
            'image_url' => 'assets/menu/tofu-mala.png',
            'supports_grill' => true,
            'supports_soup' => true,
            'sort_order' => 4,
        ]);

        $pVeggieWrap = Product::create([
            'category_id' => $catVeg->id,
            'raw_material_id' => $rawMushroom->id,
            'name' => 'พันผักรวม',
            'description' => 'เห็ดเข็มทอง พันเบคอน หอมมัน',
            'price_per_stick' => 15.00,
            'stock_sticks' => 20,
            'image_url' => 'assets/menu/veggie-wrap.png',
            'supports_grill' => true,
            'supports_soup' => true,
            'sort_order' => 5,
        ]);

        $pEnokiBacon = Product::create([
            'category_id' => $catPorkChicken->id,
            'raw_material_id' => $rawPorkBelly->id,
            'name' => 'เห็ดเข็มทองพันเบคอน',
            'description' => 'เห็ดเข็มทองกรุบๆ หอมเบคอน กลมกล่อม',
            'price_per_stick' => 20.00,
            'stock_sticks' => 26,
            'image_url' => 'assets/menu/enoki-bacon.png',
            'supports_grill' => true,
            'supports_soup' => true,
            'sort_order' => 6,
        ]);

        $pTendon = Product::create([
            'category_id' => $catPorkChicken->id,
            'raw_material_id' => $rawChickenTendon->id,
            'name' => 'เอ็นไก่กรุบกรอบ',
            'description' => 'เอ็นข้อไก่คัดพิเศษ ย่างหอมกรอบ หรือต้มซุปก็เคี้ยวเพลิน',
            'price_per_stick' => 15.00,
            'stock_sticks' => 3,
            'image_url' => 'assets/menu/pork-wrapped-mushroom.png',
            'supports_grill' => true,
            'supports_soup' => true,
            'sort_order' => 7,
        ]);

        $pSausage = Product::create([
            'category_id' => $catBallSausage->id,
            'raw_material_id' => $rawSausage->id,
            'name' => 'ไส้กรอกไก่รมควันหนังกรอบ',
            'description' => 'ไส้กรอกรมควันหอมๆ กัดกรอบเต็มคำ สตรีทฟู้ดยอดฮิต',
            'price_per_stick' => 10.00,
            'stock_sticks' => 10,
            'image_url' => 'assets/menu/meatball-assorted.png',
            'supports_grill' => true,
            'supports_soup' => true,
            'sort_order' => 8,
        ]);

        $pFishBall = Product::create([
            'category_id' => $catBallSausage->id,
            'raw_material_id' => $rawFishBall->id,
            'name' => 'ลูกชิ้นปลาภูเก็ตแท้ระเบิด',
            'description' => 'ลูกชิ้นเนื้อปลาแน่น นุ่มเด้ง ไม่คาว ย่างซอสหม่าล่าเข้าเนื้อ',
            'price_per_stick' => 10.00,
            'stock_sticks' => 5,
            'image_url' => 'assets/menu/meatball-assorted.png',
            'supports_grill' => true,
            'supports_soup' => true,
            'sort_order' => 9,
        ]);

        $pPorkBelly = Product::create([
            'category_id' => $catPorkChicken->id,
            'raw_material_id' => $rawPorkBelly->id,
            'name' => 'หมูสามชั้นสไลซ์พันเห็ดเข็มทอง',
            'description' => 'สามชั้นสไลซ์บาง มันแทรกพอดี ห่อเห็ดย่างเกรียมหอมฉ่ำ',
            'price_per_stick' => 20.00,
            'stock_sticks' => 30,
            'image_url' => 'assets/menu/pork-raw.png',
            'supports_grill' => true,
            'supports_soup' => true,
            'sort_order' => 10,
        ]);

        $pBeef = Product::create([
            'category_id' => $catBeef->id,
            'raw_material_id' => $rawBeef->id,
            'name' => 'เนื้อใบพายโคขุนพรีเมียม',
            'description' => 'เนื้อโคขุนคัดพิเศษ นุ่มลิ้น หอมเครื่องเทศหม่าล่าจีนแท้',
            'price_per_stick' => 25.00,
            'stock_sticks' => 18,
            'image_url' => 'assets/menu/beef-raw.png',
            'supports_grill' => true,
            'supports_soup' => true,
            'sort_order' => 11,
        ]);

        $pTofuRoll = Product::create([
            'category_id' => $catTofu->id,
            'raw_material_id' => $rawTofuRoll->id,
            'name' => 'ฟองเต้าหู้ม้วนทอดซดน้ำซุป',
            'description' => 'ฟองเต้าหู้ม้วนยอดฮิต ซึมซับน้ำซุปหม่าล่าน้ำข้นได้ดีเยี่ยม',
            'price_per_stick' => 15.00,
            'stock_sticks' => 22,
            'image_url' => 'assets/menu/tofu-mala.png',
            'supports_grill' => true,
            'supports_soup' => true,
            'sort_order' => 12,
        ]);

        $pMushroom = Product::create([
            'category_id' => $catVeg->id,
            'raw_material_id' => $rawMushroom->id,
            'name' => 'เห็ดออรินจิย่างพริกสด',
            'description' => 'เห็ดออรินจิหั่นแว่น ย่างจนหอมหวานกรอบฉ่ำ',
            'price_per_stick' => 10.00,
            'stock_sticks' => 15,
            'image_url' => 'assets/menu/veggie-wrap.png',
            'supports_grill' => true,
            'supports_soup' => true,
            'sort_order' => 13,
        ]);

        Product::create([
            'category_id' => $catSnacks->id,
            'raw_material_id' => null,
            'name' => 'เฟรนช์ฟรายส์เขย่าผงหม่าล่า',
            'description' => 'เฟรนช์ฟรายส์ทอดกรอบร้อนๆ คลุกผงหม่าล่าต้นตำรับเสฉวน',
            'price_per_stick' => 25.00,
            'stock_sticks' => 30,
            'image_url' => 'assets/categories/snacks.png',
            'supports_grill' => true,
            'supports_soup' => false,
            'sort_order' => 14,
        ]);

        Product::create([
            'category_id' => $catDrink->id,
            'raw_material_id' => null,
            'name' => 'หวังเหล่าจี๋ (ชาสมุนไพรจีน)',
            'description' => 'เครื่องดื่มกระป๋องแดง แก้เผ็ด ตัดเลี่ยน สดชื่นเข้ากันที่สุด',
            'price_per_stick' => 35.00,
            'stock_sticks' => 40,
            'image_url' => 'assets/categories/drinks.png',
            'supports_grill' => false,
            'supports_soup' => false,
            'sort_order' => 15,
        ]);

        // 4. Inventory Daily Reconciliation (สต็อกเริ่มต้นตามความเป็นจริง ไม่สุ่มข้อมูลเทส)
        $initialProducts = [$pTendon, $pSausage, $pFishBall, $pPorkBelly, $pBeef, $pTofuRoll, $pMushroom];
        foreach ($initialProducts as $prod) {
            InventoryDaily::create([
                'record_date' => $today,
                'product_id' => $prod->id,
                'opening_sticks' => $prod->stock_sticks,
                'added_sticks' => 0,
                'sold_sticks' => 0,
                'waste_sticks' => 0,
                'expected_closing_sticks' => $prod->stock_sticks,
                'actual_closing_sticks' => null,
                'notes' => 'สต็อกเริ่มต้นประจำวัน',
            ]);
        }

        // Real-world operations start with zero mock expenses and zero mock orders.
    }
}

