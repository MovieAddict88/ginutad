<?php
// Include the database configuration file
require_once 'db_config.php';

try {
    echo "Starting database setup/migration...<br>";

    // --- Create tables if they don't exist ---

    // users table
    $sql_users = 'CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(255),
        last_name VARCHAR(255),
        contact_number VARCHAR(20),
        login_code VARCHAR(255) UNIQUE,
        device_id VARCHAR(255),
        banned BOOLEAN NOT NULL DEFAULT FALSE,
        role ENUM("admin","user") NOT NULL DEFAULT "user",
        daily_limit BIGINT UNSIGNED DEFAULT 0,
        data_usage BIGINT UNSIGNED DEFAULT 0,
        promo_id INT,
        `status` VARCHAR(20) NOT NULL DEFAULT "Unpaid",
        `payment` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )';
    $pdo->exec($sql_users);
    echo "Table 'users' checked/created successfully.<br>";

    // promos table
    $sql_promos = 'CREATE TABLE IF NOT EXISTS promos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        promo_name VARCHAR(255) NOT NULL,
        icon_promo_path VARCHAR(255) NOT NULL
    )';
    $pdo->exec($sql_promos);
    echo "Table 'promos' checked/created successfully.<br>";

    // vpn_profiles table
    $sql_vpn_profiles = 'CREATE TABLE IF NOT EXISTS vpn_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        ovpn_config TEXT NOT NULL,
        type ENUM("Premium","Freemium") NOT NULL DEFAULT "Premium",
        promo_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (promo_id) REFERENCES promos(id)
    )';
    $pdo->exec($sql_vpn_profiles);
    echo "Table 'vpn_profiles' checked/created successfully.<br>";

    // vpn_sessions table
    $sql_vpn_sessions = 'CREATE TABLE IF NOT EXISTS vpn_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        start_time DATETIME NOT NULL,
        end_time DATETIME,
        ip_address VARCHAR(255) NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )';
    $pdo->exec($sql_vpn_sessions);
    echo "Table 'vpn_sessions' checked/created successfully.<br>";

    // payments table
    $sql_payments = 'CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        payment_date DATE NOT NULL,
        payment_time TIME NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        reference_number VARCHAR(255),
        attachment_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )';
    $pdo->exec($sql_payments);
    echo "Table 'payments' checked/created successfully.<br>";

    // --- Alter tables to add missing columns ---

    // Check and add bytes_in to vpn_sessions
    $stmt_bytes_in = $pdo->query("SHOW COLUMNS FROM `vpn_sessions` LIKE 'bytes_in'");
    if ($stmt_bytes_in->rowCount() == 0) {
        $pdo->exec('ALTER TABLE vpn_sessions ADD COLUMN bytes_in BIGINT UNSIGNED DEFAULT 0');
        echo "Column 'bytes_in' added to 'vpn_sessions' table.<br>";
    }

    // Check and add bytes_out to vpn_sessions
    $stmt_bytes_out = $pdo->query("SHOW COLUMNS FROM `vpn_sessions` LIKE 'bytes_out'");
    if ($stmt_bytes_out->rowCount() == 0) {
        $pdo->exec('ALTER TABLE vpn_sessions ADD COLUMN bytes_out BIGINT UNSIGNED DEFAULT 0');
        echo "Column 'bytes_out' added to 'vpn_sessions' table.<br>";
    }

    // Check and add session_status to vpn_sessions
    $stmt_status = $pdo->query("SHOW COLUMNS FROM `vpn_sessions` LIKE 'session_status'");
    if ($stmt_status->rowCount() == 0) {
        $pdo->exec('ALTER TABLE vpn_sessions ADD COLUMN session_status VARCHAR(20) NOT NULL DEFAULT "active"');
        echo "Column 'session_status' added to 'vpn_sessions' table.<br>";
    }

    // Check and add profile_id to vpn_sessions with foreign key
    $stmt_profile_id = $pdo->query("SHOW COLUMNS FROM `vpn_sessions` LIKE 'profile_id'");
    if ($stmt_profile_id->rowCount() == 0) {
        $pdo->exec('ALTER TABLE vpn_sessions ADD COLUMN profile_id INT, ADD FOREIGN KEY (profile_id) REFERENCES vpn_profiles(id) ON DELETE SET NULL');
        echo "Column 'profile_id' with foreign key added to 'vpn_sessions' table.<br>";
    }

    // Check and add icon_path to vpn_profiles
    $stmt_icon_path = $pdo->query("SHOW COLUMNS FROM `vpn_profiles` LIKE 'icon_path'");
    if ($stmt_icon_path->rowCount() == 0) {
        $pdo->exec('ALTER TABLE vpn_profiles ADD COLUMN icon_path VARCHAR(255)');
        echo "Column 'icon_path' added to 'vpn_profiles' table.<br>";
    }

    // Check and add status to users
    $stmt_status = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'status'");
    if ($stmt_status->rowCount() > 0) {
        $pdo->exec('ALTER TABLE users MODIFY COLUMN `status` VARCHAR(20) NOT NULL DEFAULT "Unpaid"');
        echo "Column 'status' in 'users' table modified.<br>";
    } else {
        $pdo->exec('ALTER TABLE users ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT "Unpaid"');
        echo "Column 'status' added to 'users' table.<br>";
    }

    // Check and add payment to users
    $stmt_payment = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'payment'");
    if ($stmt_payment->rowCount() > 0) {
        $pdo->exec('ALTER TABLE users MODIFY COLUMN `payment` DECIMAL(10, 2) NOT NULL DEFAULT 0.00');
        echo "Column 'payment' in 'users' table modified.<br>";
    } else {
        $pdo->exec('ALTER TABLE users ADD COLUMN `payment` DECIMAL(10, 2) NOT NULL DEFAULT 0.00');
        echo "Column 'payment' added to 'users' table.<br>";
    }

    // Check and add first_name to users
    $stmt_first_name = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'first_name'");
    if ($stmt_first_name->rowCount() == 0) {
        $pdo->exec('ALTER TABLE users ADD COLUMN first_name VARCHAR(255)');
        echo "Column 'first_name' added to 'users' table.<br>";
    }

    // Check and add last_name to users
    $stmt_last_name = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'last_name'");
    if ($stmt_last_name->rowCount() == 0) {
        $pdo->exec('ALTER TABLE users ADD COLUMN last_name VARCHAR(255)');
        echo "Column 'last_name' added to 'users' table.<br>";
    }

    // Check and add contact_number to users
    $stmt_contact_number = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'contact_number'");
    if ($stmt_contact_number->rowCount() == 0) {
        $pdo->exec('ALTER TABLE users ADD COLUMN contact_number VARCHAR(20)');
        echo "Column 'contact_number' added to 'users' table.<br>";
    }

    // Check and add billing_month to users
    $stmt_billing_month = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'billing_month'");
    if ($stmt_billing_month->rowCount() > 0) {
        $pdo->exec('ALTER TABLE users MODIFY COLUMN billing_month DATE');
        echo "Column 'billing_month' in 'users' table modified to DATE.<br>";
    } else {
        $pdo->exec('ALTER TABLE users ADD COLUMN billing_month DATE');
        echo "Column 'billing_month' added to 'users' table as DATE.<br>";
    }

    // settings table
    $sql_settings = 'CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE,
        value TEXT
    )';
    $pdo->exec($sql_settings);
    echo "Table 'settings' checked/created successfully.<br>";

    // --- Seed default settings if they don't exist ---
    $stmt_setting = $pdo->prepare('SELECT id FROM settings WHERE name = :name');
    $stmt_setting->execute(['name' => 'site_name']);
    if ($stmt_setting->rowCount() === 0) {
        $insert_setting_sql = 'INSERT INTO settings (name, value) VALUES (:name, :value)';
        $stmt_insert = $pdo->prepare($insert_setting_sql);
        $stmt_insert->execute(['name' => 'site_name', 'value' => 'VPN Admin Panel']);
        echo 'Default setting "site_name" created successfully.<br>';
    } else {
        echo 'Setting "site_name" already exists.<br>';
    }

    // --- Seed site_icon setting if it doesn't exist ---
    $stmt_setting = $pdo->prepare('SELECT id FROM settings WHERE name = :name');
    $stmt_setting->execute(['name' => 'site_icon']);
    if ($stmt_setting->rowCount() === 0) {
        $insert_setting_sql = 'INSERT INTO settings (name, value) VALUES (:name, :value)';
        $stmt_insert = $pdo->prepare($insert_setting_sql);
        $stmt_insert->execute(['name' => 'site_icon', 'value' => '']);
        echo 'Default setting "site_icon" created successfully.<br>';
    } else {
        echo 'Setting "site_icon" already exists.<br>';
    }

    // --- Create default admin user if it doesn't exist ---
    $stmt_admin = $pdo->prepare('SELECT id FROM users WHERE username = :username');
    $stmt_admin->execute(['username' => 'admin']);
    if ($stmt_admin->rowCount() === 0) {
        $admin_pass = password_hash('admin', PASSWORD_DEFAULT);
        $insert_admin_sql = 'INSERT INTO users (username, password, role) VALUES (:username, :password, "admin")';
        $stmt_insert = $pdo->prepare($insert_admin_sql);
        $stmt_insert->execute(['username' => 'admin', 'password' => $admin_pass]);
        echo 'Default admin user created successfully (username: admin, password: admin).<br>';
    } else {
        echo 'Admin user already exists.<br>';
    }

    echo '<br><strong>Database setup/migration completed successfully!</strong><br>';
    echo '<a href="login.php" style="display: inline-block; padding: 10px 20px; background: #4361ee; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px;">Go to Login</a>';

} catch (PDOException $e) {
    die('ERROR: Could not execute migration. ' . $e->getMessage());
}
?>
