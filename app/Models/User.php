<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, AuthenticatableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'is_synced', 'name', 'email', 'prenom', 'nom_utilisateur', 'adresse',  'telephone','sexe', 'password', 'role_id',  'idcentre', 'deleted_at',
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
        // 'password' => 'hashed',
    ];


    // pou etablit cette fonction, il faudrait que l'id du centre soit déjà dans la table user
    public function centre()
    {
        return $this->belongsTo(CentreSanitaire::class, 'idcentre'); // Spécifiez le nom de la colonne dans la table des utilisateurs 
    }

    // public function role()
    // {
    //     return $this->belongsTo(Role::class, 'role_id');
    // }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }


    public static function boot()
    {
        parent::boot();

        // Lorsqu'un utilisateur est créé, assignez-lui un ID dans l'intervalle requis
        static::creating(function ($user) {
            $nextId = User::max('id') + 1;
            $user->id = max(5000001, min($nextId, 10000000));
        });
    }
}
