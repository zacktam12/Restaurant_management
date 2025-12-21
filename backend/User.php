<?php
/**
 * User Class
 * Handles user authentication, registration, and management
 */

require_once 'Database.php';

class User {
    private $db;
    private $table = 'users';

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Register a new user
     */
    public function register($email, $password, $name, $role = 'customer', $phone = null, $professionalDetails = null) {
        // Check if user already exists
        $existingUser = $this->getUserByEmail($email);
        if ($existingUser) {
            return ['success' => false, 'message' => 'User with this email already exists'];
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_HASH_ALGO);

        // Insert user
        $query = "INSERT INTO {$this->table} (email, password, name, role, phone, professional_details) VALUES (?, ?, ?, ?, ?, ?)";
        $params = [$email, $hashedPassword, $name, $role, $phone, $professionalDetails];
        $paramTypes = "ssssss";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'User registered successfully',
                    'user_id' => $result['insert_id']
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to register user'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Authenticate user login
     */
    public function login($email, $password) {
        $user = $this->getUserByEmail($email);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Remove password from user data before returning
            unset($user['password']);
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => $user
            ];
        } else {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $query = "SELECT * FROM {$this->table} WHERE email = ?";
        $params = [$email];
        $paramTypes = "s";

        try {
            $result = $this->db->select($query, $params, $paramTypes);
            return !empty($result) ? $result[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $params = [$id];
        $paramTypes = "i";

        try {
            $result = $this->db->select($query, $params, $paramTypes);
            $user = !empty($result) ? $result[0] : null;
            
            // Remove password from user data
            if ($user) {
                unset($user['password']);
            }
            
            return $user;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Update user profile
     */
    public function updateUser($id, $name, $email, $phone, $professionalDetails) {
        $query = "UPDATE {$this->table} SET name = ?, email = ?, phone = ?, professional_details = ?, updated_at = NOW() WHERE id = ?";
        $params = [$name, $email, $phone, $professionalDetails, $id];
        $paramTypes = "ssssi";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'User profile updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update user profile'
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
     * Get all users
     */
    public function getAllUsers() {
        $query = "SELECT id, email, name, role, phone, professional_details, created_at FROM {$this->table} ORDER BY created_at DESC";

        try {
            return $this->db->select($query);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $params = [$id];
        $paramTypes = "i";

        try {
            $result = $this->db->execute($query, $params, $paramTypes);
            if ($result['success'] && $result['affected_rows'] > 0) {
                return [
                    'success' => true,
                    'message' => 'User deleted successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete user'
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
     * Close database connection
     */
    public function close() {
        $this->db->close();
    }
}
?>