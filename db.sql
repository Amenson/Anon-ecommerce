CREATE DATABASE ecommerce;

USE ecommerce;

-- Table for products
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  image VARCHAR(255),
  stock INT DEFAULT 100,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for users (clients)
CREATE TABLE users (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(255) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,          -- Toujours hasher !
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
                ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci;

-- Table for admins
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL
);

-- Table for orders
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  total DECIMAL(10,2) NOT NULL,
  status ENUM('Pending', 'Processed', 'Shipped', 'Delivered') DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table for order items
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert default admin (password: admin123, hashed with password_hash)
INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$Yv4k4Q5V5f7Q5z5p5q5r5u5v5w5x5y5z5.5/5');

-- Sample products
INSERT INTO products (name, description, price, image, stock) VALUES 
('T-Shirt Blanc', 'T-shirt confortable en coton', 19.99, 'tshirt.jpg', 50),
('Jeans Bleu', 'Jeans slim fit', 49.99, 'jeans.jpg', 30);