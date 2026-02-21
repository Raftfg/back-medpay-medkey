<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Acl\Entities\User;

class AccountValidationMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $setupLink;

    public function __construct(User $user, string $setupLink)
    {
        $this->user = $user;
        $this->setupLink = $setupLink;
    }

    public function build(): self
    {
        return $this->subject('Validation de votre compte MedKey')
            ->view('emails.account_validation');
    }
}
