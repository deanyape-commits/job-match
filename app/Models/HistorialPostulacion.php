<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro de auditoría. No se crea desde PHP: lo inserta el trigger
 * trg_historial_postulacion cada vez que cambia el estado de una postulación.
 */
class HistorialPostulacion extends Model
{
    protected $table = 'historial_postulaciones';

    // La tabla solo tiene "cambiado_en", no created_at/updated_at
    public $timestamps = false;

    protected $fillable = ['postulacion_id', 'estado_anterior', 'estado_nuevo', 'cambiado_en'];

    protected function casts(): array
    {
        return [
            'cambiado_en' => 'datetime',
        ];
    }

    /** N:1 — Pertenece a una postulación. */
    public function postulacion(): BelongsTo
    {
        return $this->belongsTo(Postulacion::class);
    }
}
