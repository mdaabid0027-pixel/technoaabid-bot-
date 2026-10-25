<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'category_id',
        'game_name',
        'image',
        'buying_price',
        'is_active',
        'price_1d',
        'cost_1d',
        'price_3d',
        'cost_3d',
        'price_3d_queue',
        'cost_3d_queue',
        'price_7d',
        'cost_7d',
        'price_7d_queue',
        'cost_7d_queue',
        'price_10d',
        'cost_10d',
        'price_15d',
        'cost_15d',
        'price_30d',
        'cost_30d',
        'price_30d_queue',
        'cost_30d_queue',
        'price_90d',
        'cost_90d',
        'price_90d_queue',
        'cost_90d_queue',
        'price_lifetime',
        'cost_lifetime',
        'reseller_price_1d',
        'reseller_price_3d',
        'reseller_price_3d_queue',
        'reseller_price_7d',
        'reseller_price_7d_queue',
        'reseller_price_10d',
        'reseller_price_15d',
        'reseller_price_30d',
        'reseller_price_30d_queue',
        'reseller_price_90d',
        'reseller_price_90d_queue',
        'reseller_price_lifetime',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_1d'                 => 'decimal:2',
            'cost_1d'                  => 'decimal:2',
            'reseller_price_1d'        => 'decimal:2',
            'price_3d'                 => 'decimal:2',
            'cost_3d'                  => 'decimal:2',
            'reseller_price_3d'        => 'decimal:2',
            'price_3d_queue'           => 'decimal:2',
            'cost_3d_queue'            => 'decimal:2',
            'reseller_price_3d_queue'  => 'decimal:2',
            'price_7d'                 => 'decimal:2',
            'cost_7d'                  => 'decimal:2',
            'reseller_price_7d'        => 'decimal:2',
            'price_7d_queue'           => 'decimal:2',
            'cost_7d_queue'            => 'decimal:2',
            'reseller_price_7d_queue'  => 'decimal:2',
            'price_10d'                => 'decimal:2',
            'cost_10d'                 => 'decimal:2',
            'reseller_price_10d'       => 'decimal:2',
            'price_15d'                => 'decimal:2',
            'cost_15d'                 => 'decimal:2',
            'reseller_price_15d'       => 'decimal:2',
            'price_30d'                => 'decimal:2',
            'cost_30d'                 => 'decimal:2',
            'reseller_price_30d'       => 'decimal:2',
            'price_30d_queue'          => 'decimal:2',
            'cost_30d_queue'           => 'decimal:2',
            'reseller_price_30d_queue' => 'decimal:2',
            'price_90d'                => 'decimal:2',
            'cost_90d'                 => 'decimal:2',
            'reseller_price_90d'       => 'decimal:2',
            'price_90d_queue'          => 'decimal:2',
            'cost_90d_queue'           => 'decimal:2',
            'reseller_price_90d_queue' => 'decimal:2',
            'price_lifetime'           => 'decimal:2',
            'cost_lifetime'            => 'decimal:2',
            'reseller_price_lifetime'  => 'decimal:2',
        ];
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get all keys for the product.
     */
    public function keys(): HasMany
    {
        return $this->hasMany(Key::class);
    }

    /**
     * Get custom VIP prices for the product.
     */
    public function customPrices(): HasMany
    {
        return $this->hasMany(UserProductPrice::class);
    }

    /**
     * Get regular customer price for duration string.
     */
    public function getPriceForDuration(string $duration): float
    {
        $d = strtolower(trim($duration));
        
        // Auto Queue matches
        if (str_contains($d, '90d_queue') || (str_contains($d, '90') && str_contains($d, 'queue'))) {
            return (float) ($this->price_90d_queue > 0 ? $this->price_90d_queue : ($this->price_90d ?? 0));
        }
        if (str_contains($d, '30d_queue') || (str_contains($d, '30') && str_contains($d, 'queue'))) {
            return (float) ($this->price_30d_queue > 0 ? $this->price_30d_queue : ($this->price_30d ?? 0));
        }
        if (str_contains($d, '7d_queue') || (str_contains($d, '7') && str_contains($d, 'queue'))) {
            return (float) ($this->price_7d_queue > 0 ? $this->price_7d_queue : ($this->price_7d ?? 0));
        }
        if (str_contains($d, '3d_queue') || (str_contains($d, '3') && str_contains($d, 'queue'))) {
            return (float) ($this->price_3d_queue > 0 ? $this->price_3d_queue : ($this->price_3d ?? 0));
        }

        // Standard Normal matches
        if (str_contains($d, 'lifetime') || $d === 'life') return (float) ($this->price_lifetime ?? 0);
        if (str_contains($d, '90d') || str_contains($d, '90')) return (float) ($this->price_90d ?? 0);
        if (str_contains($d, '30d') || str_contains($d, '30')) return (float) ($this->price_30d ?? 0);
        if (str_contains($d, '15d') || str_contains($d, '15')) return (float) ($this->price_15d ?? 0);
        if (str_contains($d, '10d') || str_contains($d, '10')) return (float) ($this->price_10d ?? 0);
        if (str_contains($d, '7d') || str_contains($d, '7')) return (float) ($this->price_7d ?? 0);
        if (str_contains($d, '3d') || str_contains($d, '3')) return (float) ($this->price_3d ?? 0);
        if (str_contains($d, '1d') || str_contains($d, '1')) return (float) ($this->price_1d ?? 0);

        return (float) ($this->price_1d ?? 0);
    }

    /**
     * Get global standard reseller price for duration string.
     */
    public function getResellerPriceForDuration(string $duration): float
    {
        $d = strtolower(trim($duration));
        
        // Auto Queue matches
        if (str_contains($d, '90d_queue') || (str_contains($d, '90') && str_contains($d, 'queue'))) {
            if ($this->reseller_price_90d_queue > 0) return (float) $this->reseller_price_90d_queue;
            if ($this->reseller_price_90d > 0) return (float) $this->reseller_price_90d;
            return $this->getPriceForDuration($duration);
        }
        if (str_contains($d, '30d_queue') || (str_contains($d, '30') && str_contains($d, 'queue'))) {
            if ($this->reseller_price_30d_queue > 0) return (float) $this->reseller_price_30d_queue;
            if ($this->reseller_price_30d > 0) return (float) $this->reseller_price_30d;
            return $this->getPriceForDuration($duration);
        }
        if (str_contains($d, '7d_queue') || (str_contains($d, '7') && str_contains($d, 'queue'))) {
            if ($this->reseller_price_7d_queue > 0) return (float) $this->reseller_price_7d_queue;
            if ($this->reseller_price_7d > 0) return (float) $this->reseller_price_7d;
            return $this->getPriceForDuration($duration);
        }
        if (str_contains($d, '3d_queue') || (str_contains($d, '3') && str_contains($d, 'queue'))) {
            if ($this->reseller_price_3d_queue > 0) return (float) $this->reseller_price_3d_queue;
            if ($this->reseller_price_3d > 0) return (float) $this->reseller_price_3d;
            return $this->getPriceForDuration($duration);
        }

        // Standard Normal matches
        if (str_contains($d, 'lifetime') || $d === 'life') {
            return (float) ($this->reseller_price_lifetime > 0 ? $this->reseller_price_lifetime : $this->price_lifetime);
        }
        if (str_contains($d, '90d') || str_contains($d, '90')) {
            return (float) ($this->reseller_price_90d > 0 ? $this->reseller_price_90d : $this->price_90d);
        }
        if (str_contains($d, '30d') || str_contains($d, '30')) {
            return (float) ($this->reseller_price_30d > 0 ? $this->reseller_price_30d : $this->price_30d);
        }
        if (str_contains($d, '15d') || str_contains($d, '15')) {
            return (float) ($this->reseller_price_15d > 0 ? $this->reseller_price_15d : $this->price_15d);
        }
        if (str_contains($d, '10d') || str_contains($d, '10')) {
            return (float) ($this->reseller_price_10d > 0 ? $this->reseller_price_10d : $this->price_10d);
        }
        if (str_contains($d, '7d') || str_contains($d, '7')) {
            return (float) ($this->reseller_price_7d > 0 ? $this->reseller_price_7d : $this->price_7d);
        }
        if (str_contains($d, '3d') || str_contains($d, '3')) {
            return (float) ($this->reseller_price_3d > 0 ? $this->reseller_price_3d : $this->price_3d);
        }
        if (str_contains($d, '1d') || str_contains($d, '1')) {
            return (float) ($this->reseller_price_1d > 0 ? $this->reseller_price_1d : $this->price_1d);
        }

        return (float) ($this->reseller_price_1d > 0 ? $this->reseller_price_1d : $this->price_1d);
    }

    /**
     * 3-Tier Dynamic Price Resolution:
     * 1. Individual VIP Custom Override Price (UserProductPrice)
     * 2. Global Standard Reseller Price ($this->reseller_price_*)
     * 3. Public Customer Base Price ($this->price_*)
     */
    public function getEffectivePriceForTelegramUser($userOrId, string $duration): float
    {
        $userId = is_object($userOrId) ? $userOrId->id : $userOrId;
        $user = is_object($userOrId) ? $userOrId : TelegramUser::find($userId);

        $d = strtolower(trim($duration));
        $normalizedDur = match (true) {
            str_contains($d, '90') && str_contains($d, 'queue') => '90d_queue',
            str_contains($d, '30') && str_contains($d, 'queue') => '30d_queue',
            str_contains($d, '7') && str_contains($d, 'queue') => '7d_queue',
            str_contains($d, '3') && str_contains($d, 'queue') => '3d_queue',
            str_contains($d, '90') => '90d',
            str_contains($d, '30') => '30d',
            str_contains($d, '15') => '15d',
            str_contains($d, '10') => '10d',
            str_contains($d, '7') => '7d',
            str_contains($d, '3') => '3d',
            str_contains($d, '1') => '1d',
            str_contains($d, 'life') => 'lifetime',
            default => $duration,
        };

        // Tier 1: Check Individual VIP Custom Override Rate
        if ($userId) {
            $custom = UserProductPrice::where('telegram_user_id', $userId)
                ->where('product_id', $this->id)
                ->where(function($q) use ($normalizedDur) {
                    $q->where('duration', $normalizedDur)->orWhereNull('duration');
                })
                ->value('price');

            if ($custom !== null && (float)$custom > 0) {
                return (float) $custom;
            }
        }

        // Tier 2: Check if user is a Standard Reseller
        if ($user && $user->is_reseller) {
            $rPrice = $this->getResellerPriceForDuration($duration);
            if ($rPrice > 0) {
                return $rPrice;
            }
        }

        // Tier 3: Standard Customer Price
        return $this->getPriceForDuration($duration);
    }

    /**
     * Get cost / purchase price for duration string.
     */
    public function getCostForDuration(string $duration): float
    {
        $d = strtolower(trim($duration));
        
        // Auto Queue matches
        if (str_contains($d, '90d_queue') || (str_contains($d, '90') && str_contains($d, 'queue'))) {
            return (float) ($this->cost_90d_queue > 0 ? $this->cost_90d_queue : ($this->cost_90d ?? 0));
        }
        if (str_contains($d, '30d_queue') || (str_contains($d, '30') && str_contains($d, 'queue'))) {
            return (float) ($this->cost_30d_queue > 0 ? $this->cost_30d_queue : ($this->cost_30d ?? 0));
        }
        if (str_contains($d, '7d_queue') || (str_contains($d, '7') && str_contains($d, 'queue'))) {
            return (float) ($this->cost_7d_queue > 0 ? $this->cost_7d_queue : ($this->cost_7d ?? 0));
        }
        if (str_contains($d, '3d_queue') || (str_contains($d, '3') && str_contains($d, 'queue'))) {
            return (float) ($this->cost_3d_queue > 0 ? $this->cost_3d_queue : ($this->cost_3d ?? 0));
        }

        // Standard matches
        if (str_contains($d, 'lifetime') || $d === 'life') return (float) ($this->cost_lifetime ?? 0);
        if (str_contains($d, '90d') || str_contains($d, '90')) return (float) ($this->cost_90d ?? 0);
        if (str_contains($d, '30d') || str_contains($d, '30')) return (float) ($this->cost_30d ?? 0);
        if (str_contains($d, '15d') || str_contains($d, '15')) return (float) ($this->cost_15d ?? 0);
        if (str_contains($d, '10d') || str_contains($d, '10')) return (float) ($this->cost_10d ?? 0);
        if (str_contains($d, '7d') || str_contains($d, '7')) return (float) ($this->cost_7d ?? 0);
        if (str_contains($d, '3d') || str_contains($d, '3')) return (float) ($this->cost_3d ?? 0);
        if (str_contains($d, '1d') || str_contains($d, '1')) return (float) ($this->cost_1d ?? 0);

        return (float) ($this->cost_1d ?? 0);
    }

    /**
     * Get clean display name without duplicate game names in parentheses.
     */
    public function getFormattedDisplayNameAttribute(): string
    {
        $name = trim($this->name);
        $game = trim($this->game_name ?: ($this->category->name ?? ''));

        if (!$game) {
            return $name;
        }

        // If product name already contains the game name in parentheses or text, avoid duplicate
        if (str_contains(strtolower($name), strtolower($game))) {
            return $name;
        }

        return "{$name} ({$game})";
    }
}

