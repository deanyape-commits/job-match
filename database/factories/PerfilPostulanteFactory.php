<?php

namespace Database\Factories;

use App\Models\PerfilPostulante;
use App\Models\Ubicacion;
use App\Models\User;
use Database\Seeders\Datos\Textos;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PerfilPostulante>
 */
class PerfilPostulanteFactory extends Factory
{
    private const TITULARES = [
        'Desarrollador Backend', 'Desarrolladora Full Stack', 'Desarrollador Frontend', 'Analista de Datos',
        'Técnico en Soporte TI', 'Administrador de Redes', 'Analista QA', 'Diseñadora UX/UI',
        'Desarrollador Móvil', 'Ingeniero DevOps', 'Analista de Ciberseguridad', 'Egresado de Computación e Informática',
    ];

    public function definition(): array
    {
        $anios = fake()->numberBetween(0, 8);

        return [
            'usuario_id' => User::factory(),
            'ubicacion_id' => fn () => Ubicacion::inRandomOrder()->value('id'),
            'titular' => fake()->randomElement(self::TITULARES).($anios < 2 ? ' Jr.' : ''),
            'resumen' => fake()->randomElement(Textos::RESUMENES),
            'fecha_nacimiento' => fake()->dateTimeBetween('-40 years', '-19 years'),
            'anios_experiencia' => $anios,
            'salario_esperado' => fake()->numberBetween(12, 90) * 100,
            'linkedin_url' => 'https://linkedin.com/in/'.fake()->userName(),
        ];
    }
}
