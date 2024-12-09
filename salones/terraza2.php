<?php
session_start();
include '../conexion/conexion.php';

// Función para convertir un número entero a un número romano
function romanNumerals($number) {
    $map = [
        'M' => 1000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
        'I' => 1
    ];
    $result = '';
    foreach ($map as $roman => $int) {
        while ($number >= $int) {
            $result .= $roman;
            $number -= $int;
        }
    }
    return $result;
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    echo "<h6>Por favor, inicie sesión.</h6>";
    exit;
}

$usuario = $_SESSION['usuario'];

// Obtener ID del usuario basado en el nombre de usuario
try {
    $sqlGetUserId = "SELECT user_id FROM tbl_users WHERE username = :usuario";
    $stmtGetUserId = $conexion->prepare($sqlGetUserId);
    $stmtGetUserId->bindParam(':usuario', $usuario, PDO::PARAM_STR);
    $stmtGetUserId->execute();
    $result = $stmtGetUserId->fetch(PDO::FETCH_ASSOC);
    $userId = $result ? $result['user_id'] : null;
} catch (Exception $e) {
    echo "Error en la consulta SQL: " . $e->getMessage();
    exit;
}

// Actualizar la ocupación o desocupación de una mesa
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['tableId'])) {
    $tableId = $_POST['tableId'];
    $action = $_POST['action'];

    try {
        if ($action === 'occupy') {
            $conexion->beginTransaction();

            // Actualizar el estado de la mesa
            $sqlUpdateTable = "UPDATE tbl_tables SET status = 'occupied' WHERE table_id = :tableId";
            $stmtUpdateTable = $conexion->prepare($sqlUpdateTable);
            $stmtUpdateTable->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtUpdateTable->execute();

            // Insertar nueva ocupación
            $sqlInsertOccupation = "INSERT INTO tbl_occupations (table_id, user_id, start_time) VALUES (:tableId, :userId, CURRENT_TIMESTAMP)";
            $stmtInsertOccupation = $conexion->prepare($sqlInsertOccupation);
            $stmtInsertOccupation->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtInsertOccupation->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmtInsertOccupation->execute();

            $conexion->commit();
        } elseif ($action === 'free') {
            $conexion->beginTransaction();

            // Liberar la mesa
            $sqlUpdateTable = "UPDATE tbl_tables SET status = 'free' WHERE table_id = :tableId";
            $stmtUpdateTable = $conexion->prepare($sqlUpdateTable);
            $stmtUpdateTable->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtUpdateTable->execute();

            // Finalizar ocupación activa
            $sqlEndOccupation = "UPDATE tbl_occupations SET end_time = CURRENT_TIMESTAMP WHERE table_id = :tableId AND end_time IS NULL";
            $stmtEndOccupation = $conexion->prepare($sqlEndOccupation);
            $stmtEndOccupation->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtEndOccupation->execute();

            $conexion->commit();
        }
    } catch (Exception $e) {
        $conexion->rollBack();
        echo "Error en la actualización: " . $e->getMessage();
        exit;
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Consultar el estado actual de cada mesa en la terraza 2
try {
    $sql = "SELECT table_id, status FROM tbl_tables WHERE room_id = 2";
    $stmt = $conexion->query($sql);
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error al consultar las mesas: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terraza II</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sancreek&display=swap" rel="stylesheet">
</head>
<body>
    <div><img src="./../img/logo.webp" alt="Logo de la página" class="superpuesta"><br></div>
    <div class="container2">
        <div class="header">
            <h1>T e r r a z a II</h1>
        </div>
        <div class="grid2">
            <?php
            $roomId = 2; 
            foreach ($tables as $row) {
                $tableId = $row['table_id'];
                $status = $row['status'];
                $romanTableId = romanNumerals($tableId);
                $imgSrc = ($status === 'occupied') ? '../img/sombrillaRoja.webp' : '../img/sombrilla.webp';

                echo "
                <div class='table' id='mesa$tableId' onclick='openTableOptions($tableId, \"$status\", \"$romanTableId\", $roomId)'>
                    <img id='imgMesa$tableId' src='$imgSrc' alt='Mesa $tableId'>
                    <p>Mesa $romanTableId</p>
                </div>

                <form id='formMesa$tableId' method='POST' style='display: none;'>
                    <input type='hidden' name='tableId' value='$tableId'>
                    <input type='hidden' name='action' id='action$tableId'>
                    <input type='hidden' name='newRoomId' id='newRoomId$tableId'>
                </form>
                ";
            }
            ?>
        </div>

        <button class="logout-button" onclick="logout()">Cerrar Sesión</button>
        <form action="../paginaPrincipal.php">
            <button class="logout" onclick="goBack()">Volver</button>
        </form>
    </div>

    <script src="../validaciones/funcionesSalones.js"></script>
    <script src="../validaciones/funciones.js"></script>
    <script>
    const roomId = 2;
    </script>
</body>
</html>
