<?php

use App\Http\Controllers\CvController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\Postulante;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reclutador;
use App\Http\Controllers\Supervisor;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Breeze redirige a "dashboard" después del login/registro.
// Esta ruta envía a cada usuario al panel de su rol.
Route::get('/panel', [PanelController::class, 'redirigir'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ---------------- Postulante ----------------
    Route::middleware('rol:postulante')->prefix('postulante')->name('postulante.')->group(function () {
        Route::get('/', fn () => redirect()->route('postulante.vacantes.index'))->name('inicio');

        // Buscar vacantes y detalle (por slug: /postulante/vacantes/desarrollador-laravel-ab12cd)
        Route::get('vacantes', [Postulante\VacanteController::class, 'index'])->name('vacantes.index');
        Route::get('vacantes/{vacante:slug}', [Postulante\VacanteController::class, 'show'])->name('vacantes.show');
        Route::post('vacantes/{vacante:slug}/guardar', [Postulante\VacanteController::class, 'guardar'])->name('vacantes.guardar');
        Route::post('vacantes/{vacante:slug}/postular', [Postulante\PostulacionController::class, 'store'])->name('vacantes.postular');

        Route::get('postulaciones', [Postulante\PostulacionController::class, 'index'])->name('postulaciones.index');
        Route::get('postulaciones/{postulacion}', [Postulante\PostulacionController::class, 'show'])->name('postulaciones.show');

        Route::get('guardados', Postulante\GuardadoController::class)->name('guardados.index');

        // Perfil profesional
        Route::get('perfil', [Postulante\PerfilController::class, 'edit'])->name('perfil.edit');
        Route::patch('perfil', [Postulante\PerfilController::class, 'update'])->name('perfil.update');
        Route::put('perfil/habilidades', [Postulante\PerfilController::class, 'habilidades'])->name('perfil.habilidades');
        Route::post('perfil/experiencias', [Postulante\PerfilController::class, 'agregarExperiencia'])->name('perfil.experiencias.store');
        Route::delete('perfil/experiencias/{experiencia}', [Postulante\PerfilController::class, 'eliminarExperiencia'])->name('perfil.experiencias.destroy');
        Route::post('perfil/estudios', [Postulante\PerfilController::class, 'agregarEstudio'])->name('perfil.estudios.store');
        Route::delete('perfil/estudios/{estudio}', [Postulante\PerfilController::class, 'eliminarEstudio'])->name('perfil.estudios.destroy');
    });

    // CV protegido (lo ven el dueño, los reclutadores a cuyas vacantes postuló, supervisores y admin)
    Route::get('cv/{perfil}', CvController::class)->name('cv.show');

    // ---------------- Reclutador ----------------
    Route::middleware('rol:reclutador')->prefix('reclutador')->name('reclutador.')->group(function () {
        Route::get('/', Reclutador\DashboardController::class)->name('inicio');

        Route::resource('vacantes', Reclutador\VacanteController::class)
            ->except('show')
            ->parameters(['vacantes' => 'vacante']);
        Route::patch('vacantes/{vacante}/cerrar', [Reclutador\VacanteController::class, 'cerrar'])->name('vacantes.cerrar');
    });

    // ---------------- Supervisor (el administrador también puede entrar) ----------------
    Route::middleware('rol:supervisor,administrador')->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/', fn () => redirect()->route('supervisor.empresas.index'))->name('inicio');

        Route::get('empresas', [Supervisor\EmpresaController::class, 'index'])->name('empresas.index');
        Route::patch('empresas/{empresa}/verificar', [Supervisor\EmpresaController::class, 'verificar'])->name('empresas.verificar');
        Route::patch('empresas/{empresa}/rechazar', [Supervisor\EmpresaController::class, 'rechazar'])->name('empresas.rechazar');
    });

    // ---------------- Administrador ----------------
    Route::middleware('rol:administrador')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [PanelController::class, 'admin'])->name('inicio');
    });
});

require __DIR__.'/auth.php';
