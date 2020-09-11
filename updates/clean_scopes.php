<?php namespace Waka\Mailer\Updates;

//use Excel;
use Seeder;
use Waka\Mailer\Models\WakaMail;

//use System\Models\File;
//use Waka\Worder\Models\BlocType;

// use Waka\Crsm\Classes\CountryImport;

class CleanScopes extends Seeder
{
    public function run()
    {
        //$this->call('Waka\Crsm\Updates\Seeders\SeedWorder');
        WakaMail::where('scopes', '<>', null)->update(['scopes' => null]);

    }
}
