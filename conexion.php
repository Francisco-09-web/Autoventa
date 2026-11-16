<?php
// Conexión a MySQL usando PDO
$host = 'localhost';
$bd   = 'autoventa';
$user = 'root';
$pass = ''; // XAMPP por defecto no tiene contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$bd;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    exit('No se pudo conectar con la base de datos.');
}
