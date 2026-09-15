# Multi-Service Booking and Reservation Management System (MSBRMS)

A web-based booking system that allows customers to reserve appointments/slots
across multiple service categories (clinics, salons, meeting rooms, consultancy),
with a separate admin dashboard for managing services, time slots, bookings and users.

## Tech Stack
- PHP 8.x (native, PDO, MVC-style architecture)
- MySQL 5.7+ / MariaDB
- Bootstrap 5 (via CDN)
- Apache (XAMPP / WAMP / LAMP)

## Requirements
- XAMPP (or WAMP/MAMP) with PHP >= 8.0 and MySQL
- A modern web browser
- Internet connection on first load (Bootstrap/Icons are loaded from CDN).
  To run fully offline, download bootstrap.min.css/js and bootstrap-icons
  into /public/css and /public/js and update the links in views/layouts/header.php.

## Installation Steps

1. **Copy the project folder**
   Copy the entire `booking_system` folder into your web server's document root,
   e.g. `C:\xampp\htdocs\booking_system` (Windows) or `/Applications/XAMPP/htdocs/booking_system` (Mac).

2. **Start Apache and MySQL**
   Open the XAMPP Control Panel and start both services.

3. **Create the database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Click "Import" → choose `database/schema.sql` → Go
   - This creates the `msbrms` database with all tables and seed/sample data.

4. **Configure database credentials (if needed)**
   Edit `config/db.php` if your MySQL username/password differ from the
   XAMPP defaults (`root` / empty password).

5. **Access the system**
   - Public site: http://localhost/booking_system/index.php
   - Demo admin login: `admin@msbrms.local` / `Admin@123`
   - Register a new account to test the customer flow.

6. **Generate time slots**
   Log in as admin → Services → "Generate Slots" → choose a service and a
   date range. This is required before customers can book that service.

## Folder Structure
```
booking_system/
├── admin/              Admin-side pages (dashboard, services, bookings, users)
├── customer/            Customer-side pages (browse, book, my bookings)
├── config/              Database configuration
├── controllers/         (reserved for future controller separation)
├── includes/            Shared helper functions (auth, sanitisation)
├── models/               Business logic and data access (User, Service, TimeSlot, Booking)
├── views/layouts/        Shared header/footer templates
├── public/                CSS/JS/uploaded assets
├── database/schema.sql   Full database schema + seed data
├── index.php, login.php, register.php, logout.php  Entry points
└── README.md
```

## Key Feature: Preventing Double-Booking
See `models/Booking.php::createBooking()`. The system uses a MySQL
transaction with `SELECT ... FOR UPDATE` to lock the target slot row before
re-checking its availability, closing the "check-then-act" race condition
that would otherwise allow two users to book the same slot simultaneously.
This is discussed in detail in Chapter 4 of the project report.
