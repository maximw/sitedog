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
    protected $cryptoModel;

    /**
     * @var integer $defaultInterval Default interval between checks in seconds
     */
    protected static $defaultInterval = 3600;

    const CHECK_UNKNOWN = 0;
    const CHECK_SUCCESS = 1;
    const CHECK_ERROR   = 2;

    public function __construct(
                        \Kdyby\Doctrine\EntityManager $em,
                        \App\Models\Users $usersModel,
                        \App\Models\Contacts $contactsModel,
                        \App\Models\Crypto $cryptModel
                    )
    {
        $this->em = $em;
        $this->tasksRepo = $this->em->getRepository(\App\Entities\Task::class);
        $this->usersModel = $usersModel;
        $this->contactsModel = $contactsModel;
        $this->cryptoModel = $cryptModel;
    }

    public function save($data)
    {
        $isNew = false;
        if (empty($data['id'])) {
            $isNew = true;
            $task = new Entities\Task();
            $task->setUser($this->em->getReference('App\Entities\User', $data['user_id']));
            $task->filename = strtolower($this->cryptoModel->getRandom(18, true));
            $task->password = $this->cryptoModel->getRandom(32, true);
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

    public function getNextToCheck($limit)
    {
        $qb = $this->tasksRepo->createQueryBuilder('t');
        $qb->where($qb->expr()->lt($qb->expr()->sum('t.last_check', 't.check_interval'), ':time'));
        $qb->orderBy($qb->expr()->sum('t.last_check', 't.check_interval'), 'ASC');
        $qb->setParameter('time', time());
        $qb->setMaxResults($limit);
        $query = $qb->getQuery();
        $tasks = $query->getResult();
        return $tasks;
    }

    public function executeCheck(Entities\Task $task)
    {
        // TODO locking records
        //$lock = $this->em->getConnection()->query('SELECT SQL_NO_CACHE GET_LOCK("task_lock_'.((int)33).'", 0) AS l;');
        //$lock = $lock->fetchAll();
        $check = new Entities\Check();
        $check->task = $task;
        $check->start_time = time();

        $this->em->persist($check);
        $this->em->flush();

        $command = array();
        $command['command'] = 'check';
        $command['directory'] = $task->directory;
        $command['extensions'] = $task->extensions;
        $cmd = $this->cryptoModel->encrypt(json_encode($command), $task->password);

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_USERAGENT, 'uufilemon');
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30000);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_URL, $task->getClientUrl());
            curl_setopt($curl, CURLOPT_POSTFIELDS, 'cmd='.$cmd);
            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                throw new \Exception('Curl error: '.curl_errno($curl).' '.curl_error($curl));
            }

            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if($http_code != 200) {
                throw new \Exception('HTTP code: '.$http_code);
            }

            $result = $this->cryptoModel->decrypt($response, $task->password);
            $result = json_decode($result);

            if (json_last_error()) {
                throw new \Exception('JSON error: '.json_last_error().' '.json_last_error_msg());
            }

            if (isset($result['error']) && $result['error'] > 0) {
                throw \Exception('Client error: '.$result['message']);
            }

            $prevfiles = array();
            if ($task->status == static::CHECK_SUCCESS) {
                $prevfiles = json_decode($task->last_result);
                if (json_last_error()) {
                    throw new \Exception('Taks JSON error: '.json_last_error().' '.json_last_error_msg());
                }
            }
            $lastfiles = $result;

            $comparison  = array();
            $changed = 0;
            $new = 0;
            $deleted = 0;

            foreach($prevfiles as $key => $prevfile) {
                if (isset($lastfiles[$key])) {
                    if ($lastfiles[$key] != $prevfiles[$key]) {
                        $comparison[$key] = 'c';
                        $changed++;
                    }
                    unset($lastfiles[$key]);
                } else {
                    $comparison[$key] = 'd';
                    $deleted++;
                }
                unset($prevfiles[$key]);
            }

            $new = count($lastfiles);
            foreach($lastfiles as $key => $lastfile) {
                $comparison[$key] = 'n';
            }

            $check->status = static::CHECK_SUCCESS;
            $check->result = $result;
            $check->comparison = $comparison;
            $check->finish_time = time();
            $check->changed = 0;
            $check->new = 0;
            $check->deleted = 0;

            $task->last_status = static::CHECK_SUCCESS;
            $task->last_result = $result;
            $task->last_check = time();
            $task->changed = 0;
            $task->new = 0;
            $task->deleted = 0;

            $this->em->persist($check);
            $this->em->persist($task);
            $this->em->flush();

            if ($changed > 0 ||  $new > 0 || $deleted > 0) {
                foreach($task->contacts as $contact) {
                    $message = $contact->getChannel()->sendAlert($task);
                }
            }

        } catch (\Exception $e) {

            $check->status = static::CHECK_ERROR;
            $check->result = $e->getMessage();
            $check->finish_time = time();

            $task->last_status = static::CHECK_ERROR;
            $task->last_result = $e->getMessage();
            $task->last_check = time();
            $task->changed = 0;
            $task->new = 0;
            $task->deleted = 0;

            $this->em->persist($check);
            $this->em->flush();
            $this->em->persist($task);
            $this->em->flush();

        }
        
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

}