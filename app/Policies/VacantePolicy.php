<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vacante;

/**
 * Un reclutador solo puede gestionar las vacantes de SU empresa.
 * Laravel encuentra esta clase automáticamente por su nombre (Vacante → VacantePolicy).
 */
class VacantePolicy
{
    /** Crear vacantes: solo reclutadores de una empresa verificada. */
    public function create(User $user): bool
    {
        return $user->tieneRol('reclutador') && $user->empresa?->estaVerificada();
    }

    public function update(User $user, Vacante $vacante): bool
    {
        return $user->tieneRol('reclutador') && $user->empresa_id === $vacante->empresa_id;
    }

    public function delete(User $user, Vacante $vacante): bool
    {
        return $this->update($user, $vacante);
    }
}
