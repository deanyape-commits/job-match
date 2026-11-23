<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Usuario del sistema. Se mantiene el nombre "User" porque Breeze y la
 * autenticación de Laravel dependen de él, pero su tabla es "usuarios".
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Nombre de la tabla en la base de datos.
     */
    protected $table = 'usuarios';

    /**
     * Valores por defecto en memoria (iguales a los de la migración),
     * para que un usuario recién creado ya tenga activo = true.
     */
    protected $attributes = [
        'activo' => true,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'rol_id',
        'empresa_id',
        'nombre',
        'email',
        'telefono',
        'avatar_ruta',
        'activo',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * "password" => "hashed" aplica bcrypt automáticamente al guardar.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /** N:1 — El usuario tiene un rol. */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    /** N:1 — Empresa del reclutador (null para otros roles). */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /** 1:1 — Perfil profesional (solo postulantes). */
    public function perfilPostulante(): HasOne
    {
        return $this->hasOne(PerfilPostulante::class, 'usuario_id');
    }

    /** Iniciales para el avatar: "Luis Quispe Mamani" → "LQ". */
    public function getInicialesAttribute(): string
    {
        return collect(explode(' ', $this->nombre))->filter()->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    }

    /** Perfil del postulante; si por algún motivo no existe, lo crea vacío. */
    public function perfil(): PerfilPostulante
    {
        return $this->perfilPostulante ?? $this->perfilPostulante()->create();
    }

    /** 1:N — Vacantes creadas por el reclutador. */
    public function vacantesCreadas(): HasMany
    {
        return $this->hasMany(Vacante::class, 'reclutador_id');
    }

    /** 1:N — Empresas verificadas por el supervisor. */
    public function empresasVerificadas(): HasMany
    {
        return $this->hasMany(Empresa::class, 'verificado_por');
    }

    /** ¿El usuario tiene el rol indicado? Ej.: $user->tieneRol(Rol::RECLUTADOR) */
    public function tieneRol(string ...$slugs): bool
    {
        return in_array($this->rol?->slug, $slugs, true);
    }

    /** Nombre de la ruta del panel principal según el rol del usuario. */
    public function rutaInicio(): string
    {
        return match ($this->rol?->slug) {
            Rol::ADMINISTRADOR => 'admin.inicio',
            Rol::SUPERVISOR => 'supervisor.inicio',
            Rol::RECLUTADOR => 'reclutador.inicio',
            default => 'postulante.inicio',
        };
    }
}
