<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Salas</h2>
    <a href="?seccion=salas&accion=nueva" class="btn btn-success">Nueva Sala</a>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'guardar':
                $nombre = $_POST['nombre'];
                $capacidad = $_POST['capacidad'];
                $descripcion = $_POST['descripcion'];
                $id_sala = $_POST['id_sala'] ?? null;

                if ($id_sala) {
                    $stmt = $conexion->prepare("UPDATE tbl_rooms SET name = ?, capacity = ?, description = ? WHERE room_id = ?");
                    $stmt->execute([$nombre, $capacidad, $descripcion, $id_sala]);
                } else {
                    $stmt = $conexion->prepare("INSERT INTO tbl_rooms (name, capacity, description) VALUES (?, ?, ?)");
                    $stmt->execute([$nombre, $capacidad, $descripcion]);
                }
                echo "<script>window.location.href = '?seccion=salas';</script>";
                exit;
                break;

            case 'eliminar':
                $id_sala = $_POST['id_sala'];
                $stmt = $conexion->prepare("DELETE FROM tbl_rooms WHERE room_id = ?");
                $stmt->execute([$id_sala]);
                echo "<script>window.location.href = '?seccion=salas';</script>";
                exit;
                break;
        }
    }
}

// Mostrar formulario si se solicita
if (isset($_GET['accion']) && ($_GET['accion'] == 'nueva' || $_GET['accion'] == 'editar')) {
    $sala = null;
    if ($_GET['accion'] == 'editar' && isset($_GET['id'])) {
        $stmt = $conexion->prepare("SELECT * FROM tbl_rooms WHERE room_id = ?");
        $stmt->execute([$_GET['id']]);
        $sala = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    ?>
    <form method="POST" class="mb-4">
        <input type="hidden" name="accion" value="guardar">
        <?php if ($sala) { ?>
            <input type="hidden" name="id_sala" value="<?php echo $sala['room_id']; ?>">
        <?php } ?>
        
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" 
                   value="<?php echo $sala ? $sala['name'] : ''; ?>" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Capacidad</label>
            <input type="number" class="form-control" name="capacidad" 
                   value="<?php echo $sala ? $sala['capacity'] : ''; ?>" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" rows="3"><?php echo $sala ? $sala['description'] : ''; ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="?seccion=salas" class="btn btn-secondary">Cancelar</a>
    </form>
    <?php
    return;
}
?>

<!-- Mostrar filtros solo si no se está editando -->
<?php if (!isset($_GET['accion'])): ?>
<div class="filter-section mb-4">
    <form method="GET" class="row g-3">
        <input type="hidden" name="seccion" value="salas">
        
        <div class="col-md-3">
            <input type="text" class="form-control" name="buscar_nombre" 
                   placeholder="Buscar por nombre" 
                   value="<?php echo $_GET['buscar_nombre'] ?? ''; ?>">
        </div>
        <div class="col-md-3">
            <input type="number" class="form-control" name="buscar_capacidad" 
                   placeholder="Capacidad mínima" 
                   value="<?php echo $_GET['buscar_capacidad'] ?? ''; ?>">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary me-2">Filtrar</button>
            <a href="?seccion=salas" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
</div>
<?php endif; ?>

<?php
// Construir la consulta con filtros
$where = [];
$params = [];

if (!empty($_GET['buscar_nombre'])) {
    $where[] = "name LIKE :nombre";
    $params[':nombre'] = '%' . $_GET['buscar_nombre'] . '%';
}

if (!empty($_GET['buscar_capacidad'])) {
    $where[] = "capacity >= :capacidad";
    $params[':capacidad'] = $_GET['buscar_capacidad'];
}

$sql = "SELECT * FROM tbl_rooms";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY room_id";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
?>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Capacidad</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($sala = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo $sala['room_id']; ?></td>
                    <td><?php echo $sala['name']; ?></td>
                    <td><?php echo $sala['capacity']; ?></td>
                    <td><?php echo $sala['description']; ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="?seccion=salas&accion=editar&id=<?php echo $sala['room_id']; ?>" 
                               class="btn btn-sm btn-primary">Editar</a>
                            <form method="POST" class="m-0">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id_sala" value="<?php echo $sala['room_id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger"
                                        onclick="return confirm('¿Estás seguro de eliminar esta sala?')">
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