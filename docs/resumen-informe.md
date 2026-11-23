# JobMatch: resumen técnico para el informe y la exposición

Este documento sigue la estructura de `guia-inicial/guia-bd.docx`. Cada sección indica dónde está el código en el proyecto, para mostrarlo durante la exposición.

---

## 1. Introducción y planteamiento del problema

En el Perú, quienes buscan empleo en tecnología (egresados técnicos, practicantes, profesionales junior) postulan a decenas de vacantes sin saber si su perfil realmente encaja. Las empresas, a su vez, reciben cientos de CV que deben revisar uno por uno, y muchas ofertas publicadas en portales generales provienen de empresas cuya existencia nadie verifica. El resultado es un proceso lento, con postulaciones poco pertinentes, alta frustración para el candidato y un mayor costo de selección para la empresa.

## 2. Solución propuesta

**JobMatch** es una plataforma web especializada en empleos del sector tecnológico que:

- **Calcula un porcentaje de compatibilidad (match)** entre el perfil del postulante y las habilidades que pide cada vacante, antes de postular.
- **Ordena a los candidatos por compatibilidad** para que el reclutador revise primero a los más adecuados.
- **Verifica a las empresas** (por RUC) antes de permitirles publicar, lo que evita ofertas falsas.
- **Integra entrevistas virtuales** (Jitsi Meet) y **notificaciones** por correo y dentro de la aplicación en cada etapa del proceso.

## 3. Actores y roles del sistema

| Rol | Permisos y vistas |
|---|---|
| **Administrador** | Acceso total. Ve el resumen global, gestiona usuarios (crea supervisores, cambia roles, activa o desactiva cuentas) y también puede validar empresas. |
| **Supervisor** | Verifica o rechaza las empresas registradas. Al rechazar una, sus vacantes publicadas se cierran. |
| **Reclutador** | Pertenece a una empresa. Publica vacantes con sus habilidades, revisa candidatos ordenados por match, cambia estados y agenda entrevistas. Ve un dashboard con indicadores. |
| **Postulante** | Completa su perfil profesional, busca vacantes con filtros, ve su compatibilidad, postula, guarda favoritas y sigue el estado de sus procesos. |

El acceso se controla con el middleware `rol` (`app/Http/Middleware/VerificarRol.php`), por ejemplo `->middleware('rol:reclutador')` en `routes/web.php`.

---

## 4. Modelado de base de datos

**Motor:** PostgreSQL 15. El modelo completo está en [`modelo-de-datos.md`](modelo-de-datos.md), con el MER en Mermaid, el diccionario de datos y el de relaciones.

**15 tablas propias:** `roles`, `usuarios`, `empresas`, `ubicaciones`, `categorias`, `habilidades`, `perfiles_postulante`, `experiencias`, `estudios`, `perfil_habilidad`, `vacantes`, `vacante_habilidad`, `vacantes_guardadas`, `postulaciones`, `historial_postulaciones` y `entrevistas`.

> **Captura de la Vista Diseñador (MER):** abre pgAdmin o DBeaver y conéctate a `localhost:5432`, base `jobmatch`, usuario `jobmatch`, contraseña `jobmatch_secret`. En pgAdmin, clic derecho sobre la base → **ERD For Database**. En DBeaver, abre el esquema `public` → pestaña **ER Diagram**.

### Diccionario de relaciones (resumen)

| Tipo | Ejemplo en JobMatch | Cómo se implementa |
|---|---|---|
| **Uno a uno** | Un usuario tiene **un** perfil de postulante | `perfiles_postulante.usuario_id` con restricción **UNIQUE** |
| **Uno a muchos** | Una empresa publica **muchas** vacantes | `vacantes.empresa_id` (FK) |
| **Muchos a uno** | Muchos usuarios tienen **un** rol | `usuarios.rol_id` (FK) |
| **Muchos a muchos** | Vacantes ↔ habilidades, con el dato extra "obligatoria" | Tabla pivote `vacante_habilidad` |

---

## 5. Arquitectura y desarrollo del backend

Desarrollado con **Laravel 12** y el ORM **Eloquent**. Los 13 modelos están en `app/Models/`.

### BelongsTo (muchos a uno)
```php
// app/Models/Vacante.php
public function empresa(): BelongsTo
{
    return $this->belongsTo(Empresa::class);
}
```

### HasMany (uno a muchos)
```php
// app/Models/Empresa.php
public function vacantes(): HasMany
{
    return $this->hasMany(Vacante::class);
}
```

### HasOne (uno a uno)
```php
// app/Models/User.php — la tabla se llama "usuarios", por eso se indica la clave foránea
public function perfilPostulante(): HasOne
{
    return $this->hasOne(PerfilPostulante::class, 'usuario_id');
}
```

