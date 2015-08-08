<?php

namespace App\Presenters;

class CronPresenter extends BasePresenter
{

    public function actionDefault()
    {
        $batchsize = $this->config->get('cron:batchsize');
        $tasks = $this->tasksModel->getNextToCheck($batchsize);

        foreach($tasks as $task) {
            $this->tasksModel->executeCheck($task);
        }

        die;

    }

}
