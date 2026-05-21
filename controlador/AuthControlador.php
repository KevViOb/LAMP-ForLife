<?php

require_once __DIR__ . '/../modelo/Usuario.php';

/**
 * Clase AuthControlador
 * 
 * Intermediario entre las vistas y el modelo Usuario.
 * Recibe los datos del formulario, los valida y delega al modelo.
 * Las vistas nunca acceden al modelo directamente.
 * 
 * @author Grupo 1 — Sistemas Operativos ULACIT
 * @version 1.0
 */
class AuthControlador {

    private Usuario $modelo;

    // Longitudes permitidas para los campos del formulario
    private const MIN_USUARIO  = 4;
    private const MAX_USUARIO  = 50;
    private const MIN_PASSWORD = 8;
    private const MAX_NOMBRE   = 100;

    public function __construct() {
        $this->modelo = new Usuario();
    }

    /**
     * Procesa el formulario de registro.
     * Valida los campos antes de intentar guardar en la BD.
     * Retorna array con 'exito' (bool) y 'mensaje' (string).
     */
    public function procesarRegistro(array $datos): array {
        $usuario   = $this->sanitizar($datos['usuario'] ?? '');
        $nombre    = $this->sanitizar($datos['nombre'] ?? '');
        $apellido1 = $this->sanitizar($datos['apellido1'] ?? '');
        $apellido2 = $this->sanitizar($datos['apellido2'] ?? '');
        $password  = $datos['password'] ?? '';
        $confirmar = $datos['confirmar'] ?? '';

        $errores = $this->validarRegistro($usuario, $nombre, $apellido1, $apellido2, $password, $confirmar);

        if (!empty($errores)) {
            return ['exito' => false, 'mensaje' => implode(' ', $errores)];
        }

        try {
            $registrado = $this->modelo->registrar($usuario, $nombre, $apellido1, $apellido2, $password);

            if (!$registrado) {
                return ['exito' => false, 'mensaje' => 'El nombre de usuario ya está en uso.'];
            }

            return ['exito' => true, 'mensaje' => 'Registro exitoso. Puede iniciar sesión.'];

        } catch (RuntimeException $e) {
            return ['exito' => false, 'mensaje' => 'Error interno. Intente nuevamente.'];
        }
    }

    /**
     * Procesa el formulario de login.
     * Si las credenciales son válidas, inicia la sesión del usuario.
     * Retorna array con 'exito' (bool) y 'mensaje' (string).
     */
    public function procesarLogin(array $datos): array {
        $usuario  = $this->sanitizar($datos['usuario'] ?? '');
        $password = $datos['password'] ?? '';
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (empty($usuario) || empty($password)) {
            return ['exito' => false, 'mensaje' => 'Complete todos los campos.'];
        }

        try {
            $datosUsuario = $this->modelo->autenticar($usuario, $password, $ip);

            if ($datosUsuario === null) {
                return ['exito' => false, 'mensaje' => 'Usuario o contraseña incorrectos.'];
            }

            $this->iniciarSesion($datosUsuario);
            return ['exito' => true, 'mensaje' => 'Acceso autorizado.'];

        } catch (RuntimeException $e) {
            return ['exito' => false, 'mensaje' => 'Error interno. Intente nuevamente.'];
        }
    }

    /**
     * Cierra la sesión activa del usuario limpiando todos los datos de sesión.
     */
    public function cerrarSesion(): void {
        session_start();
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Retorna la lista de usuarios para el panel de administración.
     * Solo debe llamarse cuando el rol de la sesión activa sea 'admin'.
     */
    public function obtenerUsuarios(): array {
        try {
            return $this->modelo->obtenerTodos();
        } catch (RuntimeException $e) {
            return [];
        }
    }

    /**
     * Retorna los logs de acceso para el panel de administración.
     */
    public function obtenerLogs(): array {
        try {
            return $this->modelo->obtenerLogsAcceso();
        } catch (RuntimeException $e) {
            return [];
        }
    }

    /**
     * Cambia el estado activo/inactivo de un usuario desde el panel admin.
     */
    public function cambiarEstadoUsuario(int $id, int $estado): bool {
        try {
            return $this->modelo->cambiarEstado($id, $estado);
        } catch (RuntimeException $e) {
            return false;
        }
    }

    /**
     * Inicia la sesión PHP con los datos del usuario autenticado.
     * Regenera el ID de sesión para prevenir ataques de fijación de sesión.
     */
    private function iniciarSesion(array $datos): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Regenerar ID previene session fixation attacks
        session_regenerate_id(true);

        $_SESSION['id']        = $datos['id'];
        $_SESSION['usuario']   = $datos['usuario'];
        $_SESSION['nombre']    = $datos['nombre'];
        $_SESSION['apellido1'] = $datos['apellido1'];
        $_SESSION['apellido2'] = $datos['apellido2'];
        $_SESSION['rol']       = $datos['rol'];
    }

    /**
     * Valida todos los campos del formulario de registro.
     * Retorna un array de mensajes de error (vacío si todo es válido).
     */
    private function validarRegistro(
        string $usuario,
        string $nombre,
        string $apellido1,
        string $apellido2,
        string $password,
        string $confirmar
    ): array {
        $errores = [];

        if (strlen($usuario) < self::MIN_USUARIO || strlen($usuario) > self::MAX_USUARIO) {
            $errores[] = 'El usuario debe tener entre ' . self::MIN_USUARIO . ' y ' . self::MAX_USUARIO . ' caracteres.';
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $usuario)) {
            $errores[] = 'El usuario solo puede contener letras, números y guión bajo.';
        }

        if (empty($nombre) || strlen($nombre) > self::MAX_NOMBRE) {
            $errores[] = 'El nombre es obligatorio y no puede superar ' . self::MAX_NOMBRE . ' caracteres.';
        }

        if (empty($apellido1) || strlen($apellido1) > self::MAX_NOMBRE) {
            $errores[] = 'El primer apellido es obligatorio.';
        }

        if (empty($apellido2) || strlen($apellido2) > self::MAX_NOMBRE) {
            $errores[] = 'El segundo apellido es obligatorio.';
        }

        if (strlen($password) < self::MIN_PASSWORD) {
            $errores[] = 'La contraseña debe tener al menos ' . self::MIN_PASSWORD . ' caracteres.';
        }

        if ($password !== $confirmar) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        return $errores;
    }

    /**
     * Elimina caracteres peligrosos de los inputs del formulario.
     * Complementa la protección de las consultas preparadas en el modelo.
     */
    private function sanitizar(string $valor): string {
        return trim(htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'));
    }
}
