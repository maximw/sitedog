<?php

namespace App\Models\Channels;

use Nette;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class EmailChannel extends BaseChannel
{
    protected $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function sendMessage($message)
    {
        $mail = new Message;
        $mail->setFrom('John <john@example.com>')
            ->addTo($this->value)
            ->setSubject('Sitedog notice')
            ->setBody($message);

        $mailer = new SendmailMailer;
        $mailer->send($mail);
    }

    public function name()
    {
        return 'Email';
    }


}