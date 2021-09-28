<?php namespace Waka\Mailer\Classes;

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
    public function messageType(Request $request, $type)
    {
        $data = $request->all();
        trace_log($data);
        trace_log($type);
        trace_log($request->input('event-data.user-variables'));
        trace_log($request->input('signature.timestamp'));
        
        

    }


    private function processData(string $type, $data)
    {
        $storeMessageData = $this->mailgunService->store($type, $data);

        try{
            $this->alertService->sendAlert($type, $data);
        }catch (\Exception $exception){
            return response()->json('Error: ' . $exception->getMessage(), 503);
        }

        if( $storeMessageData ){
            return response()->json('Success!', 200);
        }

        return response()->json('Error!', 503);
    }

}
