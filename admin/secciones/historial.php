<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Historial de Ocupaciones</h2>
</div>

<!-- Filtros de búsqueda -->
<div class="filter-section mb-4">
    <form method="GET" class="row g-3">
        <input type="hidden" name="seccion" value="historial">
        
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
                   value="<?php echo $_GET['buscar_fecha'] ?? ''; ?>"
                   placeholder="Fecha">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary me-2">Filtrar</button>
            <a href="?seccion=historial" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
</div>

<?php
// Inicializar variables para los filtros
$where = [];
$params = [];

// Construir la consulta con filtros
if (!empty($_GET['buscar_camarero'])) {
    $where[] = "o.user_id = :user_id";
    $params[':user_id'] = $_GET['buscar_camarero'];
}

if (!empty($_GET['buscar_sala'])) {
    $where[] = "t.room_id = :room_id";
    $params[':room_id'] = $_GET['buscar_sala'];
}

if (!empty($_GET['buscar_fecha'])) {
    $where[] = "DATE(o.start_time) = :fecha";
    $params[':fecha'] = $_GET['buscar_fecha'];
}

$sql = "SELECT o.*, t.table_number, r.name as room_name, u.username as user_name,
               TIMESTAMPDIFF(MINUTE, o.start_time, COALESCE(o.end_time, NOW())) as duration
        FROM tbl_occupations o
        JOIN tbl_tables t ON o.table_id = t.table_id
        JOIN tbl_rooms r ON t.room_id = r.room_id
        JOIN tbl_users u ON o.user_id = u.user_id";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY o.occupation_id ASC";

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
                <th>Camarero</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Duración</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($ocupacion = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo $ocupacion['occupation_id']; ?></td>
                    <td><?php echo $ocupacion['table_number']; ?></td>
                    <td><?php echo $ocupacion['room_name']; ?></td>
                    <td><?php echo $ocupacion['user_name']; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($ocupacion['start_time'])); ?></td>
                    <td>
                        <?php 
                        echo $ocupacion['end_time'] 
                            ? date('d/m/Y H:i', strtotime($ocupacion['end_time']))
                            : '<span class="badge bg-success">Activa</span>';
                        ?>
                    </td>
                    <td>
                        <?php
                        $duracion = $ocupacion['duration'];
                        if ($duracion < 60) {
                            echo $duracion . ' minutos';
                        } else {
                            $horas = floor($duracion / 60);
                            $minutos = $duracion % 60;
                            echo $horas . 'h ' . $minutos . 'm';
                        }
                        ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div> 