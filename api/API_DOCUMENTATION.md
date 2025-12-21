# Restaurant Management System - API Documentation

This document provides comprehensive documentation for the RESTful API of the Restaurant Management System (Group 7). The API follows REST conventions and uses JSON for request/response payloads.

## Base URL
```
http://your-domain.com/api
```

## Web Service Technology
This API implements **REST** (Representational State Transfer) architecture. All endpoints use standard HTTP methods:
- `GET` - Retrieve resources
- `POST` - Create resources
- `PUT` - Update resources
- `DELETE` - Delete resources

## Authentication
API requests require authentication via API Key in the `Authorization` header:
```
Authorization: Bearer YOUR_API_KEY
```

## Service Provider Endpoints

### 1. Restaurants Management

#### Get All Restaurants
```
GET /service_provider.php/restaurants
```
**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "La Bella Vista",
      "description": "Authentic Italian cuisine with a modern twist",
      "cuisine": "Italian",
      "address": "123 Harbor View, Downtown",
      "phone": "+1 234 567 8900",
      "price_range": "$$$",
      "rating": 4.8,
      "image": "/elegant-italian-restaurant.png",
      "seating_capacity": 80,
      "created_at": "2025-01-01 12:00:00",
      "updated_at": "2025-01-01 12:00:00"
    }
  ]
}
```

#### Get Specific Restaurant
```
GET /service_provider.php/restaurants/{id}
```
**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "La Bella Vista",
    "description": "Authentic Italian cuisine with a modern twist",
    "cuisine": "Italian",
    "address": "123 Harbor View, Downtown",
    "phone": "+1 234 567 8900",
    "price_range": "$$$",
    "rating": 4.8,
    "image": "/elegant-italian-restaurant.png",
    "seating_capacity": 80,
    "created_at": "2025-01-01 12:00:00",
    "updated_at": "2025-01-01 12:00:00",
    "menu_items": [...]
  }
}
```

#### Create Restaurant
```
POST /service_provider.php/restaurants
```
**Request Body:**
```json
{
  "name": "Restaurant Name",
  "description": "Restaurant Description",
  "cuisine": "Cuisine Type",
  "address": "Full Address",
  "phone": "Phone Number",
  "price_range": "$$$",
  "image": "/path/to/image.jpg",
  "seating_capacity": 100
}
```

#### Update Restaurant
```
PUT /service_provider.php/restaurants/{id}
```
**Request Body:**
```json
{
  "name": "Updated Restaurant Name",
  "description": "Updated Description",
  "cuisine": "Updated Cuisine",
  "address": "Updated Address",
  "phone": "Updated Phone",
  "price_range": "$$$$",
  "image": "/path/to/new-image.jpg",
  "seating_capacity": 120
}
```

#### Delete Restaurant
```
DELETE /service_provider.php/restaurants/{id}
```

#### Search Restaurants
```
GET /service_provider.php/restaurants?search=keyword
```

#### Filter by Cuisine
```
GET /service_provider.php/restaurants?cuisine=Italian
```

### 2. Menu Management

#### Get Menu Items by Restaurant
```
GET /service_provider.php/menu?restaurant_id=1
```

#### Get Menu Items by Category
```
GET /service_provider.php/menu?restaurant_id=1&category=main
```

#### Get Specific Menu Item
```
GET /service_provider.php/menu/{id}
```

#### Create Menu Item
```
POST /service_provider.php/menu
```
**Request Body:**
```json
{
  "restaurant_id": 1,
  "name": "Dish Name",
  "description": "Dish Description",
  "price": 19.99,
  "category": "main",
  "image": "/path/to/dish-image.jpg",
  "available": 1
}
```

#### Update Menu Item
```
PUT /service_provider.php/menu/{id}
```
**Request Body:**
```json
{
  "name": "Updated Dish Name",
  "description": "Updated Description",
  "price": 24.99,
  "category": "appetizer",
  "image": "/path/to/updated-image.jpg",
  "available": 0
}
```

#### Delete Menu Item
```
DELETE /service_provider.php/menu/{id}
```

### 3. Reservations Management

#### Get All Reservations
```
GET /service_provider.php/reservations
```

#### Get Reservations by Restaurant
```
GET /service_provider.php/reservations?restaurant_id=1
```

#### Get Reservations by Status
```
GET /service_provider.php/reservations?status=confirmed
```

#### Get Specific Reservation
```
GET /service_provider.php/reservations/{id}
```

