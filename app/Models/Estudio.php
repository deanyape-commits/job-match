<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Estudio extends Model
{
    use HasFactory;

    protected $table = 'estudios';

    protected $fillable = [
        'perfil_postulante_id', 'institucion', 'grado', 'carrera', 'fecha_inicio', 'fecha_fin',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    /** N:1 — Pertenece a un perfil de postulante. */
    public function perfilPostulante(): BelongsTo
    {
        return $this->belongsTo(PerfilPostulante::class);
    }
}
