<?php namespace Waka\Worder\Updates;

use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Schema;
use Waka\Mailer\Models\WakaMail;
use Waka\Session\Models\WakaSession;

class ChangeWakaMailsTableU200 extends Migration
{
    public function up()
    {
        $mails = WakaMail::get();
        foreach($mails as $mail) {
            $ds = $mail->data_source;
            $testId = $mail->test_id;
            if($ds) {
                $wakaSession = new WakaSession();
                $wakaSession->data_source = $ds;
                $wakaSession->ds_id_test = $testId;
                $wakaSession->name = 'mail_'.$mail->slug;
                $wakaSession->has_ds = true;
                $wakaSession->embed_all_ds = true;
                $wakaSession->key_duration = '1y';
                $wakaSession->save();
                $mail->waka_session()->add($wakaSession);
            }
        }
    }

    public function down()
    {

    }
}