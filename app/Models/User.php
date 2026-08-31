<?php
namespace App\Models;

class User {
    private $db;
    
    // Define valid roles
    const ROLE_OWNER = 'owner';
    const ROLE_STAFF = 'staff';
    const ROLE_CUSTOMER = 'customer';
    
    // Define valid statuses
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    public function __construct($mysqli) {
        $this->db = $mysqli;
    }

    /**
     * Find user by email
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT id, name, password, role, status FROM users WHERE email = ? AND status = ?");
        $status = self::STATUS_ACTIVE;
        $stmt->bind_param("ss", $email, $status);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    /**
     * Check if email already exists
     */
    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    /**
     * Register new user
     */
    public function register($name, $email, $password, $role = self::ROLE_CUSTOMER, $phone = '', $address = '') {
        // Backward compatibility for older code and legacy records
        if ($role === 'admin' || $role === 'staff') {
            $role = self::ROLE_OWNER;
        }

        // Canonical roles for this app: owner and customer only
        $validRoles = [self::ROLE_OWNER, self::ROLE_CUSTOMER];
        if (!in_array($role, $validRoles)) {
            $role = self::ROLE_CUSTOMER;
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $status = self::STATUS_ACTIVE;
        
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, phone, address, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $hashed_password, $role, $phone, $address, $status);
        return $stmt->execute();
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, name, email, phone, address, role, status, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get all owners
     */
    public function getOwners() {
        $query = "SELECT id, name, email, phone, address, status, created_at FROM users WHERE role = ? ORDER BY name ASC";
        $stmt = $this->db->prepare($query);
        $role = self::ROLE_OWNER;
        $stmt->bind_param("s", $role);
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Get all staff members
     */
    public function getStaff() {
        $query = "SELECT id, name, email, phone, address, status, created_at FROM users WHERE role = ? ORDER BY name ASC";
        $stmt = $this->db->prepare($query);
        $role = self::ROLE_STAFF;
        $stmt->bind_param("s", $role);
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Get all customers
     */
    public function getCustomers() {
        $query = "SELECT id, name, email, phone, address, status, created_at FROM users WHERE role = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $role = self::ROLE_CUSTOMER;
        $stmt->bind_param("s", $role);
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Get owners with vehicles
     */
    public function getOwnersWithCars() {
        $query = "SELECT DISTINCT u.id, u.name, u.email, u.phone, COUNT(c.id) as car_count 
                  FROM users u 
                  LEFT JOIN cars c ON u.id = c.owner_id 
                  WHERE u.role = ? 
                  GROUP BY u.id 
                  ORDER BY u.name ASC";
        $stmt = $this->db->prepare($query);
        $role = self::ROLE_OWNER;
        $stmt->bind_param("s", $role);
        $stmt->execute();
        return $stmt->get_result();
    }

    /**
     * Update user information
     */
    public function updateUser($id, $name, $email, $phone = '', $address = '') {
        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $email, $phone, $address, $id);
        return $stmt->execute();
    }

    /**
     * Update user status
     */
    public function updateStatus($id, $status) {
        $validStatuses = [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_SUSPENDED];
        if (!in_array($status, $validStatuses)) {
            return false;
        }
        
        $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    /**
     * Update user role
     */
    public function updateRole($id, $role) {
        if ($role === 'admin' || $role === 'staff') {
            $role = self::ROLE_OWNER;
        }

        $validRoles = [self::ROLE_OWNER, self::ROLE_CUSTOMER];
        if (!in_array($role, $validRoles)) {
            return false;
        }
        
        $stmt = $this->db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $id);
        return $stmt->execute();
    }

    /**
     * Get user count by role
     */
    public function getCountByRole($role) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE role = ?");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] ?? 0;
    }

    /**
     * Get total user count
     */
    public function getTotalCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM users");
        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;
    }

    /**
     * Delete user
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Change password
     */
    public function changePassword($id, $newPassword) {
        $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $id);
        return $stmt->execute();
    }

    /**
     * Search users by name or email
     */
    public function search($searchTerm, $role = null) {
        $searchTerm = '%' . $searchTerm . '%';
        
        if ($role) {
            $query = "SELECT id, name, email, phone, role, status, created_at FROM users WHERE (name LIKE ? OR email LIKE ?) AND role = ? ORDER BY name ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("sss", $searchTerm, $searchTerm, $role);
        } else {
            $query = "SELECT id, name, email, phone, role, status, created_at FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY name ASC";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ss", $searchTerm, $searchTerm);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }
}
