<!DOCTYPE html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Login</title>
    <link rel="stylesheet" href="./css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Sancreek&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div>
            <form action="./procLogin/validacionLogin.php" method="POST" onsubmit="return validarFormulario()">
                <div><p class="sombraindex">Iniciar SesionDD</p></div>
                <div><p class="tituloindex">Iniciar Sesion</p></div>
                <!-- Contenedor flex para los inputs -->
                <div class="section-1">
                    <!-- Usuario -->
                    <div class="column-2">
                        <div><p class="pformularioindex">Nombre de Usuario:</p></div>
                        <input type="text" name="usuario" id="usuario" placeholder="Nombre de usuario..." onblur="validaNombre()">
                        <div id="error-nombre" class="mensaje-error" style="color: red;"></div>
                    </div>
                    <!-- Contraseña -->
                    <div class="column-2">
                        <div><p class="pformularioindex">Contraseña:</p></div>
                        <input type="password" name="password" id="password" placeholder="Contraseña..." onblur="validaContraseña()">
                        <div id="error_contraseña" class="mensaje-error" style="color: red;"></div>
                    </div>
                </div>
                <!-- Boton para entrar -->
                <button type="submit">ENTRAR</button>
                <?php
                if (isset($_GET['error']) && $_GET['error'] == 1) {
                    echo "<p style='color: red'>Usuario o contraseña incorrectos</p>";
                }
                if (isset($_GET['error']) && $_GET['error'] == 2) {
                    echo "<p style='color: red'>No eres un administrador</p>";
                }
                ?>
            </form>
            <div>
                <img src="./img/logo.webp" alt="Logo de la página"><br>
            </div>
        </div>
    </div>
    <script src="./validaciones/validaciones.js"></script>
</body>
</html>


