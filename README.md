# JobMatch

Plataforma web de búsqueda de empleo del sector tecnológico en el Perú. JobMatch compara el perfil de cada postulante con las habilidades que pide cada vacante y muestra un **porcentaje de compatibilidad (match)**, tanto al postulante como a la empresa.

Proyecto final · Desarrollo de Aplicaciones Web y Base de Datos · Tecsup

## Tecnologías

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12 (PHP 8.2), ORM Eloquent |
| Vistas | Plantillas Blade + Tailwind CSS + Alpine.js, compiladas con Vite |
| Autenticación | Laravel Breeze (Blade), contraseñas con bcrypt |
| Base de datos | PostgreSQL 15 (triggers y procedimiento almacenado en PL/pgSQL) |
| Correo | SMTP (Mailpit en desarrollo) |
| Videollamadas | Jitsi Meet (enlaces generados, sin API) |
| Contenedores | Docker Compose (PostgreSQL y Mailpit) |

## Roles

| Rol | Qué puede hacer |
|---|---|
| **Postulante** | Completa su perfil (CV, experiencia, estudios, habilidades), busca vacantes ordenadas por compatibilidad, postula, guarda favoritas y sigue sus procesos y entrevistas. |
| **Reclutador** | Pertenece a una empresa. Publica vacantes, revisa candidatos ordenados por match, cambia estados y agenda entrevistas por videollamada. |
| **Supervisor** | Verifica o rechaza las empresas registradas. Solo las empresas verificadas pueden publicar. |
| **Administrador** | Ve el resumen de la plataforma y gestiona usuarios (roles, activar o desactivar). |

## Requisitos

- PHP 8.2 o superior con las extensiones `pdo_pgsql` y `pgsql` activadas. En XAMPP, quita el `;` de `extension=pdo_pgsql` y `extension=pgsql` en `C:\xampp\php\php.ini`.
- Composer 2
- Node.js 20 o superior
- Docker Desktop
- Git

## Instalación

```bash
git clone <url-del-repositorio> job-match
cd job-match

composer install
npm install

cp .env.example .env          # en Windows PowerShell: copy .env.example .env
php artisan key:generate
php artisan storage:link      # para que se vean las fotos de perfil

docker compose up -d          # levanta PostgreSQL (5432) y Mailpit (1025 / 8025)

php artisan migrate:fresh --seed   # crea las tablas, triggers, procedimiento y ~1 minuto de datos de prueba
npm run build
```

## Ejecutar

Abre dos terminales en la carpeta del proyecto:

```bash
php artisan serve    # http://localhost:8000
npm run dev          # recompila los estilos al guardar cambios (opcional)
```

- **Aplicación:** http://localhost:8000
- **Bandeja de correos de prueba (Mailpit):** http://localhost:8025

Antes de trabajar, abre Docker Desktop. Los contenedores `jobmatch-db` y `jobmatch-mail` se inician solos.

## Cuentas de prueba

Todas usan la contraseña **`password`**.

| Correo | Rol | Qué tiene |
|---|---|---|
| `admin@jobmatch.com` | Administrador | Acceso al resumen, usuarios y validación de empresas |
| `supervisor@jobmatch.com` | Supervisor | Empresas pendientes por validar |
| `reclutador@jobmatch.com` | Reclutador | Inka Fintech S.A.C., con vacantes, candidatos y entrevistas |
| `postulante@jobmatch.com` | Postulante | Perfil completo, postulaciones y una entrevista en 2 días |

## Datos de prueba

`php artisan migrate:fresh --seed` **borra toda la base** y la vuelve a llenar con seeders jerárquicos:

1. `CatalogoSeeder`: 4 roles, 43 ubicaciones del Perú, 12 categorías y 83 habilidades
2. `UsuarioSeeder`: administrador y 2 supervisores
3. `EmpresaSeeder`: 60 empresas (50 verificadas, 6 pendientes, 4 rechazadas) con 1 a 3 reclutadores
4. `PostulanteSeeder`: 1 000 postulantes con perfil, experiencias, estudios y habilidades
5. `VacanteSeeder`: 500 vacantes con sus habilidades
6. `PostulacionSeeder`: unas 3 700 postulaciones con el match calculado, y entrevistas

## Tests

```bash
php artisan test
```

Los tests usan una base de datos separada, `jobmatch_test`, que Docker crea automáticamente (`docker/postgres/init.sql`), así que no tocan los datos de desarrollo. Hay más de 100 tests: triggers, procedimiento almacenado, matching, permisos por rol y cada pantalla.

## Estructura principal

```
app/
  Http/Controllers/
    Postulante/   Reclutador/   Supervisor/   Admin/   ← un controlador por pantalla y rol
  Http/Middleware/VerificarRol.php                     ← middleware 'rol:reclutador'
  Models/                                              ← 13 modelos Eloquent con sus relaciones
  Notifications/                                       ← correos y avisos de la campanita
  Policies/                                            ← reglas: cada reclutador solo ve lo de su empresa
  Services/MatchingService.php                         ← cálculo del % de compatibilidad
database/
  migrations/   ← tablas en español + triggers y procedimiento (2026_11_22_000017_…)
  factories/    seeders/
resources/views/  ← vistas Blade por rol
docs/             ← modelo de datos y resumen para el informe
```

## Imágenes

Fotografías de [Unsplash](https://unsplash.com) bajo la Licencia Unsplash (uso gratuito):

- Portada: *Team collaborating around a computer in an office*
- Login y registro: *Six colleagues collaborating around a conference table with laptops*
- Sección para empresas: *Two professionals shaking hands across a table*

## Documentación

- [`docs/modelo-de-datos.md`](docs/modelo-de-datos.md): MER, diccionario de datos y de relaciones
- [`docs/resumen-informe.md`](docs/resumen-informe.md): resumen técnico para el informe y la exposición
