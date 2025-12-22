<?php
// Function to format bytes into a human-readable format
function format_bytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Function to convert a limit value and unit to bytes
function convert_to_bytes($limit_value, $limit_unit) {
    $daily_limit = 0;

    if (!empty($limit_value)) {
        switch ($limit_unit) {
            case 'KB':
                $daily_limit = $limit_value * 1024;
                break;
            case 'MB':
                $daily_limit = $limit_value * 1024 * 1024;
                break;
            case 'GB':
                $daily_limit = $limit_value * 1024 * 1024 * 1024;
                break;
        }
    }

    return $daily_limit;
}

// Function to generate a unique 8-digit login code
function generate_unique_login_code($pdo) {
    do {
        $login_code = mt_rand(10000000, 99999999);
        $stmt = $pdo->prepare('SELECT id FROM users WHERE login_code = :login_code');
        $stmt->bindParam(':login_code', $login_code, PDO::PARAM_INT);
        $stmt->execute();
    } while ($stmt->rowCount() > 0);

    return $login_code;
}

// Function to get the base URL
function get_base_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    $path = dirname($script_name);
    // Ensure the path ends with a slash
    if (substr($path, -1) !== '/') {
        $path .= '/';
    }
    return $protocol . $host . $path;
}

function get_setting($pdo, $name) {
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE name = :name');
    $stmt->execute(['name' => $name]);
    $result = $stmt->fetch();
    return $result ? $result['value'] : null;
}

function update_setting($pdo, $name, $value) {
    // Check if the setting already exists
    $stmt_check = $pdo->prepare('SELECT id FROM settings WHERE name = :name');
    $stmt_check->execute(['name' => $name]);

    if ($stmt_check->rowCount() > 0) {
        // Update existing setting
        $stmt_update = $pdo->prepare('UPDATE settings SET value = :value WHERE name = :name');
        return $stmt_update->execute(['name' => $name, 'value' => $value]);
    } else {
        // Insert new setting
        $stmt_insert = $pdo->prepare('INSERT INTO settings (name, value) VALUES (:name, :value)');
        return $stmt_insert->execute(['name' => $name, 'value' => $value]);
    }
}

// Global variable to hold translations
$translations = [];

// Function to load language translations
function load_language($pdo) {
    global $translations;

    // Define an allow-list of available languages
    $allowed_langs = ['en', 'es', 'fr', 'de', 'fil', 'zh'];

    // Get language setting from the database, default to 'en'
    $lang = get_setting($pdo, 'language');
    if (!$lang || !in_array($lang, $allowed_langs)) {
        $lang = 'en';
    }

    // Define the path to the language file
    $lang_file = 'languages/' . $lang . '.php';

    // Default to English if the language file doesn't exist (should not happen with the check above)
    if (!file_exists($lang_file)) {
        $lang_file = 'languages/en.php';
    }

    // Include the language file
    if (file_exists($lang_file)) {
        $translations = require $lang_file;
    }
}

// Function to get a translated string
function translate($key) {
    global $translations;
    // Check if the key exists in the translations array
    return isset($translations[$key]) ? $translations[$key] : $key;
}
?>
