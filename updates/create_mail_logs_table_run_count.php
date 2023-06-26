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

        // $latestMailLogs = \Illuminate\Support\Facades\DB::table('waka_mailer_mail_logs')
        //     ->selectRaw('MAX(created_at) as latest_created_at')
        //     ->groupBy('send_box_id', 'maileable_id', 'logeable_type', 'logeable_id', 'name', 'type');

        // $mailLogs = Illuminate\Support\Facades\DB::table('waka_mailer_mail_logs')
        //     ->joinSub($latestMailLogs, 'latest_logs', function ($join) {
        //         $join->on('mail_logs.created_at', '=', 'latest_logs.latest_created_at');
        //     })
        //     ->select('waka_mailer_mail_logs.*', \Illuminate\Support\Facades\DB::raw('SUM(count) as count_sum'))
        //     ->groupBy('send_box_id', 'maileable_id', 'logeable_type', 'logeable_id', 'name', 'type')
        //     ->get();

        // trace_log($mailLogs->toArray());

        // $mailLogs->each(function ($group) {
        //     $countSum = $group->sum('count');
        //     $lastLog = $group->first();
        //     $lastLog->count = $countSum;
        //     $lastLog->save();
        // });

        // $mailLogs->except(0)->each(function ($log) {
        //     $log->delete();
        // });
    }
}
