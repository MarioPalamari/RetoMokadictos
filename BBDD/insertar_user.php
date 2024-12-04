<?php
// Conectar a la base de datos
include_once('../conexion/conexion.php');

// Datos del usuario a insertar
$username = 'Admin'; 
$password = 'qweQWE123'; 
$role_id = 2;

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO tbl_users (username, pwd, role_id) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conexion, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssi", $username, $hashed_password, $role_id);
    if (mysqli_stmt_execute($stmt)) {
        echo "Usuario insertado con éxito.";
    } else {
        echo "Error al insertar el usuario: " . mysqli_error($conexion);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error en la preparación de la consulta: " . mysqli_error($conexion);
}

mysqli_close($conexion);    
?>
