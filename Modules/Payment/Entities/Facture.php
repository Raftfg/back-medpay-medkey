<?php

namespace Modules\Payment\Entities;

// use Modules\Acl\Entities\User;
use Modules\User\Entities\User;
use Modules\Movment\Entities\Movment;
use Modules\Cash\Entities\CashRegister;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Modules\Administration\Entities\MedicalAct;
use Modules\Administration\Entities\Hospital;
use Modules\Media\Entities\Media;
class Facture extends Model
{
    use HasFactory;

    protected $table = 'factures';
    protected $connection = 'tenant';

    protected $fillable = [
        'uuid',
        'cash_registers_id',
        'movments_id',
        'mode_payements_id',
        'is_synced',
        'reference',
        'acte_medical_id',
        'lots_uuid',
        'user_id',
        'hospital_id',
        'centre_id',
        'code',
        'designation',
        'type',
        'partial_amount',
        'prix',
        'quantite',
        'amount',
        'paid',
        'is_factured',
        'percentageassurance',
        'created_at',
        'updated_at',
    ];

    public function movment()
    {
        return $this->belongsTo(Movment::class, 'movments_id');
    }

    // public function acteMedical()
    // {
    //     return $this->belongsTo(MedicalAct::class, 'acte_medical_id');
    // }

    public function medical_acts()
    {
        return $this->hasMany(MedicalAct::class, 'acte_medical_id', 'id');
    }

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
        static::creating(function ($facture) {
            $facture->uuid = (string) Str::uuid();
        });
    }

    public static function getSolde($caisseID)
    {
        return self::where('cash_registers_id', $caisseID)->sum('montant');
    }
}
