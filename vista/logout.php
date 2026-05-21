<?php

/**
 * Cierre de sesión del sistema.
 * Destruye la sesión activa y redirige al login.
 */

require_once __DIR__ . '/../controlador/AuthControlador.php';

$controlador = new AuthControlador();
$controlador->cerrarSesion();

header('Location: login.php');
exit;
