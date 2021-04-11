<?php namespace Waka\Mailer\Updates;

use Seeder;
use Waka\Mailer\Models\Layout;

class SeedUsersTable extends Seeder
{
    public function run()
    {
        // $user = User::create([
        //     'email'                 => 'user@example.com',
        //     'login'                 => 'user',
        //     'password'              => 'password123',
        //     'password_confirmation' => 'password123',
        //     'first_name'            => 'Actual',
        //     'last_name'             => 'Person',
        //     'is_activated'          => true
        // ]);
        $layountNum = Layout::count();
        if(!$layountNum) {
            $layout = new Layout();
            $layout->name ="Template de base";
            $layout->baseCss = "/wcli/wconfig/assets/css/simple_grid/email.css";
            $layout->contenu = \File::get(plugins_path('waka/mailer/updates/files/base_template.htm'));
            $layout->save();
        }
    }
}
