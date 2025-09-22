<?php

$host = 'opal18.opalstack.com';
$dbname = 'flores_db';
$username = 'jiory';
$password = '3fwPqEHLOwWT680';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$createTable = "
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    text TEXT NOT NULL,
    color VARCHAR(20) NOT NULL,
    rotation INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$pdo->exec($createTable);
?>
