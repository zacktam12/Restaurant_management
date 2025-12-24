<?php
/**
 * Service Class
 * Handles external services management (tours, hotels, taxis)
 */

require_once __DIR__ . '/config.php';

class Service {
    private $conn;
    private $client;
    private $table = 'external_services'; // Needed for legacy methods
    
    public function __construct() {
        // Local DB for legacy support (admin)
        $database = new Database();
        $this->conn = $database->getConnection();
        
        // External Client for live fetching
        require_once __DIR__ . '/ExternalServiceClient.php';
        $this->client = new ExternalServiceClient();
    }
    
    /**
     * Get all services from external providers
     */
    public function getAllServices() {
        $services = [];
        
        // Parallel fetching could be better, but sequential for simplicity
        
        // 1. Tours
        $tours = $this->client->getTours();
        if ($tours['success'] ?? false) {
            foreach ($tours['data'] ?? [] as $tour) {
                $services[] = $this->normalizeService($tour, 'tour');
            }
        }
        
        // 2. Hotels
        $hotels = $this->client->getHotels();
        if ($hotels['success'] ?? false) {
            foreach ($hotels['data'] ?? [] as $hotel) {
                $services[] = $this->normalizeService($hotel, 'hotel');
            }
        }
        
        // 3. Taxis
        // Note: For taxis, we usually search by location, but for "browse all" we might get a general list or defaults
        $taxis = $this->client->getTaxiAvailability('Addis Ababa'); // Default location
        if ($taxis['success'] ?? false) {
            foreach ($taxis['data'] ?? [] as $taxi) {
                $services[] = $this->normalizeService($taxi, 'taxi');
            }
        }
        
        // 4. Tickets (Removed)
        // Check removed
        
        return $services;
    }
    
    /**
     * Get services by type
     */
    public function getServicesByType($type) {
        $services = [];
        
        switch ($type) {
            case 'tour':
                $response = $this->client->getTours();
                break;
            case 'hotel':
                $response = $this->client->getHotels();
                break;
            case 'taxi':
                $response = $this->client->getTaxiAvailability('Addis Ababa');
                break;
            // case 'ticket' removed
            default:
                return [];
        }
        
        if ($response['success'] ?? false) {
            foreach ($response['data'] ?? [] as $item) {
                $services[] = $this->normalizeService($item, $type);
            }
        }
        
        return $services;
    }
    
    /**
     * Helper to normalize diverse external data into unified service structure
     */
    private function normalizeService($item, $type) {
        // Basic mapping, can be expanded based on specific external API fields
        return [
            'id' => $item['id'] ?? 0,
            'type' => $type,
            'name' => $item['name'] ?? $item['vehicle_type'] ?? $item['route'] ?? 'Unknown Service',
            'description' => $item['description'] ?? $item['city'] ?? "Service from $type provider",
            'price' => $item['price'] ?? $item['price_per_km'] ?? $item['price_per_night'] ?? 0,
            'rating' => $item['rating'] ?? 5.0,
            'available' => 1,
            'image' => $item['image'] ?? null // Use placeholder if null
        ];
    }

    /**
     * Get service by ID (and Type)
     * Note: ID collisions possible between services, so type is typically required.
     * Use a composite ID or type parameter in calls.
     */
    public function getServiceById($id) {
        // Ideally we need type here. For now, check local cache or iterate all (slow)
        // Or updated to accept type: getServiceById($id, $type)
        // Returning null as this signature is deprecated in distributed context without type
        // Use getServiceDetails($id, $type) instead.
        return null; 
    }
    
    public function getServiceDetails($id, $type) {
        switch ($type) {
            case 'tour': return $this->client->getTourDetails($id)['data'] ?? null;
            case 'hotel': return $this->client->getHotelDetails($id)['data'] ?? null;
            case 'taxi': return null; // Taxis usually don't have "details" page
            case 'ticket': return $this->client->getTicketDetails($id)['data'] ?? null;
        }
        return null;
    }
    
    /**
     * Add service
     */
    public function addService($type, $name, $description, $price, $image = null, $rating = 0.0) {
        $sql = "INSERT INTO {$this->table} (type, name, description, price, image, rating) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssdsd", $type, $name, $description, $price, $image, $rating);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Service added successfully',
                'service_id' => $this->conn->insert_id
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to add service'];
    }
    
    /**
     * Update service
     */
    public function updateService($id, $name, $description, $price, $available, $image = null) {
        if ($image) {
            $sql = "UPDATE {$this->table} SET name = ?, description = ?, price = ?, available = ?, image = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssdisi", $name, $description, $price, $available, $image, $id);
        } else {
            $sql = "UPDATE {$this->table} SET name = ?, description = ?, price = ?, available = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssdii", $name, $description, $price, $available, $id);
        }
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Service updated successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to update service'];
    }
    
    /**
     * Delete service
     */
    public function deleteService($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Service deleted successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to delete service'];
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
