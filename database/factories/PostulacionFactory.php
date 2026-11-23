<?php

namespace Database\Factories;

use App\Models\PerfilPostulante;
use App\Models\Postulacion;
use App\Models\Vacante;
use Database\Seeders\Datos\Textos;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Postulacion>
 */
class PostulacionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vacante_id' => Vacante::factory(),
            'perfil_postulante_id' => PerfilPostulante::factory(),
            'carta_presentacion' => fake()->boolean(40) ? fake()->randomElement(Textos::CARTAS) : null,
            'porcentaje_match' => fake()->randomFloat(2, 10, 100),
            'estado' => 'postulado',
        ];
    }
}
