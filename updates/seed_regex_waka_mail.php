<?php namespace Waka\Mailer\Updates;

use Seeder;
use Waka\Mailer\Models\Layout;

class SeedRegexWakaMail extends Seeder
{
    public function run()
    {
        $mails = \Waka\Mailer\Models\WakaMail::get();
        foreach($mails as $mail) {
            $html = $mail->html;
            $html = $this->transformFnc($html);
            $html = $this->addDatas($html);
            $mail->html = $html;
            $mjml = $mail->mjml;
            $mjml = $this->transformFnc($mjml);
            $mjml = $this->addDatas($mjml);
            $mail->mjml = $mjml;
            $mail->save();
        }
        
    }

    public function transformFnc($content) {
        return preg_replace('/(FNC.)/m', 'fncs.', $content );
    }

    public function addDatas($content) {
         return preg_replace('/(fncs.\w+)/m', '${1}.datas', $content );
    }
}
