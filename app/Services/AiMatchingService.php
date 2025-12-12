<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AiMatchingService
{
    /**
     * Compare two pieces of content using AI.
     */
    public function compareContent(string $original, string $suspect): array
    {
        $provider = config('contentshield.ai.provider', 'openai');

        return match ($provider) {
            'openai' => $this->compareWithOpenAI($original, $suspect),
            'anthropic' => $this->compareWithClaude($original, $suspect),
            default => $this->fallbackComparison($original, $suspect),
        };
    }

    /**
     * Compare content using OpenAI.
     */
    private function compareWithOpenAI(string $original, string $suspect): array
    {
        $apiKey = config('services.openai.api_key');

        if (!$apiKey) {
            Log::warning('OpenAI API key not configured');
            return $this->fallbackComparison($original, $suspect);
        }

        try {
            // Truncate content to fit within token limits
            $originalTruncated = mb_substr($original, 0, 4000);
            $suspectTruncated = mb_substr($suspect, 0, 4000);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a plagiarism detection expert. Analyze the two pieces of content and determine if the second is plagiarized from the first. Respond with a JSON object containing: similarity_score (0-100), is_plagiarism (boolean), confidence (low/medium/high), and explanation (brief).',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Original content:\n\n{$originalTruncated}\n\n---\n\nSuspect content:\n\n{$suspectTruncated}",
                    ],
                ],
                'temperature' => 0,
                'response_format' => ['type' => 'json_object'],
            ]);

            if (!$response->successful()) {
                Log::error('OpenAI API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->fallbackComparison($original, $suspect);
            }

            $data = $response->json();
            $result = json_decode($data['choices'][0]['message']['content'], true);

            return [
                'similarity_score' => $result['similarity_score'] ?? 0,
                'is_plagiarism' => $result['is_plagiarism'] ?? false,
                'confidence' => $result['confidence'] ?? 'low',
                'explanation' => $result['explanation'] ?? '',
                'method' => 'openai',
            ];

        } catch (\Exception $e) {
            Log::error('Error in OpenAI comparison', ['error' => $e->getMessage()]);
            return $this->fallbackComparison($original, $suspect);
        }
    }

    /**
     * Compare content using Claude.
     */
    private function compareWithClaude(string $original, string $suspect): array
    {
        $apiKey = config('services.anthropic.api_key');

        if (!$apiKey) {
            Log::warning('Anthropic API key not configured');
            return $this->fallbackComparison($original, $suspect);
        }

        try {
            $originalTruncated = mb_substr($original, 0, 4000);
            $suspectTruncated = mb_substr($suspect, 0, 4000);

            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-3-5-haiku-latest',
                'max_tokens' => 1024,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "You are a plagiarism detection expert. Analyze if the suspect content is plagiarized from the original.\n\nOriginal:\n{$originalTruncated}\n\n---\n\nSuspect:\n{$suspectTruncated}\n\nRespond with ONLY a JSON object: {\"similarity_score\": 0-100, \"is_plagiarism\": boolean, \"confidence\": \"low\"/\"medium\"/\"high\", \"explanation\": \"brief\"}",
                    ],
                ],
            ]);

            if (!$response->successful()) {
                Log::error('Claude API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->fallbackComparison($original, $suspect);
            }

            $data = $response->json();
            $content = $data['content'][0]['text'] ?? '';

            // Extract JSON from response
            preg_match('/\{.*\}/s', $content, $matches);
            if (!empty($matches[0])) {
                $result = json_decode($matches[0], true);
                return [
                    'similarity_score' => $result['similarity_score'] ?? 0,
                    'is_plagiarism' => $result['is_plagiarism'] ?? false,
                    'confidence' => $result['confidence'] ?? 'low',
                    'explanation' => $result['explanation'] ?? '',
                    'method' => 'anthropic',
                ];
            }

            return $this->fallbackComparison($original, $suspect);

        } catch (\Exception $e) {
            Log::error('Error in Claude comparison', ['error' => $e->getMessage()]);
            return $this->fallbackComparison($original, $suspect);
        }
    }

    /**
     * Fallback comparison using text similarity algorithms.
     */
    private function fallbackComparison(string $original, string $suspect): array
    {
        // Normalize texts
        $original = $this->normalizeText($original);
        $suspect = $this->normalizeText($suspect);

        // Use multiple algorithms for comparison
        $levenshteinSimilarity = $this->levenshteinSimilarity($original, $suspect);
        $jaroWinklerSimilarity = $this->jaroWinklerSimilarity($original, $suspect);
        $ngramSimilarity = $this->ngramSimilarity($original, $suspect);

        // Weighted average
        $similarity = ($levenshteinSimilarity * 0.3) + ($jaroWinklerSimilarity * 0.3) + ($ngramSimilarity * 0.4);

        return [
            'similarity_score' => round($similarity, 2),
            'is_plagiarism' => $similarity >= 70,
            'confidence' => match (true) {
                $similarity >= 90 => 'high',
                $similarity >= 70 => 'medium',
                default => 'low',
            },
            'explanation' => 'Comparison using text similarity algorithms.',
            'method' => 'fallback',
            'details' => [
                'levenshtein' => $levenshteinSimilarity,
                'jaro_winkler' => $jaroWinklerSimilarity,
                'ngram' => $ngramSimilarity,
            ],
        ];
    }

    /**
     * Normalize text for comparison.
     */
    private function normalizeText(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Calculate similarity using Levenshtein distance.
     */
    private function levenshteinSimilarity(string $s1, string $s2): float
    {
        // For very long strings, use sampling
        if (strlen($s1) > 1000 || strlen($s2) > 1000) {
            $s1 = mb_substr($s1, 0, 1000);
            $s2 = mb_substr($s2, 0, 1000);
        }

        $maxLen = max(strlen($s1), strlen($s2));
        if ($maxLen === 0) {
            return 100;
        }

        $distance = levenshtein($s1, $s2);
        return ((($maxLen - $distance) / $maxLen) * 100);
    }

    /**
     * Calculate Jaro-Winkler similarity.
     */
    private function jaroWinklerSimilarity(string $s1, string $s2): float
    {
        $s1Len = strlen($s1);
        $s2Len = strlen($s2);

        if ($s1Len === 0 && $s2Len === 0) {
            return 100;
        }

        // For very long strings, use sampling
        if ($s1Len > 500 || $s2Len > 500) {
            $s1 = mb_substr($s1, 0, 500);
            $s2 = mb_substr($s2, 0, 500);
            $s1Len = strlen($s1);
            $s2Len = strlen($s2);
        }

        $matchDistance = (int) floor(max($s1Len, $s2Len) / 2) - 1;

        $s1Matches = array_fill(0, $s1Len, false);
        $s2Matches = array_fill(0, $s2Len, false);

        $matches = 0;
        $transpositions = 0;

        for ($i = 0; $i < $s1Len; $i++) {
            $start = max(0, $i - $matchDistance);
            $end = min($i + $matchDistance + 1, $s2Len);

            for ($j = $start; $j < $end; $j++) {
                if ($s2Matches[$j] || $s1[$i] !== $s2[$j]) {
                    continue;
                }
                $s1Matches[$i] = true;
                $s2Matches[$j] = true;
                $matches++;
                break;
            }
        }

        if ($matches === 0) {
            return 0;
        }

        $k = 0;
        for ($i = 0; $i < $s1Len; $i++) {
            if (!$s1Matches[$i]) {
                continue;
            }
            while (!$s2Matches[$k]) {
                $k++;
            }
            if ($s1[$i] !== $s2[$k]) {
                $transpositions++;
            }
            $k++;
        }

        $jaro = (($matches / $s1Len) + ($matches / $s2Len) + (($matches - $transpositions / 2) / $matches)) / 3;

        // Calculate common prefix
        $prefix = 0;
        for ($i = 0; $i < min($s1Len, $s2Len, 4); $i++) {
            if ($s1[$i] === $s2[$i]) {
                $prefix++;
            } else {
                break;
            }
        }

        return ($jaro + ($prefix * 0.1 * (1 - $jaro))) * 100;
    }

    /**
     * Calculate n-gram similarity.
     */
    private function ngramSimilarity(string $s1, string $s2, int $n = 3): float
    {
        $ngrams1 = $this->getNgrams($s1, $n);
        $ngrams2 = $this->getNgrams($s2, $n);

        if (empty($ngrams1) || empty($ngrams2)) {
            return 0;
        }

        $intersection = count(array_intersect($ngrams1, $ngrams2));
        $union = count(array_unique(array_merge($ngrams1, $ngrams2)));

        return ($intersection / $union) * 100;
    }

    /**
     * Generate n-grams from text.
     */
    private function getNgrams(string $text, int $n): array
    {
        $words = explode(' ', $text);

        if (count($words) < $n) {
            return [$text];
        }

        $ngrams = [];
        for ($i = 0; $i <= count($words) - $n; $i++) {
            $ngrams[] = implode(' ', array_slice($words, $i, $n));
        }

        return $ngrams;
    }

    /**
     * Generate text embedding using OpenAI.
     */
    public function generateEmbedding(string $text): ?array
    {
        $apiKey = config('services.openai.api_key');

        if (!$apiKey) {
            return null;
        }

        $cacheKey = 'embedding_' . md5($text);

        return Cache::remember($cacheKey, 3600, function () use ($apiKey, $text) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post('https://api.openai.com/v1/embeddings', [
                    'model' => 'text-embedding-3-small',
                    'input' => mb_substr($text, 0, 8000),
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $data['data'][0]['embedding'] ?? null;
                }

            } catch (\Exception $e) {
                Log::error('Error generating embedding', ['error' => $e->getMessage()]);
            }

            return null;
        });
    }

    /**
     * Calculate cosine similarity between embeddings.
     */
    public function cosineSimilarity(array $embedding1, array $embedding2): float
    {
        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;

        for ($i = 0; $i < count($embedding1); $i++) {
            $dotProduct += $embedding1[$i] * $embedding2[$i];
            $norm1 += $embedding1[$i] ** 2;
            $norm2 += $embedding2[$i] ** 2;
        }

        if ($norm1 === 0 || $norm2 === 0) {
            return 0;
        }

        return $dotProduct / (sqrt($norm1) * sqrt($norm2));
    }
}
