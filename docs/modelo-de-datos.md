# Ludan: modelo de datos (v1)

Basado en `guia-inicial/guia-bd.docx`. Motor: **PostgreSQL 15**. Framework: **Laravel 12 + Eloquent**.

> **Convención de nombres:** tablas y columnas en **español**, en minúsculas y `snake_case`. Los modelos Eloquent también van en español (`Vacante`, `Postulacion`…), salvo `User`, que se mantiene porque Breeze y la autenticación de Laravel dependen de él (su tabla sí se llama `usuarios`).
>
> El pluralizador de Laravel funciona en inglés: para `Habilidad` buscaría `habilidads`. Por eso **cada modelo declara su tabla** con `protected $table = 'habilidades';`, y las relaciones indican la clave foránea cuando no es obvia.
>
> Se mantienen en inglés solo las columnas que el framework necesita (`email`, `password`, `remember_token`, `email_verified_at`, `created_at`, `updated_at`, `deleted_at`) y sus tablas internas (`sessions`, `cache`, `jobs`, `password_reset_tokens`, `notifications`).

---

## 1. Actores y roles

| Rol (`roles.slug`) | Descripción y permisos |
|---|---|
| `administrador` | Acceso total. Gestiona usuarios, roles, catálogos (categorías, habilidades, ubicaciones) y ve reportes globales. |
| `supervisor` | Valida la veracidad de las empresas (aprueba o rechaza) y modera vacantes reportadas. |
| `reclutador` | Pertenece a una empresa. Publica y gestiona vacantes, revisa postulantes, cambia estados y agenda entrevistas virtuales. |
| `postulante` | Crea su perfil profesional (CV, experiencia, estudios, habilidades), busca vacantes, guarda favoritos, postula y ve sus entrevistas. |

> **"Empresa"** (del diagrama de despliegue) no es un rol, sino una **entidad** (`empresas`). Los reclutadores son los usuarios que la representan.

---

## 2. MER (diagrama entidad-relación)

```mermaid
erDiagram
    roles ||--o{ usuarios : "tiene"
    empresas ||--o{ usuarios : "emplea (reclutadores)"
    usuarios ||--o| perfiles_postulante : "tiene perfil"
    ubicaciones ||--o{ perfiles_postulante : "ubica"
    ubicaciones ||--o{ empresas : "ubica"
    ubicaciones ||--o{ vacantes : "ubica"
    categorias ||--o{ vacantes : "clasifica"
    empresas ||--o{ vacantes : "publica"
    usuarios ||--o{ vacantes : "crea (reclutador)"
    perfiles_postulante ||--o{ experiencias : "tiene"
    perfiles_postulante ||--o{ estudios : "tiene"
    perfiles_postulante ||--o{ perfil_habilidad : ""
    habilidades ||--o{ perfil_habilidad : ""
    vacantes ||--o{ vacante_habilidad : ""
    habilidades ||--o{ vacante_habilidad : ""
    perfiles_postulante ||--o{ vacantes_guardadas : ""
    vacantes ||--o{ vacantes_guardadas : ""
    vacantes ||--o{ postulaciones : "recibe"
    perfiles_postulante ||--o{ postulaciones : "realiza"
    postulaciones ||--o{ historial_postulaciones : "registra"
    postulaciones ||--o{ entrevistas : "agenda"

    roles { bigint id PK; string nombre; string slug UK }
    usuarios { bigint id PK; bigint rol_id FK; bigint empresa_id FK "nullable"; string nombre; string email UK; string password; bool activo }
    empresas { bigint id PK; bigint ubicacion_id FK; string razon_social; string ruc UK; string estado; bigint verificado_por FK "nullable" }
    perfiles_postulante { bigint id PK; bigint usuario_id FK,UK; bigint ubicacion_id FK; string titular; string cv_ruta }
    vacantes { bigint id PK; bigint empresa_id FK; bigint reclutador_id FK; bigint categoria_id FK; bigint ubicacion_id FK; string titulo; string modalidad; string estado; int puestos }
    postulaciones { bigint id PK; bigint vacante_id FK; bigint perfil_postulante_id FK; decimal porcentaje_match; string estado }
    entrevistas { bigint id PK; bigint postulacion_id FK; timestamp fecha_hora; string enlace_reunion; string estado }
    habilidades { bigint id PK; string nombre UK; string tipo }
    categorias { bigint id PK; string nombre; string slug UK }
    ubicaciones { bigint id PK; string departamento; string provincia }
    experiencias { bigint id PK; bigint perfil_postulante_id FK }
    estudios { bigint id PK; bigint perfil_postulante_id FK }
    perfil_habilidad { bigint perfil_postulante_id FK; bigint habilidad_id FK; smallint nivel }
    vacante_habilidad { bigint vacante_id FK; bigint habilidad_id FK; bool obligatoria }
    vacantes_guardadas { bigint perfil_postulante_id FK; bigint vacante_id FK }
    historial_postulaciones { bigint id PK; bigint postulacion_id FK; string estado_anterior; string estado_nuevo }
```

