<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends MainModel
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'status_id',
        'price',
        'product_id',
        'type_id',
        'user_id',
        'uid',
        'game_id',
        'email',
        'amount',
        'uc_amount',
        'payment_status',
        'completed_at',
        'external_order_id',
        'external_status',
    ];

    protected $casts = [
        'price' => 'integer',
        'amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'completed';
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'payment_status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
