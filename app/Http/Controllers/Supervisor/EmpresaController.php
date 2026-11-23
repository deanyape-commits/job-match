<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * El supervisor revisa las empresas registradas y las verifica o rechaza.
 * Solo las empresas verificadas pueden publicar vacantes.
 */
class EmpresaController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->query('estado', 'pendiente');
        if (! in_array($estado, ['pendiente', 'verificada', 'rechazada'], true)) {
            $estado = 'pendiente';
        }

        $empresas = Empresa::with(['ubicacion', 'reclutadores', 'verificador'])
            ->where('estado', $estado)
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('razon_social', 'ilike', '%'.$request->q.'%')
                    ->orWhere('ruc', 'like', $request->q.'%');
            }))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        // Totales por estado para las tarjetas de arriba
        $totales = Empresa::selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado');

        return view('supervisor.empresas.index', compact('empresas', 'estado', 'totales'));
    }

    public function verificar(Request $request, Empresa $empresa): RedirectResponse
    {
        $empresa->update([
            'estado' => 'verificada',
            'verificado_por' => $request->user()->id,
            'verificado_en' => now(),
        ]);

        return back()->with('exito', "La empresa {$empresa->razon_social} fue verificada. Ya puede publicar vacantes.");
    }

    public function rechazar(Request $request, Empresa $empresa): RedirectResponse
    {
        $empresa->update([
            'estado' => 'rechazada',
            'verificado_por' => $request->user()->id,
            'verificado_en' => now(),
        ]);

        // Si tenía vacantes publicadas, dejan de mostrarse
        $empresa->vacantes()->where('estado', 'publicada')->update(['estado' => 'cerrada']);

        return back()->with('exito', "La empresa {$empresa->razon_social} fue rechazada.");
    }
}
