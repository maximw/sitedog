<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI;

class TasksPresenter extends BasePresenter
{

    public function actionDefault()
    {
        $this->template->tasks = $this->tasksModel->getByUser($this->getUser()->getId());

    }

    public function actionChecks($id = 0)
    {
        $this->error('Not ready yet');
    }

    public function actionDownload($id = 0)
    {
		if ($id > 0) {
			$task = $this->tasksModel->getById($id);
			if (!$task || $task->user->id != $this->getUser()->getId()) {
				$this->error('No permissions');
			}

            $temp_files = $this->formatTemplateFiles();
            $this->template->setFile($temp_files[0]);
            $this->template->key = $task->password;
            $code = '<?php\n'.PHP_EOL.(string)$this->template;

            header("Cache-Control: private");
            header("Content-Type: application/stream");
            header("Content-Length: ".strlen($code));
            header("Content-Disposition: attachment; filename=".$task->filename.'.php');

            echo $code;
            die;
		} else {
           $this->error('No permissions'); 
        }

    }

    public function actionEdit($id = 0)
    {
		$form = $this['taskForm'];
		if ($id > 0 && !$form->isSubmitted()) {
			$task = $this->tasksModel->getById($id);
			if (!$task) {
				$this->error('Record not found');
			}
			$form->setDefaults($this->tasksModel->toArray($task));

            $contacts = array();
            foreach($task->contacts as $contact) {
                $contacts[] = $contact->id;
            }

            $form['contacts']->setDefaultValue($contacts);
		}
    }

    public function actionDelete($id)
    {
        try{
            $this->tasksModel->delete((int)$id, $this->getUser());
        } catch (\Exception $e) {
            $this->flashMessage($e->getMessage());
            $this->redirect('Tasks:');
        }
        $this->flashMessage('Task was deleted');
        $this->redirect('Tasks:');
    }

	/**
	 * Edit form factory.
	 * @return Form
	 */
	protected function createComponentTaskForm()
	{
		$form = new UI\Form;
		$form->addHidden('id');
		$form->addText('title', 'Title')
			->setRequired('Please enter an title');
		$form->addText('url', 'Site domain:')
			->setRequired('Please enter a site domain');
		$form->addText('directory', 'Root directory (empty = webserver document root)');
        $form->addText('extensions', 'Files extensions to check (comma separated)');

        $allContacts = $this->contactsModel->getByUser($this->getUser()->getId());
        $contacts = array();
        foreach($allContacts as $contact) {
            $contacts[$contact->id] = $contact->channel->name().' '.$contact->value;
        }
        $form->addCheckboxList('contacts', 'Contacts', $contacts);

        $form->addSubmit('save', 'Save')
			->onClick[] = [$this, 'taskFormSucceeded'];
		$form->addSubmit('cancel', 'Cancel')
			->onClick[] = [$this, 'taskformCancelled'];
		$form->addProtection('CSRF token check fail');

        $form = static::bootstrapifyForm($form);

		return $form;
	}

	public function taskFormSucceeded($button)
	{
		$values = $button->getForm()->getValues();
		$id = (int) $this->getParameter('id');

        $task = array(
                'id' => $id,
                'user_id' => $this->getUser()->getId(),
                'title' => $values['title'],
                'url' => $values['url'],
                'directory' => $values['directory'],
                'extensions' => $values['extensions'],
                'contacts' => $values['contacts'],
            );

        try {
            $this->tasksModel->save($task);

            if ($id) {
                $this->flashMessage('Task has been updated.');
            } else {
                $this->flashMessage('Task has been added.');
            }
        } catch(\Exception $e) {
            $this->flashMessage($e->getMessage(), 'danger');
        }

		$this->redirect('Tasks:');
	}

	public function taskformCancelled()
	{
		$this->redirect('Tasks:');
	}

}
