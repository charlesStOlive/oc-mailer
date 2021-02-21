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
            //trace_log($mail_id);
            $wakamail = WakaMail::where('slug', $mail_id)->first();
            if (!$wakamail) {
                //trace_log("pas trouvé");
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
        $dataSourceId = $this->wakamail->data_source;
        $ds = new DataSource($dataSourceId);

        $logKey = null;
        if (class_exists('\Waka\Lp\Classes\LogKey')) {
            if ($this->wakamail->use_key) {
                $logKey = new \Waka\Lp\Classes\LogKey($modelId, $this->wakamail);
                $logKey->add();
            }
        }

        //trace_log("model ID : " . $modelId);

        $varName = strtolower($ds->name);
        $values = $ds->getValues($modelId);
        $img = $ds->wimages->getPicturesUrl($this->wakamail->images);
        $fnc = $ds->getFunctionsCollections($modelId, $this->wakamail->model_functions);

        $model = [
            $varName => $values,
            'IMG' => $img,
            'FNC' => $fnc,
            'log' => $logKey ? $logKey->log : null,
        ];

        if ($this->wakamail->is_mjml) {
            $htmlLayout = $this->renderMjml($model, $varName);
        } else {
            $htmlLayout = $this->renderHtml($model, $varName);
        }

        $pjs = [];
        if ($this->wakamail->pjs) {
            $pjs = $this->wakamail->pjs;
        }

        if ($test) {
            return $htmlLayout;
        }

        if ($dataSession['send_with_gmail'] ?? false) {
            //$backup = Mail::getSwiftMailer();
            $gmail = new Swift_Mailer(new GmailTransport());
            // Set the mailer as gmail
            Mail::setSwiftMailer($gmail);

            \Mail::raw(['html' => $htmlLayout], function ($message) use ($datasEmail, $pjs) {
                $message->to($datasEmail['emails']);
                $message->subject($datasEmail['subject']);
                if ($pjs) {
                    foreach ($pjs as $pj) {
                        // $message->attach(storage_path('app/media/cv/' . $contact->cv_name . '.pdf'));
                        // $this->resolvePj($pj);
                    }
                }
            });
            //Mail::setSwiftMailer($backup);
        } else {
            \Mail::raw(['html' => $htmlLayout], function ($message) use ($datasEmail, $pjs, $modelId) {
                $message->to($datasEmail['emails']);
                $message->subject($datasEmail['subject']);
                if ($pjs) {
                    foreach ($pjs as $pj) {
                        //$message->attach(storage_path('app/media/cv/' . $contact->cv_name . '.pdf'));
                        $mailPj = $this->resolvePj($pj, $modelId);
                        trace_log($mailPj);
                        $message->attach(storage_path('app/' . $mailPj));
                    }
                }
                //$message->attach(storage_path('app/media/cv/'.$contact->cv_name.'.pdf'));
                // if(!$isTest) {
                // //Si ce n'est pas un test on met les headers.
                //     $headers = $message->getHeaders();
                //     $headers->addTextHeader('X-Mailgun-Variables', '{"email": "'. $contact->email . '", ' .'"campaign_id": "' . $dataCampaign['id'] . '"}');
                // }
            });
        }
        trace_log("fin du mail");
        return true;
    }

    public function resolvePj($data, $modelId)
    {
        $productorId = $data['productorId'];
        trace_log('resolve PJ');
        $classProductor = $data['classType'];
        $productor = null;
        if ($classProductor == "Waka\Pdfer\Models\WakaPdf") {
            $productor = new \Waka\Pdfer\Classes\PdfCreator($productorId);
        }
        if ($classProductor == "Waka\Worder\Models\Document") {
            $productor = new \Waka\Worder\Classes\WordCreator2($productorId);
        }
        if ($productor) {
            trace_log($modelId);
            return $productor->renderTemp($modelId);
        } else {
            return null;
        }
    }

    public function renderHtml($model, $varName)
    {
        $this->startTwig();
        $text = \Markdown::parse($this->wakamail->html);
        $text = html_entity_decode(preg_replace("/[\r\n]{2,}/", "\n", $text), ENT_QUOTES, 'UTF-8');
        $htmlContent = \Twig::parse($text, $model);
        $data = [
            $varName => $model,
            'content' => $htmlContent,
            'baseCss' => \File::get(plugins_path() . $this->wakamail->layout->baseCss),
            'AddCss' => $this->wakamail->layout->Addcss,
        ];
        $htmlLayout = \Twig::parse($this->wakamail->layout->contenu, $data);
        $this->stopTwig();
        return $htmlLayout;

    }

    public function renderMjml($model)
    {
        $this->startTwig();
        $htm = $this->wakamail->mjml_html;
        //$htm = html_entity_decode(preg_replace("/[\r\n]{2,}/", "\n", $text), ENT_QUOTES, 'UTF-8');
        $htmlContent = \Twig::parse($htm, $model);
        $this->stopTwig();
        return $htmlContent;
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
