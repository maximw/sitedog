<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tasks")
 */
class Task extends \Kdyby\Doctrine\Entities\BaseEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    /* **
      * @ ORM\Column(type="integer")
      **/
    //protected $user_id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    protected $user;

    /**
     * @ORM\Column(type="string")
     */
    protected $url;

    /**
     * @ORM\Column(type="string")
     */
    protected $directory;

    /**
     * @ORM\Column(type="string")
     */
    protected $extensions;

    /**
     * @ORM\Column(type="string")
     */
    protected $filename;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="integer")
     */
    protected $check_interval;

    /**
     * @ORM\Column(type="integer")
     */
    protected $is_enabled;

    /**
     * @ORM\Column(type="integer")
     */
    protected $last_status;

    /**
     * @ORM\Column(type="integer")
     */
    protected $last_check;

    /**
     * @ORM\Column(type="string")
     */
    protected $last_result;

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
     * @ORM\ManyToMany(targetEntity="Contact")
     * @ORM\JoinTable(name="tasks_contacts",
     *      joinColumns={@ORM\JoinColumn(name="task_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="contact_id", referencedColumnName="id")}
     *      )
     **/
    protected $contacts;

    /**
     * @ORM\OneToMany(targetEntity="Check", mappedBy="Task", cascade={"remove"})
     */
    protected $checks;

    public function __construct()
    {
        $this->contacts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getClientUrl()
    {
        $this->url.$this->filename.'.php';
    }


}
