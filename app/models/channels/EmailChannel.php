<?php

namespace App\Models\Channels;

use Nette;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class EmailChannel extends BaseChannel
{

    public function sendMessage($message)
    {
        $mail = new Message;
        $mail->setFrom($this->config['from'])
            ->addTo($this->value)
            ->setSubject('Sitedog alert')
            ->setBody($message);

        $mailer = new SendmailMailer;
        //$mailer->send($mail);
    }

    public function formatAlert($task)
    {
        return $task->title.' new:'.$task->new.' changed:'.$task->changed.' deleted:'.$task->deleted;

    }

    public function name()
    {
        return 'Email';
    }


}