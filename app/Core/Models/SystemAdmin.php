<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

/**
 * Modèle SystemAdmin (CORE)
 * 
 * Représente un administrateur système qui gère la plateforme multi-tenant.
 * Ces administrateurs ont accès à la base CORE et peuvent créer/gérer les hôpitaux.
 * 
 * @package App\Core\Models
 */
class SystemAdmin extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

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
    protected $table = 'system_admins';

    /**
     * Attributs assignables en masse
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'permissions',
        'role',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * Attributs à caster
     *
     * @var array
     */
    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Attributs cachés lors de la sérialisation
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relation : Hôpitaux créés par cet administrateur
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdHospitals()
    {
        return $this->hasMany(Hospital::class, 'created_by');
    }

    /**
     * Vérifie si l'administrateur est un super admin
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Vérifie si l'administrateur est un admin
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->isSuperAdmin();
    }

    /**
     * Vérifie si l'administrateur a une permission spécifique
     *
     * @param  string  $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissions = $this->permissions ?? [];
        return in_array($permission, $permissions);
    }

    /**
     * Enregistre la dernière connexion
     *
     * @param  string|null  $ip
     * @return void
     */
    public function recordLogin(?string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? request()->ip(),
        ]);
    }

    /**
     * Scope : Administrateurs actifs uniquement
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope : Par rôle
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
