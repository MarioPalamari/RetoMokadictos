<?php
session_start();
include_once '../conexion/conexion.php';

$usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
$password = mysqli_real_escape_string($conexion, $_POST['password']);
$_SESSION['usuario'] = $usuario;

try {
    // Consulta para obtener el nombre de usuario, contraseña y rol
    $sql = "SELECT username, pwd, role_id 
            FROM tbl_users 
            WHERE username = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $usuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // Si el usuario existe y la contraseña es correcta
    if ($row && password_verify($password, $row['pwd'])) {
        $_SESSION['username'] = $row['username'];

        // Verificar el rol del usuario
        $sql_role = "SELECT role_name FROM tbl_roles WHERE role_id = ?";
        $stmt_role = mysqli_prepare($conexion, $sql_role);
        mysqli_stmt_bind_param($stmt_role, "i", $row['role_id']);
        mysqli_stmt_execute($stmt_role);
        $result_role = mysqli_stmt_get_result($stmt_role);
        $role_data = mysqli_fetch_assoc($result_role);
        mysqli_stmt_close($stmt_role);

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
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
