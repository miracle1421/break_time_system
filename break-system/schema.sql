CREATE DATABASE IF NOT EXISTS break_monitoring;
USE break_monitoring;

CREATE TABLE IF NOT EXISTS profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    department VARCHAR(255) NOT NULL,
    photo_data LONGBLOB NOT NULL,
    photo_type VARCHAR(50) NOT NULL,
    status ENUM('IN', 'ON_BREAK') DEFAULT 'IN',
    time_out DATETIME NULL,
    expected_in DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);