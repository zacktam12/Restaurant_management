# Integration Testing Guide

This document provides comprehensive procedures for testing the integration between Group 7 (Restaurant Management System) and other service provider groups in the Tourism Management System.

## Table of Contents
1. [Overview](#overview)
2. [Prerequisites](#prerequisites)
3. [Testing Environments](#testing-environments)
4. [Service Provider Testing (Group 7)](#service-provider-testing-group-7)
5. [Service Consumer Testing](#service-consumer-testing)
6. [API Authentication Testing](#api-authentication-testing)
7. [Error Handling Testing](#error-handling-testing)
8. [Performance Testing](#performance-testing)
9. [Security Testing](#security-testing)
10. [Test Cases](#test-cases)
11. [Troubleshooting](#troubleshooting)

## Overview

This guide covers the testing procedures for verifying that the Restaurant Management System (Group 7) correctly integrates with other service groups:
- **Tour Groups**: Groups 1, 5, 9
- **Hotel Groups**: Groups 2, 6
- **Taxi Groups**: Groups 4, 8

## Prerequisites

Before conducting integration tests, ensure the following:

### For Service Providers (Group 7)
- REST API endpoints are accessible at `/api/service_provider.php`
- Database is properly configured and populated
- API keys are generated for authorized consumers
- SSL certificates are configured (recommended)

### For Service Consumers
- Access to Group 7's API endpoints
- Valid API key with appropriate permissions
- Network connectivity to Group 7's server

### Test Data Requirements
- Sample restaurants with menu items
- Test user accounts with different roles
- Sample reservations and bookings
- Valid API keys for testing

## Testing Environments

### Development Environment
- Local development servers
- Test database with sample data
- Debug mode enabled

### Staging Environment
- Mirror of production setup
- Realistic data sets
- Performance monitoring enabled

### Production Environment
- Live system with actual data
- Monitoring and alerting configured

## Service Provider Testing (Group 7)

### REST API Endpoint Testing

#### 1. Restaurants Endpoints
**GET /restaurants**
- Verify all restaurants are returned
- Check pagination if implemented
- Validate response format

**GET /restaurants/{id}**
- Verify specific restaurant details
- Confirm menu items are included
- Check error handling for invalid IDs

**POST /restaurants**
- Test with valid restaurant data
- Verify authentication requirement
- Check duplicate prevention
- Validate response format

**PUT /restaurants/{id}**
- Test partial updates
- Verify authentication requirement
- Check concurrency handling

**DELETE /restaurants/{id}**
- Verify soft delete if applicable
- Test cascade deletion of related data
- Check error handling

#### 2. Menu Endpoints
**GET /menu**
- Test filtering by restaurant
- Test filtering by category
- Validate item availability status

**POST /menu**
- Test with all required fields
- Verify pricing validation
- Check image upload if applicable

#### 3. Reservations Endpoints
**GET /reservations**
- Test filtering by restaurant
- Test filtering by status
- Validate date range queries

**POST /reservations**
- Test availability checking
- Verify guest count validation
- Check double booking prevention

#### 4. Availability Endpoints
**GET /availability/{restaurant_id}**
- Test with various dates
- Verify capacity calculations
- Check time slot validation

### Authentication Testing

#### API Key Validation
- Test with valid API keys
- Test with expired/inactive keys
- Test with malformed keys
- Verify permission-based access control

#### Rate Limiting
- Test request limits per API key
- Verify rate limiting response codes
- Check reset timing

## Service Consumer Testing

### Tour Services Integration (Groups 1, 5, 9)
**GET /tours**
- Verify tour listings are displayed
- Test search functionality
- Check sorting options

**POST /bookings/tour**
- Test successful booking creation
- Verify confirmation emails
- Check error handling for full tours

### Hotel Services Integration (Groups 2, 6)
**GET /hotels**
- Verify hotel listings with details
- Test location-based filtering
- Check availability indicators

**POST /bookings/hotel**
- Test room booking process
- Verify date conflict detection
- Check cancellation policies

### Taxi Services Integration (Groups 4, 8)
**GET /taxis**
- Verify taxi service listings
- Test vehicle type filtering
- Check real-time availability

**POST /bookings/taxi**
- Test pickup/dropoff validation
- Verify scheduling conflicts
- Check driver assignment

## API Authentication Testing

### Bearer Token Authentication
```
Authorization: Bearer YOUR_API_KEY_HERE
```

### Query Parameter Authentication
```
GET /api/service_provider.php/restaurants?api_key=YOUR_API_KEY_HERE
```

### Header-based Authentication
```
X-API-Key: YOUR_API_KEY_HERE
```

### Permission Testing
- Read-only keys should only allow GET requests
- Write-only keys should only allow POST/PUT/DELETE
- Read-write keys should allow all operations

## Error Handling Testing

### HTTP Status Codes
- **200 OK**: Successful requests
- **400 Bad Request**: Invalid input data
- **401 Unauthorized**: Missing or invalid authentication
- **403 Forbidden**: Insufficient permissions
- **404 Not Found**: Invalid endpoints or resources
- **429 Too Many Requests**: Rate limiting exceeded
- **500 Internal Server Error**: Server-side errors

### Error Response Format
```json
{
  "success": false,
  "message": "Error description",
  "error_code": "ERROR_CODE"
}
```

### Common Error Scenarios
- Invalid restaurant IDs
- Overbooking attempts
- Expired API keys
- Network timeouts
- Database connection failures

## Performance Testing

### Response Time Benchmarks
- API responses should be under 500ms for simple queries
- Complex queries should be under 2 seconds
- File uploads should be under 10 seconds

### Concurrent User Testing
- Test with 100 concurrent users
- Verify system stability under load
- Check database connection pooling

### Stress Testing
- Simulate peak usage periods
- Test with 1000+ concurrent requests
- Monitor resource utilization

## Security Testing

### Input Validation
- Test SQL injection attempts
- Verify XSS protection
- Check file upload restrictions

### API Key Security
- Test key rotation procedures
- Verify key storage encryption
- Check key expiration policies

### Data Privacy
- Verify PII protection
- Test data encryption at rest
- Check audit logging

## Test Cases

### Functional Test Cases

#### TC-001: Restaurant Creation
**Description**: Verify that restaurants can be created via API
**Preconditions**: Valid API key with write permissions
**Steps**:
1. Send POST request to `/restaurants` with valid data
2. Verify 200 response
3. Confirm restaurant appears in GET requests
**Expected Result**: Restaurant created successfully

#### TC-002: Menu Item Availability
**Description**: Verify menu item availability toggling
**Preconditions**: Existing restaurant with menu items
**Steps**:
1. Toggle menu item availability via PUT request
2. Verify change in subsequent GET requests
**Expected Result**: Availability status updated correctly

#### TC-003: Reservation Booking
**Description**: Verify reservation creation and validation
**Preconditions**: Available restaurant with capacity
**Steps**:
1. Send POST request to `/reservations` with valid data
2. Attempt to overbook the same time slot
3. Verify successful booking and rejection of overbooking
**Expected Result**: Valid booking accepted, overbooking rejected

#### TC-004: Cross-Service Booking
**Description**: Verify integration with external services
**Preconditions**: Valid API connections to tour/hotel/taxi services
**Steps**:
1. Book restaurant reservation
2. Book connecting tour service
3. Book hotel for stay
4. Arrange taxi transfer
**Expected Result**: All services booked successfully with proper linking

### Negative Test Cases

#### TC-005: Invalid API Key
**Description**: Verify rejection of invalid API keys
**Preconditions**: None
**Steps**:
1. Send request with invalid API key
2. Verify 401 response
**Expected Result**: Request rejected with appropriate error

#### TC-006: Insufficient Permissions
**Description**: Verify permission-based access control
**Preconditions**: API key with read-only permissions
**Steps**:
1. Attempt POST/PUT/DELETE with read-only key
2. Verify 403 response
**Expected Result**: Request rejected due to insufficient permissions

#### TC-007: Rate Limiting
**Description**: Verify rate limiting enforcement
**Preconditions**: Valid API key
**Steps**:
1. Send 1001 requests within one hour
2. Verify 429 response on 1001st request
**Expected Result**: Rate limiting enforced correctly

## Troubleshooting

### Common Issues and Solutions

#### API Connection Failures
**Symptoms**: Timeout errors, connection refused
**Solutions**:
- Verify network connectivity
- Check firewall settings
- Confirm server is running
- Validate endpoint URLs

#### Authentication Errors
**Symptoms**: 401, 403 responses
**Solutions**:
- Verify API key validity
- Check key permissions
- Confirm authentication header format

#### Data Synchronization Issues
**Symptoms**: Inconsistent data between services
**Solutions**:
- Check timestamp synchronization
- Verify data validation rules
- Review error logs for sync failures

#### Performance Degradation
**Symptoms**: Slow response times
**Solutions**:
- Optimize database queries
- Implement caching
- Scale infrastructure
- Review network latency

### Logging and Monitoring

#### API Request Logging
- Log all API requests with timestamps
- Record request/response details
- Track user agents and IP addresses

#### Error Tracking
- Log all error responses
- Include stack traces for 500 errors
- Monitor error frequency and patterns

#### Performance Metrics
- Track response times
- Monitor throughput
- Measure error rates

## Conclusion

This integration testing guide provides a comprehensive framework for verifying the interoperability of the Restaurant Management System with other service groups. Regular testing using these procedures will ensure stable, secure, and performant service integrations.

For additional support, contact the Group 7 development team or refer to the API documentation at `/api/API_DOCUMENTATION.md`.