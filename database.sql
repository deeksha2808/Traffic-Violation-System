-- Smart Traffic Violation Detection & Management System
-- Run this in MySQL: mysql -u root -p < database.sql

CREATE DATABASE IF NOT EXISTS traffic_system;
USE traffic_system;

-- Users table (officers + owners)
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    phone      VARCHAR(15)  NOT NULL,
    user_type  ENUM('officer','owner') NOT NULL,
    password   VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vehicles table (registered vehicles)
CREATE TABLE IF NOT EXISTS vehicles (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_number VARCHAR(20)  NOT NULL UNIQUE,
    owner_name     VARCHAR(100) NOT NULL,
    owner_email    VARCHAR(150) NOT NULL
);

-- Violations / Challans table
CREATE TABLE IF NOT EXISTS violations (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_number VARCHAR(20)   NOT NULL,
    violation_type VARCHAR(100)  NOT NULL,
    location       VARCHAR(200)  NOT NULL,
    image_path     VARCHAR(255)  DEFAULT NULL,
    fine_amount    DECIMAL(10,2) NOT NULL,
    status         ENUM('Unpaid','Paid') DEFAULT 'Unpaid',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample vehicles for testing
INSERT IGNORE INTO vehicles (vehicle_number, owner_name, owner_email) VALUES
('KA01AB1234', 'Deeksha DS', 'deeksha@gmail.com'),
('MH12XY5678', 'Anvitha AV',    'anvitha@gmail.com');
