<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername", $username, $password);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Setting up Budget Baas Database</h2>";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS budget_baas";
    $conn->exec($sql);
    echo "<p>Database created successfully or already exists</p>";
    
    // Connect to the budget_baas database
    $conn = new PDO("mysql:host=$servername;dbname=budget_baas", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Drop users table if it exists (to ensure we have the correct structure)
    $conn->exec("DROP TABLE IF EXISTS expenses");
    $conn->exec("DROP TABLE IF EXISTS budgets");
    $conn->exec("DROP TABLE IF EXISTS users");
    echo "<p>Cleaned up old tables</p>";
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        is_admin BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "<p>Users table created successfully</p>";
    
    // Create categories table
    $sql = "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE
    )";
    $conn->exec($sql);
    echo "<p>Categories table created successfully</p>";
    
    // Check if categories are already present
    $stmt = $conn->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    $categoryCount = $stmt->fetchColumn();
    
    if ($categoryCount == 0) {
        // Insert default categories
        $sql = "INSERT INTO categories (name) VALUES
            ('Abonnementen'),
            ('Eten'),
            ('Uitgaan'),
            ('Kleding'),
            ('Vervoer'),
            ('Wonen'),
            ('Overig')";
        $conn->exec($sql);
        echo "<p>Default categories added successfully</p>";
    }
    
    // Create budget table
    $sql = "CREATE TABLE IF NOT EXISTS budgets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        month INT NOT NULL,
        year INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY user_month_year (user_id, month, year)
    )";
    $conn->exec($sql);
    echo "<p>Budgets table created successfully</p>";
    
    // Create expenses table
    $sql = "CREATE TABLE IF NOT EXISTS expenses (
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
    )";
    $conn->exec($sql);
    echo "<p>Expenses table created successfully</p>";
    
    // Check if default admin exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetchColumn();
    
    if (!$adminExists) {
        // Create a default admin user
        $sql = "INSERT INTO users (username, email, password, is_admin) 
                VALUES ('admin', 'admin@budgetbaas.nl', 'admin123', 1)";
        $conn->exec($sql);
        echo "<p>Default admin user created successfully</p>";
    }
    
    // Check if sample user exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = 'gebruiker'");
    $stmt->execute();
    $userExists = $stmt->fetchColumn();
    
    if (!$userExists) {
        // Create a sample user
        $sql = "INSERT INTO users (username, email, password, is_admin) 
                VALUES ('gebruiker', 'gebruiker@example.com', 'gebruiker123', 0)";
        $conn->exec($sql);
        echo "<p>Sample regular user created successfully</p>";
    }
    
    echo "<h3>Database setup completed successfully!</h3>";
    echo "<p>You can now <a href='homepage.php'>go to the homepage</a> and log in with:</p>";
    echo "<ul>
            <li>Username: <strong>admin</strong>, Password: <strong>admin123</strong> (Administrator)</li>
            <li>Username: <strong>gebruiker</strong>, Password: <strong>gebruiker123</strong> (Regular user)</li>
          </ul>";
    
} catch(PDOException $e) {
    echo "<div style='color:red'><h3>Error:</h3>" . $e->getMessage() . "</div>";
}

$conn = null;
?> 