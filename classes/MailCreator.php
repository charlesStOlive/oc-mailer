<?php namespace Waka\Mailer\Classes;

use ApplicationException;
use Event;
use Waka\Mailer\Models\WakaMail;
use Waka\Utils\Classes\TmpFiles;
use Waka\Utils\Classes\ProductorCreator;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Options as DomOptions;
//use Zaxbux\GmailMailerDriver\Classes\GmailDraftTransport;

class MailCreator extends ProductorCreator
{
    public static $maileable_type;
    public $manualData = [];
    public $resolveContext = 'mail';
    
    public static function find($mail_id, $slug = false)
    {
        if ($slug) {
            $productorModel = WakaMail::where('slug', $mail_id)->first();
        } else {
            $productorModel = WakaMail::find($mail_id);
        }
        if (!$productorModel) {
            /**/trace_log("Le code ou id  email ne fonctionne pas : " . $mail_id. "vous dever entrer l'id ou le code suivi de true");
            throw new ApplicationException("Le code ou id  email ne fonctionne pas : " . $mail_id. "vous dever entrer l'id ou le code suivi de true");
        }
        self::$productor = $productorModel;
        self::$maileable_type = "Waka\Mailer\Models\WakaMail";
        return new self;
    }

    

    public function setManualData($data) {
        if($this->productorDsQuery) {
            $this->manualData = array_merge($this->productorDsQuery->toArray(), $data);
        } else {
            $this->manualData = $data;
        }
        return $this;
    }

    public function prepare()
    {
        if ((!$this->productorDs || !$this->modelId) && !count($this->manualData)) {
            throw new \ApplicationException("Le modelId n a pas ete instancié et il n' y a pas de données manuel");
        }
        $model = $this->getProductorVars();

        //Ajout des donnnées manuels
        if(count($this->manualData)) {
            $model = array_merge($model, $this->manualData);
        }
        
        if ($this->getProductor()->is_mjml) {
            return $this->renderMjml($model);
        } else {
            return $this->renderHtml($model);
        }
    }

    public function renderTest()
    {
        $testId = $this->getProductor()->waka_session?->ds_id_test;
        //
        if($testId) {
            $this->setModelId($testId);
        } else {
            $this->manualData = ['emails' => []];
        }
        return $this->prepare();
    }

    public function renderNoModel() {

    }

    public function PrepareProductorMeta($datasEmail) {
        if($this->getProductor()) {
            if ($this->getProductor()->pjs) {
                $pjs = $this->getProductor()->pjs;
                $datasEmail['pjs'] = $pjs;
            }
        }
        
        
        $subject = null;
        $subjectTemp = $datasEmail['subject'] ?? null;
        if($subjectTemp) {
            trace_log('$subjectTemp : '. $subjectTemp);
            trace_log($this->productorDsQuery->toArray());
            $subject = \Twig::parse($subjectTemp, ['ds' => $this->productorDsQuery->toArray()]);
        }  else {
            $subject = $this->createTwigStrName('subject');
        }
        $datasEmail['subject'] = $subject;
        $emails = $datasEmail['emails'] ?? $this->getDefaultEmail();
        $datasEmail['emails'] = $emails;
        return $datasEmail;
    }

    
    private function getDefaultEmail()
    {
        if($this->productorDs) {
          return  $this->productorDs->getContact('to', null)[0];
        } else {
            throw new ApplicationException("Il n y a pas de datasource connu et pas d'email reçu dans dataemail");
        }
    }

    public function renderHtmlforTest()
    {
        return  $this->prepare();
    }

    public function prepareLogs() {
        if(!$this->getProductor()->has_log) {
            return [];
        }
        return [
            'mail_type' => $this->getProductorClass(),
            'mail_id' => $this->getProductor()->id ?? null,
            'ds' => $this->productorDs->class ?? null,
            'ds_id' => $this->modelId ?? null,
        ];
        
        
    }

