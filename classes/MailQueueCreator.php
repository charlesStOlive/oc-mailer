<?php namespace Waka\Mailer\Classes;

use Event;
use Waka\Utils\Classes\DataSource;

class MailQueueCreator
{

    public function fire($job, $data)
    {
        if ($job) {
            Event::fire('job.start.email', [$job, 'Envoi email ']);
        }

        //trace_log('in fire');

        $listIds = $data['listIds'];
        $productorId = $data['productorId'];
        //trace_log('2');
        $mc = MailCreator::find($productorId);
        //trace_log('3');
        $modelDataSource = $mc->getProductor()->data_source;
        //trace_log('4 : ' . $modelDataSource);
        $ds = new DataSource($modelDataSource);

        foreach ($listIds as $modelId) {
            //trace_log('model ID : ' . $modelId);
            $emails = $ds->getContact('to', $modelId);

            $datasEmail = [
                'emails' => $emails,
                'subject' => $data['subject'],
            ];
            //trace_log($datasEmail);
            $mc->renderMail($modelId, $datasEmail);
        }

        if ($job) {
            Event::fire('job.end.email', [$job]);
            $job->delete();
        }

    }

}
