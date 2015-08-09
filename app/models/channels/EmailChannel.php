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
            ->setHTMLBody($message);

        $mailer = new SendmailMailer;
        $mailer->send($mail);
    }

    public function formatAlert($task)
    {

        $latte = new Latte\Engine;
        $params = array(
            'title' => $task->title,
            'new'  => $task->new,
            'changed'  => $task->changed,
            'deleted'  => $task->deleted,
        );

        return $latte->renderToString(__DIR__.DIRECTORY_SEPARATOR.'email.latte', $params);
    }

    public function name()
    {
        return 'Email';
    }


}