<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Gestión de usuarios por el administrador.
 * No se eliminan usuarios (tienen postulaciones, vacantes, etc.): se desactivan.
 */
class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $usuarios = User::with(['rol', 'empresa'])
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q
                ->where('nombre', 'ilike', '%'.$request->q.'%')
                ->orWhere('email', 'ilike', '%'.$request->q.'%')))
            ->when($request->filled('rol'), fn ($q) => $q->whereHas('rol', fn ($r) => $r->where('slug', $request->rol)))
            ->when($request->estado === 'activos', fn ($q) => $q->where('activo', true))
            ->when($request->estado === 'inactivos', fn ($q) => $q->where('activo', false))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'roles' => Rol::orderBy('id')->get(),
            'total' => User::count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.usuarios.form', $this->datosFormulario(new User(['activo' => true])));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        $usuario = DB::transaction(function () use ($datos) {
            $usuario = User::create($datos);
            $this->asegurarPerfil($usuario);

            return $usuario;
        });

        return redirect()->route('admin.usuarios.index')->with('exito', "Usuario {$usuario->nombre} creado.");
    }

    public function edit(User $usuario): View
    {
        return view('admin.usuarios.form', $this->datosFormulario($usuario));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $datos = $this->validar($request, $usuario);

        // Protección: el administrador no puede quitarse su propio rol ni desactivarse
        if ($usuario->is($request->user())) {
            $datos['rol_id'] = $usuario->rol_id;
            $datos['activo'] = true;
        }

        if (empty($datos['password'])) {
            unset($datos['password']);
        }

        DB::transaction(function () use ($usuario, $datos) {
            $usuario->update($datos);
            $this->asegurarPerfil($usuario->fresh());
        });

        return redirect()->route('admin.usuarios.index')->with('exito', "Usuario {$usuario->nombre} actualizado.");
    }

    /** Activar / desactivar (el interruptor de la tabla). */
    public function cambiarActivo(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        return back()->with('exito', $usuario->activo
            ? "{$usuario->nombre} fue activado."
            : "{$usuario->nombre} fue desactivado. Ya no podrá iniciar sesión.");
    }

    // ------------------------------------------------------------------

    private function validar(Request $request, ?User $usuario = null): array
    {
        $rolReclutador = Rol::where('slug', Rol::RECLUTADOR)->value('id');

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('usuarios', 'email')->ignore($usuario?->id)],
            'telefono' => ['nullable', 'string', 'max:20'],
            'rol_id' => ['required', 'exists:roles,id'],
            'empresa_id' => [Rule::requiredIf($request->rol_id == $rolReclutador), 'nullable', 'exists:empresas,id'],
            'activo' => ['boolean'],
            'password' => [$usuario ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ], [
            'empresa_id.required' => 'Un reclutador debe pertenecer a una empresa.',
        ], [
            'rol_id' => 'rol',
            'empresa_id' => 'empresa',
        ]);

        $datos['activo'] = $request->boolean('activo');

        // Solo los reclutadores tienen empresa
        if ($datos['rol_id'] != $rolReclutador) {
            $datos['empresa_id'] = null;
        }

        return $datos;
    }

    /** Si el usuario es postulante, se asegura de que tenga su perfil (relación 1:1). */
    private function asegurarPerfil(User $usuario): void
    {
        if ($usuario->tieneRol(Rol::POSTULANTE)) {
            $usuario->perfil();
        }
    }

    private function datosFormulario(User $usuario): array
    {
        return [
            'usuario' => $usuario,
            'roles' => Rol::orderBy('id')->get(),
            'empresas' => Empresa::orderBy('razon_social')->get(['id', 'razon_social', 'estado']),
            'rolReclutador' => Rol::where('slug', Rol::RECLUTADOR)->value('id'),
        ];
    }
}
