<?php
/**
 * Service Consumer Interface
 * Functions to consume services from other groups in the tourism system
 */

class ServiceConsumer {
    
    /**
     * Consume tour service from Group 1, 5, or 9
     */
    public static function getTours($search = null, $location = null) {
        // In a real implementation, this would make HTTP requests to other groups' APIs
        // For demonstration, we'll return sample data
        
        $tours = [
            [
                'id' => 1,
                'name' => 'City Historical Tour',
                'description' => '3-hour guided tour of historical landmarks',
                'price' => 45.00,
                'duration' => '3 hours',
                'location' => 'Downtown',
                'rating' => 4.7,
                'available' => true
            ],
            [
                'id' => 2,
                'name' => 'Museum Explorer',
                'description' => 'Visit to major museums with expert guide',
                'price' => 35.00,
                'duration' => '4 hours',
                'location' => 'Cultural District',
                'rating' => 4.5,
                'available' => true
            ]
        ];
        
        return $tours;
    }
    
    /**
     * Book a tour
     */
    public static function bookTour($tourId, $customerName, $customerEmail, $date, $participants) {
        // In a real implementation, this would make HTTP requests to other groups' APIs
        // For demonstration, we'll return a sample response
        
        return [
            'success' => true,
            'booking_id' => rand(1000, 9999),
            'message' => 'Tour booked successfully',
            'tour_details' => [
                'tour_id' => $tourId,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'date' => $date,
                'participants' => $participants
            ]
        ];
    }
    
    /**
     * Consume hotel service from Group 2 or 6
     */
    public static function getHotels($location = null, $checkin = null, $checkout = null) {
        // In a real implementation, this would make HTTP requests to other groups' APIs
        // For demonstration, we'll return sample data
        
        $hotels = [
            [
                'id' => 1,
                'name' => 'Grand Palace Hotel',
                'description' => '5-star luxury accommodation',
                'price_per_night' => 250.00,
                'rating' => 4.9,
                'amenities' => ['WiFi', 'Pool', 'Spa', 'Restaurant'],
                'location' => 'City Center',
                'available' => true
            ],
            [
                'id' => 2,
                'name' => 'Cozy Inn',
                'description' => 'Budget-friendly comfortable stay',
                'price_per_night' => 80.00,
                'rating' => 4.2,
                'amenities' => ['WiFi', 'Parking', 'Breakfast'],
                'location' => 'Suburb',
                'available' => true
            ]
        ];
        
        return $hotels;
    }
    
    /**
     * Book a hotel room
     */
    public static function bookHotel($hotelId, $customerName, $customerEmail, $checkin, $checkout, $rooms) {
        // In a real implementation, this would make HTTP requests to other groups' APIs
        // For demonstration, we'll return a sample response
        
        return [
            'success' => true,
            'booking_id' => rand(1000, 9999),
            'message' => 'Hotel room booked successfully',
            'hotel_details' => [
                'hotel_id' => $hotelId,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'checkin_date' => $checkin,
                'checkout_date' => $checkout,
                'rooms' => $rooms
            ]
        ];
    }
    
    /**
     * Consume taxi service from Group 4 or 8
     */
    public static function getTaxiServices() {
        // In a real implementation, this would make HTTP requests to other groups' APIs
        // For demonstration, we'll return sample data
        
        $taxiServices = [
            [
                'id' => 1,
                'name' => 'Premium Taxi Service',
                'description' => '24/7 reliable transportation',
                'price_per_km' => 2.50,
                'rating' => 4.5,
                'vehicle_types' => ['Sedan', 'SUV', 'Van'],
                'available' => true
            ],
            [
                'id' => 2,
                'name' => 'Economy Rides',
                'description' => 'Affordable transportation option',
                'price_per_km' => 1.50,
                'rating' => 4.0,
                'vehicle_types' => ['Sedan', 'Hatchback'],
                'available' => true
            ]
        ];
        
        return $taxiServices;
    }
    
    /**
     * Book a taxi
     */
    public static function bookTaxi($taxiId, $customerName, $customerEmail, $pickupLocation, $destination, $dateTime) {
        // In a real implementation, this would make HTTP requests to other groups' APIs
        // For demonstration, we'll return a sample response
        
        return [
            'success' => true,
            'booking_id' => rand(1000, 9999),
            'message' => 'Taxi booked successfully',
            'taxi_details' => [
                'taxi_id' => $taxiId,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'pickup_location' => $pickupLocation,
                'destination' => $destination,
                'pickup_time' => $dateTime
            ]
        ];
    }
    
    /**
     * Make HTTP request to external service
     * This is a helper function for real implementations
     */
    private static function makeHttpRequest($url, $method = 'GET', $data = null, $headers = []) {
        // This would be implemented with cURL or similar in a real application
        // For now, we'll just return sample data
        
        return [
            'status' => 200,
            'data' => [
                'message' => 'Sample response from external service',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];
    }
}
?>