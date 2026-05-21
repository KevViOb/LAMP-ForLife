<?php

require_once __DIR__ . '/../config/Conexion.php';

/**
 * Clase Usuario
 * 
 * Contiene toda la lógica de negocio relacionada con usuarios:
 * registro, autenticación, gestión de sesión y administración.
 * Esta capa no sabe nada de HTML ni de cómo se presentan los datos.
 * 
 * @author Grupo 1 — Sistemas Operativos ULACIT
 * @version 1.0
 */
class Usuario {

    private mysqli $db;

    public function __construct() {
        $this->db = Conexion::obtenerInstancia()->getConexion();
    }

    /**
     * Registra un nuevo usuario en el sistema.
     * Aplica hash bcrypt a la contraseña antes de guardarla.
     * Retorna true si el registro fue exitoso, false si el usuario ya existe.
     * 
     * @throws RuntimeException si ocurre un error inesperado en la BD
     */
    public function registrar(
        string $usuario,
        string $nombre,
        string $apellido1,
        string $apellido2,
        string $password
    ): bool {

        if ($this->usuarioExiste($usuario)) {
            return false;
        }

        $hashPassword = password_hash($password, PASSWORD_BCRYPT);

        // Columnas explícitas, nunca INSERT sin especificar campos
        $sql = "INSERT INTO usuarios (usuario, nombre, apellido1, apellido2, password_hash, rol)
                VALUES (?, ?, ?, ?, ?, 'usuario')";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('sssss', $usuario, $nombre, $apellido1, $apellido2, $hashPassword);
            $stmt->execute();
            $stmt->close();
            return true;
        } catch (mysqli_sql_exception $e) {
            error_log('Error al registrar usuario: ' . $e->getMessage());
            throw new RuntimeException('Error al registrar el usuario.');
        }
    }

    /**
     * Autentica un usuario verificando su contraseña contra el hash almacenado.
     * Registra el intento en logs_acceso independientemente del resultado.
     * Retorna los datos del usuario si es válido, null si las credenciales fallan.
     */
    public function autenticar(string $usuario, string $password, string $ip): ?array {
        $sql = "SELECT id, usuario, nombre, apellido1, apellido2, password_hash, rol, activo
                FROM usuarios
                WHERE usuario = ?";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $datos = $resultado->fetch_assoc();
            $stmt->close();

            // Verificar que el usuario existe, está activo y la contraseña es correcta
            if ($datos === null || !$datos['activo'] || !password_verify($password, $datos['password_hash'])) {
                $idUsuario = $datos['id'] ?? null;
                $this->registrarAcceso($idUsuario, $usuario, 'fallido', $ip);
                return null;
            }

            $this->registrarAcceso($datos['id'], $usuario, 'exitoso', $ip);
            $this->actualizarUltimoAcceso($datos['id']);

            // No retornar el hash de la contraseña a las capas superiores
            unset($datos['password_hash']);
            return $datos;

        } catch (mysqli_sql_exception $e) {
            error_log('Error al autenticar usuario: ' . $e->getMessage());
            throw new RuntimeException('Error en el proceso de autenticación.');
        }
    }

    /**
     * Verifica si un nombre de usuario ya está registrado.
     * Se usa antes del INSERT para dar un mensaje claro al usuario.
     */
    public function usuarioExiste(string $usuario): bool {
        $sql = "SELECT id FROM usuarios WHERE usuario = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $stmt->store_result();
        $existe = $stmt->num_rows > 0;
        $stmt->close();

        return $existe;
    }

    /**
     * Retorna la lista completa de usuarios para el panel de administración.
     * Solo accesible desde el controlador cuando el rol es 'admin'.
     */
    public function obtenerTodos(): array {
        $sql = "SELECT id, usuario, nombre, apellido1, apellido2, rol, activo, fecha_registro, ultimo_acceso
                FROM usuarios
                ORDER BY fecha_registro DESC";

        try {
            $resultado = $this->db->query($sql);
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $e) {
            error_log('Error al obtener usuarios: ' . $e->getMessage());
            throw new RuntimeException('Error al obtener la lista de usuarios.');
        }
    }

    /**
     * Cambia el estado activo/inactivo de un usuario.
     * El administrador usa esto para bloquear cuentas sin eliminarlas.
     */
    public function cambiarEstado(int $id, int $estado): bool {
        $sql = "UPDATE usuarios SET activo = ? WHERE id = ? AND rol = 'usuario'";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('ii', $estado, $id);
            $stmt->execute();
            $afectados = $stmt->affected_rows;
            $stmt->close();
            return $afectados > 0;
        } catch (mysqli_sql_exception $e) {
            error_log('Error al cambiar estado de usuario: ' . $e->getMessage());
            throw new RuntimeException('Error al actualizar el estado del usuario.');
        }
    }

    /**
     * Retorna los últimos registros de logs de acceso para el panel admin.
     * Útil para detectar intentos de acceso fallidos repetidos.
     */
    public function obtenerLogsAcceso(int $limite = 50): array {
        $sql = "SELECT l.id, l.usuario_texto, l.resultado, l.ip_origen, l.fecha_intento
                FROM logs_acceso l
                ORDER BY l.fecha_intento DESC
                LIMIT ?";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $limite);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $stmt->close();
            return $resultado->fetch_all(MYSQLI_ASSOC);
        } catch (mysqli_sql_exception $e) {
            error_log('Error al obtener logs: ' . $e->getMessage());
            throw new RuntimeException('Error al obtener los registros de acceso.');
        }
    }

    /**
     * Registra cada intento de login en la tabla de auditoría.
     * Se llama siempre, sin importar si el login fue exitoso o no.
     */
    private function registrarAcceso(?int $usuarioId, string $usuarioTexto, string $resultado, string $ip): void {
        $sql = "INSERT INTO logs_acceso (usuario_id, usuario_texto, resultado, ip_origen)
                VALUES (?, ?, ?, ?)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('isss', $usuarioId, $usuarioTexto, $resultado, $ip);
            $stmt->execute();
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            // No interrumpir el flujo principal si el log falla
            error_log('Error al registrar log de acceso: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza la marca de tiempo del último acceso exitoso del usuario.
     */
    private function actualizarUltimoAcceso(int $id): void {
        $sql = "UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = ?";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            error_log('Error al actualizar último acceso: ' . $e->getMessage());
        }
    }
}
