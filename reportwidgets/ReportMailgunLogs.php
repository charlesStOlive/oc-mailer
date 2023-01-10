<?php namespace Waka\Mailer\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Exception;

/**
 * reportMailgunLogs Report Widget
 */
class ReportMailgunLogs extends ReportWidgetBase
{
    /**
     * @var string The default alias to use for this widget
     */
    protected $defaultAlias = 'ReportMailgunLogsReportWidget';

    /**
     * Defines the widget's properties
     * @return array
     */
    public function defineProperties()
    {
        return [
            'title' => [
                'title'             => 'backend::lang.dashboard.widget_title_label',
                'default'           => 'Report Mailgun Logs Report Widget',
                'type'              => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error',
            ],
        ];
    }
    
    /**
     * Adds widget specific asset files. Use $this->addJs() and $this->addCss()
     * to register new assets to include on the page.
     * @return void
     */
    protected function loadAssets()
    {
    }
    
    /**
     * Renders the widget's primary contents.
     * @return string HTML markup supplied by this widget.
     */
    public function render()
    {
        try {
            $this->prepareVars();
        } catch (Exception $ex) {
            $this->vars['error'] = $ex->getMessage();
        }

        return $this->makePartial('reportmailgunlogs');
    }

    /**
     * Prepares the report widget view data
     */
    public function prepareVars()
    {
        
    }
}
