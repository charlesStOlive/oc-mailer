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

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'mailPartial' => function ($twig, $data, $dataKey2 = null, $data2 = null) {
                    $bloc = \Waka\Mailer\Models\Bloc::where('slug', $twig)->first();
                    if ($dataKey2) {
                        $data[$dataKey2] = $data2;
                        $test = compact('data');
                    }
                    if ($bloc) {
                        $bloc_html = \Twig::parse($bloc->contenu, compact('data'));
                        return $bloc_html;
                    } else {
                        return null;
                    }
                    return null;

                },
            ],
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        // Event::listen('backend.update.prod', function ($controller) {
        //     if (get_class($controller) == 'Waka\Mailer\Controllers\WakaMails') {
        //         return;
        //     }

        //     if (in_array('Waka.Mailer.Behaviors.MailBehavior', $controller->implement)) {
        //         $data = [
        //             'model' => $modelClass = str_replace('\\', '\\\\', get_class($controller->formGetModel())),
        //             'modelId' => $controller->formGetModel()->id,
        //         ];
        //         return View::make('waka.mailer::publishMail')->withData($data);;
        //     }
        // });
        // Event::listen('popup.actions.prod', function ($controller, $model, $id) {
        //     if (get_class($controller) == 'Waka\Mailer\Controllers\WakaMails') {
        //         return;
        //     }

        //     if (in_array('Waka.Mailer.Behaviors.MailBehavior', $controller->implement)) {
        //         //trace_log("Laligne 1 est ici");
        //         $data = [
        //             'model' => str_replace('\\', '\\\\', $model),
        //             'modelId' => $id,
        //         ];
        //         return View::make('waka.mailer::publishMailContent')->withData($data);;
        //     }
        // });
        // Event::listen('backend.top.index', function ($controller) {
        //     $user = \BackendAuth::getUser();
        //     if (!$user->hasAccess('waka.importexport.exp.*')) {
        //         return;
        //     }
        //     if (get_class($controller) == 'Waka\Mailer\Controllers\WakaMails') {
        //         return;
        //     }
        //     if (in_array('Waka.Mailer.Behaviors.MailBehavior', $controller->implement)) {
        //         $data = [
        //             'model' => $modelClass = str_replace('\\', '\\\\', $controller->listGetConfig()->modelClass),
        //             //'modelId' => $controller->formGetModel()->id
        //         ];
        //         return View::make('waka.mailer::lot_mail')->withData($data);;
        //     }
        // });

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
            // 'bloc_types' => [
            //     'label' => Lang::get('waka.mailer::lang.menu.bloc_type'),
            //     'description' => Lang::get('waka.mailer::lang.menu.bloc_type_description'),
            //     'category' => Lang::get('waka.mailer::lang.menu.settings_category'),
            //     'icon' => 'icon-th-large',
            //     'url' => Backend::url('waka/mailer/bloctypes'),
            //     'permissions' => ['waka.mailer.admin'],
            //     'order' => 1,
            // ],
        ];
    }
}
