
-- Create database if not exists
CREATE DATABASE IF NOT EXISTS joomla_db;

-- Use the database
USE joomla_db;

-- Create table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    age INT,
    faculty VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert sample data
INSERT INTO users (name, description, age, faculty) VALUES
('Ion Popescu', 'Student la Informatică', 21, 'Facultatea de Matematică și Informatică'),
('Maria Ionescu', 'Doctorandă în Biologie', 28, 'Facultatea de Biologie'),
('Alexandru Georgescu', 'Profesor asistent', 35, 'Facultatea de Istorie'),
('Elena Dumitrescu', 'Studentă la Drept', 20, 'Facultatea de Drept');

-- Create user for Joomla with mysql_native_password authentication
CREATE USER IF NOT EXISTS 'joomla_user'@'%' IDENTIFIED WITH mysql_native_password BY 'joomla_pass';

-- Grant privileges to the Joomla user
GRANT ALL PRIVILEGES ON joomla_db.* TO 'joomla_user'@'%' WITH GRANT OPTION;

-- Ensure the user can connect from any host
ALTER USER 'joomla_user'@'%' IDENTIFIED WITH mysql_native_password BY 'joomla_pass';

-- Flush privileges to ensure changes take effect
FLUSH PRIVILEGES;