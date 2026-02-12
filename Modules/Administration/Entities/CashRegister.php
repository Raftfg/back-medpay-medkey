<?php

namespace Modules\Administration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashRegister extends Model
{
    use HasFactory;

    protected $table = 'cash_registers';
    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'cash_categories_id',
        'solde',
    ];

    public function cashCategory()
    {
        return $this->belongsTo(CashCategory::class, 'cash_categories_id');
    }

    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\CashRegisterFactory::new();
    }
}
