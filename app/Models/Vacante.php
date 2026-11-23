<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vacante extends Model
{
    use HasFactory, SoftDeletes;

    // Opciones de los campos enum: valor guardado => texto en pantalla
    public const MODALIDADES = [
        'presencial' => 'Presencial',
        'remoto' => 'Remoto',
        'hibrido' => 'Híbrido',
    ];

    public const TIPOS_CONTRATO = [
        'tiempo_completo' => 'Tiempo completo',
        'medio_tiempo' => 'Medio tiempo',
        'practicas' => 'Prácticas',
        'freelance' => 'Freelance',
    ];

    public const NIVELES = [
        'practicante' => 'Practicante',
        'junior' => 'Junior',
        'semi_senior' => 'Semi senior',
        'senior' => 'Senior',
    ];

    protected $table = 'vacantes';

    protected $fillable = [
        'empresa_id', 'reclutador_id', 'categoria_id', 'ubicacion_id', 'titulo', 'slug',
        'descripcion', 'requisitos', 'modalidad', 'tipo_contrato', 'nivel_experiencia',
        'salario_min', 'salario_max', 'puestos', 'estado', 'publicada_en', 'cierra_en',
    ];

    protected function casts(): array
    {
        return [
            'salario_min' => 'decimal:2',
            'salario_max' => 'decimal:2',
            'publicada_en' => 'datetime',
            'cierra_en' => 'datetime',
        ];
    }

    /** N:1 — Empresa que publica la vacante. */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /** N:1 — Reclutador que creó la vacante. */
    public function reclutador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reclutador_id');
    }

    /** N:1 — Categoría (área laboral). */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /** N:1 — Ubicación del puesto. */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    /** N:M — Habilidades requeridas (obligatorias o deseables). */
    public function habilidades(): BelongsToMany
    {
        return $this->belongsToMany(Habilidad::class, 'vacante_habilidad', 'vacante_id', 'habilidad_id')
            ->withPivot('obligatoria');
    }

    /** 1:N — Postulaciones recibidas. */
    public function postulaciones(): HasMany
    {
        return $this->hasMany(Postulacion::class);
    }

    /** 1:N a través de postulaciones — Entrevistas de esta vacante. */
    public function entrevistas(): HasManyThrough
    {
        return $this->hasManyThrough(Entrevista::class, Postulacion::class);
    }

    /** N:M — Postulantes que guardaron esta vacante. */
    public function guardadaPor(): BelongsToMany
    {
        return $this->belongsToMany(PerfilPostulante::class, 'vacantes_guardadas', 'vacante_id', 'perfil_postulante_id');
    }

    public function getModalidadTextoAttribute(): string
    {
        return self::MODALIDADES[$this->modalidad] ?? $this->modalidad;
    }

    public function getTipoContratoTextoAttribute(): string
    {
        return self::TIPOS_CONTRATO[$this->tipo_contrato] ?? $this->tipo_contrato;
    }

    public function getNivelTextoAttribute(): string
    {
        return self::NIVELES[$this->nivel_experiencia] ?? $this->nivel_experiencia;
    }

    /** "S/ 3,000 – 4,000", "S/ 1,200" o "A tratar". */
    public function getSalarioTextoAttribute(): string
    {
        $formato = fn ($monto) => number_format((float) $monto, 0, '.', ',');

        return match (true) {
            $this->salario_min && $this->salario_max && $this->salario_min != $this->salario_max => 'S/ '.$formato($this->salario_min).' – '.$formato($this->salario_max),
            (bool) ($this->salario_min ?: $this->salario_max) => 'S/ '.$formato($this->salario_min ?: $this->salario_max),
            default => 'A tratar',
        };
    }

    /** Scope: solo vacantes visibles en el buscador público. */
    public function scopePublicadas(Builder $query): Builder
    {
        return $query->where('estado', 'publicada');
    }
}
