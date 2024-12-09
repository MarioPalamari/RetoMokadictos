<?php
session_start();
include_once '../conexion/conexion.php';

$usuario = $_POST['usuario'];
$password = $_POST['password'];
$_SESSION['usuario'] = $usuario;

try {
    // Consulta para obtener el nombre de usuario, contraseña y rol
    $sql = "SELECT username, pwd, role_id FROM tbl_users WHERE username = :usuario";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si el usuario existe y la contraseña es correcta
    if ($row && password_verify($password, $row['pwd'])) {
        $_SESSION['username'] = $row['username'];

        // Verificar el rol del usuario
        $sql_role = "SELECT role_name FROM tbl_roles WHERE role_id = :role_id";
        $stmt_role = $conexion->prepare($sql_role);
        $stmt_role->bindParam(':role_id', $row['role_id'], PDO::PARAM_INT);
        $stmt_role->execute();
        $role_data = $stmt_role->fetch(PDO::FETCH_ASSOC);

        if ($role_data['role_name'] === 'Administrador') {
            header('Location: ../admin/historial.php'); // Página de bienvenida del admin
            exit();
        } else {
            header('Location: ../paginaPrincipal.php'); // Página principal para usuarios normales
            exit();
        }
    } else {
        // Redirigir al login con error si la autenticación falla
        header('Location: ../index.php?error=1');
        exit();
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
