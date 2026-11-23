<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Subir o reemplazar la foto de perfil (todos los roles).
     * Se guarda en storage/app/public/avatars y se ve por /storage/avatars/...
     */
    public function actualizarFoto(Request $request): RedirectResponse
    {
        $request->validateWithBag('foto', [
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // 2 MB
        ], [], ['foto' => 'foto']);

        $user = $request->user();

        if ($user->avatar_ruta) {
            Storage::disk('public')->delete($user->avatar_ruta);
        }

        $user->update(['avatar_ruta' => $request->file('foto')->store('avatars', 'public')]);

        return Redirect::route('profile.edit')->with('exito', 'Tu foto de perfil se actualizó.');
    }

    public function eliminarFoto(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar_ruta) {
            Storage::disk('public')->delete($user->avatar_ruta);
            $user->update(['avatar_ruta' => null]);
        }

        return Redirect::route('profile.edit')->with('exito', 'Quitaste tu foto de perfil.');
    }

    /**
     * Delete the user's account.
     * Solo los postulantes pueden eliminar su propia cuenta: los reclutadores tienen
     * vacantes asociadas y las cuentas internas las gestiona el administrador.
     */
    public function destroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()->tieneRol('postulante'), 403);

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->avatar_ruta) {
            Storage::disk('public')->delete($user->avatar_ruta);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