    public function renderMail($datasEmail = [], $forceAuto = null)
    {
       //trace_log('renderEmail');
       //trace_log($this->getProductorClass());
        try {
            $htmlLayout = $this->prepare();
            $datasEmail = $this->PrepareProductorMeta($datasEmail);
            
            $logs = $this->preparelogs();
            //trace_log($logs);
            $sender = null;
            if($this->getProductor()->has_sender) {
                $sender = $this->getProductor()->sender;
            }

            $productor = $this->getProductor();
            $sender = $productor->sender;
            $reply_to = $productor->reply_to;
            $open_log = $productor->open_log;
            $click_log = $productor->click_log;
            $is_embed = $productor->is_embed;

            $mailSendBox = \Waka\Mailer\Models\SendBox::create([
                'name' => $datasEmail['subject'],
                'content' => $htmlLayout,
                'tos' => $datasEmail['emails'],
                'mail_vars' => $logs,
                'mail_tags' => [],
                'maileable_type' => $this->getProductorClass(),
                'maileable_id' => $this->getProductor()->id ?? null,
                'targeteable_type' => $this->productorDs->class ?? Null,
                'targeteable_id' => $this->modelId ?? null,
                'sender' =>   $sender,
                'reply_to' =>   $reply_to,
                'open_log' =>   $open_log,
                'click_log' =>  $click_log,
                'is_embed' =>  $is_embed,
            ]);
            $pjs = $datasEmail['pjs'] ?? null;

            if ($pjs) {
                foreach ($pjs as $pj) {
                   $this->resolvePj($mailSendBox, 'swift', $pj);
                }
            }
            if($forceAuto) {
                if($forceAuto =="send") {
                    $mailSendBox->send();
                } else {
                    //On ne fait rien. 
                }
            } else if($this->getProductor()->auto_send) {
                $mailSendBox->send();
            }

            

            \Flash::success(trans('waka.mailer::wakamail.mail_success'));
        }
        catch (Exception $ex) {
            \Log::error($ex->getMessage());
        }
        
    }

    public function renderOutlook($datasEmail = [], $userMsId = null, $sendType = 'draft')
    {
        try {
            $htmlLayout = $this->prepare();
            $datasEmail = $this->PrepareProductorMeta($datasEmail);
            
            //trace_log("ok pour prepare");
            
            //trace_log("ok pour data email ensuite connect");
            if(!\MsGraphAdmin::isConnected()) {
                throw new ApplicationException('MsGraphAdmin not connected');
            }
            if(!$userMsId) {
                throw new ApplicationException('Missing userMsId in renderOutlook');
            }

            
            
            $mail = \MsGraphAdmin::emails()
                    ->userid($userMsId)
                    ->to($datasEmail['emails'])
                    ->subject($datasEmail['subject']);
            
            
                    // ->body($htmlLayout);
            // GESTION SI EMBED IMAGE 
            if($this->getProductor()->is_embed) {
                //trace_log("IS EMBED");
                $tempFiles = new \Waka\Utils\Models\TempFile;
                $regex = '/<img\s.*?src=(?:\'|")([^\'">]+)(?:\'|")/';
                $htmlCorrected = preg_replace_callback($regex, function($match) use($tempFiles) {
                    $file = new \System\Models\File;
                    $srcUrl = $match[1];
                    if(empty($srcUrl)) {
                        return $match[0];
                    } else {
                        if(!starts_with($srcUrl, 'https'))  {
                            $srcUrl = url($srcUrl);
                        }
                        $file->fromUrl($srcUrl);
                        $tempFiles->files()->add($file);
                        $path = $file->getLocalPath();
                        $cid = uniqid();
                        $this->cids[$cid] = $path;
                        $match[0] = str_replace($match[1], 'cid:'.$cid,  $match[0] );
                        return $match[0];
                    };
                }, $htmlLayout);
                //trace_log($this->cids);
                $mail->attachmentsInLine($this->cids);
                $mail->body($htmlCorrected);
                //trace_log($htmlCorrected);
            } else {
                $mail->body($htmlLayout);
            }
                    
            $pjs = $datasEmail['pjs'] ?? null;
            if($pjs) {
                foreach ($pjs as $pj) {
                    $mail = $this->resolvePj($mail, 'outlook', $pj);
                }
            }
            //trace_log("pj ok");

            //trace_log($sendType);
            if($sendType == 'draft') {
                //trace_log("J'envoi le mail en brouillon");
                return $mail->make();
            } 
            if($sendType == 'send') {
                return $mail->send();
            }
            $tempFiles->delete();
        }
        
        catch (Exception $ex) {
            \Flash::error($ex->getMessage());
        }
        
    }

    public $cids = [];
    public function addCids() {

    }

    public function getCids() {

    }

