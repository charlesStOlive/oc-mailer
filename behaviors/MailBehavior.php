<?php namespace Waka\Mailer\Behaviors;

use Backend\Classes\ControllerBehavior;
use Redirect;
use Waka\Mailer\Classes\MailCreator;
use Waka\Mailer\Models\WakaMail;

class MailBehavior extends ControllerBehavior
{
    protected $mailBehaviorWidget;
    protected $mailDataWidget;

    public function __construct($controller)
    {
        parent::__construct($controller);
        $this->mailBehaviorWidget = $this->createMailBehaviorWidget();
        $this->mailDataWidget = $this->createMailDataWidget();
    }

    /**
     * METHODES
     */

    public function getDataSourceClassName(String $model)
    {
        $modelClassDecouped = explode('\\', $model);
        return array_pop($modelClassDecouped);

    }

    public function getDataSourceFromModel(String $model)
    {
        $modelClassName = $this->getDataSourceClassName($model);
        //On recherche le data Source depuis le nom du model
        return \Waka\Utils\Models\DataSource::where('model', '=', $modelClassName)->first();
    }

    public function getModel($model, $modelId)
    {
        $myModel = $model::find($modelId);
        return $myModel;
    }
    public function checkScopes($myModel, $scopes)
    {
        $result = false;

        $conditions = $scopes['conditions'] ?? null;
        $mode = $scopes['mode'] ?? 'all';

        //trace_log("'mode : " . $mode);

        if (!$conditions) {
            //si on ne retrouve pas les conditions on retourne true pour valider le model
            return true;
        }

        $nbConditions = count($conditions);
        $conditionsOk = [];

        foreach ($conditions as $condition) {
            $test = false;
            if (!$condition['self']) {
                $model = $this->getStringModelRelation($myModel, $condition['target']);
                $test = in_array($model->id, $condition['ids']);
            } else {
                //trace_log($condition['ids']);
                $test = in_array($myModel->id, $condition['ids']);

            }

            if ($test) {
                if ($mode == 'one') {
                    //si le test est bon et que le mode est 'one' a la première bonne valeur on retourne oui
                    return true;
                }
                //si le test est bon mais que toutes les conditions doivent être bonne  on le met dans le tableau des OK
                array_push($conditionsOk, $test);
            }
        }
        //trace_log("nbConditions : " . $nbConditions);
        //trace_log("count(conditionsOk) : " . count($conditionsOk));
        if ($nbConditions == count($conditionsOk)) {
            return true;
        } else {
            return false;
        }

    }

    public function getPartialOptions($model, $modelId)
    {
        $modelClassName = $this->getDataSourceClassName($model);

        $options = WakaMail::whereHas('data_source', function ($query) use ($modelClassName) {
            $query->where('model', '=', $modelClassName);
        });

        $myModel = $this->getModel($model, $modelId);

        $optionsList = [];

        foreach ($options->get() as $option) {
            if ($option->scopes) {
                if ($this->checkScopes($myModel, $option->scopes)) {
                    $optionsList[$option->id] = $option->name;
                }
            } else {
                $optionsList[$option->id] = $option->name;
            }
        }
        return $optionsList;

    }
    /**
     * LOAD DES POPUPS
     */
    public function onLoadMailBehaviorPopupForm()
    {
        $model = post('model');
        $modelId = post('modelId');

        $dataSource = $this->getDataSourceFromModel($model);

        $options = $this->getPartialOptions($model, $modelId);

        //trace_log("avant contact");
        $contact = $dataSource->getContact('ask_to', $modelId);
        //trace_log($contact);
        $this->mailBehaviorWidget->getField('email')->options = $contact;

        $cc = $dataSource->getContact('ask_cc', $modelId);
        //trace_log($cc);
        $this->mailBehaviorWidget->getField('cc')->options = $cc;

        $this->vars['mailBehaviorWidget'] = $this->mailBehaviorWidget;
        $this->vars['modelId'] = $modelId;
        $this->vars['options'] = $options;

        //trace_log('yo mister');

        return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_popup.htm');
    }
    public function onLoadMailBehaviorContentForm()
    {
        //trace_log("fuck");
        $model = post('model');
        $modelId = post('modelId');

        $dataSource = $this->getDataSourceFromModel($model);

        $options = $this->getPartialOptions($model, $modelId);

        $contact = $dataSource->getContact('ask_to', $modelId);

        $this->mailBehaviorWidget->getField('email')->options = $contact;

        $cc = $dataSource->getContact('ask_cc', $modelId);

        $this->mailBehaviorWidget->getField('cc')->options = $cc;

        $this->vars['mailBehaviorWidget'] = $this->mailBehaviorWidget;
        $this->vars['modelId'] = $modelId;
        $this->vars['options'] = $options;

        return [
            '#popupActionContent' => $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_content.htm'),
        ];
    }
    /**
     * Cette fonction est utilisé lors du test depuis le controller wakamail.
     */
    public function onLoadMailTestForm()
    {
        $wakamailId = post('wakamailId');
        // $model = post('model');
        // $modelId = post('modelId');

        $wakaMail = WakaMail::find($wakamailId);
        $model = $wakaMail->data_source->modelClass;
        $modelId = $model::first()->id;

        $dataSource = $wakaMail->data_source;

        $options = $this->getPartialOptions($model, $modelId);

        $contact = $dataSource->getContact('ask_to', $modelId);
        $this->mailBehaviorWidget->getField('email')->options = $contact;

        $cc = $dataSource->getContact('ask_cc', $modelId);
        $this->mailBehaviorWidget->getField('cc')->hidden = true;

        $this->mailDataWidget->getField('subject')->value = $wakaMail->subject;

        //$this->getFieldFromWakaMail($wakaMail);

        $this->vars['wakamailId'] = $wakamailId;
        $this->vars['mailDataWidget'] = $this->mailDataWidget;
        $this->vars['mailBehaviorWidget'] = $this->mailBehaviorWidget;

        $this->vars['modelId'] = $modelId;
        $this->vars['options'] = $options;

        return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_test.htm');
    }

