<?php
/**
 * Copyright (c) 2018 Viamage Limited
 * All Rights Reserved
 */

namespace Waka\Mailer\Jobs;

use Waka\Wakajob\Classes\JobManager;
use Waka\Wakajob\Classes\RequestSender;
use Waka\Wakajob\Contracts\WakajobQueueJob;
use October\Rain\Database\Model;
use Viamage\CallbackManager\Models\Rate;

/**
 * Class SendRequestJob
 *
 * Sends POST requests with given data to multiple target urls. Example of Wakajob Job.
 *
 * @package Waka\Wakajob\Jobs
 */
class SendEmails implements WakajobQueueJob
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
     * MC
     */
    private $mailCreator;

    /**
     * MC
     */
    private $ds;

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
    public function __construct(array $dataEmails)
    {
        
        $productorId = $dataEmails['productorId'];
        $this->mailCreator = MailCreator::find($productorId);

        $listIds = $dataEmails['listIds'];
        $this->data = $listIds;
        $this->updateExisting = true;
        $this->chunk = 1;
        /** @var Model $model */
        //$model = new $model();
        //$this->table = $model->getTable();
    }

    /**
     * Job handler. This will be done in background.
     *
     * @param JobManager $jobManager
     */
    public function handle(JobManager $jobManager)
    {
        /**
         * We initialize database job. It has been assigned ID on dispatching,
         * so we pass it together with number of all elements to proceed (max_progress)
         */
        $loop = 1;
        $jobManager->startJob($this->jobId, \count($this->data));
        $created = 0;
        $updated = 0;
        $skipped = 0;

        //Travail sur les données
        $data = array_chunk($this->data, $this->chunk);
        $modelDataSource = $this->mailCreator->getProductor()->data_source;
        $ds = new DataSource($modelDataSource);


        try {
            foreach ($data as $chunk) {
                foreach ($chunk as $data) {
                    if ($jobManager->checkIfCanceled($this->jobId)) {
                        $jobManager->failJob($this->jobId);
                        break;
                    }
                    // LANCEMENT EMAIL
                    $emails = $ds->getContact('to', $modelId);
                    if (!$emails) {
                        ++$skipped;
                    } else {
                        $datasEmail = [
                            'emails' => $emails,
                            'subject' => $data['subject'],
                        ];
                        $mc->renderMail($modelId, $datasEmail);
                        ++$created;
                    }
                    // Fin de lancement email
                }
                $loop += $this->chunk;
                $jobManager->updateJobState($this->jobId, $loop);
            }
        } catch (\Exception $ex) {
            $jobManager->failJob($this->jobId, ['error' => $ex->getMessage()]);
        }
        $jobManager->completeJob(
            $this->jobId,
            [
                'message' => \count($this->data).' Email envoyé',
                'Created' => $created,
                'Updated' => $updated,
                'Skipped' => $skipped,
            ]
        );
    }
}
