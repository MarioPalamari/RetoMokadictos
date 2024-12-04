<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'Admin') {
    header('Location: ../index.php?error=2');
    exit();
}

include_once('../conexion/conexion.php');

if (!isset($conexion)) {
    die("Error: La conexión a la base de datos no se estableció.");
}

// Eliminar todas las ocupaciones de la tabla tbl_occupations
$sql = "DELETE FROM tbl_occupations";

if ($conexion->query($sql) === TRUE) {
    // Redirigir con mensaje de éxito
    header('Location: historial.php?mensaje=historial_eliminado');
} else {
    // Redirigir con mensaje de error
    header('Location: historial.php?mensaje=error_al_eliminar');
}

$conexion->close();
?>
