<?php

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    /** @var Models\User */
    protected $userModel;


    /**
     * Inject all models that are expected to be used in most of BasePresenter's ancestors
     * @param Models\User
     */
    public function injectBaseModels(Models\User $userModel)
    {
        $this->userModel = $userModel;
    }

}