#### Create Reservation
```
POST /service_provider.php/reservations
```
**Request Body:**
```json
{
  "restaurant_id": 1,
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "customer_phone": "+1234567890",
  "date": "2025-12-25",
  "time": "19:00:00",
  "guests": 4,
  "special_requests": "Window seat preferred"
}
```

#### Update Reservation Status
```
PUT /service_provider.php/reservations/{id}
```
**Request Body:**
```json
{
  "status": "confirmed"
}
```

#### Update Reservation Details
```
PUT /service_provider.php/reservations/{id}
```
**Request Body:**
```json
{
  "date": "2025-12-26",
  "time": "20:00:00",
  "guests": 6,
  "special_requests": "Birthday celebration"
}
```

#### Delete Reservation
```
DELETE /service_provider.php/reservations/{id}
```

### 4. Availability Checking

#### Check Restaurant Availability
```
GET /service_provider.php/availability/{restaurant_id}?date=2025-12-25
```
**Response:**
```json
{
  "success": true,
  "data": {
    "restaurant_id": 1,
    "date": "2025-12-25",
    "total_seats": 80,
    "reserved_seats": 30,
    "available_seats": 50,
    "availability_percentage": 62.5
  }
}
```

## Service Consumer Endpoints

### 1. Tour Services (Consuming from Groups 1, 5, 9)

#### Get Tours
```
GET /service_consumer.php/tours
```
**Parameters:**
- `search` (optional): Search keyword
- `location` (optional): Location filter

#### Book Tour
```
POST /service_consumer.php/bookings/tour
```
**Request Body:**
```json
{
  "tour_id": 1,
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "date": "2025-12-25",
  "participants": 4
}
```

### 2. Hotel Services (Consuming from Groups 2, 6)

#### Get Hotels
```
GET /service_consumer.php/hotels
```
**Parameters:**
- `location` (optional): Location filter
- `checkin` (optional): Check-in date
- `checkout` (optional): Check-out date

#### Book Hotel
```
POST /service_consumer.php/bookings/hotel
```
**Request Body:**
```json
{
  "hotel_id": 1,
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "checkin": "2025-12-25",
  "checkout": "2025-12-30",
  "rooms": 2
}
```

### 3. Taxi Services (Consuming from Groups 4, 8)

#### Get Taxi Services
```
GET /service_consumer.php/taxis
```

#### Book Taxi
```
POST /service_consumer.php/bookings/taxi
```
**Request Body:**
```json
{
  "taxi_id": 1,
  "customer_name": "John Doe",
  "customer_email": "john@example.com",
  "pickup_location": "Hotel Address",
  "destination": "Restaurant Address",
  "pickup_time": "2025-12-25 18:00:00"
}
```

## Error Responses

All error responses follow this format:
```json
{
  "success": false,
  "message": "Error description"
}
```

Common HTTP status codes:
- `200 OK` - Successful request
- `400 Bad Request` - Invalid request data
- `401 Unauthorized` - Missing or invalid API key
- `404 Not Found` - Resource not found
- `500 Internal Server Error` - Server error

## Rate Limiting

API requests are limited to 1000 requests per hour per API key.

## Service Contracts

### Data Models

#### Restaurant
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| id | integer | No | Unique identifier |
| name | string | Yes | Restaurant name |
| description | string | Yes | Restaurant description |
| cuisine | string | Yes | Cuisine type |
| address | string | Yes | Full address |
| phone | string | Yes | Contact phone |
| price_range | enum | Yes | Price range ($, $$, $$$, $$$$) |
| rating | decimal | No | Rating (0.0-5.0) |
| image | string | No | Image URL |
| seating_capacity | integer | Yes | Maximum seating capacity |

#### Menu Item
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| id | integer | No | Unique identifier |
| restaurant_id | integer | Yes | Associated restaurant |
| name | string | Yes | Item name |
| description | string | Yes | Item description |
| price | decimal | Yes | Price |
| category | enum | Yes | Category (appetizer, main, dessert, beverage) |
| image | string | No | Image URL |
| available | boolean | Yes | Availability status |

#### Reservation
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| id | integer | No | Unique identifier |
| restaurant_id | integer | Yes | Associated restaurant |
| customer_name | string | Yes | Customer name |
| customer_email | string | Yes | Customer email |
| customer_phone | string | Yes | Customer phone |
| date | date | Yes | Reservation date |
| time | time | Yes | Reservation time |
| guests | integer | Yes | Number of guests |
| status | enum | No | Status (pending, confirmed, cancelled, completed) |
| special_requests | string | No | Special requests |

## Version Information
- **API Version**: v1.0
- **Last Updated**: December 21, 2025
- **Contact**: Group 7 - Restaurant Management Team