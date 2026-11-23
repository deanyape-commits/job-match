<?php

namespace App\Http\Controllers\Reclutador;

use App\Http\Controllers\Controller;
use App\Http\Requests\VacanteRequest;
use App\Models\Categoria;
use App\Models\Habilidad;
use App\Models\Ubicacion;
use App\Models\Vacante;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * CRUD de vacantes del reclutador (solo las de su empresa).
 */
class VacanteController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->query('estado');

        $vacantes = Vacante::where('empresa_id', $request->user()->empresa_id)
            ->when(in_array($estado, ['borrador', 'publicada', 'cerrada'], true), fn ($q) => $q->where('estado', $estado))
            ->withCount([
                'postulaciones',
                'postulaciones as contratados_count' => fn ($q) => $q->where('estado', 'contratado'),
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('reclutador.vacantes.index', [
            'vacantes' => $vacantes,
            'estado' => $estado,
            'puedePublicar' => $request->user()->can('create', Vacante::class),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Vacante::class);

        return view('reclutador.vacantes.form', $this->datosFormulario(new Vacante([
            'puestos' => 1,
            'modalidad' => 'presencial',
            'nivel_experiencia' => 'junior',
            'tipo_contrato' => 'tiempo_completo',
        ])));
    }

    public function store(VacanteRequest $request): RedirectResponse
    {
        Gate::authorize('create', Vacante::class);

        $vacante = DB::transaction(function () use ($request) {
            $vacante = new Vacante($this->camposVacante($request));
            $vacante->empresa_id = $request->user()->empresa_id;
            $vacante->reclutador_id = $request->user()->id;
            $vacante->slug = Str::slug($request->titulo).'-'.Str::lower(Str::random(6));
            $vacante->save();

            $this->sincronizarHabilidades($vacante, $request);

            return $vacante;
        });

        return redirect()->route('reclutador.vacantes.index')->with('exito', $vacante->estado === 'publicada'
            ? "La vacante \"{$vacante->titulo}\" ya está publicada."
            : "La vacante \"{$vacante->titulo}\" se guardó como borrador.");
    }

    public function edit(Vacante $vacante): View
    {
        Gate::authorize('update', $vacante);

        return view('reclutador.vacantes.form', $this->datosFormulario($vacante->load('habilidades')));
    }

    public function update(VacanteRequest $request, Vacante $vacante): RedirectResponse
    {
        Gate::authorize('update', $vacante);

        DB::transaction(function () use ($request, $vacante) {
            $campos = $this->camposVacante($request);

            // Una vacante cerrada o publicada no vuelve a borrador al editarla
            if ($vacante->estado !== 'borrador' && $request->accion === 'borrador') {
                unset($campos['estado'], $campos['publicada_en']);
            }
            // Si ya estaba publicada, se conserva su fecha original
            if ($vacante->publicada_en) {
                unset($campos['publicada_en']);
            }

            $vacante->update($campos);
            $this->sincronizarHabilidades($vacante, $request);
        });

        return redirect()->route('reclutador.vacantes.index')->with('exito', 'Vacante actualizada.');
    }

    /** Cerrar una vacante publicada (deja de aparecer en el buscador). */
    public function cerrar(Vacante $vacante): RedirectResponse
    {
        Gate::authorize('update', $vacante);

        $vacante->update(['estado' => 'cerrada']);

        return back()->with('exito', "La vacante \"{$vacante->titulo}\" fue cerrada.");
    }

    public function destroy(Vacante $vacante): RedirectResponse
    {
        Gate::authorize('delete', $vacante);

        $vacante->delete(); // softDelete: queda en la BD con deleted_at

        return redirect()->route('reclutador.vacantes.index')->with('exito', 'Vacante eliminada.');
    }

    // ------------------------------------------------------------------

    private function datosFormulario(Vacante $vacante): array
    {
        return [
            'vacante' => $vacante,
            'categorias' => Categoria::orderBy('nombre')->get(),
            'ubicaciones' => Ubicacion::orderBy('departamento')->orderBy('provincia')->get(),
            'habilidades' => Habilidad::orderBy('nombre')->get(['id', 'nombre']),
        ];
    }

    private function camposVacante(VacanteRequest $request): array
    {
        $publicar = $request->accion === 'publicar' && $request->user()->empresa?->estaVerificada();

        return [
            ...$request->safe()->except(['habilidades', 'accion']),
            'estado' => $publicar ? 'publicada' : 'borrador',
            'publicada_en' => $publicar ? now() : null,
        ];
    }

    /** Guarda las habilidades en la tabla pivote vacante_habilidad. */
    private function sincronizarHabilidades(Vacante $vacante, VacanteRequest $request): void
    {
        $vacante->habilidades()->sync(
            collect($request->habilidades)->mapWithKeys(fn ($obligatoria, $id) => [
                $id => ['obligatoria' => (bool) $obligatoria],
            ])
        );
    }
}
