<?php
/**
 * External Service Consumer Client
 * Consumes services from other groups (Tours, Hotels, Taxis, Travel Tickets)
 * 
 * This class acts as a client to integrate with external service providers
 */

class ExternalServiceClient {
    private $apiKey;
    private $serviceEndpoints;
    
    public function __construct() {
        // API key for authenticating with other services
        $this->apiKey = 'RESTAURANT_CONSUMER_KEY_2025';
        
        // ==================================================================================
        // 🚀 PRODUCTION CONFIGURATION
        // Enter the actual URLs provided by the other groups here.
        // If they are on the same network, these will likely be IP addresses (e.g., http://192.168.1.50/api)
        // ==================================================================================
        
        $tourServiceUrl   = 'https://tour-management-web.onrender.com/api/v1';   
        $hotelServiceUrl  = 'http://localhost/hotel-service/api';  
        $taxiServiceUrl   = 'https://taxi-system.infinityfreeapp.com/api'; 
        
        // Mock Mode (Set to true to switch back to simulator for testing)
        // $baseUrl = 'http://localhost/rest/api/mock-external-services.php';
        
        $this->serviceEndpoints = [
            'tours' => [
                'base_url' => $tourServiceUrl, 
                'name' => 'Tour Operator Service',
                'api_key' => 'demo-api-key', // Specific Key for Tour Group
                'capabilities' => ['book_tour', 'list_tours'],
                'status' => 'production'
            ],
            'hotels' => [
                'base_url' => $hotelServiceUrl,
                'name' => 'Hotel Booking Service',
                'capabilities' => ['book_room', 'list_hotels', 'check_availability'],
                'status' => 'production'
            ],
            'taxis' => [
                'base_url' => $taxiServiceUrl,
                'name' => 'Taxi Service',
                'api_key' => 'TAXI_GROUP_SECURE_KEY_2024', // Specific Key for Taxi Group
                'capabilities' => ['book_taxi', 'list_services'],
                'status' => 'production'
            ]
        ];
    }
    
    /**
     * Make HTTP request to external service
     */
    public function makeRequest($url, $method = 'GET', $data = null, $serviceType = null) {
        $ch = curl_init();
        
        // Determine API Key
        $apiKey = $this->apiKey; // Default
        if ($serviceType && isset($this->serviceEndpoints[$serviceType]['api_key'])) {
            $apiKey = $this->serviceEndpoints[$serviceType]['api_key'];
        }

        $headers = [
            'X-API-Key: ' . $apiKey,
            'X-Consumer: Restaurant-Management-System'
        ];
        
        // Only set Content-Type: JSON if we're sending JSON (not for GET and not for form-encoded services)
        $isTaxi = ($serviceType === 'taxis');
        if (($method === 'POST' || $method === 'PUT') && !$isTaxi) {
            $headers[] = 'Content-Type: application/json';
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_ENCODING => '', // Accept all encodings
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            // Taxis expect form-urlencoded data as per common PHP $_POST usage
            $payload = ($isTaxi && is_array($data)) ? http_build_query($data) : json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            $payload = ($isTaxi && is_array($data)) ? http_build_query($data) : json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        if ($error) return ['success' => false, 'message' => 'Connection error: ' . $error, 'http_code' => $httpCode];
        
        $decoded = json_decode($response, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'Invalid JSON response: ' . $response,
                'raw_response' => $response,
                'http_code' => $httpCode
            ];
        }
        
        return $decoded;
    }
    
    /**
     * Get service information
     */
    public function getServiceInfo($serviceType) {
        if (!isset($this->serviceEndpoints[$serviceType])) {
            return ['success' => false, 'message' => 'Unknown service type'];
        }
        
        return [
            'success' => true,
            'data' => $this->serviceEndpoints[$serviceType]
        ];
    }
    
    /**
     * Helper to construct URL based on environment (Mock vs Production)
     */
    private function buildUrl($serviceType, $endpoint, $params = []) {
        $config = $this->serviceEndpoints[$serviceType];
        $isMock = strpos($config['base_url'], 'mock-external-services.php') !== false;
        
        if ($isMock) {
            // Mock Mode: Convert REST endpoint to query parameters
            // Endpoint /tours -> action=list
            // Endpoint /tours/123 -> action=details&id=123
            // Endpoint /bookings -> action=book
            
            $url = $config['base_url']; // already has ?service=...
            $action = 'list';
            
            if (strpos($endpoint, '/bookings') !== false) {
                $action = 'book';
            } elseif (strpos($endpoint, '/availability') !== false || strpos($endpoint, '/routes') !== false || strpos($endpoint, '/search') !== false) {
                 // specific actions
                 $action = str_replace('/', '', $endpoint); 
            } elseif (preg_match('/\/(\d+)$/', $endpoint, $matches)) {
                $action = 'details';
                $params['id'] = $matches[1];
            }
            
            // Special case mappings
            if ($endpoint === '/health') $action = 'health';
            if ($serviceType === 'taxis' && $endpoint === '/fare-estimate') $action = 'fare_estimate';
            
            $url .= '&action=' . $action;
        } else {
            // Production Mode: Standard REST path
            $url = $config['base_url'] . $endpoint;
        }
        
        if (!empty($params)) {
            $joinChar = (strpos($url, '?') !== false) ? '&' : '?';
            $url .= $joinChar . http_build_query($params);
        }
        
        return $url;
    }

