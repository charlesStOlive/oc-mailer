<?php namespace Waka\Mailer\Classes;

use Mail;
use Swift_Mailer;
use Waka\Mailer\Models\WakaMail;
use Waka\Utils\Classes\DataSource;
use Zaxbux\GmailMailerDriver\Classes\GmailTransport;

//use Zaxbux\GmailMailerDriver\Classes\GmailDraftTransport;

class MailCreator
{

    private $dataSourceModel;
    private $dataSourceId;
    private $additionalParams;
    private $dataSourceAdditionalParams;
    private $forceTo;

    //use \Waka\Cloudis\Classes\Traits\CloudisKey;

    public function __construct($mail_id)
    {
        $wakamail = WakaMail::find($mail_id);
        $this->wakamail = $wakamail;
    }

    public function setForceTo(array $data)
    {
        $this->forceTo = $value;
    }

    public function renderMail($modelId, $dataFromPopup, $test = false)
    {
        $dataFromPopup = $dataFromPopup;

        $dataSourceId = $this->wakamail->data_source_id;
        $ds = new DataSource($dataSourceId, 'id');

        $logKey = null;
        if (class_exists('\Waka\Lp\Classes\LogKey')) {
            if ($this->wakamail->use_key) {
                $logKey = new \Waka\Lp\Classes\LogKey($modelId, $this->wakamail);
                $logKey->add();
            }
        }

        $varName = strtolower($ds->name);

        $values = $ds->getValues($modelId);
        //le modele est instancié avec getValus. inutile de l'instancier.
        $img = $ds->wimages->getPicturesUrl($this->wakamail->images);
        $fnc = $ds->getFunctionsCollections($modelId, $this->wakamail->model_functions);

        $model = [
            $varName => $values,
            'IMG' => $img,
            'FNC' => $fnc,
            'log' => $logKey ? $logKey->log : null,
        ];
        //trace_log($model);
        $htmlContent = \Twig::parse($this->wakamail->html, $model);

        //trace_log($htmlContent);
        $data = [
            'content' => $htmlContent,
            'baseCss' => \File::get(plugins_path() . $this->wakamail->layout->baseCss),
            'AddCss' => $this->wakamail->layout->Addcss,
        ];
        $htmlLayout = \Twig::parse($this->wakamail->layout->contenu, $data);

        if ($test) {
            return $htmlLayout;
        }
        if (!$dataFromPopup) {
            if ($this->forceTo) {
                $dataFromPopup = $this->forceTo;
            } else {
                throw new ApplicationException("Impossible d'envoyer des données email sans les données du popup ou un forceTo");
            }
        }
        if ($this->forceTo) {
            $datasEmail['emails'] = $this->forceTo;
        }

        if ($dataSession['send_with_gmail'] ?? false) {
            //$backup = Mail::getSwiftMailer();
            $gmail = new Swift_Mailer(new GmailTransport());
            // Set the mailer as gmail
            Mail::setSwiftMailer($gmail);

            \Mail::raw(['html' => $htmlLayout], function ($message) use ($datasEmail) {
                $message->to($datasEmail['emails']);
                $message->subject($datasEmail['subject']);
            });
            //Mail::setSwiftMailer($backup);
        } else {
            \Mail::raw(['html' => $htmlLayout], function ($message) use ($datasEmail) {
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
