<?php namespace Waka\Mailer\Behaviors;

use Backend\Classes\ControllerBehavior;
use Session;
use Waka\Mailer\Classes\MailCreator;
use Waka\Mailer\Models\WakaMail;
use Waka\Utils\Classes\DataSource;

class MailBehavior extends ControllerBehavior
{
    use \Waka\Utils\Classes\Traits\StringRelation;
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

    // public function getDataSourceFromModel(String $model)
    // {
    //     $modelClassDecouped = explode('\\', $model);
    //     $modelClassName = array_pop($modelClassDecouped);
    //     return DataSource::where('model', '=', $modelClassName)->first();
    // }

    public function getPostContent()
    {
        $model = post('model');
        $modelId = post('modelId');

        trace_log($model);

        $ds = new DataSource($model, 'class');
        $options = $ds->getPartialOptions($modelId, 'Waka\Mailer\Models\WakaMail');
        $contact = $ds->getContact('to', $modelId);
        //
        $this->mailBehaviorWidget->getField('email')->options = $contact;

        if (class_exists('Zaxbux\GmailMailerDriver\Classes\GmailTransport')) {
            $this->mailBehaviorWidget->addFields([
                'send_with_gmail' => [
                    'label' => ' Envoyer avec GMAIL',
                    'type' => 'checkbox',
                ],
            ]);
        }

        $cc = $ds->getContact('cc', $modelId);
        //trace_log($cc);
        $this->mailBehaviorWidget->getField('cc')->options = $cc;

        $this->vars['mailBehaviorWidget'] = $this->mailBehaviorWidget;
        $this->vars['modelId'] = $modelId;
        $this->vars['options'] = $options;
    }
    /**
     * LOAD DES POPUPS
     */
    public function onLoadMailBehaviorPopupForm()
    {
        $this->getPostContent();
        return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_popup.htm');
    }
    public function onLoadMailBehaviorContentForm()
    {
        $this->getPostContent();
        return ['#popupActionContent' => $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_content.htm')];
    }

    /**
     * Traitement par lot
     */
    public function onLotWord()
    {
        $lists = $this->controller->makeLists();
        $widget = $lists[0] ?? reset($lists);
        $query = $widget->prepareQuery();
        $results = $query->get();

        $checkedIds = post('checked');

        $countCheck = null;
        if (is_countable($checkedIds)) {
            $countCheck = count($checkedIds);
        }
        Session::put('lotWord.listId', $results->lists('id'));
        Session::put('lotWord.checkedIds', $checkedIds);

        $model = post('model');
        $ds = new DataSource($model, 'class');
        $options = $ds->getPartialIndexOptions('Waka\Mailer\Models\WakaMail');

        $this->vars['options'] = $options;
        $this->vars['mailDataWidget'] = $this->mailDataWidget;
        $this->vars['all'] = $model::count();
        $this->vars['model'] = $model;
        $this->vars['filtered'] = $query->count();
        $this->vars['countCheck'] = $countCheck;

        return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_lot.htm');

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

        $dataSourceId = $wakaMail->data_source_id;
        $ds = new DataSource($dataSourceId, 'id');

        $options = $ds->getPartialOptions($modelId, 'Waka\Mailer\Models\WakaMail');

        $contact = $ds->getContact('to', $modelId);
        $this->mailBehaviorWidget->getField('email')->options = $contact;

        $cc = $ds->getContact('cc', $modelId);
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
        $errors = $this->CheckValidation($datas);

        if ($errors) {
            throw new \ValidationException(['error' => $errors]);
        }

        $wakamailId = $datas['wakamailId'];
        $modelId = $datas['modelId'];

        if (post('testHtml')) {
            $wc = new MailCreator($wakamailId);
            $this->vars['html'] = $wc->renderMail($modelId, null, true);
            return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_html.htm');
        } else {
            $datasEmail = [
                'emails' => $datas['mailBehavior_array']['email'],
                'subject' => $datas['mailData_array']['subject'],
            ];
            $wc = new MailCreator($wakamailId);
            return $wc->renderMail($modelId, $datasEmail);
        }

    }

    public function onMailTestShow()
    {
        $wakamailId = post('wakamailId');
        $modelId = null;
        //
        $wc = new MailCreator($wakamailId);
        $this->vars['html'] = $wc->renderMail($modelId, null, true);
        //
        return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_html.htm');

    }

    public function onLotWordValidation()
    {
        $errors = $this->CheckIndexValidation(\Input::all());
        if ($errors) {
            throw new \ValidationException(['error' => $errors]);
        }
        trace_log(\Input::all());

        $lotType = post('lotType');
        $wakamailId = post('wakamailId');
        $listIds = null;
        if ($lotType == 'filtered') {
            $listIds = Session::get('lotWord.listId');
        } elseif ($lotType == 'checked') {
            $listIds = Session::get('lotWord.checkedIds');
        }
        Session::forget('lotWord.listId');
        Session::forget('lotWord.checkedIds');

        $datas = [
            'listIds' => $listIds,
            'wakamailId' => $wakamailId,
            'subject' => post('mailData_array.subject'),
        ];
        $jobId = \Queue::push('\Waka\Mailer\Classes\MailQueueCreator', $datas);
        \Event::fire('job.create.imp', [$jobId, 'Import en attente ']);

    }

    /**
     * Validations
     */
    public function CheckValidation($inputs)
    {
        $rules = [
            'wakamailId' => 'required',
            'modelId' => 'required',
        ];
        $is_test = $inputs['testHtml'] ?? false;
        if (!$is_test) {
            $rules['mailData_array.subject'] = 'required | min:3';
            $rules['mailBehavior_array.email'] = 'required';
        }

        $validator = \Validator::make($inputs, $rules);

        if ($validator->fails()) {
            return $validator->messages()->first();
        } else {
            return false;
        }
    }
    public function CheckIndexValidation($inputs)
    {
        $rules = [
            'wakamailId' => 'required',
            'mailData_array.subject' => 'required | min:3',
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

    // public function makemail()
    // {
    //     $wakamailId = post('wakamailId');
    //     $modelId = post('modelId');

    // }

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