> El informe pide una **captura de la Vista Diseñador**. Cuando las migraciones estén creadas, la sacamos con pgAdmin (ERD Tool) o DBeaver.

---

## 3. Tablas (diccionario de datos)

Todas las tablas incluyen `created_at` y `updated_at` (`timestamps()`), salvo las tablas pivote.

### 3.1 Catálogos

**`roles`**: `id`, `nombre` (Administrador…), `slug` UK, `descripcion` nullable.

**`categorias`** (áreas laborales: Desarrollo de software, Redes, Ciberseguridad, Datos, Soporte TI, Diseño UX/UI…): `id`, `nombre`, `slug` UK.

**`ubicaciones`** (Perú): `id`, `departamento` (Lima, Arequipa…), `provincia`.

**`habilidades`** (Laravel, Python, SQL, Docker, Inglés…): `id`, `nombre` UK, `tipo` enum(`tecnica`, `blanda`, `idioma`).

### 3.2 Usuarios y empresas

**`usuarios`** (modelo `User`)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| rol_id | FK → roles | N:1 |
| empresa_id | FK → empresas, nullable | Solo para reclutadores |
| nombre | string | |
| email | string UK | |
| password | string | Hash **bcrypt** (ver §6) |
| telefono | string nullable | |
| avatar_ruta | string nullable | |
| activo | boolean default true | El administrador puede desactivar |
| email_verified_at, remember_token | | Columnas de Breeze |

**`empresas`**
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| ubicacion_id | FK → ubicaciones | |
| razon_social | string | |
| ruc | char(11) UK | |
| descripcion | text | |
| sitio_web, logo_ruta | string nullable | |
| tamano | enum(`1-10`,`11-50`,`51-200`,`201-500`,`500+`) | |
| estado | enum(`pendiente`,`verificada`,`rechazada`) default `pendiente` | La valida el supervisor |
| verificado_por | FK → usuarios, nullable | Supervisor que validó |
| verificado_en | timestamp nullable | |

### 3.3 Perfil del postulante

**`perfiles_postulante`** (1:1 con `usuarios`)
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| usuario_id | FK → usuarios, **UNIQUE** | Lo que hace la relación 1:1 |
| ubicacion_id | FK → ubicaciones, nullable | |
| titular | string | Ej.: "Desarrollador Backend Jr." |
| resumen | text nullable | |
| fecha_nacimiento | date nullable | |
| anios_experiencia | smallint default 0 | |
| salario_esperado | decimal(10,2) nullable | |
| cv_ruta | string nullable | PDF subido |
| linkedin_url, portafolio_url | string nullable | |

**`experiencias`**: `id`, `perfil_postulante_id` FK, `empresa_nombre`, `cargo`, `fecha_inicio`, `fecha_fin` nullable (= actual), `descripcion`.

**`estudios`**: `id`, `perfil_postulante_id` FK, `institucion`, `grado` enum(`tecnico`,`bachiller`,`titulado`,`maestria`,`curso`), `carrera`, `fecha_inicio`, `fecha_fin` nullable.

**`perfil_habilidad`** (pivote N:M): `perfil_postulante_id`, `habilidad_id`, `nivel` smallint 1–5. PK compuesta.

### 3.4 Vacantes

**`vacantes`**
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| empresa_id | FK → empresas | |
| reclutador_id | FK → usuarios | Reclutador que la creó |
| categoria_id | FK → categorias | |
| ubicacion_id | FK → ubicaciones | |
| titulo, slug | string | |
| descripcion | text | |
| requisitos | text | |
| modalidad | enum(`presencial`,`remoto`,`hibrido`) | |
| tipo_contrato | enum(`tiempo_completo`,`medio_tiempo`,`practicas`,`freelance`) | |
| nivel_experiencia | enum(`practicante`,`junior`,`semi_senior`,`senior`) | |
| salario_min, salario_max | decimal(10,2) nullable | |
| puestos | smallint default 1 | Puestos a cubrir |
| estado | enum(`borrador`,`publicada`,`cerrada`) | El trigger la cierra (§7) |
| publicada_en, cierra_en | timestamp nullable | |
| deleted_at | | `softDeletes()` |

**`vacante_habilidad`** (pivote N:M): `vacante_id`, `habilidad_id`, `obligatoria` boolean (obligatoria o deseable).

