<?php

namespace App\Jobs;

use App\Models\DmcaRequest;
use App\Notifications\DmcaNotification;
use App\Services\DmcaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDmcaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public DmcaRequest $dmcaRequest
    ) {}

    /**
     * Execute the job.
     */
    public function handle(DmcaService $dmcaService): void
    {
        Log::info('Processing DMCA send job', [
            'dmca_id' => $this->dmcaRequest->id,
            'reference' => $this->dmcaRequest->reference_number,
            'recipient_type' => $this->dmcaRequest->recipient_type,
        ]);

        // Check if already sent
        if ($this->dmcaRequest->sent_at) {
            Log::info('DMCA already sent, skipping', [
                'dmca_id' => $this->dmcaRequest->id,
            ]);
            return;
        }

        try {
            // Handle different recipient types
            $result = match ($this->dmcaRequest->recipient_type) {
                'google' => $this->handleGoogleSubmission($dmcaService),
                'bing' => $this->handleBingSubmission($dmcaService),
                'hosting_provider', 'website_owner', 'cloudflare' => $this->sendEmail($dmcaService),
                default => $this->sendEmail($dmcaService),
            };

            if ($result['success']) {
                $this->dmcaRequest->markAsSent();

                // Notify the license owner
                $this->dmcaRequest->license->user->notify(
                    new DmcaNotification($this->dmcaRequest, 'sent')
                );

                Log::info('DMCA notice sent successfully', [
                    'dmca_id' => $this->dmcaRequest->id,
                    'method' => $result['method'] ?? 'email',
                ]);
            } else {
                Log::warning('DMCA send returned unsuccessful', [
                    'dmca_id' => $this->dmcaRequest->id,
                    'message' => $result['message'] ?? 'Unknown error',
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send DMCA notice', [
                'dmca_id' => $this->dmcaRequest->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle Google submission.
     */
    private function handleGoogleSubmission(DmcaService $dmcaService): array
    {
        // Google requires manual submission - prepare the data
        $result = $dmcaService->submitToGoogle($this->dmcaRequest);

        // Store submission info in metadata
        $this->dmcaRequest->update([
            'metadata' => array_merge(
                $this->dmcaRequest->metadata ?? [],
                [
                    'google_submission_prepared' => true,
                    'submission_url' => $result['submission_url'],
                    'prepared_at' => now()->toIso8601String(),
                ]
            ),
        ]);

        // Send email to user with instructions
        $this->dmcaRequest->license->user->notify(
            new DmcaNotification($this->dmcaRequest, 'google_prepared')
        );

        return [
            'success' => true,
            'method' => 'google_prepared',
            'message' => 'Google submission prepared and sent to user.',
        ];
    }

    /**
     * Handle Bing submission.
     */
    private function handleBingSubmission(DmcaService $dmcaService): array
    {
        // Similar to Google, Bing requires manual submission
        $this->dmcaRequest->update([
            'metadata' => array_merge(
                $this->dmcaRequest->metadata ?? [],
                [
                    'bing_submission_prepared' => true,
                    'submission_url' => 'https://www.microsoft.com/en-us/concern/dmca',
                    'prepared_at' => now()->toIso8601String(),
                ]
            ),
        ]);

        $this->dmcaRequest->license->user->notify(
            new DmcaNotification($this->dmcaRequest, 'bing_prepared')
        );

        return [
            'success' => true,
            'method' => 'bing_prepared',
            'message' => 'Bing submission prepared and sent to user.',
        ];
    }

    /**
     * Send DMCA via email.
     */
    private function sendEmail(DmcaService $dmcaService): array
    {
        // If no recipient email, try to look it up
        if (!$this->dmcaRequest->recipient_email) {
            $domain = parse_url($this->dmcaRequest->infringing_url, PHP_URL_HOST);
            $hostingInfo = $dmcaService->lookupHostingProvider($domain);

            if (!empty($hostingInfo['abuse_email'])) {
                $this->dmcaRequest->update([
                    'recipient_email' => $hostingInfo['abuse_email'],
                    'metadata' => array_merge(
                        $this->dmcaRequest->metadata ?? [],
                        ['hosting_info' => $hostingInfo]
                    ),
                ]);
            } else {
                return [
                    'success' => false,
                    'message' => 'Could not determine recipient email address.',
                ];
            }
        }

        // Send the email
        $sent = $dmcaService->sendNotice($this->dmcaRequest);

        return [
            'success' => $sent,
            'method' => 'email',
            'message' => $sent ? 'Email sent successfully.' : 'Failed to send email.',
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendDmcaJob failed permanently', [
            'dmca_id' => $this->dmcaRequest->id,
            'error' => $exception->getMessage(),
        ]);

        // Update DMCA request with failure info
        $this->dmcaRequest->update([
            'metadata' => array_merge(
                $this->dmcaRequest->metadata ?? [],
                [
                    'send_failed' => true,
                    'failure_reason' => $exception->getMessage(),
                    'failed_at' => now()->toIso8601String(),
                ]
            ),
        ]);

        // Notify user of failure
        try {
            $this->dmcaRequest->license->user->notify(
                new DmcaNotification($this->dmcaRequest, 'failed')
            );
        } catch (\Exception $e) {
            // Ignore notification errors
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'dmca',
            'dmca:' . $this->dmcaRequest->id,
            'license:' . $this->dmcaRequest->license_id,
            'type:' . $this->dmcaRequest->recipient_type,
        ];
    }
}
