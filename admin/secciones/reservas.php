<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Reservas</h2>
    <a href="?seccion=reservas&accion=nueva" class="btn btn-success">Nueva Reserva</a>
</div>

<!-- Filtros de búsqueda -->
<div class="filter-section mb-4">
    <form method="GET" class="row g-3">
        <input type="hidden" name="seccion" value="reservas">
        
        <div class="col-md-3">
            <select class="form-control" name="buscar_camarero">
                <option value="">Todos los camareros</option>
                <?php
                $camareros = $conexion->query("SELECT * FROM tbl_users ORDER BY username");
                while ($camarero = $camareros->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($_GET['buscar_camarero'] ?? '') == $camarero['user_id'] ? 'selected' : '';
                    echo "<option value='{$camarero['user_id']}' {$selected}>{$camarero['username']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-control" name="buscar_sala">
                <option value="">Todas las salas</option>
                <?php
                $salas = $conexion->query("SELECT * FROM tbl_rooms ORDER BY name");
                while ($sala = $salas->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($_GET['buscar_sala'] ?? '') == $sala['room_id'] ? 'selected' : '';
                    echo "<option value='{$sala['room_id']}' {$selected}>{$sala['name']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" class="form-control" name="buscar_fecha" 
                   value="<?php echo $_GET['buscar_fecha'] ?? ''; ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary me-2">Filtrar</button>
            <a href="?seccion=reservas" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'guardar':
                try {
                    $mesa_id = $_POST['mesa_id'];
                    $user_id = $_SESSION['user_id'];
                    $fecha = $_POST['fecha'];
                    $hora_inicio = $_POST['hora'];
                    $hora_fin = $_POST['hora_fin'];
                    $estado = 'pending';
                    $reserva_id = $_POST['reserva_id'] ?? null;

                    // Verificar si la mesa está disponible en ese horario
                    $stmt = $conexion->prepare("
                        SELECT COUNT(*) FROM tbl_reservations 
                        WHERE table_id = ? 
                        AND reservation_date = ? 
                        AND ((start_time <= ? AND end_time >= ?) 
                        OR (start_time <= ? AND end_time >= ?))
                        AND reservation_id != ?
                    ");
                    $stmt->execute([$mesa_id, $fecha, $hora_inicio, $hora_inicio, $hora_fin, $hora_fin, $reserva_id ?? 0]);
                    if ($stmt->fetchColumn() > 0) {
                        throw new Exception("La mesa ya está reservada en ese horario");
                    }

                    if ($reserva_id) {
                        $stmt = $conexion->prepare("UPDATE tbl_reservations SET 
                            table_id = ?, user_id = ?, reservation_date = ?, 
                            start_time = ?, end_time = ?, status = ? 
                            WHERE reservation_id = ?");
                        $stmt->execute([$mesa_id, $user_id, $fecha, 
                                      $hora_inicio, $hora_fin, $estado, $reserva_id]);
                    } else {
                        $stmt = $conexion->prepare("INSERT INTO tbl_reservations 
                            (table_id, user_id, reservation_date, start_time, end_time, status) 
                            VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$mesa_id, $user_id, $fecha, 
                                      $hora_inicio, $hora_fin, $estado]);
                    }
                    echo "<script>window.location.href = '?seccion=reservas';</script>";
                    exit;
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
                }
                break;

            case 'eliminar':
                $reserva_id = $_POST['reserva_id'];
                $stmt = $conexion->prepare("DELETE FROM tbl_reservations WHERE reservation_id = ?");
                $stmt->execute([$reserva_id]);
                echo "<script>window.location.href = '?seccion=reservas';</script>";
                exit;
                break;
        }
    }
}

// Mostrar formulario
if (isset($_GET['accion']) && ($_GET['accion'] == 'nueva' || $_GET['accion'] == 'editar')) {
    $reserva = null;
    if ($_GET['accion'] == 'editar' && isset($_GET['id'])) {
        $stmt = $conexion->prepare("SELECT * FROM tbl_reservations WHERE reservation_id = ?");
        $stmt->execute([$_GET['id']]);
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    ?>
    <form method="POST" class="mb-4">
        <input type="hidden" name="accion" value="guardar">
        <?php if ($reserva) { ?>
            <input type="hidden" name="reserva_id" value="<?php echo $reserva['reservation_id']; ?>">
        <?php } ?>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Fecha</label>
                <input type="date" class="form-control" name="fecha" 
                       value="<?php echo $reserva ? $reserva['reservation_date'] : ''; ?>">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Hora de inicio</label>
                <input type="time" class="form-control" name="hora" 
                       value="<?php echo $reserva ? $reserva['start_time'] : ''; ?>">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Hora de fin</label>
                <input type="time" class="form-control" name="hora_fin" 
                       value="<?php echo $reserva ? $reserva['end_time'] : ''; ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Mesa</label>
            <select class="form-control" name="mesa_id" >
                <option value="">Seleccione una mesa</option>
                <?php
                $mesas = $conexion->query("
                    SELECT t.*, r.name as room_name 
                    FROM tbl_tables t 
                    JOIN tbl_rooms r ON t.room_id = r.room_id 
                    ORDER BY r.name, t.table_number
                ");
                while ($mesa = $mesas->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($reserva && $reserva['table_id'] == $mesa['table_id']) ? 'selected' : '';
                    echo "<option value='{$mesa['table_id']}' {$selected}>
                            Mesa {$mesa['table_number']} - {$mesa['room_name']} 
                            (Capacidad: {$mesa['capacity']})
                          </option>";
                }
                ?>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="?seccion=reservas" class="btn btn-secondary">Cancelar</a>
    </form>
    <?php
    return;
}

// Inicializar variables para los filtros
$where = [];
$params = []; // Inicializar el array de parámetros

// Construir la consulta con filtros
if (!empty($_GET['buscar_camarero'])) {
    $where[] = "r.user_id = :user_id";
    $params[':user_id'] = $_GET['buscar_camarero'];
}

if (!empty($_GET['buscar_sala'])) {
    $where[] = "t.room_id = :room_id";
    $params[':room_id'] = $_GET['buscar_sala'];
}

if (!empty($_GET['buscar_fecha'])) {
    $where[] = "r.reservation_date = :fecha";
    $params[':fecha'] = $_GET['buscar_fecha'];
}

$sql = "SELECT r.*, t.table_number, rm.name as room_name, u.username as user_name
        FROM tbl_reservations r 
        JOIN tbl_tables t ON r.table_id = t.table_id 
        JOIN tbl_rooms rm ON t.room_id = rm.room_id
        JOIN tbl_users u ON r.user_id = u.user_id";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY r.reservation_id ASC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
?>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Mesa</th>
                <th>Sala</th>
                <th>Fecha</th>
                <th>Horario</th>
                <th>Usuario</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($reserva = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo $reserva['reservation_id']; ?></td>
                    <td><?php echo $reserva['table_number']; ?></td>
                    <td><?php echo $reserva['room_name']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($reserva['reservation_date'])); ?></td>
                    <td><?php echo date('H:i', strtotime($reserva['start_time'])) . ' - ' . 
                               date('H:i', strtotime($reserva['end_time'])); ?></td>
                    <td><?php echo $reserva['user_name']; ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="?seccion=reservas&accion=editar&id=<?php echo $reserva['reservation_id']; ?>" 
                               class="btn btn-sm btn-primary">Editar</a>
                            <form method="POST" class="m-0 form-eliminar" data-tipo="reserva">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="reserva_id" value="<?php echo $reserva['reservation_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div> 
<script src="../js/validarform.js"></script>