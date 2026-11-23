<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Habilidad;
use App\Models\Vacante;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Sugerencias mientras se escribe en el buscador (inicio y buscador del postulante).
 * Devuelve vacantes, habilidades y empresas que coinciden con el texto.
 */
class SugerenciaController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['grupos' => []]);
        }

        $vacantes = Vacante::publicadas()
            ->whereHas('empresa', fn ($e) => $e->where('estado', 'verificada'))
            ->where('titulo', 'ilike', "%{$q}%")
            ->with('empresa:id,razon_social')
            ->latest('publicada_en')
            ->limit(5)
            ->get(['id', 'slug', 'titulo', 'empresa_id'])
            ->map(fn ($v) => [
                'texto' => $v->titulo,
                'detalle' => $v->empresa->razon_social,
                'url' => route('postulante.vacantes.show', $v),
            ]);

        $buscar = fn (string $texto) => route('postulante.vacantes.index', ['q' => $texto]);

        $habilidades = Habilidad::where('nombre', 'ilike', "%{$q}%")
            ->orderBy('nombre')->limit(4)->pluck('nombre')
            ->map(fn ($nombre) => ['texto' => $nombre, 'detalle' => 'Habilidad', 'url' => $buscar($nombre)]);

        $empresas = Empresa::where('estado', 'verificada')
            ->where('razon_social', 'ilike', "%{$q}%")
            ->orderBy('razon_social')->limit(3)->pluck('razon_social')
            ->map(fn ($nombre) => ['texto' => $nombre, 'detalle' => 'Empresa', 'url' => $buscar($nombre)]);

        return response()->json(['grupos' => [
            ['titulo' => 'Vacantes', 'items' => $vacantes->values()],
            ['titulo' => 'Habilidades', 'items' => $habilidades->values()],
            ['titulo' => 'Empresas', 'items' => $empresas->values()],
        ]]);
    }
}
