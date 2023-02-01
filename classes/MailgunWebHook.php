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
        $loageableType = $logVars['ds'] ?? null;
        $loageableType = $this->checkMorphMap($loageableType, true);

        $mailLogData = [
            'name' => $email,
            'send_box_id' => $logVars['send_box_id'] ?? null,
            'maileable_id' => $logVars['mail_id'] ?? null,
            'maileable_type' => $logVars['mail_type'] ?? null,//todotargeteable
            'logeable_type' => $loageableType,
            'logeable_id' => $logVars['ds_id'] ?? null,
            'meta' => $logVars['meta'] ?? null,
            'type' => $type,
        ];
        $test = MailLog::create($mailLogData);
        Event::fire('wcli.mailer.mailgun_web_hook', [$mailLogData]);
        return response()->json('Success!', 200);
    }

    public function checkMorphMap($className, $name = false) {
        if(!$className) return;
        if (substr($className, 0, 1) === "\\") {
            $className = substr($className, 1);
        }
        $morphClassMaps = \Winter\Storm\Database\Relations\Relation::morphMap();
        foreach($morphClassMaps as $morphName=>$morphClass) {
            // trace_log($morphClass ."  ==  ".$className."  ==  ".$morphName);
            if($morphClass ==  $className)  {
                return $name ? $morphName : $morphClass;
            } else if($morphName ==  $className)  {
                return $name ? $morphName : $morphClass;
            } 
           
        }
         return $className;
    }
}
