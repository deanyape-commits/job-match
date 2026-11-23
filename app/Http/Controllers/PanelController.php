<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Paneles de inicio de cada rol.
 * Son provisionales: en la fase 5 se reemplazan por las pantallas del diseño.
 */
class PanelController extends Controller
{
    /**
     * Ruta "dashboard" de Breeze: después del login o el registro,
     * envía a cada usuario al panel de su rol.
     */
    public function redirigir(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->rutaInicio());
    }

    public function postulante(Request $request): View
    {
        return view('paneles.postulante', [
            'perfil' => $request->user()->perfilPostulante,
        ]);
    }

    public function reclutador(Request $request): View
    {
        return view('paneles.reclutador', [
            'empresa' => $request->user()->empresa,
        ]);
    }

    public function supervisor(): View
    {
        return view('paneles.supervisor', [
            'empresasPendientes' => Empresa::where('estado', 'pendiente')->count(),
        ]);
    }

    public function admin(): View
    {
        return view('paneles.admin', [
            'usuariosPorRol' => User::query()
                ->join('roles', 'roles.id', '=', 'usuarios.rol_id')
                ->selectRaw('roles.nombre as rol, count(*) as total')
                ->groupBy('roles.nombre')
                ->pluck('total', 'rol'),
        ]);
    }
}
