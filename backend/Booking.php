<?php
/**
 * Booking Class
 * Handles booking management for external services
 */

require_once __DIR__ . '/config.php';

class Booking {
    private $conn;
    private $table = 'bookings';
    
    private $client;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        
        require_once __DIR__ . '/ExternalServiceClient.php';
        $this->client = new ExternalServiceClient();
    }
    
    /**
     * Create booking (Internal + External)
     */
    public function createBooking($service_type, $service_id, $customer_id, $date = null, $time = null, $guests = null, $special_requests = null) {
        // 1. Prepare Customer Data
        $customerData = [
            'id' => $customer_id, // For basic identification
            // In a real app, we'd fetch name/email/phone from DB using $customer_id
            // providing defaults here for robustness
            'user_id' => $customer_id,
            'name' => 'Customer #' . $customer_id,
            'email' => 'customer' . $customer_id . '@example.com',
            'phone' => '+251911000000',
            'date' => $date,
            'time' => $time,
            'guests' => $guests,
            'special_requests' => $special_requests
        ];
        
        // 2. Call External Service
        $externalResult = ['success' => false, 'message' => 'Service type not supported'];
        
        try {
            switch ($service_type) {
                case 'tour':
                    $externalResult = $this->client->bookTour($service_id, $customerData);
                    break;
                    
                case 'hotel':
                    $externalResult = $this->client->bookHotel($service_id, $customerData);
                    break;
                    
                case 'taxi':
                    // Extract pickup/dropoff from special_requests or use active defaults
                    // Format in special_requests: "Pickup: X; Dropoff: Y"
                    $pickup = 'Addis Ababa';
                    $dropoff = 'Bole Airport';
                    
                    if ($special_requests) {
                        if (preg_match('/Pickup:\s*([^;]+)/i', $special_requests, $m)) $pickup = trim($m[1]);
                        if (preg_match('/Dropoff:\s*([^;]+)/i', $special_requests, $m)) $dropoff = trim($m[1]);
                    }
                    
                    // Taxi API expects 'pickup_time', map from 'date' + 'time'
                    // Doc example: "2024-12-25 14:30:00" (Space separator)
                    $pickupTime = ($date && $time) ? ($date . ' ' . $time . ':00') : date('Y-m-d H:i:s');
                    $customerData['pickup_time'] = $pickupTime;
                    
                    // Taxi API specific IDs
                    $customerData['service_id'] = $service_id;
                    $customerData['user_id'] = $customer_id;
                    $customerData['guests'] = $guests; // Map party size to passengers
                    
                    $externalResult = $this->client->bookTaxi($pickup, $dropoff, $customerData);
                    break;
                    
                    // Tickets removed
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'External service error: ' . $e->getMessage()];
        }
        
        // 3. If External Booking Successful, Save Locally
        if ($externalResult['success']) {
            $sql = "INSERT INTO {$this->table} (service_type, service_id, customer_id, date, time, guests, special_requests, status, external_reference) VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed', ?)";
            $stmt = $this->conn->prepare($sql);
            
            // Get external reference ID if available
            $ref = $externalResult['data']['booking_id'] ?? $externalResult['booking_id'] ?? 'EXT-' . time();
            
            $stmt->bind_param("siississ", $service_type, $service_id, $customer_id, $date, $time, $guests, $special_requests, $ref);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Booking confirmed with provider!',
                    'booking_id' => $this->conn->insert_id,
                    'external_response' => $externalResult
                ];
            } else {
                 return ['success' => true, 'message' => 'Booking confirmed externally but local save failed.', 'external_response' => $externalResult];
            }
        } else {
            return [
                'success' => false, 
                'message' => 'Provider rejected booking: ' . ($externalResult['message'] ?? 'Unknown error')
            ];
        }
    }
    
    /**
     * Get all bookings
     */
    public function getAllBookings() {
        $sql = "SELECT b.*, u.name as customer_name, u.email as customer_email 
                FROM {$this->table} b 
                LEFT JOIN users u ON b.customer_id = u.id 
                ORDER BY b.created_at DESC";
        $result = $this->conn->query($sql);
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        return $bookings;
    }
    
    /**
     * Get booking by ID
     */
    public function getBookingById($id) {
        $sql = "SELECT b.*, u.name as customer_name, u.email as customer_email 
                FROM {$this->table} b 
                LEFT JOIN users u ON b.customer_id = u.id 
                WHERE b.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get bookings by customer
     */
    public function getBookingsByCustomer($customer_id) {
        $sql = "SELECT * FROM {$this->table} WHERE customer_id = ? ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        return $bookings;
    }
    
    /**
     * Get bookings by service type
     */
    public function getBookingsByServiceType($service_type) {
        $sql = "SELECT b.*, u.name as customer_name, u.email as customer_email 
                FROM {$this->table} b 
                LEFT JOIN users u ON b.customer_id = u.id 
                WHERE b.service_type = ? 
                ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $service_type);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        return $bookings;
    }
    
    /**
     * Update booking status
     */
    public function updateStatus($id, $status) {
        $validStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        
        $sql = "UPDATE {$this->table} SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Status updated successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to update status'];
    }
    
    /**
     * Cancel booking
     */
    public function cancelBooking($id) {
        return $this->updateStatus($id, 'cancelled');
    }
    
    /**
     * Delete booking
     */
    public function deleteBooking($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Booking deleted successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to delete booking'];
    }
    
    /**
     * Close database connection
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
