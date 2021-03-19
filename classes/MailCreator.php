<?php namespace Waka\Mailer\Classes;

use ApplicationException;
use Event;
use Waka\Mailer\Models\WakaMail;
use Waka\Utils\Classes\DataSource;
use Waka\Utils\Classes\TmpFiles;

//use Zaxbux\GmailMailerDriver\Classes\GmailDraftTransport;

class MailCreator extends \October\Rain\Extension\Extendable
{
    public static $wakamail;
    public $ds;
    public $modelId = null;
    private $isTwigStarted;
    public $manualData = [];
    public $implement = [];

    public static function find($mail_id, $slug = false)
    {
        $wakamail;
        if ($slug) {
            $wakamailModel = WakaMail::where('slug', $mail_id)->first();
            if (!$wakamailModel) {
                throw new ApplicationException("Le code email ne fonctionne pas : " . $mail_id);
            }
        } else {
            $wakamailModel = WakaMail::find($mail_id);
        }
        self::$wakamail = $wakamailModel;
        return new self;
    }
    public static function getProductor()
    {
        return self::$wakamail;
    }

    public function setModelId($modelId)
    {
        $this->modelId = $modelId;
        $dataSourceId = $this->getProductor()->data_source;
        $this->ds = new DataSource($dataSourceId);
        $this->ds->instanciateModel($modelId);
        return $this;
    }

    public function setModelTest()
    {
        $this->modelId = $this->getProductor()->test_id;
        $dataSourceId = $this->getProductor()->data_source;
        $this->ds = new DataSource($dataSourceId);
        $this->ds->instanciateModel($this->modelId);
        return $this;
    }

    public function checkScopes()
    {
        //trace_log('checkScopes');
        if (!$this->ds || !$this->modelId) {
            //trace_log("modelId pas instancie");
            throw new \SystemException("Le modelId n a pas ete instancié");
        }
        //trace_log('nom modèle : '.$this->ds->model);
        $scope = new \Waka\Utils\Classes\Scopes($this->getProductor(), $this->ds->model);
        //trace_log('scope calcule');
        if ($scope->checkScopes()) {
            return true;
        } else {
            return false;
        }
    }

    

    public function setManualData($data) {
        $this->manualData = array_merge($this->manualData, $data);
        return $this;
    }

    public function prepare()
    {
        if ((!$this->ds || !$this->modelId) && !count($this->manualData)) {
            throw new \ApplicationException("Le modelId n a pas ete instancié et il n' y a pas de données manuel");
        }
        $model = [];
        if($this->ds && $this->modelId) {
            $model = $this->prepareModel();
        }
        //
        if(count($this->manualData)) {
            $model = array_merge($model, $this->manualData);
        }
        //Recupère des variables par des evenements exemple LP log dans la finction boot
        $dataModelFromEvent = Event::fire('waka.productor.subscribeData', [$this]);
        if ($dataModelFromEvent[0] ?? false) {
            foreach ($dataModelFromEvent as $dataEvent) {
                $model[key($dataEvent)] = $dataEvent;
            }
        }
        if ($this->getProductor()->is_mjml) {
            return $this->renderMjml($model);
        } else {
            return $this->renderHtml($model);
        }
    }

    public function prepareModel() {
        
        $values = $this->ds->getValues($this->modelId);
        $img = $this->ds->wimages->getPicturesUrl($this->getProductor()->images);
        $fnc = $this->ds->getFunctionsCollections($this->modelId, $this->getProductor()->model_functions);
        $varName = $this->ds->code;
        //
        return [
            $varName => $values,
            'IMG' => $img,
            'FNC' => $fnc,
            //'log' => $logKey ? $logKey->log : null,
        ];

    }

    public function renderTest()
    {
        $this->setModelId($this->getProductor()->test_id);
        return $this->prepare();
    }

    public function renderNoModel() {

    }

    public function PrepareProductorMeta($datasEmail) {
        if ($this->getProductor()->pjs) {
            $pjs = $this->getProductor()->pjs;
            $datasEmail['pjs'] = $pjs;
        }
        $subject = $datasEmail['subject'] ?? null;
        if(!$subject) {
            $datasEmail['subject'] = $this->getProductor()->subject;
        }
        return $datasEmail;
    }

    public function renderMail($datasEmail = [])
    {
        $htmlLayout = $this->prepare();
        $datasEmail = $this->PrepareProductorMeta($datasEmail);
        
        \Mail::raw(['html' => $htmlLayout], function ($message) use ($datasEmail) {
            $message->to($datasEmail['emails']);
            $message->subject($datasEmail['subject']);
            $pjs = $datasEmail['pjs'] ?? null;
            if ($pjs) {
                foreach ($pjs as $pj) {
                    $message = $this->resolvePj($message, 'swift', $pj);
                }
            }
        });
        return true;
    }

    public function renderOutlook($datasEmail = [], $sendType = 'draft')
    {
        $htmlLayout = $this->prepare();
        $datasEmail = $this->PrepareProductorMeta($datasEmail);

        if(!\MsGraph::isConnected()) {
            return null;
        }
        $mail = \MsGraph::emails()
                ->to($datasEmail['emails'])
                ->subject($datasEmail['subject'])
                ->body($htmlLayout);
                
        $pjs = $datasEmail['pjs'] ?? null;
        if($pjs) {
            foreach ($pjs as $pj) {
                $mail = $this->resolvePj($mail, 'outlook', $pj);
            }
        }
        //trace_log($sendType);
        if($sendType == 'draft') {
            return $mail->make();
        } 
        if($sendType == 'send') {
            return $mail->send();
        }
        
    }

