<?php

namespace Modules\Administration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AllocateCashRegister extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
protected $fillable = [
        'cash_registers_id',
        'cashiers_id',
    ];

    // Éventuellement, définir des relations avec d'autres modèles
    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class, 'cash_registers_id');
    }

    public function cashier()
    {
        return $this->belongsTo(Cashier::class, 'cashiers_id');
    }

    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\AllocateCashRegisterFactory::new();
    }
}
