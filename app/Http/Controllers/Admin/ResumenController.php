<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Postulacion;
use App\Models\Rol;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\View\View;

/** Panel de inicio del administrador: cifras globales de la plataforma. */
class ResumenController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.resumen', [
            'usuariosPorRol' => Rol::withCount('usuarios')->orderBy('id')->get(),
            'empresasPorEstado' => Empresa::selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado'),
            'vacantesPorEstado' => Vacante::selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado'),
            'postulacionesPorEstado' => Postulacion::selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado'),
            'usuariosInactivos' => User::where('activo', false)->count(),
            'ultimosUsuarios' => User::with('rol')->latest()->limit(6)->get(),
        ]);
    }
}
