<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\PerfilPostulante;
use App\Models\Rol;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'ubicaciones' => Ubicacion::orderBy('departamento')->orderBy('provincia')->get(),
        ]);
    }

    /**
     * Registro público. Hay dos tipos de cuenta:
     *  - postulante: se crea el usuario y su perfil profesional vacío (relación 1:1).
     *  - reclutador: se crea la empresa (estado "pendiente") y el usuario vinculado a ella.
     *
     * Los supervisores y administradores no se registran: los crea el administrador.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $esReclutador = $request->input('tipo_cuenta') === 'reclutador';

        $request->validate([
            'tipo_cuenta' => ['required', 'in:postulante,reclutador'],
            'nombre' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // Campos de la empresa: solo obligatorios para reclutadores
            'razon_social' => [$esReclutador ? 'required' : 'nullable', 'string', 'max:150'],
            'ruc' => [$esReclutador ? 'required' : 'nullable', 'regex:/^(10|20)\d{9}$/', 'unique:empresas,ruc'],
            'ubicacion_id' => [$esReclutador ? 'required' : 'nullable', 'exists:ubicaciones,id'],
        ], [
            'ruc.regex' => 'El RUC debe tener 11 dígitos y empezar con 10 o 20.',
            'ruc.unique' => 'Ya existe una empresa registrada con este RUC.',
        ], [
            'razon_social' => 'razón social',
            'ubicacion_id' => 'ubicación',
        ]);

        // Transacción: si algo falla, no queda un usuario sin perfil o sin empresa
        $user = DB::transaction(function () use ($request, $esReclutador) {
            $empresa = null;

            if ($esReclutador) {
                $empresa = Empresa::create([
                    'razon_social' => $request->razon_social,
                    'ruc' => $request->ruc,
                    'ubicacion_id' => $request->ubicacion_id,
                    'estado' => 'pendiente',
                ]);
            }

            $user = User::create([
                'rol_id' => Rol::where('slug', $esReclutador ? Rol::RECLUTADOR : Rol::POSTULANTE)->value('id'),
                'empresa_id' => $empresa?->id,
                'nombre' => $request->nombre,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            if (! $esReclutador) {
                PerfilPostulante::create(['usuario_id' => $user->id]);
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
