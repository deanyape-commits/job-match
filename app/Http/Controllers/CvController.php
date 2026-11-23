<?php

namespace App\Http\Controllers;

use App\Models\PerfilPostulante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Descarga protegida del CV. Los archivos están en storage/app/private,
 * así que solo se pueden ver a través de esta ruta, que verifica permisos:
 *  - el propio postulante,
 *  - un reclutador de una empresa a cuya vacante postuló,
 *  - supervisores y administradores.
 */
class CvController extends Controller
{
    public function __invoke(Request $request, PerfilPostulante $perfil): StreamedResponse
    {
        $usuario = $request->user();

        $puedeVer = $usuario->id === $perfil->usuario_id
            || $usuario->tieneRol('supervisor', 'administrador')
            || ($usuario->tieneRol('reclutador') && $perfil->postulaciones()
                ->whereHas('vacante', fn ($q) => $q->where('empresa_id', $usuario->empresa_id))
                ->exists());

        abort_unless($puedeVer, 403);
        abort_unless($perfil->cv_ruta && Storage::disk('local')->exists($perfil->cv_ruta), 404);

        $nombre = 'CV - '.$perfil->usuario->nombre.'.pdf';

        return Storage::disk('local')->response($perfil->cv_ruta, $nombre);
    }
}
