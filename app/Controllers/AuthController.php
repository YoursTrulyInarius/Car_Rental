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

                    if ($user['role'] == 'admin') {
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

    public function register() {
        $error = '';
        $success = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'customer';
            $password = trim($_POST['password'] ?? '');
            $confirm_password = trim($_POST['confirm_password'] ?? '');

            if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
                $error = "All fields are required.";
            } elseif ($password != $confirm_password) {
                $error = "Passwords do not match.";
            } elseif (strlen($password) < 6) {
                $error = "Password must be at least 6 characters.";
            } else {
                if ($this->userModel->emailExists($email)) {
                    $error = "This email is already taken.";
                } else {
                    $allowed_roles = ['customer', 'admin'];
                    if (!in_array($role, $allowed_roles)) $role = 'customer';

                    if ($this->userModel->register($name, $email, $password, $role)) {
                        $success = "Registration successful! <a href='" . BASE_URL . "login.php'>Login here</a>.";
                    } else {
                        $error = "Something went wrong. Please try again.";
                    }
                }
            }
        }

        require_once __DIR__ . '/../Views/auth/register.php';
    }

    public function logout() {
        $_SESSION = array();
        session_destroy();
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}
