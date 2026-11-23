<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerfilPostulante extends Model
{
    use HasFactory;

    protected $table = 'perfiles_postulante';

    protected $fillable = [
        'usuario_id', 'ubicacion_id', 'titular', 'resumen', 'fecha_nacimiento',
        'anios_experiencia', 'salario_esperado', 'cv_ruta', 'linkedin_url', 'portafolio_url',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'salario_esperado' => 'decimal:2',
        ];
    }

    /**
     * Qué tan completo está el perfil (0 a 100) y qué le falta.
     *
     * @return array{porcentaje: int, faltantes: list<string>}
     */
    public function completitud(): array
    {
        $criterios = [
            'un titular profesional' => filled($this->titular),
            'un resumen' => filled($this->resumen),
            'tu ubicación' => filled($this->ubicacion_id),
            'tu CV en PDF' => filled($this->cv_ruta),
            'al menos 3 habilidades' => $this->habilidades()->count() >= 3,
            'experiencia o estudios' => $this->experiencias()->exists() || $this->estudios()->exists(),
        ];

        return [
            'porcentaje' => (int) round(collect($criterios)->filter()->count() / count($criterios) * 100),
            'faltantes' => collect($criterios)->reject()->keys()->all(),
        ];
    }

    /** 1:1 (lado inverso) — El perfil pertenece a un usuario. */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /** N:1 — Ubicación del postulante. */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    /** 1:N — Experiencias laborales. */
    public function experiencias(): HasMany
    {
        return $this->hasMany(Experiencia::class)->orderByDesc('fecha_inicio');
    }

    /** 1:N — Estudios. */
    public function estudios(): HasMany
    {
        return $this->hasMany(Estudio::class)->orderByDesc('fecha_inicio');
    }

    /** N:M — Habilidades del postulante, con su nivel (1 a 5). */
    public function habilidades(): BelongsToMany
    {
        return $this->belongsToMany(Habilidad::class, 'perfil_habilidad', 'perfil_postulante_id', 'habilidad_id')
            ->withPivot('nivel');
    }

    /** 1:N — Postulaciones realizadas. */
    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class);
    }

    /** N:M — Vacantes guardadas como favoritas. */
    public function vacantesGuardadas(): BelongsToMany
    {
        return $this->belongsToMany(Vacante::class, 'vacantes_guardadas', 'perfil_postulante_id', 'vacante_id')
            ->withPivot('created_at');
    }
}
