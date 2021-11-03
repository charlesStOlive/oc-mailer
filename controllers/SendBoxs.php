<?php namespace Waka\Mailer\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;

/**
 * Send Box Back-end Controller
 */
class SendBoxs extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Waka.Utils.Behaviors.BtnsBehavior',
        'Backend.Behaviors.RelationController',
    ];
    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $btnsConfig = 'config_btns.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $requiredPermissions = ['waka.mailer.*'];
    //FIN DE LA CONFIG AUTO

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Waka.Mailer', 'SendBoxs');
    }

    //startKeep/
    public function onSendAll() {
        $job = new \Waka\Mailer\Jobs\SendSendBox();
        $jobManager = \App::make('Waka\Wakajob\Classes\JobManager');
        $jobManager->dispatch($job, "Envoi d'emails de la boite envoie");
        $this->vars['jobId'] = $job->jobId;
        return $this->makePartial('$/waka/wakajob/controllers/jobs/_confirm_popup.htm');
    }

    //endKeep/
}

