CREATE DATABASE IF NOT EXISTS reunion_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reunion_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(200) NOT NULL,
  email VARCHAR(200) NOT NULL UNIQUE,
  phone VARCHAR(50),
  batch_year YEAR,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  status ENUM('pending','success','failed') DEFAULT 'pending',
  txn_id VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (fullname, email, phone, batch_year, password_hash)
VALUES (
  'Admin User',
  'admin@admin.com',
  '01700000000',
  1994,
  '$2y$10$3q.UJLl1VrjwScG8QDx2R.oJbxQfY9W8j8fdDPLTtTuoGtIzHwE8O' -- jkl123@
);

-- Get the last inserted user id (optional if you need it for payment)
SET @last_user_id = LAST_INSERT_ID();

-- Insert a payment for that user
INSERT INTO payments (user_id, amount, status, txn_id)
VALUES (
  @last_user_id,
  500.00,
  'success',
  'TXN123456789'
);