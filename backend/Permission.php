<?php
/**
 * Permission Class
 * Handles role-based access control (RBAC) for the system
 */

class Permission {
    // Valid roles in the system
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_CUSTOMER = 'customer';
    
    // Permission categories
    const PERM_USER_MANAGEMENT = 'user_management';
    const PERM_USER_MANAGEMENT_FULL = 'user_management_full'; // Can manage all users including admin/manager
    const PERM_USER_MANAGEMENT_LIMITED = 'user_management_limited'; // Can only manage customers
    
    const PERM_BUSINESS_OPERATIONS = 'business_operations';
    const PERM_RESTAURANTS = 'restaurants';
    const PERM_RESERVATIONS = 'reservations';
    const PERM_BOOKINGS = 'bookings';
    const PERM_EXTERNAL_SERVICES = 'external_services';
    const PERM_PLACES = 'places';
    
    const PERM_REPORTS_BUSINESS = 'reports_business';
    const PERM_REPORTS_SYSTEM = 'reports_system';
    
    const PERM_API_KEYS = 'api_keys';
    const PERM_SERVICE_REGISTRY = 'service_registry';
    const PERM_SYSTEM_CONFIG = 'system_config';
    
    /**
     * Check if a role has a specific permission
     */
    public static function hasPermission($role, $permission) {
        $permissions = self::getRolePermissions($role);
        return in_array($permission, $permissions);
    }
    
    /**
     * Get all permissions for a specific role
     */
    public static function getRolePermissions($role) {
        switch ($role) {
            case self::ROLE_ADMIN:
                return [
                    self::PERM_USER_MANAGEMENT,
                    self::PERM_USER_MANAGEMENT_FULL,
                    self::PERM_BUSINESS_OPERATIONS,
                    self::PERM_RESTAURANTS,
                    self::PERM_RESERVATIONS,
                    self::PERM_BOOKINGS,
                    self::PERM_EXTERNAL_SERVICES,
                    self::PERM_PLACES,
                    self::PERM_REPORTS_BUSINESS,
                    self::PERM_REPORTS_SYSTEM,
                    self::PERM_API_KEYS,
                    self::PERM_SERVICE_REGISTRY,
                    self::PERM_SYSTEM_CONFIG
                ];
                
            case self::ROLE_MANAGER:
                return [
                    self::PERM_USER_MANAGEMENT,
                    self::PERM_USER_MANAGEMENT_LIMITED,
                    self::PERM_BUSINESS_OPERATIONS,
                    self::PERM_RESTAURANTS,
                    self::PERM_RESERVATIONS,
                    self::PERM_BOOKINGS,
                    self::PERM_EXTERNAL_SERVICES,
                    self::PERM_PLACES,
                    self::PERM_REPORTS_BUSINESS
                ];
                
            case self::ROLE_CUSTOMER:
                return [];
                
            default:
                return [];
        }
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin($role) {
        return $role === self::ROLE_ADMIN;
    }
    
    /**
     * Check if user is manager
     */
    public static function isManager($role) {
        return $role === self::ROLE_MANAGER;
    }
    
    /**
     * Check if user is customer
     */
    public static function isCustomer($role) {
        return $role === self::ROLE_CUSTOMER;
    }
    
    /**
     * Check if user is admin or manager (has business management access)
     */
    public static function isBusinessUser($role) {
        return $role === self::ROLE_ADMIN || $role === self::ROLE_MANAGER;
    }
    
    /**
     * Check if user can manage other users
     */
    public static function canManageUsers($role) {
        return self::hasPermission($role, self::PERM_USER_MANAGEMENT);
    }
    
    /**
     * Check if user can manage all users (including admin/manager)
     */
    public static function canManageAllUsers($role) {
        return self::hasPermission($role, self::PERM_USER_MANAGEMENT_FULL);
    }
    
    /**
     * Check if user can only manage customers
     */
    public static function canManageCustomersOnly($role) {
        return self::hasPermission($role, self::PERM_USER_MANAGEMENT_LIMITED) && 
               !self::hasPermission($role, self::PERM_USER_MANAGEMENT_FULL);
    }
    
    /**
     * Check if user can access API keys management
     */
    public static function canManageApiKeys($role) {
        return self::hasPermission($role, self::PERM_API_KEYS);
    }
    
    /**
     * Check if user can access service registry
     */
    public static function canAccessServiceRegistry($role) {
        return self::hasPermission($role, self::PERM_SERVICE_REGISTRY);
    }
    
    /**
     * Check if user can access system-level reports
     */
    public static function canAccessSystemReports($role) {
        return self::hasPermission($role, self::PERM_REPORTS_SYSTEM);
    }
    
    /**
     * Check if user can access business reports
     */
    public static function canAccessBusinessReports($role) {
        return self::hasPermission($role, self::PERM_REPORTS_BUSINESS);
    }
    
    /**
     * Validate if a role is valid
     */
    public static function isValidRole($role) {
        return in_array($role, [self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_CUSTOMER]);
    }
    
    /**
     * Get all valid roles
     */
    public static function getAllRoles() {
        return [self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_CUSTOMER];
    }
    
    /**
     * Require specific permission or redirect
     */
    public static function requirePermission($role, $permission, $redirectUrl = '../login.php') {
        if (!self::hasPermission($role, $permission)) {
            header('Location: ' . $redirectUrl);
            exit();
        }
    }
    
    /**
     * Require admin role or redirect
     */
    public static function requireAdmin($role, $redirectUrl = '../login.php') {
        if (!self::isAdmin($role)) {
            header('Location: ' . $redirectUrl);
            exit();
        }
    }
    
    /**
     * Require business user (admin or manager) or redirect
     */
    public static function requireBusinessUser($role, $redirectUrl = '../login.php') {
        if (!self::isBusinessUser($role)) {
            header('Location: ' . $redirectUrl);
            exit();
        }
    }
    
    /**
     * Get dashboard URL for a role
     */
    public static function getDashboardUrl($role) {
        switch ($role) {
            case self::ROLE_ADMIN:
                return 'admin/index.php';
            case self::ROLE_MANAGER:
                return 'manager/index.php';
            case self::ROLE_CUSTOMER:
                return 'customer/index.php';
            default:
                return 'login.php';
        }
    }
    
    /**
     * Filter users based on manager permissions
     * If user is manager, remove admin and manager users from the list
     */
    public static function filterUsersByPermission($role, $users) {
        if (self::canManageCustomersOnly($role)) {
            return array_filter($users, function($user) {
                return $user['role'] === self::ROLE_CUSTOMER;
            });
        }
        return $users;
    }
}
?>
