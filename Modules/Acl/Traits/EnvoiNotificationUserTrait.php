<?php
namespace Modules\Acl\Traits;
use Modules\Notifier\Emails\CourrielNotifier;
use URL;

/**
 *
 * @author ACER
 */
trait EnvoiNotificationUserTrait {

    protected function confirmationCourriel($model) {
        $sujet = __("Confirmation de votre courriel");
        $view_url = "acl::emails.email_confirmation";
        $backUrlSign = URL::temporarySignedRoute(
                        'auth.email_confirmation',
                        now()->addMinutes(config('premier.duree_url_temporaire')),
                        ['uuid' => $model->uuid]
        );
        //Récupérer la chaîne représentant la signature et la date d'expiration
        $signAndDate = explode("?", $backUrlSign)[1];
        \Log::info($backUrlSign);
        $duree_url_temporaire = config('premier.duree_url_temporaire');
        $duree_url_temporaire_h = intval($duree_url_temporaire / 60);

        $data = [
            'aliasAcl' => strtolower(config('acl.name')),
            'item' => $model->fresh(),
            'frontend_url_temp' => config('premier.frontend.url.validation_email')
            . '?uuid=' . $model->uuid
            . '&' . $signAndDate,
            'duree_url_temporaire' => config('premier.duree_url_temporaire') . " minutes ($duree_url_temporaire_h h)",
        ];
        //Juste pour la version locale. Ça permet de récupérer l'URL rapidement dans telescope pour les test
        if (app()->environment() == "local") {
            \Log::info("==========================");
            \Log::info("backUrlSign = $backUrlSign");
            \Log::info("==========================");
        }
        $attributes = [
            'view_url' => $view_url,
            'data' => $data,
            'destinataires' => [$model->email],
            'sujet' => $sujet,
        ];
        mail_queue(new CourrielNotifier($attributes));
    }
    
    protected function envoiMotPasseTemporaire($model, $motDePasse) {
        $data["user"] = $model;
        $data["motDePasse"] = $motDePasse;
        $view_url = "acl::emails.envoi_motpasse_temporaire";
        $sujet = __("Votre mot de passe temporaire");
        $attributes = [
            'view_url' => $view_url,
            'data' => $data,
            'destinataires' => [$model->email],
            'sujet' => $sujet,
        ];
        mail_queue(new CourrielNotifier($attributes));
    }
}
