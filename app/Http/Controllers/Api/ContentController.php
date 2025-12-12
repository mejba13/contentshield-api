<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContentRegisterRequest;
use App\Http\Requests\Api\ContentUpdateRequest;
use App\Models\Content;
use App\Services\FingerprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(
        private FingerprintService $fingerprintService
    ) {}

    /**
     * Register content for monitoring.
     *
     * POST /api/v1/content/register
     */
    public function register(ContentRegisterRequest $request): JsonResponse
    {
        $license = $request->attributes->get('license');
        $activation = $request->attributes->get('activation');
        $validated = $request->validated();

        // Check if license can monitor more posts
        if (!$license->canMonitorMorePosts()) {
            return response()->json([
                'success' => false,
                'error' => 'post_limit_reached',
                'message' => 'You have reached your post monitoring limit.',
                'limit' => $license->getPostLimit(),
            ], 400);
        }

        // Check if content already exists
        $existingContent = Content::where('license_id', $license->id)
            ->where('post_id', $validated['post_id'])
            ->where('activation_id', $activation->id)
            ->first();

        if ($existingContent) {
            // Update existing content
            $existingContent->update([
                'post_url' => $validated['post_url'],
                'post_title' => $validated['post_title'],
                'fingerprint' => $validated['fingerprint'],
                'content_hash' => $validated['content_hash'],
                'watermark_data' => $validated['watermark_data'] ?? null,
                'word_count' => $validated['word_count'] ?? 0,
                'status' => 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Content updated successfully.',
                'content' => $this->formatContent($existingContent->fresh()),
            ]);
        }

        // Create new content
        $content = Content::create([
            'license_id' => $license->id,
            'activation_id' => $activation->id,
            'post_id' => $validated['post_id'],
            'post_url' => $validated['post_url'],
            'post_title' => $validated['post_title'],
            'fingerprint' => $validated['fingerprint'],
            'content_hash' => $validated['content_hash'],
            'watermark_data' => $validated['watermark_data'] ?? null,
            'word_count' => $validated['word_count'] ?? 0,
            'monitoring_enabled' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Content registered successfully.',
            'content' => $this->formatContent($content),
        ], 201);
    }

    /**
     * List registered content.
     *
     * GET /api/v1/content/list
     */
    public function list(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');
        $activation = $request->attributes->get('activation');

        $query = Content::where('license_id', $license->id)
            ->where('activation_id', $activation->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by monitoring enabled
        if ($request->has('monitoring')) {
            $query->where('monitoring_enabled', $request->boolean('monitoring'));
        }

        $contents = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $contents->items(),
            'pagination' => [
                'current_page' => $contents->currentPage(),
                'last_page' => $contents->lastPage(),
                'per_page' => $contents->perPage(),
                'total' => $contents->total(),
            ],
        ]);
    }

    /**
     * Get single content details.
     *
     * GET /api/v1/content/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $license = $request->attributes->get('license');

        $content = Content::where('license_id', $license->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'content' => $this->formatContent($content),
            'stats' => [
                'total_matches' => $content->monitoringResults()->count(),
                'unresolved_matches' => $content->getUnresolvedMatchesCount(),
                'dmca_requests' => $content->dmcaRequests()->count(),
            ],
        ]);
    }

    /**
     * Update content details.
     *
     * PUT /api/v1/content/{id}
     */
    public function update(ContentUpdateRequest $request, int $id): JsonResponse
    {
        $license = $request->attributes->get('license');
        $validated = $request->validated();

        $content = Content::where('license_id', $license->id)
            ->findOrFail($id);

        $content->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Content updated successfully.',
            'content' => $this->formatContent($content->fresh()),
        ]);
    }

    /**
     * Remove content from monitoring.
     *
     * DELETE /api/v1/content/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $license = $request->attributes->get('license');

        $content = Content::where('license_id', $license->id)
            ->findOrFail($id);

        // Soft delete by setting status to deleted
        $content->update([
            'status' => 'deleted',
            'monitoring_enabled' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Content removed from monitoring.',
        ]);
    }

    /**
     * Bulk register multiple contents.
     *
     * POST /api/v1/content/bulk-register
     */
    public function bulkRegister(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');
        $activation = $request->attributes->get('activation');

        $request->validate([
            'contents' => 'required|array|max:100',
            'contents.*.post_id' => 'required|integer',
            'contents.*.post_url' => 'required|url|max:2048',
            'contents.*.post_title' => 'required|string|max:500',
            'contents.*.fingerprint' => 'required|string|max:128',
            'contents.*.content_hash' => 'required|string|size:64',
            'contents.*.word_count' => 'nullable|integer|min:0',
        ]);

        $limit = $license->getPostLimit();
        $currentCount = $license->contents()->where('monitoring_enabled', true)->count();
        $available = $limit === -1 ? PHP_INT_MAX : $limit - $currentCount;

        $registered = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($request->input('contents') as $contentData) {
            // Check if content already exists
            $existingContent = Content::where('license_id', $license->id)
                ->where('post_id', $contentData['post_id'])
                ->where('activation_id', $activation->id)
                ->first();

            if ($existingContent) {
                $existingContent->update([
                    'post_url' => $contentData['post_url'],
                    'post_title' => $contentData['post_title'],
                    'fingerprint' => $contentData['fingerprint'],
                    'content_hash' => $contentData['content_hash'],
                    'word_count' => $contentData['word_count'] ?? 0,
                    'status' => 'active',
                ]);
                $updated++;
                continue;
            }

            // Check limit
            if ($registered >= $available) {
                $skipped++;
                continue;
            }

            Content::create([
                'license_id' => $license->id,
                'activation_id' => $activation->id,
                'post_id' => $contentData['post_id'],
                'post_url' => $contentData['post_url'],
                'post_title' => $contentData['post_title'],
                'fingerprint' => $contentData['fingerprint'],
                'content_hash' => $contentData['content_hash'],
                'word_count' => $contentData['word_count'] ?? 0,
                'monitoring_enabled' => true,
            ]);
            $registered++;
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk registration completed.',
            'stats' => [
                'registered' => $registered,
                'updated' => $updated,
                'skipped' => $skipped,
            ],
        ]);
    }

    /**
     * Format content for response.
     */
    private function formatContent(Content $content): array
    {
        return [
            'id' => $content->id,
            'post_id' => $content->post_id,
            'post_url' => $content->post_url,
            'post_title' => $content->post_title,
            'fingerprint' => $content->fingerprint,
            'content_hash' => $content->content_hash,
            'word_count' => $content->word_count,
            'status' => $content->status,
            'monitoring_enabled' => $content->monitoring_enabled,
            'last_monitored_at' => $content->last_monitored_at?->toIso8601String(),
            'created_at' => $content->created_at->toIso8601String(),
            'updated_at' => $content->updated_at->toIso8601String(),
        ];
    }
}
