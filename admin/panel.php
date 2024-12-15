<?php
session_start();

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] !== 'Admin') {
    header('Location: ../index.php?error=2');
    exit();
}

include_once('../conexion/conexion.php');

if (!isset($conexion)) {
    die("Error: La conexión a la base de datos no se estableció.");
}

// Determinar qué sección mostrar
$seccion = isset($_GET['seccion']) ? $_GET['seccion'] : 'usuarios';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            padding: 0;
            margin: 0;
            min-height: 100vh;
            display: block;
            background-color: #d9a875;
        }

        .admin-container {
            background-color: #a67c52;
            padding: 20px;
            margin: 0;
            color: white;
            min-height: 100vh;
            width: 100%;
        }

        .filter-section {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .filter-section input,
        .filter-section select {
            background-color: rgba(255, 255, 255, 0.9);
            border: none;
            padding: 8px;
        }

        .filter-section .btn {
            margin-right: 10px;
        }

        .table {
            width: 100%;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            margin-top: 20px;
        }

        .table th {
            background-color: #3b2c24;
            color: white;
            padding: 15px;
            border: none;
        }

        .table td {
            padding: 12px;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: none !important;
        }

        .table td form {
            background: none !important;
        }

        .btn-group {
            background: none !important;
        }

        .d-flex {
            background: none !important;
        }

        .btn-danger, .btn-primary {
            background-color: var(--bs-danger) !important;
            border: none;
        }

        .btn-primary {
            background-color: var(--bs-primary) !important;
        }

        /* Ajustar anchos de columnas */
        .table th:nth-child(1), .table td:nth-child(1) { width: 5%; }  /* ID */
        .table th:nth-child(2), .table td:nth-child(2) { width: 20%; } /* Username */
        .table th:nth-child(3), .table td:nth-child(3) { width: 20%; } /* Nombre */
        .table th:nth-child(4), .table td:nth-child(4) { width: 20%; } /* Apellidos */
        .table th:nth-child(5), .table td:nth-child(5) { width: 20%; } /* Role */
        .table th:nth-child(6), .table td:nth-child(6) { width: 15%; } /* Actions */

        .btn-sm {
            padding: 5px 10px;
            margin: 0 2px;
        }

        .nav-tabs {
            border-bottom: none;
        }

        .nav-tabs .nav-link {
            color: #FBFFEB;
            background-color: rgba(59, 44, 36, 0.7);
            border: none;
            margin-right: 5px;
            border-radius: 8px 8px 0 0;
            padding: 10px 20px;
        }

        .nav-tabs .nav-link:hover {
            background-color: rgba(59, 44, 36, 0.9);
            color: #FBFFEB;
            border: none;
        }

        .nav-tabs .nav-link.active {
            background-color: #3b2c24;
            color: #FBFFEB;
            border: none;
        }

        .crud-section {
            display: none;
        }

        .crud-section.active {
            display: block;
        }

        .tab-content {
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 0 8px 8px 8px;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Panel de Administración</h1>
            <a href="../cerrarSesion/logout.php" class="btn btn-danger">Cerrar Sesión</a>
        </div>

        <!-- Menú de navegación -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion == 'usuarios' ? 'active' : ''; ?>" 
                   href="?seccion=usuarios">Usuarios</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion == 'salas' ? 'active' : ''; ?>" 
                   href="?seccion=salas">Salas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion == 'mesas' ? 'active' : ''; ?>" 
                   href="?seccion=mesas">Mesas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion == 'reservas' ? 'active' : ''; ?>" 
                   href="?seccion=reservas">Reservas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $seccion == 'historial' ? 'active' : ''; ?>" 
                   href="?seccion=historial">Historial</a>
            </li>
        </ul>

        <!-- Contenido -->
        <div class="content-section">
            <?php
            switch($seccion) {
                case 'usuarios':
                    include 'secciones/usuarios.php';
                    break;
                case 'salas':
                    include 'secciones/salas.php';
                    break;
                case 'mesas':
                    include 'secciones/mesas.php';
                    break;
                case 'reservas':
                    include 'secciones/reservas.php';
                    break;
                case 'historial':
                    include 'secciones/historial.php';
                    break;
                default:
                    include 'secciones/usuarios.php';
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/validarform.js"></script>
    <script src="./js/confirmaciones.js"></script>
</body>
</html>