    /**
     * TOUR SERVICE METHODS
     */
    /**
     * TOUR SERVICE METHODS (Updated for Group 1 API)
     */
    public function getTours($destination = null) {
        $params = [];
        if ($destination) $params['q'] = $destination; // API uses 'q' for search
        
        $url = $this->buildUrl('tours', '/tours.php', $params);
        $response = $this->makeRequest($url, 'GET', null, 'tours');
        
        // API returns { "data": [...] }
        if (isset($response['data']) && is_array($response['data'])) {
             return [
                 'success' => true,
                 'data' => array_map(function($tour) {
                     return [
                         'id' => $tour['id'],
                         'name' => $tour['title'],
                         'location' => $tour['location'],
                         'description' => $tour['title'] . ((isset($tour['schedule_date']) && $tour['schedule_date']) ? ' (' . $tour['schedule_date'] . ')' : '') . ' in ' . $tour['location'], 
                         'price' => $tour['price'],
                         'image' => $tour['image'] ?? 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=500&q=80', // Fallback image
                         'available' => 1,
                         'details' => $tour 
                     ];
                 }, $response['data'])
             ];
        }
        
        return ['success' => false, 'message' => 'Failed to fetch tours', 'raw' => $response];
    }
    
    public function getTourDetails($tourId) {
        return $this->makeRequest($this->buildUrl('tours', '/tours.php', ['id' => $tourId]), 'GET', null, 'tours');
    }
    
    public function bookTour($tourId, $customerData, $restaurantBookingRef = null) {
        $url = $this->buildUrl('tours', '/tours.php'); // POST to tours.php creates booking
        
        // Payload: { tour_id, email, name }
        // Payload as per Tour API documentation - either user_id OR email+name
        $payload = [
            'tour_id' => (int)$tourId
        ];
        
        // Use email/name for Partner Integration (Guest Mode)
        // We do not send 'user_id' because customerData['user_id'] is a Local ID, not the Tour System's ID.
        $payload['email'] = $customerData['email'] ?? '';
        $payload['name'] = $customerData['name'] ?? '';
        
        // If we strictly had their ID, we would use:
        // if (isset($customerData['external_tour_user_id'])) $payload['user_id'] = ...
        
        $response = $this->makeRequest($url, 'POST', $payload, 'tours');
        
        // Tour API returns: { "message": "Booking Created", "data": { "tour_id": 1, "user_id": 10, "status": "confirmed" } }
        if (isset($response['message']) && (strpos(strtolower($response['message']), 'booking') !== false || strpos(strtolower($response['message']), 'created') !== false)) {
            return [
                'success' => true,
                'message' => $response['message'] ?? 'Tour booked successfully',
                'booking_details' => $response['data'] ?? []
            ];
        }
        
        return [
            'success' => false, 
            'message' => 'Tour booking failed: ' . ($response['message'] ?? 'Unknown error'),
            'raw' => $response
        ];
    }
    
    /**
     * HOTEL SERVICE METHODS
     */
    public function getHotels($city = null, $checkIn = null, $checkOut = null) {
        $params = [];
        if ($city) $params['city'] = $city;
        if ($checkIn) $params['check_in'] = $checkIn;
        if ($checkOut) $params['check_out'] = $checkOut;
        return $this->makeRequest($this->buildUrl('hotels', '/hotels', $params));
    }
    
    public function getHotelDetails($hotelId) {
        return $this->makeRequest($this->buildUrl('hotels', '/hotels/' . $hotelId));
    }
    
    public function bookHotel($hotelId, $customerData, $restaurantBookingRef = null) {
        $url = $this->buildUrl('hotels', '/bookings');
        $data = array_merge($customerData, [
            'hotel_id' => $hotelId,
            'source_system' => 'restaurant_management',
            'linked_booking' => $restaurantBookingRef
        ]);
        return $this->makeRequest($url, 'POST', $data);
    }
    
