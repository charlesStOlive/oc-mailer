<?php namespace Waka\Mailer\Models;

use Model;

/**
 * sendBox Model
 */

class SendBox extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Waka\Utils\Classes\Traits\WakAnonymize;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'waka_mailer_send_boxes';


    /**
     * @var array Guarded fields
     */
    protected $guarded = ['id'];

    /**
     * @var array Fillable fields
     */
    //protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [
        'name' => 'required',
    ];

    public $customMessages = [
        'data_source.required' => 'waka.mailer::sendbox.e.data_source',
    ];

    /**
     * @var array attributes send to datasource for creating document
     */
    public $attributesToDs = [
    ];

    /**
     * @var array field anaymisés par wakAnonymize
     */
    public $anonymizeFields = [
        'content',
        'pjs',
        'name',
        'tos',
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [
        'tos',
        'mail_vars',
        'mail_tags',
    ];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [
    ];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'send_at',
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [
    ];
    public $hasMany = [
        'mail_logs' => ['Waka\Mailer\Models\MailLog'],
    ];
    public $hasOneThrough = [
    ];
    public $hasManyThrough = [
    ];
    public $belongsTo = [
    ];
    public $belongsToMany = [
    ];        
    public $morphTo = [
        'targeteable' => [],
        'maileable' => [],

    ];
    public $morphOne = [
    ];
    public $morphMany = [
    ];
    public $attachOne = [
    ];
    public $attachMany = [
        'pjs' => ['System\Models\File', 'delete' => true],
    ];

    //startKeep/

    /**
     *EVENTS
     **/ 
    public function beforeCreate() {
        $this->state = "Attente";
    }

    /**
     * LISTS
     **/

    /**
     * GETTERS
     **/
    public function getHasPjsAttributes() {
        return $this->pjs->count();
    }

    public function getParsedTosAttribute() {
        if(is_array($this->tos)) {
            return  implode(', ',$this->tos);
        } else {
            return $this->tos;
        }
    }
    public function getParsedVarsAttribute() {
        //trace_log(implode(', ',$this->mail_vars));
        if($this->mail_vars) {
            return urldecode(http_build_query($this->mail_vars,'',', '));
        } else {
            return null;
        }
    }
    public function getLastLogAttribute() {
        return $this->mail_logs()->latest('updated_at')->first()->type ?? "Inc";
    }
    public function getMaileableNameAttribute() {
        return $this->maileable->name ?? null;
    }

    public function getTargeteableNameAttribute() {
        return $this->targeteable->name ?? 'Inc';
    }

    /**
     * SCOPES
     */

    /**
     * SETTERS
     */
 
    /**
     * FILTER FIELDS
     */

    /**
     * OTHERS
     */
    public function send() {
        if($this->state == "Envoyé") {
            $this->meta = "Email déjà envoyé il est interdit de l'envoyer de nouveau";
            $this->save();
            return false;
        }
        try {
            \Mail::raw(['html' => $this->content], function ($message) {
                //trace_log($datasEmail);
                $message->to($this->tos);
                if($this->sender) {
                    $senders = array_map('trim', explode(',', $this->sender));
                    $message->from($senders[0], $senders[1] );
                    
                }
                if($this->reply_to) {
                    $replys = array_map('trim', explode(',', $this->reply_to));
                    $message->replyTo($replys[0], $replys[1] ?? null);
                }
                $message->subject($this->name);
                $headers = $message->getSwiftMessage()->getHeaders();
                //Ajout ID dans les variables.
                $mailVars = array_merge($this->mail_vars, ['send_box_id' => $this->id]);
                //
                $headers->addTextHeader('X-Mailgun-Variables', json_encode($mailVars));
                if($this->open_log) {
                    $headers->addTextHeader('X-Mailgun-Track-Opens', true);
                }
                if($this->click_log) {
                    $headers->addTextHeader('X-Mailgun-Track-Clicks', true);
                }
                if ($this->pjs->count()) {
                    //trace_log("Il y a des pjs");
                    foreach ($this->pjs as $pj) {
                        $message->attach($pj->getLocalPath(), ['as' => $pj->title]);
                    }
                }
            });
            $this->state = 'Envoyé';
            $this->send_at = \Carbon\Carbon::now();
            $this->save();
            return true;

        } catch (Exeption $e) {
            $this->state = 'Erreur';
            $this->meta = $e;
            $this->save();
            return false;
        }
        

    }

//endKeep/
}