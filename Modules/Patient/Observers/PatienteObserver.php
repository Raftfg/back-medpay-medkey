<?php

namespace Modules\Patient\Observers;

use Modules\Patient\Entities\Patiente;
use Webpatser\Uuid\Uuid;

class PatienteObserver
{

    /**
     * Handle to the note "creating" event.
     *
     * @param  Patiente  $model
     * @return void
     */
    public function creating(Patiente $model)
    {
        $model->uuid = Uuid::generate();
        // Générer un numéro d'IPP unique de 12 chiffres
        $model->ipp = $this->generateUniqueIPP();
    }

    private function generateUniqueIPP()
    {
        $ipp = mt_rand(100000000000, 999999999999); // Génère un nombre aléatoire de 12 chiffres

        // Vérifiez si l'IPP est unique dans la base de données, sinon regénérez-le
        // On utilise la façade DB pour éviter les GlobalScopes (comme HospitalScope) qui cherchent hospital_id
        while (\Illuminate\Support\Facades\DB::table('patients')->where('ipp', $ipp)->exists()) {
            $ipp = mt_rand(100000000000, 999999999999); // Regénérez l'IPP
        }

        return $ipp;
    }
}
