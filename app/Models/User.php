<?php
namespace App\Models;

class User {
    private $db;

    public function __construct($mysqli) {
        $this->db = $mysqli;
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    public function register($name, $email, $password, $role = 'customer') {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
        return $stmt->execute();
    }

    public function getOwners() {
        return $this->db->query("SELECT id, name, email FROM users WHERE role = 'admin'");
    }

    public function getOwnersWithCars() {
        return $this->db->query("SELECT DISTINCT u.id, u.name, u.email FROM users u JOIN cars c ON u.id = c.owner_id WHERE u.role = 'admin'");
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, name, email, role FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