### BelongsToMany (muchos a muchos, con datos en la tabla pivote)
```php
// app/Models/Vacante.php
public function habilidades(): BelongsToMany
{
    return $this->belongsToMany(Habilidad::class, 'vacante_habilidad', 'vacante_id', 'habilidad_id')
        ->withPivot('obligatoria');
}
```

### HasManyThrough (a través de otra tabla)
```php
// app/Models/Vacante.php — entrevistas de una vacante, pasando por sus postulaciones
public function entrevistas(): HasManyThrough
{
    return $this->hasManyThrough(Entrevista::class, Postulacion::class);
}
```

> Como las tablas están en español, cada modelo indica su tabla (`protected $table = 'habilidades';`), porque el pluralizador de Laravel trabaja en inglés y buscaría `habilidads`.

### Lógica de negocio: el matching (`app/Services/MatchingService.php`)

```
habilidades obligatorias que tiene el postulante  × 70 %
habilidades deseables que tiene el postulante     × 20 %
misma región o vacante remota                     × 10 %
```

La fórmula está implementada **dos veces**:
- **En PHP**, para el detalle de cada vacante y para guardar el % al postular.
- **En SQL**, para ordenar todo el buscador por compatibilidad sin cargar las vacantes en memoria.

Un test (`tests/Feature/MatchingTest.php`) comprueba que ambas versiones dan el mismo resultado.

### Poblamiento de la base de datos (factories y seeders)

- **Factories** (`database/factories/`): `UserFactory` (con estados por rol), `EmpresaFactory`, `PerfilPostulanteFactory`, `ExperienciaFactory`, `EstudioFactory`, `VacanteFactory`, `PostulacionFactory` y `EntrevistaFactory`.
- **Seeders jerárquicos** (`database/seeders/DatabaseSeeder.php`): cada nivel depende solo de los anteriores, para respetar la integridad referencial.

| Nivel | Seeder | Datos |
|---|---|---|
| 1 | `CatalogoSeeder` | 4 roles, 43 ubicaciones, 12 categorías, 83 habilidades |
| 2 | `UsuarioSeeder` | Administrador y supervisores |
| 3 | `EmpresaSeeder` | **60 empresas** con sus reclutadores |
| 4 | `PostulanteSeeder` | **1 000 postulantes** con perfil, experiencia, estudios y habilidades |
| 5 | `VacanteSeeder` | **500 vacantes** con sus habilidades |
| 6 | `PostulacionSeeder` | **~3 700 postulaciones** con match real y **~1 100 entrevistas** |

Comando: `php artisan migrate:fresh --seed` (tarda alrededor de 1 minuto).

---

## 6. Seguridad y cifrado

### Gestión de contraseñas
Las contraseñas **no se guardan en texto plano**. Se usa **bcrypt**, el algoritmo por defecto de Laravel:

```php
// app/Models/User.php
protected function casts(): array
{
    return ['password' => 'hashed']; // bcrypt automático al guardar
}
```

> **¿Por qué no MD5?** La guía lo menciona como ejemplo, pero MD5 está obsoleto para contraseñas: es muy rápido y se puede revertir con tablas *rainbow*. bcrypt es lento a propósito y agrega un *salt* aleatorio, así que dos contraseñas iguales generan hashes distintos.

### Otras medidas
- **Control de acceso por rol:** middleware `VerificarRol`. Un postulante que intenta entrar a `/reclutador` recibe un error 403.
- **Policies:** `VacantePolicy` y `PostulacionPolicy` impiden que un reclutador vea o modifique datos de otra empresa.
- **Usuarios desactivados:** no pueden iniciar sesión, y si tienen la sesión abierta se les cierra (`VerificarUsuarioActivo`).
- **CV protegidos:** se guardan en `storage/app/private`. Solo los descargan el dueño, los reclutadores de empresas a las que postuló, los supervisores y los administradores (`CvController`).
- **CSRF:** todos los formularios Blade incluyen `@csrf`.
- **Validación** de cada formulario, por ejemplo el RUC de 11 dígitos que empieza con 10 o 20, o el CV que solo acepta PDF de hasta 2 MB.

---

## 7. Implementación avanzada en la base de datos

Archivo: `database/migrations/2026_11_22_000017_create_triggers_y_procedimientos.php`. Se instala con `php artisan migrate`.

### Trigger 1: `trg_actualizar_estado_vacante`
Se activa **después de actualizar el estado** de una postulación. Si pasa a `contratado` y ya se cubrieron todos los puestos, la vacante cambia a `cerrada` y desaparece del buscador. Es el equivalente al ejemplo "Adoptada" de la guía.

```sql
CREATE TRIGGER trg_actualizar_estado_vacante
AFTER UPDATE OF estado ON postulaciones
FOR EACH ROW EXECUTE FUNCTION fn_actualizar_estado_vacante();
```

