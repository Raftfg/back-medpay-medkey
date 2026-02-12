<?php

namespace Modules\Patient\Entities;

use Modules\Acl\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Administration\Entities\Pays;
use Modules\Administration\Entities\Commune;
use Modules\Administration\Entities\Quartier;
use Modules\Patient\Entities\PatientInsurance;
use Modules\Administration\Entities\Departement;
use Modules\Administration\Entities\Arrondissement;
use Modules\Administration\Entities\Hospital;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Patiente extends Model
{
    protected $table = 'patients';
    protected $connection = 'tenant';
    protected $guarded = [];
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
    public function pays()
    {
        return $this->belongsTo(Pays::class, 'pays_id');
    }
    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departements_id');
    }
    public function commune()
    {
        return $this->belongsTo(Commune::class, 'communes_id');
    }
    public function arrondissement()
    {
        return $this->belongsTo(Arrondissement::class, 'arrondissements_id');
    }
    public function patientInsurances()
    {
        return $this->hasMany(PatientInsurance::class, 'patients_id');
    }
}
