# Vehicle Rental Management System - Quick Start Guide

## 🚀 Getting Started

### Prerequisites
- XAMPP / PHP 7.4+ with mysqli extension
- MySQL 5.7 or higher
- A code editor (VS Code recommended)
- Web browser

### Step 1: Database Setup

```bash
# Option A: Using command line (Windows)
mysql -u root car_rental < database.sql

# Option B: Using phpMyAdmin
1. Open http://localhost/phpmyadmin
2. Create new database: "car_rental"
3. Import database.sql file
4. Verify all tables are created
```

### Step 2: Configuration

Edit `config.php`:
```php
// Database (usually defaults are fine for local dev)
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');  // Leave empty for XAMPP
define('DB_NAME', 'car_rental');

// SMTP (for email notifications) - optional for testing
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');

// Base URL
define('BASE_URL', 'http://localhost/Car_Rental/');
```

### Step 3: File Permissions

```bash
# Linux/Mac
chmod 755 uploads/
chmod 644 config.php

# Windows
# Right-click uploads → Properties → Security → Edit → Allow Full Control
```

### Step 4: Verify Installation

1. Navigate to `http://localhost/Car_Rental/`
2. You should see the home page
3. Click "Register" to create test account
4. Test login with created credentials

---

## 📖 First Time Use

### Create Test Data

**1. Register as Owner**
```
Email: owner@test.com
Password: test123
Role: Owner
Name: John Owner
```

**2. Register as Customer**
```
Email: customer@test.com
Password: test123
Role: Customer
Name: Jane Customer
```

**3. Add Vehicles (as Owner)**
- Login as owner
- Go to Admin → Manage Vehicles
- Add a test vehicle:
  - Plate: ABC-123
  - Brand: Toyota
  - Model: Camry
  - Year: 2023
  - Price: ₱500/day
  - Type: Sedan

**4. Create Rental (as Customer)**
- Login as customer
- Browse available vehicles
- Select "Rent This Vehicle"
- Choose dates: Start today +1 day, End today +3 days
- Submit rental request

**5. Approve Rental (as Owner)**
- Login as owner
- Admin → Manage Rentals
- Approve pending rental

**6. Make Payment (as Customer)**
- Login as customer
- My Rentals → Select rental → Pay
- Select payment method: Cash
- Confirm payment

---

## 🏗️ Project Structure

```
app/
├── Controllers/
│   ├── AuthController.php          # Login, Register, Logout
│   ├── CarController.php           # Vehicle operations
│   ├── RentalController.php        # Rental management
│   ├── PaymentController.php       # Payment processing
│   └── ReservationController.php   # Reservations
│
├── Models/
│   ├── User.php                    # User operations
│   ├── Car.php                     # Vehicle operations
│   ├── Rental.php                  # Rental operations
│   ├── Payment.php                 # Payment operations
│   ├── Reservation.php             # Reservation operations
│   └── Validator.php               # Business logic validation
│
└── Views/
    ├── auth/                       # Login/Register pages
    ├── cars/                       # Car listing/details
    ├── dashboard/                  # User dashboards
    ├── rentals/                    # Rental pages
    └── includes/                   # Header/Footer/Navbar
```

---

## 💻 Common Development Tasks

### 1. Add a New Model Method

**File**: `app/Models/Car.php`
```php
public function getByBrand($brand) {
    $stmt = $this->db->prepare("SELECT * FROM cars WHERE brand = ? ORDER BY model");
    $stmt->bind_param("s", $brand);
    $stmt->execute();
    return $stmt->get_result();
}
```

### 2. Add a New Controller Action

**File**: `app/Controllers/CarController.php`
```php
public function getCarsByBrand() {
    $brand = $_GET['brand'] ?? '';
    $cars = $this->carModel->getByBrand($brand);
    require_once __DIR__ . '/../Views/cars/list.php';
}
```

### 3. Validate Input

**Use Validator Model**:
```php
$validator = new Validator($mysqli);

if (!$validator->validateRentalPeriod($start_date, $end_date)) {
    $errors = $validator->getErrors();
    // Handle errors
}

if (!$validator->validateVehicleAvailability($car_id, $start_date, $end_date)) {
    $errors = $validator->getErrors();
    // Handle errors
}
```

### 4. Query Best Practices

