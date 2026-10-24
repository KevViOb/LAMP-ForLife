<?php

/**
 * Panel de administración del sistema For Life, S.A.
 * Solo accesible para usuarios con rol 'admin'.
 */

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../controlador/AuthControlador.php';

$controlador = new AuthControlador();
$mensaje     = '';
$tipoMensaje = '';

// Token CSRF para que otro sitio no pueda enviar acciones en nombre del admin
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'], $_POST['id'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $mensaje     = 'Solicitud inválida. Recargue la página e intente de nuevo.';
        $tipoMensaje = 'error';
    } else {
        $id     = (int) $_POST['id'];
        $estado = $_POST['accion'] === 'activar' ? 1 : 0;
        $exito  = $controlador->cambiarEstadoUsuario($id, $estado);

        $mensaje     = $exito ? 'Estado actualizado correctamente.' : 'No se pudo actualizar el estado.';
        $tipoMensaje = $exito ? 'exito' : 'error';
    }
}

$usuarios = $controlador->obtenerUsuarios();
$logs     = $controlador->obtenerLogs();

$coloresAvatar = ['#2563EB', '#7C3AED', '#D97706', '#16A34A', '#DC2626', '#0891B2', '#C026D3'];

$navInicial = strtoupper(substr($_SESSION['usuario'], 0, 1));
$navNombre  = htmlspecialchars($_SESSION['nombre']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>For Life S.A. — Panel de Administración</title>
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
                <span class="nav-badge-admin">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:11px;height:11px"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Panel Admin
                </span>
            </div>

            <nav class="nav-centro">
                <a href="admin.php" class="nav-item activo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Usuarios
                </a>
                <a href="#seccion-logs" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Logs
                </a>
            </nav>

            <div class="nav-derecha">
                <div class="nav-avatar"><?= $navInicial ?></div>
                <span class="nav-usuario-nombre"><?= $navNombre ?></span>
                <a href="logout.php" class="btn-salir">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Cerrar sesión
                </a>
            </div>
        </header>

        <!-- CONTENIDO -->
        <main class="contenido-principal">

            <?php if (!empty($mensaje)): ?>
                <div class="alerta alerta-<?= $tipoMensaje ?>"><?= $mensaje ?></div>
            <?php endif; ?>

            <!-- Cabecera de página -->
            <div class="page-header">
                <div class="page-titulo">
                    <h1>Panel de Administración</h1>
                    <p>Gestión de usuarios y registro de accesos del sistema</p>
                </div>
            </div>

            <!-- Tabla de usuarios -->
            <div class="tabla-card" id="seccion-usuarios">
                <div class="tabla-card-header">
                    <div class="tabla-card-titulo">
                        <span>Gestión de Usuarios</span>
                        <span class="tabla-badge-count"><?= count($usuarios) ?> registros</span>
                    </div>
                    <input type="text" class="tabla-search" placeholder="Buscar usuario..." id="buscadorUsuarios">
                </div>
                <div class="tabla-contenedor">
                    <table class="tabla-admin" id="tablaUsuarios">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Registro</th>
                                <th>Último acceso</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u):
                                $iniciales    = strtoupper(substr($u['nombre'], 0, 1) . substr($u['apellido1'], 0, 1));
                                $colorAvatar  = $coloresAvatar[$u['id'] % count($coloresAvatar)];
                                $nombreCompleto = htmlspecialchars($u['nombre'] . ' ' . $u['apellido1'] . ' ' . $u['apellido2']);
                            ?>
                            <tr class="<?= $u['activo'] ? '' : 'fila-inactiva' ?>">
                                <td>
                                    <div class="celda-usuario">
                                        <div class="usuario-avatar-sm" style="background:<?= $colorAvatar ?>">
                                            <?= $iniciales ?>
                                        </div>
                                        <div class="celda-usuario-info">
                                            <span class="celda-usuario-nombre"><?= $nombreCompleto ?></span>
                                            <span class="celda-usuario-sub">#<?= $u['id'] ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($u['usuario']) ?></td>
                                <td>
                                    <span class="badge-rol badge-<?= $u['rol'] ?>">
                                        <?= ucfirst($u['rol']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-estado <?= $u['activo'] ? 'activo' : 'inactivo' ?>">
                                        <?= $u['activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td><?= $u['fecha_registro'] ?></td>
                                <td><?= $u['ultimo_acceso'] ?? '—' ?></td>
                                <td>
                                    <?php if ($u['rol'] !== 'admin'): ?>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <input type="hidden" name="accion" value="<?= $u['activo'] ? 'desactivar' : 'activar' ?>">
                                            <button
                                                type="submit"
                                                class="btn-accion <?= $u['activo'] ? 'btn-desactivar' : 'btn-activar' ?>"
                                                onclick="return confirm('¿Confirma esta acción?')"
                                            >
                                                <?= $u['activo'] ? 'Desactivar' : 'Activar' ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="texto-gris">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Logs de acceso -->
            <div class="tabla-card" id="seccion-logs">
                <div class="tabla-card-header">
                    <div class="tabla-card-titulo">
                        <span>Registro de Accesos</span>
                        <span class="tabla-badge-count">Últimos 50</span>
                    </div>
                </div>
                <div class="tabla-contenedor">
                    <table class="tabla-admin">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Resultado</th>
                                <th>IP de origen</th>
                                <th>Fecha y hora</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['usuario_texto']) ?></td>
                                <td>
                                    <span class="badge-estado <?= $log['resultado'] === 'exitoso' ? 'activo' : 'inactivo' ?>">
                                        <?= ucfirst($log['resultado']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($log['ip_origen']) ?></td>
                                <td><?= $log['fecha_intento'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>

    </div>
    <script src="../assets/js/app.js"></script>
</body>
</html>
