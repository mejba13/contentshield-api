<?php

namespace App\Jobs;

use App\Models\Content;
use App\Models\ScanLog;
use App\Services\MonitoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScanContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Content $content,
        public ?string $specificUrl = null,
        public ?ScanLog $scanLog = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(MonitoringService $monitoringService): void
    {
        Log::info('Starting content scan job', [
            'content_id' => $this->content->id,
            'specific_url' => $this->specificUrl,
        ]);

        try {
            // If scanning a specific URL
            if ($this->specificUrl) {
                $result = $monitoringService->scanUrl(
                    $this->content,
                    $this->specificUrl,
                    $this->scanLog
                );

                if ($this->scanLog) {
                    $this->scanLog->incrementUrlsChecked();
                    if ($result) {
                        $this->scanLog->incrementMatchesFound();
                    }
                }

                $this->content->markAsMonitored();

                if ($this->scanLog && !$this->specificUrl) {
                    $this->scanLog->markAsCompleted(1, $result ? 1 : 0);
                }

                return;
            }

            // Perform Google search for potential plagiarism
            $searchResults = $monitoringService->searchGoogle($this->content);

            $urlsChecked = 0;
            $matchesFound = 0;

            foreach ($searchResults as $result) {
                $match = $monitoringService->scanUrl(
                    $this->content,
                    $result['url'],
                    $this->scanLog
                );

                $urlsChecked++;

                if ($match) {
                    $matchesFound++;
                }

                // Rate limiting between requests
                usleep(500000); // 500ms delay
            }

            // Mark content as monitored
            $this->content->markAsMonitored();

            // Update scan log if provided
            if ($this->scanLog) {
                $this->scanLog->markAsCompleted($urlsChecked, $matchesFound);
            }

            Log::info('Content scan completed', [
                'content_id' => $this->content->id,
                'urls_checked' => $urlsChecked,
                'matches_found' => $matchesFound,
            ]);

        } catch (\Exception $e) {
            Log::error('Content scan failed', [
                'content_id' => $this->content->id,
                'error' => $e->getMessage(),
            ]);

            if ($this->scanLog) {
                $this->scanLog->markAsFailed($e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ScanContentJob failed permanently', [
            'content_id' => $this->content->id,
            'error' => $exception->getMessage(),
        ]);

        if ($this->scanLog) {
            $this->scanLog->markAsFailed('Job failed after max retries: ' . $exception->getMessage());
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'scan',
            'content:' . $this->content->id,
            'license:' . $this->content->license_id,
        ];
    }
}
