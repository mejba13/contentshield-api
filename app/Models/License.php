<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class License extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'key_hash',
        'key_prefix',
        'plan',
        'status',
        'activations_limit',
        'activations_count',
        'expires_at',
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
            'expires_at' => 'datetime',
            'metadata' => 'array',
            'activations_limit' => 'integer',
            'activations_count' => 'integer',
        ];
    }

    /**
     * Plan configuration with features.
     */
    public const PLANS = [
        'starter' => [
            'name' => 'Starter',
            'price' => 9,
            'monitoring_frequency' => 'weekly',
            'monitored_posts' => 50,
            'auto_dmca' => false,
            'api_access' => false,
            'sites_limit' => 1,
            'ai_matching' => true,
        ],
        'pro' => [
            'name' => 'Pro',
            'price' => 19,
            'monitoring_frequency' => 'daily',
            'monitored_posts' => 500,
            'auto_dmca' => true,
            'api_access' => true,
            'sites_limit' => 5,
            'ai_matching' => true,
        ],
        'agency' => [
            'name' => 'Agency',
            'price' => 49,
            'monitoring_frequency' => 'hourly',
            'monitored_posts' => -1, // unlimited
            'auto_dmca' => true,
            'api_access' => true,
            'sites_limit' => 50,
            'ai_matching' => true,
            'white_label' => true,
        ],
    ];

    /**
     * Get the user that owns the license.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the activations for the license.
     */
    public function activations(): HasMany
    {
        return $this->hasMany(Activation::class);
    }

    /**
     * Get the contents registered under this license.
     */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    /**
     * Get the monitoring results for this license.
     */
    public function monitoringResults(): HasMany
    {
        return $this->hasMany(MonitoringResult::class);
    }

    /**
     * Get the DMCA requests for this license.
     */
    public function dmcaRequests(): HasMany
    {
        return $this->hasMany(DmcaRequest::class);
    }

    /**
     * Get the scan logs for this license.
     */
    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }

    /**
     * Check if the license is active and not expired.
     */
    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the license has reached its activation limit.
     */
    public function hasReachedActivationLimit(): bool
    {
        return $this->activations_count >= $this->activations_limit;
    }

    /**
     * Get the plan features.
     */
    public function getFeatures(): array
    {
        return self::PLANS[$this->plan] ?? self::PLANS['starter'];
    }

    /**
     * Check if the plan has a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        $features = $this->getFeatures();
        return isset($features[$feature]) && $features[$feature];
    }

    /**
     * Get the monitoring frequency for this plan.
     */
    public function getMonitoringFrequency(): string
    {
        return $this->getFeatures()['monitoring_frequency'] ?? 'weekly';
    }

    /**
     * Get the post limit for this plan.
     */
    public function getPostLimit(): int
    {
        return $this->getFeatures()['monitored_posts'] ?? 50;
    }

    /**
     * Check if the license can monitor more posts.
     */
    public function canMonitorMorePosts(): bool
    {
        $limit = $this->getPostLimit();

        // -1 means unlimited
        if ($limit === -1) {
            return true;
        }

        return $this->contents()->where('monitoring_enabled', true)->count() < $limit;
    }
}
