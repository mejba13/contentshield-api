<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ScanContentJob;
use App\Models\Content;
use App\Models\MonitoringResult;
use App\Models\ScanLog;
use App\Services\MonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function __construct(
        private MonitoringService $monitoringService
    ) {}

    /**
     * Get monitoring status overview.
     *
     * GET /api/v1/monitoring/status
     */
    public function status(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');

        $stats = [
            'total_content' => $license->contents()->count(),
            'monitored_content' => $license->contents()->monitored()->count(),
            'total_matches' => $license->monitoringResults()->count(),
            'new_matches' => $license->monitoringResults()->new()->count(),
            'unresolved_matches' => $license->monitoringResults()->unresolved()->count(),
            'resolved_matches' => $license->monitoringResults()->where('status', 'resolved')->count(),
            'false_positives' => $license->monitoringResults()->where('status', 'false_positive')->count(),
        ];

        $recentScans = ScanLog::where('license_id', $license->id)
            ->orderBy('started_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn ($scan) => [
                'id' => $scan->id,
                'type' => $scan->getScanTypeLabel(),
                'status' => $scan->getStatusLabel(),
                'urls_checked' => $scan->urls_checked,
                'matches_found' => $scan->matches_found,
                'started_at' => $scan->started_at->toIso8601String(),
                'completed_at' => $scan->completed_at?->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'recent_scans' => $recentScans,
            'monitoring_frequency' => $license->getMonitoringFrequency(),
        ]);
    }

    /**
     * Get monitoring results.
     *
     * GET /api/v1/monitoring/results
     */
    public function results(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');

        $query = MonitoringResult::where('license_id', $license->id)
            ->with('content:id,post_id,post_title,post_url');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by content
        if ($request->has('content_id')) {
            $query->where('content_id', $request->input('content_id'));
        }

        // Filter by minimum similarity
        if ($request->has('min_similarity')) {
            $query->minSimilarity($request->input('min_similarity'));
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->where('detected_at', '>=', $request->input('from'));
        }
        if ($request->has('to')) {
            $query->where('detected_at', '<=', $request->input('to'));
        }

        $results = $query->orderBy('detected_at', 'desc')
            ->paginate($request->input('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $results->items(),
            'pagination' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ],
        ]);
    }

    /**
     * Get single monitoring result.
     *
     * GET /api/v1/monitoring/results/{id}
     */
    public function showResult(Request $request, int $id): JsonResponse
    {
        $license = $request->attributes->get('license');

        $result = MonitoringResult::where('license_id', $license->id)
            ->with(['content', 'dmcaRequests'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'result' => [
                'id' => $result->id,
                'found_url' => $result->found_url,
                'found_domain' => $result->found_domain,
                'similarity_score' => $result->similarity_score,
                'match_type' => $result->match_type,
                'match_type_label' => $result->getMatchTypeLabel(),
                'detection_method' => $result->detection_method,
                'detection_method_label' => $result->getDetectionMethodLabel(),
                'matched_excerpt' => $result->matched_excerpt,
                'status' => $result->status,
                'severity' => $result->getSeverity(),
                'is_false_positive' => $result->is_false_positive,
                'detected_at' => $result->detected_at->toIso8601String(),
                'resolved_at' => $result->resolved_at?->toIso8601String(),
                'content' => [
                    'id' => $result->content->id,
                    'post_id' => $result->content->post_id,
                    'post_title' => $result->content->post_title,
                    'post_url' => $result->content->post_url,
                ],
                'dmca_requests' => $result->dmcaRequests->map(fn ($dmca) => [
                    'id' => $dmca->id,
                    'reference_number' => $dmca->reference_number,
                    'status' => $dmca->status,
                    'sent_at' => $dmca->sent_at?->toIso8601String(),
                ]),
            ],
        ]);
    }

    /**
     * Update monitoring result status.
     *
     * PUT /api/v1/monitoring/results/{id}
     */
    public function updateResult(Request $request, int $id): JsonResponse
    {
        $license = $request->attributes->get('license');

        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(MonitoringResult::STATUSES)),
        ]);

        $result = MonitoringResult::where('license_id', $license->id)
            ->findOrFail($id);

        $status = $request->input('status');

        if ($status === 'false_positive') {
            $result->markAsFalsePositive();
        } elseif ($status === 'resolved') {
            $result->markAsResolved();
        } else {
            $result->update(['status' => $status]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Result status updated.',
            'status' => $result->fresh()->status,
        ]);
    }

    /**
     * Trigger manual scan.
     *
     * POST /api/v1/monitoring/scan
     */
    public function scan(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');

        $request->validate([
            'content_id' => 'nullable|integer',
            'url' => 'nullable|url|max:2048',
        ]);

        // If scanning a specific URL
        if ($request->has('url')) {
            $request->validate([
                'content_id' => 'required|integer',
            ]);

            $content = Content::where('license_id', $license->id)
                ->findOrFail($request->input('content_id'));

            $scanLog = ScanLog::start($license->id, 'manual', $content->id);

            // Dispatch job to scan the URL
            ScanContentJob::dispatch($content, $request->input('url'), $scanLog);

            return response()->json([
                'success' => true,
                'message' => 'Scan initiated.',
                'scan_id' => $scanLog->id,
            ]);
        }

        // If scanning specific content
        if ($request->has('content_id')) {
            $content = Content::where('license_id', $license->id)
                ->findOrFail($request->input('content_id'));

            $scanLog = ScanLog::start($license->id, 'manual', $content->id);

            // Dispatch job to scan the content
            ScanContentJob::dispatch($content, null, $scanLog);

            return response()->json([
                'success' => true,
                'message' => 'Scan initiated for content.',
                'scan_id' => $scanLog->id,
            ]);
        }

        // Scan all monitored content
        $contents = Content::where('license_id', $license->id)
            ->active()
            ->monitored()
            ->get();

        $scanLog = ScanLog::start($license->id, 'manual');

        foreach ($contents as $content) {
            ScanContentJob::dispatch($content, null, $scanLog);
        }

        return response()->json([
            'success' => true,
            'message' => 'Full scan initiated.',
            'scan_id' => $scanLog->id,
            'content_count' => $contents->count(),
        ]);
    }

    /**
     * Get scan logs.
     *
     * GET /api/v1/monitoring/logs
     */
    public function logs(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');

        $query = ScanLog::where('license_id', $license->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('scan_type', $request->input('type'));
        }

        $logs = $query->orderBy('started_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    /**
     * Get single scan log.
     *
     * GET /api/v1/monitoring/logs/{id}
     */
    public function showLog(Request $request, int $id): JsonResponse
    {
        $license = $request->attributes->get('license');

        $log = ScanLog::where('license_id', $license->id)
            ->with('content:id,post_id,post_title,post_url')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'log' => [
                'id' => $log->id,
                'scan_type' => $log->scan_type,
                'scan_type_label' => $log->getScanTypeLabel(),
                'status' => $log->status,
                'status_label' => $log->getStatusLabel(),
                'urls_checked' => $log->urls_checked,
                'matches_found' => $log->matches_found,
                'started_at' => $log->started_at->toIso8601String(),
                'completed_at' => $log->completed_at?->toIso8601String(),
                'duration_seconds' => $log->getDuration(),
                'error_message' => $log->error_message,
                'content' => $log->content ? [
                    'id' => $log->content->id,
                    'post_id' => $log->content->post_id,
                    'post_title' => $log->content->post_title,
                ] : null,
            ],
        ]);
    }
}
