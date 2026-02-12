<?php

namespace Modules\Payment\Entities;

// use Modules\Acl\Entities\User;
use Modules\User\Entities\User;
use Modules\Movment\Entities\Movment;
// use Modules\Cash\Entities\CashRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Operation extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $connection = 'tenant';
    
    // protected static function newFactory()
    // {
    //     return \Modules\Payment\Database\factories\MomoFactory::new();
    // }

    public function user(){
         return $this->belongsTo(User::class, 'user_id');

    }

    public function movement(){
         return $this->belongsTo(Movment::class, 'movement_id');
    }

    
//     public function cashRegister(){
//         return $this->belongsTo(CashRegister::class, 'cash_register_id');
//    }



}
