<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DmcaRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'license_id',
        'content_id',
        'monitoring_result_id',
        'infringing_url',
        'original_url',
        'status',
        'recipient_type',
        'recipient_email',
        'notice_content',
        'reference_number',
        'sent_at',
        'acknowledged_at',
        'resolved_at',
        'resolution',
        'response_notes',
        'evidence_files',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
            'evidence_files' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Status options.
     */
    public const STATUSES = [
        'draft' => 'Draft',
        'pending' => 'Pending Review',
        'sent' => 'Sent',
        'acknowledged' => 'Acknowledged',
        'processing' => 'Being Processed',
        'resolved' => 'Resolved',
        'rejected' => 'Rejected',
        'escalated' => 'Escalated',
    ];

    /**
     * Recipient types.
     */
    public const RECIPIENT_TYPES = [
        'google' => 'Google Search',
        'bing' => 'Bing Search',
        'hosting_provider' => 'Hosting Provider',
        'domain_registrar' => 'Domain Registrar',
        'cloudflare' => 'Cloudflare',
        'website_owner' => 'Website Owner',
        'other' => 'Other',
    ];

    /**
     * Resolution types.
     */
    public const RESOLUTIONS = [
        'content_removed' => 'Content Removed',
        'deindexed' => 'Deindexed from Search',
        'site_taken_down' => 'Site Taken Down',
        'counter_notice' => 'Counter Notice Received',
        'no_action' => 'No Action Taken',
        'other' => 'Other',
    ];

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->reference_number)) {
                $model->reference_number = self::generateReferenceNumber();
            }
        });
    }

    /**
     * Generate a unique reference number.
     */
    public static function generateReferenceNumber(): string
    {
        do {
            $reference = 'DMCA-' . strtoupper(Str::random(8)) . '-' . date('Ymd');
        } while (self::where('reference_number', $reference)->exists());

        return $reference;
    }

    /**
     * Get the license that this DMCA request belongs to.
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /**
     * Get the content that this DMCA request is for.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Get the monitoring result that triggered this DMCA request.
     */
    public function monitoringResult(): BelongsTo
    {
        return $this->belongsTo(MonitoringResult::class);
    }

    /**
     * Scope a query to only include drafts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include sent requests.
     */
    public function scopeSent($query)
    {
        return $query->whereNotNull('sent_at');
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['sent', 'acknowledged', 'processing']);
    }

    /**
     * Mark the request as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark the request as acknowledged.
     */
    public function markAsAcknowledged(): void
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
        ]);
    }

    /**
     * Mark the request as resolved.
     */
    public function markAsResolved(string $resolution, ?string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution' => $resolution,
            'response_notes' => $notes,
        ]);

        // Also mark the monitoring result as resolved
        if ($this->monitoringResult) {
            $this->monitoringResult->markAsResolved();
        }
    }

    /**
     * Get the human-readable status.
     */
    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get the human-readable recipient type.
     */
    public function getRecipientTypeLabel(): string
    {
        return self::RECIPIENT_TYPES[$this->recipient_type] ?? $this->recipient_type;
    }

    /**
     * Check if the request can be edited.
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    /**
     * Check if the request can be sent.
     */
    public function canSend(): bool
    {
        return $this->status === 'draft' || $this->status === 'pending';
    }
}
