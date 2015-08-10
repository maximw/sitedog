<?php

namespace App\Presenters;

use App\Models;
use Nette;
use Nette\Utils\Validators;
use Nette\Application\UI;
use Nette\Forms\Controls;
use Nette\Security\User;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    /**
     * @var \App\Models\Config @inject
     */
    public $config;

    /**
     * @var \App\Models\Users @inject
     */
    public $usersModel;

    /**
     * @var \App\Models\Tasks @inject
     */
    public $tasksModel;

    /**
     * @var \App\Models\Contacts @inject
     */
    public $contactsModel;

    /**
     * @var \App\Models\Checks @inject
     */
    public $checksModel;

    public function startup()
    {
        parent::startup();
        $user = $this->getUser();
        $auth = $user->getAuthorizator();

        if ($user->isLoggedIn()) {
            $role = 'user';
            if ($auth->hasResource($this->name.':'.$this->action)) {
                if (!$auth->isAllowed($role, $this->name.':'.$this->action)) {
                    $this->redirect('Tasks:');
                }
            } elseif ($auth->hasResource($this->name))  {
                if (!$auth->isAllowed($role, $this->name)) {
                    $this->redirect('Tasks:');
                }
            }
        } else {
            if ($user->getLogoutReason() === User::INACTIVITY) {
                $this->flashMessage('Session timeout, you have been logged out');
            }
            $role = 'guest';

            if ($auth->hasResource($this->name.':'.$this->action)) {
                if (!$auth->isAllowed($role, $this->name.':'.$this->action)) {
                    $this->redirect('User:login', array(
                        'backlink' => $this->storeRequest()
                    ));
                }
            } elseif ($auth->hasResource($this->name))  {
                if (!$auth->isAllowed($role, $this->name)) {

                    $this->redirect('User:login', array(
                        'backlink' => $this->storeRequest()
                    ));
                }
            }
        }
    }


    protected static function bootstrapifyForm($form)
    {
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-9';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
        // make form and controls compatible with Twitter Bootstrap
        $form->getElementPrototype()->class('form-horizontal');


        foreach ($form->getControls() as $control) {
            if ($control instanceof Controls\Button) {
                $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                $usedPrimary = TRUE;
            } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
                $control->getControlPrototype()->addClass('form-control');
            } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
                $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
            }
        }
        return $form;
    }

}