    public function resolvePj($message, $mailResolver,  $data)
    {
        $pjToReturn = null;
        $pjsToReturn = null;
        $productorId = $data['productorId'] ?? null; 
        $classProductor = $data['classType'];
        $forcedPjName = $data['force_pj_name'] ?? null;
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
            $model = $this->productorDsQuery;
            //trace_log($this->productorDsQuery);
            //trace_log("type : ".$type);
            if ($type =='file_one') {
                //trace_log($attribute);
                //trace_log($model->name);
                $file = $model->{$attribute};
                $path = $file->getLocalPath();
                $extension = pathinfo($path)['extension'];
                $name = $forcedPjName ? $forcedPjName.'.'.$extension : $file->file_name;
                $pjToReturn = [
                    'path' =>  $path,
                    'name' => $name,
                ];
            }
            
            if ($type =='cloudi_one') {
                $cloudiFile = $model->{$attribute};
                $tempFile = TmpFiles::createDirectory()->putUrlFile($cloudiFile->getCloudiUrl());
                $path = $tempFile->getFilePath();
                $extension = pathinfo($path)['extension'];
                $name = $forcedPjName ? $forcedPjName.'.'.$extension : $cloudiFile->file_name;
                $pjToReturn = [
                    'path' =>  $path,
                    'name' => $name,
                ];
            }
            //TRAITEMENT DES LISTES
            if ($type =='file_multi') {
                //trace_log("multi");
                $multi = $model->{$attribute};
                $pjs = [];
                foreach ($multi as $key => $file) {
                    $path = $file->getLocalPath();
                    $extension = pathinfo($path)['extension'];
                    $name = $forcedPjName ? $forcedPjName.'_'.$key.'.'.$extension : $file->file_name;
                    $pjsToReturn[$key] = [
                        'path' => $path,
                        'name' => $name,
                    ];
                }
            }
        }
        if($mailResolver == 'outlook') {
            return $this->returnOutlookPj($message, $pjToReturn, $pjsToReturn);
        }
        if($mailResolver == 'swift') {
           $this->returnMailFile($message, $pjToReturn, $pjsToReturn);
        }
    }

    public function returnOutlookPj($message, $pjToReturn, $pjsToReturn) {
        if($pjToReturn) {
            return $message->attachments([$pjToReturn['path']]);
            }
        if($pjsToReturn) {
            $allPjsPath = [];
            foreach($pjsToReturn as $pj) {
                array_push($allPjsPath, $pj['path']); 
            }
            return $message->attachments($allPjsPath);
        }
    }

    public function returnMailFile($mail, $pjToReturn, $pjsToReturn) {
        //trace_log($pjsToReturn);
        if($pjToReturn) {
                $pjName = $pjToReturn['name'] ?? null;
                $file = new \System\Models\File;
                $file->data = $pjToReturn['path'];
                $file->title = $pjName;
                $file->is_public = false;
                //trace_log($file->toArray());
                $mail->pjs()->add($file);
            }
        if($pjsToReturn) {
            foreach ($pjsToReturn as $pjPathUnique) {
                $pjName = $pjPathUnique['name'] ?? null;
                $file = new \System\Models\File;
                $file->data = $pjPathUnique['path'];
                $file->title = $pjName;
                $file->is_public = false;
                //trace_log($file->toArray());
                $mail->pjs()->add($file);
            }
        }
    }

    public function renderHtml($model)
    {
        
        $text = $this->getProductor()->html;
        $htmlContent = \Twig::parse($text, $model);
        $data = [
            'subject' => $this->getProductor()->toArray(),
            'content' => $htmlContent,
            'baseCss' => \File::get(plugins_path() . $this->getProductor()->layout->baseCss),
            'AddCss' => $this->getProductor()->layout->Addcss,
        ];
        //trace_log($data);
        if($this->productorDs) {
            $data['data'] =  $model['ds'];
        } else {
            $data['data'] =  $model;
        }

        //trace_log($data);
        
        $htmlLayout = \Twig::parse($this->getProductor()->layout->contenu, $data);
        
        return $htmlLayout;
    }

    public function renderMjml($model)
    {
        
        $htm = $this->getProductor()->mjml_html;
        //$htm = html_entity_decode(preg_replace("/[\r\n]{2,}/", "\n", $text), ENT_QUOTES, 'UTF-8');
        $htmlContent = \Twig::parse($htm, $model);
        
        return $htmlContent;
    }

    

    public function getModelEmails()
    {
        return $this->productorDs->getContact('to', null);
    }
}
