<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bot',
        'telegram_id',
        'telegram_username',
        'telegram_first_name',
        'linked_at',
    ];

    protected $casts = [
        'telegram_id' => 'integer',
        'linked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}