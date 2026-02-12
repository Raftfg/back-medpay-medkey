<?php
namespace App\Traits;
use App\Notifications\CourrielNotifier;

/**
 *
 * @author ACER
 */
trait EnvoiNotificationUserTrait {
    /**
     * 
     * @param type $model
     * @param type $motDePasse
     */
    public function envoiInvitation($model, $motDePasse) {
        $data["user"] = $model;
        $data["motDePasse"] = $motDePasse;
        $view_url = "acteur::emails.invitation";
        $sujet = __("Votre invitation");
        $attributes = [
            'view_url' => $view_url,
            'data' => $data,
            'destinataires' => [$model->email],
            'sujet' => $sujet,
        ];
        mail_queue(new CourrielNotifier($attributes));
    }
    
}
