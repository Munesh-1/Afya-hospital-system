-- Sunrise Hospital Receptionist System database schema
-- Run this script in MySQL/MariaDB (e.g. via phpMyAdmin or the MySQL CLI)

CREATE DATABASE IF NOT EXISTS sunrise_hospital
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE sunrise_hospital;

-- Receptionists / system users
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  username VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  last_login_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Patients
CREATE TABLE IF NOT EXISTS patients (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  age TINYINT UNSIGNED NOT NULL,
  gender ENUM('Male', 'Female', 'Other') NOT NULL,
  contact VARCHAR(50) NOT NULL,
  email VARCHAR(150) DEFAULT NULL,
  address VARCHAR(255) DEFAULT NULL,
  medical_history TEXT,
  created_by INT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_patients_created_by FOREIGN KEY (created_by)
    REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Appointments
CREATE TABLE IF NOT EXISTS appointments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  patient_id INT UNSIGNED NOT NULL,
  doctor VARCHAR(120) NOT NULL,
  department VARCHAR(120) NOT NULL,
  appointment_date DATE NOT NULL,
  appointment_time TIME NOT NULL,
  status ENUM('Scheduled','Confirmed','Completed','Cancelled') DEFAULT 'Scheduled',
  notes TEXT,
  created_by INT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_appointments_patient FOREIGN KEY (patient_id)
    REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT fk_appointments_created_by FOREIGN KEY (created_by)
    REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Billing
CREATE TABLE IF NOT EXISTS bills (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  patient_id INT UNSIGNED NOT NULL,
  service_type VARCHAR(120) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_method ENUM('Cash','MPesa','Card','Insurance','Other') NOT NULL,
  status ENUM('Pending','Paid','Partial') DEFAULT 'Pending',
  billing_date DATE NOT NULL,
  description TEXT,
  created_by INT UNSIGNED DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bills_patient FOREIGN KEY (patient_id)
    REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT fk_bills_created_by FOREIGN KEY (created_by)
    REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- User preferences (settings section)
CREATE TABLE IF NOT EXISTS user_preferences (
  user_id INT UNSIGNED PRIMARY KEY,
  email_notifications TINYINT(1) NOT NULL DEFAULT 1,
  sms_notifications TINYINT(1) NOT NULL DEFAULT 0,
  auto_backup TINYINT(1) NOT NULL DEFAULT 1,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_preferences_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed demo receptionist (replace password_hash with a real hash before using in production)
INSERT INTO users (full_name, username, password_hash, email, phone)
VALUES (
  'Demo Receptionist',
  'demo',
  -- hash for password "demo123" generated with PASSWORD_BCRYPT
  '$2y$10$B2pUIjG5zuhFq8T40ojYxOL7ay9n7dLTo9EUPqkAUGrkTrpn/N3nK',
  'demo@sunrisehospital.com',
  '+254700000000'
)
ON DUPLICATE KEY UPDATE username = username;

