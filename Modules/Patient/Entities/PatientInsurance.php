<?php

namespace Modules\Patient\Entities;

use Modules\Patient\Entities\Patiente;
use Illuminate\Database\Eloquent\Model;
use Modules\Administration\Entities\Pack;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class PatientInsurance extends Model
{
    protected $guarded = [];
    protected $connection = 'tenant';
    use HasFactory;

    public function patient()
    {
        return $this->belongsTo(Patiente::class, 'patients_id');
    }

    public function pack()
    {
        return $this->belongsTo(Pack::class, 'pack_id');
    }
}
