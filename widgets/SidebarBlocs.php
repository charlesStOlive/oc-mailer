<?php namespace Waka\Mailer\Widgets;

use Backend\Classes\WidgetBase;
use Waka\Utils\Classes\DataSource;

class SideBarBlocs extends WidgetBase
{
    use \Waka\Utils\Classes\Traits\StringRelation;
    /**
     * @var string A unique alias to identify this widget.
     */
    protected $defaultAlias = 'blocs';

    public $model;
    public $dataSource;

    public function init()
    {

    }

    public function render()
    {
        $controllerModel = $this->controller->formGetModel();
        $this->dataSource = new DataSource($controllerModel->data_source);
        $blocs = $this->getBlocs();
        $this->vars['blocs'] = $blocs;
        return $this->makePartial('list_blocs');

    }

    public function getBlocs()
    {
        $name = strtolower($this->dataSource->name);
        $blocs = \Waka\Mailer\Models\Bloc::get();
        return $blocs->map(function ($item, $key) use ($name) {
            $item['code'] = "{{mailPartial('" . $item['slug'] . "'," . $name . ")}}";
            return $item;
        });

    }

    public function loadAssets()
    {
        $this->addCss('css/sidebarattributes.css', 'Waka.Utils');
        $this->addJs('js/clipboard.min.js', 'Waka.Utils');
    }

}
