<?php namespace Waka\Mailer\Classes\Middleware;

use ApplicationException;
use Event;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class MailgunWebHook 
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->isMethod('post')) {
           abort(Response::HTTP_FORBIDDEN, 'Only POST requests are allowed.');
        }

        //trace_log("verifiy");

        if ($this->verify($request)) {
            return $next($request);
        }

        return response()->json('Error signature non verifie!', 403);
    }

    /**
     * Build the signature from POST data
     *
     * @see https://documentation.mailgun.com/user_manual.html#securing-webhooks
     * @param  $request The request object
     * @return string
     */
    private function buildSignature($request)
    {
        $sk = null;
        if(\Config::get('waka.mailer::mailgun_webhooks.signing_key')) {
            $sk = \Config::get('waka.mailer::mailgun_webhooks.signing_key');
            
            $hashMac = hash_hmac(
                'sha256',
                sprintf('%s%s', $request->input('signature.timestamp'), $request->input('signature.token')), $sk);
            return $hashMac;
        } else {
            \Log::error('Problème  MAILGUN_SECRET manquant dans env');
            return null;
        }
        
    }

    /**
     * @param $request
     * @return bool
     */
    private function verify($request)
    {
        // Check if the timestamp is fresh
        if (abs(time() - $request->input('signature.timestamp')) > 15) {
            return false;
        }
        $buidSignature = null;
        try {
            $buidSignature = $this->buildSignature($request);
        } catch (Error $e) {
            \Log::error('Impossible de calculer la signature');
            return false;
        }

        if(!$buidSignature) {
            return false;
        } else {
            return $buidSignature === $request->input('signature.signature');
        }
        
        
    }

}
