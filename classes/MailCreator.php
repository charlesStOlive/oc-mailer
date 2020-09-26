<?php namespace Waka\Mailer\Classes;

use Mail;
use Swift_Mailer;
use Waka\Mailer\Models\WakaMail;
use Zaxbux\GmailMailerDriver\Classes\GmailTransport;

//use Zaxbux\GmailMailerDriver\Classes\GmailDraftTransport;

class MailCreator
{

    private $dataSourceModel;
    private $dataSourceId;
    private $additionalParams;
    private $dataSourceAdditionalParams;

    use \Waka\Cloudis\Classes\Traits\CloudisKey;

    public function __construct($mail_id)
    {
        $wakamail = WakaMail::find($mail_id);
        $this->wakamail = $wakamail;
    }

    public function prepareCreatorVars($dataSourceId)
    {
        $this->dataSourceModel = $this->linkModelSource($dataSourceId);
        $this->dataSourceAdditionalParams = $this->dataSourceModel->hasRelationArray;
    }
    public function setAdditionalParams($additionalParams)
    {
        if ($additionalParams) {
            $this->additionalParams = $additionalParams;
        }
    }
    private function linkModelSource($dataSourceId)
    {
        $this->dataSourceId = $dataSourceId;
        // si vide on puise dans le test
        if (!$this->dataSourceId) {
            $this->dataSourceId = $this->wakamail->data_source->test_id;
        }
        //on enregistre le modèle
        //trace_log($this->wakamail->data_source->modelClass);
        return $this->wakamail->data_source->modelClass::find($this->dataSourceId);
    }

    public function getModelEmails($dataSourceId)
    {
        return $this->wakamail->data_source->getContact('ask_to', $dataSourceId);
    }

    public function renderMail($dataSourceId, $datasEmail, $test = false)
    {
        $this->prepareCreatorVars($dataSourceId);

        $logKey = null;
        if (class_exists('\Waka\Lp\Classes\LogKey')) {
            if ($this->wakamail->use_key) {
                $logKey = new \Waka\Lp\Classes\LogKey($dataSourceId, $this->wakamail);
                $logKey->add();
            }
        }

        $varName = strtolower($this->wakamail->data_source->model);

        $doted = $this->wakamail->data_source->getValues($dataSourceId);
        $img = $this->wakamail->data_source->getPicturesUrl($dataSourceId, $this->wakamail->images);
        $fnc = $this->wakamail->data_source->getFunctionsCollections($dataSourceId, $this->wakamail->model_functions);

        $model = [
            $varName => $doted,
            'IMG' => $img,
            'FNC' => $fnc,
            'log' => $logKey ? $logKey->log : null,
        ];
        //trace_log($model);
        $html = \Twig::parse($this->wakamail->template, $model);

        if ($test) {
            return $html;
        }
        if ($dataSession['send_with_gmail'] ?? false) {
            //$backup = Mail::getSwiftMailer();
            $gmail = new Swift_Mailer(new GmailTransport());
            // Set the mailer as gmail
            Mail::setSwiftMailer($gmail);

            \Mail::raw(['html' => $html], function ($message) use ($datasEmail) {
                $message->to($datasEmail['emails']);
                $message->subject($datasEmail['subject']);
            });
            //Mail::setSwiftMailer($backup);
        } else {
            \Mail::raw(['html' => $html], function ($message) use ($datasEmail) {
                $message->to($datasEmail['emails']);
                $message->subject($datasEmail['subject']);
                // if ($addPj) {
                //     $message->attach(storage_path('app/media/cv/' . $contact->cv_name . '.pdf'));
                // }
                //$message->attach(storage_path('app/media/cv/'.$contact->cv_name.'.pdf'));
                // if(!$isTest) {
                // //Si ce n'est pas un test on met les headers.
                //     $headers = $message->getHeaders();
                //     $headers->addTextHeader('X-Mailgun-Variables', '{"email": "'. $contact->email . '", ' .'"campaign_id": "' . $dataCampaign['id'] . '"}');
                // }
            });
        }

        \Flash::info("Le(s) email(s) ont bien été envoyés ! ");
        return \Redirect::back();

    }

}
