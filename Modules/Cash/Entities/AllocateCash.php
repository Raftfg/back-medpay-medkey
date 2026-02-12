<?php

namespace Modules\Cash\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class AllocateCash extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'allocate_cashes';
    protected $connection = 'tenant';
    protected $fillable = [
        'user_id',
        'cashier_id',
        'cash_registers_id',
        'selected_date',
        'opening_time',
        'closing_time',
        'is_choose',
        'statut',
        'uuid',
        'is_synced',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class, 'cash_registers_id');
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($allocateCash) {
            $allocateCash->uuid = (string) Str::uuid();
        });
    }


}
