<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends MainModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'delivery_mode',
        'manual_notice',
    ];

    protected $casts = [
        'price' => 'integer',
    ];

    public function isManual(): bool
    {
        return $this->delivery_mode === 'manual';
    }
}