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
        'Backend.Behaviors.RelationController',
        'Waka.Utils.Behaviors.SideBarUpdate',
        'Waka.Mailer.Behaviors.MailBehavior',
        'Backend.Behaviors.ReorderController',
        'Waka.Utils.Behaviors.DuplicateModel',
    ];
    public $formConfig = 'config_form.yaml';
    public $btnsConfig = 'config_btns.yaml';
    public $relationConfig = 'config_relation.yaml';
    public $duplicateConfig = 'config_duplicate.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $sideBarUpdateConfig = 'config_side_bar_update.yaml';

    public $requiredPermissions = ['waka.mailer.admin.*'];
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
        // return [
        //     '#sidebar_attributes' => $this->attributesRender($this->params[0]),
        // ];
        $fieldAttributs = $this->formGetWidget()->renderField('attributs', ['useContainer' => true]);
        $fieldInfos = $this->formGetWidget()->renderField('infos', ['useContainer' => true]);
        //trace_log($fieldInfos);

        return [
            '#Form-field-WakaMail-attributs-group' => $fieldAttributs,
            '#Form-field-WakaMail-infos-group' => $fieldInfos
        ];
    }

    public function formExtendFieldsBefore($form) {
        if(!$this->user->hasAccess(['waka.mailer.admin.super'])) {
            //Le blocage du champs code de ask est fait dans le model wakaMail
            $model =  WakaMail::find($this->params[0]);
            $countAsks = 0;
            if($model->asks) {
                $countAsks = count($model->asks);
                $form->tabs['fields']['asks']['maxItems'] = $countAsks;
                $form->tabs['fields']['asks']['minItems'] = $countAsks;
            }
        }
    }
    //endKeep/
    public function onTestNewAskFnc() {
        //trace_log("asks-------------------------------------------");
        $this->transformData('waka_utils_rule_asks');
        //trace_log("fncs-------------------------------------------");
        $this->transformData('waka_utils_rule_fncs');
    }
    public function transformData($table) {
        $rows = \DB::table($table)->get();
        foreach($rows as $row) {
            //trace_log($row->code.' : '.$row->class_name.' :--------');
            $id = $row->id;
            $subForm = new $row->class_name;
            $decodedConfig = json_decode($row->config_data, true);
            $modif = false;
            if($jsons = $subForm->jsonable) {
                foreach($jsons as $jsonField) {
                    if($jsonField != 'datas') {
                        //trace_log("--analyse : ".$jsonField);
                        $field = $decodedConfig[$jsonField] ?? null;
                        if($field) {
                            $modif = true;
                            $fieldToArray = explode(',',$field);
                            //trace_log($field." a modifier en --");
                            //trace_log($fieldToArray);
                            $decodedConfig[$jsonField] = $fieldToArray;
                            
                        }
                        
                    }
                    //
                    
                }
            }
            if($row->datas) {
                $modif = true;
                //trace_log('--A copier dans le config');
                //trace_log($row->datas);
                $decodedConfig['datas'] = json_decode($row->datas);
                //trace_log("fin copie--");
            }
            if($modif) {
                $encodedConfig = json_encode($decodedConfig, JSON_UNESCAPED_SLASHES);
                //trace_log("--------config after modif---------");
                //trace_log($decodedConfig);
                //trace_log('----------------encode---------------');
                //trace_log($encodedConfig);
                \DB::table($table)->where('id', $id)->update(['config_data' => $encodedConfig]);
            }
        }
    }
}

