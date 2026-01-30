<?php
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $envPath = __DIR__ . '/../../.env';
    if (file_exists($envPath)) {
        $_ENV = parse_ini_file($envPath);
    } else {
        die("Environment file not found at: " . $envPath);
    }
?>
