<?php

$envPath = __DIR__ . '/../.env';

if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
    
    define('DB_HOST', $env['DB_HOST']);
    define('DB_NAME', $env['DB_NAME']);
    define('DB_USER', $env['DB_USER']);
    define('DB_PASS', $env['DB_PASS']);
} else {
    die("El archivo .env no existe.");
}
?>