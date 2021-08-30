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
     ******************** LOAD DES POPUPS et du test******************************
     */

    public function onLoadMailBehaviorPopupForm()
    {
        $this->getPopUpContent();
        if($this->vars['options']) {
            return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_popup.htm');
        } else {
            return $this->makePartial('$/waka/utils/views/_popup_no_model.htm');
        }
        
    }

    public function onLoadMailBehaviorContentForm()
    {
        $content = $this->getPopUpContent();
        if($this->vars['options']) {
             return ['#popupActionContent' => $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_content.htm')];
        } else {
             return ['#popupActionContent' => $this->makePartial('$/waka/utils/views/_content_no_model.htm')];
        }
       
    }

    public function getPopUpContent()
    {
        $modelClass = post('modelClass');
        $modelId = post('modelId');
        //datasource
        $ds = new DataSource($modelClass, 'class');
        $options = $ds->getProductorOptions('Waka\Mailer\Models\WakaMail', $modelId);
        $contact = $ds->getContact('to', $modelId);
        //
        $this->mailBehaviorWidget->getField('email')->options = $contact;
        $cc = $ds->getContact('cc', $modelId);
        $this->mailBehaviorWidget->getField('cc')->options = $cc;
        //
        $this->vars['mailBehaviorWidget'] = $this->mailBehaviorWidget;
        $this->vars['modelId'] = $modelId;
        $this->vars['modelClass'] = $modelClass;
        $this->vars['options'] = $options;
    }

    /**
     * Cette fonction est utilisé lors du test depuis le controller wakamail.
     */
    public function onLoadMailTestForm()
    {
        $productorId = post('productorId');
        $wakaMail = WakaMail::find($productorId);
        $dataSourceCode = $wakaMail->data_source;
        $ds = new DataSource($dataSourceCode);
        $options = $ds->getProductorOptions('Waka\Mailer\Models\WakaMail');
        $contact = $ds->getContact('to', null);
        $this->mailBehaviorWidget->getField('email')->options = $contact;
        $cc = $ds->getContact('cc', null);
        $this->mailBehaviorWidget->getField('cc')->hidden = true;
        $this->mailDataWidget->getField('subject')->value = $wakaMail->subject;
        $this->vars['productorId'] = $productorId;
        $this->vars['mailDataWidget'] = $this->mailDataWidget;
        $this->vars['mailBehaviorWidget'] = $this->mailBehaviorWidget;
        $this->vars['modelId'] = null;
        $this->vars['options'] = $options;
        return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_test.htm');
    }
    /**
     * Cette fonction est utilisé lors du test depuis le controller wakamail.
     */
    
    public function onSelectWakaMail()
    {
        $productorId = post('productorId');
        $modelClass = post('modelClass');
        $modelId = post('modelId');
        $ds = new DataSource($modelClass, 'class');
        $wakaMail = WakaMail::find($productorId);


        $subject = $ds->dynamyseText($wakaMail->subject, $modelId);
        $this->mailDataWidget->getField('subject')->value = $subject;
        $this->vars['mailDataWidget'] = $this->mailDataWidget;

        $askDataWidget = $this->createAskDataWidget();
        $asks = $ds->getProductorAsks('Waka\Mailer\Models\WakaMail',$productorId, $modelId);
        $askDataWidget->addFields($asks);
        $this->vars['askDataWidget'] = $askDataWidget;
        return [
            '#mailDataWidget' => $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_widget_data.htm'),
            '#askDataWidget' => $this->makePartial('$/waka/utils/models/ask/_widget_ask_data.htm'),
        ];
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
            $this->vars['html'] = MailCreator::find($productorId)->setModelId($modelId)->setAsksResponse($datas['asks_array'] ?? [])->renderHtmlforTest();
            return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_html.htm');
        } else {
            $datasEmail = [
                'emails' => $datas['mailBehavior_array']['email'],
                'subject' => $datas['mailData_array']['subject']
            ];
            return MailCreator::find($productorId)->setModelId($modelId)->setAsksResponse($datas['asks_array'] ?? [])->renderMail($datasEmail);
        }
    }

    public function onMailBehaviorPartialTestValidation()
    {
        $datas = post();
        $productorId = $datas['productorId'];
        $modelId = null;
        if (post('testHtml')) {
            $this->vars['html'] = MailCreator::find($productorId)->setModelTest()->setAsksResponse($datas['asks_array'] ?? [])->renderTest();
            return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_html.htm');
        } else {
            $datasEmail = [
                'emails' => $datas['mailBehavior_array']['email'],
                'subject' => $datas['mailData_array']['subject'],
            ];
            return MailCreator::find($productorId)->setModelTest()->setAsksResponse($datas['asks_array'] ?? [])->renderMail($datasEmail);
        }
    }

    public function onMailTestShow()
    {
        //trace_log('onMailTestShow');
        $productorId = post('productorId');
        $this->vars['html'] = MailCreator::find($productorId)->setModelId($modelId)->renderTest();
        return $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_html.htm');
    }
    /**
     * Validations
     */
    public function CheckValidation($inputs)
    {
        $rules = [
            'productorId' => 'required',
            'modelId' => 'required'
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

    /**
     * ************************************Traitement par lot**********************************
     */
    public function onLotMail()
    {
        $modelClass = post('modelClass');
        $ds = new DataSource($modelClass, 'class');
        $options = $ds->getPartialIndexOptions('Waka\Mailer\Models\WakaMail');
        //
        $this->vars['options'] = $options;
        $this->vars['mailDataWidget'] = $this->mailDataWidget;
        $this->vars['modelClass'] = $modelClass;
        //
        return ['#popupActionContent' => $this->makePartial('$/waka/mailer/behaviors/mailbehavior/_lot.htm')];
    }

    public function onLotMailBehaviorValidation()
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
        Session::forget('lot.listId');
        Session::forget('lot.checkedIds');
        //
        $datas = [
            'listIds' => $listIds,
            'productorId' => $productorId,
            'subject' => post('mailData_array.subject')
        ];
        try {
            $job = new \Waka\Mailer\Jobs\SendEmails($datas);
            $jobManager = \App::make('Waka\Wakajob\Classes\JobManager');
            $jobManager->dispatch($job, "Envoi d'emails");
            $this->vars['jobId'] = $job->jobId;
        } catch (Exception $ex) {
                $this->controller->handleError($ex);
        }
        return ['#popupActionContent' => $this->makePartial('$/waka/wakajob/controllers/jobs/_confirm.htm')];
    }

    

    public function CheckIndexValidation($inputs)
    {
        $rules = [
            'productorId' => 'required',
        ];

        $validator = \Validator::make($inputs, $rules);

        if ($validator->fails()) {
            return $validator->messages()->first();
        } else {
            return false;
        }
    }

    /**
     * *********************Création des widgets****************************************
     */

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
    public function createAskDataWidget()
    {
        $config = $this->makeConfig('$/waka/utils/models/ask/empty_fields.yaml');
        $config->alias = 'askDataformWidget';
        $config->arrayName = 'asks_array';
        $config->model = new \Waka\Utils\Models\Ask();
        $widget = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();
        return $widget;
    }
}
