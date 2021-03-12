<?php namespace Waka\Mailer\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Waka\Utils\Classes\DataSource;

/**
 * ScopesList Form Widget
 */
class PjList extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'waka_mailer_pj_list';

    /**
     * @inheritDoc
     */
    public function init()
    {
        //$this->scopesType = \Config::get('waka.utils::scopesType');
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('pjlist');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->jsonValues = $this->getLoadValue();
        $this->vars['name'] = $this->formField->getName();
        $this->vars['values'] = $this->getLoadValue();
        $this->vars['model'] = $this->model;
    }

    /**
     * @inheritDoc
     */
    public function loadAssets()
    {
        // $this->addCss('css/scopeslist.css', 'Waka.Utils');
        // $this->addJs('js/scopeslist.js', 'Waka.Utils');
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        return \Backend\Classes\FormField::NO_SAVE_DATA;
    }

    public function onShowPjList()
    {
        $modelDataSource = $this->model->data_source;
        $ds = new DataSource($modelDataSource, 'code');
        $options = $ds->getPublicationsType();
        if ($options) {
            $this->vars['pjList'] = $options;
            return $this->makePartial('popup');
        } else {
            throw new \ApplicationException("Il n' y a pas de publications disponibles pour ce type de DataSource");
        }
    }

    public function onSelectPjype()
    {
        $modelDataSource = $this->model->data_source;
        $ds = new DataSource($modelDataSource, 'code');
        $class = post('classType');
        $options = $ds->getPublicationsFromType($class);
        if ($options) {
            $this->vars['options_prod'] = $options;
            return ['#pjAttribute' => $this->makePartial('attributes')];
        } else {
            return ['#pjAttribute' => '--'];
        }
    }
    
    public function onCreatePjValidation()
    {
        //trace_log(post());
        $classType = post('classType');
        $pjData = [];
        //
        $classIsNotProductor = strpos($classType, '.');
        if ($classIsNotProductor) {
            $array = explode(".", $classType);
            // on recherche si il y a plusieurs ou un seul fichier grâce à la valeur avant le point .
            $pjData['classType'] = $classType;
            $pjData['productorId'] = null;
            $pjData['productorName'] = 'Fichier ou montage lié au modèle';
        } else {
            $pjData['classType'] = $classType;
            $pjData['productorId'] = post('productorId');
            $pjData['productorName'] = $classType::find(post('productorId'))->name;
        }
        $pjData['pjCode'] = uniqid();

        $data;
        $modelValues = $this->getLoadValue();
        if ($modelValues && count($modelValues)) {
            $datas = new \October\Rain\Support\Collection($modelValues);
        } else {
            $datas = new \October\Rain\Support\Collection();
        }
        $datas->push($pjData);

        //enregistrement du model
        $field = $this->fieldName;
        $this->model[$field] = $datas;
        $this->model->save();

        //rafraichissement de la liste
        return [
            '#pjList' => $this->makePartial('list', ['values' => $datas]),
        ];
    }

    public function onDeletePj()
    {
        $pjCode = post('pjCode');
        $datas = $this->getLoadValue();

        $updatedDatas = [];
        foreach ($datas as $key => $data) {
            if ($data['pjCode'] != $pjCode) {
                $updatedDatas[$key] = $data;
            }
        }
        //enregistrement du model
        $field = $this->fieldName;
        $this->model[$field] = $updatedDatas;
        $this->model->save();

        return [
            '#pjList' => $this->makePartial('list', ['values' => $updatedDatas]),
        ];
    }
}
