<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\DmcaRequest;
use App\Models\MonitoringResult;
use App\Models\ScanLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Get dashboard statistics.
     *
     * GET /api/v1/reports/dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');

        // Content stats
        $contentStats = [
            'total' => Content::where('license_id', $license->id)->count(),
            'active' => Content::where('license_id', $license->id)->active()->count(),
            'monitored' => Content::where('license_id', $license->id)->monitored()->count(),
        ];

        // Monitoring stats
        $monitoringStats = [
            'total_matches' => MonitoringResult::where('license_id', $license->id)->count(),
            'new' => MonitoringResult::where('license_id', $license->id)->new()->count(),
            'unresolved' => MonitoringResult::where('license_id', $license->id)->unresolved()->count(),
            'resolved' => MonitoringResult::where('license_id', $license->id)->where('status', 'resolved')->count(),
            'false_positives' => MonitoringResult::where('license_id', $license->id)->where('is_false_positive', true)->count(),
        ];

        // DMCA stats
        $dmcaStats = [
            'total' => DmcaRequest::where('license_id', $license->id)->count(),
            'sent' => DmcaRequest::where('license_id', $license->id)->sent()->count(),
            'resolved' => DmcaRequest::where('license_id', $license->id)->where('status', 'resolved')->count(),
            'success_rate' => $this->calculateDmcaSuccessRate($license->id),
        ];

        // Recent activity
        $recentMatches = MonitoringResult::where('license_id', $license->id)
            ->with('content:id,post_id,post_title')
            ->orderBy('detected_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn ($match) => [
                'id' => $match->id,
                'found_url' => $match->found_url,
                'similarity_score' => $match->similarity_score,
                'severity' => $match->getSeverity(),
                'status' => $match->status,
                'detected_at' => $match->detected_at->toIso8601String(),
                'content_title' => $match->content?->post_title,
            ]);

        // Plan usage
        $planUsage = [
            'posts_used' => $contentStats['monitored'],
            'posts_limit' => $license->getPostLimit(),
            'posts_percentage' => $license->getPostLimit() > 0
                ? round(($contentStats['monitored'] / $license->getPostLimit()) * 100, 1)
                : 0,
            'sites_used' => $license->activations_count,
            'sites_limit' => $license->activations_limit,
        ];

        return response()->json([
            'success' => true,
            'content' => $contentStats,
            'monitoring' => $monitoringStats,
            'dmca' => $dmcaStats,
            'recent_matches' => $recentMatches,
            'plan_usage' => $planUsage,
            'plan' => $license->plan,
            'features' => $license->getFeatures(),
        ]);
    }

    /**
     * Export reports.
     *
     * GET /api/v1/reports/export
     */
    public function export(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');

        // Check API access
        if (!$license->hasFeature('api_access')) {
            return response()->json([
                'success' => false,
                'error' => 'feature_not_available',
                'message' => 'Report export is not available on your plan.',
            ], 403);
        }

        $request->validate([
            'type' => 'required|in:monitoring,dmca,content,summary',
            'format' => 'required|in:json,csv',
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $type = $request->input('type');
        $format = $request->input('format');
        $from = $request->input('from') ? Carbon::parse($request->input('from')) : null;
        $to = $request->input('to') ? Carbon::parse($request->input('to')) : null;

        $data = match ($type) {
            'monitoring' => $this->exportMonitoringResults($license, $from, $to),
            'dmca' => $this->exportDmcaRequests($license, $from, $to),
            'content' => $this->exportContent($license),
            'summary' => $this->exportSummary($license, $from, $to),
        };

        if ($format === 'csv') {
            return $this->toCsvResponse($data, "{$type}_export");
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'exported_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get trend data.
     *
     * GET /api/v1/reports/trends
     */
    public function trends(Request $request): JsonResponse
    {
        $license = $request->attributes->get('license');

        $request->validate([
            'period' => 'nullable|in:7d,30d,90d,1y',
        ]);

        $period = $request->input('period', '30d');
        $startDate = match ($period) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '1y' => now()->subYear(),
        };

        // Get daily match counts
        $matchesTrend = MonitoringResult::where('license_id', $license->id)
            ->where('detected_at', '>=', $startDate)
            ->selectRaw('DATE(detected_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        // Get daily resolved counts
        $resolvedTrend = MonitoringResult::where('license_id', $license->id)
            ->where('resolved_at', '>=', $startDate)
            ->whereNotNull('resolved_at')
            ->selectRaw('DATE(resolved_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        // Get DMCA trend
        $dmcaTrend = DmcaRequest::where('license_id', $license->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        return response()->json([
            'success' => true,
            'period' => $period,
            'start_date' => $startDate->toDateString(),
            'trends' => [
                'matches_detected' => $matchesTrend,
                'matches_resolved' => $resolvedTrend,
                'dmca_requests' => $dmcaTrend,
            ],
        ]);
    }

    /**
     * Calculate DMCA success rate.
     */
    private function calculateDmcaSuccessRate(int $licenseId): float
    {
        $total = DmcaRequest::where('license_id', $licenseId)
            ->whereIn('status', ['resolved', 'rejected'])
            ->count();

        if ($total === 0) {
            return 0;
        }

        $successful = DmcaRequest::where('license_id', $licenseId)
            ->where('status', 'resolved')
            ->whereIn('resolution', ['content_removed', 'deindexed', 'site_taken_down'])
            ->count();

        return round(($successful / $total) * 100, 1);
    }

    /**
     * Export monitoring results.
     */
    private function exportMonitoringResults($license, $from, $to): array
    {
        $query = MonitoringResult::where('license_id', $license->id)
            ->with('content:id,post_id,post_title,post_url');

        if ($from) {
            $query->where('detected_at', '>=', $from);
        }
        if ($to) {
            $query->where('detected_at', '<=', $to);
        }

        return $query->orderBy('detected_at', 'desc')
            ->get()
            ->map(fn ($result) => [
                'id' => $result->id,
                'content_title' => $result->content?->post_title,
                'content_url' => $result->content?->post_url,
                'found_url' => $result->found_url,
                'found_domain' => $result->found_domain,
                'similarity_score' => $result->similarity_score,
                'match_type' => $result->match_type,
                'detection_method' => $result->detection_method,
                'status' => $result->status,
                'detected_at' => $result->detected_at->toIso8601String(),
                'resolved_at' => $result->resolved_at?->toIso8601String(),
            ])
            ->toArray();
    }

    /**
     * Export DMCA requests.
     */
    private function exportDmcaRequests($license, $from, $to): array
    {
        $query = DmcaRequest::where('license_id', $license->id)
            ->with('content:id,post_id,post_title,post_url');

        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return $query->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($dmca) => [
                'reference_number' => $dmca->reference_number,
                'content_title' => $dmca->content?->post_title,
                'original_url' => $dmca->original_url,
                'infringing_url' => $dmca->infringing_url,
                'recipient_type' => $dmca->recipient_type,
                'status' => $dmca->status,
                'resolution' => $dmca->resolution,
                'created_at' => $dmca->created_at->toIso8601String(),
                'sent_at' => $dmca->sent_at?->toIso8601String(),
                'resolved_at' => $dmca->resolved_at?->toIso8601String(),
            ])
            ->toArray();
    }

    /**
     * Export content.
     */
    private function exportContent($license): array
    {
        return Content::where('license_id', $license->id)
            ->get()
            ->map(fn ($content) => [
                'post_id' => $content->post_id,
                'post_title' => $content->post_title,
                'post_url' => $content->post_url,
                'fingerprint' => $content->fingerprint,
                'word_count' => $content->word_count,
                'status' => $content->status,
                'monitoring_enabled' => $content->monitoring_enabled,
                'last_monitored_at' => $content->last_monitored_at?->toIso8601String(),
                'created_at' => $content->created_at->toIso8601String(),
            ])
            ->toArray();
    }

    /**
     * Export summary report.
     */
    private function exportSummary($license, $from, $to): array
    {
        $matchQuery = MonitoringResult::where('license_id', $license->id);
        $dmcaQuery = DmcaRequest::where('license_id', $license->id);

        if ($from) {
            $matchQuery->where('detected_at', '>=', $from);
            $dmcaQuery->where('created_at', '>=', $from);
        }
        if ($to) {
            $matchQuery->where('detected_at', '<=', $to);
            $dmcaQuery->where('created_at', '<=', $to);
        }

        return [
            'period' => [
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'content' => [
                'total' => Content::where('license_id', $license->id)->count(),
                'monitored' => Content::where('license_id', $license->id)->monitored()->count(),
            ],
            'matches' => [
                'detected' => $matchQuery->count(),
                'resolved' => (clone $matchQuery)->where('status', 'resolved')->count(),
                'false_positives' => (clone $matchQuery)->where('is_false_positive', true)->count(),
            ],
            'dmca' => [
                'total' => $dmcaQuery->count(),
                'sent' => (clone $dmcaQuery)->whereNotNull('sent_at')->count(),
                'resolved' => (clone $dmcaQuery)->where('status', 'resolved')->count(),
            ],
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Convert data to CSV response.
     */
    private function toCsvResponse(array $data, string $filename): JsonResponse
    {
        if (empty($data)) {
            return response()->json([
                'success' => true,
                'csv' => '',
                'filename' => "{$filename}.csv",
            ]);
        }

        $headers = array_keys($data[0]);
        $csv = implode(',', $headers) . "\n";

        foreach ($data as $row) {
            $values = array_map(function ($value) {
                if (is_null($value)) {
                    return '';
                }
                if (is_bool($value)) {
                    return $value ? 'true' : 'false';
                }
                // Escape quotes and wrap in quotes if contains comma
                $value = str_replace('"', '""', $value);
                if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
                    return '"' . $value . '"';
                }
                return $value;
            }, array_values($row));
            $csv .= implode(',', $values) . "\n";
        }

        return response()->json([
            'success' => true,
            'csv' => $csv,
            'filename' => "{$filename}_" . date('Y-m-d') . ".csv",
        ]);
    }
}
