<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LicenseValidateRequest;
use App\Http\Requests\Api\LicenseDeactivateRequest;
use App\Models\Activation;
use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function __construct(
        private LicenseService $licenseService
    ) {}

    /**
     * Validate and activate a license key.
     *
     * POST /api/v1/license/validate
     */
    public function validate(LicenseValidateRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->licenseService->validateAndActivate(
            $validated['license_key'],
            $validated['site_url'],
            $validated['site_hash'],
            $validated['plugin_version'] ?? null
        );

        if (!$result['valid']) {
            return response()->json([
                'valid' => false,
                'error' => $result['error'],
                'message' => $result['message'],
                'limit' => $result['limit'] ?? null,
            ], 400);
        }

        return response()->json([
            'valid' => true,
            'license' => [
                'plan' => $result['license']->plan,
                'status' => $result['license']->status,
                'expires_at' => $result['license']->expires_at?->toIso8601String(),
                'features' => $result['license']->getFeatures(),
            ],
            'activation' => [
                'site_url' => $result['activation']->site_url,
                'activated_at' => $result['activation']->activated_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Deactivate a license from a site.
     *
     * POST /api/v1/license/deactivate
     */
    public function deactivate(LicenseDeactivateRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->licenseService->deactivate(
            $validated['license_key'],
            $validated['site_url']
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'],
                'message' => $result['message'],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'License deactivated successfully.',
        ]);
    }

    /**
     * Get the status of a license.
     *
     * GET /api/v1/license/status
     */
    public function status(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');
        $activation = $request->attributes->get('activation');

        return response()->json([
            'license' => [
                'plan' => $license->plan,
                'status' => $license->status,
                'expires_at' => $license->expires_at?->toIso8601String(),
                'activations_count' => $license->activations_count,
                'activations_limit' => $license->activations_limit,
                'features' => $license->getFeatures(),
            ],
            'activation' => [
                'site_url' => $activation->site_url,
                'activated_at' => $activation->activated_at->toIso8601String(),
                'last_check' => $activation->last_check?->toIso8601String(),
            ],
            'usage' => [
                'monitored_posts' => $license->contents()->where('monitoring_enabled', true)->count(),
                'posts_limit' => $license->getPostLimit(),
                'unresolved_matches' => $license->monitoringResults()->unresolved()->count(),
                'pending_dmca' => $license->dmcaRequests()->pending()->count(),
            ],
        ]);
    }

    /**
     * Refresh license validation (heartbeat).
     *
     * POST /api/v1/license/refresh
     */
    public function refresh(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');
        $activation = $request->attributes->get('activation');

        // Update last check timestamp
        $activation->updateLastCheck();

        // Check if license is still valid
        if (!$license->isValid()) {
            return response()->json([
                'valid' => false,
                'error' => 'license_expired',
                'message' => 'Your license has expired.',
            ], 400);
        }

        return response()->json([
            'valid' => true,
            'license' => [
                'plan' => $license->plan,
                'status' => $license->status,
                'expires_at' => $license->expires_at?->toIso8601String(),
                'features' => $license->getFeatures(),
            ],
        ]);
    }
}
