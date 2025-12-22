<?php
// Simple test to verify upload works
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<pre>';
    echo 'POST data: ';
    print_r($_POST);
    echo 'FILES data: ';
    print_r($_FILES);
    echo '</pre>';
    
    if (isset($_FILES['app_apk'])) {
        echo 'File name: ' . $_FILES['app_apk']['name'] . '<br>';
        echo 'File size: ' . $_FILES['app_apk']['size'] . ' bytes<br>';
        echo 'Temp file: ' . $_FILES['app_apk']['tmp_name'] . '<br>';
        echo 'Error code: ' . $_FILES['app_apk']['error'] . '<br>';
        
        if ($_FILES['app_apk']['error'] === UPLOAD_ERR_OK) {
            echo '✅ File upload was successful!<br>';
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Simple Test</title></head>
<body>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="app_version_code" value="100"><br>
        <input type="file" name="app_apk"><br>
        <button type="submit">Test Upload</button>
    </form>
</body>
</html>