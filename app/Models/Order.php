<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
// use App\Models\OrderItem;
// use Illuminate\Support\Carbon;
// use App\Models\User;
// use App\Models\Table;
// use App\Models\OrderStatusHistory;




// class Order extends Model
// {
//     use HasFactory;
//     protected $fillable = ['user_id', 'order_no','phone', 'pickup_date', 'pickup_time','order_type','table_id', 'status', 'total','parent_order_id','has_add_on','has_comment'];
//     const STATUS_PREPARING = 'preparing';
//     const STATUS_PENDING = 'pending';
//     const STATUS_CONFIRMED = 'confirmed';
//     const STATUS_DELIVERED = 'delivered';
//     const STATUS_EATING = 'eating';
//     const STATUS_DONE = 'done';
//     const STATUS_CANCELED = 'canceled';

//     public static function getStatuses()
//     {
//         return [
//             self::STATUS_PREPARING,
//             self::STATUS_PENDING,
//             self::STATUS_CONFIRMED,
//             self::STATUS_DELIVERED,
//             self::STATUS_EATING,
//             self::STATUS_DONE,
//             self::STATUS_CANCELED,
//         ];
//     }

//     public function items() {
//         return $this->hasMany(OrderItem::class);
//     }

//     public function updateStatus(string $newStatus): void
// {
//     $this->status = $newStatus;

//     $timestamps = $this->status_timestamps ?? [];
//     $timestamps[$newStatus] = now()->toDateTimeString();

//     $this->status_timestamps = $timestamps;
//     $this->save();

//     // Optional: Fire event for broadcasting
//     event(new \App\Events\OrderStatusUpdated($this));
// }
// public function customer()
// {
//     return $this->belongsTo(User::class, 'user_id');
// }
// public function statusHistory()
// {
//     return $this->hasMany(OrderStatusHistory::class);
// }
// public function getStatusTimestampsAttribute()
// {
//     return $this->statusHistory()
//         ->get()
//         ->pluck('created_at', 'status')
//         ->map(fn($date) => $date->toDateTimeString())
//         ->toArray();
// }
// public function table()
// {
//     return $this->belongsTo(Table::class, 'table_id');
// }
// }



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Models\Table;
use App\Models\Tax;

class Order extends Model
{
    use HasFactory;

    // === Status constants ===
    const STATUS_PREPARING = 'preparing';
    const STATUS_PENDING   = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_EATING    = 'eating';
    const STATUS_DONE      = 'done';
    const STATUS_CANCELED  = 'canceled';

    protected $fillable = [
        'user_id',
        'order_no',
        'phone',
        'pickup_date',
        'pickup_time',
        'order_type',
        'table_id',
        'status',

        // pricing
        'subtotal',
        'tax_id',
        'tax_name_snapshot',
        'tax_percent_snapshot',
        'tax_amount',
        'total',

        // flags/links
        'parent_order_id',
        'has_add_on',
        'has_comment',
    ];

    protected $casts = [
        'pickup_date'          => 'date',
        'subtotal'             => 'integer',
        'tax_amount'           => 'integer',
        'total'                => 'integer',
        'tax_percent_snapshot' => 'decimal:2',
    ];

    // === Relations ===
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    // === Status helpers ===
    public static function getStatuses()
    {
        return [
            self::STATUS_PREPARING,
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_DELIVERED,
            self::STATUS_EATING,
            self::STATUS_DONE,
            self::STATUS_CANCELED,
        ];
    }

    /**
     * Update status and append to history.
     */
    public function updateStatus(string $newStatus): void
    {
        $this->status = $newStatus;
        $this->save();

        // Persist history row (source of truth for timestamps)
        $this->statusHistory()->create([
            'status'     => $newStatus,
            'changed_at' => now(),
        ]);

        // Broadcast
        event(new \App\Events\OrderStatusUpdated($this));
    }

    /**
     * Compute a status => timestamp array from history.
     * (Read-only derived attribute for your UI.)
     */
    public function getStatusTimestampsAttribute(): array
    {
        return $this->statusHistory()
            ->get()
            ->pluck('created_at', 'status')
            ->map(fn($date) => $date->toDateTimeString())
            ->toArray();
    }

    // === Pricing helpers ===

    /**
     * Build pricing (subtotal/tax/total) and snapshot tax for this order.
     * $cart is the same structure you store in session:
     *   [ productId => ['name'=>..., 'price'=>int(unit final), 'qty'=>int, 'comment'=>?string], ... ]
     */
    public function setPricingFromCart(array $cart, ?Tax $tax = null): self
    {
        // subtotal from cart using unit FINAL prices
        $subtotal = array_sum(array_map(
            fn($it) => (int) $it['price'] * (int) $it['qty'],
            $cart
        ));

        // Resolve tax snapshot
        $percent = $tax?->percent ? (float) $tax->percent : 0.0;
        $taxAmount = (int) floor($subtotal * ($percent / 100));
        $total = $subtotal + $taxAmount;

        // Snapshot fields make old orders stable even if tax changes later
        $this->subtotal             = $subtotal;
        $this->tax_id               = $tax?->id;
        $this->tax_name_snapshot    = $tax?->name;
        $this->tax_percent_snapshot = $tax?->percent;
        $this->tax_amount           = $taxAmount;
        $this->total                = $total;

        return $this;
    }
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_order_id');
    }
    public function children()
    {
        return $this->hasMany(self::class, 'parent_order_id');
    }
}
