<?php namespace Waka\Mailer\Updates;

use Seeder;
use Waka\Mailer\Models\WakaMail;

class WorkChangeMarkdownU121 extends Seeder
{
    public function run()
    {
        $mails = WakaMail::get();
        if(!$mails) {
            trace_log('pas trouvé');
            return;
        }
       
        foreach($mails as $mail) {
            if(!$mail->is_mjml) {
                trace_log('pas mjml');
                $hasHtm =  str_contains($mail->html, '<p>');
                if(!$hasHtm) {
                    trace_log('pas htm');
                    $mail->html = \Markdown::parse($mail->html);
                    $mail->save();
                }
            }
            
        }
    }
}
