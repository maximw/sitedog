<?php

namespace App\Models;

use Nette;
use App\Entities;

class Tasks extends Nette\Object
{
    use Traits\EntityToArray;

    protected $em;
    protected $tasksRepo;
    protected $usersModel;
    protected $contactsModel;

    /**
     * @var integer $defaultInterval Default interval between checks in seconds
     */
    protected static $defaultInterval = 3600;

    public function __construct(
                        \Kdyby\Doctrine\EntityManager $em,
                        \App\Models\Users $usersModel,
                        \App\Models\Contacts $contactsModel
                    )
    {
        $this->em = $em;
        $this->tasksRepo = $this->em->getRepository(\App\Entities\Task::class);
        $this->usersModel = $usersModel;
        $this->contactsModel = $contactsModel;
    }

    public function save($data)
    {
        $isNew = false;
        if (empty($data['id'])) {
            $isNew = true;
            $task = new Entities\Task();
            $task->setUser($this->em->getReference('App\Entities\User', $data['user_id']));
            $task->filename = strtolower(static::getRandom(18));
            $task->password = static::getRandom(32);
            $task->check_interval = static::$defaultInterval;
        } else {
            $task = $this->getById($data['id']);
            if ($task->user->id != $data['user_id']) {
                throw new \Exception('Not have permission');
            }
        }

        if (!empty($data['title'])) {
            $task->title = $data['title'];
        } elseif ($isNew) {
            throw new \Exception('Title cannot be empty');
        }

        if (!empty($data['url'])) {
            $task->url = static::normalizeUrl($data['url']);
        } elseif ($isNew) {
            throw new \Exception('URL cannot be empty');
        }

        $task->directory = $data['directory'];
        
        $task->extensions = static::normalizeExtensions($data['extensions']);

        foreach($task->contacts as $contact) {
            $task->removeContact($contact);
        }
        foreach($data['contacts'] as $cid){
            $task->addContact($this->em->getReference('App\Entities\Contact', $cid));
        }

        $this->em->persist($task);
        $this->em->flush();

    }

    public function delete($id, $user)
    {
        $task = $this->getById($id);
        if (empty($task)) {
            throw new \Exception('Task not found');
        }
        if ($task->user->id != $user->getId()) {
            throw new \Exception('Not have permission');
        }
        $this->em->remove($task);
        $this->em->flush();
    }

    public function getById($id)
    {
        $task = $this->tasksRepo->findOneBy(['id' => $id]);
        return $task;
    }

    public function getByUser($user_id)
    {
        $tasks = $this->tasksRepo->findBy(['user.id' => $user_id]);
        return $tasks;
    }

    protected static function normalizeUrl($url)
    {
        $url = trim(mb_strtolower($url));
        $url = trim($url, '/').'/';
        if (substr($url, 0, 7) != 'http://' && substr($url, 0, 7) != 'https://') {
            $url = 'http://'.$url;
        }
        return $url;
    }

    protected static function normalizeExtensions($extensions)
    {
        $extensions = explode(',', $extensions);
        array_walk($extensions, function(&$value, $key){
            $value = trim($value, ". \t\n\r\0\x0B");
            return $value;
        });
        $extensions = array_filter($extensions, 'trim');
        return implode(',', $extensions);
    }

    protected static function getRandom($length)
    {
        $result = openssl_random_pseudo_bytes($length);
        if (strlen($result) != $length) {
            throw new Exception('Fail get random bytes');
        }
        return rtrim(strtr(base64_encode($result), '+/', '-_'), '=');
    }

}