<?php

namespace App\Http\Middleware;

use App\Models\Activation;
use App\Models\License;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateLicense
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get authorization header (Bearer token = key hash)
        $keyHash = $this->extractKeyHash($request);

        if (!$keyHash) {
            return response()->json([
                'valid' => false,
                'error' => 'missing_authorization',
                'message' => 'Authorization header is required.',
            ], 401);
        }

        // Get site URL from header
        $siteUrl = $request->header('X-Site-URL');

        if (!$siteUrl) {
            return response()->json([
                'valid' => false,
                'error' => 'missing_site_url',
                'message' => 'X-Site-URL header is required.',
            ], 400);
        }

        // Find license by key hash
        $license = License::where('key_hash', $keyHash)->first();

        if (!$license) {
            return response()->json([
                'valid' => false,
                'error' => 'invalid_license',
                'message' => 'Invalid license key.',
            ], 401);
        }

        // Check if license is valid
        if (!$license->isValid()) {
            $error = match ($license->status) {
                'expired' => ['error' => 'license_expired', 'message' => 'License has expired.'],
                'cancelled' => ['error' => 'license_cancelled', 'message' => 'License has been cancelled.'],
                'suspended' => ['error' => 'license_suspended', 'message' => 'License has been suspended.'],
                'revoked' => ['error' => 'license_revoked', 'message' => 'License has been revoked.'],
                default => ['error' => 'license_invalid', 'message' => 'License is not valid.'],
            };

            return response()->json([
                'valid' => false,
                ...$error,
            ], 403);
        }

        // Check expiration
        if ($license->expires_at && $license->expires_at->isPast()) {
            return response()->json([
                'valid' => false,
                'error' => 'license_expired',
                'message' => 'License has expired.',
                'expired_at' => $license->expires_at->toIso8601String(),
            ], 403);
        }

        // Find activation for this site
        $normalizedUrl = Activation::normalizeUrl($siteUrl);
        $activation = Activation::where('license_id', $license->id)
            ->where('site_url', $normalizedUrl)
            ->where('is_active', true)
            ->first();

        if (!$activation) {
            return response()->json([
                'valid' => false,
                'error' => 'not_activated',
                'message' => 'License is not activated for this site.',
                'site_url' => $normalizedUrl,
            ], 403);
        }

        // Update last check
        $activation->updateLastCheck();

        // Attach license and activation to request for controllers
        $request->attributes->set('license', $license);
        $request->attributes->set('activation', $activation);

        return $next($request);
    }

    /**
     * Extract key hash from request.
     */
    private function extractKeyHash(Request $request): ?string
    {
        $header = $request->header('Authorization');

        if (!$header) {
            return null;
        }

        // Bearer {key_hash}
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return $header;
    }
}
