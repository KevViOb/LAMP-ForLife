<?php

/**
 * Punto de entrada principal del sistema For Life, S.A.
 * Solo enruta al destino correcto según el estado de la sesión.
 * No contiene lógica de negocio ni HTML directamente.
 */

session_start();

// Si ya hay sesión activa, redirigir según el rol
if (isset($_SESSION['usuario'])) {
    if ($_SESSION['rol'] === 'admin') {
        header('Location: vista/admin.php');
    } else {
        header('Location: vista/bienvenida.php');
    }
    exit;
}

// Sin sesión activa, mostrar el login
header('Location: vista/login.php');
exit;
