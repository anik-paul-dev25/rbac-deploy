
-- Create the database
CREATE DATABASE IF NOT EXISTS rbac_system;
USE rbac_system;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'contributor', 'user', 'pending') DEFAULT 'pending',
    status ENUM('approved', 'rejected', 'pending') DEFAULT 'pending',
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Roles table
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name ENUM('admin', 'editor', 'contributor', 'user') NOT NULL
);

-- Permissions table
CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

-- Role_permissions table
CREATE TABLE role_permissions (
    role_id INT,
    permission_id INT,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id),
    PRIMARY KEY (role_id, permission_id)
);

-- Notifications table
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message TEXT NOT NULL,
    recipient_role ENUM('admin', 'user') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blog_posts table
CREATE TABLE blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    user_id INT,
    post_image VARCHAR(255) DEFAULT NULL, -- Added column for post image
    FOREIGN KEY (user_id) REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert initial roles
INSERT INTO roles (name) VALUES ('admin'), ('editor'), ('contributor'), ('user');

-- Insert initial permissions
INSERT INTO permissions (name) VALUES ('manage_users'), ('edit_users'), ('manage_posts'), ('add_posts');

-- Assign permissions to roles
INSERT INTO role_permissions (role_id, permission_id) VALUES 
    (1, 1), (1, 2), (1, 3), (1, 4), -- Admin: manage_users, edit_users, manage_posts, add_posts
    (2, 2), (2, 3), (2, 4),         -- Editor: edit_users, manage_posts, add_posts
    (3, 3), (3, 4),                 -- Contributor: manage_posts, add_posts
    (4, 4);                         -- User: add_posts
