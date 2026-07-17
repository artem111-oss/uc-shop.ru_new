<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginCodeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $code
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Код входа в личный кабинет uc-shop.ru')
            ->view('emails.login-code');
    }
}