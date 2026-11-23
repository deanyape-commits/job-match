<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';

    protected $fillable = ['departamento', 'provincia'];

    /** 1:N — En una ubicación hay muchas empresas. */
    public function empresas(): HasMany
    {
        return $this->hasMany(Empresa::class);
    }

    /** 1:N — En una ubicación hay muchas vacantes. */
    public function vacantes(): HasMany
    {
        return $this->hasMany(Vacante::class);
    }

    /** 1:N — En una ubicación viven muchos postulantes. */
    public function perfilesPostulante(): HasMany
    {
        return $this->hasMany(PerfilPostulante::class);
    }

    /** Texto para mostrar: "Lima – Miraflores". */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->departamento} – {$this->provincia}";
    }
}
