<?php

namespace App\Presenters;

use App\Models;
use Nette;
use Nette\Utils\Validators;
use Nette\Application\UI;
use Nette\Forms\Controls;

class ContactsPresenter extends BasePresenter
{

    public function actionDefault()
    {
        $this->template->contacts = $this->contactsModel->getByUser($this->getUser()->getId());

    }

    public function actionAdd()
    {
        $this->template->width = 6;
		$form = $this['contactForm'];
    }

    public function actionDelete($id)
    {
        try{
            $this->contactsModel->delete((int)$id, $this->getUser());
        } catch (\Exception $e) {
            $this->flashMessage($e->getMessage());
            $this->redirect('Contacts:');
        }
        $this->flashMessage('Contacts was deleted');
        $this->redirect('Contacts:');
    }

	/**
	 * Edit form factory.
	 * @return Form
	 */
	protected function createComponentContactForm()
	{
		$form = new UI\Form;
        $form->addSelect('type', 'Type *', $this->contactsModel->getChannels())
			->setRequired('Please select contact type');
		$form->addText('value', 'Contact ID *')
			->setRequired('Please enter contact ID');
		$form->addSubmit('save', 'Save')
			->setAttribute('class', 'default')
			->onClick[] = [$this, 'contactFormSucceeded'];
		$form->addSubmit('cancel', 'Cancel')
			->onClick[] = [$this, 'contactformCancelled'];
		$form->addProtection('CSRF token check fail');

        $form = static::bootstrapifyForm($form);

		return $form;
	}

	public function contactFormSucceeded($button)
	{
		$values = $button->getForm()->getValues();
		
        $contact = array(
                'user_id' => $this->getUser()->getId(),
                'type' => $values['type'],
                'value' => $values['value'],
            );
        
        try {
            $this->contactsModel->save($contact);
            $this->flashMessage('Contact was added');
        } catch(\Exception $e) {
            $this->flashMessage('Contact was not added. '. $e->getMessage(), 'danger');
        }

		$this->redirect('Contacts:');
	}

	public function contactFormCancelled()
	{
		$this->redirect('Contacts:');
	}

}
