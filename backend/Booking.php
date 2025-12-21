<?php
/**
 * Booking Class
 * Handles external service booking operations (tours, hotels, taxis)
 */

require_once 'Database.php';

class Booking {
    private $db;
    private $table = 'bookings';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all bookings for a customer
     */
    public function getBookingsByCustomer($customerId) {
        $query = "SELECT * FROM {$this->table} WHERE customer_id = ? ORDER BY created_at DESC";
        $params = [$customerId];
        $paramTypes = "i";

        try {
            return $this->db->select($query, $params, $paramTypes);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get all bookings
     */
    public function getAllBookings() {
        $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC";

        try {
            return $this->db->select($query);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get booking by ID
     */
    public function getBookingById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $params = [$id];
        $paramTypes = "i";

        try {
            $result = $this->db->select($query, $params, $paramTypes);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Create a new booking
     */
    public function createBooking($serviceType, $serviceId, $customerId, $date = null, $time = null, $guests = null, $specialRequests = null) {
        $query = "INSERT INTO {$this->table} (service_type, service_id, customer_id, date, time, guests, special_requests) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params = [$serviceType, $serviceId, $customerId, $date, $time, $guests, $specialRequests];
        $paramTypes = "siissis";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Booking created successfully',
                    'booking_id' => $result['insert_id']
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to create booking'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus($id, $status) {
        $validStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }

        $query = "UPDATE {$this->table} SET status = ?, updated_at = NOW() WHERE id = ?";
        $params = [$status, $id];
        $paramTypes = "si";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Booking status updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update booking status'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update booking details
     */
    public function updateBooking($id, $date, $time, $guests, $specialRequests = null) {
        $query = "UPDATE {$this->table} SET date = ?, time = ?, guests = ?, special_requests = ?, updated_at = NOW() WHERE id = ?";
        $params = [$date, $time, $guests, $specialRequests, $id];
        $paramTypes = "ssisi";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Booking updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update booking'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete booking
     */
    public function deleteBooking($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $params = [$id];
        $paramTypes = "i";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Booking deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete booking'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get bookings by service type
     */
    public function getBookingsByServiceType($serviceType) {
        $query = "SELECT * FROM {$this->table} WHERE service_type = ? ORDER BY created_at DESC";
        $params = [$serviceType];
        $paramTypes = "s";

        try {
            return $this->db->select($query, $params, $paramTypes);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get bookings by status
     */
    public function getBookingsByStatus($status) {
        $query = "SELECT * FROM {$this->table} WHERE status = ? ORDER BY created_at DESC";
        $params = [$status];
        $paramTypes = "s";

        try {
            return $this->db->select($query, $params, $paramTypes);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get upcoming bookings for a customer
     */
    public function getUpcomingBookings($customerId, $days = 30) {
        $query = "SELECT * FROM {$this->table} WHERE customer_id = ? AND date >= CURDATE() AND date <= DATE_ADD(CURDATE(), INTERVAL ? DAY) ORDER BY date, time";
        $params = [$customerId, $days];
        $paramTypes = "ii";

        try {
            return $this->db->select($query, $params, $paramTypes);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Close database connection
     */
    public function close() {
        $this->db->close();
    }
}
?>