<?php

// DIAGNÓSTICO - quitar después
/*echo "<pre>";
echo "HOST: " . ($_ENV['DB_HOST'] ?? 'NO DEFINIDO') . "\n";
echo "PORT: " . ($_ENV['DB_PORT'] ?? 'NO DEFINIDO') . "\n";
echo "USER: " . ($_ENV['DB_USER'] ?? 'NO DEFINIDO') . "\n";
echo "PASS: " . ($_ENV['DB_PASS'] ?? 'NO DEFINIDO') . "\n";
echo "NAME: " . ($_ENV['DB_NAME'] ?? 'NO DEFINIDO') . "\n";
echo "</pre>";
*/
// FIN DIAGNÓSTICO

try {
    $host = $_ENV['DB_HOST'] ?? $_ENV['DB_SERVER'] ?? 'db';
    $port = $_ENV['DB_PORT'] ?? '3306';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    $database = $_ENV['DB_NAME'] ?? '';

    $db = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("SET NAMES utf8mb4");
    $db->exec("SET CHARACTER SET utf8mb4");
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        "detalle" => $e->getMessage(),
        "mensaje" => "Error de conexión a la base de datos",
        "codigo" => 5
    ]);
    exit;
}
