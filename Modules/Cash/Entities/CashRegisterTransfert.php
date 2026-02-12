<?php

namespace Modules\Cash\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Acl\Entities\User;
class CashRegisterTransfert extends Model
{
    use HasFactory;

    
    use SoftDeletes;

    protected $table = 'cash_register_transferts';
    protected $connection = 'tenant';

    protected $fillable = [
        'user_id',
        'cashier_id',
        'cash_registers_id',
        'approver_id',
        'number',
        'fonds',
        'solde',
        'credits',
        'statut',
        'uuid',
        'deleted_at',
        'is_synced',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class, 'cash_registers_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Écoutez l'événement "creating" du modèle
        static::creating(function ($model) {
            // Générez un UUID et attribuez-le à la colonne "uuid" si elle est vide
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });


        static::creating(function ($model) {
            
            $randomNumber = mt_rand(1000, 9999);

            // Concaténer la date (ymd) avec le nombre aléatoire
            $uniqueNumber = (date('dmy') . $randomNumber);
        
            // Vérifier s'il existe déjà un enregistrement avec le même numéro
            $existingModel = self::where('number', $uniqueNumber)->first();
            
            // Si un enregistrement avec le même numéro existe, régénérez le nombre aléatoire
            while ($existingModel) {
                $randomNumber = mt_rand(1000, 9999);
                $uniqueNumber = (int)(date('ymd') . $randomNumber);
                $existingModel = self::where('number', $uniqueNumber)->first();
            }
        
            $model->number = $uniqueNumber;

        });

    }


    

}
