<?php

namespace Modules\Patient\Observers;

use Modules\Patient\Entities\PatientInsurance;
use Webpatser\Uuid\Uuid;

class PatientInsuranceObserver
{

    /**
     * Handle to the note "creating" event.
     *
     * @param  PatientInsurance  $model
     * @return void
     */
    public function creating(PatientInsurance $model)
    {
        $model->uuid = Uuid::generate();
    }
}
