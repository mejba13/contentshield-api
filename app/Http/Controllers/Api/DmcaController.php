<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DmcaGenerateRequest;
use App\Http\Requests\Api\DmcaSendRequest;
use App\Jobs\SendDmcaJob;
use App\Models\Content;
use App\Models\DmcaRequest;
use App\Models\MonitoringResult;
use App\Services\DmcaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DmcaController extends Controller
{
    public function __construct(
        private DmcaService $dmcaService
    ) {}

    /**
     * Generate a DMCA notice.
     *
     * POST /api/v1/dmca/generate
     */
    public function generate(DmcaGenerateRequest $request): JsonResponse
    {
        $license = $request->attributes->get('license');
        $validated = $request->validated();

        // Check if the license plan supports DMCA features
        if (!$license->hasFeature('ai_matching')) {
            return response()->json([
                'success' => false,
                'error' => 'feature_not_available',
                'message' => 'DMCA features are not available on your plan.',
            ], 403);
        }

        $content = Content::where('license_id', $license->id)
            ->findOrFail($validated['content_id']);

        $monitoringResult = null;
        if (isset($validated['monitoring_result_id'])) {
            $monitoringResult = MonitoringResult::where('license_id', $license->id)
                ->findOrFail($validated['monitoring_result_id']);
        }

        $dmcaRequest = $this->dmcaService->generateNotice(
            license: $license,
            content: $content,
            infringingUrl: $validated['infringing_url'],
            recipientType: $validated['recipient_type'],
            monitoringResult: $monitoringResult,
            ownerInfo: $validated['owner_info'] ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'DMCA notice generated.',
            'dmca' => $this->formatDmca($dmcaRequest),
        ], 201);
    }

    /**
     * Send a DMCA notice.
     *
     * POST /api/v1/dmca/send
     */
    public function send(DmcaSendRequest $request): JsonResponse
    {
        $license = $request->attributes->get('license');
        $validated = $request->validated();

        // Check if auto DMCA is available on this plan
        if (!$license->hasFeature('auto_dmca')) {
            return response()->json([
                'success' => false,
                'error' => 'feature_not_available',
                'message' => 'Automated DMCA sending is not available on your plan.',
            ], 403);
        }

        $dmcaRequest = DmcaRequest::where('license_id', $license->id)
            ->findOrFail($validated['dmca_id']);

        if (!$dmcaRequest->canSend()) {
            return response()->json([
                'success' => false,
                'error' => 'cannot_send',
                'message' => 'This DMCA request cannot be sent in its current status.',
            ], 400);
        }

        // Dispatch job to send the DMCA
        SendDmcaJob::dispatch($dmcaRequest);

        return response()->json([
            'success' => true,
            'message' => 'DMCA notice queued for sending.',
            'reference_number' => $dmcaRequest->reference_number,
        ]);
    }

    /**
     * Get DMCA templates.
     *
     * GET /api/v1/dmca/templates
     */
    public function templates(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');

        // Check if DMCA templates are available
        if (!$license->hasFeature('ai_matching')) {
            return response()->json([
                'success' => false,
                'error' => 'feature_not_available',
                'message' => 'DMCA templates are not available on your plan.',
            ], 403);
        }

        $templates = $this->dmcaService->getTemplates();

        return response()->json([
            'success' => true,
            'templates' => $templates,
        ]);
    }

    /**
     * Get DMCA history.
     *
     * GET /api/v1/dmca/history
     */
    public function history(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');

        $query = DmcaRequest::where('license_id', $license->id)
            ->with('content:id,post_id,post_title,post_url');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by recipient type
        if ($request->has('recipient_type')) {
            $query->where('recipient_type', $request->input('recipient_type'));
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }
        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }

        $requests = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $requests->items(),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    /**
     * Get single DMCA request.
     *
     * GET /api/v1/dmca/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $license = $request->attributes->get('license');

        $dmcaRequest = DmcaRequest::where('license_id', $license->id)
            ->with(['content', 'monitoringResult'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'dmca' => $this->formatDmca($dmcaRequest, includeContent: true),
        ]);
    }

    /**
     * Update DMCA request.
     *
     * PUT /api/v1/dmca/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $license = $request->attributes->get('license');

        $request->validate([
            'notice_content' => 'nullable|string',
            'recipient_email' => 'nullable|email',
            'status' => 'nullable|in:' . implode(',', array_keys(DmcaRequest::STATUSES)),
            'resolution' => 'nullable|in:' . implode(',', array_keys(DmcaRequest::RESOLUTIONS)),
            'response_notes' => 'nullable|string',
        ]);

        $dmcaRequest = DmcaRequest::where('license_id', $license->id)
            ->findOrFail($id);

        if (!$dmcaRequest->canEdit() && !$request->has('status')) {
            return response()->json([
                'success' => false,
                'error' => 'cannot_edit',
                'message' => 'This DMCA request cannot be edited in its current status.',
            ], 400);
        }

        $data = $request->only(['notice_content', 'recipient_email', 'response_notes']);

        if ($request->has('status')) {
            $status = $request->input('status');
            if ($status === 'resolved') {
                $dmcaRequest->markAsResolved(
                    $request->input('resolution', 'other'),
                    $request->input('response_notes')
                );
            } else {
                $data['status'] = $status;
            }
        }

        $dmcaRequest->update($data);

        return response()->json([
            'success' => true,
            'message' => 'DMCA request updated.',
            'dmca' => $this->formatDmca($dmcaRequest->fresh()),
        ]);
    }

    /**
     * Delete DMCA request (drafts only).
     *
     * DELETE /api/v1/dmca/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $license = $request->attributes->get('license');

        $dmcaRequest = DmcaRequest::where('license_id', $license->id)
            ->findOrFail($id);

        if ($dmcaRequest->status !== 'draft') {
            return response()->json([
                'success' => false,
                'error' => 'cannot_delete',
                'message' => 'Only draft DMCA requests can be deleted.',
            ], 400);
        }

        $dmcaRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'DMCA request deleted.',
        ]);
    }

    /**
     * Get DMCA statistics.
     *
     * GET /api/v1/dmca/stats
     */
    public function stats(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');

        $stats = [
            'total' => DmcaRequest::where('license_id', $license->id)->count(),
            'drafts' => DmcaRequest::where('license_id', $license->id)->draft()->count(),
            'sent' => DmcaRequest::where('license_id', $license->id)->sent()->count(),
            'pending' => DmcaRequest::where('license_id', $license->id)->pending()->count(),
            'resolved' => DmcaRequest::where('license_id', $license->id)->where('status', 'resolved')->count(),
            'by_recipient_type' => DmcaRequest::where('license_id', $license->id)
                ->selectRaw('recipient_type, count(*) as count')
                ->groupBy('recipient_type')
                ->pluck('count', 'recipient_type'),
            'by_resolution' => DmcaRequest::where('license_id', $license->id)
                ->whereNotNull('resolution')
                ->selectRaw('resolution, count(*) as count')
                ->groupBy('resolution')
                ->pluck('count', 'resolution'),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Format DMCA request for response.
     */
    private function formatDmca(DmcaRequest $dmca, bool $includeContent = false): array
    {
        $data = [
            'id' => $dmca->id,
            'reference_number' => $dmca->reference_number,
            'infringing_url' => $dmca->infringing_url,
            'original_url' => $dmca->original_url,
            'status' => $dmca->status,
            'status_label' => $dmca->getStatusLabel(),
            'recipient_type' => $dmca->recipient_type,
            'recipient_type_label' => $dmca->getRecipientTypeLabel(),
            'recipient_email' => $dmca->recipient_email,
            'sent_at' => $dmca->sent_at?->toIso8601String(),
            'acknowledged_at' => $dmca->acknowledged_at?->toIso8601String(),
            'resolved_at' => $dmca->resolved_at?->toIso8601String(),
            'resolution' => $dmca->resolution,
            'can_edit' => $dmca->canEdit(),
            'can_send' => $dmca->canSend(),
            'created_at' => $dmca->created_at->toIso8601String(),
        ];

        if ($includeContent) {
            $data['notice_content'] = $dmca->notice_content;
            $data['response_notes'] = $dmca->response_notes;
            $data['evidence_files'] = $dmca->evidence_files;
            $data['content'] = $dmca->content ? [
                'id' => $dmca->content->id,
                'post_id' => $dmca->content->post_id,
                'post_title' => $dmca->content->post_title,
                'post_url' => $dmca->content->post_url,
            ] : null;
        }

        return $data;
    }
}
