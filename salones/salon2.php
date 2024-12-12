<?php
session_start();
include '../conexion/conexion.php';

// Función para convertir un número entero a un número romano
function romanNumerals($number) {
    $map = [
        'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
        'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
        'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
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

if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php?error=1');
    exit;
}

$usuario = $_SESSION['usuario'];

// Obtener ID del usuario
$sqlGetUserId = "SELECT user_id FROM tbl_users WHERE username = :usuario";
$stmtGetUserId = $conexion->prepare($sqlGetUserId);
$stmtGetUserId->bindParam(':usuario', $usuario, PDO::PARAM_STR);
$stmtGetUserId->execute();
$result = $stmtGetUserId->fetch(PDO::FETCH_ASSOC);
$userId = $result ? $result['user_id'] : null;

// Función para eliminar una reserva
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deleteReservationId'])) {
    $reservationId = $_POST['deleteReservationId'];
    $sqlDeleteReservation = "DELETE FROM tbl_reservations WHERE reservation_id = :reservationId";
    $stmtDeleteReservation = $conexion->prepare($sqlDeleteReservation);
    $stmtDeleteReservation->bindParam(':reservationId', $reservationId, PDO::PARAM_INT);
    $stmtDeleteReservation->execute();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" || isset($_GET['action'])) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    if ($action === 'viewReservations') {
        $tableId = $_POST['tableId'] ?? $_GET['tableId'] ?? '';
        
        if ($tableId) {
            $sqlReservations = "SELECT r.*, u.username 
                               FROM tbl_reservations r 
                               JOIN tbl_users u ON r.user_id = u.user_id 
                               WHERE r.table_id = :tableId 
                               AND r.reservation_date >= CURRENT_DATE 
                               ORDER BY r.reservation_date, r.start_time";
            $stmtReservations = $conexion->prepare($sqlReservations);
            $stmtReservations->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtReservations->execute();
            $reservations = $stmtReservations->fetchAll(PDO::FETCH_ASSOC);

            $html = "<div class='reservations-list'>";
            if (count($reservations) > 0) {
                foreach ($reservations as $reservation) {
                    $html .= sprintf(
                        "<div class='reservation-item'>
                            <p>
                                <strong>Fecha:</strong> %s<br>
                                <strong>Hora:</strong> %s - %s<br>
                                <strong>Reservado por:</strong> %s
                            </p>
                            <button class='swal2-confirm swal2-styled' onclick='deleteReservation(%d)'>
                                Eliminar Reserva
                            </button>
                            <hr>
                        </div>",
                        date('d/m/Y', strtotime($reservation['reservation_date'])),
                        date('H:i', strtotime($reservation['start_time'])),
                        date('H:i', strtotime($reservation['end_time'])),
                        htmlspecialchars($reservation['username']),
                        $reservation['reservation_id']
                    );
                }
            } else {
                $html .= "<p>No hay reservas para esta mesa.</p>";
            }
            $html .= "</div>";

            if (isset($_GET['action'])) {
                echo $html;
                exit;
            }
        }
    } elseif (isset($_POST['action']) && isset($_POST['tableId'])) {
        $tableId = $_POST['tableId'];
        $action = $_POST['action'];

        if ($action === 'occupy') {
            $sqlUpdateTable = "UPDATE tbl_tables SET status = 'occupied' WHERE table_id = :tableId";
            $stmtUpdateTable = $conexion->prepare($sqlUpdateTable);
            $stmtUpdateTable->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtUpdateTable->execute();

            $sqlInsertOccupation = "INSERT INTO tbl_occupations (table_id, user_id, start_time) VALUES (:tableId, :userId, CURRENT_TIMESTAMP)";
            $stmtInsertOccupation = $conexion->prepare($sqlInsertOccupation);
            $stmtInsertOccupation->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtInsertOccupation->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmtInsertOccupation->execute();
        } elseif ($action === 'free') {
            $sqlUpdateTable = "UPDATE tbl_tables SET status = 'free' WHERE table_id = :tableId";
            $stmtUpdateTable = $conexion->prepare($sqlUpdateTable);
            $stmtUpdateTable->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtUpdateTable->execute();

            $sqlEndOccupation = "UPDATE tbl_occupations SET end_time = CURRENT_TIMESTAMP WHERE table_id = :tableId AND end_time IS NULL";
            $stmtEndOccupation = $conexion->prepare($sqlEndOccupation);
            $stmtEndOccupation->bindParam(':tableId', $tableId, PDO::PARAM_INT);
            $stmtEndOccupation->execute();
        } elseif ($action === 'reserve' && isset($_POST['reservationDate'], $_POST['startTime'], $_POST['endTime'])) {
            $reservationDate = $_POST['reservationDate'];
            $startTime = $_POST['startTime'];
            $endTime = $_POST['endTime'];

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

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Consulta modificada para incluir información de reservas
$sql = "SELECT t.table_id, t.status,
        (SELECT COUNT(*) FROM tbl_reservations r 
         WHERE r.table_id = t.table_id 
         AND r.reservation_date >= CURRENT_DATE) as has_reservations
        FROM tbl_tables t 
        WHERE t.table_id BETWEEN 31 AND 40";
$stmt = $conexion->query($sql);
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salón II</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sancreek&display=swap" rel="stylesheet">
</head>
<body>
    <div><img src="./../img/logo.webp" alt="Logo de la página" class="superpuesta"><br></div>
    <div class="container4">
        <div class="header">
            <h1>S A L Ó N    II</h1>
        </div>
        <div class="grid6">
            <?php
            foreach ($tables as $row) {
                $tableId = $row['table_id'];
                $status = $row['status'];
                $hasReservations = $row['has_reservations'] > 0;
                $romanTableId = romanNumerals($tableId);
                
                // Si está ocupada, mostrar roja sin importar las reservas
                $imgSrc = $status === 'occupied' ? '../img/salonRoja.webp' : 
                          ($hasReservations ? '../img/salonAmarilla.webp' : '../img/salonVerde.webp');

                echo "
                <div class='table' id='mesa$tableId' onclick='openTableOptions($tableId, \"$status\", \"$romanTableId\")'>
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
