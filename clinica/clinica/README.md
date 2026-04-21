# Clinica

Sistema web para administracion de pacientes, citas e historial clinico, desarrollado con Laravel 12.

## Stack

- PHP 8.2
- Laravel 12
- MySQL 8
- Vite
- Tailwind CSS
- Docker / Docker Desktop

## Modulos actuales

- Autenticacion con Laravel Breeze
- Roles de usuario:
  - `admin`
  - `doctor`
  - `paciente`
- Gestion de pacientes
- Gestion de citas
- Portal del paciente
- Historial clinico:
  - consultas
  - observaciones
  - archivos adjuntos simples

## Estado funcional

Actualmente el proyecto contempla:

- CRUD de pacientes
- CRUD operativo de citas
- Portal para paciente autenticado
- Registro y consulta de historial clinico por paciente

Fuera de alcance actual:

- metodo de pago en produccion

Existe una tabla/modelo de `pagos` por trabajo previo, pero no forma parte del alcance activo del proyecto.

## Estructura principal

```text
app/
  Http/Controllers/
  Http/Requests/
  Models/
database/
  migrations/
resources/
  views/
routes/
  web.php
docker/
  app-entrypoint.sh
```

## Como levantar el proyecto con Docker

### Opcion rapida en Windows

```bat
docker-up.bat
```

### Opcion manual

1. Crear `.env.docker` si no existe:

```powershell
Copy-Item .env.docker.example .env.docker
```

2. Levantar contenedores:

```powershell
docker compose --env-file .env.docker up --build -d
```

3. Abrir la app:

```text
http://localhost:8000
```

4. Ver logs si hace falta:

```powershell
docker compose --env-file .env.docker logs -f
```

5. Detener el entorno:

```powershell
docker compose --env-file .env.docker down
```

## Muy importante sobre Docker Desktop

Este proyecto usa un bind mount con la carpeta actual del repositorio.

La ruta correcta de este entorno es:

```text
C:\Users\tempo\OneDrive - UVG\Escritorio\UVG\ciclo 5\clinica\clinica\clinica
```

Si Docker Desktop intenta arrancar un proyecto viejo desde una ruta distinta, por ejemplo una carpeta en `OneDrive\Desktop`, el contenedor `app` puede fallar con errores como:

```text
cp: cannot stat '.env.example': No such file or directory
```

Eso significa que Docker monto una carpeta equivocada o incompleta.

### Si pasa ese error

1. Abre una terminal en la carpeta real del proyecto.
2. Ejecuta:

```powershell
docker compose --env-file .env.docker down --remove-orphans
docker compose --env-file .env.docker up --build -d
```

3. Verifica que la app responda:

```powershell
Invoke-WebRequest -UseBasicParsing http://localhost:8000/up
```

Si responde `200`, Laravel quedo arriba correctamente.

## Que hace el arranque de Docker

El `entrypoint` del contenedor `app`:

- valida que los archivos del proyecto esten montados
- crea `.env` si no existe
- instala dependencias de Composer si faltan
- instala dependencias de Node si faltan
- genera `APP_KEY` si falta
- espera a MySQL
- corre migraciones
- compila assets si hace falta
- levanta `php artisan serve`

Archivo involucrado:

- [docker/app-entrypoint.sh](docker/app-entrypoint.sh)

## Variables importantes

El entorno Docker usa `.env.docker`.

Valores relevantes por defecto:

```env
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=clinica_db
DB_USERNAME=clinica_user
DB_PASSWORD=clinica_pass
MYSQL_HOST_PORT=3307
```

## Base de datos

Migraciones principales del proyecto:

- `users`
- `pacientes`
- `citas`
- `historiales`
- `consultas`
- `observaciones`
- `archivos`

Las migraciones se ejecutan automaticamente al levantar Docker.

Si necesitas correrlas manualmente dentro del contenedor:

```powershell
docker compose --env-file .env.docker exec app php artisan migrate
```

## Credenciales de prueba

El seeder actual crea un usuario administrador:

- correo: `test@example.com`
- password: `password`
- rol: `admin`

Para sembrar la base:

```powershell
docker compose --env-file .env.docker exec app php artisan db:seed
```

## Historial clinico

El backend del historial clinico incluye:

- `Consulta`
  - pertenece a `Paciente`
  - pertenece a `User`
  - tiene muchas `Observacion`
  - tiene muchos `Archivo`
- `Observacion`
  - pertenece a `Consulta`
- `Archivo`
  - pertenece a `Consulta`

Flujo actual:

- `admin` y `doctor` pueden crear consultas para un paciente
- `paciente` solo puede ver su propio historial

Rutas principales:

- `GET /pacientes/{paciente}/consultas`
- `GET /pacientes/{paciente}/consultas/create`
- `POST /pacientes/{paciente}/consultas`
- `GET /consultas/{consulta}`
- `GET /portal/historial-clinico`
- `GET /portal/historial-clinico/{consulta}`

## Archivos adjuntos

Se permiten archivos simples:

- PDF
- JPG
- JPEG
- PNG
- WEBP

Para exponerlos correctamente en un entorno local sin Docker, crea el symlink de storage:

```powershell
php artisan storage:link
```

Con Docker:

```powershell
docker compose --env-file .env.docker exec app php artisan storage:link
```

## Pruebas

Ejecutar pruebas:

```powershell
php artisan test
```

Con Docker:

```powershell
docker compose --env-file .env.docker exec app php artisan test
```

La suite actual cubre:

- autenticacion
- acceso por roles
- visibilidad de citas
- integridad de pagos existente
- historial clinico

## Desarrollo sin Docker

Si quieres correrlo localmente:

1. Instala dependencias:

```powershell
composer install
npm install
```

2. Crea `.env`:

```powershell
Copy-Item .env.example .env
```

3. Genera la key:

```powershell
php artisan key:generate
```

4. Configura tu base de datos en `.env`

5. Corre migraciones:

```powershell
php artisan migrate
```

6. Levanta backend y frontend:

```powershell
php artisan serve
npm run dev
```

## Scripts utiles

- `docker-up.bat`: levanta Docker
- `docker-down.bat`: baja Docker
- `docker-rebuild.bat`: reconstruye contenedores
- `docker-logs.bat`: muestra logs

## Notas para el equipo

- El proyecto ya no debe preocuparse por implementar metodo de pago como parte del alcance actual.
- Si Docker vuelve a tomar una ruta vieja, siempre levantar desde la carpeta real del repositorio.
- Antes de reportar un problema de arranque, revisar:
  - `docker compose --env-file .env.docker ps`
  - `docker compose --env-file .env.docker logs --tail=100 app`
  - `docker compose --env-file .env.docker logs --tail=100 mysql`
