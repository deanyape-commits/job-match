<?php

namespace Database\Factories;

use App\Models\PerfilPostulante;
use Database\Seeders\Datos\Textos;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Experiencia>
 */
class ExperienciaFactory extends Factory
{
    private const CARGOS = [
        'Practicante de desarrollo', 'Desarrollador Junior', 'Analista programador', 'Soporte técnico',
        'Asistente de TI', 'Desarrollador web', 'Analista de datos', 'Técnico de redes', 'QA Tester',
        'Desarrollador Full Stack', 'Administrador de sistemas', 'Diseñador UX/UI',
    ];

    private const EMPRESAS = [
        'Banco Andino', 'Retail Perú S.A.', 'Clínica San Gabriel', 'Minera del Sur', 'Agroexport Ica',
        'Universidad del Pacífico Norte', 'Municipalidad Provincial', 'Startup Kallpa', 'Consultora Q&A',
        'Telecom Andes', 'Logística Express', 'Seguros Cóndor',
    ];

    public function definition(): array
    {
        $inicio = fake()->dateTimeBetween('-8 years', '-6 months');
        $fin = fake()->boolean(70) ? fake()->dateTimeBetween($inicio, 'now') : null;

        return [
            'perfil_postulante_id' => PerfilPostulante::factory(),
            'empresa_nombre' => fake()->randomElement(self::EMPRESAS),
            'cargo' => fake()->randomElement(self::CARGOS),
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'descripcion' => fake()->randomElement(Textos::EXPERIENCIAS),
        ];
    }
}
