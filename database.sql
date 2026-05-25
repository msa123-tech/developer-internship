-- MySQL database for user login credentials only
-- Run this in phpMyAdmin or: mysql -u root < database.sql

CREATE DATABASE IF NOT EXISTS arjun_task_db;
USE arjun_task_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
