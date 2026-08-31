<?php
namespace App\Controllers;

use App\Models\User;

class AuthController {
    private $db;
    private $userModel;

    public function __construct($mysqli) {
        $this->db = $mysqli;
        $this->userModel = new User($mysqli);
    }

    /**
     * Handle user login
     */
    public function login() {
        $error = '';
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                $error = "Please enter both email and password.";
            } else {
                $user = $this->userModel->findByEmail($email);
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['name'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect based on role
                    if ($user['role'] == User::ROLE_OWNER) {
                        header("Location: " . BASE_URL . "admin/dashboard.php");
                    } else {
                        header("Location: " . BASE_URL . "dashboard.php");
                    }
                    exit;
                } else {
                    $error = "Invalid email or password.";
                }
            }
        }

        require_once __DIR__ . '/../Views/auth/login.php';
    }

    /**
     * Handle user registration
     */
    public function register() {
        $error = '';
        $success = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $role = $_POST['role'] ?? User::ROLE_CUSTOMER;
            $password = trim($_POST['password'] ?? '');
            $confirm_password = trim($_POST['confirm_password'] ?? '');

            if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
                $error = "Name, email, and password are required.";
            } elseif ($password != $confirm_password) {
                $error = "Passwords do not match.";
            } elseif (strlen($password) < 6) {
                $error = "Password must be at least 6 characters.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Please enter a valid email address.";
            } else {
                if ($this->userModel->emailExists($email)) {
                    $error = "This email is already registered.";
                } else {
                    // Validate role
                    $allowed_roles = [User::ROLE_CUSTOMER, User::ROLE_OWNER];
                    if (!in_array($role, $allowed_roles)) {
                        $role = User::ROLE_CUSTOMER;
                    }

                    if ($this->userModel->register($name, $email, $password, $role, $phone, $address)) {
                        $success = "Registration successful! <a href='" . BASE_URL . "login.php'>Login here</a>.";
                    } else {
                        $error = "Something went wrong. Please try again.";
                    }
                }
            }
        }

        require_once __DIR__ . '/../Views/auth/register.php';
    }

    /**
     * Handle user logout
     */
    public function logout() {
        $_SESSION = array();
        session_destroy();
        header("Location: " . BASE_URL . "login.php");
        exit;
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole($role) {
        return self::isAuthenticated() && isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    /**
     * Check if user is owner or staff
     */
    public static function isAdmin() {
        return self::isAuthenticated() && isset($_SESSION['role']) &&
               in_array($_SESSION['role'], [User::ROLE_OWNER]);
    }

    /**
     * Require authentication
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }
    }

    /**
     * Require admin access
     */
    public static function requireAdmin() {
        if (!self::isAdmin()) {
            header("Location: " . BASE_URL . "login.php");
            exit;
        }
    }

    /**
     * Get current user ID
     */
    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user role
     */
    public static function getCurrentUserRole() {
        return $_SESSION['role'] ?? null;
    }
}
