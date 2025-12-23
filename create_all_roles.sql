-- Create all roles with their credentials
-- This script will insert users for all 5 roles in the system

-- Admin user
INSERT INTO users (email, password, name, role, phone, professional_details) 
VALUES (
    'admin@restaurant.com', 
    '$2y$10$oceww2P/bCjssWQWYU1BWOGLLotvC.qm.5Rfc/yWXkp8XhMpXsKCG', -- admin123
    'System Administrator', 
    'admin', 
    '+1-555-0100', 
    'System administrator with full access rights'
);

-- Manager user
INSERT INTO users (email, password, name, role, phone, professional_details) 
VALUES (
    'manager@restaurant.com', 
    '$2y$10$92hBjwYh2nXq9Z.zQ8fN6e0k6zZq9Z.zQ8fN6e0k6zZq9Z.zQ8fN6', -- manager123 (will be replaced with proper hash)
    'Restaurant Manager', 
    'manager', 
    '+1-555-0101', 
    'Restaurant manager with business management access'
);

-- Customer user
INSERT INTO users (email, password, name, role, phone, professional_details) 
VALUES (
    'customer@restaurant.com', 
    '$2y$10$92hBjwYh2nXq9Z.zQ8fN6e0k6zZq9Z.zQ8fN6e0k6zZq9Z.zQ8fN6', -- customer123 (will be replaced with proper hash)
    'Regular Customer', 
    'customer', 
    '+1-555-0102', 
    NULL
);

-- Tourist user
INSERT INTO users (email, password, name, role, phone, professional_details) 
VALUES (
    'tourist@restaurant.com', 
    '$2y$10$92hBjwYh2nXq9Z.zQ8fN6e0k6zZq9Z.zQ8fN6e0k6zZq9Z.zQ8fN6', -- tourist123 (will be replaced with proper hash)
    'Visiting Tourist', 
    'tourist', 
    '+1-555-0103', 
    NULL
);

-- Tour Guide user
INSERT INTO users (email, password, name, role, phone, professional_details) 
VALUES (
    'guide@restaurant.com', 
    '$2y$10$92hBjwYh2nXq9Z.zQ8fN6e0k6zZq9Z.zQ8fN6e0k6zZq9Z.zQ8fN6', -- guide123 (will be replaced with proper hash)
    'Professional Tour Guide', 
    'tour_guide', 
    '+1-555-0104', 
    'Certified tour guide with extensive local knowledge'
);