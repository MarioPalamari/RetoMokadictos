<?php 
session_start(); 
include('./conexion/conexion.php');

if (!isset($_SESSION['usuario'])) {
    header('Location: ./index.php?error=1') ;   
exit();
}

// Funciones para obtener el número de mesas libres y ocupadas en cada terraza
function obtenerEstadoMesas($conexion, $roomId) {
    $sql = "SELECT 
                SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) AS ocupadas,
                SUM(CASE WHEN status = 'free' THEN 1 ELSE 0 END) AS libres
            FROM tbl_tables WHERE room_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

$estadoTerraza1 = obtenerEstadoMesas($conexion, 1); // Terraza 1
$estadoTerraza2 = obtenerEstadoMesas($conexion, 2); // Terraza 2
$estadoTerraza3 = obtenerEstadoMesas($conexion, 3); // Terraza 3

// Funciones para obtener el número de mesas libres y ocupadas en cada terraza
function obtenerEstadoSalones($conexion, $roomId) {
    $sql = "SELECT 
                SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) AS ocupadas,
                SUM(CASE WHEN status = 'free' THEN 1 ELSE 0 END) AS libres
            FROM tbl_tables WHERE room_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

$estadoSalon1 = obtenerEstadoSalones($conexion, 4); // Salon 1
$estadoSalon2 = obtenerEstadoSalones($conexion, 5); // Salon 2

// Funciones para obtener el número de mesas libres y ocupadas en cada terraza
function obtenerEstadoVIPS($conexion, $roomId) {
    $sql = "SELECT 
                SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) AS ocupadas,
                SUM(CASE WHEN status = 'free' THEN 1 ELSE 0 END) AS libres
            FROM tbl_tables WHERE room_id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

$estadoVIP1 = obtenerEstadoVIPS($conexion, 6); // VIP 1
$estadoVIP2 = obtenerEstadoVIPS($conexion, 7); // VIP 2
$estadoVIP3 = obtenerEstadoVIPS($conexion, 8); // VIP 3
$estadoVIP4 = obtenerEstadoVIPS($conexion, 9); // VIP 4
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selección de Salas</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sancreek&display=swap" rel="stylesheet">

</head>
<body>
    <div><img src="./img/logo.webp" alt="Logo de la página" class="superpuesta"><br></div>
    <div class="container">
        <h1>S A L A S</h1>
        <div class="room-sections">
            <!-- Sección de Terrazas -->
            <div class="room-category">
                <img src="./img/terraza.webp" alt="Terrazas" onclick="mostrarEstadoTerrazas()">
                <div class="buttons">
                    <form action="./salones/terraza1.php">
                        <button><img class="nums" src="./img/nums/1.webp" alt=""></button>
                    </form>
                    <form action="./salones/terraza2.php">
                        <button><img class="nums" src="./img/nums/2.webp" alt=""></button>
                    </form>
                    <form action="./salones/terraza3.php">
                        <button><img class="nums" src="./img/nums/3.webp" alt=""></button>
                    </form>
                </div>
            </div>

            <!-- Sección de Salones Principales -->
            <div class="room-category">
                <img src="./img/salon.webp" alt="Salones Principales" onclick="mostrarEstadoSalones()">
                <div class="buttons">
                    <form action="./salones/salon1.php">
                        <button><img class="nums" src="./img/nums/1.webp" alt=""></button>
                    </form>
                    <form action="./salones/salon2.php">
                        <button><img class="nums" src="./img/nums/2.webp" alt=""></button>
                    </form>
                </div>
            </div>

            <!-- Sección de Salas Privadas -->
            <div class="room-category">
                <img src="./img/vip.webp" alt="Salas Privadas" onclick="mostrarEstadoVIPS()">
                <div class="buttons">
                    <form action="./salones/vip1.php">
                        <button><img class="nums" src="./img/nums/1.webp" alt=""></button>
                    </form>
                    <form action="./salones/vip2.php">
                        <button><img class="nums" src="./img/nums/2.webp" alt=""></button>
                    </form>
                    <form action="./salones/vip3.php">
                        <button><img class="nums" src="./img/nums/3.webp" alt=""></button>
                    </form>
                    <form action="./salones/vip4.php">
                        <button><img class="nums" src="./img/nums/4.webp" alt=""></button>
                    </form>
                </div>
            </div>
        </div>
        <button class="logout-button" onclick="logout1()">Cerrar Sesión</button>
    </div>

    <script src="./validaciones/funciones.js"></script>
    <script src="./validaciones/funcionesPaginaPrincipal.js"></script>

    <div id="estadoTerraza" 
        data-ocupadas-t1="<?php echo $estadoTerraza1['ocupadas']; ?>"
        data-libres-t1="<?php echo $estadoTerraza1['libres']; ?>"
        data-ocupadas-t2="<?php echo $estadoTerraza2['ocupadas']; ?>"
        data-libres-t2="<?php echo $estadoTerraza2['libres']; ?>"
        data-ocupadas-t3="<?php echo $estadoTerraza3['ocupadas']; ?>"
        data-libres-t3="<?php echo $estadoTerraza3['libres']; ?>"
        data-ocupadas-s1="<?php echo $estadoSalon1['ocupadas']; ?>"
        data-libres-s1="<?php echo $estadoSalon1['libres']; ?>"
        data-ocupadas-s2="<?php echo $estadoSalon2['ocupadas']; ?>"
        data-libres-s2="<?php echo $estadoSalon2['libres']; ?>"
        data-ocupadas-v1="<?php echo $estadoVIP1['ocupadas']; ?>"
        data-libres-v1="<?php echo $estadoVIP1['libres']; ?>"
        data-ocupadas-v2="<?php echo $estadoVIP2['ocupadas']; ?>"
        data-libres-v2="<?php echo $estadoVIP2['libres']; ?>"
        data-ocupadas-v3="<?php echo $estadoVIP3['ocupadas']; ?>"
        data-libres-v3="<?php echo $estadoVIP3['libres']; ?>"
        data-ocupadas-v4="<?php echo $estadoVIP4['ocupadas']; ?>"
        data-libres-v4="<?php echo $estadoVIP4['libres']; ?>">
    </div>

</body>
</html>

