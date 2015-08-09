<?php

namespace App\Models\Channels;

use Nette;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class PostChannel extends BaseChannel
{

    public function sendMessage($message)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, 'Sitedog');
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30000);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_URL, $this->value);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array('message' => $message));
        curl_exec($curl);

    }

    public function formatAlert($task)
    {
        $result = array(
            'title' => $task->title,
            'url' => $taks->url,
            'last_status' => $task->last_status,
            'changed' => $task->changed,
            'new' => $task->new,
            'deleted' => $task->deleted,
        );

        return json_encode($result);

    }

    public function name()
    {
        return 'HTTP Post request';
    }


}