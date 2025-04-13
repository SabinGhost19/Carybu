-- MySQL 8 Initialization Script

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS joomla_db;

-- Use the database
USE joomla_db;

-- Create basic Joomla session table
CREATE TABLE IF NOT EXISTS `j1ma3_session` (
  `session_id` varbinary(192) NOT NULL,
  `client_id` tinyint(3) unsigned DEFAULT NULL,
  `guest` tinyint(3) unsigned DEFAULT '1',
  `time` int(11) NOT NULL DEFAULT '0',
  `data` mediumtext,
  `userid` int(11) DEFAULT '0',
  `username` varchar(150) DEFAULT '',
  PRIMARY KEY (`session_id`),
  KEY `userid` (`userid`),
  KEY `time` (`time`),
  KEY `client_id_guest` (`client_id`,`guest`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create essential Joomla tables
CREATE TABLE IF NOT EXISTS `j1ma3_extensions` (
  `extension_id` int NOT NULL AUTO_INCREMENT,
  `package_id` int NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `element` varchar(100) NOT NULL,
  `changelogurl` text,
  `folder` varchar(100) NOT NULL,
  `client_id` tinyint NOT NULL,
  `enabled` tinyint NOT NULL DEFAULT '0',
  `access` int unsigned NOT NULL DEFAULT '1',
  `protected` tinyint NOT NULL DEFAULT '0',
  `locked` tinyint NOT NULL DEFAULT '0',
  `manifest_cache` text NOT NULL,
  `params` text NOT NULL,
  `custom_data` text NOT NULL,
  `checked_out` int unsigned,
  `checked_out_time` datetime,
  `ordering` int DEFAULT '0',
  `state` int DEFAULT '0',
  `note` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`extension_id`),
  KEY `element_clientid` (`element`,`client_id`),
  KEY `element_folder_clientid` (`element`,`folder`,`client_id`),
  KEY `extension` (`type`,`element`,`folder`,`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create your custom users table
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
GRANT ALL PRIVILEGES ON joomla_db.* TO 'joomla_user'@'%';

-- Ensure the user can connect from any host
ALTER USER 'joomla_user'@'%' IDENTIFIED WITH mysql_native_password BY 'joomla_pass';

-- Flush privileges to ensure changes take effect
FLUSH PRIVILEGES;