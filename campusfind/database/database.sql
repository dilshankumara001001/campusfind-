-- ============================================================
-- CampusFind Database
-- ============================================================

CREATE DATABASE IF NOT EXISTS campusfind;
USE campusfind;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    college VARCHAR(100),
    role ENUM('user', 'admin') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
);

-- Lost items table
CREATE TABLE lost_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    location VARCHAR(200) NOT NULL,
    date_lost DATE NOT NULL,
    image VARCHAR(255),
    status ENUM('open', 'matched', 'claimed', 'returned', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_category (category),
    INDEX idx_status (status),
    FULLTEXT INDEX idx_search (title, description, location)
);

-- Found items table
CREATE TABLE found_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    location VARCHAR(200) NOT NULL,
    date_found DATE NOT NULL,
    image VARCHAR(255),
    status ENUM('open', 'matched', 'claimed', 'returned', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_category (category),
    INDEX idx_status (status),
    FULLTEXT INDEX idx_search (title, description, location)
);

-- Claims table
CREATE TABLE claims (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lost_item_id INT NOT NULL,
    found_item_id INT NOT NULL,
    claimant_id INT NOT NULL,
    owner_id INT,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    message TEXT,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (lost_item_id) REFERENCES lost_items(id) ON DELETE CASCADE,
    FOREIGN KEY (found_item_id) REFERENCES found_items(id) ON DELETE CASCADE,
    FOREIGN KEY (claimant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    UNIQUE KEY unique_claim (lost_item_id, found_item_id)
);

-- Match log table
CREATE TABLE match_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lost_item_id INT NOT NULL,
    found_item_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    status ENUM('pending', 'notified', 'claimed', 'ignored') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lost_item_id) REFERENCES lost_items(id) ON DELETE CASCADE,
    FOREIGN KEY (found_item_id) REFERENCES found_items(id) ON DELETE CASCADE,
    INDEX idx_score (score)
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(500),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read)
);

-- Activity log table
CREATE TABLE activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_action (action),
    INDEX idx_created (created_at)
);

-- Rate limits table
CREATE TABLE rate_limits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_key VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_key (request_key(191))
);

-- Default admin user (password: admin123)
INSERT INTO users (username, email, password, phone, college, role) VALUES
('admin', 'admin@campusfind.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0712345678', 'University of Colombo', 'admin');

-- Sample users (password: password123)
INSERT INTO users (username, email, password, phone, college, role) VALUES
('amila_perera', 'amila@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0712345679', 'University of Colombo', 'user'),
('nimal_silva', 'nimal@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0712345680', 'University of Peradeniya', 'user'),
('samanthi_j', 'samanthi@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0712345681', 'University of Moratuwa', 'user');

-- Sample lost items
INSERT INTO lost_items (user_id, category, title, description, location, date_lost) VALUES
(2, 'Electronics', 'Black Samsung Galaxy S21', 'Black Samsung phone with cracked screen protector', 'Main Library, 2nd Floor', '2026-08-15'),
(2, 'Keys', 'Keychain with 3 keys', '3 keys with a bunny keychain', 'Cafeteria', '2026-08-14'),
(3, 'Books', 'Data Structures and Algorithms', 'CLRS textbook 3rd edition', 'CS Building, Room 301', '2026-08-12');

-- Sample found items
INSERT INTO found_items (user_id, category, title, description, location, date_found) VALUES
(4, 'Electronics', 'Samsung Galaxy Phone', 'Black Samsung phone with purple case', 'Main Library, 2nd Floor', '2026-08-15'),
(4, 'Keys', 'Set of keys found', '3 keys with bunny charm', 'Cafeteria', '2026-08-14'),
(2, 'Books', 'Algorithm Textbook', 'CLRS data structures book', 'CS Building, Room 302', '2026-08-13');

-- Sample claims
INSERT INTO claims (lost_item_id, found_item_id, claimant_id, owner_id, status, message) VALUES
(1, 1, 2, 4, 'pending', 'I think this might be my phone.'),
(2, 2, 2, 4, 'approved', 'These look like my keys!');