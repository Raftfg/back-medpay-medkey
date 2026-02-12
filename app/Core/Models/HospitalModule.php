<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Modèle HospitalModule (CORE)
 * 
 * Représente un module activé pour un hôpital spécifique.
 * 
 * @package App\Core\Models
 */
class HospitalModule extends Model
{
    use HasFactory;

    /**
     * Nom de la connexion (base CORE)
     *
     * @var string
     */
    protected $connection = 'core';

    /**
     * Nom de la table
     *
     * @var string
     */
    protected $table = 'hospital_modules';

    /**
     * Attributs assignables en masse
     *
     * @var array
     */
    protected $fillable = [
        'hospital_id',
        'module_name',
        'is_enabled',
        'config',
        'enabled_at',
        'disabled_at',
        'enabled_by',
        'notes',
    ];

    /**
     * Attributs à caster
     *
     * @var array
     */
    protected $casts = [
        'is_enabled' => 'boolean',
        'config' => 'array',
        'enabled_at' => 'datetime',
        'disabled_at' => 'datetime',
    ];

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($module) {
            if ($module->is_enabled && !$module->enabled_at) {
                $module->enabled_at = now();
            }
        });

        static::updating(function ($module) {
            if ($module->isDirty('is_enabled')) {
                if ($module->is_enabled) {
                    $module->enabled_at = now();
                    $module->disabled_at = null;
                } else {
                    $module->disabled_at = now();
                }
            }
        });
    }

    /**
     * Relation : Hôpital propriétaire
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }

    /**
     * Relation : Administrateur qui a activé le module
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function enabledBy()
    {
        return $this->belongsTo(SystemAdmin::class, 'enabled_by');
    }

    /**
     * Active le module
     *
     * @param  int|null  $adminId
     * @return void
     */
    public function enable(?int $adminId = null): void
    {
        $this->update([
            'is_enabled' => true,
            'enabled_at' => now(),
            'disabled_at' => null,
            'enabled_by' => $adminId,
        ]);
    }

    /**
     * Désactive le module
     *
     * @return void
     */
    public function disable(): void
    {
        $this->update([
            'is_enabled' => false,
            'disabled_at' => now(),
        ]);
    }

    /**
     * Scope : Modules activés uniquement
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope : Modules désactivés uniquement
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisabled($query)
    {
        return $query->where('is_enabled', false);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Database\Factories\Core\HospitalModuleFactory::new();
    }
}
