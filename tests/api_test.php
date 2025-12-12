<?php

/**
 * ContentShield AI API Test Script
 * Run: php tests/api_test.php
 */

$baseUrl = 'http://127.0.0.1:8088';
$licenseKey = 'CSAI-UPBV-JZNA-KYIL-IFZ1';
$licenseHash = hash('sha256', $licenseKey);
$siteUrl = 'https://example.com';

function apiRequest($method, $url, $data = null, $headers = [])
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $allHeaders = ['Content-Type: application/json'];
    foreach ($headers as $key => $value) {
        $allHeaders[] = "$key: $value";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => json_decode($response, true),
        'raw' => $response
    ];
}

function printResult($name, $result, $expected = 200)
{
    $status = $result['code'] == $expected ? '✅' : '❌';
    echo "\n$status $name (HTTP {$result['code']})\n";
    echo "   " . json_encode($result['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
}

echo "===========================================\n";
echo "ContentShield AI API Tests\n";
echo "===========================================\n";
echo "License Key: $licenseKey\n";
echo "License Hash: $licenseHash\n";
echo "Site URL: $siteUrl\n";

// Test 1: Health Check
$result = apiRequest('GET', "$baseUrl/api/health");
printResult('Health Check', $result);

// Test 2: License Validation
$result = apiRequest('POST', "$baseUrl/api/v1/license/validate", [
    'license_key' => $licenseKey,
    'site_url' => $siteUrl,
    'site_hash' => hash('sha256', $siteUrl . '|test_db'),
    'plugin_version' => '1.0.0'
]);
printResult('License Validation', $result);

// Test 3: License Status
$result = apiRequest('GET', "$baseUrl/api/v1/license/status", null, [
    'Authorization' => "Bearer $licenseHash",
    'X-Site-URL' => $siteUrl
]);
printResult('License Status', $result);

// Test 4: Content Registration
$result = apiRequest('POST', "$baseUrl/api/v1/content/register", [
    'post_id' => 1,
    'post_url' => "$siteUrl/my-first-post",
    'post_title' => 'My First Protected Post',
    'fingerprint' => 'a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4',
    'content_hash' => 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2',
    'word_count' => 1500
], [
    'Authorization' => "Bearer $licenseHash",
    'X-Site-URL' => $siteUrl
]);
printResult('Content Registration', $result, 201);

// Test 5: Content List
$result = apiRequest('GET', "$baseUrl/api/v1/content/list", null, [
    'Authorization' => "Bearer $licenseHash",
    'X-Site-URL' => $siteUrl
]);
printResult('Content List', $result);

// Test 6: Monitoring Status
$result = apiRequest('GET', "$baseUrl/api/v1/monitoring/status", null, [
    'Authorization' => "Bearer $licenseHash",
    'X-Site-URL' => $siteUrl
]);
printResult('Monitoring Status', $result);

// Test 7: Dashboard Stats
$result = apiRequest('GET', "$baseUrl/api/v1/reports/dashboard", null, [
    'Authorization' => "Bearer $licenseHash",
    'X-Site-URL' => $siteUrl
]);
printResult('Dashboard Stats', $result);

// Test 8: DMCA Templates
$result = apiRequest('GET', "$baseUrl/api/v1/dmca/templates", null, [
    'Authorization' => "Bearer $licenseHash",
    'X-Site-URL' => $siteUrl
]);
printResult('DMCA Templates', $result);

// Test 9: DMCA Stats
$result = apiRequest('GET', "$baseUrl/api/v1/dmca/stats", null, [
    'Authorization' => "Bearer $licenseHash",
    'X-Site-URL' => $siteUrl
]);
printResult('DMCA Stats', $result);

// Test 10: Trends
$result = apiRequest('GET', "$baseUrl/api/v1/reports/trends?period=30d", null, [
    'Authorization' => "Bearer $licenseHash",
    'X-Site-URL' => $siteUrl
]);
printResult('Trends (30d)', $result);

echo "\n===========================================\n";
echo "API Tests Complete!\n";
echo "===========================================\n";
