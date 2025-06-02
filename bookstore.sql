-- Create database
DROP DATABASE IF EXISTS bookstore;
CREATE DATABASE bookstore;
USE bookstore;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Books table
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    author VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    image VARCHAR(255)
);

-- Cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (book_id) REFERENCES books(id)
);

-- Sample books
INSERT INTO books (title, author, price, description, image) VALUES
('Girls of Riyadh', 'Rajaa Al-Sanea', 14.25, 'Groundbreaking novel about the lives of young Saudi women.', 'girls_riyadh.jpg'),
('The Arch and the Butterfly', 'Mohammed Hasan Alwan', 13.99, 'Award-winning novel about a Saudi man searching for meaning.', 'arch_butterfly.jpg'),
('Throwing Sparks', 'Abdo Khal', 17.50, 'International Prize for Arabic Fiction winner about power and corruption in Jeddah.', 'throwing_sparks.jpg'),
('My Thousand and One Nights', 'Raja Alem', 12.75, 'A mystical journey through Mecca by a Saudi female author.', 'thousand_nights.jpg'),
('The Great Gatsby', 'F. Scott Fitzgerald', 10.99, 'A classic novel about the American Dream.', 'gatsby.jpg'),
('To Kill a Mockingbird', 'Harper Lee', 12.50, 'A story of racial injustice and moral growth.', 'mockingbird.jpg'),
('Season of Migration to the North', 'Tayeb Salih', 14.99, 'A Sudanese novel exploring cultural identity and colonialism.', 'season_migration.jpg'),
('The Arabian Nights', 'Various Arab Authors', 15.50, 'Classic collection of Middle Eastern folk tales.', 'arabian_nights.jpg'),
('Cities of Salt', 'Abdul Rahman Munif', 13.75, 'A Saudi epic about oil discovery and its impact on Bedouin life.', 'cities_salt.jpg'),
('The Yacoubian Building', 'Alaa Al Aswany', 11.25, 'Egyptian novel depicting life in a Cairo apartment building.', 'yacoubian.jpg'),
('Gate of the Sun', 'Elias Khoury', 12.99, 'Palestinian narrative about refugee experiences and memory.', 'gate_sun.jpg');


-- Main orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    shipping_name VARCHAR(100) NOT NULL,
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_zip VARCHAR(20) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table (stores individual products in each order)
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Order status history table (tracks changes to order status)
CREATE TABLE order_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL,
    status_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);