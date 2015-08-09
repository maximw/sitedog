<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="contacts")
 */
class Contact extends \Kdyby\Doctrine\Entities\BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     **/
    protected $user;

    /**
     * @ORM\Column(type="integer")
     */
    protected $type;

    /**
     * @ORM\Column(type="string")
     */
    protected $value;

    /**
     * @ORM\Column(type="integer")
     */
    protected $is_enabled;

    /**
     * @ORM\Column(type="integer")
     */
    protected $verify_code;

    /**
     * @ORM\Column(type="integer")
     */
    protected $is_verified;


    /**
     * @ORM\ManyToMany(targetEntity="Task")
     * @ORM\JoinTable(name="tasks_contacts",
     *      joinColumns={@ORM\JoinColumn(name="contact_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="task_id", referencedColumnName="id")}
     *      )
     **/
    protected $tasks;

    public $channel;

    public function __construct()
    {
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
    }

}