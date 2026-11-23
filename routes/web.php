<?php

use App\Http\Controllers\PanelController;
use App\Http\Controllers\ProfileController;
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
        Route::get('/', [PanelController::class, 'reclutador'])->name('inicio');
    });

    // ---------------- Supervisor (el administrador también puede entrar) ----------------
    Route::middleware('rol:supervisor,administrador')->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::get('/', [PanelController::class, 'supervisor'])->name('inicio');
    });

    // ---------------- Administrador ----------------
    Route::middleware('rol:administrador')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [PanelController::class, 'admin'])->name('inicio');
    });
});

require __DIR__.'/auth.php';
