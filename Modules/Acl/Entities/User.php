<?php

namespace Modules\Acl\Entities;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
class User extends Authenticatable implements MustVerifyEmail, HasMedia {

    use HasFactory,
        Notifiable,
        HasApiTokens,
        HasRoles,
        RevisionableTrait,
        SoftDeletes,
        InteractsWithMedia;
    
    /**
     * Nom de la connexion (base tenant)
     *
     * @var string
     */
    protected $connection = 'tenant';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'is_synced',
        'name',
        'email',
        'prenom',
        'nom_utilisateur',
        'adresse',
        'telephone',
        'sexe',
        'password',
        'must_change_password',
        'idcentre',
        'deleted_at',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /*
      |--------------------------------------------------------------------------
      | SCOPES
      |--------------------------------------------------------------------------
      */


    /*
      |--------------------------------------------------------------------------
      | ACCESORS
      |--------------------------------------------------------------------------
     */

    /*
      |--------------------------------------------------------------------------
      | MUTATORS
      |--------------------------------------------------------------------------
     */
    /**
     * Obtenir le nom complet de l'utilisateur
     *  
     * @return string
     */
    public function getFullNameAttribute() {
        return ucfirst($this->name) . ' ' . ucfirst($this->prenom);
    }

}
