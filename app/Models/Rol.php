<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    // Slugs de los roles del sistema
    public const ADMINISTRADOR = 'administrador';

    public const SUPERVISOR = 'supervisor';

    public const RECLUTADOR = 'reclutador';

    public const POSTULANTE = 'postulante';

    protected $table = 'roles';

    protected $fillable = ['nombre', 'slug', 'descripcion'];

    /** 1:N — Un rol tiene muchos usuarios. */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id');
    }
}
