<?php namespace Waka\Mailer\Classes;

use Event;

class MailQueueCreator
{

    public function fire($job, $data)
    {
        if ($job) {
            Event::fire('job.start.email', [$job, 'Envoi email ']);
        }

        //trace_log($data);

        $listIds = $data['listIds'];
        $wakamailId = $data['wakamailId'];
        $mc = new MailCreator($wakamailId);

        foreach ($listIds as $modelId) {

            $datasEmail = [
                'emails' => $mc->getModelEmails($modelId),
                'subject' => $data['subject'],
            ];
            $mc->renderMail($modelId, $datasEmail);
        }

        if ($job) {
            Event::fire('job.end.email', [$job]);
            $job->delete();
        }

    }

}
