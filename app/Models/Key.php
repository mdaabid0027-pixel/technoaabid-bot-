<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Key extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'keys';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'duration',
        'license_key',
        'is_sold',
        'telegram_user_id',
        'sold_at',
        'expires_at',
        'renewal_alert_sent_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_sold' => 'boolean',
            'sold_at' => 'datetime',
            'expires_at' => 'datetime',
            'renewal_alert_sent_at' => 'datetime',
        ];
    }

    /**
     * Calculate expiry date from duration string.
     */
    public static function calculateExpiryFromDuration(string $duration, $from = null): Carbon
    {
        $base = $from ? Carbon::parse($from) : Carbon::now();
        $d = strtolower(trim($duration));

        if (str_contains($d, 'lifetime') || str_contains($d, 'life time')) {
            return $base->copy()->addYears(10);
        }

        if (str_contains($d, '1d') || str_contains($d, '1 day') || $d === '1') {
            return $base->copy()->addDays(1);
        }

        if (str_contains($d, '3d') || str_contains($d, '3 day') || $d === '3') {
            return $base->copy()->addDays(3);
        }

        if (str_contains($d, '7d') || str_contains($d, '7 day') || str_contains($d, '1 week') || $d === '7') {
            return $base->copy()->addDays(7);
        }

        if (str_contains($d, '10d') || str_contains($d, '10 day') || $d === '10') {
            return $base->copy()->addDays(10);
        }

        if (str_contains($d, '15d') || str_contains($d, '15 day') || $d === '15') {
            return $base->copy()->addDays(15);
        }

        if (str_contains($d, '30d') || str_contains($d, '30 day') || str_contains($d, '1 month') || $d === '30') {
            return $base->copy()->addDays(30);
        }

        if (str_contains($d, '90d') || str_contains($d, '90 day') || str_contains($d, '3 month') || $d === '90') {
            return $base->copy()->addDays(90);
        }

        // Default 30 days
        return $base->copy()->addDays(30);
    }

    /**
     * Check if key has already expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if key is expiring within next N hours.
     */
    public function isExpiringSoon(int $hours = 6): bool
    {
        if (!$this->expires_at || $this->isExpired()) {
            return false;
        }

        return $this->expires_at->diffInHours(now(), false) <= $hours;
    }

    /**
     * Formatted string of remaining time until expiry.
     */
    public function formattedTimeRemaining(): string
    {
        if (!$this->expires_at) {
            return 'Active';
        }

        if ($this->isExpired()) {
            return 'Expired';
        }

        $now = now();
        $totalMinutes = (int) $now->diffInMinutes($this->expires_at, false);

        if ($totalMinutes <= 0) {
            return 'Expiring now';
        }

        $days = intdiv($totalMinutes, 1440);
        $hours = intdiv($totalMinutes % 1440, 60);
        $minutes = $totalMinutes % 60;

        if ($days > 0) {
            return "{$days}d {$hours}h remaining";
        }

        if ($hours > 0) {
            return "{$hours}h {$minutes}m remaining";
        }

        return "{$minutes}m remaining";
    }

    /**
     * Get the product that owns the key.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the reseller who purchased the key.
     */
    public function telegramUser(): BelongsTo
    {
        return $this->belongsTo(TelegramUser::class, 'telegram_user_id');
    }
}
