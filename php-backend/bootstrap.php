<?php
declare(strict_types=1);
ini_set('display_errors', '0');
if (!is_file(__DIR__ . '/config.php')) {
    http_response_code(503);
    exit('Server not configured. Copy config.example.php to config.php.');
}
$config = require __DIR__ . '/config.php';
if (!is_array($config)) { http_response_code(500); exit('Invalid configuration'); }
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','httponly'=>true,'samesite'=>'Lax']);
session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
function db(): PDO {
    global $config;
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'] . ';charset=utf8mb4';
        $pdo = new PDO($dsn, $config['db_user'], $config['db_password'], [
          PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES=>false
        ]);
    }
    return $pdo;
}
function logged_in(): bool { return isset($_SESSION['admin']) && $_SESSION['admin'] === true; }
function require_admin(): void { if (!logged_in()) { http_response_code(401); throw new RuntimeException('Authentication required'); } }
function csrf_ok(): bool { return isset($_POST['csrf']) && is_string($_POST['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']); }
function e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function uuid(): string {
    $s = bin2hex(random_bytes(16));
    return substr($s,0,8).'-'.substr($s,8,4).'-4'.substr($s,13,3).'-'.dechex((hexdec($s[16]) & 3) | 8).substr($s,17,3).'-'.substr($s,20,12);
}
