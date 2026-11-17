<?php
// ---- Edit these to match your MySQL setup (XAMPP default shown) ----
const DB_HOST = '127.0.0.1';
const DB_NAME = 'transport';
const DB_USER = 'root';
const DB_PASS = '';

// Seat map layout: rows A-F, seats 1-8 in each row (48 seats per trip)
const SEAT_ROWS = 'ABCDEF';
const SEAT_COLS = 8;

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

set_exception_handler(function (Throwable $t) {
    error_log($t);
    out(['error' => 'Server error. Is MySQL running and is backend/config.php correct?'], 500);
});

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}
function out($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}
function input(): array {
    $j = json_decode(file_get_contents('php://input'), true);
    return is_array($j) ? $j : [];
}
function method(string $m): void {
    if ($_SERVER['REQUEST_METHOD'] !== $m) out(['error' => 'Method not allowed'], 405);
}
function current_user(): ?array {
    if (empty($_SESSION['pid'])) return null;
    $q = db()->prepare('SELECT passenger_id, first_name, last_name, email, is_admin FROM passengers WHERE passenger_id = ?');
    $q->execute([$_SESSION['pid']]);
    return $q->fetch() ?: null;
}
function require_login(): array {
    $u = current_user();
    if (!$u) out(['error' => 'Please log in.'], 401);
    return $u;
}
function require_admin(): array {
    $u = require_login();
    if (!$u['is_admin']) out(['error' => 'Admins only.'], 403);
    return $u;
}
