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

// Filtros de búsqueda
$salaFiltro = isset($_POST['sala']) ? $_POST['sala'] : '';
$estadoFiltro = isset($_POST['estado']) ? $_POST['estado'] : '';
$usuarioFiltro = isset($_POST['usuario']) ? $_POST['usuario'] : '';
$fechaFiltro = isset($_POST['fecha_ocupacion']) ? $_POST['fecha_ocupacion'] : '';

// Consulta dinámica con filtros
$sql = "
    SELECT 
        tbl_tables.table_id,
        tbl_tables.table_number,
        tbl_rooms.name AS room_name,
        tbl_tables.status,
        tbl_occupations.start_time,
        tbl_occupations.end_time,
        tbl_users.username
    FROM 
        tbl_tables
    INNER JOIN 
        tbl_rooms ON tbl_tables.room_id = tbl_rooms.room_id
    INNER JOIN 
        tbl_occupations ON tbl_tables.table_id = tbl_occupations.table_id
    INNER JOIN 
        tbl_users ON tbl_occupations.user_id = tbl_users.user_id
    WHERE 
        1=1 ";

if ($salaFiltro != '') {
    // Modificado a LIKE para permitir coincidencias parciales
    $sql .= " AND tbl_rooms.name LIKE '%" . $conexion->real_escape_string($salaFiltro) . "%' ";
}
if ($estadoFiltro != '') {
    $sql .= " AND tbl_tables.status = '" . $conexion->real_escape_string($estadoFiltro) . "' ";
}
if ($usuarioFiltro != '') {
    $sql .= " AND tbl_users.username LIKE '%" . $conexion->real_escape_string($usuarioFiltro) . "%' ";
}
if ($fechaFiltro != '') {
    $sql .= " AND DATE(tbl_occupations.start_time) = '" . $conexion->real_escape_string($fechaFiltro) . "' ";
}

$sql .= " ORDER BY tbl_occupations.start_time DESC";

// Ejecución de la consulta
$result = $conexion->query($sql);

// Obtener los usuarios de la base de datos
$usuariosSql = "SELECT username FROM tbl_users";
$usuariosResult = $conexion->query($usuariosSql);
$usuarios = [];
if ($usuariosResult->num_rows > 0) {
    while ($row = $usuariosResult->fetch_assoc()) {
        $usuarios[] = $row['username'];
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <div class="containerHistorial">
    <h1 class="text-center">Panel de Administración - Historial y Estado de Salas</h1>
        <!-- Filtros de Búsqueda -->
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label for="sala" class="texto-historial">Sala</label>
                    <select name="sala" id="sala" class="form-control">
                        <option value="">Filtrar por sala</option>
                        <option value="Terraza" <?= $salaFiltro == 'Terraza' ? 'selected' : '' ?>>Terraza</option>
                        <option value="Salón" <?= $salaFiltro == 'Salón' ? 'selected' : '' ?>>Salón</option>
                        <option value="VIP" <?= $salaFiltro == 'VIP' ? 'selected' : '' ?>>VIP</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="estado" class="texto-historial">Estado</label>
                    <select name="estado" id="estado" class="form-control">
                        <option value="">Filtrar por estado</option>
                        <option value="occupied" <?= $estadoFiltro == 'occupied' ? 'selected' : '' ?>>Ocupada</option>
                        <option value="free" <?= $estadoFiltro == 'free' ? 'selected' : '' ?>>Libre</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="usuario" class="texto-historial">Usuario</label>
                    <select name="usuario" id="usuario" class="form-control">
                        <option value="">Filtrar por Usuario</option>
                        <?php
                        foreach ($usuarios as $usuario) {
                            echo "<option value='" . htmlspecialchars($usuario) . "' " . ($usuarioFiltro == $usuario ? 'selected' : '') . ">" . htmlspecialchars($usuario) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fecha_ocupacion" class="texto-historial">Fecha de Ocupación</label>
                    <input type="date" class="form-control" name="fecha_ocupacion" id="fecha_ocupacion" value="<?= htmlspecialchars($fechaFiltro) ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Filtrar</button>
        </form>

        <!-- Tabla de Resultados -->
            <?php
            if ($result->num_rows > 0) {
                echo "<table class='tabla tabla-bordered tabla-striped'>";
                echo "<thead class='thead-dark'>
                        <tr>
                            <th>Número de Mesa</th>
                            <th>Sala</th>
                            <th>Estado</th>
                            <th>Fecha Ocupación</th>
                            <th>Fecha Liberación</th>
                            <th>Usuario</th>
                        </tr>
                      </thead>";
                echo "<tbody>";
                
                // Mostrar resultados de la consulta con un bucle for
                $rows = $result->fetch_all(MYSQLI_ASSOC);
                $rowCount = count($rows);
                
                for ($i = 0; $i < $rowCount; $i++) {
                    $estadoClase = $rows[$i]["status"] == "occupied" ? "table-danger" : "table-success";
                    echo "<tr class='{$estadoClase}'>
                            <td>" . $rows[$i]["table_id"] . "</td>
                            <td>" . $rows[$i]["room_name"] . "</td>
                            <td>" . ucfirst($rows[$i]["status"]) . "</td>
                            <td>" . ($rows[$i]["start_time"] ?: "N/A") . "</td>
                            <td>" . ($rows[$i]["end_time"] ? $rows[$i]["end_time"] : "Ocupada actualmente") . "</td>
                            <td>" . $rows[$i]["username"] . "</td>
                          </tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p class='text-center text-warning'>No hay resultados que coincidan con los filtros aplicados.</p>";
            }
            $conexion->close();
            ?>
        <br>
        <div class="text-right mb-3">
            <form id="eliminarHistorialForm" action="eliminar_historial.php" method="post">
                <button type="button" class="btn btn-danger" onclick="confirmarEliminacion()">Eliminar Historial de Ocupaciones</button>
            </form>
        </div>
        <button class="logout-button" onclick="logout()">Cerrar Sesión</button>
        <!-- Script para la confirmación con SweetAlert -->
        <script>
        function confirmarEliminacion(tableId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará la ocupación de la mesa y no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirigir al script de eliminación pasando el ID de la mesa
                    window.location.href = 'eliminar_historial.php?id=' + tableId;
                }
            });
        }
        </script>
    </div>
    <script src="../validaciones/funciones.js"></script>

</body>
</html>
