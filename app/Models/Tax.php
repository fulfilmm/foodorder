<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $fillable = ['name','percent','is_active','is_default','description'];

    protected $casts = [
        'percent'    => 'decimal:2',
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
    ];

    public function scopeActive($q){ return $q->where('is_active', true); }
}
