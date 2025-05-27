-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS budget_baas;
USE budget_baas;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  is_admin BOOLEAN DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table for expense categories
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
);

-- Insert default categories
INSERT INTO categories (name) VALUES
  ('Abonnementen'),
  ('Eten'),
  ('Uitgaan'),
  ('Kleding'),
  ('Vervoer'),
  ('Wonen'),
  ('Overig');

-- Budget table to store monthly budgets
CREATE TABLE IF NOT EXISTS budgets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  amount DECIMAL(10, 2) NOT NULL,
  month INT NOT NULL,
  year INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY user_month_year (user_id, month, year)
);

-- Expenses table for individual expense entries
CREATE TABLE IF NOT EXISTS expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  category_id INT NOT NULL,
  title VARCHAR(100) NOT NULL,
  amount DECIMAL(10, 2) NOT NULL,
  description TEXT,
  expense_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

-- Create a default admin user (username: admin, password: admin123)
INSERT INTO users (username, email, password, is_admin) 
VALUES ('admin', 'admin@budgetbaas.nl', 'admin123', 1);

-- Create a sample regular user (username: gebruiker, password: gebruiker123)
INSERT INTO users (username, email, password, is_admin) 
VALUES ('gebruiker', 'gebruiker@example.com', 'gebruiker123', 0); 