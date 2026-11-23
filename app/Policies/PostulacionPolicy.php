<?php

namespace App\Policies;

use App\Models\Postulacion;
use App\Models\User;

/**
 * Un reclutador solo gestiona (ve, cambia de estado, entrevista)
 * las postulaciones a vacantes de SU empresa.
 */
class PostulacionPolicy
{
    public function gestionar(User $user, Postulacion $postulacion): bool
    {
        return $user->tieneRol('reclutador')
            && $user->empresa_id !== null
            && $user->empresa_id === $postulacion->vacante()->withTrashed()->value('empresa_id');
    }
}
