<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Entrevista extends Model
{
    use HasFactory;

    protected $table = 'entrevistas';

    protected $fillable = [
        'postulacion_id', 'fecha_hora', 'duracion_minutos', 'enlace_reunion', 'estado', 'notas',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
        ];
    }

    /** N:1 — Postulación a la que corresponde la entrevista. */
    public function postulacion(): BelongsTo
    {
        return $this->belongsTo(Postulacion::class);
    }

    /** Genera un enlace único de videollamada en Jitsi Meet (no requiere API). */
    public static function generarEnlaceJitsi(): string
    {
        return 'https://meet.jit.si/jobmatch-'.Str::lower(Str::random(12));
    }
}
