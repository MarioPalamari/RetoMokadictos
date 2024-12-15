# MokAdictos - Sistema de Gestión de Restaurante

## Descripción
MokAdictos es una aplicación web diseñada para la gestión de mesas y reservas de un restaurante. Permite administrar diferentes espacios (terrazas, salones y salas VIP) así como gestionar usuarios y visualizar el historial de ocupaciones.

## Características Principales

- Gestión de mesas en diferentes áreas:
  - 3 Terrazas
  - 2 Salones principales
  - 4 Salas VIP
- Sistema de reservas
- Control de ocupación de mesas en tiempo real
- Panel de administración
- Historial de ocupaciones
- Gestión de usuarios (camareros y administradores)

## Usuarios de Prueba

### Administrador
- Usuario: `Admin`
- Contraseña: `qweQWE123`

### Camarero
- Usuario: `MPalamari`
- Contraseña: `qweQWE123`

## Estado de las Mesas

Las mesas se muestran con diferentes colores según su estado:
- Verde: Mesa libre
- Rojo: Mesa ocupada
- Amarillo: Mesa con reserva

## Funcionalidades por Tipo de Usuario

### Administrador
- Gestión completa de usuarios
- Visualización del historial de ocupaciones
- Acceso a todas las funcionalidades de camarero

### Camarero
- Ocupar/liberar mesas
- Realizar reservas
- Consultar reservas existentes
- Gestionar sus propias reservas

## Instalación

1. Clonar el repositorio
2. Importar la base de datos desde `BBDD/bbdd.sql`
3. Configurar la conexión a la base de datos en `conexion/conexion.php`
4. Asegurarse que el servidor web tiene permisos de escritura en las carpetas necesarias

## Estructura de la Base de Datos

La base de datos `db_mokadictos` incluye las siguientes tablas principales:
- `tbl_users`: Usuarios del sistema
- `tbl_roles`: Roles de usuario
- `tbl_tables`: Mesas del restaurante
- `tbl_reservations`: Reservas
- `tbl_occupations`: Historial de ocupaciones

## Notas Importantes

- Las reservas deben realizarse con al menos 1 día de antelación
- Una mesa ocupada no puede ser reservada
- Solo los administradores pueden eliminar usuarios y ver el historial completo
- Las contraseñas están encriptadas en la base de datos

## Soporte

Para reportar problemas o sugerir mejoras, por favor crear un issue en el repositorio.
