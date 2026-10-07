<?php
// Break Monitoring Database Configuration
$host     = 'localhost';
$db_break = 'break_monitoring';
$user     = 'root';
$pass     = '';

// Live Queue Database Configuration
$db_live  = 'onelivesched';

try {
    // Connection 1: Break System DB
    $pdo = new PDO("mysql:host=$host;dbname=$db_break;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Connection 2: Live Queue DB
    $pdo_live = new PDO("mysql:host=$host;dbname=$db_live;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("<div style='color:red; font-family:sans-serif; padding:20px; text-align:center;'>
            <h2>Database Connection Failed!</h2>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
         </div>");
}
?>