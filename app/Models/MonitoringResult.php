<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoringResult extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content_id',
        'license_id',
        'found_url',
        'found_domain',
        'similarity_score',
        'match_type',
        'matched_excerpt',
        'detection_method',
        'status',
        'is_false_positive',
        'detected_at',
        'resolved_at',
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
            'similarity_score' => 'decimal:2',
            'is_false_positive' => 'boolean',
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Match types for plagiarism detection.
     */
    public const MATCH_TYPES = [
        'exact' => 'Exact Match',
        'near_exact' => 'Near Exact',
        'substantial' => 'Substantial Similarity',
        'partial' => 'Partial Match',
        'watermark' => 'Watermark Detected',
        'fingerprint' => 'Fingerprint Match',
    ];

    /**
     * Detection methods.
     */
    public const DETECTION_METHODS = [
        'fingerprint' => 'SimHash Fingerprint',
        'watermark' => 'Zero-Width Watermark',
        'ai_semantic' => 'AI Semantic Analysis',
        'google_search' => 'Google Search',
        'manual_scan' => 'Manual URL Scan',
    ];

    /**
     * Status options.
     */
    public const STATUSES = [
        'new' => 'New',
        'reviewing' => 'Under Review',
        'dmca_sent' => 'DMCA Sent',
        'resolved' => 'Resolved',
        'false_positive' => 'False Positive',
    ];

    /**
     * Get the content that this result belongs to.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Get the license that this result belongs to.
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /**
     * Get the DMCA requests for this monitoring result.
     */
    public function dmcaRequests(): HasMany
    {
        return $this->hasMany(DmcaRequest::class);
    }

    /**
     * Scope a query to only include new results.
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope a query to only include unresolved results.
     */
    public function scopeUnresolved($query)
    {
        return $query->whereNotIn('status', ['resolved', 'false_positive']);
    }

    /**
     * Scope a query to filter by minimum similarity score.
     */
    public function scopeMinSimilarity($query, float $score)
    {
        return $query->where('similarity_score', '>=', $score);
    }

    /**
     * Mark the result as reviewing.
     */
    public function markAsReviewing(): void
    {
        $this->update(['status' => 'reviewing']);
    }

    /**
     * Mark the result as a false positive.
     */
    public function markAsFalsePositive(): void
    {
        $this->update([
            'status' => 'false_positive',
            'is_false_positive' => true,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Mark the result as resolved.
     */
    public function markAsResolved(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    /**
     * Get the severity level based on similarity score.
     */
    public function getSeverity(): string
    {
        return match (true) {
            $this->similarity_score >= 90 => 'critical',
            $this->similarity_score >= 70 => 'high',
            $this->similarity_score >= 50 => 'medium',
            default => 'low',
        };
    }

    /**
     * Get the human-readable match type.
     */
    public function getMatchTypeLabel(): string
    {
        return self::MATCH_TYPES[$this->match_type] ?? $this->match_type;
    }

    /**
     * Get the human-readable detection method.
     */
    public function getDetectionMethodLabel(): string
    {
        return self::DETECTION_METHODS[$this->detection_method] ?? $this->detection_method;
    }
}
