<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Postulacion extends Model
{
    use HasFactory;

    public const ESTADOS = ['postulado', 'en_revision', 'entrevista', 'rechazado', 'contratado'];

    /** Texto que ve el reclutador para cada estado. */
    public const ESTADOS_TEXTO = [
        'postulado' => 'Postulado',
        'en_revision' => 'En revisión',
        'entrevista' => 'Entrevista',
        'rechazado' => 'Rechazado',
        'contratado' => 'Contratado',
    ];

    protected $table = 'postulaciones';

    protected $fillable = [
        'vacante_id', 'perfil_postulante_id', 'carta_presentacion', 'porcentaje_match', 'estado',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje_match' => 'decimal:2',
        ];
    }

    /** N:1 — Vacante a la que se postuló. */
    public function vacante(): BelongsTo
    {
        return $this->belongsTo(Vacante::class);
    }

    /** N:1 — Postulante que realizó la postulación. */
    public function perfilPostulante(): BelongsTo
    {
        return $this->belongsTo(PerfilPostulante::class);
    }

    /** 1:N — Entrevistas agendadas. */
    public function entrevistas(): HasMany
    {
        return $this->hasMany(Entrevista::class);
    }

    /** 1:N — Historial de cambios de estado (lo llena un trigger). */
    public function historial(): HasMany
    {
        return $this->hasMany(HistorialPostulacion::class)->orderBy('cambiado_en');
    }
}
