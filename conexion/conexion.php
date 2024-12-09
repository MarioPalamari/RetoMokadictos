<?php
$dbServer = "localhost";
$dbUser = "root";
$dbPsswd = "qweQWE123";
$dbName = "db_mokadictos";

try {
    $conexion = new PDO("mysql:host=$dbServer;dbname=$dbName", $dbUser, $dbPsswd);
    // Establecer el modo de errores de PDO
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>
