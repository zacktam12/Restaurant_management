<?php
/**
 * Reservation Class
 * Handles restaurant reservation operations
 */

require_once 'Database.php';

class Reservation {
    private $db;
    private $table = 'reservations';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all reservations
     */
    public function getAllReservations() {
        $query = "SELECT * FROM {$this->table} ORDER BY date DESC, time DESC";

        try {
            return $this->db->select($query);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get reservations by restaurant
     */
    public function getReservationsByRestaurant($restaurantId) {
        $query = "SELECT * FROM {$this->table} WHERE restaurant_id = ? ORDER BY date DESC, time DESC";
        $params = [$restaurantId];
        $paramTypes = "i";

        try {
            return $this->db->select($query, $params, $paramTypes);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get reservation by ID
     */
    public function getReservationById($id) {
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
     * Create a new reservation
     */
    public function createReservation($restaurantId, $customerName, $customerEmail, $customerPhone, $date, $time, $guests, $specialRequests = null) {
        $query = "INSERT INTO {$this->table} (restaurant_id, customer_name, customer_email, customer_phone, date, time, guests, special_requests) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [$restaurantId, $customerName, $customerEmail, $customerPhone, $date, $time, $guests, $specialRequests];
        $paramTypes = "isssssis";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Reservation created successfully',
                    'reservation_id' => $result['insert_id']
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to create reservation'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Update reservation status
     */
    public function updateReservationStatus($id, $status) {
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
                    'message' => 'Reservation status updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update reservation status'
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
     * Update reservation details
     */
    public function updateReservation($id, $date, $time, $guests, $specialRequests = null) {
        $query = "UPDATE {$this->table} SET date = ?, time = ?, guests = ?, special_requests = ?, updated_at = NOW() WHERE id = ?";
        $params = [$date, $time, $guests, $specialRequests, $id];
        $paramTypes = "ssisi";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Reservation updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update reservation'
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
     * Delete reservation
     */
    public function deleteReservation($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $params = [$id];
        $paramTypes = "i";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'Reservation deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete reservation'
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
     * Get reservations by status
     */
    public function getReservationsByStatus($status) {
        $query = "SELECT * FROM {$this->table} WHERE status = ? ORDER BY date DESC, time DESC";
        $params = [$status];
        $paramTypes = "s";

        try {
            return $this->db->select($query, $params, $paramTypes);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get upcoming reservations for a restaurant
     */
    public function getUpcomingReservations($restaurantId, $days = 30) {
        $query = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND date >= CURDATE() AND date <= DATE_ADD(CURDATE(), INTERVAL ? DAY) ORDER BY date, time";
        $params = [$restaurantId, $days];
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