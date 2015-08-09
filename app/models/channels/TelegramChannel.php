<?php

namespace App\Models\Channels;

use Nette;

class TelegramChannel extends BaseChannel
{
    protected $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function sendMessage($message)
    {

        $data = array(
            'chat_id' => $this->value,
            'message' => $message,
        );

        $curl = curl_init('https://api.telegram.org/bot'.$this->config['token'].'/sendMessage');
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_CONNECTTIMEOUT => 600,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => http_build_query($data),
        );

        curl_setopt_array($curl, $options);
        curl_exec($curl);
        curl_close($curl);
    }

    public function formatAlert($task)
    {
        return $task->title.' new:'.$task->new.' changed:'.$task->changed.' deleted:'.$task->deleted;
    }


    public function name()
    {
        return 'Telegram';
    }


}
