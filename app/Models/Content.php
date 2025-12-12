<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Content extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'license_id',
        'activation_id',
        'post_id',
        'post_url',
        'post_title',
        'fingerprint',
        'content_hash',
        'watermark_data',
        'word_count',
        'status',
        'monitoring_enabled',
        'last_monitored_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'post_id' => 'integer',
            'word_count' => 'integer',
            'monitoring_enabled' => 'boolean',
            'last_monitored_at' => 'datetime',
        ];
    }

    /**
     * Get the license that this content belongs to.
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /**
     * Get the activation that registered this content.
     */
    public function activation(): BelongsTo
    {
        return $this->belongsTo(Activation::class);
    }

    /**
     * Get the monitoring results for this content.
     */
    public function monitoringResults(): HasMany
    {
        return $this->hasMany(MonitoringResult::class);
    }

    /**
     * Get the DMCA requests for this content.
     */
    public function dmcaRequests(): HasMany
    {
        return $this->hasMany(DmcaRequest::class);
    }

    /**
     * Get the scan logs for this content.
     */
    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }

    /**
     * Scope a query to only include active contents.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include contents with monitoring enabled.
     */
    public function scopeMonitored($query)
    {
        return $query->where('monitoring_enabled', true);
    }

    /**
     * Scope a query for contents that need monitoring.
     */
    public function scopeNeedsMonitoring($query, string $frequency = 'daily')
    {
        $threshold = match ($frequency) {
            'hourly' => now()->subHour(),
            'daily' => now()->subDay(),
            'weekly' => now()->subWeek(),
            default => now()->subDay(),
        };

        return $query->active()
            ->monitored()
            ->where(function ($q) use ($threshold) {
                $q->whereNull('last_monitored_at')
                    ->orWhere('last_monitored_at', '<', $threshold);
            });
    }

    /**
     * Mark the content as monitored.
     */
    public function markAsMonitored(): void
    {
        $this->update(['last_monitored_at' => now()]);
    }

    /**
     * Get unresolved monitoring results count.
     */
    public function getUnresolvedMatchesCount(): int
    {
        return $this->monitoringResults()
            ->whereNotIn('status', ['resolved', 'false_positive'])
            ->count();
    }
}
