<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ReportJob;

class ReportWorkerCommand extends Command
{
    protected $signature = 'report:worker';
    protected $description = 'Process pending report jobs';

    public function handle()
    {
        $this->info('Report worker started...');

        while (true) {
            $pendingJobs = ReportJob::where('status', 'pending')->get();

            foreach ($pendingJobs as $job) {
                $this->processJob($job);
            }

            sleep(5);
        }
    }

    protected function processJob(ReportJob $job)
    {
        $job->status = 'processing';
        $job->save();

        // This is the vulnerable SQL query - the stored filter is concatenated directly
        // INTENTIONALLY VULNERABLE — SQLi training lab
        $storedFilter = $job->filter_expression;

        // The vulnerability: stored input is concatenated into SQL without parameterization
        $query = "SELECT s.*, COUNT(se.id) as event_count
                 FROM shipments s
                 LEFT JOIN shipment_events se ON s.id = se.shipment_id
                 WHERE " . $storedFilter . "
                 GROUP BY s.id
                 ORDER BY s.created_at DESC";

        try {
            $results = DB::select($query);

            $job->status = 'completed';
            $job->result_reference = 'result_' . $job->id . '_' . time();
            $job->save();

            $this->info('Report job ' . $job->id . ' completed.');
        } catch (\Exception $e) {
            $job->status = 'failed';
            $job->save();

            $this->error('Report job ' . $job->id . ' failed: ' . $e->getMessage());
        }
    }
}