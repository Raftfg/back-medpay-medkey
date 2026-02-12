<?php

namespace Modules\Movment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PrescriptionItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';

    protected $fillable = [
        'uuid',
        'prescription_id',
        'product_id',
        'medication_name',
        'dosage',
        'form',
        'administration_route',
        'quantity',
        'frequency',
        'instructions',
        'duration_days',
        'status',
    ];

    protected $dates = ['deleted_at'];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function product()
    {
        return $this->belongsTo(\Modules\Stock\Entities\Product::class, 'product_id');
    }
}
