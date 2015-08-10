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

    public function getByTask($task_id, $filter = '', $limit = 100)
    {
        $qb = $this->checksRepo->createQueryBuilder('t');
        $qb->where($qb->expr()->eq('IDENTITY(t.task)', $task_id));
        if ($filter == 'changes') {
            $qb->andWhere(
                $qb->expr()->orX(
                       $qb->expr()->gt('t.new', 0),
                       $qb->expr()->gt('t.changed', 0),
                       $qb->expr()->gt('t.deleted', 0)
                )
            );
        } elseif ($filter == 'errors') {
            $qb->andWhere($qb->expr()->neq('t.status', \App\Models\Tasks::CHECK_SUCCESS));
        }
        $qb->orderBy('t.start_time', 'DESC');
        $qb->setMaxResults($limit);
        $query = $qb->getQuery();
        $checks = $query->getResult();
        return $checks;
    }

}