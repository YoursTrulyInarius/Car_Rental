# Vehicle Rental Management System - Implementation Guide

## Overview
This is a comprehensive Vehicle Rental Management System built according to the SRS (Software Requirements Specification). The system manages vehicle inventory, customer rentals, payments, and reservations with proper role-based access control.

## Project Structure

```
Car_Rental/
├── config.php                 # Database and application configuration
├── database.sql               # Complete database schema
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── CarController.php
│   │   ├── DashboardController.php
│   │   ├── RentalController.php
│   │   ├── PaymentController.php
│   │   └── ReservationController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Car.php
│   │   ├── Rental.php
│   │   ├── Payment.php
│   │   └── Reservation.php
│   └── Views/
│       └── [View templates]
└── admin/
    └── [Admin panel files]
```

## Database Schema

### Tables Created

1. **users** - Stores all system users (owner, staff, customers)
   - Roles: owner, staff, customer
   - Status: active, inactive, suspended
   - Fields: id, name, email, password, phone, address, role, status, created_at, updated_at

2. **cars** - Vehicle information
   - Status: available, reserved, rented, maintenance
   - Types: sedan, suv, truck, van, sports, other
   - Fields: id, plate_number, brand, model, year, type, price_per_day, owner_id, status, image, description, created_at, updated_at

3. **rentals** - Rental transactions
   - Status: pending, approved, rejected, completed, cancelled
   - Fields: id, user_id, car_id, start_date, end_date, actual_return_date, total_price, status, notes, created_at, updated_at

4. **reservations** - Vehicle reservations
   - Status: pending, confirmed, cancelled, completed
   - Fields: id, user_id, car_id, start_date, end_date, status, notes, created_at, updated_at

5. **payments** - Payment records
   - Payment Methods: cash, credit_card, debit_card, bank_transfer, online
   - Payment Status: pending, completed, cancelled, refunded
   - Fields: id, rental_id, user_id, amount, payment_method, payment_status, transaction_id, reference_number, payment_date, notes, created_at, updated_at

6. **audit_logs** - System activity tracking for security and compliance

## User Roles & Permissions

### Customer Role
- Browse available vehicles
- Create rental requests
- Create reservations
- View rental history
- Make payments
- Manage personal reservations

### Staff Role
- Access to admin panel
- Process rental requests (approve/reject)
- Manage vehicle information
- Process payments
- View rental statistics
- Limited admin features

### Owner Role
- Full admin access
- Can rent/reserve their own vehicles
- Manage all vehicles, rentals, reservations, payments
- View business analytics and statistics
- Manage staff and customer accounts
- Access audit logs

## Key Features Implemented

### 1. Vehicle Management
- Add/edit/delete vehicles
- Track vehicle status (available, reserved, rented, maintenance)
- Search and filter vehicles by:
  - Brand and model
  - Price range
  - Type (sedan, suv, etc.)
  - Availability status
  - Date availability

**Models/Methods:**
```php
$carModel->getAll($owner_id_filter, $status_filter, $type_filter)
$carModel->isAvailableForPeriod($car_id, $start_date, $end_date)
$carModel->search($searchTerm, $filters)
$carModel->getAvailable($start_date, $end_date, $price_max)
```

### 2. Customer Management
- User registration with role selection
- User profile management
- Search users by name or email
- User status management (active, inactive, suspended)
- User role assignment

**Models/Methods:**
```php
$userModel->register($name, $email, $password, $role, $phone, $address)
$userModel->updateUser($id, $name, $email, $phone, $address)
$userModel->updateRole($id, $role)
$userModel->updateStatus($id, $status)
$userModel->search($searchTerm, $role)
```

### 3. Rental Management
- Create rental requests
- Approve/reject rental requests
- Track rental status
- Calculate rental costs automatically
- Prevent overlapping rentals
- Support owner self-rentals
- Track actual return dates
- Email notifications for status updates

**Models/Methods:**
```php
$rentalModel->createRental($user_id, $car_id, $start_date, $end_date, $total_price)
$rentalModel->updateStatus($rental_id, $status)
$rentalModel->completeRental($id, $actual_return_date)
$rentalModel->getStatistics()
$rentalModel->getByDateRange($start_date, $end_date, $status)
```

### 4. Reservation System
- Reserve vehicles for future rental
- Check vehicle availability for period
- Manage reservations (confirm, cancel, complete)
- Convert reservations to rentals
- Track upcoming reservations

**Models/Methods:**
```php
$reservationModel->create($user_id, $car_id, $start_date, $end_date, $notes)
$reservationModel->isAvailableForPeriod($car_id, $start_date, $end_date)
$reservationModel->convertToRental($reservation_id)
$reservationModel->getUpcoming($days)
```

### 5. Payment Management
- Record payments for rentals
- Support multiple payment methods
- Track payment status (pending, completed, cancelled, refunded)
- Generate payment reports
- Audit payment transactions

**Models/Methods:**
```php
$paymentModel->create($rental_id, $user_id, $amount, $payment_method)
$paymentModel->updateStatus($id, $status, $payment_date)
$paymentModel->getStatistics()
$paymentModel->getByDateRange($start_date, $end_date, $status)
$paymentModel->getTotalAmount($status)
```

### 6. Business Rules & Validation
- **Availability Check**: Automatically prevents double-booking
- **Date Validation**: Ensures end_date > start_date
- **Price Calculation**: Automatic based on rental days
- **Role-Based Access**: Enforced at controller and model level
- **Status Workflow**: 
  - Rentals: pending → approved/rejected → completed
  - Payments: pending → completed/refunded
  - Reservations: pending → confirmed → completed

