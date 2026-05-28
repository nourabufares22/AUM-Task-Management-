-- ============================================================
-- AUM Maintenance Request System - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS aum_task_system
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE aum_task_system;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100)  NOT NULL,
    email       VARCHAR(100)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Maintenance requests table
CREATE TABLE IF NOT EXISTS requests (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id        INT UNSIGNED NOT NULL,
    building_name  VARCHAR(100) NOT NULL,
    request_title  VARCHAR(200) NOT NULL,
    category       ENUM('Maintenance','IT Support','Cleaning','Electrical','Furniture','Plumbing','Other') NOT NULL,
    priority       ENUM('Low','Medium','High') NOT NULL DEFAULT 'Low',
    location       VARCHAR(100) NOT NULL,
    description    TEXT         NOT NULL,
    image          VARCHAR(255) DEFAULT NULL,
    status         ENUM('Pending','In Progress','Completed','Rejected') NOT NULL DEFAULT 'Pending',
    created_at     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
