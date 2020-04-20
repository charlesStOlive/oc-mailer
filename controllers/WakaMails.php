<?php namespace Waka\Mailer\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;

/**
 * WakaMails Back-end Controller
 */
class WakaMails extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.ReorderController',
        'Waka.Informer.Behaviors.PopupInfo',
        'Waka.Mailer.Behaviors.MailBehavior',
        'Waka.Utils.Behaviors.DuplicateModel',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    //public $duplicateConfig = 'config_duplicate.yaml';

    public $reorderConfig = 'config_reorder.yaml';
    public $duplicateConfig = 'config_duplicate.yaml';
    public $contextContent;

    public $sidebarAttributes;

    public function __construct()
    {
        parent::__construct();

        //BackendMenu::setContext('Waka.Mailer', 'mailer', 'side-menu-wakamails');
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Waka.Mailer', 'wakamails');

        $this->sidebarAttributes = new \Waka\Utils\Widgets\SidebarAttributes($this);
        $this->sidebarAttributes->alias = 'SideBarAttributes';
        $this->sidebarAttributes->type = 'twig';
        $this->sidebarAttributes->bindToController();

    }
    public function update($id)
    {
        $this->bodyClass = 'compact-container';
        return $this->asExtension('FormController')->update($id);
    }

    // public function onTestList()
    // {
    //     $model = \Waka\Mailer\Models\WakaMail::find($this->params[0]);
    // }

    // public function onCreateItem()
    // {
    //     $bloc = $this->getBlocModel();

    //     $data = post($bloc->bloc_type->code . 'Form');
    //     $sk = post('_session_key');
    //     $bloc->delete_informs();

    //     $model = new \Waka\Mailer\Models\Content;
    //     $model->fill($data);
    //     $model->save();

    //     $bloc->contents()->add($model, $sk);

    //     return $this->refreshOrderItemList($sk);
    // }

    // public function onUpdateContent()
    // {
    //     $bloc = $this->getBlocModel();

    //     $recordId = post('record_id');
    //     $data = post($bloc->bloc_type->code . 'Form');
    //     $sk = post('_session_key');

    //     $model = \Waka\Mailer\Models\Content::find($recordId);
    //     $model->fill($data);
    //     $model->save();

    //     return $this->refreshOrderItemList($sk);
    // }

    // public function onDeleteItem()
    // {
    //     $recordId = post('record_id');
    //     $sk = post('_session_key');

    //     $model = \Waka\Mailer\Models\Content::find($recordId);

    //     $bloc = $this->getBlocModel();
    //     $bloc->contents()->remove($model, $sk);

    //     return $this->refreshOrderItemList($sk);
    // }

    // protected function refreshOrderItemList($sk)
    // {
    //     $bloc = $this->getBlocModel();
    //     $contents = $bloc->contents()->withDeferred($sk)->get();

    //     $this->vars['contents'] = $contents;
    //     $this->vars['bloc_type'] = $bloc->bloc_type;
    //     return [
    //         '#contentList' => $this->makePartial('content_list'),
    //     ];
    // }

    // public function getBlocModel()
    // {
    //     $manageId = post('manage_id');

    //     $bloc = $manageId
    //     ? \Waka\Mailer\Models\Bloc::find($manageId)
    //     : new \Waka\Mailer\Models\Bloc;

    //     return $bloc;
    // }
    // public function relationExtendManageWidget($widget, $field, $model)
    // {
    //     $widget->bindEvent('form.extendFields', function () use ($widget) {

    //         if (!$widget->model->bloc_type) {
    //             return;
    //         }

    //         $options = [];

    //         $yaml = Yaml::parse($widget->model->bloc_type->config);

    //         $modelOptions = $yaml['model']['options'] ?? false;
    //         if ($modelOptions) {
    //             foreach ($modelOptions as $key => $opt) {
    //                 $options[$key] = $opt;
    //             }
    //         }

    //         $fields = $yaml['fields'];
    //         foreach ($fields as $field) {
    //             if ($field['options'] ?? false) {
    //                 foreach ($field['options'] as $key => $opt) {
    //                     $options[$key] = $opt;
    //                 }

    //             }
    //         }
    //         if (count($options) > 0 ?? false) {
    //             $fieldtoAdd = [
    //                 'bloc_form' => [
    //                     'tab' => 'content',
    //                     'type' => 'nestedform',
    //                     'usePanelStyles' => false,
    //                     'form' => [
    //                         'fields' => $options,
    //                     ],
    //                 ],
    //             ];
    //             $widget->addTabFields($fieldtoAdd);
    //         }

    //     });
    // }

}
