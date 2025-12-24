# Restaurant Management System

A comprehensive PHP-based restaurant management system with role-based access control.

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3 (OKLCH color system), Vanilla JavaScript
- **Server**: Apache (XAMPP recommended)

## Features

### Admin Features
- Full system access
- User management (Create, Read, Update, Delete)
- Restaurant management
- Reservation oversight
- Analytics and reporting

### Manager Features
- Manage assigned restaurants
- View and manage restaurant reservations
- Update restaurant menus
- Analytics for managed restaurants

### Customer Features
- Browse restaurants
- Make reservations
- View booking history
- Access external services (tours, hotels, taxis)
- Rate and review restaurants

## Installation

### Prerequisites
- XAMPP (or similar LAMP/WAMP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Instructions

1. **Clone or extract the project** to your XAMPP htdocs directory:
   ```
   c:\xampp\htdocs\rest\
   ```

2. **Import the database**:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database or let the script create it
   - Import the following files in order:
     1. `database/restaurant_system.sql` (main schema)
     2. `database/enterprise-enhancements.sql` (additional features)
     3. `database/add-ratings-schema.sql` (ratings system)
     4. `database/add-customer-id-migration.sql` (if upgrading existing database)

3. **Configure database connection**:
   - Edit `backend/config.php`
   - Update database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'restaurant_management');
     ```

4. **Start XAMPP**:
   - Start Apache and MySQL services

5. **Access the application**:
   - Open your browser and navigate to: `http://localhost/rest/`

## Default Login Credentials

### Admin Account
- **Email**: admin@restaurant.com
- **Password**: admin123

### Manager Account
- **Email**: manager@restaurant.com
- **Password**: manager123

### Customer Account
- **Email**: customer@restaurant.com
- **Password**: customer123

**Important**: Change these passwords in production!

## Project Structure

```
rest/
├── admin/              # Admin dashboard pages
├── manager/            # Manager dashboard pages
├── customer/           # Customer dashboard pages
├── api/                # REST API endpoints
├── backend/            # PHP classes and business logic
├── database/           # SQL schema and migration files
├── css/                # Stylesheets
├── index.php           # Main entry point
├── login.php           # Login page
├── register.php        # Registration page
├── landing.php         # Landing page
└── logout.php          # Logout handler
```

## Security Features

- Password hashing with bcrypt
- CSRF token protection
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization)
- Session management
- Rate limiting for login attempts
- Account lockout after failed attempts

## Database Schema

### Main Tables
- **users**: User accounts with role-based permissions
- **restaurants**: Restaurant listings and details
- **reservations**: Customer reservations
- **menu_items**: Restaurant menu items
- **external_services**: Tours, hotels, and taxi services
- **bookings**: Service bookings
- **ratings**: Customer ratings and reviews

### Supporting Tables
- **audit_logs**: System activity tracking
- **notifications**: User notifications
- **change_history**: Change tracking for restaurants
- **login_attempts**: Security monitoring

## Recent Bug Fixes (2025-12-24)

1. ✅ Fixed `customer_id` column missing from reservations table
2. ✅ Fixed password validation error message mismatch
3. ✅ Fixed session double-start warnings
4. ✅ Added missing CSS variables (`--dark-color`, `--light-color`)
5. ✅ Added missing `flex-1` utility class
6. ✅ Updated Reservation class to support customer_id linking

## API Endpoints

All API endpoints return JSON responses.

- `POST /api/auth.php?action=login` - User login
- `POST /api/auth.php?action=register` - User registration
- `GET /api/auth.php?action=logout` - User logout
- `GET /api/restaurants.php` - Get all restaurants
- `GET /api/reservations.php` - Get reservations
- `POST /api/bookings.php` - Create booking
- `POST /api/ratings.php` - Add/update rating

## Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge

## License

This project is for educational purposes.

## Support

For issues or questions, please check the code comments or contact the development team.

---

**Version**: 1.1.0  
**Last Updated**: December 24, 2025
