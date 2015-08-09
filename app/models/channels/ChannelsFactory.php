<?php

namespace App\Models\Channels;

use Nette;

class ChannelsFactory extends Nette\Object
{
    protected $types = array(
        1 => 'Email',
        3 => 'Http Post request',
    );

    protected $configModel;

    public function __construct(\App\Models\Config $config)
    {
        $this->configModel = $config;
    }

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
            $config = $this->configModel->get('channels:email');
            return new EmailChannel($value, $config);
        }
        if ($id == 3) {
            $config = $this->configModel->get('channels:post');
            return new PostChannel($value, $config);
        }
    }

}