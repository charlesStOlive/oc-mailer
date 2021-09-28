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

        if ($this->verify($request)) {
            return $next($request);
        }

        abort(Response::HTTP_FORBIDDEN);
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
        } else {
            throw new \ApplicationException('Problème webhook key manquant dans la config ou env');
        }
        return hash_hmac(
            'sha256',
            sprintf('%s%s', $request->input('signature.timestamp'), $request->input('signature.token')), $sk);
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

        return $this->buildSignature($request) === $request->input('signature.signature');
    }

}
