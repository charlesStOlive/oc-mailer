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
        $logVars = $request->input('event-data.user-variables');
        if(!$logVars) return response()->json('Success!', 200);
        $email = $request->input('event-data.recipient');
        $test = MailLog::create([
            'name' => $email,
            'waka_mail_id' => $logVars['id'] ?? null,
            'logeable_type' => $logVars['ds'] ?? null,
            'logeable_id' => $logVars['ds_id'] ?? null,
            'type' => $type,
        ]);
        return response()->json('Success!', 200);
    }
}
