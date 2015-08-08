<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="checks")
 */
class Check extends \Kdyby\Doctrine\Entities\BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Task")
     **/
    protected $task;

    /**
     * @ORM\Column(type="string")
     */
    protected $task_params;

    /**
     * @ORM\Column(type="string")
     */
    protected $result;

    /**
     * @ORM\Column(type="string")
     */
    protected $comparison;

    /**
     * @ORM\Column(type="integer")
     */
    protected $changed;

    /**
     * @ORM\Column(type="integer")
     */
    protected $new;

    /**
     * @ORM\Column(type="integer")
     */
    protected $deleted;

    /**
     * @ORM\Column(type="integer")
     */
    protected $start_time;

    /**
     * @ORM\Column(type="integer")
     */
    protected $finish_time;

    /**
     * @ORM\Column(type="integer")
     */
    protected $status;

}