<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Mesas</h2>
    <a href="?seccion=mesas&accion=nueva" class="btn btn-success">Nueva Mesa</a>
</div>

<!-- Filtros de búsqueda -->
<div class="filter-section mb-4">
    <form method="GET" class="row g-3">
        <input type="hidden" name="seccion" value="mesas">
        
        <div class="col-md-3">
            <input type="text" class="form-control" name="buscar_numero" 
                   placeholder="Buscar por número" 
                   value="<?php echo $_GET['buscar_numero'] ?? ''; ?>">
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
            <input type="number" class="form-control" name="buscar_capacidad" 
                   placeholder="Capacidad mínima" 
                   value="<?php echo $_GET['buscar_capacidad'] ?? ''; ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary me-2">Filtrar</button>
            <a href="?seccion=mesas" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'guardar':
                $numero = $_POST['numero'];
                $capacidad = $_POST['capacidad'];
                $room_id = $_POST['room_id'];
                $estado = match(strtolower($_POST['estado'])) {
                    'disponible' => 'free',
                    'ocupada' => 'occupied',
                    'reservada' => 'reserved',
                    default => 'free'
                };
                $mesa_id = $_POST['mesa_id'] ?? null;

                if ($mesa_id) {
                    $stmt = $conexion->prepare("UPDATE tbl_tables SET table_number = ?, capacity = ?, room_id = ?, status = ? WHERE table_id = ?");
                    $stmt->execute([$numero, $capacidad, $room_id, $estado, $mesa_id]);
                } else {
                    $stmt = $conexion->prepare("INSERT INTO tbl_tables (table_number, capacity, room_id, status) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$numero, $capacidad, $room_id, $estado]);
                }
                echo "<script>window.location.href = '?seccion=mesas';</script>";
                exit;
                break;

            case 'eliminar':
                $mesa_id = $_POST['mesa_id'];
                $stmt = $conexion->prepare("DELETE FROM tbl_tables WHERE table_id = ?");
                $stmt->execute([$mesa_id]);
                echo "<script>window.location.href = '?seccion=mesas';</script>";
                exit;
                break;
        }
    }
}

// Mostrar formulario si se solicita
if (isset($_GET['accion']) && ($_GET['accion'] == 'nueva' || $_GET['accion'] == 'editar')) {
    $mesa = null;
    if ($_GET['accion'] == 'editar' && isset($_GET['id'])) {
        $stmt = $conexion->prepare("SELECT * FROM tbl_tables WHERE table_id = ?");
        $stmt->execute([$_GET['id']]);
        $mesa = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    ?>
    <form method="POST" class="mb-4">
        <input type="hidden" name="accion" value="guardar">
        <?php if ($mesa) { ?>
            <input type="hidden" name="mesa_id" value="<?php echo $mesa['table_id']; ?>">
        <?php } ?>
        
        <div class="mb-3">
            <label class="form-label">Número de Mesa en sala</label>
            <input type="number" class="form-control" name="numero" 
                   value="<?php echo $mesa ? $mesa['table_number'] : ''; ?>" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Capacidad</label>
            <input type="number" class="form-control" name="capacidad" 
                   value="<?php echo $mesa ? $mesa['capacity'] : ''; ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Sala</label>
            <select class="form-control" name="room_id" required>
                <?php
                $salas = $conexion->query("SELECT * FROM tbl_rooms ORDER BY name");
                while ($sala = $salas->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($mesa && $mesa['room_id'] == $sala['room_id']) ? 'selected' : '';
                    echo "<option value='{$sala['room_id']}' {$selected}>{$sala['name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Estado</label>
            <select class="form-control" name="estado" required>
                <option value="disponible" <?php echo ($mesa && $mesa['status'] == 'free') ? 'selected' : ''; ?>>Disponible</option>
                <option value="ocupada" <?php echo ($mesa && $mesa['status'] == 'occupied') ? 'selected' : ''; ?>>Ocupada</option>
                <option value="reservada" <?php echo ($mesa && $mesa['status'] == 'reserved') ? 'selected' : ''; ?>>Reservada</option>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="?seccion=mesas" class="btn btn-secondary">Cancelar</a>
    </form>
    <?php
    return;
}

// Construir la consulta con filtros
$where = [];
$params = [];

if (!empty($_GET['buscar_numero'])) {
    $where[] = "t.table_number LIKE :numero";
    $params[':numero'] = '%' . $_GET['buscar_numero'] . '%';
}

if (!empty($_GET['buscar_sala'])) {
    $where[] = "t.room_id = :room_id";
    $params[':room_id'] = $_GET['buscar_sala'];
}

if (!empty($_GET['buscar_capacidad'])) {
    $where[] = "t.capacity >= :capacidad";
    $params[':capacidad'] = $_GET['buscar_capacidad'];
}

$sql = "SELECT t.*, r.name as room_name 
        FROM tbl_tables t 
        JOIN tbl_rooms r ON t.room_id = r.room_id";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY t.table_id ASC";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
?>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Número en sala</th>
                <th>Sala</th>
                <th>Capacidad</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($mesa = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo $mesa['table_id']; ?></td>
                    <td><?php echo $mesa['table_number']; ?></td>
                    <td><?php echo $mesa['room_name']; ?></td>
                    <td><?php echo $mesa['capacity']; ?></td>
                    <td>
                        <span class="badge <?php 
                            echo match($mesa['status']) {
                                'free' => 'bg-success',
                                'occupied' => 'bg-danger',
                                'reserved' => 'bg-warning',
                                default => 'bg-secondary'
                            };
                        ?>">
                            <?php 
                            echo match($mesa['status']) {
                                'free' => 'Disponible',
                                'occupied' => 'Ocupada',
                                'reserved' => 'Reservada',
                                default => 'Desconocido'
                            };
                            ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="?seccion=mesas&accion=editar&id=<?php echo $mesa['table_id']; ?>" 
                               class="btn btn-sm btn-primary">Editar</a>
                            <form method="POST" class="m-0">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="mesa_id" value="<?php echo $mesa['table_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Estás seguro de eliminar esta mesa?')">
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