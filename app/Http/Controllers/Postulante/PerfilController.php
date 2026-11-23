<?php

namespace App\Http\Controllers\Postulante;

use App\Http\Controllers\Controller;
use App\Models\Estudio;
use App\Models\Experiencia;
use App\Models\Habilidad;
use App\Models\Ubicacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Perfil profesional del postulante: datos, CV, habilidades, experiencias y estudios.
 */
class PerfilController extends Controller
{
    public function edit(Request $request): View
    {
        $perfil = $request->user()->perfil()->load(['ubicacion', 'experiencias', 'estudios', 'habilidades']);

        return view('postulante.perfil', [
            'perfil' => $perfil,
            'completitud' => $perfil->completitud(),
            'ubicaciones' => Ubicacion::orderBy('departamento')->orderBy('provincia')->get(),
            'catalogoHabilidades' => Habilidad::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    /** Datos generales + CV. */
    public function update(Request $request): RedirectResponse
    {
        $perfil = $request->user()->perfil();

        $datos = $request->validate([
            'titular' => ['required', 'string', 'max:150'],
            'ubicacion_id' => ['required', 'exists:ubicaciones,id'],
            'anios_experiencia' => ['required', 'integer', 'min:0', 'max:50'],
            'salario_esperado' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:-16 years'],
            'resumen' => ['nullable', 'string', 'max:2000'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'portafolio_url' => ['nullable', 'url', 'max:255'],
            'cv' => ['nullable', 'file', 'mimes:pdf', 'max:2048'], // 2 MB
        ], [
            'fecha_nacimiento.before' => 'Debes tener al menos 16 años.',
        ]);

        if ($request->hasFile('cv')) {
            // Se guarda en storage/app/private (no es accesible por URL directa)
            if ($perfil->cv_ruta) {
                Storage::disk('local')->delete($perfil->cv_ruta);
            }
            $datos['cv_ruta'] = $request->file('cv')->store('cvs', 'local');
        }
        unset($datos['cv']);

        $perfil->update($datos);

        return back()->with('exito', 'Tu perfil se actualizó.');
    }

    /** Habilidades con nivel (tabla pivote perfil_habilidad). */
    public function habilidades(Request $request): RedirectResponse
    {
        $request->validate([
            'habilidades' => ['array', 'max:30'],
            'habilidades.*' => ['integer', 'between:1,5'],
        ]);

        $ids = array_keys($request->input('habilidades', []));
        abort_if(Habilidad::whereIn('id', $ids)->count() !== count($ids), 422);

        $request->user()->perfil()->habilidades()->sync(
            collect($request->input('habilidades', []))->mapWithKeys(fn ($nivel, $id) => [$id => ['nivel' => $nivel]])
        );

        return back()->with('exito', 'Tus habilidades se guardaron. Tu compatibilidad con las vacantes se actualizó.');
    }

    public function agregarExperiencia(Request $request): RedirectResponse
    {
        $datos = $request->validateWithBag('experiencia', [
            'empresa_nombre' => ['required', 'string', 'max:150'],
            'cargo' => ['required', 'string', 'max:150'],
            'fecha_inicio' => ['required', 'date', 'before_or_equal:today'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio', 'before_or_equal:today'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->perfil()->experiencias()->create($datos);

        return back()->with('exito', 'Experiencia agregada.');
    }

    public function eliminarExperiencia(Request $request, Experiencia $experiencia): RedirectResponse
    {
        abort_unless($experiencia->perfil_postulante_id === $request->user()->perfil()->id, 403);
        $experiencia->delete();

        return back()->with('exito', 'Experiencia eliminada.');
    }

    public function agregarEstudio(Request $request): RedirectResponse
    {
        $datos = $request->validateWithBag('estudio', [
            'institucion' => ['required', 'string', 'max:150'],
            'grado' => ['required', 'in:tecnico,bachiller,titulado,maestria,curso'],
            'carrera' => ['required', 'string', 'max:150'],
            'fecha_inicio' => ['required', 'date', 'before_or_equal:today'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
        ]);

        $request->user()->perfil()->estudios()->create($datos);

        return back()->with('exito', 'Estudio agregado.');
    }

    public function eliminarEstudio(Request $request, Estudio $estudio): RedirectResponse
    {
        abort_unless($estudio->perfil_postulante_id === $request->user()->perfil()->id, 403);
        $estudio->delete();

        return back()->with('exito', 'Estudio eliminado.');
    }
}
