<?php
// Load website name from config.json
$config_json = file_get_contents('config.json');
$config = json_decode($config_json, true);
$website_name = $config['website_name'];
?>
