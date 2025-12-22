<?php
require_once 'db_config.php';

try {
    // SQL to create table
    $sql = "CREATE TABLE IF NOT EXISTS admob_ads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        ad_unit_id VARCHAR(255) NOT NULL,
        ad_type VARCHAR(255) NOT NULL DEFAULT 'banner',
        is_enabled BOOLEAN NOT NULL DEFAULT 1,
        description TEXT
    )";

    // Execute the query
    $pdo->exec($sql);

    echo "Table admob_ads created successfully.\n";

    // SQL to insert data
    $insert_sql = "INSERT INTO admob_ads (name, ad_unit_id) VALUES
        ('App Open', 'ca-app-pub-3940256099942544/9257395921'),
        ('Adaptive Banner', 'ca-app-pub-3940256099942544/9214589741'),
        ('Fixed Size Banner', 'ca-app-pub-3940256099942544/6300978111'),
        ('Interstitial', 'ca-app-pub-3940256099942544/1033173712'),
        ('Rewarded Ads', 'ca-app-pub-3940256099942544/5224354917'),
        ('Rewarded Interstitial', 'ca-app-pub-3940256099942544/5354046379'),
        ('Native', 'ca-app-pub-3940256099942544/2247696110'),
        ('Native Video', 'ca-app-pub-3940256099942544/1044960115')";

    // Execute the query
    $pdo->exec($insert_sql);

    echo "Test ad units inserted successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>