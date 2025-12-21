<?php
/**
 * Integration Test Script
 * Simple script to demonstrate integration testing between service groups
 */

// Configuration
$baseUrl = 'http://localhost/restaurant-management-system/api';
$apiKey = 'YOUR_API_KEY_HERE'; // Replace with actual API key

echo "=== Restaurant Management System Integration Tests ===\n\n";

// Test 1: Get all restaurants (No authentication required)
echo "Test 1: Get all restaurants\n";
$response = makeRequest('GET', $baseUrl . '/service_provider.php/restaurants');
if ($response['success']) {
    echo "✓ PASS: Retrieved " . count($response['data']) . " restaurants\n";
} else {
    echo "✗ FAIL: " . $response['message'] . "\n";
}

// Test 2: Get specific restaurant
echo "\nTest 2: Get specific restaurant\n";
$response = makeRequest('GET', $baseUrl . '/service_provider.php/restaurants/1');
if ($response['success']) {
    echo "✓ PASS: Retrieved restaurant: " . $response['data']['name'] . "\n";
} else {
    echo "✗ FAIL: " . $response['message'] . "\n";
}

// Test 3: Search restaurants
echo "\nTest 3: Search restaurants\n";
$response = makeRequest('GET', $baseUrl . '/service_provider.php/restaurants?search=Italian');
if ($response['success']) {
    echo "✓ PASS: Found " . count($response['data']) . " Italian restaurants\n";
} else {
    echo "✗ FAIL: " . $response['message'] . "\n";
}

// Test 4: Check availability (No authentication required)
echo "\nTest 4: Check restaurant availability\n";
$response = makeRequest('GET', $baseUrl . '/service_provider.php/availability/1?date=2025-12-25');
if ($response['success']) {
    echo "✓ PASS: Availability checked for restaurant ID 1\n";
    echo "  Total seats: " . $response['data']['total_seats'] . "\n";
    echo "  Available seats: " . $response['data']['available_seats'] . "\n";
} else {
    echo "✗ FAIL: " . $response['message'] . "\n";
}

// Test 5: Create reservation (No authentication required for demo)
echo "\nTest 5: Create reservation\n";
$reservationData = [
    'restaurant_id' => 1,
    'customer_name' => 'Integration Tester',
    'customer_email' => 'tester@example.com',
    'customer_phone' => '+1234567890',
    'date' => '2025-12-25',
    'time' => '19:00:00',
    'guests' => 4,
    'special_requests' => 'Window seat preferred'
];

if ($apiKey === 'YOUR_API_KEY_HERE' || $apiKey === '') {
    echo "↷ SKIP: API key not configured. Set \$apiKey to run write-operation tests.\n";
} else {
    $response = makeRequest('POST', $baseUrl . '/service_provider.php/reservations', $reservationData, $apiKey);
    if ($response['success']) {
        echo "✓ PASS: Reservation created successfully\n";
        echo "  Reservation ID: " . ($response['reservation_id'] ?? ($response['data']['reservation_id'] ?? '')) . "\n";
    } else {
        echo "✗ FAIL: " . $response['message'] . "\n";
    }
}

// Test 6: Get menu items
echo "\nTest 6: Get menu items\n";
$response = makeRequest('GET', $baseUrl . '/service_provider.php/menu?restaurant_id=1');
if ($response['success']) {
    echo "✓ PASS: Retrieved " . count($response['data']) . " menu items\n";
} else {
    echo "✗ FAIL: " . $response['message'] . "\n";
}

// Test 7: Consume external services (tours)
echo "\nTest 7: Get tours from external services\n";
$response = makeRequest('GET', $baseUrl . '/service_consumer.php/tours');
if ($response['success']) {
    echo "✓ PASS: Retrieved " . count($response['data']) . " tours\n";
} else {
    echo "✗ FAIL: " . $response['message'] . "\n";
}

// Test 8: Book a tour
echo "\nTest 8: Book a tour\n";
$tourBookingData = [
    'tour_id' => 1,
    'customer_name' => 'Integration Tester',
    'customer_email' => 'tester@example.com',
    'date' => '2025-12-25',
    'participants' => 4
];

if ($apiKey === 'YOUR_API_KEY_HERE' || $apiKey === '') {
    echo "↷ SKIP: API key not configured. Set \$apiKey to run write-operation tests.\n";
} else {
    $response = makeRequest('POST', $baseUrl . '/service_consumer.php/bookings/tour', $tourBookingData, $apiKey);
    if ($response['success']) {
        echo "✓ PASS: Tour booked successfully\n";
    } else {
        echo "✗ FAIL: " . $response['message'] . "\n";
    }
}

// Test 9: Get hotels
echo "\nTest 9: Get hotels from external services\n";
$response = makeRequest('GET', $baseUrl . '/service_consumer.php/hotels');
if ($response['success']) {
    echo "✓ PASS: Retrieved " . count($response['data']) . " hotels\n";
} else {
    echo "✗ FAIL: " . $response['message'] . "\n";
}

// Test 10: Get taxis
echo "\nTest 10: Get taxi services\n";
$response = makeRequest('GET', $baseUrl . '/service_consumer.php/taxis');
if ($response['success']) {
    echo "✓ PASS: Retrieved " . count($response['data']) . " taxi services\n";
} else {
    echo "✗ FAIL: " . $response['message'] . "\n";
}

echo "\n=== Integration Tests Completed ===\n";

/**
 * Make HTTP request helper function
 */
function makeRequest($method, $url, $data = null, $apiKey = null) {
    $ch = curl_init();

    $headers = [
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    if ($apiKey !== null && $apiKey !== '' && $apiKey !== 'YOUR_API_KEY_HERE') {
        $headers[] = 'Authorization: Bearer ' . $apiKey;
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        return ['success' => false, 'message' => 'cURL Error: ' . $error];
    }
    
    $responseData = json_decode($response, true);

    if (!is_array($responseData)) {
        $responseData = [];
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        return $responseData;
    }

    return [
        'success' => false,
        'message' => 'HTTP ' . $httpCode . ': ' . ($responseData['message'] ?? 'Unknown error')
    ];
}
?>