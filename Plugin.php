<?php namespace Waka\Mailer;

use Backend;
use Lang;
use System\Classes\PluginBase;

/**
 * Mailer Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = [
        'Waka.Utils',
        'Waka.Informer',
        'Waka.Session',
    ];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'Mailer',
            'description' => 'No description provided yet...',
            'author' => 'Waka',
            'icon' => 'icon-leaf',
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {
    }


    public function registerFormWidgets(): array
    {
        return [
            //'Waka\Mailer\FormWidgets\ShowAttributes' => 'showattributes',
            'Waka\Mailer\FormWidgets\PjList' => 'pjlist',
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        \DataSources::registerDataSources(plugins_path().'/waka/mailer/config/datasources.yaml');
        
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'Waka\Mailer\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Register model to clean.
     *
     * @return void
     */
    public function registerModelToClean()
    {
        $nbdays = \Config::get('wcli.wconfig::anonymize.sendBox', 7);
        return [
            'anonymize' => [
                \Waka\Mailer\Models\SendBox::class => [
                    'nb_day' => $nbdays,
                    'column' => 'created_at',
                ],
            ],
            'cleanSoftDelete' => [
                \Waka\Mailer\Models\WakaMail::class => 0,
            ],
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'waka.mailer.admin.super' => [
                'tab' => 'Waka - Mailer',
                'label' => 'Super Administrateur de Mailer',
            ],
            'waka.mailer.admin.base' => [
                'tab' => 'Waka - Mailer',
                'label' => 'Administrateur de Mailer',
            ],
            'waka.mailer.user' => [
                'tab' => 'Waka - Mailer',
                'label' => 'Utilisateur de Mailer',
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [];
    }

    public function registerWakaRules()
    {
        return [
            'blocs' => [
                ['\Waka\Mailer\WakaRules\Blocs\Mjml', 'onlyClass' => ['wakaMail']], 
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'wakamails' => [
                'label' => Lang::get('waka.mailer::lang.menu.wakamails'),
                'description' => Lang::get('waka.mailer::lang.menu.wakamails_description'),
                'category' => Lang::get('waka.utils::lang.menu.settings_category_model'),
                'icon' => 'icon-envelope',
                'url' => Backend::url('waka/mailer/wakamails/index/wakamails'),
                'permissions' => ['waka.mailer.admin.*'],
                'order' => 30,
            ],
            'sendBox' => [
                'label' => Lang::get('waka.mailer::lang.menu.sendbox'),
                'description' => Lang::get('waka.mailer::lang.menu.sendbox_description'),
                'category' => Lang::get('waka.utils::lang.menu.settings_controle'),
                'icon' => 'icon-envelope',
                'url' => Backend::url('waka/mailer/sendboxs'),
                'permissions' => ['waka.mailer.admin.*'],
                'order' => 30,
            ],
        ];
    }
}
