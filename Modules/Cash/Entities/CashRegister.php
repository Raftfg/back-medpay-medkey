<?php

namespace Modules\Cash\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Acl\Entities\User;
use Modules\Administration\Entities\Hospital;
class CashRegister extends Model
{
    use HasFactory, SoftDeletes;

    // Utilisation de SoftDeletes pour prendre en charge la suppression douce (soft delete)

    protected $table = 'cash_registers'; // Nom de la table
    protected $connection = 'tenant';

    protected $fillable = [
        
        'uuid',
        'user_id',
        'hospital_id',
        'designation',
        'description',
        'type',
        'total_partial',
        'solde',
        'credits',
        'total_espece',
        'totalMtnMomo',
        'totalMoovMomo',
        'totalCarteBancaire',
        'totalCarteCredit',
        'totalTresorPay',
        'statut',
        'is_synced',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function boot()
    {
        parent::boot();
        // Événement "creating" pour générer un UUID avant l'enregistrement
        static::creating(function ($cashRegister) {
            $cashRegister->uuid = (string) Str::uuid();
        });
    }

   
}
