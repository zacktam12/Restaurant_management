-- Complete Role Credentials for Restaurant Management System
-- This script creates users for all 5 roles with proper hashed passwords

-- Delete existing role users to avoid conflicts
DELETE FROM users WHERE email IN ('admin@restaurant.com', 'manager@restaurant.com', 'customer@restaurant.com', 'tourist@restaurant.com', 'guide@restaurant.com');

-- Admin User
-- Email: admin@restaurant.com
-- Password: admin123
-- Role: admin
INSERT INTO users (email, password, name, role, phone, professional_details) VALUES ( 
    'admin@restaurant.com',
    '$2y$10$FNA7YWU204BvCdBBgLgwm.RdP75i2IMIvyQknOxQUkWg0653tWzjq',
    'System Administrator',
    'admin',
    '+1-555-0100',
    'System administrator with full access rights'
);

-- Manager User
-- Email: manager@restaurant.com
-- Password: manager123
-- Role: manager
INSERT INTO users (email, password, name, role, phone, professional_details) VALUES ( 
    'manager@restaurant.com',
    '$2y$10$wKYe1Cc5XDFol0gIEYgyR.9apAHAbK./oHizGLedaOz6oO0BVYOxa',
    'Restaurant Manager',
    'manager',
    '+1-555-0101',
    'Restaurant manager with business management access'
);

-- Customer User
-- Email: customer@restaurant.com
-- Password: customer123
-- Role: customer
INSERT INTO users (email, password, name, role, phone, professional_details) VALUES ( 
    'customer@restaurant.com',
    '$2y$10$iR/YSVyk0CZuGd7wSYzdau13K64ZhJ617Rw9B78q.96.jNIX7BRHi',
    'Regular Customer',
    'customer',
    '+1-555-0102',
    NULL
);

-- Tourist User
-- Email: tourist@restaurant.com
-- Password: tourist123
-- Role: tourist
INSERT INTO users (email, password, name, role, phone, professional_details) VALUES ( 
    'tourist@restaurant.com',
    '$2y$10$tp9clwHwdP94gQDpgMlCKeIAkwTX8w5A84T1C2FsnGC0eEhbCARvm',
    'Visiting Tourist',
    'tourist',
    '+1-555-0103',
    NULL
);

-- Tour Guide User
-- Email: guide@restaurant.com
-- Password: guide123
-- Role: tour_guide
INSERT INTO users (email, password, name, role, phone, professional_details) VALUES ( 
    'guide@restaurant.com',
    '$2y$10$Qq59e7aiLEZCY5FQmc4IO.k0MjgI8UDDUvzI62e1A9IBr8Jhgq0Km',
    'Professional Tour Guide',
    'tour_guide',
    '+1-555-0104',
    'Certified tour guide with extensive local knowledge'
);

-- Verification query to check all users
SELECT id, email, name, role, created_at FROM users WHERE email IN ('admin@restaurant.com', 'manager@restaurant.com', 'customer@restaurant.com', 'tourist@restaurant.com', 'guide@restaurant.com');