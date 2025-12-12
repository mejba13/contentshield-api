<?php

namespace App\Notifications;

use App\Models\DmcaRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DmcaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public DmcaRequest $dmcaRequest,
        public string $type
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
        return match ($this->type) {
            'sent' => $this->sentMail(),
            'google_prepared' => $this->googlePreparedMail(),
            'bing_prepared' => $this->bingPreparedMail(),
            'failed' => $this->failedMail(),
            'resolved' => $this->resolvedMail(),
            default => $this->defaultMail(),
        };
    }

    /**
     * DMCA sent notification.
     */
    private function sentMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('DMCA Notice Sent - ' . $this->dmcaRequest->reference_number)
            ->greeting('DMCA Notice Sent Successfully')
            ->line('Your DMCA takedown notice has been sent.')
            ->line('')
            ->line('**Details:**')
            ->line("- Reference: {$this->dmcaRequest->reference_number}")
            ->line("- Infringing URL: {$this->dmcaRequest->infringing_url}")
            ->line("- Recipient: {$this->dmcaRequest->getRecipientTypeLabel()}")
            ->line("- Sent At: " . $this->dmcaRequest->sent_at?->format('F j, Y g:i A'))
            ->line('')
            ->line('We will notify you when we receive a response.')
            ->action('View DMCA Request', url('/dashboard/dmca/' . $this->dmcaRequest->id));
    }

    /**
     * Google submission prepared notification.
     */
    private function googlePreparedMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Google DMCA Submission Prepared - ' . $this->dmcaRequest->reference_number)
            ->greeting('Your Google DMCA Submission is Ready')
            ->line('Google requires manual submission through their legal portal. We have prepared everything for you.')
            ->line('')
            ->line('**What to do:**')
            ->line('1. Click the button below to go to Google\'s DMCA form')
            ->line('2. Sign in with your Google account')
            ->line('3. Fill in the form using the information from your DMCA request')
            ->line('')
            ->line('**Your Prepared Information:**')
            ->line("- Original URL: {$this->dmcaRequest->original_url}")
            ->line("- Infringing URL: {$this->dmcaRequest->infringing_url}")
            ->line("- Reference: {$this->dmcaRequest->reference_number}")
            ->action('Submit to Google', 'https://support.google.com/legal/contact/lr_dmca')
            ->line('')
            ->line('You can also view the full DMCA notice in your dashboard.');
    }

    /**
     * Bing submission prepared notification.
     */
    private function bingPreparedMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('Bing DMCA Submission Prepared - ' . $this->dmcaRequest->reference_number)
            ->greeting('Your Bing DMCA Submission is Ready')
            ->line('Bing requires manual submission through Microsoft\'s legal portal. We have prepared everything for you.')
            ->line('')
            ->line('**Your Prepared Information:**')
            ->line("- Original URL: {$this->dmcaRequest->original_url}")
            ->line("- Infringing URL: {$this->dmcaRequest->infringing_url}")
            ->line("- Reference: {$this->dmcaRequest->reference_number}")
            ->action('Submit to Bing', 'https://www.microsoft.com/en-us/concern/dmca');
    }

    /**
     * DMCA failed notification.
     */
    private function failedMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('DMCA Notice Failed - ' . $this->dmcaRequest->reference_number)
            ->error()
            ->greeting('DMCA Notice Could Not Be Sent')
            ->line('Unfortunately, we were unable to send your DMCA takedown notice.')
            ->line('')
            ->line("**Reference:** {$this->dmcaRequest->reference_number}")
            ->line("**Infringing URL:** {$this->dmcaRequest->infringing_url}")
            ->line('')
            ->line('This may be due to:')
            ->line('- Invalid recipient email address')
            ->line('- Email delivery issues')
            ->line('- Network connectivity problems')
            ->line('')
            ->line('Please try again or contact support for assistance.')
            ->action('Retry DMCA Request', url('/dashboard/dmca/' . $this->dmcaRequest->id));
    }

    /**
     * DMCA resolved notification.
     */
    private function resolvedMail(): MailMessage
    {
        $resolution = DmcaRequest::RESOLUTIONS[$this->dmcaRequest->resolution] ?? $this->dmcaRequest->resolution;

        return (new MailMessage)
            ->subject('DMCA Request Resolved - ' . $this->dmcaRequest->reference_number)
            ->greeting('Your DMCA Request Has Been Resolved')
            ->line('Great news! Your DMCA takedown request has been resolved.')
            ->line('')
            ->line('**Details:**')
            ->line("- Reference: {$this->dmcaRequest->reference_number}")
            ->line("- Infringing URL: {$this->dmcaRequest->infringing_url}")
            ->line("- Resolution: {$resolution}")
            ->line("- Resolved At: " . $this->dmcaRequest->resolved_at?->format('F j, Y'))
            ->line('')
            ->line('Thank you for using ContentShield AI to protect your content.')
            ->action('View Details', url('/dashboard/dmca/' . $this->dmcaRequest->id));
    }

    /**
     * Default notification.
     */
    private function defaultMail(): MailMessage
    {
        return (new MailMessage)
            ->subject('DMCA Update - ' . $this->dmcaRequest->reference_number)
            ->greeting('DMCA Request Update')
            ->line("Your DMCA request ({$this->dmcaRequest->reference_number}) has been updated.")
            ->line("Status: {$this->dmcaRequest->getStatusLabel()}")
            ->action('View Details', url('/dashboard/dmca/' . $this->dmcaRequest->id));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'dmca_id' => $this->dmcaRequest->id,
            'reference_number' => $this->dmcaRequest->reference_number,
            'type' => $this->type,
            'status' => $this->dmcaRequest->status,
        ];
    }
}
