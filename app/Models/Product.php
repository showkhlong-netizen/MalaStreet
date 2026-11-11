<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'price_per_stick' => 'decimal:2',
        'stock_sticks' => 'integer',
        'is_available' => 'boolean',
        'supports_grill' => 'boolean',
        'supports_soup' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryDailies(): HasMany
    {
        return $this->hasMany(InventoryDaily::class)->orderByDesc('record_date');
    }

    /**
     * Get display name, automatically synced with RawMaterial if linked.
     */
    public function getDisplayNameAttribute(): string
    {
        return !empty($this->name) ? $this->name : ($this->rawMaterial?->name ?? '');
    }

    /**
     * Get display image URL, automatically synced with RawMaterial from Price Tracker.
     */
    public function getDisplayImageAttribute(): string
    {
        $rawImg = $this->rawMaterial?->image_url;
        if (!empty($rawImg) && !str_contains($rawImg, 'beef-premium.png')) {
            $path = preg_replace('#^https?://(?:localhost|127\.0\.0\.1)(?::\d+)?/#', '', $rawImg);
            return str_starts_with($path, 'http') ? $path : asset(ltrim($path, '/'));
        }

        $itemName = $this->rawMaterial?->name ?: $this->name;
        if (!empty($itemName)) {
            if (str_contains($itemName, 'เนื้อ') || str_contains($itemName, 'โคขุน')) {
                return asset('assets/ingredients/beef-ribeye-sliced.png');
            } elseif (str_contains($itemName, 'สันใน')) {
                return asset('assets/ingredients/beef-ribeye-sliced.png');
            } elseif (str_contains($itemName, 'หมู') || str_contains($itemName, 'สามชั้น') || str_contains($itemName, 'เบาว์ราช')) {
                return asset('assets/ingredients/pork-belly-sliced.png');
            } elseif (str_contains($itemName, 'เอ็นไก่') || str_contains($itemName, 'ไก่ห่อ') || str_contains($itemName, 'ไก่ย่าง') || str_contains($itemName, 'เทอร์โบ') || str_contains($itemName, 'ดลไก่')) {
                return asset('assets/ingredients/chicken-tendon.png');
            } elseif (str_contains($itemName, 'ไส้กรอก') || str_contains($itemName, 'โบโลน่า') || str_contains($itemName, 'ค็อกเทล') || str_contains($itemName, 'ไก่ยอ') || str_contains($itemName, 'แหนม')) {
                return asset('assets/ingredients/sausage-smoked.png');
            } elseif (str_contains($itemName, 'ลูกชิ้น') || str_contains($itemName, 'ปูอัด') || str_contains($itemName, 'ปูไลน์') || str_contains($itemName, 'ทูนิมิ') || str_contains($itemName, 'เกี๊ยว')) {
                return asset('assets/ingredients/fishball-phuket.png');
            } elseif (str_contains($itemName, 'เต้าหู้') || str_contains($itemName, 'ฟองเต้าหู้') || str_contains($itemName, 'ชีส') || str_contains($itemName, 'ไข่')) {
                return asset('assets/ingredients/tofu-fried-roll.png');
            } elseif (str_contains($itemName, 'เห็ด')) {
                return asset('assets/ingredients/mushroom-fresh.png');
            } elseif (str_contains($itemName, 'ผัก')) {
                return asset('assets/ingredients/vegetable-greens.png');
            }
        }

        if (!empty($this->image_url) && !str_contains($this->image_url, 'beef-premium.png')) {
            $path = preg_replace('#^https?://(?:localhost|127\.0\.0\.1)(?::\d+)?/#', '', $this->image_url);
            return str_starts_with($path, 'http') ? $path : asset(ltrim($path, '/'));
        }

        return asset('assets/menu/food-fallback.svg');
    }
}