    public function onMailTestShow()
    {
        $wakamailId = post('wakamailId');
        // $model = post('model');
        // $modelId = post('modelId');
        $wakaMail = WakaMail::find($wakamailId);
        $model = $wakaMail->data_source->modelClass;
        $modelId = $model::first()->id;
        $wc = new MailCreator($wakamailId);
        $this->vars['html'] = $wc->renderMail($modelId, true);
        return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_html.htm');

    }

    public function onSelectWakaMail()
    {
        $wakamailId = post('wakamailId');
        $wakaMail = WakaMail::find($wakamailId);

        $this->mailDataWidget->getField('subject')->value = $wakaMail->subject;

        //$this->getFieldFromWakaMail($wakaMail);

        $this->vars['mailDataWidget'] = $this->mailDataWidget;

        return [
            '#mailDataWidget' => $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_widget_data.htm'),
        ];
    }
    public function getFieldFromWakaMail($wakaMail)
    {
        $fields = $wakaMail->add_fields;
        // trace_log("fields");
        // trace_log($fields);
        foreach ($fields as $field) {
            $this->mailDataWidget->addFields([
                $field['code'] => [
                    'label' => $field['label'],
                    'type' => $field['type'],
                    'required' => $field['required'],
                ],
            ]);

        }

    }

    public function onMailBehaviorPartialValidation()
    {
        $datas = post();
        //trace_log($datas);
        // $contacts = $this->mailBehaviorWidget->getSaveData();
        // $datas = $this->mailDataWidget->getSaveData();

        $errors = $this->CheckValidation($datas);

        \Session::put('emailData', $datas);

        if ($errors) {
            throw new \ValidationException(['error' => $errors]);
        }

        //trace_log($datas);

        $wakamailId = $datas['wakamailId'];
        $modelId = $datas['modelId'];

        if (post('testHtml')) {
            $wc = new MailCreator($wakamailId);
            $this->vars['html'] = $wc->renderMail($modelId, true);
            return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_html.htm');
        } else {
            return Redirect::to('/backend/waka/mailer/wakamails/makemail/?wakamailId=' . $wakamailId . '&modelId=' . $modelId);
        }

    }

    /**
     * Validations
     */
    public function CheckValidation($inputs)
    {
        $rules = [
            'wakamailId' => 'required',
            'mailBehavior_array.email' => 'required',
            'mailData_array.subject' => 'required | min:3',
            'modelId' => 'required',
        ];

        $validator = \Validator::make($inputs, $rules);

        if ($validator->fails()) {
            return $validator->messages()->first();
        } else {
            return false;
        }
    }
    public function validationAdditionalParams($field, $input, $fieldOption = null)
    {
        $rules = [
            $field => 'required',
        ];

        $validator = \Validator::make([$input], $rules);

        if ($validator->fails()) {
            return $validator->messages()->first();
        } else {
            return false;
        }
    }

    public function makemail()
    {
        $wakamailId = post('wakamailId');
        $modelId = post('modelId');

        $wc = new MailCreator($wakamailId);
        return $wc->renderMail($modelId);
    }

    public function makeDemo()
    {
        $wakamailId = post('wakamailId');
        $modelId = post('modelId');

        $wc = new MailCreator($wakamailId);
        return $wc->renderMail($modelId);
    }

    public function createMailBehaviorWidget()
    {

        $config = $this->makeConfig('$/waka/mailer/models/wakamail/fields_for_mail.yaml');
        $config->alias = 'mailBehaviorformWidget';
        $config->arrayName = 'mailBehavior_array';
        $config->model = new WakaMail();
        $widget = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();
        return $widget;
    }
    public function createMailDataWidget()
    {

        $config = $this->makeConfig('$/waka/mailer/models/wakamail/fields_for_data_mail.yaml');
        $config->alias = 'mailDataformWidget';
        $config->arrayName = 'mailData_array';
        $config->model = new WakaMail();
        $widget = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();
        return $widget;
    }

}
