# 🚗 Car Rental System

A PHP-based car rental web application for managing vehicle listings, customer rentals, and admin approval workflows. The system is designed for a multi-role setup with customers, owners, and staff using a simple MVC structure with Bootstrap styling.

## Overview

This project allows:

- customers to browse available vehicles and view car details
- guests or users to register and sign in
- renters to request a booking for a specific vehicle
- owners/admins to review pending rental requests
- admins to approve, reject, or manage rental operations

## Default login

Use the seeded admin account to access the dashboard:

- Email: admin@carental.com
- Password: admin123

## Roles

| Role | Access |
|---|---|
| owner | full system and fleet access |
| staff | dashboard and operational access |
| customer | browse cars, request bookings, and view rentals |

## Features

- user registration and login flow
- role-based dashboard system
- car catalog with filters and details modal
- vehicle availability and booking request handling
- admin approval workflow for rentals
- rental status tracking
- responsive Bootstrap UI
- PHPMailer based notifications

## How the system works

```mermaid
flowchart TD
    A[Visitor opens homepage] --> B[Register or Sign In]
    B --> C{User role}

    C -->|Customer| D[Browse available cars]
    D --> E[View car details modal]
    E --> F[Select vehicle and book now]
    F --> G[Create rental request]
    G --> H[Admin/Owner reviews request]

    H --> I{Request approved?}
    I -->|No| J[Rental rejected]
    I -->|Yes| K[Payment / confirmation step]
    K --> L[Rental becomes active]
    L --> M[Customer completes rental period]
    M --> N[Rental marked completed]

    C -->|Owner/Staff| O[Access dashboard]
    O --> P[Manage cars and rental requests]
    P --> Q[Approve or reject bookings]
    Q --> R[Monitor operations and activity]
```

## Typical user flow

1. A user visits the public site.
2. The user signs up or logs in.
3. A customer browses available cars, applies filters, and opens car details.
4. The customer clicks Book Now or proceeds with the rental request.
5. The owner/staff receives the request in the admin dashboard.
6. The request is approved or rejected.
7. If approved, the rental moves into the payment/active stage.
8. The customer can then track the rental in their account area.

## Tech stack

- PHP
- MySQL
- Bootstrap 5
- PHPMailer
- MVC architecture with app/Models, app/Controllers, and app/Views

## Setup

1. Start Apache and MySQL in XAMPP.
2. Import the database schema from database.sql.
3. Update database credentials in config.php if needed.
4. Open the project in your browser at http://localhost/Car_Rental/

## Project structure

```text
Car_Rental/
├── app/
│   ├── Controllers/
│   ├── Models/
│   └── Views/
├── admin/
├── assets/
├── uploads/
├── config.php
├── database.sql
├── index.php
├── login.php
├── register.php
├── dashboard.php
├── my_rentals.php
├── rent_process.php
├── README.md
├── LICENSE
└── other project docs
```

## Notes

- The seeded admin account is created from database.sql.
- Roles are normalized to owner, staff, and customer.
- In production, the default admin credentials should be changed after setup.

## Status

The project is currently structured for local deployment and supports a working customer-to-admin rental workflow with a modern web interface.
