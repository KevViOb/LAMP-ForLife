<?php

/**
 * Clase Conexion
 * 
 * Maneja la conexión a la base de datos usando el patrón Singleton.
 * Solo existe una instancia de conexión durante toda la ejecución,
 * lo que evita abrir múltiples conexiones innecesarias a MySQL.
 * 
 * @author Grupo 1 — Sistemas Operativos ULACIT
 * @version 1.0
 */
class Conexion {

    // Datos de conexión como constantes para facilitar cambios futuros
    private const DB_HOST     = '127.0.0.1';
    private const DB_NOMBRE   = 'forlife_db';
    private const DB_USUARIO  = 'forlife_user';
    private const DB_PASSWORD = 'TU_PASSWORD_AQUI';
    private const DB_CHARSET  = 'utf8mb4';

    private static ?Conexion $instancia = null;
    private mysqli $conexion;

    /**
     * Constructor privado para forzar el uso de obtenerInstancia().
     * Establece la conexión real con MySQL y lanza excepción si falla.
     */
    private function __construct() {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $this->conexion = new mysqli(
                self::DB_HOST,
                self::DB_USUARIO,
                self::DB_PASSWORD,
                self::DB_NOMBRE
            );
            $this->conexion->set_charset(self::DB_CHARSET);
        } catch (mysqli_sql_exception $e) {
            // No exponer detalles internos al usuario, solo registrar
            error_log('Error de conexión a la base de datos: ' . $e->getMessage());
            throw new RuntimeException('No se pudo conectar a la base de datos.');
        }
    }

    /**
     * Retorna la única instancia de conexión disponible.
     * Si no existe, la crea. Si ya existe, la reutiliza.
     */
    public static function obtenerInstancia(): Conexion {
        if (self::$instancia === null) {
            self::$instancia = new Conexion();
        }
        return self::$instancia;
    }

    /**
     * Expone el objeto mysqli para usarlo en consultas.
     * Solo el modelo accede a este método, nunca las vistas.
     */
    public function getConexion(): mysqli {
        return $this->conexion;
    }

    // Evitar clonación y deserialización del Singleton
    private function __clone() {}
    public function __wakeup() {
        throw new RuntimeException('No se puede deserializar el Singleton de conexión.');
    }
}
