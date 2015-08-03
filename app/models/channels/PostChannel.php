<?php

namespace App\Models\Channels;

use Nette;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class PostChannel extends BaseChannel
{
    protected $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function sendMessage($message)
    {

    }

    public function name()
    {
        return 'HTTP Post request';
    }


}