**❌ Bad** - SQL Injection vulnerability:
```php
$result = $mysqli->query("SELECT * FROM users WHERE id = $id");
```

**✅ Good** - Using prepared statements:
```php
$stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
```

### 5. Authorization Check

```php
// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

// Check if user is owner
if ($_SESSION['role'] !== 'owner') {
    header("Location: " . BASE_URL . "dashboard.php");
    exit;
}

// Or use controller helper
AuthController::requireAdmin();
```

---

## 🔍 Debugging Tips

### Enable Error Display (Development Only)

Add to `config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Log Errors

```php
error_log("User ID: " . $_SESSION['user_id'] . " attempted to access admin panel");
```

### Database Debug

```php
// Show SQL query before execution
$query = "SELECT * FROM cars WHERE status = 'available'";
error_log("Query: " . $query);
$result = $mysqli->query($query);
```

### Session Debug

```php
// Print session data
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
```

---

## 📊 Database Relationships

```
users (1) ──→ (∞) cars (as owner)
users (1) ──→ (∞) rentals
users (1) ──→ (∞) reservations
users (1) ──→ (∞) payments

cars (1) ──→ (∞) rentals
cars (1) ──→ (∞) reservations

rentals (1) ──→ (1) payments
```

---

## 🎯 Common Workflows

### Rental Workflow
```
Customer creates rental request
        ↓
Admin approves/rejects
        ↓
If approved: Customer pays
        ↓
Admin confirms payment
        ↓
Rental is active
        ↓
Rental completed (manual)
```

### Reservation Workflow
```
Customer creates reservation
        ↓
Admin confirms/cancels
        ↓
If confirmed: Can convert to rental
        ↓
Reservation completed
```

### Payment Workflow
```
Customer initiates payment
        ↓
Payment recorded (pending status)
        ↓
Admin confirms payment
        ↓
Payment marked as completed
```

---

## 🔐 Security Checklist

- [ ] Configure database credentials in `config.php`
- [ ] Set strong database password in production
- [ ] Configure SMTP credentials for email
- [ ] Enable HTTPS in production
- [ ] Set proper file permissions (755 for dirs, 644 for files)
- [ ] Disable error display in production
- [ ] Regular backups of database
- [ ] Monitor audit_logs table
- [ ] Update PHP version to latest
- [ ] Use environment variables for sensitive data

---

## 🆘 Troubleshooting

### "Database Connection Error"
```
✓ Check credentials in config.php
✓ Verify MySQL is running
✓ Check database name is correct
✓ Verify user has proper permissions
```

### "Undefined variable" in views
```
✓ Check variable is set before render
✓ Verify controller passes data to view
✓ Check variable name spelling
```

### "404 Not Found" errors
```
✓ Verify file path is correct
✓ Check BASE_URL in config.php
✓ Verify file exists in specified location
✓ Check file permissions
```

### "Session not persisting"
```
✓ Check session_start() is called in config.php
✓ Verify session.save_path is writable
✓ Check browser cookie settings
✓ Verify no session_destroy() calls
```

### "Email not sending"
```
✓ Configure SMTP in config.php
✓ Verify SMTP credentials
✓ Check port 587 is open
✓ Allow "Less secure apps" if using Gmail
✓ Verify email format is valid
```

---

## 📚 Useful Resources

- **PHP Documentation**: https://www.php.net/docs.php
- **MySQLi Documentation**: https://www.php.net/manual/en/book.mysqli.php
- **OWASP Security**: https://owasp.org/
- **Bootstrap CSS**: https://getbootstrap.com/

---

## 🚀 Deployment Checklist

- [ ] Test all features locally
- [ ] Update BASE_URL for production
- [ ] Configure production database
- [ ] Set up SSL/HTTPS certificate
- [ ] Configure email with production SMTP
- [ ] Disable error display
- [ ] Set up database backups
- [ ] Set proper file permissions
- [ ] Enable logging
- [ ] Test payment processing (if integrated)
- [ ] Create admin account
- [ ] Do final testing on production
- [ ] Monitor error logs
- [ ] Set up monitoring/alerts

---

## 📞 Getting Help

1. Check `IMPLEMENTATION_GUIDE.md` for detailed documentation
2. Review model/controller comments
3. Check database schema in `database.sql`
4. Test with sample data
5. Enable error display for debugging
6. Check error logs

---

**Happy Coding! 🎉**
