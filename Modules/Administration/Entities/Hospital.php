<?php

namespace Modules\Administration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Acl\Entities\User;
use Modules\Patient\Entities\Patiente;

/**
 * Modèle Hospital (Tenant)
 * 
 * Représente un hôpital (tenant) dans la plateforme multi-tenant.
 * Chaque hôpital a son propre domaine et ses données isolées.
 * 
 * @package Modules\Administration\Entities
 */
class Hospital extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nom de la table
     *
     * @var string
     */
    protected $table = 'hospitals';
    protected $connection = 'tenant';

    /**
     * Attributs assignables en masse
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'domain',
        'slug',
        'status',
        'address',
        'phone',
        'email',
        'logo',
        'description',
        'uuid',
        'created_by',
        'is_synced',
    ];

    /**
     * Attributs à caster
     *
     * @var array
     */
    protected $casts = [
        'is_synced' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * Attributs cachés lors de la sérialisation
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
    ];

    /**
     * Boot du modèle
     * Génère automatiquement l'UUID si non fourni
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($hospital) {
            if (empty($hospital->uuid)) {
                $hospital->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            
            // Génère le slug à partir du nom si non fourni
            if (empty($hospital->slug)) {
                $hospital->slug = \Illuminate\Support\Str::slug($hospital->name);
            }
        });
    }

    /**
     * Factory pour le modèle
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        /** @phpstan-ignore-next-line */
        return \Modules\Administration\Database\factories\HospitalFactory::new();
    }

    /**
     * Relation : Hôpital créé par un utilisateur
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation : Utilisateurs de l'hôpital
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'hospital_id');
    }

    /**
     * Relation : Patients de l'hôpital
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function patients()
    {
        return $this->hasMany(Patiente::class, 'hospital_id');
    }

    /**
     * Relation : Paramètres de l'hôpital
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function settings()
    {
        return $this->hasMany(HospitalSetting::class, 'hospital_id');
    }

    /**
     * Relation : Rendez-vous de l'hôpital
     * (Préparé pour le module Rendez-vous à venir - ÉTAPE 7)
     * 
     * Cette méthode sera implémentée lors de la création du module Appointment à l'ÉTAPE 7
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    // public function appointments()
    // {
    //     return $this->hasMany(\Modules\Appointment\Entities\Appointment::class, 'hospital_id');
    // }

    /**
     * Vérifie si l'hôpital est actif
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Vérifie si l'hôpital est suspendu
     *
     * @return bool
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Vérifie si l'hôpital est inactif
     *
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    /**
     * Scope : Hôpitaux actifs uniquement
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope : Recherche par domaine
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $domain
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDomain($query, string $domain)
    {
        return $query->where('domain', $domain);
    }
}
