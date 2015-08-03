<?php

namespace App\Models\Channels;

use Nette;

class ChannelsFactory extends Nette\Object
{
    protected $types = array(
        1 => 'Email',
        2 => 'Telegram',
        3 => 'Http Post request',
    );

    public function getTypes()
    {
        return $this->types;
    }

    public function isTypeExists($id)
    {
        return (bool)in_array($id, array_keys($this->types));
    }

    public function getChannel($id, $value)
    {
        if ($id == 1) {
            return new EmailChannel($value);
        }
        if ($id == 2) {
            return new TelegramChannel($value);
        }
        if ($id == 3) {
            return new PostChannel($value);
        }
    }

}