<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
}
