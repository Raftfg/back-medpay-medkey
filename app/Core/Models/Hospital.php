<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Modèle Hospital (CORE)
 * 
 * Représente un hôpital (tenant) dans la base CORE.
 * Chaque hôpital a son propre domaine et sa propre base de données MySQL.
 * 
 * @package App\Core\Models
 */
class Hospital extends Model
{
    use HasFactory, SoftDeletes;

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
    protected $table = 'hospitals';

    /**
     * Attributs assignables en masse
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'domain',
        'slug',
        'database_name',
        'database_host',
        'database_port',
        'database_username',
        'database_password',
        'status',
        'plan',
        'address',
        'country',
        'city',
        'phone',
        'email',
        'main_language',
        'logo',
        'description',
        'uuid',
        'created_by',
        'is_synced',
        'provisioned_at',
        'onboarding_status',
        'setup_wizard_state',
        'setup_wizard_completed_at',
    ];

    /**
     * Attributs à caster
     *
     * @var array
     */
    protected $casts = [
        'is_synced' => 'boolean',
        'provisioned_at' => 'datetime',
        'setup_wizard_state' => 'array',
        'setup_wizard_completed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Attributs cachés lors de la sérialisation
     *
     * @var array
     */
    protected $hidden = [
        'database_password',
        'deleted_at',
    ];

    /**
     * Boot du modèle
     * Génère automatiquement l'UUID et le slug si non fourni
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($hospital) {
            if (empty($hospital->uuid)) {
                $hospital->uuid = (string) Str::uuid();
            }
            
            // Génère le slug à partir du nom si non fourni
            if (empty($hospital->slug)) {
                $hospital->slug = Str::slug($hospital->name);
            }
            
            // Génère le nom de la base de données si non fourni
            if (empty($hospital->database_name)) {
                $hospital->database_name = 'medkey_' . Str::slug($hospital->name, '_');
            }
        });
    }

    /**
     * Relation : Modules activés pour cet hôpital
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function modules()
    {
        return $this->hasMany(HospitalModule::class, 'hospital_id');
    }

    /**
     * Relation : Modules activés uniquement
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function enabledModules()
    {
        return $this->hasMany(HospitalModule::class, 'hospital_id')
            ->where('is_enabled', true);
    }

    /**
     * Relation : Administrateur système qui a créé l'hôpital
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(SystemAdmin::class, 'created_by');
    }

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
     * Vérifie si l'hôpital est en cours de provisioning
     *
     * @return bool
     */
    public function isProvisioning(): bool
    {
        return $this->status === 'provisioning';
    }

    /**
     * Vérifie si un module est activé pour cet hôpital
     *
     * @param  string  $moduleName
     * @return bool
     */
    public function hasModule(string $moduleName): bool
    {
        return $this->modules()
            ->where('module_name', $moduleName)
            ->where('is_enabled', true)
            ->exists();
    }

    /**
     * Récupère la configuration de connexion à la base tenant
     *
     * @return array
     */
    public function getDatabaseConfig(): array
    {
        return [
            'driver' => 'mysql',
            'host' => $this->database_host ?? config('database.connections.mysql.host'),
            'port' => $this->database_port ?? config('database.connections.mysql.port'),
            'database' => $this->database_name,
            'username' => $this->database_username ?? config('database.connections.mysql.username'),
            'password' => $this->database_password ?? config('database.connections.mysql.password'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB',
        ];
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

    /**
     * Scope : Recherche par slug
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $slug
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return \Database\Factories\Core\HospitalFactory::new();
    }
}
