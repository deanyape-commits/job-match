<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

/**
 * Primer seeder de la jerarquía: los roles deben existir antes que los usuarios.
 * Usa updateOrCreate para poder ejecutarse varias veces sin duplicar datos.
 */
class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [Rol::ADMINISTRADOR, 'Administrador', 'Acceso total. Gestiona usuarios, catálogos y reportes.'],
            [Rol::SUPERVISOR, 'Supervisor', 'Valida empresas y modera vacantes reportadas.'],
            [Rol::RECLUTADOR, 'Reclutador', 'Publica vacantes, revisa postulantes y agenda entrevistas.'],
            [Rol::POSTULANTE, 'Postulante', 'Busca vacantes, postula y sigue sus procesos.'],
        ];

        foreach ($roles as [$slug, $nombre, $descripcion]) {
            Rol::updateOrCreate(['slug' => $slug], ['nombre' => $nombre, 'descripcion' => $descripcion]);
        }
    }
}
