<?php namespace Waka\Mailer\Models;

use BackendAuth;
use Mjml\Client as MjmlClient;
use Model;

/**
 * WakaMail Model
 */
class WakaMail extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;
    use \October\Rain\Database\Traits\Sortable;
    //
    use \Waka\Informer\Classes\Traits\InformerTrait;

    use \October\Rain\Database\Traits\Sluggable;
    protected $slugs = ['slug' => 'name'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'waka_mailer_wakamails';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [
        'data_source' => 'required',
        'name' => 'required',
        'mjml' => 'required',
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = ['scopes', 'images', 'model_functions'];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

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
    public $hasOne = [];
    public $hasMany = [
        'blocs' => ['Waka\Mailer\Models\Bloc', 'delete' => true],
    ];
    public $belongsTo = [
        'data_source' => ['Waka\Utils\Models\DataSource'],
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [
        'informs' => ['Waka\Informer\Models\Inform', 'name' => 'informeable'],
    ];
    public $attachOne = [];
    public $attachMany = [];

    public function afterCreate()
    {
        if (BackendAuth::getUser()) {
            // $wp = new MailProcessor($this->id);
            // $wp->checkTags();
        }

    }
    public function beforeSave()
    {
        //transformation du mjmm en html via api mailjet.
        $applicationId = env('MJML_API_ID');
        $secretKey = env('MJML_API_SECRET');
        $client = new MjmlClient($applicationId, $secretKey);
        $this->template = $client->render($this->mjml);
    }
    //
    public function listContacts()
    {
        return \Waka\Crsm\Models\Contact::lists('name', 'id');
    }

    public function getFunctionsList()
    {
        return $this->data_source->getFunctionsList();
    }

    public function filterFields($fields, $context = null)
    {
        $functionCode = $fields->functioncode->value ?? false;
        if (!$functionCode) {
            // si on est pas dans le repeater qui a fonction code on quitte. A noter tous les repeater sont pris en compte
            return;
        }
        $fnc = $this->data_source->getFunctionClass();

        $baseAttributes = $fnc->getFunctionAttribute($functionCode);
        trace_log("Base Attribut");
        trace_log($baseAttributes);
        if (!$baseAttributes) {
            return;
        }

        foreach ($baseAttributes as $key => $attribute) {
            $fields->{$key}->readOnly = false;
            $fields->{$key}->label = $attribute;
        }

    }
//     $this->bindEvent('model.form.filterFields', function ($formWidget, $fields, $context) {
    //         // Skip nested form widgets (i.e. repeaters)
    //         if ($formWidget->isNested) { return; }

//         // do your field filtering here
    //    });
}
