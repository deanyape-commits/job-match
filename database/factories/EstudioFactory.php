<?php

namespace Database\Factories;

use App\Models\PerfilPostulante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estudio>
 */
class EstudioFactory extends Factory
{
    private const INSTITUCIONES = [
        'Tecsup', 'SENATI', 'Cibertec', 'IDAT', 'Universidad Nacional de Ingeniería',
        'Universidad Nacional Mayor de San Marcos', 'Universidad Nacional de San Agustín',
        'Pontificia Universidad Católica del Perú', 'Universidad Tecnológica del Perú', 'Instituto Toulouse Lautrec',
    ];

    private const CARRERAS = [
        'Diseño y Desarrollo de Software', 'Computación e Informática', 'Ingeniería de Sistemas',
        'Ingeniería de Software', 'Redes y Comunicaciones de Datos', 'Ciencia de Datos',
        'Ingeniería Informática', 'Administración de Redes y Comunicaciones', 'Diseño Gráfico Digital',
    ];

    public function definition(): array
    {
        $inicio = fake()->dateTimeBetween('-12 years', '-2 years');
        $grado = fake()->randomElement(['tecnico', 'tecnico', 'bachiller', 'titulado', 'curso']);

        return [
            'perfil_postulante_id' => PerfilPostulante::factory(),
            'institucion' => fake()->randomElement(self::INSTITUCIONES),
            'grado' => $grado,
            'carrera' => $grado === 'curso' ? fake()->randomElement(['AWS Cloud Practitioner', 'Scrum Master', 'Cisco CCNA', 'Power BI', 'Laravel avanzado']) : fake()->randomElement(self::CARRERAS),
            'fecha_inicio' => $inicio,
            'fecha_fin' => fake()->boolean(85) ? (clone $inicio)->modify('+'.($grado === 'curso' ? '3 months' : '3 years')) : null,
        ];
    }
}
