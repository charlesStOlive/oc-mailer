<?php namespace Waka\Mailer\Classes;

use ApplicationException;
use Waka\Mailer\Models\WakaMail;
use Waka\Utils\Classes\DataSource;

//use Zaxbux\GmailMailerDriver\Classes\GmailDraftTransport;

class MailCreator extends \October\Rain\Extension\Extendable
{

    private $dataSourceModel;
    private $dataSourceId;
    private $additionalParams;
    private $dataSourceAdditionalParams;
    public static $wakamail;
    public $dataForEmail;
    //private $forceTo;
    private $isTwigStarted;

    public $implement = [

    ];

    public static function find($mail_id, $slug = false)
    {
        $wakamail;
        if ($slug) {
            $wakamail = WakaMail::where('slug', $mail_id)->first();
            if (!$wakamail) {
                throw new ApplicationException("Le code email ne fonctionne pas : " . $mail_id);
            }
        } else {
            $wakamail = WakaMail::find($mail_id);
        }
        self::$wakamail = $wakamail;
        return new self;
    }

    public function prepare($modelId)
    {
        $dataSourceId = self::$wakamail->data_source;
        $ds = new DataSource($dataSourceId);

        $logKey = null;
        if (class_exists('\Waka\Lp\Classes\LogKey')) {
            if (self::$wakamail->use_key) {
                $logKey = new \Waka\Lp\Classes\LogKey($modelId, self::$wakamail);
                $logKey->add();
            }
        }

        $varName = strtolower($ds->name);
        $values = $ds->getValues($modelId);
        $img = $ds->wimages->getPicturesUrl(self::$wakamail->images);
        $fnc = $ds->getFunctionsCollections($modelId, self::$wakamail->model_functions);

        $model = [
            $varName => $values,
            'IMG' => $img,
            'FNC' => $fnc,
            'log' => $logKey ? $logKey->log : null,
        ];

        if (self::$wakamail->is_mjml) {
            return $this->renderMjml($model, $varName);
        } else {
            return $this->renderHtml($model, $varName);
        }

    }

    public function renderTest($modelId)
    {
        return $this->prepare($modelId);
    }

    public function renderMail($modelId, $datasEmail)
    {
        $htmlLayout = $this->prepare($modelId);

        $pjs = [];
        if (self::$wakamail->pjs) {
            $pjs = self::$wakamail->pjs;
        }

        \Mail::raw(['html' => $htmlLayout], function ($message) use ($datasEmail, $pjs, $modelId) {
            $message->to($datasEmail['emails']);
            $message->subject($datasEmail['subject']);
            if ($pjs) {
                foreach ($pjs as $pj) {
                    $mailPj = $this->resolvePj($pj, $modelId);
                    trace_log($mailPj);
                    $message->attach(storage_path('app/' . $mailPj));
                }
            }
        });
        trace_log("fin du mail");
        return true;
    }

    public function renderGMail($modelId, $datasEmail)
    {
        $htmlLayout = $this->prepare($modelId);
        trace_log('send with gmail');

        // $pjs = [];
        // if (self::$wakamail->pjs) {
        //     $pjs = self::$wakamail->pjs;
        // }
        // //$backup = Mail::getSwiftMailer();
        // $gmail = new Swift_Mailer(new GmailTransport());
        // // Set the mailer as gmail
        // Mail::setSwiftMailer($gmail);

        // \Mail::raw(['html' => $htmlLayout], function ($message) use ($datasEmail, $pjs) {
        //     $message->to($datasEmail['emails']);
        //     $message->subject($datasEmail['subject']);
        //     if ($pjs) {
        //         foreach ($pjs as $pj) {
        //             $mailPj = $this->resolvePj($pj, $modelId);
        //             trace_log($mailPj);
        //             $message->attach(storage_path('app/' . $mailPj));
        //         }
        //     }
        // });
        // //Mail::setSwiftMailer($backup);
        // trace_log("fin du mail");
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
        $text = \Markdown::parse(self::$wakamail->html);
        $text = html_entity_decode(preg_replace("/[\r\n]{2,}/", "\n", $text), ENT_QUOTES, 'UTF-8');
        $htmlContent = \Twig::parse($text, $model);
        $data = [
            $varName => $model,
            'content' => $htmlContent,
            'baseCss' => \File::get(plugins_path() . self::$wakamail->layout->baseCss),
            'AddCss' => self::$wakamail->layout->Addcss,
        ];
        $htmlLayout = \Twig::parse(self::$wakamail->layout->contenu, $data);
        $this->stopTwig();
        return $htmlLayout;

    }

    public function renderMjml($model)
    {
        $this->startTwig();
        $htm = self::$wakamail->mjml_html;
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
