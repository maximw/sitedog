<?php

namespace App\Models\Channels;

use Nette;

abstract class BaseChannel extends Nette\Object
{
    protected $value;
    protected $config;

    public function __construct($value, $config) {
        $this->value = $value;
        $this->config = $config;
    }

    public function sendMessage($message)
    {
    }

    public function sendAlert($task)
    {
        $message = $this->formatAlert($task);
        $this->sendMessage($message);
    }

    public function formatAlert($task)
    {
        return 'Some changes were detected during checking "'.$task->title.'".';
    }


    public function generateCode()
    {
        return mt_rand(1000000, 9999999);

    }

    public function name()
    {
        return 'dev/null';
    }



}