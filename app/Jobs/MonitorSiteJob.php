<?php

namespace App\Jobs;

use App\Models\License;
use App\Models\ScanLog;
use App\Services\MonitoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MonitorSiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 3600; // 1 hour for full site monitoring

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public License $license
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MonitoringService $monitoringService): void
    {
        // Check if license is still valid
        if (!$this->license->isValid()) {
            Log::info('Skipping monitoring for invalid license', [
                'license_id' => $this->license->id,
                'status' => $this->license->status,
            ]);
            return;
        }

        Log::info('Starting site monitoring job', [
            'license_id' => $this->license->id,
            'plan' => $this->license->plan,
        ]);

        // Create scan log
        $scanLog = ScanLog::start($this->license->id, 'scheduled');

        try {
            // Get contents that need monitoring based on frequency
            $contents = $monitoringService->getContentsNeedingMonitoring($this->license);

            if ($contents->isEmpty()) {
                Log::info('No contents need monitoring', [
                    'license_id' => $this->license->id,
                ]);
                $scanLog->markAsCompleted(0, 0);
                return;
            }

            $totalUrlsChecked = 0;
            $totalMatchesFound = 0;

            foreach ($contents as $content) {
                // Dispatch individual scan job for each content
                ScanContentJob::dispatch($content, null, $scanLog)
                    ->onQueue('monitoring');

                // Add small delay to prevent overwhelming the queue
                usleep(100000); // 100ms
            }

            // Note: The scan log will be updated by individual ScanContentJob instances
            // For now, mark as running
            $scanLog->update([
                'metadata' => [
                    'contents_queued' => $contents->count(),
                    'queued_at' => now()->toIso8601String(),
                ],
            ]);

            Log::info('Site monitoring jobs dispatched', [
                'license_id' => $this->license->id,
                'contents_count' => $contents->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Site monitoring failed', [
                'license_id' => $this->license->id,
                'error' => $e->getMessage(),
            ]);

            $scanLog->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('MonitorSiteJob failed permanently', [
            'license_id' => $this->license->id,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'monitoring',
            'license:' . $this->license->id,
            'plan:' . $this->license->plan,
        ];
    }

    /**
     * Determine the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [
            // Add rate limiting middleware if needed
        ];
    }
}
