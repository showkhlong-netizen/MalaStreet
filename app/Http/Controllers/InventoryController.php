<?php

namespace App\Http\Controllers;

use App\Models\InventoryDaily;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Display Skewer Stock Matrix and Daily Reconciliation.
     */
    public function index(Request $request): View|JsonResponse
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $date = Carbon::parse($selectedDate)->startOfDay();
        $yesterday = $date->copy()->subDay();

        $products = Product::with('category')->where('is_available', true)->orderBy('sort_order')->get();

        $inventoryRecords = [];
        $totalOpeningSticks = 0;
        $totalAddedSticks = 0;
        $totalSoldSticks = 0;
        $totalWasteSticks = 0;
        $totalExpectedSticks = 0;
        $totalActualSticks = 0;

        foreach ($products as $product) {
            $record = InventoryDaily::whereDate('record_date', $date)->where('product_id', $product->id)->first();

            if (!$record) {
                $yesterdayRecord = InventoryDaily::whereDate('record_date', $yesterday)->where('product_id', $product->id)->first();
                $opening = $yesterdayRecord ? ($yesterdayRecord->actual_closing_sticks ?? $yesterdayRecord->expected_closing_sticks) : $product->stock_sticks;

                $record = InventoryDaily::create([
                    'record_date' => $date->toDateString(),
                    'product_id' => $product->id,
                    'opening_sticks' => $opening,
                    'added_sticks' => 0,
                    'sold_sticks' => 0,
                    'waste_sticks' => 0,
                    'expected_closing_sticks' => $opening,
                ]);
            }

            $inventoryRecords[] = [
                'product' => $product,
                'record' => $record,
            ];

            $totalOpeningSticks += $record->opening_sticks;
            $totalAddedSticks += $record->added_sticks;
            $totalSoldSticks += $record->sold_sticks;
            $totalWasteSticks += $record->waste_sticks;
            $totalExpectedSticks += $record->expected_closing_sticks;
            if ($record->actual_closing_sticks !== null) {
                $totalActualSticks += $record->actual_closing_sticks;
            }
        }

        if ($request->wantsJson() || $request->boolean('json')) {
            return response()->json([
                'success' => true,
                'selected_date' => $date->toDateString(),
                'formatted_date' => $date->format('d/m/Y'),
                'summary' => [
                    'opening_sticks' => (int) $totalOpeningSticks,
                    'added_sticks' => (int) $totalAddedSticks,
                    'sold_sticks' => (int) $totalSoldSticks,
                    'waste_sticks' => (int) $totalWasteSticks,
                    'expected_sticks' => (int) $totalExpectedSticks,
                    'actual_sticks' => (int) $totalActualSticks,
                ],
                'records' => collect($inventoryRecords)->map(function ($row) {
                    $p = $row['product'];
                    $r = $row['record'];
                    $variance = $r->actual_closing_sticks !== null ? ((int) $r->actual_closing_sticks - (int) $r->expected_closing_sticks) : null;
                    return [
                        'product_id' => $p->id,
                        'product_name' => $p->name,
                        'category_name' => $p->category?->name ?? '-',
                        'price_per_stick' => (float) $p->price_per_stick,
                        'stock_sticks' => (int) $p->stock_sticks,
                        'opening_sticks' => (int) $r->opening_sticks,
                        'added_sticks' => (int) $r->added_sticks,
                        'sold_sticks' => (int) $r->sold_sticks,
                        'waste_sticks' => (int) $r->waste_sticks,
                        'expected_closing_sticks' => (int) $r->expected_closing_sticks,
                        'actual_closing_sticks' => $r->actual_closing_sticks !== null ? (int) $r->actual_closing_sticks : null,
                        'variance' => $variance,
                        'notes' => $r->notes,
                    ];
                })->values(),
            ]);
        }

        return view('admin.inventory.index', compact(
            'inventoryRecords',
            'selectedDate',
            'totalOpeningSticks',
            'totalAddedSticks',
            'totalSoldSticks',
            'totalWasteSticks',
            'totalExpectedSticks',
            'totalActualSticks'
        ));
    }

    /**
     * Multi-item or single-item stock adjustment (เสียบเพิ่ม / ตัดของเสีย) with optional evidence photos.
     */
    public function adjust(Request $request): RedirectResponse|JsonResponse
    {
        // Support both multi-item array ('items') and legacy single-item ('product_id', 'quantity')
        $isMultiItem = $request->has('items') && is_array($request->input('items'));

        if ($isMultiItem) {
            $validated = $request->validate([
                'date' => 'required|date',
                'action' => 'required|in:add,waste',
                'notes' => 'nullable|string|max:500',
                'evidence_images' => 'nullable|array|max:10',
                'evidence_images.*' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.note' => 'nullable|string|max:255',
            ]);
            $itemsToProcess = $validated['items'];
            $globalNote = $validated['notes'] ?? null;
        } else {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'date' => 'required|date',
                'action' => 'required|in:add,waste',
                'quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string|max:255',
            ]);
            $itemsToProcess = [[
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'note' => $validated['notes'] ?? null,
            ]];
            $globalNote = $validated['notes'] ?? null;
        }

        $date = Carbon::parse($validated['date'])->startOfDay();
        $action = $validated['action'];

        // Save any attached base64 evidence photos to public/uploads/inventory_evidence
        $savedEvidencePaths = [];
        $evidenceImages = $request->input('evidence_images', []);
        if (is_array($evidenceImages) && count($evidenceImages) > 0) {
            $targetDir = public_path('uploads/inventory_evidence');
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            foreach ($evidenceImages as $base64Img) {
                if (!is_string($base64Img) || empty($base64Img)) {
                    continue;
                }
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Img, $matches)) {
                    $ext = strtolower($matches[1]);
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                        $ext = 'jpg';
                    }
                    $rawBase64 = substr($base64Img, strpos($base64Img, ',') + 1);
                    $decoded = base64_decode($rawBase64);
                    if ($decoded !== false) {
                        $fileName = 'inv_' . date('Ymd_His') . '_' . Str::random(5) . '.' . $ext;
                        file_put_contents($targetDir . '/' . $fileName, $decoded);
                        $savedEvidencePaths[] = 'uploads/inventory_evidence/' . $fileName;
                    }
                }
            }
        }

        $totalAdjustedSticks = 0;
        $updatedProductNames = [];

        DB::transaction(function () use ($itemsToProcess, $date, $action, $globalNote, $savedEvidencePaths, &$totalAdjustedSticks, &$updatedProductNames) {
            // Consolidate duplicate product_ids in the same request so stock is never double-corrupted
            $grouped = [];
            foreach ($itemsToProcess as $row) {
                $pid = (int) $row['product_id'];
                $q = max(1, (int) $row['quantity']);
                $rNote = trim((string) ($row['note'] ?? ''));
                if (!isset($grouped[$pid])) {
                    $grouped[$pid] = ['quantity' => 0, 'notes' => []];
                }
                $grouped[$pid]['quantity'] += $q;
                if ($rNote !== '') {
                    $grouped[$pid]['notes'][] = $rNote;
                }
            }

            foreach ($grouped as $pid => $info) {
                $product = Product::lockForUpdate()->findOrFail($pid);
                $qty = $info['quantity'];

                $record = InventoryDaily::whereDate('record_date', $date)->where('product_id', $product->id)->first();
                if (!$record) {
                    $record = InventoryDaily::create([
                        'record_date' => $date->toDateString(),
                        'product_id' => $product->id,
                        'opening_sticks' => $product->stock_sticks,
                        'added_sticks' => 0,
                        'sold_sticks' => 0,
                        'waste_sticks' => 0,
                        'expected_closing_sticks' => $product->stock_sticks,
                    ]);
                }

                if ($action === 'add') {
                    $record->increment('added_sticks', $qty);
                    $product->increment('stock_sticks', $qty);
                } else {
                    $record->increment('waste_sticks', $qty);
                    $product->decrement('stock_sticks', min($qty, $product->stock_sticks));
                }

                $record->expected_closing_sticks = max(0, ($record->opening_sticks + $record->added_sticks) - $record->sold_sticks - $record->waste_sticks);

                $noteFragments = [];
                if ($globalNote) {
                    $noteFragments[] = $globalNote;
                }
                if (!empty($info['notes'])) {
                    $noteFragments[] = implode(', ', $info['notes']);
                }
                if (!empty($savedEvidencePaths)) {
                    $noteFragments[] = 'รูปหลักฐาน: ' . count($savedEvidencePaths) . ' รูป';
                }

                if (!empty($noteFragments)) {
                    $entryText = '[' . ($action === 'add' ? "+{$qty}" : "-{$qty} เสีย") . '] ' . implode(' - ', $noteFragments);
                    $record->notes = trim(($record->notes ? $record->notes . ' | ' : '') . $entryText, ' |');
                }

                $record->save();

                $totalAdjustedSticks += $qty;
                $updatedProductNames[] = $product->name;
            }
        });

        $actionLabel = $action === 'add' ? 'เสียบเพิ่มเข้าสต็อก' : 'ตัดของเสียออกจากสต็อก';
        $msg = "บันทึก{$actionLabel} จำนวน " . count($updatedProductNames) . " รายการ (รวม {$totalAdjustedSticks} ไม้) เรียบร้อยแล้ว";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'action' => $action,
                'total_adjusted_sticks' => $totalAdjustedSticks,
                'evidence_urls' => array_map(fn ($p) => asset($p), $savedEvidencePaths),
            ]);
        }

        return redirect()->route('admin.inventory.index', ['date' => $validated['date']])
            ->with('success', $msg);
    }

    /**
     * Save actual end-of-day stock count (Daily Stock Reconciliation).
     */
    public function reconcile(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'actual_counts' => 'required|array',
            'actual_counts.*' => 'nullable|integer|min:0',
        ]);

        $date = Carbon::parse($validated['date'])->startOfDay();
        $nextDay = $date->copy()->addDay();
        $updatedCount = 0;

        DB::transaction(function () use ($validated, $date, $nextDay, &$updatedCount) {
            foreach ($validated['actual_counts'] as $productId => $actual) {
                if ($actual !== null && $actual !== '') {
                    $actualInt = max(0, (int) $actual);
                    $record = InventoryDaily::whereDate('record_date', $date)->where('product_id', $productId)->first();
                    if ($record) {
                        $record->actual_closing_sticks = $actualInt;
                        $record->save();

                        $product = Product::find($productId);
                        if ($product) {
                            $product->stock_sticks = $actualInt;
                            $product->save();
                        }

                        // If next day's record was already initialized, sync its opening_sticks
                        $nextRecord = InventoryDaily::whereDate('record_date', $nextDay)->where('product_id', $productId)->first();
                        if ($nextRecord) {
                            $nextRecord->opening_sticks = $actualInt;
                            $nextRecord->expected_closing_sticks = max(0, ($actualInt + $nextRecord->added_sticks) - $nextRecord->sold_sticks - $nextRecord->waste_sticks);
                            $nextRecord->save();
                        }

                        $updatedCount++;
                    }
                }
            }
        });

        $msg = "บันทึกยอดตรวจนับจริงสิ้นวัน ({$updatedCount} รายการ) เรียบร้อยแล้ว ยอดนี้จะยกยอดไปวันถัดไปโดยอัตโนมัติ";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'updated_count' => $updatedCount,
            ]);
        }

        return redirect()->route('admin.inventory.index', ['date' => $validated['date']])
            ->with('success', $msg);
    }
}
