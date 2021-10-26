<?php namespace Waka\Mailer\Models;

use Model;
use Mjml\Client as MjmlClient;

/**
 * wakaMail Model
 */

class WakaMail extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\SoftDelete;
    use \Winter\Storm\Database\Traits\Sortable;
    use \Waka\Utils\Classes\Traits\DataSourceHelpers;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'waka_mailer_waka_mails';


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
        'state' => 'required',
        'name' => 'required',
        'slug' => 'required|unique:waka_mailer_waka_mails',
        'layout' => 'required',
        'subject' => 'required',
    ];

    public $customMessages = [
        'data_source.required' => 'waka.mailer::wakamail.e.data_source',
    ];

    /**
     * @var array attributes send to datasource for creating document
     */
    public $attributesToDs = [
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [
        'model_functions',
        'images',
        'pjs',
        'scopes',
        'asks',
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
        'deleted_at',
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [
    ];
    public $hasMany = [
    ];
    public $hasOneThrough = [
    ];
    public $hasManyThrough = [
    ];
    public $belongsTo = [
       'layout' => ['Waka\Mailer\Models\Layout'],
    ];
    public $belongsToMany = [
    ];        
    public $morphTo = [];
    public $morphOne = [
    ];
    public $morphMany = [
        'sendBoxs' => [
            'Waka\Mailer\Models\SendBox',
            'name' => 'maileable'
        ],
        'mailLogs' => [
            'Waka\Mailer\Models\MailLog',
            'name' => 'maileable'
        ],
        'rule_asks' => [
            'Waka\Utils\Models\RuleAsk',
            'name' => 'askeable',
            'delete' => true
        ],
        'rule_fncs' => [
            'Waka\Utils\Models\RuleFnc',
            'name' => 'fnceable',
            'delete' => true
        ],
        'rule_conditions' => [
            'Waka\Utils\Models\RuleCondition',
            'name' => 'conditioneable',
            'delete' => true
        ],
    ];
    public $attachOne = [
    ];
    public $attachMany = [
    ];

    //startKeep/
    /**
     *EVENTS
     **/
    public function beforeSave()
    {
        if ($this->is_mjml && $this->mjml) {
            //transformation du mjmm en html via api mailjet.
            $applicationId = env('MJML_API_ID');
            $secretKey = env('MJML_API_SECRET');
            $clientMjml = new MjmlClient($applicationId, $secretKey);
            //constructtion du mjml final avec les blocs.

            $this->mjml_html = $clientMjml->render($this->mjml);
        }
    }

    /**
     * LISTS
     **/
    public function listStates() {
        return \Config::get('waka.utils::basic_state');
    }

    /**
     * GETTERS
     **/


    /**
     * SCOPES
     */
    public function scopeActive($query) {
        return $query->where('state', 'Actif');

    }
    public function scopeLot($query) {
        return $query->where('is_lot',true);
    }

    /**
     * SETTERS
     */
 
    /**
     * FILTER FIELDS
     */
    public function filterFields($fields, $context = null) {
        $user = \BackendAuth::getUser();
        //La limite du  nombre de asks est géré dans le controller.
        if(!$user->hasAccess(['waka.mailer.admin.super'])) {
            if(isset($fields->code)) {
                    $fields->code->readOnly = true;
            }
            if(isset($fields->has_asks)) {
                    $fields->has_asks->readOnly = true;
            }
        }
    }

    /**
     * OTHERS
     */

//endKeep/
}
