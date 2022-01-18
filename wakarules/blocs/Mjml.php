<?php namespace Waka\Mailer\WakaRules\Blocs;

use Waka\Utils\Classes\Rules\BlocBase;
use Waka\Utils\Interfaces\Bloc as BlocInterface;

class Mjml extends BlocBase  implements BlocInterface
{

    /**
     * Returns information about this event, including name and description.
     */
    public function subFormDetails()
    {
        return [
            'name'        => 'Mjml',
            'description' => 'Du code MJML',
            'icon'        => 'icon-xml',
            'share_mode'  => 'choose',
            'premission'  => 'wcli.utils.ask.edit.admin',
            'show_attributes' => true,
        ];
    }

    public function getText()
    {
        $name = $this->host->config_data['name'] ?? 'Nom du bloc manquant';
        return "Ce bloc est exploitable dans le template en utilisant le code : {".$this->host->code."}";
    }
    
}
