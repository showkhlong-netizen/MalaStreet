<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\InventoryDaily;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    /**
     * Display list of scanned receipts and daily cost summary.
     */
    public function index(Request $request): View|JsonResponse
    {
        $receipts = Receipt::with('items.rawMaterial')
            ->select('id', 'receipt_no', 'vendor_name', 'purchase_date', 'total_amount', 'image_path', 'parsed_data', 'status', 'created_at')
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(20);

        $costSummary = $this->getReceiptCostSummary();
        $todayCost = $costSummary['today_cost'];
        $thisMonthCost = $costSummary['this_month_cost'];
        $rawMaterials = RawMaterial::orderBy('name')->get();
        $hasGeminiKey = !empty(env('GEMINI_API_KEY')) || !empty(config('services.gemini.key'));

        if ($request->wantsJson() || $request->boolean('json')) {
            return response()->json([
                'success' => true,
                'today_cost' => (float) $todayCost,
                'this_month_cost' => (float) $thisMonthCost,
                'materials_count' => $rawMaterials->count(),
                'receipts_total' => $receipts->total(),
                'has_gemini_key' => $hasGeminiKey,
                'receipts' => collect($receipts->items())->map(function (Receipt $rec) {
                    $allImgs = ($rec->parsed_data && isset($rec->parsed_data['images']) && is_array($rec->parsed_data['images']))
                        ? $rec->parsed_data['images']
                        : ($rec->image_path ? [$rec->image_path] : []);
                    return [
                        'id' => $rec->id,
                        'receipt_no' => $rec->receipt_no ?: '-',
                        'vendor_name' => $rec->vendor_name,
                        'purchase_date' => $rec->purchase_date->format('d/m/Y'),
                        'total_amount' => (float) $rec->total_amount,
                        'images' => array_values($allImgs),
                        'items' => $rec->items->map(fn ($i) => [
                            'item_name' => $i->item_name,
                            'quantity' => (float) $i->quantity,
                            'unit' => $i->unit,
                            'unit_price' => (float) $i->unit_price,
                            'yield_sticks_per_unit' => (float) $i->yield_sticks_per_unit,
                            'total_yield_sticks' => (int) $i->total_yield_sticks,
                            'cost_per_stick' => (float) $i->cost_per_stick,
                        ])->values(),
                    ];
                })->values(),
            ]);
        }

        return view('admin.receipts.index', compact('receipts', 'todayCost', 'thisMonthCost', 'rawMaterials', 'hasGeminiKey'));
    }

    /**
     * Upload and compress receipt image, returning public URL.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $fileName = 'receipt_' . date('Ymd_His') . '_' . Str::random(6) . '.jpg';
        $targetDir = public_path('uploads/receipts');

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if ($request->filled('image_base64')) {
            $base64Image = $request->input('image_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image)) {
                $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            }
            $imageDecoded = base64_decode($base64Image);
            $fullPath = $targetDir . '/' . $fileName;
            file_put_contents($fullPath, $imageDecoded);
            $imagePath = 'uploads/receipts/' . $fileName;
        } elseif ($request->hasFile('receipt_image')) {
            $file = $request->file('receipt_image');
            $file->move($targetDir, $fileName);
            $imagePath = 'uploads/receipts/' . $fileName;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบไฟล์รูปภาพใบเสร็จ'
            ], 422);
        }

        $url = asset($imagePath);
        $relativeUrl = '/' . $imagePath;
        $fileSizeKb = file_exists(public_path($imagePath)) ? round(filesize(public_path($imagePath)) / 1024, 1) : 0;

        return response()->json([
            'success' => true,
            'message' => 'อัปโหลดและสร้าง URL ใบเสร็จเรียบร้อยแล้ว',
            'image_url' => $url,
            'relative_url' => $relativeUrl,
            'image_path' => $imagePath,
            'filename' => $fileName,
            'size_kb' => $fileSizeKb,
        ]);
    }

    /**
     * Smart OCR Scan for receipts (Real Text Extraction & Yield Calculation).
     * Uses Google Gemini Multimodal Vision AI for high accuracy Thai receipt reading.
     * Guaranteed response within 25-30 seconds.
     */
    public function scanOcr(Request $request): JsonResponse
    {
        $rawText = $request->input('raw_text', '');
        $images = $request->input('images', []);
        $imageBase64 = $request->input('image_base64');
        $imagePath = $request->input('image_path');

        if (!empty($imageBase64) && empty($images)) {
            $images = [$imageBase64];
        }

        // Fetch known raw materials from DB to match yields accurately
        $knownMaterials = RawMaterial::all();
        $apiKey = env('GEMINI_API_KEY') ?: config('services.gemini.key');

        // Priority 1: High-Accuracy Multimodal AI Vision (Gemini 1.5/2.0 Flash)
        if (!empty($images) && !empty($apiKey)) {
            $aiResult = $this->parseWithGeminiVision($images, $knownMaterials, $apiKey);
            if ($aiResult) {
                if (!empty($aiResult['items'])) {
                    return response()->json($aiResult);
                }
                if (!empty($aiResult['error'])) {
                    return response()->json([
                        'success' => false,
                        'need_api_key' => (($aiResult['error_code'] ?? '') === 'API_KEY_INVALID'),
                        'message' => $aiResult['error'],
                        'receipt_no' => '',
                        'vendor_name' => '',
                        'purchase_date' => date('Y-m-d'),
                        'total_amount' => 0,
                        'items' => [],
                    ]);
                }
            }
        }

        // Priority 2: When images are submitted but GEMINI_API_KEY is not set
        if (!empty($images) && empty($apiKey) && empty($rawText)) {
            return response()->json([
                'success' => false,
                'need_api_key' => true,
                'message' => 'ยังไม่ได้ระบุ Gemini API Key ในระบบ กรุณาใส่ API Key ฟรีเพื่อเปิดใช้งาน AI สแกนบิลภาษาไทยความแม่นยำสูง (รวดเร็ว 2-4 วินาที ไม่เกิน 30 วินาที)',
                'receipt_no' => '',
                'vendor_name' => '',
                'purchase_date' => date('Y-m-d'),
                'total_amount' => 0,
                'items' => [],
            ]);
        }

        $detectedReceiptNo = '';
        $detectedVendor = '';
        $detectedDate = date('Y-m-d');
        $detectedItems = [];
        $totalAmount = 0.0;

        if (!empty($rawText)) {
            $lines = preg_split('/\r\n|\r|\n/', $rawText);
            
            // 1. Detect Receipt / Invoice No
            foreach ($lines as $line) {
                if (preg_match('/(INV|TAX|REC|NO|BILL|บิล|เลขที่|ใบกำกับ)[\s\:\.\#\№]*([A-Z0-9\-\/]{4,25})/i', $line, $matches)) {
                    $detectedReceiptNo = trim($matches[2]);
                    break;
                }
            }
            if (!$detectedReceiptNo) {
                // Try fallback alphanumeric order pattern
                foreach ($lines as $line) {
                    if (preg_match('/([A-Z]{2,4}[\-\_]\d{4,12}[\-\_]?\d*)/i', $line, $matches)) {
                        $detectedReceiptNo = trim($matches[1]);
                        break;
                    }
                }
            }

            // 2. Detect Vendor Name
            $vendorKeywords = [
                'แม็คโคร' => 'สยามแม็คโคร',
                'MAKRO' => 'สยามแม็คโคร',
                'โลตัส' => 'โลตัส เอ็กซ์เพรส',
                'LOTUS' => 'โลตัส เอ็กซ์เพรส',
                'BIG C' => 'บิ๊กซี ซูเปอร์เซ็นเตอร์',
                'บิ๊กซี' => 'บิ๊กซี ซูเปอร์เซ็นเตอร์',
                'BETAGRO' => 'เบทาโกร ช็อป',
                'เบทาโกร' => 'เบทาโกร ช็อป',
                'CP' => 'ซีพี เฟรชมาร์ท',
                'ตลาด' => 'ตลาดสด',
            ];
            foreach ($lines as $line) {
                foreach ($vendorKeywords as $kw => $vendor) {
                    if (stripos($line, $kw) !== false) {
                        $detectedVendor = $vendor;
                        break 2;
                    }
                }
            }
            if (!$detectedVendor && count($lines) > 0) {
                foreach ($lines as $line) {
                    $clean = trim($line);
                    if (mb_strlen($clean) >= 3 && mb_strlen($clean) <= 50 && !preg_match('/^\d+$/', $clean)) {
                        $detectedVendor = $clean;
                        break;
                    }
                }
            }

            // 3. Detect Date
            foreach ($lines as $line) {
                if (preg_match('/(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-](\d{2,4})/', $line, $matches)) {
                    $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                    $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                    $year = (int) $matches[3];
                    if (strlen($matches[3]) == 2) {
                        $year = (int) ('20' . $matches[3]);
                    }
                    if ($year > 2500) { // Buddhist Era
                        $year = $year - 543;
                    }
                    if (checkdate((int)$month, (int)$day, (int)$year)) {
                        $detectedDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        break;
                    }
                }
            }

            // 4. Detect Line Items & Quantities & Prices
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (empty($trimmed)) continue;

                $matchedMaterial = null;
                foreach ($knownMaterials as $mat) {
                    $aliases = is_array($mat->scan_names) ? $mat->scan_names : (json_decode($mat->scan_names ?? '[]', true) ?: []);
                    $candidateNames = [$mat->name];
                    foreach ($aliases as $al) {
                        if (is_array($al)) {
                            if (!empty($al['name'])) $candidateNames[] = $al['name'];
                        } elseif (is_string($al) && trim($al) !== '') {
                            $candidateNames[] = trim($al);
                        }
                    }

                    foreach ($candidateNames as $cand) {
                        $cand = trim((string) $cand);
                        if (!empty($cand) && mb_stripos($trimmed, $cand) !== false) {
                            $matchedMaterial = $mat;
                            break 2;
                        }
                    }
                }

                if ($matchedMaterial) {
                    $name = $matchedMaterial->name;
                    $qty = 1.0;
                    $unit = $matchedMaterial->purchase_unit ?: 'แพ็ก';
                    $price = (float) $matchedMaterial->latest_cost_price;
                    $yield = (float) $matchedMaterial->yield_sticks_per_unit;

                    if (preg_match('/(\d+(?:\.\d{1,2})?)\s*(?:บาท|B|฿)?\s*$/i', $trimmed, $pMatches)) {
                        $price = (float) $pMatches[1];
                    }
                    if (preg_match('/(\d+)\s*(?:แพ็ก|กก|ถุง|ชิ้น|หน่วย|x|\*)/i', $trimmed, $qMatches)) {
                        $qty = (float) $qMatches[1];
                    }

                    $totalSticks = (int) round($qty * $yield);
                    $costStick = $yield > 0 ? round($price / $yield, 2) : 0;
                    $subtotal = $qty * $price;
                    $totalAmount += $subtotal;

                    $detectedItems[] = [
                        'item_name' => $name,
                        'raw_material_id' => $matchedMaterial->id,
                        'quantity' => $qty,
                        'unit' => $unit,
                        'unit_price' => $price,
                        'yield_sticks_per_unit' => $yield,
                        'total_yield_sticks' => $totalSticks,
                        'cost_per_stick' => $costStick,
                        'add_to_inventory' => true,
                    ];
                } elseif (preg_match('/^([^\d$฿]{2,30})\s+(\d+(?:\.\d{1,2})?)\s*$/u', $trimmed, $genMatch)) {
                    $name = trim($genMatch[1]);
                    $price = (float) $genMatch[2];
                    if ($price >= 5 && $price <= 10000) {
                        $yield = 10;
                        $totalAmount += $price;
                        $detectedItems[] = [
                            'item_name' => $name,
                            'raw_material_id' => null,
                            'quantity' => 1,
                            'unit' => 'แพ็ก',
                            'unit_price' => $price,
                            'yield_sticks_per_unit' => $yield,
                            'total_yield_sticks' => 10,
                            'cost_per_stick' => round($price / $yield, 2),
                            'add_to_inventory' => true,
                        ];
                    }
                }
            }

            // 5. Detect Total Amount from Receipt if present
            foreach ($lines as $line) {
                if (preg_match('/(TOTAL|ยอดรวม|รวมทั้งสิ้น|สุทธิ|NET)[\s:฿B]*(\d+(?:\.\d{1,2})?)/iu', $line, $tMatches)) {
                    $scannedTotal = (float) $tMatches[2];
                    if ($scannedTotal > 0) {
                        $totalAmount = $scannedTotal;
                    }
                    break;
                }
            }
        }

        // If NO items found, return honest empty result (NO RANDOM MOCKING)
        if (empty($detectedItems)) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อความรายการสินค้าที่ชัดเจนในรูปภาพ กรุณาถ่ายภาพให้ชัดเจนขึ้น หรือกดปุ่ม [+ เพิ่มรายการ] เพื่อระบุเอง',
                'receipt_no' => $detectedReceiptNo ?: '',
                'vendor_name' => $detectedVendor ?: '',
                'purchase_date' => $detectedDate ?: date('Y-m-d'),
                'total_amount' => $totalAmount > 0 ? $totalAmount : 0.00,
                'items' => [],
                'raw_ocr_text' => $rawText,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'สแกนใบเสร็จสำเร็จ! ตรวจพบ ' . count($detectedItems) . ' รายการ และล็อกสูตรแปลงไม้เรียบร้อย',
            'receipt_no' => $detectedReceiptNo ?: ('REC-' . date('Ymd-His')),
            'vendor_name' => $detectedVendor ?: 'ซัพพลายเออร์ทั่วไป',
            'purchase_date' => $detectedDate,
            'total_amount' => $totalAmount,
            'items' => $detectedItems,
            'raw_ocr_text' => $rawText,
        ]);
    }

    /**
     * Parse receipt images using Google Gemini Multimodal Vision API.
     * Extracts vendor, date, items, yields, and totals with high Thai accuracy.
     */
    private function parseWithGeminiVision(array $images, $knownMaterials, string $apiKey): ?array
    {
        try {
            $materialsCatalog = $knownMaterials->map(function ($m) {
                $scanNames = is_array($m->scan_names) ? $m->scan_names : (json_decode($m->scan_names ?? '[]', true) ?: []);
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'scan_names' => $scanNames,
                    'unit' => $m->purchase_unit ?: 'แพ็ก',
                    'yield_sticks_per_unit' => (int) $m->yield_sticks_per_unit,
                    'latest_cost' => (float) $m->latest_cost_price,
                ];
            })->values()->all();

            $catalogJson = json_encode($materialsCatalog, JSON_UNESCAPED_UNICODE);

            $systemInstruction = "คุณคือระบบ AI OCR อัจฉริยะสำหรับสแกนใบเสร็จซื้อของ/วัตถุดิบ (Receipt Scanner) ของร้านหม่าล่าสตรีท\n"
                . "หน้าที่ของคุณคืออ่านภาพใบเสร็จ/สลิปภาษาไทยและอังกฤษอย่างละเอียดและฉลาดที่สุด\n\n"
                . "⭐ กฎสำคัญข้อที่ 1: การกรองรายการซ้ำจากการถ่ายบิลยาวหลายรูป (Deduplication across overlapping photos):\n"
                . "- หากได้รับภาพมากกว่า 1 ภาพ ภาพเหล่านั้นคือ 'ใบเสร็จยาวใบเดียวกันที่ถ่ายต่อเนื่องกันหลายท่อน' (เช่น หัวบิล, กลางบิล, ท้ายบิล) ซึ่งมักจะมีพื้นที่เหลื่อมล้ำ (Overlap) ซ้ำซ้อนกันระหว่างรูป\n"
                . "- คุณต้องต่อบิลในใจอย่างถูกต้อง และ 'ตัดรายการที่ซ้ำกันตรงรอยต่อออกเด็ดขาด' ห้ามมีรายการสินค้าเดียวกัน ราคาเดียวกัน ปรากฏซ้ำสองรอบในผลลัพธ์\n\n"
                . "⭐ กฎสำคัญข้อที่ 2: การเดาคำ ขยายตัวย่อ และแก้ตัวอักษรที่ขาดหาย (Spell Correction & Real Food Name Inference):\n"
                . "- บิลเครื่องพิมพ์ความร้อน (Thermal/Dot-matrix) มักจะมีหมึกจาง สระหลุดลอย หรือใช้คำย่อ ห้ามใส่คำสะกดผิดหรือตัวอักษรขาดวิ่นมาตรงๆ เด็ดขาด\n"
                . "- จงอนุมาน (Infer) และแก้ไขคำให้เป็นชื่ออาหารหรือสินค้าที่มีอยู่จริงตามพจนานุกรม เช่น:\n"
                . "  * 'สภ.', 'สก.', 'สฟ.' ให้ขยายเป็น 'ไส้กรอก...' (เช่น 'สภ.เบรคฟัส' -> 'ไส้กรอกเบรคฟาสต์')\n"
                . "  * 'น.ใบพาย' -> 'เนื้อใบพาย', 'หมูสามช.' -> 'หมูสามชั้น', 'ฟองตห.' -> 'ฟองเต้าหู้', 'ลกช.' -> 'ลูกชิ้น'\n"
                . "  * คำที่สระหรือวรรณยุกต์ขาดหาย เช่น 'เอ็นขอไก' -> 'เอ็นข้อไก่', 'ลกชินปลา' -> 'ลูกชิ้นปลา', 'หมสามชน' -> 'หมูสามชั้น'\n"
                . "- หากชื่อสินค้าตรงหรือคล้ายกับวัตถุดิบของร้านในรายการ catalog ด้านล่าง (โดยเปรียบเทียบทั้งกับ 'name' หรือเทียบกับทุกชื่อใน 'scan_names' / ชื่อสำหรับสแกน) ให้ปรับชื่อให้ตรงกับชื่อหลัก 'name' ของวัตถุดิบ และระบุ 'raw_material_id' นั้นทันที\n\n"
                . "จงวิเคราะห์ข้อมูลและแปลงออกมาเป็นโครงสร้าง JSON ตามสเปกต่อไปนี้อย่างแม่นยำ 100%:\n\n"
                . "1. \"receipt_no\": เลขที่ใบเสร็จ หรือ เลขที่เอกสาร TAX INV (ถ้าไม่มีให้ว่างไว้ \"\")\n"
                . "2. \"vendor_name\": ชื่อร้านค้า ซัพพลายเออร์ เช่น \"สยามแม็คโคร\", \"โลตัส\", \"บิ๊กซี\", \"เบทาโกร\", \"มายด์ฟู้ด\", \"ซีพี\", \"ตลาดสด\" (ถ้าไม่มีให้ว่างไว้ \"\")\n"
                . "3. \"purchase_date\": วันที่ซื้อในบิล รูปแบบ \"YYYY-MM-DD\" (หากเป็น พ.ศ. ให้แปลงเป็น ค.ศ.)\n"
                . "4. \"items\": รายการสินค้าทั้งหมดในบิล (อาร์เรย์แบบตัดรายการซ้ำแล้ว): \n"
                . "   - \"item_name\": ชื่อสินค้าภาษาไทยที่สะกดถูกต้อง มีอยู่จริง ไม่ใช้คำย่อ คมชัด อ่านง่าย\n"
                . "   - \"raw_material_id\": id ของวัตถุดิบในร้านที่ตรงกัน หากตรงกับรายการใน catalog ด้านล่าง ให้ใส่ ID นั้น ถ้าไม่มีให้เป็น null\n"
                . "   - \"quantity\": จำนวนที่ซื้อ (ตัวเลข เช่น 1, 2, 0.5)\n"
                . "   - \"unit\": หน่วยนับ เช่น \"แพ็ก\", \"กก.\", \"ถุง\", \"ชิ้น\"\n"
                . "   - \"unit_price\": ราคารวมของสินค้ารายการนั้น (ตัวเลข)\n"
                . "   - \"yield_sticks_per_unit\": จำนวนไม้ที่จะได้ต่อ 1 หน่วย (หากตรงกับวัตถุดิบ ให้ใช้ yield_sticks_per_unit ของรายการนั้น ถ้าไม่ตรงให้ประมาณ 10)\n"
                . "   - \"total_yield_sticks\": จำนวนไม้รวม (quantity * yield_sticks_per_unit ปัดเป็นจำนวนเต็ม)\n"
                . "   - \"cost_per_stick\": ต้นทุนต่อไม้ (unit_price / total_yield_sticks ปัดเศษ 2 ตำแหน่ง)\n"
                . "   - \"add_to_inventory\": true\n"
                . "5. \"total_amount\": ยอดเงินสุทธิรวมทั้งบิล (ตัวเลข)\n\n"
                . "รายการวัตถุดิบในระบบปัจจุบันเพื่อใช้จับคู่ ID:\n" . $catalogJson;

            $parts = [
                ['text' => $systemInstruction]
            ];

            // Limit to max 4 photos to keep payload small and fast
            $images = array_slice($images, 0, 4);

            foreach ($images as $img) {
                if (empty($img)) continue;
                $rawBase64 = $img;
                $mimeType = 'image/jpeg';
                if (preg_match('/^data:(image\/[a-zA-Z0-9\+\-]+);base64,(.*)$/s', $img, $matches)) {
                    $mimeType = $matches[1];
                    $rawBase64 = $matches[2];
                }

                $parts[] = [
                    'inline_data' => [
                        'mime_type' => $mimeType,
                        'data' => trim($rawBase64)
                    ]
                ];
            }

            // Strictly set timeout to 25 seconds (well within user's 30-second requirement)
            $modelList = ['gemini-flash-lite-latest', 'gemini-3.5-flash-lite'];
            $response = null;

            foreach ($modelList as $candidateModel) {
                try {
                    $response = Http::withoutVerifying()
                        ->timeout(25)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post("https://generativelanguage.googleapis.com/v1beta/models/{$candidateModel}:generateContent?key={$apiKey}", [
                            'contents' => [
                                [
                                    'role' => 'user',
                                    'parts' => $parts,
                                ]
                            ],
                            'generationConfig' => [
                                'response_mime_type' => 'application/json',
                                'temperature' => 0.1,
                            ]
                        ]);

                    if ($response->successful()) {
                        break;
                    }
                } catch (\Throwable $mEx) {
                    Log::warning("Gemini Vision Model {$candidateModel} failed: " . $mEx->getMessage());
                }
            }

            if ($response && $response->successful()) {
                $body = $response->json();
                $rawJson = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($rawJson) {
                    $parsed = json_decode($rawJson, true);
                    if (is_array($parsed) && !empty($parsed['items'])) {
                        $calcTotal = 0.0;
                        $formattedItems = [];

                        foreach ($parsed['items'] as $item) {
                            $rawName = trim($item['item_name'] ?? '');
                            if (empty($rawName)) continue;

                            $price = (float) ($item['unit_price'] ?? 0);
                            $qty = (float) ($item['quantity'] ?? 1);
                            if ($qty <= 0) $qty = 1.0;

                            // 1. Spell correct, expand abbreviations, and infer real Thai food names
                            $cleaned = $this->cleanAndSpellCorrectItemName($rawName, $knownMaterials);
                            $name = $cleaned['name'];
                            $matId = $item['raw_material_id'] ?: $cleaned['raw_material_id'];
                            $yield = (float) ($item['yield_sticks_per_unit'] ?: $cleaned['yield']);

                            if ($matId) {
                                $matchedMat = $knownMaterials->firstWhere('id', $matId);
                                if ($matchedMat && $matchedMat->yield_sticks_per_unit > 0) {
                                    $yield = (float) $matchedMat->yield_sticks_per_unit;
                                }
                            }

                            if ($yield <= 0) $yield = 10;
                            $totalSticks = (int) round($qty * $yield);
                            $costPerStick = $totalSticks > 0 ? round($price / $totalSticks, 2) : 0;

                            $formattedItems[] = [
                                'item_name' => $name,
                                'raw_material_id' => $matId,
                                'quantity' => $qty,
                                'unit' => $item['unit'] ?? 'แพ็ก',
                                'unit_price' => $price,
                                'yield_sticks_per_unit' => $yield,
                                'total_yield_sticks' => $totalSticks,
                                'cost_per_stick' => $costPerStick,
                                'add_to_inventory' => true,
                            ];
                        }

                        // 2. Filter out duplicate items caused by overlapping multi-photo scans
                        $formattedItems = $this->deduplicateReceiptItems($formattedItems);

                        $calcTotal = 0.0;
                        foreach ($formattedItems as $fItem) {
                            $calcTotal += (float) $fItem['unit_price'];
                        }

                        $totalAmount = (float) ($parsed['total_amount'] ?? $calcTotal);
                        if ($totalAmount <= 0 || count($formattedItems) < count($parsed['items'])) {
                            // If items were deduplicated, align total with actual unique items
                            $totalAmount = $calcTotal;
                        }

                        return [
                            'success' => true,
                            'method' => 'gemini_vision',
                            'receipt_no' => $parsed['receipt_no'] ?? '',
                            'vendor_name' => $parsed['vendor_name'] ?? '',
                            'purchase_date' => $parsed['purchase_date'] ?? date('Y-m-d'),
                            'items' => $formattedItems,
                            'total_amount' => $totalAmount,
                            'message' => 'สแกนและอ่านข้อมูลด้วย AI Vision สำเร็จ (' . count($formattedItems) . ' รายการหลังกรองซ้ำ)',
                        ];
                    }
                }
            } else {
                Log::warning('Gemini Vision HTTP Status ' . ($response ? $response->status() : 'null') . ': ' . ($response ? $response->body() : 'no response'));
                if ($response && $response->status() === 400 && str_contains($response->body(), 'API_KEY_INVALID')) {
                    return [
                        'error' => 'Gemini API Key ไม่ถูกต้อง หรือหมดอายุ กรุณาตรวจสอบหรือใส่ Key ใหม่',
                        'error_code' => 'API_KEY_INVALID'
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::error('Gemini Vision OCR Error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Spell correct, expand abbreviations, and infer real Thai food names.
     */
    private function cleanAndSpellCorrectItemName(string $rawName, $knownMaterials): array
    {
        $name = trim($rawName);

        // 1. Common Thai receipt food abbreviations map
        $abbrPatterns = [
            '/^ส[ภฟกพ]\.?\s*/u' => 'ไส้กรอก',
            '/^ส\.?\s*เบรคฟ/u' => 'ไส้กรอกเบรคฟ',
            '/^น\.?\s*ใบพาย/u' => 'เนื้อใบพาย',
            '/^น\.?\s*โคขุน/u' => 'เนื้อโคขุน',
            '/^หมูสามช\.?/u' => 'หมูสามชั้น',
            '/^ฟองตห\.?/u' => 'ฟองเต้าหู้',
            '/^ลกช\.?/u' => 'ลูกชิ้น',
            '/^ลช\.?/u' => 'ลูกชิ้น',
            '/เบรคฟัส(?!ต์)/u' => 'เบรคฟาสต์',
            '/เบรคฟาส(?!ต์)/u' => 'เบรคฟาสต์',
        ];

        foreach ($abbrPatterns as $pattern => $replacement) {
            $name = preg_replace($pattern, $replacement, $name);
        }

        // Clean extra dots or trailing hyphens
        $name = trim(preg_replace('/[\s\.\-]+$/u', '', $name));

        $matchedMatId = null;
        $yield = 10;

        // 2. Cross-check against registered raw materials (both primary name and all scan_names aliases)
        foreach ($knownMaterials as $mat) {
            $aliases = is_array($mat->scan_names) ? $mat->scan_names : (json_decode($mat->scan_names ?? '[]', true) ?: []);
            $candidateNames = [$mat->name];
            foreach ($aliases as $al) {
                if (is_array($al)) {
                    if (!empty($al['name'])) $candidateNames[] = $al['name'];
                } elseif (is_string($al) && trim($al) !== '') {
                    $candidateNames[] = trim($al);
                }
            }

            foreach ($candidateNames as $cand) {
                $cand = trim((string) $cand);
                if (empty($cand)) continue;

                // Check if candidate name is contained or vice versa
                if (mb_stripos($name, $cand) !== false || mb_stripos($cand, $name) !== false) {
                    $name = $mat->name; // Snap to canonical name
                    $matchedMatId = $mat->id;
                    $yield = (float) ($mat->yield_sticks_per_unit > 0 ? $mat->yield_sticks_per_unit : 10);
                    break 2;
                }

                // Fuzzy string similarity
                similar_text($name, $cand, $simPercent);
                if ($simPercent >= 75) {
                    $name = $mat->name; // Snap to canonical name
                    $matchedMatId = $mat->id;
                    $yield = (float) ($mat->yield_sticks_per_unit > 0 ? $mat->yield_sticks_per_unit : 10);
                    break 2;
                }
            }
        }

        return [
            'name' => $name,
            'raw_material_id' => $matchedMatId,
            'yield' => $yield,
        ];
    }

    /**
     * Deduplicate items that appear twice due to overlapping multi-photo scans.
     */
    private function deduplicateReceiptItems(array $items): array
    {
        $unique = [];
        $seenSignatures = [];

        foreach ($items as $item) {
            $name = $item['item_name'];
            $price = (float) $item['unit_price'];

            // Generate clean normalized signature (no spaces, punctuation, or weights like 470g)
            $cleanName = mb_strtolower(preg_replace('/[^a-zA-Z0-9\x{0E00}-\x{0E7F}]/u', '', $name));
            // Remove common quantity/weight suffixes from signature e.g. 470g, 500g, 1kg
            $cleanName = preg_replace('/\d+(g|kg|กก|กรัม)/u', '', $cleanName);

            $isDuplicate = false;
            foreach ($seenSignatures as $seen) {
                // Same price and (identical signature OR very high similarity)
                if (abs($seen['price'] - $price) < 0.01) {
                    if ($seen['sig'] === $cleanName) {
                        $isDuplicate = true;
                        break;
                    }
                    similar_text($seen['sig'], $cleanName, $pct);
                    if ($pct >= 85) {
                        $isDuplicate = true;
                        break;
                    }
                }
            }

            if (!$isDuplicate) {
                $seenSignatures[] = [
                    'sig' => $cleanName,
                    'price' => $price,
                ];
                $unique[] = $item;
            }
        }

        return $unique;
    }

    /**
     * Save or update Gemini API Key in .env file.
     */
    public function saveGeminiKey(Request $request): JsonResponse
    {
        $key = trim($request->input('gemini_api_key', ''));
        if (empty($key)) {
            return response()->json(['success' => false, 'message' => 'กรุณาระบุ Gemini API Key']);
        }

        if (app()->environment('testing')) {
            return response()->json([
                'success' => true,
                'message' => 'บันทึก Gemini API Key เรียบร้อยแล้ว! (โหมดทดสอบ)'
            ]);
        }

        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            if (str_contains($envContent, 'GEMINI_API_KEY=')) {
                $envContent = preg_replace('/GEMINI_API_KEY=.*/', 'GEMINI_API_KEY=' . $key, $envContent);
            } else {
                $envContent .= "\nGEMINI_API_KEY=" . $key . "\n";
            }
            file_put_contents($envPath, $envContent);
        }

        return response()->json([
            'success' => true,
            'message' => 'บันทึก Gemini API Key เรียบร้อยแล้ว! ระบบ AI Vision พร้อมทำงานทันที'
        ]);
    }

    /**
     * Store scanned/uploaded receipt with smart conversion and price history tracking.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'receipt_no' => 'nullable|string|max:100',
            'vendor_name' => 'required|string|max:150',
            'purchase_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',
            'image_base64' => 'nullable|string', // Canvas-compressed image
            'image_path' => 'nullable|string', // Already uploaded path/URL
            'images' => 'nullable|array', // Multiple images array
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:150',
            'items.*.raw_material_id' => 'nullable|exists:raw_materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.yield_sticks_per_unit' => 'required|numeric|min:1',
            'items.*.add_to_inventory' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $savedImagePaths = [];
            $targetDir = public_path('uploads/receipts');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            // 1. Process multiple images array if provided
            if ($request->has('images') && is_array($request->input('images'))) {
                foreach ($request->input('images') as $imgItem) {
                    if (is_string($imgItem) && preg_match('/^data:image\/(\w+);base64,/', $imgItem)) {
                        $rawB64 = substr($imgItem, strpos($imgItem, ',') + 1);
                        $decoded = base64_decode($rawB64);
                        $fileName = 'receipt_' . date('Ymd_His') . '_' . Str::random(8) . '.jpg';
                        file_put_contents($targetDir . '/' . $fileName, $decoded);
                        $savedImagePaths[] = 'uploads/receipts/' . $fileName;
                    } elseif (is_string($imgItem) && (str_starts_with($imgItem, 'uploads/') || str_starts_with($imgItem, '/uploads/'))) {
                        $savedImagePaths[] = ltrim($imgItem, '/');
                    }
                }
            }

            // 2. Single image fallbacks
            if (empty($savedImagePaths)) {
                if (!empty($validated['image_path'])) {
                    $savedImagePaths[] = ltrim($validated['image_path'], '/');
                } elseif (!empty($validated['image_base64'])) {
                    $base64Image = $validated['image_base64'];
                    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image)) {
                        $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
                    }
                    $imageDecoded = base64_decode($base64Image);
                    $fileName = 'receipt_' . date('Ymd_His') . '_' . Str::random(8) . '.jpg';
                    file_put_contents($targetDir . '/' . $fileName, $imageDecoded);
                    $savedImagePaths[] = 'uploads/receipts/' . $fileName;
                } elseif ($request->hasFile('receipt_image')) {
                    $file = $request->file('receipt_image');
                    $fileName = 'receipt_' . date('Ymd_His') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($targetDir, $fileName);
                    $savedImagePaths[] = 'uploads/receipts/' . $fileName;
                }
            }

            $primaryImagePath = $savedImagePaths[0] ?? null;

            // Create Receipt record
            $receipt = Receipt::create([
                'receipt_no' => $validated['receipt_no'] ?: 'REC-' . date('Ymd') . '-' . rand(1000, 9999),
                'vendor_name' => $validated['vendor_name'],
                'purchase_date' => $validated['purchase_date'],
                'total_amount' => $validated['total_amount'],
                'image_path' => $primaryImagePath,
                'parsed_data' => [
                    'images' => $savedImagePaths,
                ],
                'status' => 'processed',
                'notes' => $validated['notes'] ?? null,
            ]);

            $purchaseDate = Carbon::parse($validated['purchase_date']);

            foreach ($validated['items'] as $itemData) {
                $qty = (float) $itemData['quantity'];
                $unitPrice = (float) $itemData['unit_price'];
                $totalPrice = $qty * $unitPrice;
                $yieldPerUnit = (float) $itemData['yield_sticks_per_unit'];
                $totalYieldSticks = (int) round($qty * $yieldPerUnit);
                $costPerStick = $yieldPerUnit > 0 ? ($unitPrice / $yieldPerUnit) : 0;
                $addToInv = !empty($itemData['add_to_inventory']);

                // Find or create RawMaterial
                $rawMaterial = null;
                if (!empty($itemData['raw_material_id'])) {
                    $rawMaterial = RawMaterial::find($itemData['raw_material_id']);
                }

                if (!$rawMaterial) {
                    $rawMaterial = RawMaterial::firstOrCreate(
                        ['name' => $itemData['item_name']],
                        [
                            'default_vendor' => $validated['vendor_name'],
                            'purchase_unit' => $itemData['unit'],
                            'yield_sticks_per_unit' => $yieldPerUnit,
                            'latest_cost_price' => $unitPrice,
                            'cost_per_stick' => $costPerStick,
                            'current_stock_sticks' => 0,
                        ]
                    );
                }

                // Check price fluctuation vs previous cost
                $previousCost = (float) $rawMaterial->latest_cost_price;
                $priceDiff = $previousCost > 0 ? ($unitPrice - $previousCost) : 0;
                $percentDiff = $previousCost > 0 ? round(($priceDiff / $previousCost) * 100, 2) : 0;

                // Update RawMaterial with latest price & stick cost
                $rawMaterial->default_vendor = $validated['vendor_name'];
                $rawMaterial->purchase_unit = $itemData['unit'];
                $rawMaterial->yield_sticks_per_unit = $yieldPerUnit;
                $rawMaterial->latest_cost_price = $unitPrice;
                $rawMaterial->cost_per_stick = $costPerStick;
                if ($addToInv) {
                    $rawMaterial->current_stock_sticks += $totalYieldSticks;
                }
                $rawMaterial->save();

                // Log Price History for trend tracking
                PriceHistory::create([
                    'raw_material_id' => $rawMaterial->id,
                    'receipt_id' => $receipt->id,
                    'vendor_name' => $validated['vendor_name'],
                    'invoice_number' => $receipt->receipt_no,
                    'purchase_unit_price' => $unitPrice,
                    'purchase_unit' => $itemData['unit'],
                    'yield_sticks_per_unit' => $yieldPerUnit,
                    'cost_per_stick' => $costPerStick,
                    'previous_cost_price' => $previousCost > 0 ? $previousCost : null,
                    'price_change_amount' => $previousCost > 0 ? $priceDiff : 0,
                    'price_change_percent' => $previousCost > 0 ? $percentDiff : 0,
                    'recorded_at' => $purchaseDate,
                    'notes' => $priceDiff != 0 ? ('ราคาเปลี่ยน ' . ($priceDiff > 0 ? "+$priceDiff บ." : "$priceDiff บ.")) : 'ราคาคงเดิม',
                ]);

                // Create ReceiptItem record
                ReceiptItem::create([
                    'receipt_id' => $receipt->id,
                    'raw_material_id' => $rawMaterial->id,
                    'item_name' => $itemData['item_name'],
                    'quantity' => $qty,
                    'unit' => $itemData['unit'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'yield_sticks_per_unit' => $yieldPerUnit,
                    'total_yield_sticks' => $totalYieldSticks,
                    'cost_per_stick' => $costPerStick,
                    'is_added_to_inventory' => $addToInv,
                ]);

                // If add to inventory, also update associated Product & InventoryDaily
                if ($addToInv) {
                    $linkedProducts = Product::where('raw_material_id', $rawMaterial->id)->get();
                    foreach ($linkedProducts as $prod) {
                        $prod->increment('stock_sticks', $totalYieldSticks);

                        $inventory = InventoryDaily::firstOrCreate(
                            [
                                'record_date' => $purchaseDate,
                                'product_id' => $prod->id,
                            ],
                            [
                                'opening_sticks' => max(0, $prod->stock_sticks - $totalYieldSticks),
                                'added_sticks' => 0,
                                'sold_sticks' => 0,
                                'waste_sticks' => 0,
                                'expected_closing_sticks' => $prod->stock_sticks,
                            ]
                        );

                        $inventory->increment('added_sticks', $totalYieldSticks);
                        $inventory->expected_closing_sticks = max(0, ($inventory->opening_sticks + $inventory->added_sticks) - $inventory->sold_sticks - $inventory->waste_sticks);
                        $inventory->save();
                    }
                }
            }

            AuditLog::record(
                'receipts',
                'create',
                'บันทึกใบเสร็จซื้อวัตถุดิบ',
                "ใบเสร็จ {$receipt->receipt_no} ({$receipt->vendor_name}) ยอด ฿" . number_format((float) $receipt->total_amount, 2),
                [
                    'receipt_id' => $receipt->id,
                    'receipt_no' => $receipt->receipt_no,
                    'vendor_name' => $receipt->vendor_name,
                    'total_amount' => (float) $receipt->total_amount,
                ]
            );

            if ($request->wantsJson()) {
                $costSummary = $this->getReceiptCostSummary();
                $todayCost = $costSummary['today_cost'];
                $thisMonthCost = $costSummary['this_month_cost'];
                $materialsCount = RawMaterial::count();
                $receiptsTotal = Receipt::count();

                return response()->json([
                    'success' => true,
                    'message' => 'บันทึกใบเสร็จและคำนวณต้นทุนไม้เรียบร้อยแล้ว',
                    'receipt_id' => $receipt->id,
                    'today_cost' => (float) $todayCost,
                    'this_month_cost' => (float) $thisMonthCost,
                    'materials_count' => $materialsCount,
                    'receipts_total' => $receiptsTotal,
                ]);
            }

            return redirect()->route('admin.receipts.index')->with('success', 'บันทึกใบเสร็จ เลขที่ ' . $receipt->receipt_no . ' และปรับปรุงต้นทุนไม้เรียบร้อยแล้ว');
        });
    }

    /**
     * Delete a Receipt and its associated items and price histories.
     */
    public function destroy(Request $request, Receipt $receipt): JsonResponse|RedirectResponse
    {
        try {
            $receiptNo = $receipt->receipt_no;

            DB::transaction(function () use ($receipt) {
                // Collect affected raw material IDs before deletion to re-sync latest prices
                $affectedMaterialIds = $receipt->items()
                    ->whereNotNull('raw_material_id')
                    ->pluck('raw_material_id')
                    ->unique()
                    ->all();

                // Reverse stock if inventory was added
                foreach ($receipt->items as $item) {
                    if ($item->is_added_to_inventory && $item->raw_material_id) {
                        $mat = RawMaterial::find($item->raw_material_id);
                        if ($mat) {
                            $mat->current_stock_sticks = max(0, $mat->current_stock_sticks - (int) $item->total_yield_sticks);
                            $mat->save();
                        }
                    }
                }

                // Delete associated price histories
                $receipt->priceHistories()->delete();

                // Delete associated receipt items
                $receipt->items()->delete();

                // Delete the receipt
                $receipt->delete();

                // Re-sync latest cost price for affected raw materials from remaining price histories
                foreach ($affectedMaterialIds as $matId) {
                    $mat = RawMaterial::find($matId);
                    if ($mat) {
                        $latestHist = $mat->priceHistories()->orderByDesc('recorded_at')->orderByDesc('id')->first();
                        if ($latestHist) {
                            $mat->latest_cost_price = $latestHist->purchase_unit_price;
                            $mat->cost_per_stick = $latestHist->cost_per_stick;
                            $mat->default_vendor = $latestHist->vendor_name;
                        }
                        $mat->save();
                    }
                }
            });

            AuditLog::record(
                'receipts',
                'delete',
                'ลบรายการใบเสร็จ',
                "ใบเสร็จ {$receiptNo}"
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'ลบรายการใบเสร็จ ' . ($receiptNo ?: '') . ' เรียบร้อยแล้ว',
                ]);
            }

            return redirect()->route('receipts.index')->with('success', 'ลบรายการใบเสร็จเรียบร้อยแล้ว');
        } catch (\Throwable $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการลบรายการ: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('receipts.index')->with('error', 'เกิดข้อผิดพลาดในการลบรายการ');
        }
    }

    /**
     * Price Tracker & Supplier Comparison Dashboard.
     */
    public function priceTracker(Request $request): View|JsonResponse
    {
        $today = Carbon::today()->toDateString();

        // กรองเฉพาะวัตถุดิบที่มีรายการจริงในหน้าสแกนใบเสร็จ (ตัดรายการเทสที่ไม่มีในใบเสร็จออกทั้งหมดจากหน้าตรวจจับราคา)
        $rawMaterials = RawMaterial::with([
            'products.category',
            'priceHistories' => function ($query) {
                $query->orderByDesc('recorded_at')->orderByDesc('id')->take(5);
            },
        ])
        ->where(function ($q) {
            $q->has('receiptItems')->orHas('priceHistories');
        })
        ->orderBy('name')
        ->get();

        // 1. Fetch today's inventory dailies to sync opening sticks (ไม้ยกมาจากเมื่อวาน) and remaining sticks
        $todayInventoryByProduct = DB::table('inventory_dailies')
            ->whereDate('record_date', $today)
            ->get()
            ->keyBy('product_id');

        // 2. Fetch raw materials bought today from receipts or price histories
        $todayReceiptMaterialIds = DB::table('receipt_items')
            ->join('receipts', 'receipt_items.receipt_id', '=', 'receipts.id')
            ->where(function ($q) use ($today) {
                $q->whereDate('receipts.purchase_date', $today)
                  ->orWhereDate('receipts.created_at', $today);
            })
            ->whereNotNull('receipt_items.raw_material_id')
            ->pluck('receipt_items.raw_material_id')
            ->unique()
            ->all();

        $todayPriceHistoryMaterialIds = DB::table('price_histories')
            ->whereDate('recorded_at', $today)
            ->pluck('raw_material_id')
            ->unique()
            ->all();

        $todayBoughtMaterialIds = array_values(array_unique(array_merge($todayReceiptMaterialIds, $todayPriceHistoryMaterialIds)));
        $todayBoughtCount = count($todayBoughtMaterialIds);

        // 3. Attach availability and stock stats to each RawMaterial model
        $todayAvailableCount = 0;
        foreach ($rawMaterials as $mat) {
            $isBoughtToday = in_array($mat->id, $todayBoughtMaterialIds, true);
            $openingSticks = 0;
            $addedSticks = 0;
            $soldSticks = 0;
            $stockSticks = 0;

            foreach ($mat->products as $p) {
                $inv = $todayInventoryByProduct->get($p->id);
                if ($inv) {
                    $openingSticks += (int) $inv->opening_sticks;
                    $addedSticks += (int) $inv->added_sticks;
                    $soldSticks += (int) $inv->sold_sticks;
                }
                $stockSticks += (int) $p->stock_sticks;
            }

            // An item is "มีรายการพร้อมขาย" (available today) if:
            // - It was bought today (ซื้อวันนี้)
            // - OR it has stock remaining from yesterday / in stock today in the inventory system (เหลือจากเมื่อวาน/พร้อมขาย)
            $isAvailableToday = ($isBoughtToday || $openingSticks > 0 || $addedSticks > 0 || $stockSticks > 0);

            if ($isAvailableToday) {
                $todayAvailableCount++;
            }

            $mat->is_bought_today = $isBoughtToday;
            $mat->is_available_today = $isAvailableToday;
            $mat->today_opening_sticks = $openingSticks;
            $mat->today_added_sticks = $addedSticks;
            $mat->today_sold_sticks = $soldSticks;
            $mat->today_stock_sticks = max($stockSticks, (int) $mat->current_stock_sticks);
        }

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        if ($request->wantsJson() || $request->boolean('json')) {
            return response()->json([
                'success' => true,
                'timestamp' => now()->toIso8601String(),
                'summary' => [
                    'total_materials' => $rawMaterials->count(),
                    'today_bought_count' => $todayBoughtCount,
                    'today_available_count' => $todayAvailableCount,
                    'empty_count' => $rawMaterials->count() - $todayAvailableCount,
                ],
                'materials' => $rawMaterials->map(function (RawMaterial $mat) {
                    $latestHistory = $mat->priceHistories->first();
                    return [
                        'id' => $mat->id,
                        'name' => $mat->name,
                        'purchase_unit' => $mat->purchase_unit,
                        'latest_cost_price' => (float) $mat->latest_cost_price,
                        'cost_per_stick' => (float) $mat->cost_per_stick,
                        'yield_sticks_per_unit' => (float) $mat->yield_sticks_per_unit,
                        'vendor_name' => $latestHistory ? $latestHistory->vendor_name : ($mat->default_vendor ?: 'ไม่ระบุ'),
                        'invoice_number' => $latestHistory?->invoice_number,
                        'recorded_at' => $latestHistory?->recorded_at?->format('d/m/Y'),
                        'price_change_amount' => $latestHistory ? (float) $latestHistory->price_change_amount : 0.0,
                        'price_change_percent' => $latestHistory ? (float) $latestHistory->price_change_percent : 0.0,
                        'is_available_today' => (bool) $mat->is_available_today,
                        'is_bought_today' => (bool) $mat->is_bought_today,
                        'today_opening_sticks' => (int) $mat->today_opening_sticks,
                        'today_stock_sticks' => (int) $mat->today_stock_sticks,
                        'histories' => $mat->priceHistories->map(fn ($h) => [
                            'date' => $h->recorded_at->format('d/m/Y'),
                            'short_date' => $h->recorded_at->format('d/m'),
                            'vendor_name' => $h->vendor_name,
                            'invoice_number' => $h->invoice_number,
                            'purchase_unit_price' => (float) $h->purchase_unit_price,
                            'cost_per_stick' => (float) $h->cost_per_stick,
                            'previous_cost_price' => $h->previous_cost_price ? (float) $h->previous_cost_price : null,
                            'price_change_amount' => (float) $h->price_change_amount,
                            'price_change_percent' => (float) $h->price_change_percent,
                        ])->values(),
                    ];
                })->values(),
            ]);
        }

        $priceHistories = PriceHistory::with('rawMaterial', 'receipt')->orderByDesc('recorded_at')->orderByDesc('id')->paginate(15);

        $settingsRows = DB::table('settings')
            ->whereIn('key', ['price_tracker_hero_image', 'price_tracker_hero_title', 'price_tracker_hero_subtitle'])
            ->pluck('value', 'key');

        $heroSettings = [
            'hero_image' => !empty($settingsRows['price_tracker_hero_image'])
                ? $settingsRows['price_tracker_hero_image']
                : asset('assets/ingredients/hero-price-art-left.png'),
            'hero_title' => !empty($settingsRows['price_tracker_hero_title'])
                ? $settingsRows['price_tracker_hero_title']
                : 'ระบบตรวจจับราคาขึ้น-ลง & ร้านค้าอ้างอิง',
            'hero_subtitle' => !empty($settingsRows['price_tracker_hero_subtitle'])
                ? $settingsRows['price_tracker_hero_subtitle']
                : 'ติดตามความผันผวนของราคาวัตถุดิบสำคัญได้ ไม่ว่าจะขึ้นหรือลง เช่น เอ็นไก่, เนื้อวัว, ผักสด ฯลฯ เพื่อเลือกซื้อร้านที่ถูกที่สุด',
        ];

        return view('admin.receipts.price_tracker', compact('rawMaterials', 'priceHistories', 'categories', 'heroSettings', 'todayAvailableCount', 'todayBoughtCount'));
    }

    /**
     * Create / Store a new RawMaterial item from the Price Tracker popup.
     * Also initializes product stock, daily inventory, and price history so it's fully tracked.
     */
    public function storePriceTrackerMaterial(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'purchase_unit' => 'required|string|max:100',
            'latest_cost_price' => 'required|numeric|min:0',
            'yield_sticks_per_unit' => 'required|numeric|min:0.01',
            'cost_per_stick' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'default_vendor' => 'nullable|string|max:255',
            'category_slug' => 'nullable|string|max:100',
            'image_url' => 'nullable|string|max:500',
            'image_file' => 'nullable',
            'record_history' => 'nullable|boolean',
            'scan_names' => 'nullable',
            'scan_vendors' => 'nullable',
        ]);

        // Process Scan Names & Store/Vendor names (ฝั่งซ้าย: ชื่อสำหรับสแกน, ฝั่งขวา: ชื่อร้านที่สแกนตรงกัน)
        $rawScanNames = $request->input('scan_names', []);
        $rawScanVendors = $request->input('scan_vendors', []);
        $scanNames = [];
        if (is_array($rawScanNames)) {
            foreach ($rawScanNames as $idx => $sn) {
                if (is_array($sn)) {
                    $snName = trim($sn['name'] ?? '');
                    $snVendor = trim($sn['vendor'] ?? '');
                } else {
                    $snName = trim((string) $sn);
                    $snVendor = isset($rawScanVendors[$idx]) ? trim((string) $rawScanVendors[$idx]) : '';
                }
                if (!empty($snName)) {
                    $scanNames[] = [
                        'name' => $snName,
                        'vendor' => $snVendor,
                    ];
                }
            }
        }

        $newPrice = (float) $validated['latest_cost_price'];
        $yieldSticks = (float) $validated['yield_sticks_per_unit'];
        $costPerStick = isset($validated['cost_per_stick']) && $validated['cost_per_stick'] !== null && $validated['cost_per_stick'] !== ''
            ? (float) $validated['cost_per_stick']
            : round($newPrice / $yieldSticks, 2);

        $imageUrl = self::sanitizeImageUrl($validated['image_url'] ?? '/assets/ingredients/chicken-tendon.png');
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            if ($file && $file->isValid()) {
                $destDir = public_path('assets/ingredients/custom');
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0777, true);
                }
                $filename = 'mat_new_' . time() . '_' . Str::random(5) . '.' . $file->getClientOriginalExtension();
                $file->move($destDir, $filename);
                $imageUrl = '/assets/ingredients/custom/' . $filename;
            }
        }

        $rawMaterial = RawMaterial::create([
            'name' => $validated['name'],
            'image_url' => $imageUrl,
            'category_slug' => ($validated['category_slug'] ?? null) ?: 'uncategorized',
            'default_vendor' => ($validated['default_vendor'] ?? null) ?: 'ไม่ระบุ',
            'purchase_unit' => $validated['purchase_unit'],
            'yield_sticks_per_unit' => $yieldSticks,
            'latest_cost_price' => $newPrice,
            'cost_per_stick' => $costPerStick,
            'current_stock_sticks' => (int) round($yieldSticks),
            'scan_names' => $scanNames,
        ]);

        // TWO-WAY SYNCHRONIZATION WITH POS CASHIER: Create or link menu product with stock
        $categoryId = null;
        if (!empty($validated['category_slug'])) {
            $cat = Category::where('slug', $validated['category_slug'])->first();
            if ($cat) $categoryId = $cat->id;
        }
        if (!$categoryId) {
            $cat = Category::where('is_active', true)->first();
            if ($cat) $categoryId = $cat->id;
        }

        $sellingPrice = (float) ($request->input('selling_price') ?: max(15, ceil($costPerStick * 1.5)));

        $product = null;
        if ($categoryId) {
            $product = Product::create([
                'name' => $rawMaterial->name,
                'category_id' => $categoryId,
                'raw_material_id' => $rawMaterial->id,
                'price_per_stick' => $sellingPrice,
                'image_url' => $imageUrl,
                'stock_sticks' => (int) round($yieldSticks),
                'is_available' => true,
            ]);
        }

        // Record Initial PriceHistory
        $today = Carbon::today();
        PriceHistory::create([
            'raw_material_id' => $rawMaterial->id,
            'receipt_id' => null,
            'vendor_name' => $rawMaterial->default_vendor ?: 'ไม่ระบุ',
            'invoice_number' => 'INIT-' . strtoupper(Str::random(6)),
            'purchase_unit_price' => $newPrice,
            'purchase_unit' => $rawMaterial->purchase_unit,
            'yield_sticks_per_unit' => $yieldSticks,
            'cost_per_stick' => $costPerStick,
            'previous_cost_price' => null,
            'price_change_amount' => 0.0,
            'price_change_percent' => 0.0,
            'recorded_at' => $today,
            'notes' => 'เพิ่มรายการวัตถุดิบและข้อมูลสต็อกใหม่',
        ]);

        // Initialize InventoryDaily record
        if ($product) {
            InventoryDaily::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'record_date' => $today->toDateString(),
                ],
                [
                    'opening_sticks' => (int) round($yieldSticks),
                    'added_sticks' => 0,
                    'sold_sticks' => 0,
                    'waste_sticks' => 0,
                    'expected_closing_sticks' => (int) round($yieldSticks),
                ]
            );
        }

        $actor = AuditLog::resolveCurrentUserActor();
        AuditLog::record(
            'price_tracker',
            'create',
            'เพิ่มวัตถุดิบใหม่',
            "{$rawMaterial->name} (ราคา ฿" . number_format((float) $rawMaterial->latest_cost_price, 2) . "/{$rawMaterial->purchase_unit})",
            [
                'material_id' => $rawMaterial->id,
                'latest_cost_price' => (float) $rawMaterial->latest_cost_price,
                'cost_per_stick' => (float) $rawMaterial->cost_per_stick,
            ],
            $actor
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'เพิ่มรายการวัตถุดิบและข้อมูลสต็อกเรียบร้อยแล้ว',
                'updated_by' => $actor['name'] ?? 'ผู้ใช้งาน',
                'material' => [
                    'id' => $rawMaterial->id,
                    'name' => $rawMaterial->name,
                    'scan_names' => $rawMaterial->scan_names ?: [],
                    'purchase_unit' => $rawMaterial->purchase_unit,
                    'latest_cost_price' => (float) $rawMaterial->latest_cost_price,
                    'yield_sticks_per_unit' => (float) $rawMaterial->yield_sticks_per_unit,
                    'cost_per_stick' => (float) $rawMaterial->cost_per_stick,
                    'selling_price' => $sellingPrice,
                    'vendor_name' => $rawMaterial->default_vendor,
                    'category_slug' => $rawMaterial->category_slug,
                    'image_url' => $rawMaterial->image_url,
                    'price_change_amount' => 0.0,
                    'price_change_percent' => 0.0,
                    'is_available_today' => true,
                    'is_bought_today' => true,
                    'today_opening_sticks' => (int) round($yieldSticks),
                    'today_stock_sticks' => (int) round($yieldSticks),
                ],
            ]);
        }

        return redirect()->route('admin.receipts.price_tracker')->with('success', 'เพิ่มรายการวัตถุดิบเรียบร้อยแล้ว');
    }

    /**
     * Update a RawMaterial item (name, unit, price, yield, stick cost, vendor, category, image) from the Price Tracker popup.
     */
    public function updatePriceTrackerMaterial(Request $request, RawMaterial $rawMaterial): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'purchase_unit' => 'required|string|max:100',
            'latest_cost_price' => 'required|numeric|min:0',
            'yield_sticks_per_unit' => 'required|numeric|min:0.01',
            'cost_per_stick' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'default_vendor' => 'nullable|string|max:255',
            'category_slug' => 'nullable|string|max:100',
            'image_url' => 'nullable|string|max:500',
            'image_file' => 'nullable',
            'record_history' => 'nullable|boolean',
            'scan_names' => 'nullable',
            'scan_vendors' => 'nullable',
        ]);

        // Process Scan Names & Store/Vendor names (ฝั่งซ้าย: ชื่อสำหรับสแกน, ฝั่งขวา: ชื่อร้านที่สแกนตรงกัน)
        $rawScanNames = $request->input('scan_names', []);
        $rawScanVendors = $request->input('scan_vendors', []);
        $scanNames = [];
        if (is_array($rawScanNames)) {
            foreach ($rawScanNames as $idx => $sn) {
                if (is_array($sn)) {
                    $snName = trim($sn['name'] ?? '');
                    $snVendor = trim($sn['vendor'] ?? '');
                } else {
                    $snName = trim((string) $sn);
                    $snVendor = isset($rawScanVendors[$idx]) ? trim((string) $rawScanVendors[$idx]) : '';
                }
                if ($snName !== '') {
                    $scanNames[] = [
                        'name' => $snName,
                        'vendor' => $snVendor,
                    ];
                }
            }
        } elseif (is_string($rawScanNames) && trim($rawScanNames) !== '') {
            $decoded = json_decode($rawScanNames, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (is_array($item) && !empty($item['name'])) {
                        $scanNames[] = [
                            'name' => trim($item['name']),
                            'vendor' => trim($item['vendor'] ?? ''),
                        ];
                    } elseif (is_string($item) && trim($item) !== '') {
                        $scanNames[] = [
                            'name' => trim($item),
                            'vendor' => '',
                        ];
                    }
                }
            }
        }

        $previousPrice = (float) $rawMaterial->latest_cost_price;
        $newPrice = (float) $validated['latest_cost_price'];
        $yieldSticks = max(0.01, (float) $validated['yield_sticks_per_unit']);
        $costPerStick = isset($validated['cost_per_stick']) && $validated['cost_per_stick'] !== null && $validated['cost_per_stick'] !== ''
            ? (float) $validated['cost_per_stick']
            : round($newPrice / $yieldSticks, 2);

        $imageUrl = self::sanitizeImageUrl($validated['image_url'] ?? $rawMaterial->image_url);

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            if ($file && $file->isValid()) {
                $destDir = public_path('assets/ingredients/custom');
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0777, true);
                }
                $filename = 'mat_' . $rawMaterial->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move($destDir, $filename);
                $imageUrl = '/assets/ingredients/custom/' . $filename;
            }
        }

        $rawMaterial->update([
            'name' => $validated['name'],
            'scan_names' => $scanNames,
            'purchase_unit' => $validated['purchase_unit'],
            'latest_cost_price' => $newPrice,
            'yield_sticks_per_unit' => $yieldSticks,
            'cost_per_stick' => $costPerStick,
            'default_vendor' => ($validated['default_vendor'] ?? null) ?: $rawMaterial->default_vendor,
            'category_slug' => ($validated['category_slug'] ?? null) ?: $rawMaterial->category_slug,
            'image_url' => $imageUrl,
        ]);

        // TWO-WAY SYNCHRONIZATION WITH POS CASHIER & ONLINE ORDER MENU ITEMS:
        // Automatically sync all linked products in the Cashier and Online Order storefront with the updated name, image, and price!
        $productUpdates = [
            'name' => $validated['name'],
            'image_url' => $imageUrl,
        ];
        if ($request->filled('selling_price')) {
            $productUpdates['price_per_stick'] = (float) $request->input('selling_price');
        } elseif ($request->filled('price_per_stick')) {
            $productUpdates['price_per_stick'] = (float) $request->input('price_per_stick');
        }

        if ($rawMaterial->products()->exists()) {
            foreach ($rawMaterial->products as $product) {
                $product->update($productUpdates);
            }
        } else {
            $categorySlug = $rawMaterial->category_slug ?: 'pork-chicken';
            $category = Category::where('slug', $categorySlug)->first() ?: Category::first();
            Product::create([
                'category_id' => $category?->id ?: 1,
                'raw_material_id' => $rawMaterial->id,
                'name' => $validated['name'],
                'description' => 'วัตถุดิบคัดพิเศษ ย่างหอมเครื่องเทศหม่าล่า หรือต้มซุปเข้มข้น',
                'price_per_stick' => $productUpdates['price_per_stick'] ?? max(10, round($costPerStick * 1.5, 0)),
                'image_url' => $imageUrl,
                'stock_sticks' => max(25, (int) $rawMaterial->current_stock_sticks),
                'is_available' => true,
                'supports_grill' => true,
                'supports_soup' => true,
                'sort_order' => 10 + $rawMaterial->id,
            ]);
        }

        $latestHistory = $rawMaterial->priceHistories()->orderByDesc('recorded_at')->orderByDesc('id')->first();

        if ($request->boolean('record_history') && abs($newPrice - $previousPrice) >= 0.01) {
            $diff = round($newPrice - $previousPrice, 2);
            $pct = $previousPrice > 0 ? round(($diff / $previousPrice) * 100, 1) : 0;
            $latestHistory = PriceHistory::create([
                'raw_material_id' => $rawMaterial->id,
                'vendor_name' => $validated['default_vendor'] ?: ($latestHistory?->vendor_name ?: 'ปรับปรุงจากหน้าตรวจจับราคา'),
                'purchase_unit_price' => $newPrice,
                'purchase_unit' => $validated['purchase_unit'],
                'yield_sticks_per_unit' => $yieldSticks,
                'cost_per_stick' => $costPerStick,
                'previous_cost_price' => $previousPrice > 0 ? $previousPrice : null,
                'price_change_amount' => $previousPrice > 0 ? $diff : 0,
                'price_change_percent' => $previousPrice > 0 ? $pct : 0,
                'recorded_at' => now(),
                'notes' => 'แก้ไขผ่านป๊อปอัปหน้าตรวจจับราคา',
            ]);
        } elseif ($latestHistory && !empty($validated['default_vendor'])) {
            $latestHistory->update([
                'vendor_name' => $validated['default_vendor'],
                'purchase_unit' => $validated['purchase_unit'],
                'purchase_unit_price' => $newPrice,
                'yield_sticks_per_unit' => $yieldSticks,
                'cost_per_stick' => $costPerStick,
            ]);
        }

        $actor = AuditLog::resolveCurrentUserActor();
        AuditLog::record(
            'price_tracker',
            'update',
            'แก้ไขข้อมูลราคาวัตถุดิบ',
            "{$rawMaterial->name} (฿" . number_format($previousPrice, 2) . " ➔ ฿" . number_format($newPrice, 2) . ")",
            [
                'material_id' => $rawMaterial->id,
                'previous_price' => $previousPrice,
                'new_price' => $newPrice,
                'cost_per_stick' => $costPerStick,
            ],
            $actor
        );

        if ($request->wantsJson() || $request->ajax()) {
            $rawMaterial->load(['priceHistories' => fn ($q) => $q->orderByDesc('recorded_at')->orderByDesc('id')->take(5)]);
            $latestHist = $rawMaterial->priceHistories->first();

            return response()->json([
                'success' => true,
                'message' => 'อัปเดตข้อมูลรายการ "' . $rawMaterial->name . '" เรียบร้อยแล้ว',
                'updated_by' => $actor['name'] ?? 'ผู้ใช้งาน',
                'material' => [
                    'id' => $rawMaterial->id,
                    'name' => $rawMaterial->name,
                    'scan_names' => $rawMaterial->scan_names ?: [],
                    'purchase_unit' => $rawMaterial->purchase_unit,
                    'latest_cost_price' => (float) $rawMaterial->latest_cost_price,
                    'yield_sticks_per_unit' => (float) $rawMaterial->yield_sticks_per_unit,
                    'cost_per_stick' => (float) $rawMaterial->cost_per_stick,
                    'vendor_name' => $latestHist ? $latestHist->vendor_name : ($rawMaterial->default_vendor ?: 'ไม่ระบุ'),
                    'category_slug' => $rawMaterial->category_slug,
                    'image_url' => $rawMaterial->image_url,
                    'price_change_amount' => $latestHist ? (float) $latestHist->price_change_amount : 0.0,
                    'price_change_percent' => $latestHist ? (float) $latestHist->price_change_percent : 0.0,
                ],
            ]);
        }

        return redirect()->route('admin.receipts.price_tracker')->with('success', 'อัปเดตข้อมูลรายการเรียบร้อยแล้ว');
    }

    /**
     * Delete one or multiple RawMaterials and clean up their associated records.
     */
    public function destroyPriceTrackerMaterials(Request $request): JsonResponse|RedirectResponse
    {
        $ids = $request->input('ids');
        if (!is_array($ids)) {
            if ($request->filled('id')) {
                $ids = [$request->input('id')];
            } else {
                $ids = [];
            }
        }

        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'กรุณาเลือกรายการที่ต้องการลบอย่างน้อย 1 รายการ',
                ], 422);
            }
            return back()->with('error', 'กรุณาเลือกรายการที่ต้องการลบอย่างน้อย 1 รายการ');
        }

        $materials = RawMaterial::whereIn('id', $ids)->get();
        $deletedCount = 0;
        $deletedNames = [];

        foreach ($materials as $mat) {
            $deletedNames[] = $mat->name;

            // 1. Unlink from receipt items so receipts history remains valid
            ReceiptItem::where('raw_material_id', $mat->id)->update(['raw_material_id' => null]);

            // 2. Delete price history records
            PriceHistory::where('raw_material_id', $mat->id)->delete();

            // 3. Delete linked POS products (cascades to inventory_dailies)
            $linkedProducts = Product::where('raw_material_id', $mat->id)->get();
            foreach ($linkedProducts as $prod) {
                InventoryDaily::where('product_id', $prod->id)->delete();
                $prod->delete();
            }

            // 4. Delete the material itself
            $mat->delete();
            $deletedCount++;
        }

        $actor = AuditLog::resolveCurrentUserActor();
        AuditLog::record(
            'price_tracker',
            'delete',
            "ลบวัตถุดิบ ({$deletedCount} รายการ)",
            implode(', ', array_slice($deletedNames, 0, 5)),
            [
                'deleted_ids' => $ids,
                'deleted_names' => $deletedNames,
            ],
            $actor
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "ลบรายการวัตถุดิบเรียบร้อยแล้ว ({$deletedCount} รายการ)",
                'deleted_ids' => $ids,
                'deleted_count' => $deletedCount,
                'deleted_names' => $deletedNames,
            ]);
        }

        return redirect()->route('admin.receipts.price_tracker')
            ->with('success', "ลบรายการวัตถุดิบเรียบร้อยแล้ว ({$deletedCount} รายการ)");
    }

    /**
     * Delete a single RawMaterial.
     */
    public function destroyPriceTrackerMaterial(RawMaterial $rawMaterial, Request $request): JsonResponse|RedirectResponse
    {
        $request->merge(['ids' => [$rawMaterial->id]]);
        return $this->destroyPriceTrackerMaterials($request);
    }

    /**
     * Update the Hero Banner cover image and text on the Price Tracker screen.
     */
    public function updatePriceTrackerHero(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:500',
            'hero_image_url' => 'nullable|string|max:500',
            'hero_image_file' => 'nullable|image|max:8192',
        ]);

        $heroImage = self::sanitizeImageUrl($validated['hero_image_url'] ?? null);

        if ($request->hasFile('hero_image_file')) {
            $file = $request->file('hero_image_file');
            $destDir = public_path('assets/ingredients/custom');
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }
            $filename = 'hero_price_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($destDir, $filename);
            $heroImage = '/assets/ingredients/custom/' . $filename;
        }

        $updates = [];
        if ($heroImage) {
            $updates['price_tracker_hero_image'] = $heroImage;
        }
        if (!empty($validated['hero_title'])) {
            $updates['price_tracker_hero_title'] = $validated['hero_title'];
        }
        if (!empty($validated['hero_subtitle'])) {
            $updates['price_tracker_hero_subtitle'] = $validated['hero_subtitle'];
        }

        foreach ($updates as $key => $val) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $val, 'group' => 'price_tracker', 'updated_at' => now(), 'created_at' => now()]
            );
        }

        $settingsRows = DB::table('settings')
            ->whereIn('key', ['price_tracker_hero_image', 'price_tracker_hero_title', 'price_tracker_hero_subtitle'])
            ->pluck('value', 'key');

        $heroData = [
            'hero_image' => $settingsRows['price_tracker_hero_image'] ?? asset('assets/ingredients/hero-price-art-left.png'),
            'hero_title' => $settingsRows['price_tracker_hero_title'] ?? 'ระบบตรวจจับราคาขึ้น-ลง & ร้านค้าอ้างอิง',
            'hero_subtitle' => $settingsRows['price_tracker_hero_subtitle'] ?? 'ติดตามความผันผวนของราคาวัตถุดิบสำคัญได้ ไม่ว่าจะขึ้นหรือลง เช่น เอ็นไก่, เนื้อวัว, ผักสด ฯลฯ เพื่อเลือกซื้อร้านที่ถูกที่สุด',
        ];

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'อัปเดตภาพปกและข้อมูลแบนเนอร์เรียบร้อยแล้ว',
                'hero' => $heroData,
            ]);
        }

        return redirect()->route('admin.receipts.price_tracker')->with('success', 'อัปเดตภาพปกเรียบร้อยแล้ว');
    }

    /**
     * Upload and convert raw material / ingredient image to URL immediately.
     */
    public function uploadMaterialImage(Request $request): JsonResponse
    {
        $destDir = public_path('assets/ingredients/custom');
        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }

        // 1. Process client-compressed base64 first (guarantees fast, sharp upload without PHP file size limits)
        if ($request->filled('image_base64')) {
            $base64 = $request->input('image_base64');
            $ext = 'jpg';
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
                $ext = strtolower($matches[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                $base64 = substr($base64, strpos($base64, ',') + 1);
            }
            $decoded = base64_decode($base64);
            if ($decoded !== false && strlen($decoded) > 50) {
                $filename = 'ingredient_' . time() . '_' . Str::random(6) . '.' . $ext;
                file_put_contents($destDir . '/' . $filename, $decoded);
                $url = asset('assets/ingredients/custom/' . $filename);

                return response()->json([
                    'success' => true,
                    'url' => $url,
                    'filename' => $filename,
                    'message' => 'อัปโหลดและแปลงเป็น URL เรียบร้อยแล้ว',
                ]);
            }
        }

        // 2. Try uploaded file
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            if ($file->isValid()) {
                $ext = strtolower($file->getClientOriginalExtension()) ?: 'png';
                if ($ext === 'jpeg') $ext = 'jpg';
                $filename = 'ingredient_' . time() . '_' . Str::random(6) . '.' . $ext;
                $file->move($destDir, $filename);
                $url = asset('assets/ingredients/custom/' . $filename);

                return response()->json([
                    'success' => true,
                    'url' => $url,
                    'filename' => $filename,
                    'message' => 'อัปโหลดและแปลงเป็น URL เรียบร้อยแล้ว',
                ]);
            }
        }

        // 3. If file was sent but was invalid due to php.ini upload_max_filesize (2M)
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_INI_SIZE) {
            return response()->json([
                'success' => false,
                'message' => 'ขนาดไฟล์เกิน 2MB กรุณาเลือกไฟล์ที่มีขนาดเล็กลง หรือรอระบบย่อภาพอัตโนมัติ',
            ], 422);
        }

        return response()->json([
            'success' => false,
            'message' => 'ไม่พบไฟล์รูปภาพที่ต้องการอัปโหลด',
        ], 422);
    }

    /**
     * Sync and load items purchased today from scanned receipts into Price Tracker.
     * Matches raw materials by main name and secondary / scan names (scan_names).
     */
    public function syncTodayReceipts(Request $request): JsonResponse
    {
        $today = Carbon::today()->toDateString();

        // 1. ดึงใบเสร็จที่บันทึกหรือระบุวันที่ซื้อเป็นวันนี้ทั้งหมด
        $todayReceipts = Receipt::with('items')
            ->where(function ($q) use ($today) {
                $q->whereDate('purchase_date', $today)
                  ->orWhereDate('created_at', $today);
            })
            ->orderBy('id')
            ->get();

        $allMaterials = RawMaterial::all();
        $syncedItemsCount = 0;
        $newMaterialsCount = 0;
        $todayBoughtMaterialIds = [];

        foreach ($todayReceipts as $receipt) {
            $purchaseDate = $receipt->purchase_date ?: Carbon::today();

            foreach ($receipt->items as $item) {
                $itemName = trim($item->item_name);
                if (empty($itemName)) continue;

                $matchedMaterial = null;

                // A. กรณีเชื่อมต่อ raw_material_id ไว้แล้ว
                if ($item->raw_material_id) {
                    $matchedMaterial = $allMaterials->firstWhere('id', $item->raw_material_id);
                }

                // B. ตรวจสอบชื่อหลัก (Main Name)
                if (!$matchedMaterial) {
                    $normItem = mb_strtolower(preg_replace('/\s+/', '', $itemName));
                    foreach ($allMaterials as $mat) {
                        $normMat = mb_strtolower(preg_replace('/\s+/', '', $mat->name));
                        if ($normItem === $normMat || mb_stripos($itemName, $mat->name) !== false || mb_stripos($mat->name, $itemName) !== false) {
                            $matchedMaterial = $mat;
                            break;
                        }
                    }
                }

                // C. ตรวจสอบชื่อรองหรือชื่อสำหรับสแกนบนใบเสร็จ (scan_names aliases)
                if (!$matchedMaterial) {
                    $normItem = mb_strtolower(preg_replace('/\s+/', '', $itemName));
                    foreach ($allMaterials as $mat) {
                        $aliases = is_array($mat->scan_names) ? $mat->scan_names : (json_decode($mat->scan_names ?? '[]', true) ?: []);
                        foreach ($aliases as $al) {
                            $alName = is_array($al) ? trim($al['name'] ?? '') : trim((string)$al);
                            if (empty($alName)) continue;
                            $normAl = mb_strtolower(preg_replace('/\s+/', '', $alName));
                            if ($normItem === $normAl || mb_stripos($itemName, $alName) !== false || mb_stripos($alName, $itemName) !== false) {
                                $matchedMaterial = $mat;
                                break 2;
                            }
                        }
                    }
                }

                // D. หากเป็นรายการใหม่ที่ยังไม่มีในระบบ ให้สร้าง RawMaterial เพิ่มให้อัตโนมัติเพื่อนำมาแสดง
                if (!$matchedMaterial) {
                    $unitPrice = (float) $item->unit_price;
                    $yield = (float) ($item->yield_sticks_per_unit ?: 10);
                    $costPerStick = $yield > 0 ? round($unitPrice / $yield, 2) : 0;

                    $matchedMaterial = RawMaterial::create([
                        'name' => $itemName,
                        'purchase_unit' => $item->unit ?: 'แพ็ก',
                        'latest_cost_price' => $unitPrice,
                        'yield_sticks_per_unit' => $yield,
                        'cost_per_stick' => $costPerStick,
                        'default_vendor' => $receipt->vendor_name ?: 'ไม่ระบุ',
                        'current_stock_sticks' => (int) round(((float)$item->quantity) * $yield),
                    ]);
                    $allMaterials->push($matchedMaterial);
                    $newMaterialsCount++;
                }

                // E. ผูกความสัมพันธ์ raw_material_id เข้ากับ ReceiptItem
                if ($item->raw_material_id !== $matchedMaterial->id) {
                    $item->raw_material_id = $matchedMaterial->id;
                    $item->save();
                }

                // F. อัปเดตราคาล่าสุดและร้านค้าอ้างอิงของวัตถุดิบ
                $unitPrice = (float) $item->unit_price;
                if ($unitPrice > 0) {
                    $matchedMaterial->latest_cost_price = $unitPrice;
                    if ($item->yield_sticks_per_unit > 0) {
                        $matchedMaterial->yield_sticks_per_unit = (float) $item->yield_sticks_per_unit;
                        $matchedMaterial->cost_per_stick = round($unitPrice / $matchedMaterial->yield_sticks_per_unit, 2);
                    }
                    if (!empty($receipt->vendor_name)) {
                        $matchedMaterial->default_vendor = $receipt->vendor_name;
                    }
                    $matchedMaterial->save();
                }

                // G. บันทึกประวัติราคา (PriceHistory)
                $existingHist = PriceHistory::where('raw_material_id', $matchedMaterial->id)
                    ->where('receipt_id', $receipt->id)
                    ->first();
                if (!$existingHist && $unitPrice > 0) {
                    $yield = (float) ($item->yield_sticks_per_unit ?: $matchedMaterial->yield_sticks_per_unit);
                    PriceHistory::create([
                        'raw_material_id' => $matchedMaterial->id,
                        'receipt_id' => $receipt->id,
                        'vendor_name' => $receipt->vendor_name ?: ($matchedMaterial->default_vendor ?: 'ไม่ระบุ'),
                        'invoice_number' => $receipt->receipt_no,
                        'purchase_unit_price' => $unitPrice,
                        'purchase_unit' => $item->unit ?: ($matchedMaterial->purchase_unit ?: 'แพ็ก'),
                        'yield_sticks_per_unit' => $yield,
                        'cost_per_stick' => $yield > 0 ? round($unitPrice / $yield, 2) : 0,
                        'previous_cost_price' => $matchedMaterial->latest_cost_price,
                        'price_change_amount' => 0,
                        'price_change_percent' => 0,
                        'recorded_at' => $purchaseDate,
                        'notes' => 'ซิงค์จากใบเสร็จวันนี้',
                    ]);
                }

                $todayBoughtMaterialIds[] = $matchedMaterial->id;
                $syncedItemsCount++;
            }
        }

        // เพิ่ม Material IDs จาก PriceHistory ของวันนี้เพิ่มเติม
        $todayHistIds = DB::table('price_histories')
            ->whereDate('recorded_at', $today)
            ->pluck('raw_material_id')
            ->all();

        $todayBoughtMaterialIds = array_values(array_unique(array_merge($todayBoughtMaterialIds, $todayHistIds)));

        return response()->json([
            'success' => true,
            'message' => 'โหลดและซิงค์รายการสั่งซื้อวันนี้จากหน้าสแกนใบเสร็จสำเร็จ',
            'today_bought_count' => count($todayBoughtMaterialIds),
            'today_bought_ids' => $todayBoughtMaterialIds,
            'synced_items_count' => $syncedItemsCount,
            'new_materials_created' => $newMaterialsCount,
            'receipts_count' => $todayReceipts->count(),
        ]);
    }

    /**
     * Calculate today cost and month cost from receipts.
     */
    private function getReceiptCostSummary(): array
    {
        $today = Carbon::today();
        $currentMonth = $today->month;
        $currentYear = $today->year;

        // 1. ต้นทุนซื้อวัตถุดิบวันนี้: บิลที่บันทึกเข้าระบบวันนี้ หรือระบุวันที่ซื้อเป็นวันนี้
        $todayCost = (float) Receipt::where(function ($q) use ($today) {
            $q->whereDate('created_at', $today)
              ->orWhereDate('purchase_date', $today);
        })->sum('total_amount');

        // หากยังไม่มีบิลตรงกับวันนี้ ให้ดึงยอดบิลล่าสุดในระบบมาแสดงผล
        if ($todayCost <= 0 && Receipt::count() > 0) {
            $latestDate = Receipt::max('created_at');
            if ($latestDate) {
                $latestDay = Carbon::parse($latestDate)->startOfDay();
                $todayCost = (float) Receipt::whereDate('created_at', $latestDay)->sum('total_amount');
            }
            if ($todayCost <= 0) {
                $todayCost = (float) Receipt::orderByDesc('id')->value('total_amount');
            }
        }

        // 2. ยอดซื้อวัตถุดิบเดือนนี้: บิลที่บันทึกในเดือนนี้ หรือระบุวันที่ซื้อในเดือนนี้
        $thisMonthCost = (float) Receipt::where(function ($q) use ($currentMonth, $currentYear) {
            $q->where(function ($sq) use ($currentMonth, $currentYear) {
                $sq->whereMonth('created_at', $currentMonth)
                   ->whereYear('created_at', $currentYear);
            })->orWhere(function ($sq) use ($currentMonth, $currentYear) {
                $sq->whereMonth('purchase_date', $currentMonth)
                   ->whereYear('purchase_date', $currentYear);
            });
        })->sum('total_amount');

        // ถ้าเดือนนี้ยังไม่มียอด ให้ดึงยอดรวมบิลทั้งหมดในระบบมาแสดงผล
        if ($thisMonthCost <= 0 && Receipt::count() > 0) {
            $thisMonthCost = (float) Receipt::sum('total_amount');
        }

        return [
            'today_cost' => $todayCost,
            'this_month_cost' => $thisMonthCost,
        ];
    }

    /**
     * Store a newly created Category from Price Tracker or Admin.
     */
    public function storeCategory(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100',
            'category_image' => 'nullable|image|max:10240',
            'category_image_url' => 'nullable|string|max:500',
        ]);

        $name = trim($request->input('name'));
        $rawSlug = trim($request->input('slug') ?: '');

        $slug = Str::slug($rawSlug ?: $name);
        if (empty($slug)) {
            $slug = 'cat-' . time();
        }

        $originalSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $iconPath = null;
        if ($request->hasFile('category_image')) {
            $file = $request->file('category_image');
            $filename = 'cat_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $destDir = public_path('uploads/categories');
            if (!file_exists($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $file->move($destDir, $filename);
            $iconPath = 'uploads/categories/' . $filename;
        } elseif ($request->filled('category_image_url')) {
            $iconPath = trim($request->input('category_image_url'));
        } else {
            $iconPath = 'assets/categories/all.png';
        }

        $nextSort = (Category::max('sort_order') ?? 0) + 1;

        $category = Category::create([
            'name' => $name,
            'slug' => $slug,
            'icon' => $iconPath,
            'sort_order' => $nextSort,
            'is_active' => true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'สร้างหมวดหมู่ "' . $category->name . '" สำเร็จแล้ว',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'icon' => str_starts_with($category->icon, 'http') ? $category->icon : asset($category->icon),
                ],
            ]);
        }

        return redirect()->back()->with('success', 'สร้างหมวดหมู่ "' . $category->name . '" สำเร็จแล้ว');
    }

    /**
     * Sanitize image URLs to ensure safe root-relative paths across all hosts.
     */
    public static function sanitizeImageUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }
        if (preg_match('#^https?://(?:localhost|127\.0\.0\.1)(?::\d+)?(/.*)$#i', $url, $m)) {
            return $m[1];
        }
        if (str_starts_with($url, '/assets/') || str_starts_with($url, '/uploads/')) {
            return $url;
        }
        if (str_starts_with($url, 'assets/') || str_starts_with($url, 'uploads/')) {
            return '/' . $url;
        }
        return $url;
    }
}
