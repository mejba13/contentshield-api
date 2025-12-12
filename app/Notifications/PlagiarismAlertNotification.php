<?php

namespace App\Notifications;

use App\Models\MonitoringResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PlagiarismAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public MonitoringResult $result
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $severity = $this->result->getSeverity();
        $severityEmoji = match ($severity) {
            'critical' => '🚨',
            'high' => '⚠️',
            'medium' => '📋',
            default => 'ℹ️',
        };

        return (new MailMessage)
            ->subject("{$severityEmoji} Plagiarism Alert - {$severity} severity match detected")
            ->greeting('Potential Plagiarism Detected!')
            ->line("We found content that appears to be copied from your original work.")
            ->line('')
            ->line('**Match Details:**')
            ->line("- Severity: " . ucfirst($severity))
            ->line("- Similarity: {$this->result->similarity_score}%")
            ->line("- Match Type: {$this->result->getMatchTypeLabel()}")
            ->line("- Detection Method: {$this->result->getDetectionMethodLabel()}")
            ->line('')
            ->line('**Your Original Content:**')
            ->line("- Title: {$this->result->content->post_title}")
            ->line("- URL: {$this->result->content->post_url}")
            ->line('')
            ->line('**Infringing Content:**')
            ->line("- Found at: {$this->result->found_url}")
            ->line("- Domain: {$this->result->found_domain}")
            ->line('')
            ->when($this->result->matched_excerpt, function ($message) {
                return $message
                    ->line('**Matched Excerpt:**')
                    ->line('"' . substr($this->result->matched_excerpt, 0, 200) . '..."');
            })
            ->line('')
            ->line('**Recommended Actions:**')
            ->line('1. Review the infringing content')
            ->line('2. If confirmed, consider filing a DMCA takedown')
            ->line('3. Mark as false positive if this is legitimate use')
            ->action('Review & Take Action', url('/dashboard/monitoring/' . $this->result->id))
            ->line('')
            ->line('You can also quickly file a DMCA request from your dashboard.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'result_id' => $this->result->id,
            'content_id' => $this->result->content_id,
            'found_url' => $this->result->found_url,
            'similarity_score' => $this->result->similarity_score,
            'severity' => $this->result->getSeverity(),
        ];
    }
}
