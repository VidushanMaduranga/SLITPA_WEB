-- Members table
CREATE TABLE IF NOT EXISTS members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    membership_type ENUM('regular', 'premium', 'corporate') DEFAULT 'regular',
    status ENUM('pending', 'active', 'inactive') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Member profiles table for additional information
CREATE TABLE IF NOT EXISTS member_profiles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    company_name VARCHAR(100),
    position VARCHAR(100),
    industry VARCHAR(100),
    bio TEXT,
    profile_image VARCHAR(255),
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

-- Member login history
CREATE TABLE IF NOT EXISTS member_login_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
); 