    /**
     * TAXI SERVICE METHODS
     */
    /**
     * TAXI SERVICE METHODS (Updated for Group 4 & 8 API)
     */
    public function getTaxiServices() {
        // Endpoint: GET /services.php
        // Note: buildUrl logic for mock mode handles '/services.php' as just another endpoint. 
        // If Production, it appends /services.php
        
        // For Taxi, we want to ensure we hit /services.php
        $url = $this->buildUrl('taxis', '/services.php');
        
        // If Mock logic strips .php, we might need to adjust, but let's assume buildUrl works for standard REST paths
        // Actually, the buildUrl logic (lines 129+) appends $endpoint to base_url.
        // So http.../api/services.php
        
        $response = $this->makeRequest($url, 'GET', null, 'taxis');
        
        // API returns array: [{id, name...}, ...]
        if (is_array($response) && (isset($response[0]) || empty($response))) {
            return [
                'success' => true,
                'data' => array_map(function($service) {
                    return [
                        'id' => $service['id'],
                        'name' => $service['name'],
                        'description' => $service['description'],
                        'price' => $service['base_price'], // Display base price
                        'price_per_km' => $service['price_per_km'],
                        'type' => 'taxi',
                        'available' => 1 // Assume available if listed
                    ];
                }, $response)
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to fetch taxi services', 'raw' => $response];
    }
    
    // Alias for compatibility
    public function getTaxiAvailability($location = null) {
        return $this->getTaxiServices();
    }
    
    public function bookTaxi($pickup, $dropoff, $customerData, $restaurantBookingRef = null) {
        $url = $this->buildUrl('taxis', '/bookings.php');
        
        // Payload as per working snippet
        $payload = [
            'user_id' => (int)($customerData['user_id'] ?? 0),
            'pickup_location' => $pickup,
            'destination' => $dropoff, // Renamed from dropoff_location
            'pickup_time' => $customerData['pickup_time'], // Format: Y-m-d H:i:s
            'passengers' => (int)($customerData['guests'] ?? 1)
        ];
        
        // Keep email and service_id just in case they are actually needed (merging snippet + previous contract)
        if (isset($customerData['email'])) $payload['email'] = $customerData['email'];
        if (isset($customerData['service_id'])) $payload['service_id'] = (int)$customerData['service_id'];
        
        $response = $this->makeRequest($url, 'POST', $payload, 'taxis');
        
        // Response: { message: "...", booking_id: 55 }
        if (isset($response['booking_id']) || (isset($response['success']) && $response['success'])) {
            return [
                'success' => true,
                'message' => $response['message'] ?? 'Taxi booking confirmed',
                'booking_id' => $response['booking_id'] ?? ($response['booking']['booking_id'] ?? 'EXT-' . time()),
                'details' => $response
            ];
        }
        
        return [
            'success' => false, 
            'message' => 'Taxi booking failed: ' . ($response['message'] ?? 'Unknown error'),
            'raw' => $response
        ];
    }
    
    // Travel Ticket Service removed as per request    
    public function getTicketDetails($ticketId) {
        return $this->makeRequest($this->buildUrl('tickets', '/tickets/' . $ticketId));
    }
    
    /**
     * CROSS-SERVICE PACKAGE BOOKING
     * Book multiple services together (restaurant + hotel + taxi + tickets)
     */
    public function bookCompletePackage($packageData) {
        $results = [];
        
        // Book restaurant first
        if (!empty($packageData['restaurant'])) {
            $results['restaurant'] = [
                'success' => true,
                'booking_id' => $packageData['restaurant']['booking_id'],
                'message' => 'Restaurant booking already completed'
            ];
        }
        
        // Book hotel
        if (!empty($packageData['hotel'])) {
            $results['hotel'] = $this->bookHotel(
                $packageData['hotel']['hotel_id'],
                $packageData['customer'],
                $packageData['restaurant']['booking_id'] ?? null
            );
        }
        
        // Book tour
        if (!empty($packageData['tour'])) {
            $results['tour'] = $this->bookTour(
                $packageData['tour']['tour_id'],
                $packageData['customer'],
                $packageData['restaurant']['booking_id'] ?? null
            );
        }
        
        // Book taxi
        if (!empty($packageData['taxi'])) {
            $results['taxi'] = $this->bookTaxi(
                $packageData['taxi']['pickup'],
                $packageData['taxi']['dropoff'],
                $packageData['customer'],
                $packageData['restaurant']['booking_id'] ?? null
            );
        }
        
        // Book travel tickets
        if (!empty($packageData['ticket'])) {
            $results['ticket'] = $this->bookTicket(
                $packageData['ticket'],
                $packageData['customer'],
                $packageData['restaurant']['booking_id'] ?? null
            );
        }
        
        $allSuccess = true;
        foreach ($results as $service => $result) {
            if (isset($result['success']) && !$result['success']) {
                $allSuccess = false;
            }
        }
        
        return [
            'success' => $allSuccess,
            'package_id' => 'PKG-' . date('Ymd') . '-' . strtoupper(substr(md5(time()), 0, 8)),
            'bookings' => $results,
            'message' => $allSuccess ? 'All services booked successfully' : 'Some bookings failed'
        ];
    }
    
    /**
     * Check health of external services
     */
    public function checkServiceHealth($serviceType = null) {
        $services = $serviceType ? [$serviceType => $this->serviceEndpoints[$serviceType]] : $this->serviceEndpoints;
        $health = [];
        
        foreach ($services as $name => $config) {
            $startTime = microtime(true);
            $response = $this->makeRequest($config['base_url'] . '/health');
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            $health[$name] = [
                'status' => $response['success'] ?? false ? 'online' : 'offline',
                'response_time_ms' => $responseTime,
                'endpoint' => $config['base_url']
            ];
        }
        
        return $health;
    }
}
