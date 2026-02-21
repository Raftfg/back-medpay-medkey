<?php

namespace App\Mail\Contact;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $contactMessage)
    {
    }

    public function build(): self
    {
        return $this
            ->subject('Nouveau message de contact Medkey')
            ->view('emails.contact.message-submitted');
    }
}
