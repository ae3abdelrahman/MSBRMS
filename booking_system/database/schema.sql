-- ============================================================
-- Multi-Service Booking and Reservation Management System
-- (MSBRMS) - Database Schema
-- Final Year Project - CBBR4106
-- ============================================================

CREATE DATABASE IF NOT EXISTS msbrms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE msbrms;

-- ------------------------------------------------------------
-- Table: users
-- Stores both admin and customer accounts
-- ------------------------------------------------------------
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
    status ENUM('active','suspended') NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: service_categories
-- e.g. Medical Clinic, Salon & Spa, Meeting Room, Consultancy
-- ------------------------------------------------------------
CREATE TABLE service_categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'bi-calendar',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: services
-- The individual bookable service/resource under a category
-- ------------------------------------------------------------
CREATE TABLE services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    provider_name VARCHAR(100) NOT NULL,      -- e.g. Dr. Ahmed / Room A / Stylist Sara
    service_name VARCHAR(150) NOT NULL,
    description TEXT,
    duration_minutes INT NOT NULL DEFAULT 30,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    location VARCHAR(150),
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES service_categories(category_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: time_slots
-- Discrete bookable slots per service. Prevents double booking
-- via UNIQUE + status check at application layer (transaction).
-- ------------------------------------------------------------
CREATE TABLE time_slots (
    slot_id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    slot_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    status ENUM('available','booked','blocked') NOT NULL DEFAULT 'available',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE,
    UNIQUE KEY uniq_slot (service_id, slot_date, start_time)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: bookings
-- ------------------------------------------------------------
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_ref VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    slot_id INT NOT NULL,
    status ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'confirmed',
    notes VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (slot_id) REFERENCES time_slots(slot_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: booking_logs
-- Audit trail - used as evidence in Testing chapter
-- ------------------------------------------------------------
CREATE TABLE booking_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,          -- created / confirmed / cancelled / completed
    performed_by VARCHAR(100),
    log_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Seed data: default admin (password = Admin@123)
-- ------------------------------------------------------------
-- Password for the seeded admin account is: Admin@123
INSERT INTO users (full_name, email, phone, password_hash, role) VALUES
('System Administrator', 'admin@msbrms.local', '0100000000',
 '$2b$12$SdGYUaqns7gaTmfw5KE2Dew/oy65itIRxqVO1tuYVAphy4mCVNvxq', 'admin');

-- Seed categories
INSERT INTO service_categories (category_name, description, icon) VALUES
('Medical Clinic', 'General practitioner and specialist consultations', 'bi-hospital'),
('Salon & Spa', 'Hair, beauty and wellness treatments', 'bi-scissors'),
('Meeting Rooms', 'Corporate meeting and conference room reservations', 'bi-building'),
('Consultancy', 'Legal, financial and academic consultation sessions', 'bi-briefcase');

-- Seed services
INSERT INTO services (category_id, provider_name, service_name, description, duration_minutes, price, location) VALUES
(1, 'Dr. Ahmad Faris', 'General Consultation', 'General health check-up and consultation', 30, 50.00, 'Clinic Room 1'),
(1, 'Dr. Nadia Hassan', 'Dental Check-up', 'Routine dental examination', 45, 80.00, 'Clinic Room 2'),
(2, 'Stylist Sara', 'Haircut & Styling', 'Professional haircut and styling session', 60, 40.00, 'Salon Bay 1'),
(2, 'Therapist Lina', 'Relaxation Massage', 'Full body relaxation massage', 60, 90.00, 'Spa Room A'),
(3, 'Front Office', 'Meeting Room A (10 pax)', 'Meeting room with projector and whiteboard', 60, 30.00, 'Level 2, Room A'),
(3, 'Front Office', 'Conference Hall (50 pax)', 'Large conference hall for events', 120, 150.00, 'Level 1, Hall'),
(4, 'Mr. Karim Adel', 'Legal Consultation', 'One-on-one legal advisory session', 45, 100.00, 'Office 301'),
(4, 'Ms. Huda Salim', 'Academic Advising', 'Career and academic path consultation', 30, 0.00, 'Office 205');
