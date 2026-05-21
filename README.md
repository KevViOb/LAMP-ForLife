# Sistema de Autenticación — For Life S.A.

Aplicación web desarrollada en PHP con arquitectura en capas (MVC simplificado) para gestionar el registro e inicio de sesión de usuarios de For Life S.A.

## Estructura del proyecto

```
forlife/
├── config/
│   ├── Conexion.php          # Singleton de conexión a MySQL
│   └── schema.sql            # Esquema completo de la base de datos
├── modelo/
│   └── Usuario.php           # Lógica de negocio y acceso a datos
├── controlador/
│   └── AuthControlador.php   # Intermediario entre vistas y modelo
├── vista/
│   ├── login.php             # Formulario de inicio de sesión
│   ├── registro.php          # Formulario de registro
│   ├── bienvenida.php        # Página del usuario autenticado
│   ├── admin.php             # Panel de administración
│   └── logout.php            # Cierre de sesión
├── assets/
│   ├── css/
│   │   └── estilos.css       # Estilos del frontend
│   └── img/
│       └── logo.png          # Logo de For Life S.A.
└── index.php                 # Punto de entrada y enrutador
```

## Capas de la arquitectura

**Config** — solo maneja la conexión a MySQL. Usa Singleton para mantener una única instancia durante la ejecución.

**Modelo** — contiene toda la lógica de negocio. Nunca recibe datos del formulario directamente ni genera HTML.

**Controlador** — valida y sanitiza los inputs antes de pasarlos al modelo. Es el único que conoce tanto la vista como el modelo.

**Vista** — solo presenta datos y envía formularios. Nunca accede a la base de datos directamente.

## Seguridad implementada

- Contraseñas almacenadas con `password_hash()` usando bcrypt
- Consultas preparadas con `bind_param()` en todas las interacciones con MySQL
- Sanitización de inputs con `htmlspecialchars()` antes de mostrar en HTML
- Regeneración de ID de sesión al autenticar (`session_regenerate_id`)
- Logs de auditoría de todos los intentos de acceso
- Protección contra acceso directo a vistas sin sesión activa

## Requisitos del servidor

- PHP 8.x con extensión mysqli
- MySQL 8.0
- Apache con mod_php, mod_ssl y mod_security
- OPcache habilitado en php.ini

## Instalación

1. Copiar la carpeta `forlife/` en `/var/www/html/`
2. Importar `config/schema.sql` en MySQL
3. Ajustar credenciales en `config/Conexion.php` si es necesario
4. Acceder desde el navegador a `https://192.168.100.10/forlife/`

## Credenciales del administrador inicial

```
Usuario:    admin
Contraseña: Admin2025#
```

**Cambiar la contraseña del administrador antes de la demostración.**

---

Grupo 1 — Sistemas Operativos — ULACIT 2026