**`vacantes_guardadas`** (favoritos, pivote N:M): `perfil_postulante_id`, `vacante_id`, `created_at`.

### 3.5 Postulaciones y entrevistas

**`postulaciones`**
| Campo | Tipo | Notas |
|---|---|---|
| id | bigint PK | |
| vacante_id | FK → vacantes | |
| perfil_postulante_id | FK → perfiles_postulante | |
| carta_presentacion | text nullable | |
| porcentaje_match | decimal(5,2) | Se calcula al postular |
| estado | enum(`postulado`,`en_revision`,`entrevista`,`rechazado`,`contratado`) | |
| | **UNIQUE(vacante_id, perfil_postulante_id)** | No se puede postular dos veces |

**`historial_postulaciones`** (la llena un trigger): `id`, `postulacion_id` FK, `estado_anterior` nullable, `estado_nuevo`, `cambiado_en`.

**`entrevistas`**: `id`, `postulacion_id` FK, `fecha_hora`, `duracion_minutos` default 30, `enlace_reunion` (enlace Jitsi generado: `https://meet.jit.si/ludan-{uuid}`), `estado` enum(`programada`,`realizada`,`cancelada`), `notas` nullable.

### 3.6 Tablas propias de Laravel
`notifications`, `password_reset_tokens`, `sessions`, `cache`, `jobs`. Las crea el framework y se dejan con su nombre original.

---

## 4. Diccionario de relaciones

### Uno a uno (1:1)
| Relación | Implementación | Eloquent |
|---|---|---|
| Usuario ↔ Perfil de postulante | `perfiles_postulante.usuario_id` UNIQUE | `User::hasOne(PerfilPostulante::class, 'usuario_id')` / `PerfilPostulante::belongsTo(User::class, 'usuario_id')` |

### Uno a muchos (1:N)
| Relación | Eloquent |
|---|---|
| Rol → Usuarios | `Rol::hasMany(User::class, 'rol_id')` |
| Empresa → Reclutadores | `Empresa::hasMany(User::class, 'empresa_id')` |
| Empresa → Vacantes | `Empresa::hasMany(Vacante::class)` |
| Categoría → Vacantes | `Categoria::hasMany(Vacante::class)` |
| Ubicación → Vacantes / Empresas / Perfiles | `Ubicacion::hasMany(...)` |
| Perfil → Experiencias / Estudios | `PerfilPostulante::hasMany(Experiencia::class)` / `hasMany(Estudio::class)` |
| Perfil → Postulaciones | `PerfilPostulante::hasMany(Postulacion::class)` |
| Vacante → Postulaciones | `Vacante::hasMany(Postulacion::class)` |
| Postulación → Entrevistas | `Postulacion::hasMany(Entrevista::class)` |
| Postulación → Historial | `Postulacion::hasMany(HistorialPostulacion::class)` |

### Muchos a uno (N:1), el lado inverso
| Relación | Eloquent |
|---|---|
| Usuario → Rol | `User::belongsTo(Rol::class, 'rol_id')` |
| Reclutador → Empresa | `User::belongsTo(Empresa::class, 'empresa_id')` |
| Vacante → Empresa / Categoría / Ubicación / Reclutador | `Vacante::belongsTo(Empresa::class)`, `belongsTo(Categoria::class)`, `belongsTo(Ubicacion::class)`, `belongsTo(User::class, 'reclutador_id')` |
| Postulación → Vacante / Perfil | `Postulacion::belongsTo(Vacante::class)`, `belongsTo(PerfilPostulante::class)` |
| Entrevista → Postulación | `Entrevista::belongsTo(Postulacion::class)` |

### Muchos a muchos (N:M), extra
| Relación | Eloquent |
|---|---|
| Perfil ↔ Habilidades (con nivel) | `belongsToMany(Habilidad::class, 'perfil_habilidad')->withPivot('nivel')` |
| Vacante ↔ Habilidades | `belongsToMany(Habilidad::class, 'vacante_habilidad')->withPivot('obligatoria')` |
| Perfil ↔ Vacantes guardadas | `belongsToMany(Vacante::class, 'vacantes_guardadas')` |

Extra: `Vacante::hasManyThrough(Entrevista::class, Postulacion::class)` permite ver las entrevistas de una vacante.

---

## 5. Lógica de matching (`porcentaje_match`)

Se calcula en `app/Services/MatchingService.php` cuando el postulante abre una vacante o postula:

```
habilidades obligatorias que tiene el postulante  × 70 %
habilidades deseables que tiene el postulante     × 20 %
ubicación coincide o modalidad remota             × 10 %
```

