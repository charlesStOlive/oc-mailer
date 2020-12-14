<?php namespace Waka\Mailer\Models;

use Mjml\Client as MjmlClient;
use Model;

/**
 * wakaMail Model
 */

class WakaMail extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;
    use \October\Rain\Database\Traits\Sortable;
    use \Waka\Utils\Classes\Traits\DataSourceHelpers;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'waka_mailer_waka_mails';

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
        'name' => 'required',
        'slug' => 'required|unique:waka_mailer_waka_mails',
        'subject' => 'required',
        'data_source_id' => 'required',
        'layout' => 'required',
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
        'scopes',
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
    public $hasOne = [];
    public $hasMany = [
    ];
    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsTo = [
        'layout' => [
            'Waka\Mailer\Models\Layout',
        ],
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [
    ];
    public $attachOne = [
    ];
    public $attachMany = [
    ];

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
            $this->mjml_html = $clientMjml->render($this->mjml);
        }
    }

    /**
     * LISTS
     **/

    /**
     * GETTERS
     **/

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

}
