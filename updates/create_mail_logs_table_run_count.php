<?php

namespace Waka\Mailer\Updates;

use Seeder;
use Illuminate\Support\Facades\DB;
use Waka\Mailer\Models\MailLog;

class createMailLogsTableRunCount extends Seeder
{



    public function run()
    {
        $update = MailLog::where('id', '<>', null)->update(['count' => 1]);

        // $recordsToKeep = \Illuminate\Support\Facades\DB::table('waka_mailer_mail_logs')
        //     ->select(\Illuminate\Support\Facades\DB::raw('MAX(id) as id'))
        //     ->groupBy('name', 'send_box_id', 'maileable_id', 'logeable_type', 'logeable_id', 'type')
        //     ->pluck('id');

        // \Illuminate\Support\Facades\DB::table('waka_mailer_mail_logs')
        //     ->whereNotIn('id', $recordsToKeep)
        //     ->delete();
    }
}
