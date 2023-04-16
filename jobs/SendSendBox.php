<?php
/**
 * Copyright (c) 2018 Viamage Limited
 * All Rights Reserved
 */

namespace Waka\Mailer\Jobs;

use Waka\Wakajob\Classes\JobManager;
use Waka\Wakajob\Contracts\WakajobQueueJob;

/**
 * Class SendRequestJob
 *
 * Sends POST requests with given data to multiple target urls. Example of Wakajob Job.
 *
 * @package Waka\Wakajob\Jobs
 */
class SendSendBox implements WakajobQueueJob
{
    /**
     * @var int
     */
    public $jobId;

    /**
     * @var JobManager
     */
    public $jobManager;

    /**
     * @var array
     */
    private $data;

    /**
     * @var bool
     */
    private $updateExisting;

    /**
     * @var int
     */
    private $chunk;

    /**
     * @var string
     */
    private $table;

    /**
     * @param int $id
     */
    public function assignJobId(int $id)
    {
        $this->jobId = $id;
    }

    /**
     * SendRequestJob constructor.
     *
     * We provide array with stuff to send with post and array of urls to which we want to send
     *
     * @param array  $data
     * @param string $model
     * @param bool   $updateExisting
     * @param int    $chunk
     */
    public function __construct(array $data =  [])
    {
        $this->data = $data;
        $this->updateExisting = true;
        $this->chunk = 10;
    }

    /**
     * Job handler. This will be done in background.
     *
     * @param JobManager $jobManager
     */
    public function handle(JobManager $jobManager)
    {
        /**
         * travail preparatoire sur les donnes
         */
        $mails = \Waka\Mailer\Models\SendBox::where('state', 'Attente');
        //Ajouter ici la séléection par checkbox si besoin.
        $mails = $mails->get();
        /**
         * We initialize database job. It has been assigned ID on dispatching,
         * so we pass it together with number of all elements to proceed (max_progress)
         */
        $loop = 1;
        $jobManager->startJob($this->jobId, $mails->count());
        $send = 0;
        // Fin inistialisation

        //Travail sur les données
        $mailsChunk = $mails->chunk($this->chunk);

        //trace_log($targets);
        
        try {
            foreach ($mailsChunk as $chunk) {
                foreach ($chunk as $mail) {
                    // TACHE DU JOB
                    if ($jobManager->checkIfCanceled($this->jobId)) {
                        $jobManager->failJob($this->jobId);
                        break;
                    }
                    $jobManager->updateJobState($this->jobId, $loop);
                    /**
                     * DEBUT TRAITEMENT **************
                     */
                    //trace_log("DEBUT TRAITEMENT **************");
                    $mail->send();
                    ++$send;
                    /**
                     * FIN TRAITEMENT **************
                     */
                }
                $loop += $this->chunk;
            }
            $jobManager->updateJobState($this->jobId, $loop);
            $jobManager->completeJob(
                $this->jobId,
                [
                'Message' => $mails->count().' '. \Lang::get('waka.mailer::wakamail.job_title'),
                'waka.mailer::wakamail.job_send' => $send,
                ]
            );
        } catch (\Exception $ex) {
            \Log::error($ex->getMessage());
            $jobManager->failJob($this->jobId, ['error' => $ex->getMessage()]);
        }
    }
}
