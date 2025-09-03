-- Create database (run separately if needed)
CREATE DATABASE IF NOT EXISTS patronix_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE patronix_db;

-- Talents (Users)
CREATE TABLE talents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_expiry DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Videos uploaded by talents
CREATE TABLE videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    filename VARCHAR(255) NOT NULL,
    views INT DEFAULT 0,
    likes INT DEFAULT 0,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Donation history
CREATE TABLE donation_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- user_id INT NOT NULL,      -- talent user id receiving the donation
    video_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','success','failed') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
);

-- Admin users
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('fan', 'talent') NOT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_expiry DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sample insert for admin user (password = admin123, hashed with PHP password_hash)
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$e0NRWw3pj5KZNmCrVZnlXOj4w7cnv9HFFLqTjoYvYpqntRDf8Q6i6');

ALTER TABLE donation_history ADD COLUMN fan_id INT;
ALTER TABLE donation_history ADD COLUMN talent_id INT;
ALTER TABLE donation_history ADD COLUMN phone VARCHAR(20) NOT NULL;
ALTER TABLE donation_history ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE donation_history ADD COLUMN transaction_id  VARCHAR(255) NULL;

ALTER TABLE videos ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE videos DROP FOREIGN KEY videos_ibfk_1;
ALTER TABLE videos ADD CONSTRAINT fk_videos_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
-- Indexes to speed up queries
CREATE INDEX idx_videos_user_id ON videos(user_id);
-- CREATE INDEX idx_donations_user_id ON donation_history(user_id);
CREATE INDEX idx_donations_video_id ON donation_history(video_id);