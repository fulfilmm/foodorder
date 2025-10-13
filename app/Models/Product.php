<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Include discount fields + actual_price + price
    protected $fillable = [
        'code',
        'name',
        'actual_price',   // base price
        'price',          // final selling price (auto-computed from discount)
        'qty',
        'remain_qty',
        'sell_qty',
        'image',
        'description',
        'category_id',
        'has_discount',
        'discount_type',  // 'percent' | 'fixed'
        'discount_value', // percent (0–100) or MMK amount
    ];

    protected $casts = [
        'actual_price'   => 'integer',
        'price'          => 'integer',
        'qty'            => 'integer',
        'remain_qty'     => 'integer',
        'sell_qty'       => 'integer',
        'has_discount'   => 'boolean',
        'discount_value' => 'integer',
    ];

    // Expose computed values automatically
    protected $appends = ['discount_amount', 'total_price'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** MMK discount amount derived from actual_price */
    public function getDiscountAmountAttribute(): int
    {
        if (!$this->has_discount || $this->discount_value === null) {
            return 0;
        }

        if ($this->discount_type === 'percent') {
            $p = max(0, min(100, (int) $this->discount_value));
            return (int) floor(((int) $this->actual_price) * $p / 100);
        }

        if ($this->discount_type === 'fixed') {
            $fixed = max(0, (int) $this->discount_value);
            return (int) min((int) $this->actual_price, $fixed);
        }

        return 0;
    }

    /** Final price (no tax): actual_price - discount_amount */
    public function getTotalPriceAttribute(): int
    {
        return max(0, ((int) $this->actual_price) - $this->discount_amount);
    }

    /**
     * Keep stored `price` synced automatically unless you explicitly set it.
     * Fires on create/update via Eloquent (not raw DB updates).
     */
    protected static function booted(): void
    {
        static::saving(function (Product $p) {
            if ($p->isDirty(['actual_price', 'has_discount', 'discount_type', 'discount_value'])
                && !$p->isDirty('price')) {
                $base = (int) $p->actual_price;
                $disc = (int) $p->discount_amount;
                $p->price = max(0, $base - $disc);
            }
        });
    }
}
