<?php namespace Waka\Mailer;

use Backend;
use Event;
use Lang;
use System\Classes\PluginBase;
use View;

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
            'Waka\Mailer\FormWidgets\ShowAttributes' => 'showattributes',
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        Event::listen('backend.down.update', function ($controller) {
            if (get_class($controller) == 'Waka\Mailer\Controllers\WakaMails') {
                return;
            }

            if (in_array('Waka.Mailer.Behaviors.MailBehavior', $controller->implement)) {
                $data = [
                    'model' => $modelClass = str_replace('\\', '\\\\', get_class($controller->formGetModel())),
                    'modelId' => $controller->formGetModel()->id,
                ];
                return View::make('waka.mailer::publishMail')->withData($data);;
            }
        });
        Event::listen('popup.actions.line1', function ($controller, $model, $id) {
            if (get_class($controller) == 'Waka\Mailer\Controllers\WakaMails') {
                return;
            }

            if (in_array('Waka.Mailer.Behaviors.MailBehavior', $controller->implement)) {
                //trace_log("Laligne 1 est ici");
                $data = [
                    'model' => str_replace('\\', '\\\\', $model),
                    'modelId' => $id,
                ];
                return View::make('waka.mailer::publishMailContent')->withData($data);;
            }
        });

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
                'tab' => 'Waka',
                'label' => 'Administrateur de Mailer',
            ],
            'waka.mailer.admin' => [
                'tab' => 'Waka',
                'label' => 'Administrateur de Mailer',
            ],
            'waka.mailer.user' => [
                'tab' => 'Waka',
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
                'category' => Lang::get('waka.mailer::lang.menu.settings_category'),
                'icon' => 'icon-envelope',
                'url' => Backend::url('waka/mailer/wakamails'),
                'permissions' => ['waka.mailer.admin'],
                'order' => 1,
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
