<?php

namespace App\Http\Controllers\Postulante;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Ubicacion;
use App\Models\Vacante;
use App\Services\MatchingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Buscador de vacantes y detalle con el porcentaje de compatibilidad.
 */
class VacanteController extends Controller
{
    public function __construct(private MatchingService $matching) {}

    public function index(Request $request): View
    {
        $perfil = $request->user()->perfil();
        $filtros = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'departamento' => ['nullable', 'string', 'max:60'],
            'categoria_id' => ['nullable', 'integer'],
            'modalidad' => ['nullable', 'array'],
            'modalidad.*' => ['in:'.implode(',', array_keys(Vacante::MODALIDADES))],
            'nivel' => ['nullable', 'array'],
            'nivel.*' => ['in:'.implode(',', array_keys(Vacante::NIVELES))],
            'tipo_contrato' => ['nullable', 'array'],
            'tipo_contrato.*' => ['in:'.implode(',', array_keys(Vacante::TIPOS_CONTRATO))],
            'salario' => ['nullable', 'integer', 'min:0'],
            'orden' => ['nullable', 'in:match,recientes,salario'],
        ]);
        $orden = $filtros['orden'] ?? 'match';

        $consulta = $this->vacantesVisibles()
            ->with(['empresa', 'ubicacion'])
            ->when($filtros['q'] ?? null, function (Builder $q, string $texto) {
                $q->where(function (Builder $q) use ($texto) {
                    $q->where('titulo', 'ilike', "%{$texto}%")
                        ->orWhereHas('empresa', fn ($e) => $e->where('razon_social', 'ilike', "%{$texto}%"))
                        ->orWhereHas('habilidades', fn ($h) => $h->where('nombre', 'ilike', "%{$texto}%"));
                });
            })
            ->when($filtros['departamento'] ?? null, fn ($q, $d) => $q->whereHas('ubicacion', fn ($u) => $u->where('departamento', $d)))
            ->when($filtros['categoria_id'] ?? null, fn ($q, $c) => $q->where('categoria_id', $c))
            ->when($filtros['modalidad'] ?? null, fn ($q, $m) => $q->whereIn('modalidad', $m))
            ->when($filtros['nivel'] ?? null, fn ($q, $n) => $q->whereIn('nivel_experiencia', $n))
            ->when($filtros['tipo_contrato'] ?? null, fn ($q, $t) => $q->whereIn('tipo_contrato', $t))
            ->when($filtros['salario'] ?? null, fn ($q, $s) => $q->where(fn ($q) => $q->where('salario_max', '>=', $s)->orWhere('salario_min', '>=', $s)));

        // Columna calculada en SQL: porcentaje_match
        $this->matching->agregarPorcentaje($consulta, $perfil);

        match ($orden) {
            'recientes' => $consulta->orderByDesc('publicada_en'),
            'salario' => $consulta->orderByRaw('COALESCE(salario_max, salario_min, 0) DESC'),
            default => $consulta->orderByDesc('porcentaje_match')->orderByDesc('publicada_en'),
        };

        return view('postulante.vacantes.index', [
            'vacantes' => $consulta->paginate(10)->withQueryString(),
            'filtros' => $filtros,
            'orden' => $orden,
            'departamentos' => Ubicacion::distinct()->orderBy('departamento')->pluck('departamento'),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'guardadas' => $perfil->vacantesGuardadas()->pluck('vacantes.id')->all(),
            'postuladas' => $perfil->postulaciones()->pluck('vacante_id')->all(),
            'completitud' => $perfil->completitud(),
        ]);
    }

    public function show(Request $request, Vacante $vacante): View
    {
        $perfil = $request->user()->perfil();
        $postulacion = $perfil->postulaciones()->where('vacante_id', $vacante->id)->first();

        // Una vacante cerrada solo la puede ver quien ya postuló
        abort_unless(
            $postulacion || $this->vacantesVisibles()->whereKey($vacante->id)->exists(),
            404
        );

        $vacante->load(['empresa', 'ubicacion', 'categoria', 'habilidades']);

        return view('postulante.vacantes.show', [
            'vacante' => $vacante,
            'match' => $this->matching->calcular($perfil, $vacante),
            'postulacion' => $postulacion,
            'guardada' => $perfil->vacantesGuardadas()->whereKey($vacante->id)->exists(),
            'perfil' => $perfil,
        ]);
    }

    /** Guarda o quita la vacante de favoritos (tabla pivote vacantes_guardadas). */
    public function guardar(Request $request, Vacante $vacante): RedirectResponse
    {
        $resultado = $request->user()->perfil()->vacantesGuardadas()->toggle($vacante->id);

        return back()->with('exito', $resultado['attached']
            ? 'Vacante guardada en tus favoritos.'
            : 'Vacante quitada de tus favoritos.');
    }

    /** Vacantes publicadas de empresas verificadas. */
    private function vacantesVisibles(): Builder
    {
        return Vacante::publicadas()->whereHas('empresa', fn ($q) => $q->where('estado', 'verificada'));
    }
}
