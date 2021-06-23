<?php namespace Waka\Mailer\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;
use Waka\Mailer\Models\WakaMail;

/**
 * Waka Mail Back-end Controller
 */
class WakaMails extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Waka.Utils.Behaviors.BtnsBehavior',
        'waka.Utils.Behaviors.SideBarAttributesBehavior',
        'Waka.Mailer.Behaviors.MailBehavior',
        'Backend.Behaviors.ReorderController',
        'Waka.Utils.Behaviors.DuplicateModel',
    ];
    public $formConfig = 'config_form.yaml';
    public $btnsConfig = 'config_btns.yaml';
    public $duplicateConfig = 'config_duplicate.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $sidebarAttributesConfig = 'config_attributes.yaml';    
    //FIN DE LA CONFIG AUTO

    public $listConfig = [
        'wakaMails' => 'config_list.yaml',
        'layouts' => 'config_layouts_list.yaml',
        'blocs' => 'config_blocs_list.yaml',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Waka.Mailer', 'WakaMails');

        $blocsWidget = new \Waka\Mailer\Widgets\SidebarBlocs($this);
        $blocsWidget->alias = 'blocsWidget';
        $blocsWidget->bindToController();
    }
    //startKeep/

    public function index($tab = null)
    {
        $this->asExtension('ListController')->index();
        $this->bodyClass = 'compact-container';
        $this->vars['activeTab'] = $tab ?: 'templates';
    }

    public function formExtendFields($form)
    {
        if ($form->context == 'update') {
                $no_ds = WakaMail::find($this->params[0])->no_ds;
                //trace_log($no_ds);
            if($no_ds) {
                $form->removeField('scope');
                $form->removeField('is_scope');
                $form->removeField('data_source');
                $form->removeField('pjs');
                $form->removeField('name'); 
                $form->removeField('images');
                $form->removeField('model_functions');
            }
        }
    }

    public function update($id)
    {
        $this->bodyClass = 'compact-container';
        return $this->asExtension('FormController')->update($id);
    }

    public function update_onSave($recordId = null)
    {
        $this->asExtension('FormController')->update_onSave($recordId);
        return [
            '#sidebar_attributes' => $this->attributesRender($this->params[0]),
        ];
    }
    //endKeep/
}

