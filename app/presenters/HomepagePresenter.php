<?php

namespace App\Presenters;

use Nette;
use App\Models;


class HomepagePresenter extends Nette\Application\UI\Presenter
{

    /**
     * @var \App\Models\Users @inject
     */
    public $usersModel;

    public function actionDefault() {
        //$this->usersModel->create();
        $this->usersModel->all();
    }

}
