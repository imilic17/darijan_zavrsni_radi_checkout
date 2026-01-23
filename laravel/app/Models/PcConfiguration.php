<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PcConfiguration extends Model
{
    protected $table = 'pc_configurations';

    protected $fillable = [
        'user_id',
        'session_id',
        'naziv',
        'ukupna_cijena',
    ];

    protected $casts = [
        'ukupna_cijena' => 'decimal:2',
    ];

    /**
     * Get the user who owns this configuration
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all items in this configuration
     */
    public function items(): HasMany
    {
        return $this->hasMany(PcConfigurationItem::class, 'configuration_id');
    }

    /**
     * Calculate and update total price
     */
    public function calculateTotalPrice(): float
    {
        $total = $this->items()->sum('cijena_u_trenutku');
        $this->ukupna_cijena = $total;
        $this->save();
        return $total;
    }

    /**
     * Add a component to the configuration
     */
    public function addComponent(int $componentTypeId, int $proizvodId, float $cijena): PcConfigurationItem
    {
        // Remove existing component of the same type
        $this->items()->where('component_type_id', $componentTypeId)->delete();

        // Add new component
        $item = $this->items()->create([
            'component_type_id' => $componentTypeId,
            'proizvod_id' => $proizvodId,
            'cijena_u_trenutku' => $cijena,
        ]);

        $this->calculateTotalPrice();

        return $item;
    }

    /**
     * Remove a component from the configuration
     */
    public function removeComponent(int $componentTypeId): bool
    {
        $deleted = $this->items()->where('component_type_id', $componentTypeId)->delete();
        $this->calculateTotalPrice();
        return $deleted > 0;
    }

    /**
     * Get component by type
     */
    public function getComponentByType(int $componentTypeId): ?PcConfigurationItem
    {
        return $this->items()->where('component_type_id', $componentTypeId)->first();
    }

    /**
     * Check if configuration has all required components
     */
    public function isComplete(): bool
    {
        $requiredTypes = PcComponentType::where('obavezan', true)->pluck('id');
        $selectedTypes = $this->items()->pluck('component_type_id');

        return $requiredTypes->diff($selectedTypes)->isEmpty();
    }

    /**
     * Get total TDP (power consumption estimate)
     */
    public function getTotalTdp(): int
    {
        $total = 0;
        foreach ($this->items()->with('proizvod.pcSpec')->get() as $item) {
            if ($item->proizvod && $item->proizvod->pcSpec) {
                $total += $item->proizvod->pcSpec->tdp ?? 0;
            }
        }
        return $total;
    }

    /**
     * Get recommended PSU wattage
     */
    public function getRecommendedWattage(): int
    {
        $tdp = $this->getTotalTdp();
        // Add 20% headroom + base system power (50W)
        return (int) ceil(($tdp + 50) * 1.2);
    }
}
