# 🚗 JEBWINE'S PREMIUM CAR RENTAL

A modern, full-stack car rental management platform built with **Vanilla PHP**, **MySQL**, and **Bootstrap 5**. Designed with a premium aesthetic and enterprise-level features including real-time notifications and automated email automation.

## ✨ Features

### 🛠 Admin Module
- **Real-Time Notifications**: Instant red alerts and navbar badges for new rental requests using AJAX polling.
- **Fleet Management**: Full CRUD operations for vehicle inventory with stock/quantity management.
- **Smart Dashboard**: High-level metrics (Total Revenue, Active Rentals, Pending Requests) with real-time data updates.
- **Rental Management**: One-click approval/rejection with professional SweetAlert2 feedback.
- **Automated Alerts**: Email notifications sent to customers upon status changes.

### 👤 User Module
- **Dynamic Fleet Browsing**: Real-time availability badges showing exact stock or "Available on [Date]".
- **Seamless Booking**: Easy-to-use rental process with automatic total price calculation and validation.
- **Terms & Conditions**: Integrated agreement verification during checkout.
- **Rental History**: Dedicated dashboard to track the status of personal booking requests.

## 🚀 Tech Stack
- **Backend**: PHP 8.x
- **Database**: MySQL
- **Frontend**: Bootstrap 5, Custom CSS3, Vanilla JS
- **Libraries**: 
  - [PHPMailer](https://github.com/PHPMailer/PHPMailer) for email automation.
  - [SweetAlert2](https://sweetalert2.github.io/) for modern UI notifications.
  - [Bootstrap Icons](https://icons.getbootstrap.com/) for iconography.

## ⚙️ Installation & Setup

1. **Clone the project** to your local server (e.g., `xampp/htdocs/`).
2. **Setup Database**:
   - Create a database named `car_rental`.
   - Import the provided `database.sql` file.
3. **Configure Environment**:
   - Update `config.php` with your database credentials.
   - Adjust `BASE_URL` to match your local path (default: `http://localhost/Car_Rental/`).
4. **Email Setup**:
   - The system is configured for **Gmail SMTP**.
   - Input your Gmail address and App Password in `admin/rentals.php`.
   - *Note: SSL verification bypass is enabled for local development compatibility.*
5. **Permissions**:
   - Ensure the `uploads/` directory has write permissions for car images.

## 🎨 Design Philosophy
The system utilizes a **Premium Deep Blue & Light Gray** theme focusing on:
- **Rich Aesthetics**: Glassmorphism, smooth gradients, and micro-animations.
- **Responsive Layouts**: Optimized for mobile, tablet, and desktop viewing.
- **Visual Hierarchy**: Clear typography and color-coded status badges.

---
*Created with ❤️ by JEBWINE'S Development Team.*
