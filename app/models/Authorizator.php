<?php

namespace App\Models;

use Nette;
use Nette\Security as NS;

class MyAuthorizator extends Nette\Object
    implements Nette\Security\IAuthorizator
{

    function isAllowed($role, $resource, $privilege)
    {
        return 1;
    }

}