<?php

namespace Modules\Cash\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Acl\Entities\User;

class HistoricalOpenClose extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $table = 'historical_open_closes';
    protected $connection = 'tenant';
    
    protected $fillable = [
        'user_id',
        'cashier_id',
        'cash_registers_id',
        'fonds',
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
        'uuid',
        'deleted_at',
        'is_synced',
    ];


    public static function boot()
    {
        parent::boot();

        // Événement "creating" pour générer un UUID avant l'enregistrement
        static::creating(function ($allocateCash) {
            $allocateCash->uuid = (string) Str::uuid();
        });

        static::created(function ($historicalOpenClose) {
            // Mettez à jour le statut de la caisse correspondante
            $cashRegister = CashRegister::find($historicalOpenClose->cash_registers_id);
            if ($cashRegister) {
                $cashRegister->update(['statut' => $historicalOpenClose->statut]);
            }
        });
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class, 'cash_registers_id');
    }
}
