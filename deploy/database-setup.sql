-- MySQL Database Setup for Chore Tracker
-- Run this as root or with sufficient privileges

-- Create database
CREATE DATABASE IF NOT EXISTS chore_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (replace 'your_secure_password' with a strong password)
CREATE USER IF NOT EXISTS 'chore_user'@'localhost' IDENTIFIED BY 'your_secure_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON chore_tracker.* TO 'chore_user'@'localhost';

-- If you need remote access (not recommended for production)
-- CREATE USER IF NOT EXISTS 'chore_user'@'%' IDENTIFIED BY 'your_secure_password';
-- GRANT ALL PRIVILEGES ON chore_tracker.* TO 'chore_user'@'%';

-- Refresh privileges
FLUSH PRIVILEGES;

-- Show created database and user
SHOW DATABASES LIKE 'chore_tracker';
SELECT User, Host FROM mysql.user WHERE User = 'chore_user';

-- Optional: Set some MySQL optimizations for Laravel
SET GLOBAL innodb_file_format=barracuda;
SET GLOBAL innodb_file_per_table=on;
SET GLOBAL innodb_large_prefix=on; 