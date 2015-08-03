<?php

namespace App\Presenters;

use App\Models;

use Nette;
use Nette\Utils\Validators;
use Nette\Application\UI;

class UserPresenter extends BasePresenter
{

    /**
     * @var \App\Models\Users @inject
     */
    public $usersModel;

    public function actionRegister() {

        

        $this->usersModel->create();
        $this->usersModel->all();
    }

	/**
	 * Log-in form
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentLoginForm()
	{
		$form = new UI\Form;
		$form->addText('email', 'Email:')
            ->setType('email')
			->setRequired('Please enter your email.')
            ->addRule(UI\Form::EMAIL);
		$form->addPassword('password', 'Password:')
			->setRequired('Please enter your password.');
		$form->addSubmit('send', 'Enter');
		$form->onSuccess[] = [$this, 'loginFormSucceeded'];

        $form = static::bootstrapifyForm($form);

		return $form;
	}

	public function loginFormSucceeded($form, $values)
	{
		try {
			$this->getUser()->login($values->email, $values->password);
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
			return;
		}
        
		$this->restoreRequest($this->backlink);
		$this->redirect('Homepage:');
	}

	/**
	 * Registration form
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentRegistrationForm()
	{
		$form = new UI\Form;
		$form->addText('email', 'Email:')
            ->setType('email')
            ->setRequired('Please enter your email.')
            ->addRule(UI\Form::EMAIL) ;
		$form->addPassword('password', 'Password:')
			->setRequired('Please enter your password.');
		$form->addPassword('password2', 'Password one more time:')
			->setRequired('Please enter your password.')
            ->addRule(UI\Form::EQUAL, 'Password missmatch', $form['password']);
		$form->addSubmit('send', 'Enter');
		$form->onSuccess[] = [$this, 'registrationFormSucceeded'];

        $form = static::bootstrapifyForm($form);

		return $form;
	}

	public function registrationFormSucceeded($form, $values)
	{
        try {
            $this->usersModel->create($values->email, $values->password);
        } catch (\Exception $e) {
            $form->addError($e->getMessage());
            return;
        }

		try {
			$this->getUser()->login($values->email, $values->password);
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
			return;
		}
        $this->redirect('Homepage:');
	}


	public function actionLogout()
	{
		$this->getUser()->logout();
		$this->flashMessage('You have been signed out.');
		$this->redirect('Homepage:');
	}

}
