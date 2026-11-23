<?php

namespace Database\Factories;

use App\Models\Entrevista;
use App\Models\Postulacion;
use Database\Seeders\Datos\Textos;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entrevista>
 */
class EntrevistaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'postulacion_id' => Postulacion::factory(),
            'fecha_hora' => fake()->dateTimeBetween('+1 day', '+10 days')->setTime(fake()->numberBetween(9, 17), fake()->randomElement([0, 30])),
            'duracion_minutos' => fake()->randomElement([30, 30, 45, 60]),
            'enlace_reunion' => fn () => Entrevista::generarEnlaceJitsi(),
            'estado' => 'programada',
            'notas' => null,
        ];
    }

    public function realizada(): static
    {
        return $this->state(fn () => [
            'fecha_hora' => fake()->dateTimeBetween('-2 months', '-1 day'),
            'estado' => 'realizada',
            'notas' => fake()->randomElement(Textos::NOTAS_ENTREVISTA),
        ]);
    }
}
