<?php

namespace App\Jobs;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessScheduledMonitoringJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $frequency
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting scheduled monitoring', [
            'frequency' => $this->frequency,
        ]);

        // Get all active licenses with matching monitoring frequency
        $licenses = License::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->whereHas('contents', function ($query) {
                $query->where('monitoring_enabled', true);
            })
            ->get()
            ->filter(function ($license) {
                return $license->getMonitoringFrequency() === $this->frequency;
            });

        $dispatchedCount = 0;

        foreach ($licenses as $license) {
            // Dispatch monitoring job for each license
            MonitorSiteJob::dispatch($license)
                ->onQueue('monitoring')
                ->delay(now()->addSeconds($dispatchedCount * 10)); // Stagger jobs

            $dispatchedCount++;

            // Log every 100 dispatches
            if ($dispatchedCount % 100 === 0) {
                Log::info('Dispatched monitoring jobs', [
                    'count' => $dispatchedCount,
                    'frequency' => $this->frequency,
                ]);
            }
        }

        Log::info('Scheduled monitoring dispatch completed', [
            'frequency' => $this->frequency,
            'total_dispatched' => $dispatchedCount,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessScheduledMonitoringJob failed', [
            'frequency' => $this->frequency,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'scheduled-monitoring',
            'frequency:' . $this->frequency,
        ];
    }
}
