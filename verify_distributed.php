<?php
/**
 * Final Verification Script
 * Verifies that the distributed system architecture is working correctly
 * Checks both Provider (incoming) and Consumer (outgoing) capabilities
 */

header('Content-Type: text/plain');

echo "==================================================\n";
echo "DISTRIBUTED SYSTEM ARCHITECTURE - VERIFICATION\n";
echo "==================================================\n\n";

// 1. VERIFY SERVICE PROVIDER (Incoming)
echo "[1] Testing Service Provider (Your API)...\n";
$providerUrl = 'http://localhost/rest/api/service-provider.php?action=service_info';
$response = file_get_contents($providerUrl);
$data = json_decode($response, true);

if ($data && isset($data['success']) && $data['success']) {
    echo "✅ Provider API is ONLINE\n";
    echo "   Service: " . $data['data']['service_name'] . "\n";
    echo "   Version: " . $data['data']['version'] . "\n";
    echo "   Capabilities: " . implode(', ', $data['data']['capabilities']) . "\n";
} else {
    echo "❌ Provider API Failed\n";
    echo "   Response: " . substr($response, 0, 100) . "...\n";
}
echo "\n";

// 2. VERIFY MOCK SERVICES (Infrastructure for outgoing)
echo "[2] Testing Mock Service Infrastructure...\n";
$mockUrl = 'http://localhost/rest/api/mock-external-services.php?service=tour-service&action=list';
$mockResponse = file_get_contents($mockUrl);
$mockData = json_decode($mockResponse, true);

if ($mockData && isset($mockData['success']) && $mockData['success']) {
    echo "✅ Mock Services are ONLINE\n";
    echo "   Service: " . $mockData['service'] . "\n";
    echo "   Items Found: " . count($mockData['data']) . "\n";
} else {
    echo "❌ Mock Services Failed\n";
}
echo "\n";

// 3. VERIFY SERVICE CONSUMER (Client Logic)
echo "[3] Testing External Service Client...\n";
require_once __DIR__ . '/backend/ExternalServiceClient.php';
$client = new ExternalServiceClient();

// Test Bookings URL Construction
try {
    // We can't easily test the private method, but we can test a public one that uses it
    $tours = $client->getTours();
    if (isset($tours['success']) && $tours['success']) {
        echo "✅ Consumer Client Logic is WORKING\n";
        echo "   Fetched " . count($tours['data']) . " tours via client\n";
    } else {
        echo "❌ Consumer Client Failed\n";
        echo "   Error: " . ($tours['message'] ?? 'Unknown error') . "\n";
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n==================================================\n";
echo "VERIFICATION COMPLETE\n";
echo "==================================================\n";
