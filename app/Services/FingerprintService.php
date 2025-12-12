<?php

namespace App\Services;

use App\Models\Content;

class FingerprintService
{
    /**
     * Number of bits for SimHash.
     */
    private const HASH_BITS = 128;

    /**
     * Generate a SimHash fingerprint from text content.
     */
    public function generateFingerprint(string $content): string
    {
        // Normalize the content
        $content = $this->normalizeContent($content);

        // Tokenize into shingles
        $shingles = $this->generateShingles($content, 3);

        // Generate SimHash
        return $this->simHash($shingles);
    }

    /**
     * Generate a content hash (SHA256).
     */
    public function generateContentHash(string $content): string
    {
        return hash('sha256', $this->normalizeContent($content));
    }

    /**
     * Calculate similarity between two fingerprints.
     */
    public function calculateSimilarity(string $fingerprint1, string $fingerprint2): float
    {
        // Convert hex strings to binary
        $bin1 = $this->hexToBin($fingerprint1);
        $bin2 = $this->hexToBin($fingerprint2);

        // Calculate Hamming distance
        $hammingDistance = $this->hammingDistance($bin1, $bin2);

        // Convert to similarity percentage
        $totalBits = self::HASH_BITS;
        $similarity = (($totalBits - $hammingDistance) / $totalBits) * 100;

        return round($similarity, 2);
    }

    /**
     * Find similar content by fingerprint.
     */
    public function findSimilar(string $fingerprint, float $minSimilarity = 70.0, ?int $excludeContentId = null): array
    {
        $results = [];

        $query = Content::select('id', 'fingerprint', 'post_id', 'post_title', 'post_url');

        if ($excludeContentId) {
            $query->where('id', '!=', $excludeContentId);
        }

        // Get all fingerprints and compare
        // Note: In production, you'd want to use a more efficient similarity search
        // like pgvector or a dedicated similarity index
        $contents = $query->get();

        foreach ($contents as $content) {
            $similarity = $this->calculateSimilarity($fingerprint, $content->fingerprint);

            if ($similarity >= $minSimilarity) {
                $results[] = [
                    'content' => $content,
                    'similarity' => $similarity,
                ];
            }
        }

        // Sort by similarity descending
        usort($results, fn ($a, $b) => $b['similarity'] <=> $a['similarity']);

        return $results;
    }

    /**
     * Normalize content for fingerprinting.
     */
    private function normalizeContent(string $content): string
    {
        // Remove HTML tags
        $content = strip_tags($content);

        // Convert to lowercase
        $content = mb_strtolower($content);

        // Remove extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);

        // Remove punctuation
        $content = preg_replace('/[^\p{L}\p{N}\s]/u', '', $content);

        return trim($content);
    }

    /**
     * Generate word shingles from content.
     */
    private function generateShingles(string $content, int $size = 3): array
    {
        $words = explode(' ', $content);
        $words = array_filter($words, fn ($word) => strlen($word) > 2);
        $words = array_values($words);

        if (count($words) < $size) {
            return [$content];
        }

        $shingles = [];
        for ($i = 0; $i <= count($words) - $size; $i++) {
            $shingles[] = implode(' ', array_slice($words, $i, $size));
        }

        return $shingles;
    }

    /**
     * Generate SimHash from shingles.
     */
    private function simHash(array $shingles): string
    {
        // Initialize bit vector
        $vector = array_fill(0, self::HASH_BITS, 0);

        foreach ($shingles as $shingle) {
            // Get hash of shingle
            $hash = $this->getShingleHash($shingle);

            // Convert to binary representation
            $binary = $this->hexToBin($hash);

            // Update vector based on hash bits
            for ($i = 0; $i < self::HASH_BITS; $i++) {
                if (isset($binary[$i]) && $binary[$i] === '1') {
                    $vector[$i]++;
                } else {
                    $vector[$i]--;
                }
            }
        }

        // Convert vector to binary hash
        $binaryHash = '';
        foreach ($vector as $value) {
            $binaryHash .= $value > 0 ? '1' : '0';
        }

        // Convert binary to hex
        return $this->binToHex($binaryHash);
    }

    /**
     * Get hash for a single shingle.
     */
    private function getShingleHash(string $shingle): string
    {
        // Use MD5 for speed (128 bits = our HASH_BITS)
        return md5($shingle);
    }

    /**
     * Convert hex string to binary string.
     */
    private function hexToBin(string $hex): string
    {
        $bin = '';
        for ($i = 0; $i < strlen($hex); $i++) {
            $bin .= str_pad(base_convert($hex[$i], 16, 2), 4, '0', STR_PAD_LEFT);
        }
        return $bin;
    }

    /**
     * Convert binary string to hex string.
     */
    private function binToHex(string $bin): string
    {
        $hex = '';
        for ($i = 0; $i < strlen($bin); $i += 4) {
            $hex .= base_convert(substr($bin, $i, 4), 2, 16);
        }
        return $hex;
    }

    /**
     * Calculate Hamming distance between two binary strings.
     */
    private function hammingDistance(string $bin1, string $bin2): int
    {
        $distance = 0;
        $length = max(strlen($bin1), strlen($bin2));

        for ($i = 0; $i < $length; $i++) {
            $bit1 = $bin1[$i] ?? '0';
            $bit2 = $bin2[$i] ?? '0';

            if ($bit1 !== $bit2) {
                $distance++;
            }
        }

        return $distance;
    }

    /**
     * Count words in content.
     */
    public function countWords(string $content): int
    {
        $content = strip_tags($content);
        $content = preg_replace('/\s+/', ' ', $content);
        $words = explode(' ', trim($content));
        return count(array_filter($words, fn ($word) => strlen($word) > 0));
    }

    /**
     * Extract zero-width watermark from content.
     */
    public function extractWatermark(string $content): ?string
    {
        // Zero-width characters used for watermarking
        $zwChars = [
            "\u{200B}", // Zero-width space
            "\u{200C}", // Zero-width non-joiner
            "\u{200D}", // Zero-width joiner
            "\u{FEFF}", // Zero-width no-break space
        ];

        $watermark = '';

        for ($i = 0; $i < mb_strlen($content); $i++) {
            $char = mb_substr($content, $i, 1);
            $index = array_search($char, $zwChars);

            if ($index !== false) {
                $watermark .= str_pad(decbin($index), 2, '0', STR_PAD_LEFT);
            }
        }

        if (empty($watermark)) {
            return null;
        }

        // Convert binary to text
        $text = '';
        for ($i = 0; $i < strlen($watermark); $i += 8) {
            $byte = substr($watermark, $i, 8);
            if (strlen($byte) === 8) {
                $text .= chr(bindec($byte));
            }
        }

        return $text ?: null;
    }

    /**
     * Verify watermark matches expected value.
     */
    public function verifyWatermark(string $content, string $expectedWatermark): bool
    {
        $extractedWatermark = $this->extractWatermark($content);

        if (!$extractedWatermark) {
            return false;
        }

        return $extractedWatermark === $expectedWatermark;
    }
}
