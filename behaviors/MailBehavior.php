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

    public function getPostContent()
    {
        $modelClass = post('modelClass');
        $modelId = post('modelId');

        //trace_log($model);

        $ds = new DataSource($modelClass, 'class');
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
    public function onLotMail()
    {
        $modelClass = post('modelClass');
        $ds = new DataSource($modelClass, 'class');
        $options = $ds->getPartialIndexOptions('Waka\Mailer\Models\WakaMail');

        $this->vars['options'] = $options;
        $this->vars['mailDataWidget'] = $this->mailDataWidget;
        $this->vars['modelClass'] = $modelClass;

        return ['#popupActionContent' => $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_lot.htm')];

    }

    /**
     * Cette fonction est utilisé lors du test depuis le controller wakamail.
     */
    public function onLoadMailTestForm()
    {
        $productorId = post('productorId');

        $wakaMail = WakaMail::find($productorId);

        $dataSourceId = $wakaMail->data_source;
        $ds = new DataSource($dataSourceId);

        $options = $ds->getPartialOptions(null, 'Waka\Mailer\Models\WakaMail');

        $contact = $ds->getContact('to', null);
        $this->mailBehaviorWidget->getField('email')->options = $contact;

        $cc = $ds->getContact('cc', null);
        $this->mailBehaviorWidget->getField('cc')->hidden = true;

        $this->mailDataWidget->getField('subject')->value = $wakaMail->subject;

        //$this->getFieldFromWakaMail($wakaMail);

        $this->vars['productorId'] = $productorId;
        $this->vars['mailDataWidget'] = $this->mailDataWidget;
        $this->vars['mailBehaviorWidget'] = $this->mailBehaviorWidget;

        $this->vars['modelId'] = null;
        $this->vars['options'] = $options;

        return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_test.htm');
    }

    public function onSelectWakaMail()
    {
        $productorId = post('productorId');
        $wakaMail = WakaMail::find($productorId);

        $this->mailDataWidget->getField('subject')->value = $wakaMail->subject;

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

        $productorId = $datas['productorId'];
        $modelId = $datas['modelId'];

        if (post('testHtml')) {
            $this->vars['html'] = MailCreator::find($productorId)->renderTest($modelId);
            return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_html.htm');
        } else {
            $datasEmail = [
                'emails' => $datas['mailBehavior_array']['email'],
                'subject' => $datas['mailData_array']['subject'],
            ];
            return MailCreator::find($productorId)->renderMail($modelId, $datasEmail);
        }

    }

    public function onMailBehaviorPartialTestValidation()
    {
        //trace_log('onMailBehaviorPartialTestValidation');

        $datas = post();

        $productorId = $datas['productorId'];
        $modelId = null;

        if (post('testHtml')) {
            $this->vars['html'] = MailCreator::find($productorId)->renderTest($modelId);
            return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_html.htm');
        } else {
            $datasEmail = [
                'emails' => $datas['mailBehavior_array']['email'],
                'subject' => $datas['mailData_array']['subject'],
            ];
            return MailCreator::find($productorId)->renderMail($modelId, $datasEmail);
        }

    }

    public function onMailTestShow()
    {
        //trace_log('onMailTestShow');
        $productorId = post('productorId');
        $this->vars['html'] = MailCreator::find($productorId)->renderTest($modelId);
        return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_html.htm');

    }

    public function onLotWordValidation()
    {
        $errors = $this->CheckIndexValidation(\Input::all());
        if ($errors) {
            throw new \ValidationException(['error' => $errors]);
        }

        $lotType = post('lotType');
        $productorId = post('productorId');
        $listIds = null;
        if ($lotType == 'filtered') {
            $listIds = Session::get('lot.listId');
        } elseif ($lotType == 'checked') {
            $listIds = Session::get('lot.checkedIds');
        }
        // Session::forget('lot.listId');
        // Session::forget('lot.checkedIds');

        $datas = [
            'listIds' => $listIds,
            'productorId' => $productorId,
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
            'productorId' => 'required',
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
            'productorId' => 'required',
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
