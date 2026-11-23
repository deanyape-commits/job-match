<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'ubicacion_id', 'razon_social', 'ruc', 'descripcion', 'sitio_web',
        'logo_ruta', 'tamano', 'estado', 'verificado_por', 'verificado_en',
    ];

    protected function casts(): array
    {
        return [
            'verificado_en' => 'datetime',
        ];
    }

    /** N:1 — La empresa está en una ubicación. */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    /** N:1 — Supervisor que verificó la empresa. */
    public function verificador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verificado_por');
    }

    /** 1:N — Reclutadores que trabajan en la empresa. */
    public function reclutadores(): HasMany
    {
        return $this->hasMany(User::class, 'empresa_id');
    }

    /** 1:N — Vacantes publicadas por la empresa. */
    public function vacantes(): HasMany
    {
        return $this->hasMany(Vacante::class);
    }

    public function estaVerificada(): bool
    {
        return $this->estado === 'verificada';
    }
}
