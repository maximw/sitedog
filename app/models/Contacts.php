<?php

namespace App\Models;

use Nette;
use App\Entities;

class Contacts extends Nette\Object
{
    protected $em;
    protected $contactsRepo;
    protected $channelsFactory;
    public static $types = array(
        1 => 'Email',
        2 => 'Telegram',
    );

    public function __construct(
                        \Kdyby\Doctrine\EntityManager $em,
                        Channels\ChannelsFactory $factory
                    )
    {
        $this->em = $em;
        $this->contactsRepo = $this->em->getRepository(\App\Entities\Contact::class);
        $this->channelsFactory = $factory;
    }

    public function save($data)
    {
        $contact = new Entities\Contact();
        $contact->setUser($this->em->getReference('App\Entities\User', $data['user_id']));

        if ($this->channelsFactory->isTypeExists($data['type'])) {
            $contact->type = $data['type'];
        } elseif ($isNew) {
            throw new \Exception('Unknown contact type');
        }

        if (!empty($data['value'])) {
            $contact->value = $data['value'];
        } elseif ($isNew) {
            throw new \Exception('Contact ID cannot be empty');
        }

        $contact->is_enabled = 1;
        $channel = $this->channelsFactory->getChannel($data['type'], $data['value']);
        $contact->verify_code = $channel->generateCode();
        $contact->is_verified = 0;

        $this->em->persist($contact);
        $this->em->flush();
    }

    public function delete($id, $user)
    {
        $contact = $this->getById($id);
        if (empty($contact)) {
            throw new \Exception('Contact not found');
        }
        if ($contact->user->id != $user->getId()) {
            throw new \Exception('Not have permission');
        }
        $this->em->remove($contact);
        $this->em->flush();
    }

    public function getById($id)
    {
        $contact = $this->contactsRepo->findOneBy(['id' => $id]);
        return $contact;
    }

    public function getByUser($user_id)
    {
        $contacts = $this->contactsRepo->findBy(['user.id' => $user_id]);
        foreach($contacts as $contact) {
            $this->getChannel($contact);
        }

        return $contacts;
    }

    public function getChannel($contact)
    {
        if (empty($contact->channel)) {
            $contact->channel = $this->channelsFactory->getChannel($contact->type, $contact->value);
        }
        return $contact->channel;
    }


    public function getChannels()
    {
        return $this->channelsFactory->getTypes();
    }

}