### 7. Search & Filter Functionality
- Search vehicles by brand, model, plate number
- Filter by:
  - Price range
  - Vehicle type
  - Availability status
  - Date range
- Search users by name or email

### 8. Reporting & Analytics
- Rental statistics
- Payment statistics
- Vehicle utilization reports
- Revenue tracking
- Customer rental history
- Audit logs for compliance

## API/Method Reference

### Authentication Controller
```php
AuthController::isAuthenticated()           // Check if user logged in
AuthController::hasRole($role)             // Check specific role
AuthController::isAdmin()                  // Check if owner or staff
AuthController::requireAuth()              // Redirect if not authenticated
AuthController::requireAdmin()             // Redirect if not admin
AuthController::getCurrentUserId()         // Get current user ID
AuthController::getCurrentUserRole()       // Get current user role
```

### User Model (User::ROLE_*)
```
User::ROLE_OWNER = 'owner'
User::ROLE_STAFF = 'staff'
User::ROLE_CUSTOMER = 'customer'

User::STATUS_ACTIVE = 'active'
User::STATUS_INACTIVE = 'inactive'
User::STATUS_SUSPENDED = 'suspended'
```

### Car Model (Car::STATUS_*)
```
Car::STATUS_AVAILABLE = 'available'
Car::STATUS_RESERVED = 'reserved'
Car::STATUS_RENTED = 'rented'
Car::STATUS_MAINTENANCE = 'maintenance'

Car::TYPES = ['sedan', 'suv', 'truck', 'van', 'sports', 'other']
```

### Rental Model (Rental::STATUS_*)
```
Rental::STATUS_PENDING = 'pending'
Rental::STATUS_APPROVED = 'approved'
Rental::STATUS_REJECTED = 'rejected'
Rental::STATUS_COMPLETED = 'completed'
Rental::STATUS_CANCELLED = 'cancelled'
```

### Payment Model (Payment::*)
```
Payment::METHOD_CASH = 'cash'
Payment::METHOD_CREDIT_CARD = 'credit_card'
Payment::METHOD_DEBIT_CARD = 'debit_card'
Payment::METHOD_BANK_TRANSFER = 'bank_transfer'
Payment::METHOD_ONLINE = 'online'

Payment::STATUS_PENDING = 'pending'
Payment::STATUS_COMPLETED = 'completed'
Payment::STATUS_CANCELLED = 'cancelled'
Payment::STATUS_REFUNDED = 'refunded'
```

### Reservation Model (Reservation::STATUS_*)
```
Reservation::STATUS_PENDING = 'pending'
Reservation::STATUS_CONFIRMED = 'confirmed'
Reservation::STATUS_CANCELLED = 'cancelled'
Reservation::STATUS_COMPLETED = 'completed'
```

## Setup Instructions

### 1. Database Setup
1. Import `database.sql` into your MySQL database:
   ```sql
   mysql -u root car_rental < database.sql
   ```

2. Or run the SQL file through phpMyAdmin

### 2. Configuration
Edit `config.php`:
- Update DB_SERVER, DB_USERNAME, DB_PASSWORD if needed
- Configure SMTP settings for email notifications
- Update BASE_URL to match your installation

### 3. File Permissions
Ensure `uploads/` directory is writable:
```bash
chmod 755 uploads/
```

### 4. Test the System
1. Navigate to `index.php`
2. Register as a customer or owner
3. Log in with your credentials
4. Test different features based on your role

## Security Features

1. **Password Hashing**: Using PHP's password_hash() with DEFAULT algorithm
2. **SQL Injection Prevention**: Prepared statements with parameterized queries
3. **Session Management**: Proper session handling with role-based access control
4. **Input Validation**: Email validation, date range checks, role verification
5. **Audit Logging**: System tracks important actions
6. **HTTPS Recommended**: For production deployment
7. **Role-Based Access Control**: Each controller verifies user role

## Email Notifications
The system uses PHPMailer to send:
- Rental status updates (approved/rejected)
- Payment confirmations
- Reservation confirmations

Configure SMTP in `config.php` for this to work.

## Business Logic

### Vehicle Availability Algorithm
The system checks for conflicts before creating rentals/reservations:
```
AVAILABLE IF:
  - No overlapping approved/pending rentals
  - No overlapping confirmed/pending reservations
  - Vehicle status = 'available'
```

### Rental Cost Calculation
```
Total Price = Days * Price per Day
Where Days = (end_date - start_date) or 1 if same day
```

### Status Workflows

**Rental Workflow:**
```
Pending → Approved → Completed
       ↓
       Rejected
       
Approved → Cancelled (by customer/admin)
```

**Payment Workflow:**
```
Pending → Completed → Refunded
       ↓
       Cancelled
```

**Reservation Workflow:**
```
Pending → Confirmed → Completed
       ↓
       Cancelled
       
Can convert to Rental at any stage
```

## Future Enhancements

Based on the SRS, consider implementing:
1. Online payment gateway integration (Stripe, PayPal)
2. SMS notifications
3. Vehicle insurance tracking
4. Maintenance scheduling system
5. Customer rating/review system
6. Mileage tracking
7. Fuel management
8. Late fee calculation
9. Mobile app
10. Advanced reporting dashboards

## Troubleshooting

### Database Connection Error
- Check credentials in config.php
- Verify MySQL server is running
- Ensure database exists

### Email Not Sending
- Configure SMTP settings in config.php
- Check firewall/port 587 access
- Verify Gmail/SMTP credentials

### Session Issues
- Clear browser cookies
- Check PHP session.save_path
- Verify session timeout settings

## Support & Documentation
Refer to the SRS document for complete requirements and specifications.
