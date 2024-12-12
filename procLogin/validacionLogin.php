<?php
session_start();

// Verificar si hay errores en la inclusión del archivo
try {
    require_once '../conexion/conexion.php';
} catch (Exception $e) {
    error_log("Error al incluir el archivo de conexión: " . $e->getMessage());
    header('Location: ../index.php?error=2');
    exit();
}

// Verificar que la conexión existe y es válida
if (!isset($conexion) || !($conexion instanceof PDO)) {
    error_log("Error: La conexión a la base de datos no está disponible");
    header('Location: ../index.php?error=2');
    exit();
}

// Verificar que se recibieron los datos del formulario
if (!isset($_POST['usuario']) || !isset($_POST['password'])) {
    error_log("Error: Datos de formulario incompletos");
    header('Location: ../index.php?error=1');
    exit();
}

$usuario = htmlspecialchars($_POST['usuario']);
$password = htmlspecialchars($_POST['password']);

try {
    error_log("Intento de login - Usuario: " . $usuario);
    
    $sql = "SELECT u.user_id, u.username, u.pwd, u.role_id, r.role_name 
            FROM tbl_users u 
            JOIN tbl_roles r ON u.role_id = r.role_id 
            WHERE u.username = :usuario";
            
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    error_log("Datos del usuario encontrado: " . print_r($row, true));

    if ($row && password_verify($password, $row['pwd'])) {
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['usuario'] = $row['username'];
        $_SESSION['role_id'] = $row['role_id'];
        $_SESSION['role_name'] = $row['role_name'];

        error_log("Login exitoso para usuario: " . $usuario);
        error_log("Role ID: " . $row['role_id']);
        error_log("Role Name: " . $row['role_name']);

        if ($row['role_id'] == 2) {
            header('Location: ../admin/panel.php');
        } else {
            header('Location: ../paginaPrincipal.php');
        }
        exit();
    } else {
        error_log("Login fallido para usuario: " . $usuario);
        header('Location: ../index.php?error=1');
        exit();
    }
} catch (PDOException $e) {
    error_log("Error en la base de datos: " . $e->getMessage());
    header('Location: ../index.php?error=2');
    exit();
}
?>
