<?php namespace Waka\Mailer\Classes;

use ApplicationException;
use Waka\Mailer\Models\WakaMail;
use Waka\Utils\Classes\DataSource;

//use Zaxbux\GmailMailerDriver\Classes\GmailDraftTransport;

class MailCreator
{

    private $dataSourceModel;
    private $dataSourceId;
    private $additionalParams;
    private $dataSourceAdditionalParams;
    private $forceTo;
    private $isTwigStarted;

    //use \Waka\Cloudis\Classes\Traits\CloudisKey;

    public function __construct($mail_id, $slug = false)
    {
        $wakamail;
        if ($slug) {
            trace_log($mail_id);
            $wakamail = WakaMail::where('slug', $mail_id)->first();
            if (!$wakamail) {
                throw new ApplicationException("Le code email ne fonctionne pas : " . $mail_id);
            }
        } else {
            $wakamail = WakaMail::find($mail_id);
        }
        $this->wakamail = $wakamail;
    }

    public function setForceTo(array $data)
    {
        $this->forceTo = $value;
    }

    public function renderMail($modelId, $datasEmail, $test = false)
    {
        $dataSourceId = $this->wakamail->data_source_id;
        $ds = new DataSource($dataSourceId, 'id');

        $logKey = null;
        if (class_exists('\Waka\Lp\Classes\LogKey')) {
            if ($this->wakamail->use_key) {
                $logKey = new \Waka\Lp\Classes\LogKey($modelId, $this->wakamail);
                $logKey->add();
            }
        }

        trace_log("model ID : " . $modelId);

        $varName = strtolower($ds->name);

        $values = $ds->getValues($modelId);

        //trace_log($values);
        //le modele est instancié avec getValus. inutile de l'instancier.
        $img = $ds->wimages->getPicturesUrl($this->wakamail->images);
        $fnc = $ds->getFunctionsCollections($modelId, $this->wakamail->model_functions);

        $model = [
            $varName => $values,
            'IMG' => $img,
            'FNC' => $fnc,
            'log' => $logKey ? $logKey->log : null,
        ];

        //Traitement des markup.
        $this->startTwig();

        $text = \Markdown::parse($this->wakamail->html);
        $text = html_entity_decode(preg_replace("/[\r\n]{2,}/", "\n", $text), ENT_QUOTES, 'UTF-8');
        $htmlContent = \Twig::parse($text, $model);

        //trace_log($htmlContent);

        //trace_log($htmlContent);
        $data = [
            $varName => $values,
            'content' => $htmlContent,
            'baseCss' => \File::get(plugins_path() . $this->wakamail->layout->baseCss),
            'AddCss' => $this->wakamail->layout->Addcss,
        ];
        $htmlLayout = \Twig::parse($this->wakamail->layout->contenu, $data);

        if ($test) {
            return $htmlLayout;
        }
        $this->stopTwig();

        // if (!$datasEmail) {
        //     if ($this->forceTo) {
        //         $datasEmail = $this->forceTo;
        //     } else {
        //         throw new ApplicationException("Impossible d'envoyer des données email sans les données du popup ou un forceTo");
        //     }
        // }
        // if ($this->forceTo) {
        //     $datasEmail['emails'] = $this->forceTo['email'];
        // }

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
        $htmlContent = null;
        $text = null;
        $htmlLayout = null;
        trace_log("fin du mail");

        //\Flash::info("Le(s) email(s) ont bien été envoyés ! ");
        return true;

    }

    /**
     * Temporarily registers mail based token parsers with Twig.
     * @return void
     */
    protected function startTwig()
    {
        if ($this->isTwigStarted) {
            return;
        }

        $this->isTwigStarted = true;

        $markupManager = \System\Classes\MarkupManager::instance();
        $markupManager->beginTransaction();
        $markupManager->registerTokenParsers([
            new \System\Twig\MailPartialTokenParser,
        ]);
    }

    /**
     * Indicates that we are finished with Twig.
     * @return void
     */
    protected function stopTwig()
    {
        if (!$this->isTwigStarted) {
            return;
        }

        $markupManager = \System\Classes\MarkupManager::instance();
        $markupManager->endTransaction();

        $this->isTwigStarted = false;
    }

}
