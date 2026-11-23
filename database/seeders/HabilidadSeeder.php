<?php

namespace Database\Seeders;

use App\Models\Habilidad;
use Illuminate\Database\Seeder;

/**
 * Catálogo de habilidades técnicas, blandas e idiomas.
 * Son las que se cruzan entre postulantes y vacantes para calcular el match.
 */
class HabilidadSeeder extends Seeder
{
    public function run(): void
    {
        $habilidades = [
            'tecnica' => [
                // Lenguajes
                'PHP', 'JavaScript', 'TypeScript', 'Python', 'Java', 'C#', 'Go', 'Kotlin', 'Swift', 'Dart', 'C++', 'Ruby',
                // Frameworks y librerías
                'Laravel', 'Symfony', 'Node.js', 'Express', 'NestJS', 'React', 'Vue.js', 'Angular', 'Next.js',
                'Spring Boot', '.NET', 'Django', 'Flask', 'FastAPI', 'Flutter', 'React Native', 'Tailwind CSS', 'Bootstrap',
                // Bases de datos
                'SQL', 'PostgreSQL', 'MySQL', 'SQL Server', 'Oracle', 'MongoDB', 'Redis',
                // Cloud y DevOps
                'Git', 'Docker', 'Kubernetes', 'AWS', 'Azure', 'Google Cloud', 'Linux', 'CI/CD', 'Terraform', 'Nginx',
                // Datos e IA
                'Power BI', 'Excel avanzado', 'Pandas', 'Machine Learning', 'TensorFlow', 'ETL', 'Spark',
                // Calidad
                'PHPUnit', 'Jest', 'Selenium', 'Cypress', 'Pruebas manuales',
                // Redes y seguridad
                'Redes TCP/IP', 'Cisco CCNA', 'Firewalls', 'Pentesting', 'ISO 27001', 'Active Directory', 'Soporte Windows',
                // Diseño y gestión
                'Figma', 'Diseño UX', 'Scrum', 'Jira', 'APIs REST', 'GraphQL', 'Microservicios',
            ],
            'blanda' => [
                'Trabajo en equipo', 'Comunicación efectiva', 'Resolución de problemas',
                'Liderazgo', 'Pensamiento crítico', 'Adaptabilidad', 'Gestión del tiempo',
            ],
            'idioma' => [
                'Inglés intermedio', 'Inglés avanzado', 'Portugués',
            ],
        ];

        foreach ($habilidades as $tipo => $nombres) {
            foreach ($nombres as $nombre) {
                Habilidad::firstOrCreate(['nombre' => $nombre], ['tipo' => $tipo]);
            }
        }
    }
}
