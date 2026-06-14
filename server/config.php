<?php
// config.php - ตั้งค่าการเชื่อมต่อฐานข้อมูล
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lab_system');

define('SERVER_IP', '192.168.0.53');
define('SESSION_TIMEOUT', 30); // นาที - ถ้าไม่มีการใช้งานจะ Shutdown

// เชื่อมต่อ DB
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER, DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
    return $pdo;
}

// ตรวจสอบ Session Admin
function requireLogin() {
    session_start();
    if (!isset($_SESSION['admin_id'])) {
        header('Location: /admin/index.php');
        exit;
    }
}

function isLoggedIn() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['admin_id']);
}
