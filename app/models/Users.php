<?php

namespace App\Models;

use Nette;
use App\Entities;

class Users extends Nette\Object
{
    protected $em;
    protected $usersRepo;

    public function __construct(\Kdyby\Doctrine\EntityManager $em)
    {
        $this->em = $em;
        $this->usersRepo = $this->em->getRepository(\App\Entities\User::class);
    }

    public function create($email, $password)
    {
        $user = new \App\Entities\User();
        $user->email = static::normalizeLogin($email);
        $user->password = password_hash($password,  PASSWORD_DEFAULT);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function all()
    {
        $users = $this->usersRepo->findAll();
        return $users;
    }

    public function getByEmail($email)
    {
        $email = static::normalizeLogin($email);
        $user = $this->usersRepo->findOneBy(['email' => $email]);
        return $user;

    }

    protected static function normalizeLogin($login)
    {
        return trim(mb_strtolower($login));
    }

}