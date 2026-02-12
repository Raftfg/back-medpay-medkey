<?php

namespace App\Mail;

// use App\Models\User;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Modules\Acl\Entities\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;


class PasswordResetMail extends Mailable
{

    use Queueable, SerializesModels;
    public $user;
    // public $expires; // Ajoutez cette variable
    public $resetLink;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $resetLink)
    {
        //
        $this->user = $user;
        // $this->expires=$expires;
        $this->resetLink = $resetLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Réinitialisation de mot de passe')
            ->view('emails.password_reset');
    }
    // public function build()
    // {
    //     // Utilisez le gestionnaire Blade pour formater le texte
    //     $expiresText = now()->addMinutes($this->expires)->diffForHumans(null, true);

    //     return $this->subject('Réinitialisation de mot de passe')
    //         ->view('emails.password_reset')
    //         ->with([
    //             'expiresText' => $expiresText,
    //         ]);
    // }
}
