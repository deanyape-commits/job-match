<?php

namespace Database\Factories;

use App\Models\Ubicacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empresa>
 */
class EmpresaFactory extends Factory
{
    private const PREFIJOS = ['Andina', 'Inka', 'NubeSur', 'DataPerú', 'Cóndor', 'Pacífico', 'Altiplano', 'Moche', 'Chavín', 'Amazonía', 'Qhapaq', 'Nazca', 'Lima', 'Sierra', 'Kallpa', 'Wari', 'Paracas', 'Titicaca'];

    private const SUFIJOS = ['Software', 'Tech', 'Labs', 'Digital', 'Systems', 'Cloud', 'Data', 'Fintech', 'Solutions', 'Consulting', 'Analytics', 'Code', 'Networks', 'Security'];

    public function definition(): array
    {
        $nombre = fake()->randomElement(self::PREFIJOS).' '.fake()->randomElement(self::SUFIJOS);

        return [
            'ubicacion_id' => fn () => Ubicacion::inRandomOrder()->value('id'),
            'razon_social' => $nombre.' '.fake()->randomElement(['S.A.C.', 'S.A.', 'E.I.R.L.', 'S.R.L.']),
            'ruc' => '20'.fake()->unique()->numerify('#########'),
            'descripcion' => fake()->paragraph(3),
            'sitio_web' => 'https://'.strtolower(str_replace([' ', 'ú', 'ó', 'í'], ['', 'u', 'o', 'i'], $nombre)).'.pe',
            'tamano' => fake()->randomElement(['1-10', '11-50', '51-200', '201-500', '500+']),
            'estado' => 'verificada',
            'verificado_en' => now()->subDays(fake()->numberBetween(1, 200)),
        ];
    }

    public function pendiente(): static
    {
        return $this->state(fn () => ['estado' => 'pendiente', 'verificado_en' => null]);
    }

    public function rechazada(): static
    {
        return $this->state(fn () => ['estado' => 'rechazada']);
    }
}