### Trigger 2: `trg_historial_postulacion`
Se activa **después de insertar o actualizar** una postulación. Guarda en `historial_postulaciones` el estado anterior, el nuevo y la fecha. Con esos datos se arma la línea de tiempo que ve el postulante.

### Procedimiento almacenado: `sp_reporte_empresa`
Recibe la empresa y un rango de fechas. Devuelve las vacantes publicadas, las postulaciones, las entrevistas realizadas, los contratados y el match promedio. Alimenta el dashboard del reclutador.

```sql
SELECT * FROM sp_reporte_empresa(1, '2026-01-01', '2026-12-31');
```

> En PostgreSQL, un procedimiento que **devuelve filas** se escribe como `FUNCTION … RETURNS TABLE` (un `PROCEDURE` no puede devolver un conjunto de resultados).

### Cómo demostrarlo en vivo
1. Entra como `reclutador@jobmatch.test` → **Mis vacantes** → elige una vacante con 1 puesto → **Candidatos**.
2. Cambia a un candidato a **Contratado**. Aparece el mensaje "la vacante se cerró automáticamente" (trigger 1).
3. Abre el perfil del candidato: en **Historial** aparecen los cambios (trigger 2).
4. En pgAdmin o DBeaver ejecuta `SELECT * FROM historial_postulaciones ORDER BY id DESC LIMIT 5;` y `SELECT * FROM sp_reporte_empresa(1, '2026-01-01', '2026-12-31');`.

---

## 8. Arquitectura de despliegue (adaptada a Laravel)

El diagrama original usaba Java/Spring Boot. Para Laravel queda así:

| Nodo | Componente |
|---|---|
| Dispositivo del usuario | Navegador web → páginas Blade (HTML + CSS + JS), diseño adaptable a celular |
| Servidor de aplicación | Nginx (proxy inverso, TLS) + **PHP-FPM 8.x + Laravel 12** |
| Servidor de base de datos | **PostgreSQL 15** (tablas, triggers, procedimiento almacenado) |
| Servicio externo de correo | **SMTP** (en desarrollo, Mailpit en Docker) |
| Servicio externo de videollamada | **Jitsi Meet** (enlaces `https://meet.jit.si/jobmatch-…`) |

Los módulos del diagrama se corresponden con los controladores de Laravel: gestión de usuarios (`Admin/`), perfiles y vacantes (`Postulante/PerfilController`, `Reclutador/VacanteController`), postulaciones, matching (`MatchingService`), entrevistas virtuales (`Reclutador/EntrevistaController`) y notificaciones (`app/Notifications`).

---

## 9. Guion sugerido para la demostración (20 minutos)

| Min. | Quién | Qué mostrar |
|---|---|---|
| 0–3 | Integrante 1 | Problema, solución y página de inicio (`/`) |
| 3–6 | Integrante 2 | Registro como reclutador → la empresa queda **pendiente** → el supervisor la **verifica** (correo en Mailpit, http://localhost:8025) |
| 6–10 | Integrante 3 | Postulante: **Mi perfil** → **Buscar** ordenado por match → detalle con el **círculo de compatibilidad** → postular |
| 10–14 | Integrante 4 | Reclutador: **dashboard** (procedimiento almacenado) → candidatos → **agendar entrevista** (Jitsi) → **contratar** (trigger cierra la vacante) |
| 14–17 | Integrante 1 | Base de datos: MER, migraciones, modelos con sus relaciones, seeders y SQL del trigger y del procedimiento |
| 17–20 | Integrante 2 | Seguridad (bcrypt, roles, policies) y administrador desactivando un usuario |

Ajusta los nombres según cuántos sean en el grupo: la guía pide que **todos** expongan.

---

## 10. Preguntas probables del profesor

- **¿Por qué el match se calcula en PHP y también en SQL?** En PHP se obtiene el desglose habilidad por habilidad para la pantalla de detalle. En SQL se puede **ordenar** el buscador entre cientos de vacantes sin traerlas todas a memoria. Un test garantiza que ambas versiones coinciden.
- **¿Qué pasa si alguien intenta postular dos veces?** La tabla `postulaciones` tiene una restricción `UNIQUE(vacante_id, perfil_postulante_id)`, y el controlador además lo valida y muestra un mensaje.
- **¿Por qué el modelo se llama `User` si la tabla es `usuarios`?** Breeze y el sistema de autenticación de Laravel dependen de la clase `User`. Se mantuvo el nombre y se indicó `protected $table = 'usuarios'`.
- **¿Cómo evitan que un reclutador vea candidatos de otra empresa?** Con *policies* (`PostulacionPolicy::gestionar`), que comparan la empresa del reclutador con la de la vacante. Hay tests que lo comprueban.
- **¿Qué pasa si se cae el servidor de correo?** El servicio `Notificador` registra el error y guarda igual la notificación en la campanita, así que la acción del usuario no falla.
