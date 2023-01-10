<?php namespace Waka\Mailer\Classes;

use ApplicationException;
use Event;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Waka\Mailer\Models\MailLog;

class MailgunWebHook 
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     * [user-variables] => Array
     * [id] => 9
     * [ds] => Wcli\Crm\Models\Contact
     * [ds_id] => 52
     * 
     */
    public function messageType(Request $request, $type)
    {
        //trace_log('messageType');
        $logVars = $request->input('event-data.user-variables');
        //trace_log($logVars);
        if(!$logVars) {
            \Log::error('Pas de log var on enregistre rien dans MailgunWebHook');
            return response()->json('Success!', 200);
        }
        $email = $request->input('event-data.recipient');
        //trace_log($email);
        $mailLogData = [
            'name' => $email,
            'send_box_id' => $logVars['send_box_id'] ?? null,
            'maileable_id' => $logVars['mail_id'] ?? null,
            'maileable_type' => $logVars['mail_type'] ?? null,
            'logeable_type' => $logVars['ds'] ?? null,
            'logeable_id' => $logVars['ds_id'] ?? null,
            'meta' => $logVars['meta'] ?? null,
            'type' => $type,
        ];
        $test = MailLog::create($mailLogData);
        Event::fire('wcli.mailer.mailgun_web_hook', [$mailLogData]);
        return response()->json('Success!', 200);
    }
}
