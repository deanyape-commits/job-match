<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Habilidad extends Model
{
    protected $table = 'habilidades';

    protected $fillable = ['nombre', 'tipo'];

    /** N:M — Postulantes que tienen esta habilidad (con su nivel). */
    public function perfilesPostulante(): BelongsToMany
    {
        return $this->belongsToMany(PerfilPostulante::class, 'perfil_habilidad', 'habilidad_id', 'perfil_postulante_id')
            ->withPivot('nivel');
    }

    /** N:M — Vacantes que piden esta habilidad. */
    public function vacantes(): BelongsToMany
    {
        return $this->belongsToMany(Vacante::class, 'vacante_habilidad', 'habilidad_id', 'vacante_id')
            ->withPivot('obligatoria');
    }
}
