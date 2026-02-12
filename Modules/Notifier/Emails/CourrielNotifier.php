<?php

namespace Modules\Notifier\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Notifier\Entities\NotifierTracking;

class CourrielNotifier extends Mailable {

    use Queueable,
        SerializesModels;

    private $attributes;

    /**
     * Create a notification instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct($attributes) {
        $this->attributes = $attributes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        $vueCourriel = $this->attributes['view_url'] ? view($this->attributes['view_url'], $this->attributes['data']) : "";
        $emails = $this->attributes['destinataires'];
        if (app()->environment() != 'production') {
            $emails = [env('MAIL_TEST', 'ekpotin@gmail.com')];
        }
        
        if(isset($this->attributes['exception_error']) && $this->attributes['exception_error']){ //ne pas traquer les erreurs exception
        }else{
            NotifierTracking::createDonnees([
                'sujet' => $this->attributes['sujet'],
                'message' => $vueCourriel,
                'destinataires' => $emails,
                'objet' => array_keys(objet_notification_service())[0],
            ]);
        }
        
        return $this->markdown('notifier::emails.courriel_notifier')  //$this->view //markdown pour utiliser le template par défaut de laravel. Voir aussi emails.courriel_notifier
                        ->to($emails)
                        //->bcc([])
                        ->subject($this->attributes['sujet'])
                        ->with("vueCourriel", $vueCourriel);
    }

}
