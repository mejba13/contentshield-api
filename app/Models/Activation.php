<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'license_id',
        'site_url',
        'site_hash',
        'plugin_version',
        'activated_at',
        'last_check',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'last_check' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the license that this activation belongs to.
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /**
     * Get the contents registered from this activation.
     */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    /**
     * Normalize a site URL for consistent comparison.
     */
    public static function normalizeUrl(string $url): string
    {
        // Remove protocol
        $url = preg_replace('#^https?://#i', '', $url);

        // Remove www.
        $url = preg_replace('#^www\.#i', '', $url);

        // Remove trailing slash
        $url = rtrim($url, '/');

        return strtolower($url);
    }

    /**
     * Generate a site hash from URL and identifier.
     */
    public static function generateSiteHash(string $url, string $identifier = ''): string
    {
        $normalized = self::normalizeUrl($url);
        return hash('sha256', $normalized . '|' . $identifier);
    }

    /**
     * Update the last check timestamp.
     */
    public function updateLastCheck(): void
    {
        $this->update(['last_check' => now()]);
    }

    /**
     * Deactivate this activation.
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);

        // Decrement the activation count on the license
        $this->license->decrement('activations_count');
    }
}
