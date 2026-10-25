<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class TelegramUser extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'telegram_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'telegram_id',
        'referrer_id',
        'username',
        'email',
        'first_name',
        'last_name',
        'balance',
        'is_reseller',
        'is_blocked',
        'last_spin_at',
        'total_spins',
        'total_spin_won',
        'referral_count',
        'referral_earnings',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'telegram_id' => 'integer',
            'balance' => 'decimal:2',
            'is_reseller' => 'boolean',
            'is_blocked' => 'boolean',
            'last_spin_at' => 'datetime',
            'total_spins' => 'integer',
            'total_spin_won' => 'decimal:2',
            'referral_count' => 'integer',
            'referral_earnings' => 'decimal:2',
        ];
    }

    /**
     * Check if user is eligible for the daily spin today (24h cooldown).
     */
    public function canSpinToday(): bool
    {
        if (!$this->last_spin_at) {
            return true;
        }
        return $this->last_spin_at->addHours(24)->isPast();
    }

    /**
     * Get remaining seconds until next available spin.
     */
    public function secondsUntilNextSpin(): int
    {
        if (!$this->last_spin_at) {
            return 0;
        }
        $next = $this->last_spin_at->copy()->addHours(24);
        return max(0, (int) now()->diffInSeconds($next, false));
    }

    /**
     * Get formatted string for next spin timer (e.g., "14h 23m").
     */
    public function formattedTimeUntilNextSpin(): string
    {
        $seconds = $this->secondsUntilNextSpin();
        if ($seconds <= 0) {
            return 'Now';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02dh %02dm %02ds', $hours, $minutes, $secs);
    }

    /**
     * Get the user who referred this user.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'referrer_id');
    }

    /**
     * Get all users referred by this user.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    /**
     * Get all spins played by this user.
     */
    public function spins(): HasMany
    {
        return $this->hasMany(Spin::class, 'telegram_user_id');
    }

    /**
     * Get the transactions for the user.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'telegram_user_id');
    }

    /**
     * Get the purchased keys for the user.
     */
    public function keys(): HasMany
    {
        return $this->hasMany(Key::class, 'telegram_user_id');
    }

    /**
     * Get VIP custom prices for this reseller / user.
     */
    public function customPrices(): HasMany
    {
        return $this->hasMany(UserProductPrice::class, 'telegram_user_id');
    }
}
