<?php namespace Waka\Mailer\Updates;

//use Excel;
use Seeder;
use Waka\Mailer\Models\WakaMail;

//use System\Models\File;
//use Waka\Worder\Models\BlocType;

class CleanScopes extends Seeder
{
    public function run()
    {
        WakaMail::where('scopes', '<>', null)->update(['scopes' => null]);

    }
}
