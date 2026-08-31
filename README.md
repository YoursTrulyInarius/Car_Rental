# 🚗 CAR RENTAL

A multi-role vehicle rental management system built with PHP, MySQL, and Bootstrap. The app supports owners, staff, and customers with a role-based dashboard flow, fleet management, rental processing, and seeded admin access.

## Project status

The system has been updated and stabilized with:

- role normalization to owner, staff, and customer
- fixed login redirect logic for admin/owner/staff access
- seeded default owner account for system access
- dashboard and navigation checks aligned to actual role values
- DB connection and password handling fixes
- rebranding to CAR RENTAL across the app

## Default login

Use the seeded owner account to access the admin dashboard:

- Email: admin@carental.com
- Password: admin123

## Role model

| Role | Access |
|---|---|
| owner | full admin/dashboard access |
| staff | admin/dashboard access |
| customer | customer dashboard and rental flow |

## Features

- secure login and registration flow
- role-based routing and dashboard access
- vehicle listing and owner-based fleet management
- rental request workflow with pending/approved/rejected/completed states
- payment, reservation, and audit tracking tables
- Bootstrap interface with modern dashboard layout
- PHPMailer integration for transactional notifications

## Tech stack

- PHP 8.x
- MySQL
- Bootstrap 5
- PHPMailer
- MVC architecture with app/Models, app/Controllers, and app/Views

## Setup

1. Start Apache and MySQL in XAMPP.
2. Create the database using the schema in database.sql.
3. Update the credentials in config.php if needed.
4. Open the project in the browser at http://localhost/Car_Rental/

## Important notes

- The seeded admin account is created from database.sql.
- Legacy admin role values are accepted only as backward compatibility during registration; the canonical role set in the app is owner, staff, and customer.
- The default owner account should be changed after initial setup in a real production environment.

## Main folders

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
└── LICENSE
```

## Recent fixes included

- fixed invalid legacy admin checks across controllers and views
- corrected database connection validation in config.php
- added compatibility for older admin values when registering users
- ensured the admin dashboard receives the required variables and model data
- aligned navigation and footer behavior to valid owner/staff roles
- updated the default seeded admin hash so it matches admin123

This project is now aligned to the current role-based app logic and ready for use in the local XAMPP environment.
