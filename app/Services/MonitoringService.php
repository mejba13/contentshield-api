<?php

namespace App\Services;

use App\Models\Content;
use App\Models\License;
use App\Models\MonitoringResult;
use App\Models\ScanLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonitoringService
{
    public function __construct(
        private FingerprintService $fingerprintService,
        private AiMatchingService $aiMatchingService
    ) {}

    /**
     * Scan a URL for plagiarism against registered content.
     */
    public function scanUrl(Content $content, string $url, ScanLog $scanLog): ?MonitoringResult
    {
        try {
            // Fetch the URL content
            $response = Http::timeout(30)
                ->withUserAgent('ContentShield/1.0 (+https://contentshield.ai)')
                ->get($url);

            if (!$response->successful()) {
                Log::warning('Failed to fetch URL for scanning', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $html = $response->body();
            $text = $this->extractTextFromHtml($html);

            if (empty($text) || strlen($text) < 100) {
                return null;
            }

            // Generate fingerprint of scanned content
            $scannedFingerprint = $this->fingerprintService->generateFingerprint($text);

            // Calculate similarity
            $similarity = $this->fingerprintService->calculateSimilarity(
                $content->fingerprint,
                $scannedFingerprint
            );

            // Check for watermark
            $watermarkDetected = false;
            if ($content->watermark_data) {
                $watermarkDetected = $this->fingerprintService->verifyWatermark(
                    $text,
                    $content->watermark_data
                );
            }

            // Determine if it's a match
            $isMatch = $similarity >= 50 || $watermarkDetected;

            if (!$isMatch) {
                return null;
            }

            // Determine match type
            $matchType = $this->determineMatchType($similarity, $watermarkDetected);

            // Get matched excerpt
            $matchedExcerpt = $this->extractMatchedExcerpt($text, $content);

            // Create monitoring result
            $result = MonitoringResult::create([
                'content_id' => $content->id,
                'license_id' => $content->license_id,
                'found_url' => $url,
                'found_domain' => parse_url($url, PHP_URL_HOST),
                'similarity_score' => $similarity,
                'match_type' => $matchType,
                'matched_excerpt' => $matchedExcerpt,
                'detection_method' => $watermarkDetected ? 'watermark' : 'fingerprint',
                'status' => 'new',
                'detected_at' => now(),
                'metadata' => [
                    'scanned_fingerprint' => $scannedFingerprint,
                    'watermark_detected' => $watermarkDetected,
                    'word_count' => $this->fingerprintService->countWords($text),
                ],
            ]);

            $scanLog->incrementMatchesFound();

            return $result;

        } catch (\Exception $e) {
            Log::error('Error scanning URL', [
                'url' => $url,
                'content_id' => $content->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Perform AI-powered semantic matching.
     */
    public function performAiMatching(Content $content, string $text): array
    {
        return $this->aiMatchingService->compareContent(
            $content->post_title,
            $text
        );
    }

    /**
     * Search Google for potential plagiarism.
     */
    public function searchGoogle(Content $content): array
    {
        // Extract key phrases from content
        $searchQuery = $this->buildSearchQuery($content);

        if (!$searchQuery) {
            return [];
        }

        $results = [];

        try {
            $apiKey = config('services.google.api_key');
            $searchEngineId = config('services.google.search_engine_id');

            if (!$apiKey || !$searchEngineId) {
                Log::warning('Google Search API not configured');
                return [];
            }

            $response = Http::get('https://www.googleapis.com/customsearch/v1', [
                'key' => $apiKey,
                'cx' => $searchEngineId,
                'q' => $searchQuery,
                'num' => 10,
            ]);

            if (!$response->successful()) {
                Log::warning('Google Search API request failed', [
                    'status' => $response->status(),
                ]);
                return [];
            }

            $data = $response->json();

            foreach ($data['items'] ?? [] as $item) {
                // Skip the original URL
                $originalDomain = parse_url($content->post_url, PHP_URL_HOST);
                $resultDomain = parse_url($item['link'], PHP_URL_HOST);

                if ($resultDomain === $originalDomain) {
                    continue;
                }

                $results[] = [
                    'url' => $item['link'],
                    'title' => $item['title'],
                    'snippet' => $item['snippet'] ?? '',
                ];
            }

        } catch (\Exception $e) {
            Log::error('Error searching Google', [
                'content_id' => $content->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    /**
     * Get contents that need monitoring based on frequency.
     */
    public function getContentsNeedingMonitoring(License $license): \Illuminate\Database\Eloquent\Collection
    {
        $frequency = $license->getMonitoringFrequency();

        return Content::where('license_id', $license->id)
            ->needsMonitoring($frequency)
            ->get();
    }

    /**
     * Extract text content from HTML.
     */
    private function extractTextFromHtml(string $html): string
    {
        // Remove script and style elements
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);

        // Remove comments
        $html = preg_replace('/<!--.*?-->/s', '', $html);

        // Get text from article, main, or body
        $dom = new \DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        $xpath = new \DOMXPath($dom);

        // Try to find main content areas
        $contentNodes = $xpath->query('//article | //main | //*[contains(@class, "content")] | //*[contains(@class, "post")]');

        if ($contentNodes->length > 0) {
            $text = '';
            foreach ($contentNodes as $node) {
                $text .= ' ' . $node->textContent;
            }
            return $this->cleanText($text);
        }

        // Fallback to body
        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) {
            return $this->cleanText($body->textContent);
        }

        return $this->cleanText(strip_tags($html));
    }

    /**
     * Clean extracted text.
     */
    private function cleanText(string $text): string
    {
        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove zero-width characters for comparison
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);

        return trim($text);
    }

    /**
     * Determine match type based on similarity and watermark.
     */
    private function determineMatchType(float $similarity, bool $watermarkDetected): string
    {
        if ($watermarkDetected) {
            return 'watermark';
        }

        return match (true) {
            $similarity >= 95 => 'exact',
            $similarity >= 85 => 'near_exact',
            $similarity >= 70 => 'substantial',
            default => 'partial',
        };
    }

    /**
     * Extract a matched excerpt from the content.
     */
    private function extractMatchedExcerpt(string $text, Content $content): string
    {
        // Get first 500 characters as excerpt
        $excerpt = mb_substr($text, 0, 500);

        if (mb_strlen($text) > 500) {
            $excerpt .= '...';
        }

        return $excerpt;
    }

    /**
     * Build a search query from content.
     */
    private function buildSearchQuery(Content $content): string
    {
        // Use the post title with quotes for exact phrase matching
        $query = '"' . $content->post_title . '"';

        // Exclude the original domain
        $originalDomain = parse_url($content->post_url, PHP_URL_HOST);
        if ($originalDomain) {
            $query .= ' -site:' . $originalDomain;
        }

        return $query;
    }

    /**
     * Process monitoring results in batch.
     */
    public function processResults(array $results, Content $content, ScanLog $scanLog): int
    {
        $matchCount = 0;

        foreach ($results as $result) {
            $monitoringResult = $this->scanUrl($content, $result['url'], $scanLog);

            if ($monitoringResult) {
                $matchCount++;
            }

            $scanLog->incrementUrlsChecked();
        }

        return $matchCount;
    }
}
