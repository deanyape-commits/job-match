<?php

use App\Http\Controllers\PanelController;
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
        Route::get('/', [PanelController::class, 'postulante'])->name('inicio');
    });

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
