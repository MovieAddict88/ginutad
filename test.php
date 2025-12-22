<?php
// test_permissions.php
echo "<h2>Checking Permissions</h2>";

$updates_dir = __DIR__ . '/updates';
$parent_dir = __DIR__;

echo "Current directory: " . __DIR__ . "<br>";
echo "Updates directory path: " . $updates_dir . "<br>";
echo "Updates directory exists: " . (file_exists($updates_dir) ? 'YES' : 'NO') . "<br>";
echo "Parent directory writable: " . (is_writable($parent_dir) ? 'YES' : 'NO') . "<br>";

if (!file_exists($updates_dir)) {
    echo "Attempting to create updates directory...<br>";
    if (mkdir($updates_dir, 0755, true)) {
        echo "✅ Directory created successfully!<br>";
        echo "Setting permissions...<br>";
        chmod($updates_dir, 0755);
        echo "Directory is now writable: " . (is_writable($updates_dir) ? 'YES' : 'NO') . "<br>";
    } else {
        echo "❌ Failed to create directory!<br>";
        echo "Check parent directory permissions.<br>";
    }
} else {
    echo "Directory writable: " . (is_writable($updates_dir) ? 'YES' : 'NO') . "<br>";
}

echo "<h2>PHP Upload Settings</h2>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "file_uploads: " . ini_get('file_uploads') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . "<br>";

echo "<h2>Create Test File</h2>";
$test_file = $updates_dir . '/test.txt';
if (file_put_contents($test_file, 'Test content')) {
    echo "✅ Successfully created test file!<br>";
    echo "File size: " . filesize($test_file) . " bytes<br>";
    unlink($test_file);
} else {
    echo "❌ Failed to create test file!<br>";
}
?>