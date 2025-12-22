<?php
// Include the database configuration file
require_once 'db_config.php';

try {
// Create app_updates table
$sql_app_updates = 'CREATE TABLE IF NOT EXISTS app_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version_code VARCHAR(50) NOT NULL,
    version_name VARCHAR(50) NOT NULL,
    apk_path VARCHAR(255) NOT NULL,
    file_size BIGINT,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)';
$pdo->exec($sql_app_updates);
echo "App updates table created successfully<br>";
    // Create the users table with role column
    $sql_users = 'CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        login_code VARCHAR(255) UNIQUE,
        device_id VARCHAR(255),
        banned BOOLEAN NOT NULL DEFAULT FALSE,
        role ENUM("admin","user") NOT NULL DEFAULT "user",
        daily_limit BIGINT UNSIGNED DEFAULT 0,
        data_usage BIGINT UNSIGNED DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )';
    $pdo->exec($sql_users);
    echo "Users table created successfully<br>";

    // Alter users table to add reseller role
    $sql_alter_users = "ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'user', 'reseller') NOT NULL DEFAULT 'user'";
    $pdo->exec($sql_alter_users);
    echo "Users table altered successfully to add reseller role<br>";

    // Create VPN sessions table
    $sql_vpn_sessions = 'CREATE TABLE IF NOT EXISTS vpn_sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        start_time DATETIME NOT NULL,
        end_time DATETIME,
        ip_address VARCHAR(255) NOT NULL,
        bytes_in BIGINT,
        bytes_out BIGINT,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )';
    $pdo->exec($sql_vpn_sessions);
    echo "VPN sessions table created successfully<br>";

    // Create the promos table
    $sql_promos = 'CREATE TABLE IF NOT EXISTS promos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        promo_name VARCHAR(255) NOT NULL,
        icon_promo_path VARCHAR(255) NOT NULL
    )';
    $pdo->exec($sql_promos);
    echo "Promos table created successfully<br>";

    // Create VPN profiles table
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
    echo "VPN profiles table created successfully<br>";


    // Add promo_id to users table if it doesn't exist
    $sql_check_promo_id_users = "SHOW COLUMNS FROM `users` LIKE 'promo_id'";
    $stmt_check_users = $pdo->prepare($sql_check_promo_id_users);
    $stmt_check_users->execute();
    if ($stmt_check_users->rowCount() == 0) {
        $sql_add_promo_id_to_users = 'ALTER TABLE users ADD COLUMN promo_id INT, ADD FOREIGN KEY (promo_id) REFERENCES promos(id)';
        $pdo->exec($sql_add_promo_id_to_users);
        echo "promo_id column added to users table successfully<br>";
    }

    // Add promo_id to vpn_profiles table if it doesn't exist
    $sql_check_promo_id_vpn_profiles = "SHOW COLUMNS FROM `vpn_profiles` LIKE 'promo_id'";
    $stmt_check_vpn_profiles = $pdo->prepare($sql_check_promo_id_vpn_profiles);
    $stmt_check_vpn_profiles->execute();
    if ($stmt_check_vpn_profiles->rowCount() == 0) {
        $sql_add_promo_id_to_vpn_profiles = 'ALTER TABLE vpn_profiles ADD COLUMN promo_id INT, ADD FOREIGN KEY (promo_id) REFERENCES promos(id)';
        $pdo->exec($sql_add_promo_id_to_vpn_profiles);
        echo "promo_id column added to vpn_profiles table successfully<br>";
    }

    // Check if the admin user already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
    $stmt->execute(['username' => 'admin']);
    
    if ($stmt->rowCount() === 0) {
        // Admin user does not exist, so create it
        $admin_user = 'admin';
        $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);

        $insert_admin_sql = 'INSERT INTO users (username, password, role, first_name, last_name, address, contact_number) VALUES (:username, :password, "admin", "Admin", "User", "N/A", "N/A")';
        $stmt = $pdo->prepare($insert_admin_sql);
        $stmt->execute([
            'username' => $admin_user,
            'password' => $admin_pass
        ]);
        echo 'Default admin user created successfully (username: admin, password: admin123).<br>';
    } else {
        echo 'Admin user already exists.<br>';
    }

    // Run database migrations
    echo "<br><strong>Starting database setup/migration...</strong><br>";
    require_once 'migrate.php';

    echo "<br><strong>Database setup/migration completed successfully!</strong><br>";
    echo '<a href="login.php" style="display: inline-block; padding: 10px 20px; background: #4361ee; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px;">Go to Login</a>';

} catch (PDOException $e) {
    die('ERROR: Could not execute sql statement. ' . $e->getMessage());
}



?>