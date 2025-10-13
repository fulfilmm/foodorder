<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'status',
        'qr_path',
    ];
    // All orders for this table
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Latest order created today for this table
    public function latestOrderToday()
    {
        return $this->hasOne(Order::class)
            ->latestOfMany() // requires Laravel 8+
            ->whereDate('created_at', now()->toDateString());
    }

    // Derived availability for UI
    public function getAvailabilityStatusAttribute(): string
    {
        $order = $this->latestOrderToday; // make sure to eager-load
        if (!$order) return 'available';

        $status = strtolower($order->status); // adjust column name if needed
        return in_array($status, ['done', 'cancel']) ? 'available' : 'unavailable';
    }
}
