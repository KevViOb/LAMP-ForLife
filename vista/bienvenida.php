<?php

/**
 * Vista de bienvenida para usuarios autenticados con rol 'usuario'.
 */

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'usuario') {
    header('Location: login.php');
    exit;
}

$nombre    = htmlspecialchars($_SESSION['nombre']);
$apellido1 = htmlspecialchars($_SESSION['apellido1']);
$apellido2 = htmlspecialchars($_SESSION['apellido2']);
$usuario   = htmlspecialchars($_SESSION['usuario']);
$iniciales = strtoupper(substr($_SESSION['nombre'], 0, 1) . substr($_SESSION['apellido1'], 0, 1));
$navInicial = strtoupper(substr($_SESSION['usuario'], 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>For Life S.A. — Bienvenido</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
    <div class="contenedor-dashboard">

        <!-- NAVBAR -->
        <header class="barra-superior">
            <div class="nav-marca">
                <div class="nav-logo-cuadro">
                    <img src="../assets/img/logo.svg" alt="FL">
                </div>
                <span class="nav-brand-name">For Life S.A.</span>
            </div>

            <nav class="nav-centro">
                <a href="bienvenida.php" class="nav-item activo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Inicio
                </a>
            </nav>

            <div class="nav-derecha">
                <div class="nav-avatar"><?= $navInicial ?></div>
                <span class="nav-usuario-nombre"><?= $nombre ?></span>
                <a href="logout.php" class="btn-salir">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Cerrar sesión
                </a>
            </div>
        </header>

        <!-- CONTENIDO -->
        <main class="contenido-principal">

            <!-- Hero card -->
            <div class="hero-card">
                <div class="hero-izquierda">
                    <p class="hero-saludo">¡Bienvenido de nuevo,</p>
                    <h2 class="hero-nombre"><?= $nombre ?> <?= $apellido1 ?> <?= $apellido2 ?></h2>
                    <div class="hero-badges">
                        <span class="hero-badge-rol">Usuario</span>
                    </div>
                </div>
                <div class="hero-derecha">
                    <div class="hero-avatar-grande"><?= $iniciales ?></div>
                    <span class="hero-avatar-label">Sesión activa</span>
                    <div class="hero-status">
                        <span class="dot-online"></span>
                        En línea
                    </div>
                </div>
            </div>

            <!-- Información de la cuenta -->
            <div class="tarjeta-info">
                <div class="tarjeta-info-header">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Información de la cuenta
                </div>
                <table class="tabla-datos">
                    <tr>
                        <th>Usuario</th>
                        <td><?= $usuario ?></td>
                    </tr>
                    <tr>
                        <th>Nombre</th>
                        <td><?= $nombre ?></td>
                    </tr>
                    <tr>
                        <th>Primer apellido</th>
                        <td><?= $apellido1 ?></td>
                    </tr>
                    <tr>
                        <th>Segundo apellido</th>
                        <td><?= $apellido2 ?></td>
                    </tr>
                    <tr>
                        <th>Rol</th>
                        <td><span class="badge-rol badge-usuario">Usuario</span></td>
                    </tr>
                </table>
            </div>

        </main>

    </div>
    <script src="../assets/js/app.js"></script>
</body>
</html>
