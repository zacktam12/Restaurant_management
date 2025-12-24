<?php
/**
 * Reservation Class
 * Handles reservation management
 */

require_once __DIR__ . '/config.php';

class Reservation {
    private $conn;
    private $table = 'reservations';
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Create reservation
     */
    public function createReservation($restaurant_id, $customer_name, $customer_email, $customer_phone, $date, $time, $guests, $special_requests = null, $customer_id = null) {
        // Validate Capacity first
        if (!$this->isTimeSlotAvailable($restaurant_id, $date, $time, $guests)) {
            return ['success' => false, 'message' => 'Restaurant is fully booked for this time slot. Please choose another time.'];
        }

        $sql = "INSERT INTO {$this->table} (restaurant_id, customer_id, customer_name, customer_email, customer_phone, date, time, guests, special_requests) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisssssis", $restaurant_id, $customer_id, $customer_name, $customer_email, $customer_phone, $date, $time, $guests, $special_requests);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Reservation created successfully',
                'reservation_id' => $this->conn->insert_id
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to create reservation: ' . $stmt->error];
    }
    
    /**
     * Check if time slot has capacity
     */
    private function isTimeSlotAvailable($restaurant_id, $date, $time, $requested_guests) {
        // 1. Get Seating Capacity
        $capSql = "SELECT seating_capacity FROM restaurants WHERE id = ?";
        $stmt = $this->conn->prepare($capSql);
        $stmt->bind_param("i", $restaurant_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $capacity = 0;
        if ($row = $res->fetch_assoc()) {
            $capacity = $row['seating_capacity'];
        }
        $stmt->close();
        
        if ($capacity === 0) return true; // Fail-safe if capacity not set

        // 2. Sum existing guests for this date/time (active reservations only)
        // We match strict time slot for simplicity. 
        $sql = "SELECT SUM(guests) as total_guests FROM reservations 
                WHERE restaurant_id = ? 
                AND date = ? 
                AND time = ? 
                AND status IN ('pending', 'confirmed')";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $restaurant_id, $date, $time);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_guests = 0;
        if ($row = $result->fetch_assoc()) {
            $current_guests = (int)$row['total_guests'];
        }
        $stmt->close();
        
        return ($current_guests + $requested_guests) <= $capacity;
    }
    
    /**
     * Get all reservations
     */
    public function getAllReservations() {
        $sql = "SELECT r.*, rest.name as restaurant_name 
                FROM {$this->table} r 
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id 
                ORDER BY r.date DESC, r.time DESC";
        $result = $this->conn->query($sql);
        
        $reservations = [];
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        
        return $reservations;
    }
    
    /**
     * Get reservation by ID
     */
    public function getReservationById($id) {
        $sql = "SELECT r.*, rest.name as restaurant_name 
                FROM {$this->table} r 
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id 
                WHERE r.id = ?";
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
     * Get reservations by restaurant
     */
    public function getReservationsByRestaurant($restaurant_id) {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY date DESC, time DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $restaurant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservations = [];
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        
        return $reservations;
    }
    
    /**
     * Get reservations by customer email
     */
    public function getReservationsByCustomer($email) {
        $sql = "SELECT r.*, rest.name as restaurant_name 
                FROM {$this->table} r 
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id 
                WHERE r.customer_email = ? 
                ORDER BY r.date DESC, r.time DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservations = [];
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        
        return $reservations;
    }
    
    /**
     * Get reservations by date
     */
    public function getReservationsByDate($date) {
        $sql = "SELECT r.*, rest.name as restaurant_name 
                FROM {$this->table} r 
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id 
                WHERE r.date = ? 
                ORDER BY r.time ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservations = [];
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        
        return $reservations;
    }
    
    /**
     * Get reservations by status
     */
    public function getReservationsByStatus($status) {
        $sql = "SELECT r.*, rest.name as restaurant_name 
                FROM {$this->table} r 
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id 
                WHERE r.status = ? 
                ORDER BY r.date DESC, r.time DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservations = [];
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        
        return $reservations;
    }
    
    /**
     * Update reservation status
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
     * Update reservation
     */
    public function updateReservation($id, $date, $time, $guests, $special_requests = null) {
        $sql = "UPDATE {$this->table} SET date = ?, time = ?, guests = ?, special_requests = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssisi", $date, $time, $guests, $special_requests, $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Reservation updated successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to update reservation'];
    }
    
    /**
     * Cancel reservation
     */
    public function cancelReservation($id) {
        return $this->updateStatus($id, 'cancelled');
    }
    
    /**
     * Delete reservation
     */
    public function deleteReservation($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Reservation deleted successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to delete reservation'];
    }

    /**
     * Get reservations by customer ID
     */
    public function getReservationsByCustomerId($customer_id) {
        $sql = "SELECT r.id, r.restaurant_id, r.date as reservation_date, r.guests as party_size, r.status, rest.name as restaurant_name, rest.cuisine, rest.image 
                FROM {$this->table} r 
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id 
                WHERE r.customer_id = ? 
                ORDER BY r.date DESC 
                LIMIT 5";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservations = [];
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        
        return $reservations;
    }
    
    /**
     * Get all reservations by customer ID (no limit)
     */
    public function getAllReservationsByCustomerId($customer_id) {
        $sql = "SELECT r.*, rest.name as restaurant_name, rest.cuisine, rest.address, rest.image 
                FROM {$this->table} r 
                LEFT JOIN restaurants rest ON r.restaurant_id = rest.id 
                WHERE r.customer_id = ? 
                ORDER BY r.date DESC, r.time DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservations = [];
        while ($row = $result->fetch_assoc()) {
            $reservations[] = $row;
        }
        
        return $reservations;
    }
    
    /**
     * Get reservation counts by status
     */
    public function getReservationCounts() {
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
        $result = $this->conn->query($sql);
        
        $counts = [
            'pending' => 0,
            'confirmed' => 0,
            'cancelled' => 0,
            'completed' => 0,
            'total' => 0
        ];
        
        while ($row = $result->fetch_assoc()) {
            $counts[$row['status']] = (int)$row['count'];
            $counts['total'] += (int)$row['count'];
        }
        
        return $counts;
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