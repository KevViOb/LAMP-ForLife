# ForLife S.A. — Sistema de Autenticación Web
### Aplicación web en PHP sobre stack LAMP con arquitectura MVC y seguridad en capas

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-2.4-D22128?style=flat&logo=apache&logoColor=white)
![Linux](https://img.shields.io/badge/Linux-Ubuntu_Server-E95420?style=flat&logo=ubuntu&logoColor=white)
![Status](https://img.shields.io/badge/status-proyecto%20académico-lightgrey?style=flat)

---

## Qué es esto

Sistema de autenticación de usuarios desarrollado como proyecto final del curso de Sistemas Operativos en ULACIT. El objetivo central fue desplegar una aplicación web funcional sobre un servidor Linux configurado desde cero, aplicando los conceptos del curso: gestión de servicios, permisos, seguridad del sistema operativo y administración de servidor.

La aplicación en sí implementa registro, login, panel de administración y auditoría de accesos, siguiendo una arquitectura MVC clara donde cada capa tiene una responsabilidad única y no se mezclan con las demás.

---

## Por qué se eligió esta arquitectura

| Capa | Responsabilidad | Por qué separada |
|---|---|---|
| **Config** | Conexión a MySQL con patrón Singleton | Una sola instancia durante la ejecución — evita abrir múltiples conexiones innecesarias |
| **Modelo** | Lógica de negocio y acceso a datos | Nunca recibe inputs del formulario ni genera HTML — desacoplada de la presentación |
| **Controlador** | Validación, sanitización y enrutamiento | Único punto que conoce tanto la vista como el modelo — coordina sin mezclar responsabilidades |
| **Vista** | Presentación y formularios | Solo muestra datos y envía formularios — nunca accede directamente a la base de datos |

---

## Seguridad implementada

- Contraseñas almacenadas con `password_hash()` usando bcrypt — nunca en texto plano
- Consultas preparadas con `bind_param()` en todas las interacciones con MySQL — sin riesgo de inyección SQL
- Sanitización de outputs con `htmlspecialchars()` antes de renderizar en HTML — protección contra XSS
- Regeneración de ID de sesión al autenticar con `session_regenerate_id()` — previene session fixation
- Logs de auditoría de todos los intentos de acceso, exitosos y fallidos
- Protección contra acceso directo a vistas sin sesión activa

---

## Estructura del proyecto

```
forlife/
├── .gitignore
├── index.php                          ← punto de entrada y enrutador principal
├── config/
│   ├── Conexion.php                   ← Singleton de conexión a MySQL
│   └── schema.sql                     ← esquema completo de la base de datos
├── modelo/
│   └── Usuario.php                    ← lógica de negocio: registro, autenticación, auditoría
├── controlador/
│   └── AuthControlador.php            ← valida inputs y coordina modelo con vistas
├── vista/
│   ├── login.php                      ← formulario de inicio de sesión
│   ├── registro.php                   ← formulario de registro de usuario
│   ├── bienvenida.php                 ← página del usuario autenticado
│   ├── admin.php                      ← panel de administración con logs de acceso
│   └── logout.php                     ← cierre de sesión seguro
└── assets/
    ├── css/estilos.css                ← estilos del frontend
    └── img/logo.svg                   ← logo de For Life S.A.
```

---

## Cómo instalar

**Requisitos:** Linux con Apache 2.4, PHP 8.x (`mysqli` habilitado), MySQL 8.0

```bash
# Copiar la aplicación al directorio del servidor
sudo cp -r forlife/ /var/www/html/

# Importar el esquema de la base de datos
mysql -u root -p < forlife/config/schema.sql

# Ajustar credenciales de conexión
nano /var/www/html/forlife/config/Conexion.php
```

Acceder desde el navegador a `https://<ip-del-servidor>/forlife/`

---

## Decisiones de diseño relevantes

- **Singleton en la capa de config:** `Conexion` usa constructor privado y método estático `obtenerInstancia()` — garantiza que solo exista una conexión durante toda la ejecución, sin importar cuántos objetos la soliciten.
- **Modelo sin HTML:** `Usuario.php` nunca imprime ni genera HTML — solo retorna datos o lanza excepciones. Esto permite cambiar la capa de presentación sin tocar la lógica.
- **Contraseña nunca sale del modelo:** `autenticar()` hace `unset($datos['password_hash'])` antes de retornar al controlador — el hash nunca llega a la sesión ni a las vistas.
- **Logs siempre, sin excepción:** `registrarAcceso()` se llama tanto en login exitoso como fallido — el administrador puede detectar ataques de fuerza bruta sin depender de si el usuario existe o no.

---

## Contexto académico

**Curso:** Sistemas Operativos  
**Institución:** Universidad Latinoamericana de Ciencia y Tecnología (ULACIT), San José, Costa Rica  
**Grupo:** 1  
**Ciclo:** 2026  

---

## Autor

**Kevin Villalobos Obando**  
Estudiante de Ingeniería Informática en ULACIT  
villaloboskev@outlook.es
