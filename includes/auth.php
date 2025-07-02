<?php
require_once __DIR__ . '/db.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login($personal_no, $password) {
        error_log("Login attempt for personal_no: " . $personal_no);
        
        // Check if user is locked out
        if ($this->isLockedOut()) {
            error_log("Account is locked out");
            return ['success' => false, 'message' => 'Account is temporarily locked. Please try again later.'];
        }

        // Get user from database
        $stmt = $this->db->query(
            "SELECT * FROM users WHERE personal_no = :personal_no",
            ['personal_no' => $personal_no]
        );
        $user = $stmt->fetch();
        
        error_log("User found: " . ($user ? 'yes' : 'no'));

        if (!$user) {
            error_log("Invalid personal number");
            $this->recordFailedAttempt();
            return ['success' => false, 'message' => 'Invalid personal number or password.'];
        }

        // Verify password
        $password_verified = password_verify($password, $user['password']);
        error_log("Password verification: " . ($password_verified ? 'success' : 'failed'));
        
        if (!$password_verified) {
            error_log("Invalid password");
            $this->recordFailedAttempt();
            return ['success' => false, 'message' => 'Invalid personal number or password.'];
        }

        // Clear failed attempts on successful login
        $this->clearFailedAttempts();

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['personal_no'] = $user['personal_no'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();

        error_log("Login successful for user: " . $user['personal_no']);
        return ['success' => true, 'user' => $user];
    }

    public function isLockedOut() {
        $stmt = $this->db->query(
            "SELECT COUNT(*) as attempt_count FROM login_attempts 
             WHERE ip_address = :ip AND attempt_time > NOW() - (INTERVAL '1 minute' * :lockout_time)",
            [
                'ip' => $_SERVER['REMOTE_ADDR'],
                'lockout_time' => LOCKOUT_TIME
            ]
        );
        $result = $stmt->fetch();
        $is_locked = $result['attempt_count'] >= MAX_LOGIN_ATTEMPTS;
        error_log("Lockout check - Attempts: {$result['attempt_count']}, Locked: " . ($is_locked ? 'yes' : 'no'));
        return $is_locked;
    }

    private function recordFailedAttempt() {
        $this->db->query(
            "INSERT INTO login_attempts (ip_address, attempt_time) VALUES (:ip, NOW())",
            ['ip' => $_SERVER['REMOTE_ADDR']]
        );
        error_log("Failed attempt recorded for IP: " . $_SERVER['REMOTE_ADDR']);
    }

    private function clearFailedAttempts() {
        $this->db->query(
            "DELETE FROM login_attempts WHERE ip_address = :ip",
            ['ip' => $_SERVER['REMOTE_ADDR']]
        );
        error_log("Failed attempts cleared for IP: " . $_SERVER['REMOTE_ADDR']);
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        session_destroy();
        return true;
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $stmt = $this->db->query(
            "SELECT id, personal_no, role FROM users WHERE id = :id",
            ['id' => $_SESSION['user_id']]
        );
        return $stmt->fetch();
    }
} 