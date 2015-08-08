<?php

namespace App\Models;

use Nette;

class Config extends Nette\Object
{

    protected $config;

    public function __construct($params)
    {
        $this->config = $params;
    }

    public function get($name)
    {
        $names = explode(':', $name);
        $value = $this->config;
        foreach($names as $n) {
            if (isset($value[$n])) {
                $value = $value[$n];
            } else {
                return null;
            }
        }
        return $value;
    }

}