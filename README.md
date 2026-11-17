# Registro Académico – AUNAR Villavicencio

Sistema web para registrar, consultar, buscar, editar y eliminar estudiantes, con inicio de sesión para un operador.
Proyecto de la asignatura **Programación Web II** (Encuentros 12 a 16), migrado de PHP nativo a **Laravel**.

Cada estudiante tiene cuatro campos académicos:

| Campo | Regla |
|---|---|
| `nombre` | Obligatorio, entre 2 y 80 caracteres |
| `correo` | Correo válido, máximo 120 caracteres, único |
| `edad` | Número entero entre 16 y 100 |
| `programa` | Debe estar en el catálogo de `config/programas.php` (**lista provisional**) |

## Tecnologías

- PHP 8.3 y Laravel 13 (MVC, Eloquent, Blade, migraciones, seeders)
- MySQL 8
- Bootstrap 5.3 (desde CDN, sin Node ni compilación)
- PHPUnit para las pruebas automáticas
- Git

## Requisitos

- PHP 8.3 o superior con las extensiones `mbstring`, `xml`, `curl`, `zip`, `pdo_mysql` y `pdo_sqlite`
- Composer 2
- MySQL 8 en funcionamiento

En Ubuntu:

```bash
sudo apt install composer php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-mysql php8.3-sqlite3
```

## Instalación desde una copia limpia

```bash
# 1. Entrar a la carpeta del proyecto
cd registro-academico

# 2. Instalar dependencias de PHP (crea la carpeta vendor/)
composer install

# 3. Crear el archivo de configuración y la clave de la aplicación
cp .env.example .env
php artisan key:generate
```

### Configurar MySQL

Crea la base de datos (solo la primera vez):

```bash
mysql -u root -p -e "CREATE DATABASE registro_academico CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Edita `.env` y completa los datos de conexión:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=registro_academico
DB_USERNAME=root
DB_PASSWORD="tu_contraseña_de_mysql"
```

Crea las tablas con las migraciones:

```bash
php artisan migrate
```

> No uses `migrate:fresh` ni `migrate:reset` sobre una base con datos reales: borran todas las tablas.

### Preparar la cuenta del operador

En `.env` escribe el correo y la contraseña que tendrá el operador:

```dotenv
OPERADOR_NOMBRE="Operador"
OPERADOR_EMAIL=operador@aunar.local
OPERADOR_PASSWORD="elige_una_clave_segura"
```

Luego ejecuta el seeder. Crea el usuario, o lo actualiza si ya existe, y guarda la contraseña con hash (bcrypt):

```bash
php artisan db:seed
```

## Iniciar el servidor

```bash
php artisan serve
```

Abre <http://127.0.0.1:8000> en el navegador. Si no hay sesión, el sistema te lleva a `/login`.
Entra con el `OPERADOR_EMAIL` y la `OPERADOR_PASSWORD` del `.env`.

## Ejecutar las pruebas

```bash
php artisan test
```

Las pruebas automáticas usan una base SQLite **en memoria** (configurada en `phpunit.xml`), así que **no tocan MySQL**.
El detalle de las pruebas manuales y automáticas está en [`registro-pruebas.md`](registro-pruebas.md).

## Estructura principal (MVC)

| Archivo | Qué hace |
|---|---|
| `routes/web.php` | Define las rutas y protege las privadas con el middleware `auth` |
| `app/Http/Controllers/EstudianteController.php` | CRUD, búsqueda, paginación y validación |
| `app/Http/Controllers/SesionController.php` | Inicio y cierre de sesión |
| `app/Models/Estudiante.php` | Modelo Eloquent de la tabla `estudiantes` |
| `app/Models/User.php` | Modelo del operador (contraseña con hash) |
| `database/migrations/*_create_estudiantes_table.php` | Estructura de la tabla `estudiantes` |
| `database/seeders/OperadorSeeder.php` | Crea la cuenta del operador desde `.env` |
| `config/programas.php` | Catálogo de programas permitidos (provisional) |
| `resources/views/` | Vistas Blade con Bootstrap 5 |
| `tests/Feature/` | Pruebas automáticas |

## Seguridad

- Consultas a través de Eloquent (consultas preparadas, sin SQL concatenado).
- Validación en el servidor para todos los campos.
- Token CSRF (`@csrf`) en todos los formularios: una petición sin token recibe el error 419.
- Contraseña del operador guardada con hash. El inicio de sesión tiene límite de 5 intentos por minuto.
- Rutas privadas protegidas con `auth`.
- Datos escapados automáticamente en Blade con `{{ }}`.
- Las credenciales están solo en `.env`, que está excluido del repositorio. `.env.example` no lleva contraseñas.
