<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vacante>
 */
class VacanteFactory extends Factory
{
    private const PUESTOS = [
        'Desarrollador Backend PHP', 'Desarrollador Full Stack Laravel + Vue', 'Desarrollador Frontend React',
        'Analista de Datos', 'Practicante de Desarrollo Web', 'Desarrollador Python', 'Administrador de Redes',
        'Analista de Ciberseguridad', 'Técnico de Soporte TI', 'QA Tester', 'Ingeniero DevOps',
        'Desarrollador Móvil Flutter', 'Diseñador UX/UI', 'Analista de Sistemas', 'Desarrollador Java Spring Boot',
        'Científico de Datos', 'Desarrollador .NET', 'Jefe de Proyectos TI',
    ];

    public function definition(): array
    {
        $titulo = fake()->randomElement(self::PUESTOS);
        $nivel = fake()->randomElement(['practicante', 'junior', 'junior', 'semi_senior', 'senior']);
        $base = ['practicante' => 1100, 'junior' => 2800, 'semi_senior' => 4500, 'senior' => 7000][$nivel];

        return [
            'empresa_id' => Empresa::factory(),
            // El reclutador debe ser de la misma empresa
            'reclutador_id' => fn (array $atributos) => User::factory()->reclutador(Empresa::find($atributos['empresa_id'])),
            'categoria_id' => fn () => Categoria::inRandomOrder()->value('id'),
            'ubicacion_id' => fn () => Ubicacion::inRandomOrder()->value('id'),
            'titulo' => $titulo.($nivel === 'senior' ? ' Sr.' : ($nivel === 'junior' ? ' Jr.' : '')),
            'slug' => fn (array $atributos) => Str::slug($atributos['titulo']).'-'.Str::lower(Str::random(6)),
            'descripcion' => implode("\n\n", fake()->paragraphs(3)),
            'requisitos' => implode("\n", array_map(fn ($r) => '- '.$r, fake()->sentences(4))),
            'modalidad' => fake()->randomElement(['presencial', 'remoto', 'hibrido']),
            'tipo_contrato' => $nivel === 'practicante' ? 'practicas' : fake()->randomElement(['tiempo_completo', 'tiempo_completo', 'medio_tiempo', 'freelance']),
            'nivel_experiencia' => $nivel,
            'salario_min' => $base,
            'salario_max' => $nivel === 'practicante' ? $base : $base + fake()->numberBetween(5, 20) * 100,
            'puestos' => fake()->numberBetween(1, 3),
            'estado' => 'publicada',
            'publicada_en' => fake()->dateTimeBetween('-5 months', 'now'),
            'cierra_en' => fake()->dateTimeBetween('+1 week', '+2 months'),
        ];
    }

    public function borrador(): static
    {
        return $this->state(fn () => ['estado' => 'borrador', 'publicada_en' => null]);
    }

    public function cerrada(): static
    {
        return $this->state(fn () => ['estado' => 'cerrada']);
    }
}
