<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Usuarios</h2>
    <a href="?seccion=usuarios&accion=nuevo" class="btn btn-success">Nuevo Usuario</a>
</div>

<!-- Filtros de búsqueda -->
<div class="filter-section mb-4">
    <form method="GET" class="row g-3">
        <input type="hidden" name="seccion" value="usuarios">
        
        <div class="col-md-3">
            <input type="text" class="form-control" name="buscar_usuario" 
                   placeholder="Buscar por usuario" 
                   value="<?php echo $_GET['buscar_usuario'] ?? ''; ?>">
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="buscar_nombre" 
                   placeholder="Buscar por nombre o apellidos" 
                   value="<?php echo $_GET['buscar_nombre'] ?? ''; ?>">
        </div>
        <div class="col-md-3">
            <select class="form-control" name="buscar_rol">
                <option value="">Todos los roles</option>
                <?php
                $roles = $conexion->query("SELECT * FROM tbl_roles ORDER BY role_name");
                while ($rol = $roles->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($_GET['buscar_rol'] ?? '') == $rol['role_id'] ? 'selected' : '';
                    echo "<option value='{$rol['role_id']}' {$selected}>{$rol['role_name']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary me-2">Filtrar</button>
            <a href="?seccion=usuarios" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
</div>

<?php
// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'guardar':
                try {
                    $username = $_POST['username'];
                    $nombre = $_POST['nombre'];
                    $apellidos = $_POST['apellidos'];
                    $role_id = $_POST['role_id'];
                    $user_id = $_POST['user_id'] ?? null;
                    $password = $_POST['password'] ?? null;

                    // Verificar si el usuario ya existe
                    $stmt = $conexion->prepare("SELECT COUNT(*) FROM tbl_users WHERE username = ? AND user_id != ?");
                    $stmt->execute([$username, $user_id ?? 0]);
                    if ($stmt->fetchColumn() > 0) {
                        throw new Exception("El nombre de usuario ya existe");
                    }

                    if ($user_id) {
                        if (!empty($password)) {
                            $stmt = $conexion->prepare("UPDATE tbl_users SET username = ?, nombre = ?, apellidos = ?, role_id = ?, pwd = ? WHERE user_id = ?");
                            $stmt->execute([$username, $nombre, $apellidos, $role_id, password_hash($password, PASSWORD_DEFAULT), $user_id]);
                        } else {
                            $stmt = $conexion->prepare("UPDATE tbl_users SET username = ?, nombre = ?, apellidos = ?, role_id = ? WHERE user_id = ?");
                            $stmt->execute([$username, $nombre, $apellidos, $role_id, $user_id]);
                        }
                    } else {
                        $stmt = $conexion->prepare("INSERT INTO tbl_users (username, nombre, apellidos, role_id, pwd) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$username, $nombre, $apellidos, $role_id, password_hash($password, PASSWORD_DEFAULT)]);
                    }
                    echo "<script>window.location.href = '?seccion=usuarios';</script>";
                    exit;
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
                }
                break;

            case 'eliminar':
                $user_id = $_POST['user_id'];
                $stmt = $conexion->prepare("DELETE FROM tbl_users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                echo "<script>window.location.href = '?seccion=usuarios';</script>";
                exit;
                break;
        }
    }
}

// Mostrar formulario si se solicita
if (isset($_GET['accion'])) {
    if ($_GET['accion'] == 'nuevo' || $_GET['accion'] == 'editar') {
        $usuario = null;
        if ($_GET['accion'] == 'editar' && isset($_GET['id'])) {
            $stmt = $conexion->prepare("SELECT * FROM tbl_users WHERE user_id = ?");
            $stmt->execute([$_GET['id']]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        ?>
        <form method="POST" class="mb-4" id="usuarioForm" novalidate>
            <input type="hidden" name="accion" value="guardar">
            <?php if ($usuario) { ?>
                <input type="hidden" name="user_id" value="<?php echo $usuario['user_id']; ?>">
            <?php } ?>
            
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" class="form-control" name="username" 
                       value="<?php echo $usuario ? $usuario['username'] : ''; ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-lab">Nombre</label>
                <input type="text" class="form-control" name="nombre" 
                       value="<?php echo $usuario ? $usuario['nombre'] : ''; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Apellidos</label>
                <input type="text" class="form-control" name="apellidos" 
                       value="<?php echo $usuario ? $usuario['apellidos'] : ''; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Rol</label>
                <select class="form-control" name="role_id" >
                    <?php
                    $roles = $conexion->query("SELECT * FROM tbl_roles ORDER BY role_name");
                    while ($rol = $roles->fetch(PDO::FETCH_ASSOC)) {
                        $selected = ($usuario && $usuario['role_id'] == $rol['role_id']) ? 'selected' : '';
                        echo "<option value='{$rol['role_id']}' {$selected}>{$rol['role_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña <?php echo $usuario ? '(dejar en blanco para mantener la actual)' : ''; ?></label>
                <input type="password" class="form-control" name="password" 
                       <?php echo $usuario ? '' : ''; ?>>
            </div>
            
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="?seccion=usuarios" class="btn btn-secondary">Cancelar</a>
        </form>
        <?php
        return;
    }
}

// Construir la consulta con filtros
$where = [];
$params = [];

if (!empty($_GET['buscar_usuario'])) {
    $where[] = "u.username LIKE :username";
    $params[':username'] = '%' . $_GET['buscar_usuario'] . '%';
}

if (!empty($_GET['buscar_nombre'])) {
    $where[] = "(u.nombre LIKE :nombre OR u.apellidos LIKE :nombre)";
    $params[':nombre'] = '%' . $_GET['buscar_nombre'] . '%';
}

if (!empty($_GET['buscar_rol'])) {
    $where[] = "u.role_id = :role_id";
    $params[':role_id'] = $_GET['buscar_rol'];
}

$sql = "SELECT u.*, r.role_name 
        FROM tbl_users u 
        JOIN tbl_roles r ON u.role_id = r.role_id";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY u.user_id";

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
?>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($usuario = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo $usuario['user_id']; ?></td>
                    <td><?php echo $usuario['username']; ?></td>
                    <td><?php echo $usuario['nombre']; ?></td>
                    <td><?php echo $usuario['apellidos']; ?></td>
                    <td><?php echo $usuario['role_name']; ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="?seccion=usuarios&accion=editar&id=<?php echo $usuario['user_id']; ?>" 
                               class="btn btn-sm btn-primary">Editar</a>
                            <form method="POST" class="m-0 form-eliminar" data-tipo="usuario">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="user_id" value="<?php echo $usuario['user_id']; ?>">
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
<script>
    console.log('usuarios.php cargado'); // Para verificar que llegamos a este punto
</script>
<script src="./js/validarform.js"></script>