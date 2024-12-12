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
    header('Location: ../index.php?error=1');
    exit;
}

$usuario = $_SESSION['usuario'];

try {
    // Obtener ID del usuario basado en el nombre de usuario
    $sqlGetUserId = "SELECT user_id FROM tbl_users WHERE username = :usuario";
    $stmtGetUserId = $conexion->prepare($sqlGetUserId);
    $stmtGetUserId->bindParam(':usuario', $usuario, PDO::PARAM_STR);
    $stmtGetUserId->execute();
    $result = $stmtGetUserId->fetch(PDO::FETCH_ASSOC);
    $userId = $result ? $result['user_id'] : null;

    if (!$userId) {
        header('Location: ../index.php?error=2'); // Usuario no encontrado
        exit;
    }
} catch (Exception $e) {
    echo "Error al obtener el usuario: " . $e->getMessage();
    exit;
}

// Actualizar la ocupación o desocupación de una mesa o realizar una reserva
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['tableId'])) {
    $tableId = $_POST['tableId'];
    $action = $_POST['action'];

    try {
        $conexion->beginTransaction();

        if ($action === 'occupy') {
            // Cambiar estado de la mesa a "occupied"
            $sqlUpdateTable = "UPDATE tbl_tables SET status = 'occupied' WHERE table_id = :tableId";
            $stmtUpdateTable = $conexion->prepare($sqlUpdateTable);
            $stmtUpdateTable->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtUpdateTable->execute();

            // Registrar ocupación
            $sqlInsertOccupation = "INSERT INTO tbl_occupations (table_id, user_id, start_time) VALUES (:tableId, :userId, CURRENT_TIMESTAMP)";
            $stmtInsertOccupation = $conexion->prepare($sqlInsertOccupation);
            $stmtInsertOccupation->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtInsertOccupation->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmtInsertOccupation->execute();
        } elseif ($action === 'free') {
            // Cambiar estado de la mesa a "free"
            $sqlUpdateTable = "UPDATE tbl_tables SET status = 'free' WHERE table_id = :tableId";
            $stmtUpdateTable = $conexion->prepare($sqlUpdateTable);
            $stmtUpdateTable->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtUpdateTable->execute();

            // Finalizar la ocupación activa
            $sqlEndOccupation = "UPDATE tbl_occupations SET end_time = CURRENT_TIMESTAMP WHERE table_id = :tableId AND end_time IS NULL";
            $stmtEndOccupation = $conexion->prepare($sqlEndOccupation);
            $stmtEndOccupation->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtEndOccupation->execute();
        } elseif ($action === 'reserve' && isset($_POST['reservationDate'], $_POST['startTime'], $_POST['endTime'])) {
            // Realizar reserva de mesa
            $reservationDate = $_POST['reservationDate'];
            $startTime = $_POST['startTime'];
            $endTime = $_POST['endTime'];

            // Actualizar estado de la mesa a "reserved"
            $sqlUpdateTable = "UPDATE tbl_tables SET status = 'reserved' WHERE table_id = :tableId";
            $stmtUpdateTable = $conexion->prepare($sqlUpdateTable);
            $stmtUpdateTable->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtUpdateTable->execute();

            // Insertar datos de la reserva
            $sqlInsertReservation = "INSERT INTO tbl_reservations (table_id, user_id, reservation_date, start_time, end_time) 
                                     VALUES (:tableId, :userId, :reservationDate, :startTime, :endTime)";
            $stmtInsertReservation = $conexion->prepare($sqlInsertReservation);
            $stmtInsertReservation->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtInsertReservation->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmtInsertReservation->bindParam(':reservationDate', $reservationDate);
            $stmtInsertReservation->bindParam(':startTime', $startTime);
            $stmtInsertReservation->bindParam(':endTime', $endTime);
            $stmtInsertReservation->execute();
        }

        $conexion->commit();
    } catch (Exception $e) {
        $conexion->rollBack();
        echo "Error en la actualización de la mesa: " . $e->getMessage();
        exit;
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Consultar el estado actual de las mesas 53 a 60
try {
    $sql = "SELECT t.table_id, t.status,
            (SELECT COUNT(*) FROM tbl_reservations r 
             WHERE r.table_id = t.table_id 
             AND r.reservation_date >= CURRENT_DATE) as has_reservations
            FROM tbl_tables t 
            WHERE t.table_id BETWEEN 53 AND 60";
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
    <title>V I P IV</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sancreek&display=swap" rel="stylesheet">
</head>
<body>
    <div><img src="./../img/logo.webp" alt="Logo de la página" class="superpuesta"><br></div>
    <div class="container2">
        <div class="header">
            <h1>V I P IV</h1>
        </div>
        <div class="grid7">
            <?php
            // Generar HTML para cada mesa
            foreach ($tables as $row) {
                $tableId = $row['table_id'];
                $status = $row['status'];
                $hasReservations = $row['has_reservations'] > 0;
                $romanTableId = romanNumerals($tableId);
                
                // Si está ocupada, mostrar roja sin importar las reservas
                $imgSrc = $status === 'occupied' ? '../img/sombrillaRoja.webp' : 
                          ($hasReservations ? '../img/sombrillaAmarilla.webp' : '../img/sombrilla.webp');

                echo "
                <div class='table1' id='mesa$tableId' onclick='openTableOptions($tableId, \"$status\", \"$romanTableId\")'>
                    <img id='imgMesa$tableId' src='$imgSrc' alt='Mesa $tableId'>
                    <p>Mesa $romanTableId</p>
                </div>

                <form id='formMesa$tableId' method='POST' style='display: none;'>
                    <input type='hidden' name='tableId' value='$tableId'>
                    <input type='hidden' name='action' id='action$tableId'>
                    <input type='hidden' name='reservationDate' id='reservationDate$tableId'>
                    <input type='hidden' name='startTime' id='startTime$tableId'>
                    <input type='hidden' name='endTime' id='endTime$tableId'>
                </form>

                <form id='viewReservationsForm$tableId' method='POST' style='display: none;'>
                    <input type='hidden' name='tableId' value='$tableId'>
                    <input type='hidden' name='action' value='viewReservations'>
                </form>
                ";
            }
            ?>
        </div>

        <button class="logout-button" onclick="logout()">Cerrar Sesión</button>
        <form action="../paginaPrincipal.php">
            <button class="logout">Volver</button>
        </form>
    </div>

    <script src="../validaciones/funcionesSalones.js"></script>
    <script src="../validaciones/funciones.js"></script>

    <!-- Formulario oculto para eliminar reservas -->
    <form id="deleteReservationForm" method="POST" style="display: none;">
        <input type="hidden" name="deleteReservationId" id="deleteReservationId">
    </form>
</body>
</html>
