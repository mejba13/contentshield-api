<?php

namespace App\Notifications;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LicenseKeyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public License $license,
        public string $plainKey
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
        $planName = ucfirst($this->license->plan);

        return (new MailMessage)
            ->subject('Your ContentShield AI License Key')
            ->greeting('Welcome to ContentShield AI!')
            ->line("Thank you for purchasing the {$planName} plan.")
            ->line('Your license key is:')
            ->line("**{$this->plainKey}**")
            ->line('Please save this key in a secure location. You will need it to activate the plugin on your WordPress site.')
            ->line('')
            ->line('**Plan Details:**')
            ->line("- Plan: {$planName}")
            ->line("- Sites Allowed: {$this->license->activations_limit}")
            ->line("- Expires: " . ($this->license->expires_at?->format('F j, Y') ?? 'Never'))
            ->line('')
            ->line('**Next Steps:**')
            ->line('1. Install the ContentShield AI plugin on your WordPress site')
            ->line('2. Go to Settings > ContentShield AI')
            ->line('3. Enter your license key to activate')
            ->action('Download Plugin', url('/download'))
            ->line('If you have any questions, please contact our support team.')
            ->salutation('Best regards,')
            ->salutation('The ContentShield AI Team');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'license_id' => $this->license->id,
            'plan' => $this->license->plan,
            'key_prefix' => $this->license->key_prefix,
        ];
    }
}
