<?php
session_start();
include '../conexion/conexion.php';

// Function to convert an integer to Roman numerals
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

// Verify if the user is logged in
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php?error=1');
    exit;
}

$usuario = $_SESSION['usuario'];

// Get user ID based on username
$sqlGetUserId = "SELECT user_id FROM tbl_users WHERE username = :usuario";
$stmtGetUserId = $conexion->prepare($sqlGetUserId);
$stmtGetUserId->bindParam(':usuario', $usuario, PDO::PARAM_STR);
$stmtGetUserId->execute();
$result = $stmtGetUserId->fetch(PDO::FETCH_ASSOC);
$userId = $result ? $result['user_id'] : null;

// Handle table occupation or release or reservation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['tableId'])) {
    $tableId = $_POST['tableId'];
    $action = $_POST['action'];

    if ($action === 'occupy') {
        // Occupy the table
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
        // Free the table
        $sqlUpdateTable = "UPDATE tbl_tables SET status = 'free' WHERE table_id = :tableId";
        $stmtUpdateTable = $conexion->prepare($sqlUpdateTable);
        $stmtUpdateTable->bindParam(':tableId', $tableId, PDO::PARAM_INT);
        $stmtUpdateTable->execute();

        $sqlEndOccupation = "UPDATE tbl_occupations SET end_time = CURRENT_TIMESTAMP WHERE table_id = :tableId AND end_time IS NULL";
        $stmtEndOccupation = $conexion->prepare($sqlEndOccupation);
        $stmtEndOccupation->bindParam(':tableId', $tableId, PDO::PARAM_INT);
        $stmtEndOccupation->execute();
    } elseif ($action === 'reserve' && isset($_POST['reservationDate'], $_POST['startTime'], $_POST['endTime'])) {
        // Reserve the table
        $reservationDate = $_POST['reservationDate'];
        $startTime = $_POST['startTime'];
        $endTime = $_POST['endTime'];

        // Update table status to reserved
        $sqlUpdateTable = "UPDATE tbl_tables SET status = 'reserved' WHERE table_id = :tableId";
        $stmtUpdateTable = $conexion->prepare($sqlUpdateTable);
        $stmtUpdateTable->bindParam(':tableId', $tableId, PDO::PARAM_INT);
        $stmtUpdateTable->execute();

        // Insert reservation data into tbl_reservations
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

// Get the current status of each table in the terrace
$sql = "SELECT table_id, status FROM tbl_tables WHERE room_id = 2"; // Changed to room 2 for terrace 2
$stmt = $conexion->query($sql);
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC); // Fetch all results as an associative array
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
            <h1>T e r r a z a    II</h1>
        </div>
        <div class="grid">
            <?php
            // Generate HTML for each table
            foreach ($tables as $row) {
                $tableId = $row['table_id'];
                $status = $row['status'];
                $romanTableId = romanNumerals($tableId); // Convert to Roman numerals
                $imgSrc = ($status === 'occupied') ? '../img/sombrillaRoja.webp' : 
                          ($status === 'reserved' ? '../img/sombrillaAmarilla.webp' : '../img/sombrilla.webp');

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
</body>
</html>
