<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanLog extends Model
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
        'scan_type',
        'status',
        'urls_checked',
        'matches_found',
        'started_at',
        'completed_at',
        'error_message',
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
            'urls_checked' => 'integer',
            'matches_found' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Scan types.
     */
    public const SCAN_TYPES = [
        'manual' => 'Manual Scan',
        'scheduled' => 'Scheduled Monitoring',
        'google_search' => 'Google Search Scan',
        'ai_analysis' => 'AI Analysis',
    ];

    /**
     * Status options.
     */
    public const STATUSES = [
        'pending' => 'Pending',
        'running' => 'Running',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Get the license that this scan belongs to.
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /**
     * Get the content being scanned.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Scope a query to only include running scans.
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Scope a query to only include completed scans.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed scans.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Create a new scan log entry.
     */
    public static function start(int $licenseId, string $type, ?int $contentId = null): self
    {
        return self::create([
            'license_id' => $licenseId,
            'content_id' => $contentId,
            'scan_type' => $type,
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark the scan as completed.
     */
    public function markAsCompleted(int $urlsChecked = 0, int $matchesFound = 0): void
    {
        $this->update([
            'status' => 'completed',
            'urls_checked' => $urlsChecked,
            'matches_found' => $matchesFound,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the scan as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
    }

    /**
     * Increment the URLs checked count.
     */
    public function incrementUrlsChecked(int $amount = 1): void
    {
        $this->increment('urls_checked', $amount);
    }

    /**
     * Increment the matches found count.
     */
    public function incrementMatchesFound(int $amount = 1): void
    {
        $this->increment('matches_found', $amount);
    }

    /**
     * Get the duration of the scan in seconds.
     */
    public function getDuration(): ?int
    {
        if (!$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInSeconds($this->completed_at);
    }

    /**
     * Get the human-readable scan type.
     */
    public function getScanTypeLabel(): string
    {
        return self::SCAN_TYPES[$this->scan_type] ?? $this->scan_type;
    }

    /**
     * Get the human-readable status.
     */
    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