Resultado de 0 a 100. Sirve para ordenar candidatos (reclutador) y vacantes recomendadas (postulante).

---

## 6. Seguridad y cifrado de contraseñas

La guía menciona MD5 solo **como ejemplo**. Recomendamos **no usar MD5**: es un algoritmo roto que se puede revertir con tablas *rainbow* en segundos. Laravel usa por defecto **bcrypt**, un hash lento y con *salt* aleatorio. En el modelo:

```php
protected function casts(): array {
    return ['password' => 'hashed'];
}
```

Así cumplimos "las contraseñas no se almacenan en texto plano" con un algoritmo seguro. Es un buen punto para defender en la exposición.

Además: middleware de roles (`rol:reclutador`), *policies* (un reclutador solo edita las vacantes de su empresa), protección CSRF de Blade y CV guardados fuera de la carpeta pública (solo se descargan a través de una ruta que verifica permisos).

---

## 7. Implementación avanzada en la base de datos (PostgreSQL)

Se crean desde una migración con `DB::unprepared(...)`, para que se instalen solos con `php artisan migrate`.

### Trigger 1: `trg_actualizar_estado_vacante`
**AFTER UPDATE OF estado ON postulaciones.** Cuando una postulación pasa a `contratado`, cuenta los contratados de la vacante. Si alcanzan `vacantes.puestos`, cambia la vacante a `cerrada` y deja de mostrarse en el buscador público. Es el equivalente directo al ejemplo "Adoptada" de la guía.

### Trigger 2: `trg_historial_postulacion`
**AFTER INSERT OR UPDATE OF estado ON postulaciones.** Inserta una fila en `historial_postulaciones` con el estado anterior y el nuevo. Es la auditoría que le permite al postulante ver la línea de tiempo de su postulación.

### Procedimiento almacenado: `sp_reporte_empresa(p_empresa_id, p_desde, p_hasta)`
Devuelve para la empresa y el rango de fechas: vacantes publicadas, total de postulaciones, entrevistas realizadas, contratados y promedio de `porcentaje_match`. Se usa en el dashboard del reclutador.

> En PostgreSQL, un procedimiento que **devuelve filas** se escribe como `FUNCTION ... RETURNS TABLE` y se consulta con `SELECT * FROM sp_reporte_empresa(1, '2026-01-01', '2026-12-31')`. Conviene mencionarlo en la exposición.

---

## 8. Poblamiento: factories y seeders jerárquicos

Orden en `DatabaseSeeder` (respeta la integridad referencial):

1. `CatalogoSeeder`, que llama a `RolSeeder`, `UbicacionSeeder`, `CategoriaSeeder` y `HabilidadSeeder`: 4 roles, 43 ubicaciones del Perú, 12 áreas TI y 83 habilidades
2. `UsuarioSeeder`: 1 administrador y 2 supervisores con credenciales conocidas para la demo
3. `EmpresaSeeder`: **60 empresas** (50 verificadas, 6 pendientes, 4 rechazadas), cada una con 1–3 reclutadores
4. `PostulanteSeeder`: **1 000 postulantes** con perfil, experiencias, estudios y habilidades coherentes con su área
5. `VacanteSeeder`: **500 vacantes** con 3–8 habilidades de su área
6. `PostulacionSeeder`: **~3 700 postulaciones** con `porcentaje_match` real (calculado con `MatchingService`) y unas 1 100 entrevistas. Los cambios de estado pasan por los triggers, que generan el historial y cierran las vacantes que cubren sus puestos.

Factories: `UserFactory` (con estados `postulante()`, `reclutador()`, `supervisor()` y `administrador()`), `EmpresaFactory`, `PerfilPostulanteFactory`, `ExperienciaFactory`, `EstudioFactory`, `VacanteFactory`, `PostulacionFactory` y `EntrevistaFactory`. Usan Faker en español (`APP_FAKER_LOCALE=es_PE`) para nombres y un banco de textos propio (`database/seeders/Datos/Textos.php`) para las descripciones.

---

## 9. Modelos Eloquent (`app/Models`)

| Modelo | Tabla |
|---|---|
| `Rol` | `roles` |
| `User` | `usuarios` |
| `Empresa` | `empresas` |
| `PerfilPostulante` | `perfiles_postulante` |
| `Experiencia` | `experiencias` |
| `Estudio` | `estudios` |
| `Habilidad` | `habilidades` |
| `Categoria` | `categorias` |
| `Ubicacion` | `ubicaciones` |
| `Vacante` | `vacantes` |
| `Postulacion` | `postulaciones` |
| `HistorialPostulacion` | `historial_postulaciones` |
| `Entrevista` | `entrevistas` |
