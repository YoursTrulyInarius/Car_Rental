# 🚗 Car Rental System

A PHP-based car rental web application for managing vehicle listings, rental requests, and approval workflows. The system uses a simple two-role structure: admin/car owners and regular users/renters.

## Overview

This project allows:

- users to browse available vehicles and view car details
- guests or users to register and sign in
- renters to request a booking for a selected vehicle
- admins/car owners to review pending rental requests
- admins/car owners to approve, reject, or manage rental operations

## Default login

Use the seeded admin account to access the dashboard:

- Email: admin@carental.com
- Password: admin123

## Roles

| Role | Access |
|---|---|
| admin / car owner | full system and fleet access; manages listings and rental approvals |
| user / renter | browses cars, requests bookings, and views rental activity |

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
    B --> C{User type}

    C -->|User / Renter| D[Browse available cars]
    D --> E[View car details]
    E --> F[Book now / request rental]
    F --> G[Submit booking request]
    G --> H[Admin / Car Owner reviews request]

    H --> I{Approved?}
    I -->|No| J[Request rejected]
    I -->|Yes| K[Rental proceeds to active stage]
    K --> L[User completes rental period]
    L --> M[Rental marked completed]

    C -->|Admin / Car Owner| N[Open admin dashboard]
    N --> O[Manage vehicles and rental requests]
    O --> P[Approve or reject bookings]
    P --> Q[Monitor rental activity]
```

## Typical user flow

1. A user visits the public site.
2. The user signs up or logs in.
3. A renter browses available cars, applies filters, and opens car details.
4. The renter clicks Book Now to submit a rental request.
5. The admin/car owner receives the request in the admin dashboard.
6. The request is approved or rejected.
7. If approved, the rental proceeds to the active stage.
8. The renter can then track the rental in their account area.

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
- The system uses two main roles: admin/car owner and user/renter.
- In production, the default admin credentials should be changed after setup.

## Status

The project is currently structured for local deployment and supports a working customer-to-admin rental workflow with a modern web interface.
