<?php

namespace App\Models;

use Nette;
use App\Entities;

class Users extends Nette\Object
{
    private $em;
    private $users;

    public function __construct(\Kdyby\Doctrine\EntityManager $em)
    {
        $this->em = $em;
        $this->users = $this->em->getRepository(\App\Entities\User::class);
    }

    public function create() {
        $user = new \App\Entities\User();
        $user->email = "The Tigger Movie";
        $user->password = password_hash("The Tigger Movie",  PASSWORD_DEFAULT);

        //$this->em->persist($user); // start managing the entity
        //$this->em->flush(); // save it to the database
    }

    public function all() {
        $users = $this->users->findAll();
        var_dump($users);
        foreach($users as $user) {
            echo $user->email.'<br>';
        }
        die;
    }


}