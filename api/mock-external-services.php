<?php
/**
 * Mock External Services Simulator
 * Simulates responses from external services (tours, hotels, taxis, tickets)
 * This allows testing the distributed system without requiring actual external services
 */

header('Content-Type: application/json');

// Simulate different external services based on the path
$path = $_SERVER['REQUEST_URI'];
$service = 'unknown';

if (strpos($path, 'tour-service') !== false) {
    $service = 'tour';
} elseif (strpos($path, 'hotel-service') !== false) {
    $service = 'hotel';
} elseif (strpos($path, 'taxi-service') !== false) {
    $service = 'taxi';
} elseif (strpos($path, 'ticket-service') !== false) {
    $service = 'ticket';
}

$action = $_GET['action'] ?? 'list';

// Mock data
$mockTours = [
    ['id' => 1, 'name' => 'Paris City Tour', 'destination' => 'Paris', 'price' => 99, 'duration' => '4 hours'],
    ['id' => 2, 'name' => 'Eiffel Tower Experience', 'destination' => 'Paris', 'price' => 149, 'duration' => '3 hours'],
    ['id' => 3, 'name' => 'Louvre Museum Tour', 'destination' => 'Paris', 'price' => 79, 'duration' => '2 hours']
];

$mockHotels = [
    ['id' => 1, 'name' => 'Grand Hotel Paris', 'city' => 'Paris', 'price_per_night' => 250, 'rating' => 4.5],
    ['id' => 2, 'name' => 'Boutique Hotel Downtown', 'city' => 'Paris', 'price_per_night' => 180, 'rating' => 4.3],
    ['id' => 3, 'name' => 'Luxury Resort', 'city' => 'Paris', 'price_per_night' => 450, 'rating' => 5.0]
];

$mockTaxis = [
    ['id' => 1, 'driver' => 'John Driver', 'vehicle' => 'Toyota Camry', 'available' => true, 'location' => 'Downtown'],
    ['id' => 2, 'driver' => 'Mary Transport', 'vehicle' => 'Honda Accord', 'available' => true, 'location' => 'Airport']
];

$mockTickets = [
    ['id' => 1, 'route' => 'New York → Los Angeles', 'departure' => '10:00', 'price' => 299, 'class' => 'economy'],
    ['id' => 2, 'route' => 'Paris → London', 'departure' => '14:30', 'price' => 199, 'class' => 'business'],
    ['id' => 3, 'route' => 'Tokyo → Seoul', 'departure' => '08:00', 'price' => 249, 'class' => 'economy']
];

$response = [];

switch ($service) {
    case 'tour':
        $response = [
            'success' => true,
            'service' => 'Tour Operator Service (SIMULATED)',
            'data' => $mockTours,
            'message' => 'This is a simulated response. In production, this would come from the actual tour service provider.'
        ];
        break;
        
    case 'hotel':
        $response = [
            'success' => true,
            'service' => 'Hotel Booking Service (SIMULATED)',
            'data' => $mockHotels,
            'message' => 'This is a simulated response. In production, this would come from the actual hotel service provider.'
        ];
        break;
        
    case 'taxi':
        $response = [
            'success' => true,
            'service' => 'Taxi Service (SIMULATED)',
            'data' => $mockTaxis,
            'message' => 'This is a simulated response. In production, this would come from the actual taxi service provider.'
        ];
        break;
        
    case 'ticket':
        $response = [
            'success' => true,
            'service' => 'Travel Ticket Service (SIMULATED)',
            'data' => $mockTickets,
            'message' => 'This is a simulated response. In production, this would come from the actual ticket service provider.'
        ];
        break;
        
    default:
        $response = [
            'success' => false,
            'message' => 'Unknown service type'
        ];
}

echo json_encode($response, JSON_PRETTY_PRINT);
