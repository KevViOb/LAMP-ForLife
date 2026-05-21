<?php

/**
 * Vista de inicio de sesión.
 * Procesa el POST y delega al controlador.
 * El HTML es un placeholder — el frontend lo reemplazará completamente.
 */

session_start();

// Si ya hay sesión, redirigir directo
if (isset($_SESSION['usuario'])) {
    header('Location: ' . ($_SESSION['rol'] === 'admin' ? 'admin.php' : 'bienvenida.php'));
    exit;
}

require_once __DIR__ . '/../controlador/AuthControlador.php';

$controlador = new AuthControlador();
$mensaje     = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = $controlador->procesarLogin($_POST);

    if ($resultado['exito']) {
        header('Location: ' . ($_SESSION['rol'] === 'admin' ? 'admin.php' : 'bienvenida.php'));
        exit;
    }

    $mensaje     = $resultado['mensaje'];
    $tipoMensaje = 'error';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>For Life S.A. — Iniciar Sesión</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
    <div class="contenedor-auth">
        <div class="tarjeta-auth">

            <div class="logo-empresa">
                <img src="../assets/img/logo.svg" alt="For Life S.A." class="logo">
                <h1>For Life S.A.</h1>
                <p class="subtitulo">Sistema de Autenticación</p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="alerta alerta-<?= $tipoMensaje ?>">
                    <?= $mensaje ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="formulario" novalidate>
                <div class="campo">
                    <label for="usuario">Usuario</label>
                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        placeholder="Ingrese su usuario"
                        autocomplete="username"
                        required
                        maxlength="50"
                    >
                </div>

                <div class="campo">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Ingrese su contraseña"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn-principal">Iniciar Sesión</button>
            </form>

            <p class="enlace-alternativo">
                ¿No tiene cuenta? <a href="registro.php">Regístrese aquí</a>
            </p>

        </div>
    </div>
    <script src="../assets/js/app.js"></script>
</body>
</html>
