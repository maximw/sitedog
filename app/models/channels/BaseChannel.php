<?php

namespace App\Models\Channels;

use Nette;

abstract class BaseChannel extends Nette\Object
{
    protected $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function sendMessage($message)
    {
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