    public function renderGMail($modelId, $datasEmail)
    {
        $htmlLayout = $this->prepare($modelId);
        //trace_log('send with gmail');

        // $pjs = [];
        // if ($this->getProductor()->pjs) {
        //     $pjs = $this->getProductor()->pjs;
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
        //             $mailPj = $this->resolvePj($pj, $this->modelId);
        //             //trace_log($mailPj);
        //             $message->attach(storage_path('app/' . $mailPj));
        //         }
        //     }
        // });
        // //Mail::setSwiftMailer($backup);
        // trace_log("fin du mail");
        return true;
    }

    public function resolvePj($message, $mailResolver,  $data)
    {
        $pjToReturn = null;
        $pjsToReturn = null;
        $productorId = $data['productorId'] ?? null; 
        $classProductor = $data['classType'];
        $path = null;
        if ($classProductor == "Waka\Pdfer\Models\WakaPdf") {
            $productor = \Waka\Pdfer\Classes\PdfCreator::find($productorId);
            $tempFile = $productor->setModelId($this->modelId)->renderTemp();
            $pjToReturn =   [
                'path' => $tempFile->getFilePath(),
                ];
        }
        elseif ($classProductor == "Waka\Worder\Models\Document") {
            $productor = \Waka\Worder\Classes\WordCreator::find($productorId);
            $tempFile = $productor->setModelId($this->modelId)->renderTemp();
            $pjToReturn = [
                'path' => $tempFile->getFilePath(),
                ];
        } else {
            $dotedAttributeClass = explode(".", $classProductor);
            $type = $dotedAttributeClass[0] ?? false;
            $attribute = $dotedAttributeClass[1] ?? false;
            $model = $this->ds->model;
            //trace_log("type : ".$type);
            if ($type =='file_one') {
                //trace_log($attribute);
                //trace_log($model->name);
                $pjToReturn = [
                    'path' =>  $model->{$attribute}->getLocalPath(),
                    'name' => $file->file_name,
                ];
            }
            
            if ($type =='cloudi_one') {
                $tempFile = TmpFiles::createDirectory()->putUrlFile($model->{$attribute}->getCloudiUrl());
                $pjToReturn =  $tempFile->getFilePath();
            }
            //TRAITEMENT DES LISTES
            if ($type =='file_multi') {
                trace_log("multi");
                $multi = $model->{$attribute};
                $pjs = [];
                foreach ($multi as $key => $file) {
                    $pjsToReturn[$key] = [
                        'path' => $file->getLocalPath(),
                        'name' => $file->file_name,
                    ];
                }
            }
        }
        if($mailResolver == 'outlook') {
            $this->returnOutlookPj($message, $pjToReturn, $pjsToReturn);
        }
        if($mailResolver == 'swift') {
            trace_log('swift');
           $this->returnSwiftPj($message, $pjToReturn, $pjsToReturn);
        }
    }

    public function returnOutlookPj($message, $pjToReturn, $pjsToReturn) {
        if($pjToReturn) {
            $message-attachments([$pjToReturn['path']]);
            }
        if($pjsToReturn) {
            $allPjsPath = [];
            foreach($pjsToReturn as $pj) {
                array_push($allPjsPath, $pj['path']); 
            }
            $message-attachments($allPjsPath);
        }
    }

    public function returnSwiftPj($message, $pjToReturn, $pjsToReturn) {
        trace_log($message);
        if($pjToReturn) {
                $pjName = $pjToReturn['name'] ?? null;
                if($pjName) {
                    $message->attach($pjToReturn['path'], ['as' => $pjToReturn['name']]);
                } else {
                    $message->attach($pjToReturn['path']);
                }
            }
        if($pjsToReturn) {
            foreach ($pjsToReturn as $pjPathUnique) {
                $pjName = $pjPathUnique['name'] ?? null;
                if($pjName) {
                    $message->attach($pjPathUnique['path'], ['as' => $pjPathUnique['name']]);
                } else {
                    $message->attach($pjPathUnique['path']);
                }
            }
        }
    }

    public function renderHtml($model)
    {
        $this->startTwig();
        $text = \Markdown::parse($this->getProductor()->html);
        $text = html_entity_decode(preg_replace("/[\r\n]{2,}/", "\n", $text), ENT_QUOTES, 'UTF-8');
        $htmlContent = \Twig::parse($text, $model);
        $data = [
            'content' => $htmlContent,
            'baseCss' => \File::get(plugins_path() . $this->getProductor()->layout->baseCss),
            'AddCss' => $this->getProductor()->layout->Addcss,
        ];
        if($this->ds) {
            $varName = strtolower($this->ds->code);
            $data['data'] =  $model[$varName];
        } else {
            $data['data'] =  $model;
        }

        //trace_log($data);
        
        $htmlLayout = \Twig::parse($this->getProductor()->layout->contenu, $data);
        $this->stopTwig();
        return $htmlLayout;
    }

    public function renderMjml($model)
    {
        $this->startTwig();
        $htm = $this->getProductor()->mjml_html;
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

    public function getModelEmails()
    {
        return $this->ds->getContact('to', null);
    }
}
