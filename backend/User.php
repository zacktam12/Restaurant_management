<?php
/**
 * User Class
 * Handles user authentication and management
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/AuditLog.php';

class User {
    private $conn;
    private $table = 'users';
    private $maxFailedAttempts = 5;
    private $lockoutDuration = 900; // 15 minutes
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Register new user
     */
    public function register($email, $password, $name, $role = 'customer', $phone = null, $professional_details = null) {
        $passwordValidation = Security::validatePassword($password);
        if (!$passwordValidation['valid']) {
            return ['success' => false, 'message' => implode('. ', $passwordValidation['errors'])];
        }
        
        // Check if email exists
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Validate role
        $validRoles = ['admin', 'manager', 'customer'];
        if (!in_array($role, $validRoles)) {
            return ['success' => false, 'message' => 'Invalid role specified'];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_HASH_ALGO);
        
        $sql = "INSERT INTO {$this->table} (email, password, name, role, phone, professional_details) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssss", $email, $hashedPassword, $name, $role, $phone, $professional_details);
        
        if ($stmt->execute()) {
            $auditLog = new AuditLog();
            $auditLog->logAction($this->conn->insert_id, 'REGISTRATION', 'user', $this->conn->insert_id);
            
            return [
                'success' => true,
                'message' => 'Registration successful',
                'user_id' => $this->conn->insert_id
            ];
        }
        
        return ['success' => false, 'message' => 'Registration failed: ' . $stmt->error];
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        if (!Security::checkRateLimit($email, 10, 60)) {
            return ['success' => false, 'message' => 'Too many login attempts. Please try again later.'];
        }
        
        $sql = "SELECT id, email, password, name, role, phone, profile_image, is_deleted, account_locked, locked_until FROM {$this->table} WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if account is deleted
            if ($user['is_deleted']) {
                return ['success' => false, 'message' => 'Account has been deleted'];
            }
            
            // Check if account is locked
            if ($user['account_locked']) {
                if (strtotime($user['locked_until']) > time()) {
                    return ['success' => false, 'message' => 'Account is temporarily locked. Please try again later.'];
                } else {
                    $this->unlockAccount($user['id']);
                }
            }
            
            if (password_verify($password, $user['password'])) {
                // Reset failed attempts on successful login
                $this->resetFailedAttempts($user['id']);
                unset($user['password']);
                
                // Added logging for successful login
                $auditLog = new AuditLog();
                $auditLog->logAction($user['id'], 'LOGIN_SUCCESS', 'user', $user['id']);
                
                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => $user
                ];
            } else {
                $this->incrementFailedAttempts($user['id'], $email);
                
                // Log failed login attempt
                $auditLog = new AuditLog();
                $auditLog->logAction($user['id'], 'LOGIN_FAILED', 'user', $user['id']);
            }
        } else {
            // Log failed attempt even if user doesn't exist
            $auditLog = new AuditLog();
            $auditLog->logAction(0, 'LOGIN_FAILED', 'user', 0);
        }
        
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email) {
        $sql = "SELECT id FROM {$this->table} WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id) {
        $sql = "SELECT id, email, name, role, phone, profile_image, professional_details, created_at FROM {$this->table} WHERE id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get all users
     */
    public function getAllUsers() {
        $sql = "SELECT id, email, name, role, phone, created_at FROM {$this->table} WHERE is_deleted = 0 ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole($role) {
        $sql = "SELECT id, email, name, role, phone, created_at FROM {$this->table} WHERE role = ? AND is_deleted = 0 ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($id, $name, $phone = null, $professional_details = null, $profile_image = null) {
        $query = "UPDATE {$this->table} SET name = ?, phone = ?";
        $params = [$name, $phone];
        // Types string no longer needed for execute([params])

        if ($professional_details !== null) {
            $query .= ", professional_details = ?";
            $params[] = $professional_details;
        }

        if ($profile_image !== null) {
            $query .= ", profile_image = ?";
            $params[] = $profile_image;
        }

        $query .= " WHERE id = ? AND is_deleted = 0";
        $params[] = $id;

        $stmt = $this->conn->prepare($query);
        // PHP 8.1+ allows passing params to execute directly
        if ($stmt->execute($params)) {
            return ['success' => true, 'message' => 'Profile updated successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to update profile'];
    }
    
    /**
     * Update user password
     */
    public function updatePassword($id, $currentPassword, $newPassword) {
        // Get current password hash
        $sql = "SELECT password FROM {$this->table} WHERE id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($currentPassword, $user['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_HASH_ALGO);
                
                $updateSql = "UPDATE {$this->table} SET password = ? WHERE id = ? AND is_deleted = 0";
                $updateStmt = $this->conn->prepare($updateSql);
                $updateStmt->bind_param("si", $hashedPassword, $id);
                
                if ($updateStmt->execute()) {
                    return ['success' => true, 'message' => 'Password updated successfully'];
                }
            } else {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
        }
        
        return ['success' => false, 'message' => 'Failed to update password'];
    }
    
    /**
     * Delete user
     */
    public function deleteUser($id) {
        $sql = "UPDATE {$this->table} SET is_deleted = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'User deleted successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to delete user'];
    }
    
    /**
     * Update user role
     */
    public function updateRole($id, $role) {
        $validRoles = ['admin', 'manager', 'customer'];
        if (!in_array($role, $validRoles)) {
            return ['success' => false, 'message' => 'Invalid role specified'];
        }
        
        $sql = "UPDATE {$this->table} SET role = ? WHERE id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $role, $id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Role updated successfully'];
        }
        
        return ['success' => false, 'message' => 'Failed to update role'];
    }
    
    /**
     * Increment failed login attempts
     */
    private function incrementFailedAttempts($userId, $email) {
        $sql = "UPDATE {$this->table} SET failed_login_attempts = failed_login_attempts + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        // Get current attempt count
        $countSql = "SELECT failed_login_attempts FROM {$this->table} WHERE id = ?";
        $countStmt = $this->conn->prepare($countSql);
        $countStmt->bind_param("i", $userId);
        $countStmt->execute();
        $result = $countStmt->get_result()->fetch_assoc();
        
        // Lock account if max attempts reached
        if ($result['failed_login_attempts'] >= $this->maxFailedAttempts) {
            $lockedUntil = date('Y-m-d H:i:s', time() + $this->lockoutDuration);
            $lockSql = "UPDATE {$this->table} SET account_locked = 1, locked_until = ? WHERE id = ?";
            $lockStmt = $this->conn->prepare($lockSql);
            $lockStmt->bind_param("si", $lockedUntil, $userId);
            $lockStmt->execute();
        }
    }
    
    /**
     * Reset failed login attempts
     */
    private function resetFailedAttempts($userId) {
        $sql = "UPDATE {$this->table} SET failed_login_attempts = 0, account_locked = 0, locked_until = NULL WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    /**
     * Unlock account
     */
    private function unlockAccount($userId) {
        $sql = "UPDATE {$this->table} SET account_locked = 0, failed_login_attempts = 0, locked_until = NULL WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    /**
     * Get user reservations count
     */
    public function getUserReservationsCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM reservations WHERE customer_id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] ?? 0;
    }
    
    /**
     * Get user restaurants count
     */
    public function getUserRestaurantsCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM restaurants WHERE manager_id = ? AND is_deleted = 0";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['count'] ?? 0;
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
