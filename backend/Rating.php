<?php
/**
 * Rating Class
 * Manages restaurant ratings and reviews
 */

require_once 'config.php';

class Rating {
    private $connection;
    private $table = 'reviews';
    
    public function __construct() {
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->connection->connect_error) {
            die('Connection failed: ' . $this->connection->connect_error);
        }
    }
    
    /**
     * Add or update a rating
     */
    public function addRating($restaurant_id, $customer_id, $rating, $review = null) {
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'message' => 'Rating must be between 1 and 5'];
        }
        
        $comment = $review ? trim($review) : null;
        
        // Check if rating already exists
        $checkQuery = "SELECT id FROM {$this->table} WHERE restaurant_id = ? AND user_id = ?";
        $stmt = $this->connection->prepare($checkQuery);
        $stmt->bind_param("ii", $restaurant_id, $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingRating = $result->fetch_assoc();
        $stmt->close();
        
        if ($existingRating) {
            // Update existing rating
            $updateQuery = "UPDATE {$this->table} SET rating = ?, comment = ? WHERE restaurant_id = ? AND user_id = ?";
            $stmt = $this->connection->prepare($updateQuery);
            $stmt->bind_param("isii", $rating, $comment, $restaurant_id, $customer_id);
        } else {
            // Insert new rating
            $insertQuery = "INSERT INTO {$this->table} (restaurant_id, user_id, rating, comment) VALUES (?, ?, ?, ?)";
            $stmt = $this->connection->prepare($insertQuery);
            $stmt->bind_param("iiis", $restaurant_id, $customer_id, $rating, $comment);
        }
        
        if ($stmt->execute()) {
            $stmt->close();
            $this->updateRestaurantAverageRating($restaurant_id);
            return ['success' => true, 'message' => 'Rating saved successfully'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error saving rating'];
        }
    }
    
    /**
     * Get all ratings for a restaurant
     */
    public function getRatings($restaurant_id, $limit = null) {
        $query = "
            SELECT 
                rr.*,
                u.name as customer_name,
                u.profile_image
            FROM {$this->table} rr
            JOIN users u ON rr.user_id = u.id
            WHERE rr.restaurant_id = ?
            ORDER BY rr.created_at DESC
        ";
        
        if ($limit) {
            $query .= " LIMIT " . intval($limit);
        }
        
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $restaurant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $ratings = [];
        
        while ($row = $result->fetch_assoc()) {
            $ratings[] = $row;
        }
        
        $stmt->close();
        return $ratings;
    }
    
    /**
     * Get customer's rating for a restaurant
     */
    public function getCustomerRating($restaurant_id, $customer_id) {
        $query = "SELECT * FROM {$this->table} WHERE restaurant_id = ? AND user_id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ii", $restaurant_id, $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $rating = $result->fetch_assoc();
        $stmt->close();
        
        return $rating;
    }
    
    /**
     * Get average rating for a restaurant
     */
    public function getAverageRating($restaurant_id) {
        $query = "SELECT AVG(rating) as average FROM {$this->table} WHERE restaurant_id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $restaurant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return round($row['average'] ?? 0, 1);
    }
    
    /**
     * Get rating statistics for a restaurant
     */
    public function getRatingStats($restaurant_id) {
        $query = "
            SELECT 
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
            FROM {$this->table}
            WHERE restaurant_id = ?
        ";
        
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("i", $restaurant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();
        $stmt->close();
        
        return $stats;
    }
    
    /**
     * Update restaurant's average rating
     */
    private function updateRestaurantAverageRating($restaurant_id) {
        $avgRating = $this->getAverageRating($restaurant_id);
        $updateQuery = "UPDATE restaurants SET rating = ? WHERE id = ?";
        $stmt = $this->connection->prepare($updateQuery);
        $stmt->bind_param("di", $avgRating, $restaurant_id);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Delete a rating
     */
    public function deleteRating($rating_id, $customer_id) {
        $query = "DELETE FROM {$this->table} WHERE id = ? AND user_id = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->bind_param("ii", $rating_id, $customer_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'Rating deleted'];
        } else {
            $stmt->close();
            return ['success' => false, 'message' => 'Error deleting rating'];
        }
    }
    
    public function close() {
        $this->connection->close();
    }
}
?>
