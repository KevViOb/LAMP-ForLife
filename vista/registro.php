<?php

/**
 * Vista de registro de nuevos usuarios.
 * Procesa el POST y delega al controlador.
 */

session_start();

if (isset($_SESSION['usuario'])) {
    header('Location: bienvenida.php');
    exit;
}

require_once __DIR__ . '/../controlador/AuthControlador.php';

$controlador = new AuthControlador();
$mensaje     = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = $controlador->procesarRegistro($_POST);

    $mensaje     = $resultado['mensaje'];
    $tipoMensaje = $resultado['exito'] ? 'exito' : 'error';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>For Life S.A. — Registro</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
    <div class="contenedor-auth">
        <div class="tarjeta-auth tarjeta-registro">

            <div class="logo-empresa">
                <img src="../assets/img/logo.svg" alt="For Life S.A." class="logo">
                <h1>For Life S.A.</h1>
                <p class="subtitulo">Crear cuenta nueva</p>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="alerta alerta-<?= $tipoMensaje ?>">
                    <?= $mensaje ?>
                    <?php if ($tipoMensaje === 'exito'): ?>
                        <br><a href="login.php">Ir al inicio de sesión</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="formulario" novalidate>

                <div class="campo">
                    <label for="usuario">Nombre de usuario</label>
                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        placeholder="Mínimo 4 caracteres, sin espacios"
                        autocomplete="username"
                        required
                        maxlength="50"
                        value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                    >
                </div>

                <div class="campo">
                    <label for="nombre">Nombre</label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        placeholder="Su nombre"
                        required
                        maxlength="100"
                        value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                    >
                </div>

                <div class="fila-doble">
                    <div class="campo">
                        <label for="apellido1">Primer apellido</label>
                        <input
                            type="text"
                            id="apellido1"
                            name="apellido1"
                            placeholder="Primer apellido"
                            required
                            maxlength="100"
                            value="<?= htmlspecialchars($_POST['apellido1'] ?? '') ?>"
                        >
                    </div>

                    <div class="campo">
                        <label for="apellido2">Segundo apellido</label>
                        <input
                            type="text"
                            id="apellido2"
                            name="apellido2"
                            placeholder="Segundo apellido"
                            required
                            maxlength="100"
                            value="<?= htmlspecialchars($_POST['apellido2'] ?? '') ?>"
                        >
                    </div>
                </div>

                <div class="campo">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Mínimo 8 caracteres"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <div class="campo">
                    <label for="confirmar">Confirmar contraseña</label>
                    <input
                        type="password"
                        id="confirmar"
                        name="confirmar"
                        placeholder="Repita su contraseña"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="btn-principal">Crear cuenta</button>
            </form>

            <p class="enlace-alternativo">
                ¿Ya tiene cuenta? <a href="login.php">Inicie sesión</a>
            </p>

        </div>
    </div>
    <script src="../assets/js/app.js"></script>
</body>
</html>
