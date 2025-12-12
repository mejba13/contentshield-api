<?php

namespace App\Services;

use App\Models\Activation;
use App\Models\License;
use App\Models\User;
use Illuminate\Support\Str;

class LicenseService
{
    /**
     * Store the plain text key temporarily after generation.
     */
    private ?string $plainKey = null;

    /**
     * Generate a new license key for a user.
     */
    public function generate(User $user, string $plan): License
    {
        // Generate secure key in format: CSAI-XXXX-XXXX-XXXX-XXXX
        $segments = [];
        for ($i = 0; $i < 4; $i++) {
            $segments[] = strtoupper(Str::random(4));
        }
        $key = 'CSAI-' . implode('-', $segments);

        // Store plain key temporarily for notification
        $this->plainKey = $key;

        // Create license record
        return License::create([
            'user_id' => $user->id,
            'key_hash' => hash('sha256', $key),
            'key_prefix' => substr($key, 0, 9), // CSAI-XXXX
            'plan' => $plan,
            'status' => 'active',
            'activations_limit' => $this->getActivationLimit($plan),
            'activations_count' => 0,
            'expires_at' => now()->addYear(),
        ]);
    }

    /**
     * Get the plain text key (only available immediately after generation).
     */
    public function getPlainKey(): ?string
    {
        return $this->plainKey;
    }

    /**
     * Validate a license key and activate for a site.
     */
    public function validateAndActivate(
        string $licenseKey,
        string $siteUrl,
        string $siteHash,
        ?string $pluginVersion = null
    ): array {
        // Find license by key hash
        $keyHash = hash('sha256', $licenseKey);
        $license = License::where('key_hash', $keyHash)->first();

        if (!$license) {
            return [
                'valid' => false,
                'error' => 'invalid_key',
                'message' => 'Invalid license key.',
            ];
        }

        // Check if license is active
        if ($license->status !== 'active') {
            return [
                'valid' => false,
                'error' => 'license_' . $license->status,
                'message' => 'License is ' . $license->status . '.',
            ];
        }

        // Check if license is expired
        if ($license->expires_at && $license->expires_at->isPast()) {
            return [
                'valid' => false,
                'error' => 'license_expired',
                'message' => 'License has expired.',
            ];
        }

        // Normalize site URL
        $normalizedUrl = Activation::normalizeUrl($siteUrl);

        // Check if already activated for this site
        $existingActivation = Activation::where('license_id', $license->id)
            ->where('site_url', $normalizedUrl)
            ->first();

        if ($existingActivation) {
            // Update existing activation
            $existingActivation->update([
                'site_hash' => $siteHash,
                'plugin_version' => $pluginVersion,
                'last_check' => now(),
                'is_active' => true,
            ]);

            return [
                'valid' => true,
                'license' => $license,
                'activation' => $existingActivation,
            ];
        }

        // Check activation limit
        if ($license->hasReachedActivationLimit()) {
            return [
                'valid' => false,
                'error' => 'activation_limit',
                'message' => 'Activation limit reached.',
                'limit' => $license->activations_limit,
            ];
        }

        // Create new activation
        $activation = Activation::create([
            'license_id' => $license->id,
            'site_url' => $normalizedUrl,
            'site_hash' => $siteHash,
            'plugin_version' => $pluginVersion,
            'activated_at' => now(),
            'last_check' => now(),
            'is_active' => true,
        ]);

        // Increment activation count
        $license->increment('activations_count');

        return [
            'valid' => true,
            'license' => $license,
            'activation' => $activation,
        ];
    }

    /**
     * Deactivate a license from a site.
     */
    public function deactivate(string $licenseKey, string $siteUrl): array
    {
        $keyHash = hash('sha256', $licenseKey);
        $license = License::where('key_hash', $keyHash)->first();

        if (!$license) {
            return [
                'success' => false,
                'error' => 'invalid_key',
                'message' => 'Invalid license key.',
            ];
        }

        $normalizedUrl = Activation::normalizeUrl($siteUrl);

        $activation = Activation::where('license_id', $license->id)
            ->where('site_url', $normalizedUrl)
            ->where('is_active', true)
            ->first();

        if (!$activation) {
            return [
                'success' => false,
                'error' => 'not_activated',
                'message' => 'License is not activated for this site.',
            ];
        }

        // Deactivate
        $activation->deactivate();

        return [
            'success' => true,
        ];
    }

    /**
     * Find a license by its key.
     */
    public function findByKey(string $licenseKey): ?License
    {
        $keyHash = hash('sha256', $licenseKey);
        return License::where('key_hash', $keyHash)->first();
    }

    /**
     * Find an activation by license and site.
     */
    public function findActivation(License $license, string $siteUrl): ?Activation
    {
        $normalizedUrl = Activation::normalizeUrl($siteUrl);
        return Activation::where('license_id', $license->id)
            ->where('site_url', $normalizedUrl)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get the activation limit for a plan.
     */
    private function getActivationLimit(string $plan): int
    {
        return match ($plan) {
            'starter' => 1,
            'pro' => 5,
            'agency' => 50,
            default => 1,
        };
    }

    /**
     * Revoke a license.
     */
    public function revoke(License $license): void
    {
        $license->update(['status' => 'revoked']);

        // Deactivate all activations
        $license->activations()->update(['is_active' => false]);
    }

    /**
     * Suspend a license.
     */
    public function suspend(License $license): void
    {
        $license->update(['status' => 'suspended']);
    }

    /**
     * Reactivate a suspended license.
     */
    public function reactivate(License $license): void
    {
        $license->update(['status' => 'active']);
    }

    /**
     * Extend a license expiration.
     */
    public function extend(License $license, int $days = 30): void
    {
        $newExpiry = $license->expires_at
            ? $license->expires_at->addDays($days)
            : now()->addDays($days);

        $license->update(['expires_at' => $newExpiry]);
    }

    /**
     * Upgrade or downgrade a license plan.
     */
    public function changePlan(License $license, string $newPlan): void
    {
        $license->update([
            'plan' => $newPlan,
            'activations_limit' => $this->getActivationLimit($newPlan),
        ]);
    }

    /**
     * Mask a license key for display.
     */
    public static function maskKey(string $key): string
    {
        return substr($key, 0, 9) . '-****-****-****';
    }

    /**
     * Validate license key format.
     */
    public static function isValidFormat(string $key): bool
    {
        return (bool) preg_match('/^CSAI-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/i', $key);
    }
}
