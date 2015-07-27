<?php

namespace App\Models;

use Nette;
use Nette\Security as NS;

class Authenticator extends Nette\Object implements NS\IAuthenticator
{

    /**
     * @var \App\Models\Users @inject
     */
    public $usersModel;

    public function __construct(\App\Models\Users $usersModel) {
        $this->usersModel = $usersModel;
    }

    public function authenticate(array $credentials)
    {
        list($email, $password) = $credentials;

        $user = $this->usersModel->getByEmail($email);

        if (!$user) {
            throw new NS\AuthenticationException('User not found.');
        }

        if (!password_verify($password, $user->password)) {
            throw new NS\AuthenticationException('Invalid password.');
        }

        return new NS\Identity($user->id, 0, array('username' => $user->email));
    }



}