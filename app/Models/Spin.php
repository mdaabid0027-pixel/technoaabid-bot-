<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spin extends Model
{
    protected $table = 'spins';

    protected $fillable = [
        'telegram_user_id',
        'amount_won',
        'reward_label',
    ];

    protected function casts(): array
    {
        return [
            'amount_won' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id');
    }
}
