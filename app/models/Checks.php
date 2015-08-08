<?php

namespace App\Models;

use Nette;
use App\Entities;

class Checks extends Nette\Object
{
    use Traits\EntityToArray;

    protected $em;
    protected $checksRepo;
    protected $tasksModel;
    protected $contactsModel;

    public function __construct(
                        \Kdyby\Doctrine\EntityManager $em,
                        \App\Models\Tasks $tasksModel
                    )
    {
        $this->em = $em;
        $this->checksRepo = $this->em->getRepository(\App\Entities\Check::class);
        $this->tasksModel = $tasksModel;
    }



    public function getById($id)
    {
        $task = $this->checksRepo->findOneBy(['id' => $id]);
        return $task;
    }

}