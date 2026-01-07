-- ====================================================================
-- Research Portal Database Schema
-- Database: research_portal
-- User: studentuser
-- Password: student123
-- ====================================================================

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS research_portal
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE research_portal;

-- ====================================================================
-- Table: users
-- Stores registered user information
-- ====================================================================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table: submissions
-- Stores research paper submissions
-- ====================================================================
CREATE TABLE IF NOT EXISTS submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    s3_file_url TEXT,
    file_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table: comments
-- Stores comments on research paper submissions
-- ====================================================================
CREATE TABLE IF NOT EXISTS comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    submission_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_submission_id (submission_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table: reviews
-- Stores peer reviews of submissions
-- ====================================================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    submission_id INT NOT NULL,
    user_id INT,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_submission_id (submission_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Table: analysis_results
-- Stores AI/ML analysis results for papers (future feature)
-- ====================================================================
CREATE TABLE IF NOT EXISTS analysis_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    submission_id INT NOT NULL,
    model_name VARCHAR(100),
    summary TEXT,
    score FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    INDEX idx_submission_id (submission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- Verify Tables Created
-- ====================================================================
SHOW TABLES;

-- ====================================================================
-- Current Database Statistics
-- ====================================================================
SELECT 
    'Database Statistics' as Info,
    (SELECT COUNT(*) FROM users) as Total_Users,
    (SELECT COUNT(*) FROM submissions) as Total_Submissions,
    (SELECT COUNT(*) FROM comments) as Total_Comments,
    (SELECT COUNT(*) FROM reviews) as Total_Reviews;
