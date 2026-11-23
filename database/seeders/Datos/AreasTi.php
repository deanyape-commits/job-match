<?php

namespace Database\Seeders\Datos;

/**
 * Datos realistas por área (slug de la categoría):
 * puestos típicos y habilidades técnicas que suelen pedirse.
 * Los nombres de habilidades coinciden con HabilidadSeeder.
 */
class AreasTi
{
    public const AREAS = [
        'desarrollo-de-software' => [
            'puestos' => ['Desarrollador Backend PHP', 'Desarrollador Java Spring Boot', 'Desarrollador .NET', 'Analista Programador', 'Desarrollador Python', 'Desarrollador Backend Node.js'],
            'titulares' => ['Desarrollador Backend', 'Analista Programador', 'Desarrollador de Software'],
            'habilidades' => ['PHP', 'Laravel', 'Java', 'Spring Boot', 'C#', '.NET', 'Python', 'Node.js', 'SQL', 'PostgreSQL', 'MySQL', 'Git', 'APIs REST', 'Docker', 'Microservicios', 'PHPUnit'],
        ],
        'desarrollo-web' => [
            'puestos' => ['Desarrollador Full Stack Laravel + Vue', 'Desarrollador Frontend React', 'Desarrollador Web', 'Practicante de Desarrollo Web', 'Desarrollador Angular'],
            'titulares' => ['Desarrollador Full Stack', 'Desarrolladora Frontend', 'Desarrollador Web'],
            'habilidades' => ['JavaScript', 'TypeScript', 'React', 'Vue.js', 'Angular', 'Next.js', 'Laravel', 'PHP', 'Node.js', 'Tailwind CSS', 'Bootstrap', 'MySQL', 'Git', 'APIs REST'],
        ],
        'desarrollo-movil' => [
            'puestos' => ['Desarrollador Móvil Flutter', 'Desarrollador Android Kotlin', 'Desarrollador iOS Swift', 'Desarrollador React Native'],
            'titulares' => ['Desarrollador Móvil', 'Desarrolladora Android'],
            'habilidades' => ['Flutter', 'Dart', 'Kotlin', 'Swift', 'React Native', 'JavaScript', 'Git', 'APIs REST', 'GraphQL'],
        ],
        'datos-y-analitica' => [
            'puestos' => ['Analista de Datos', 'Analista BI', 'Ingeniero de Datos', 'Practicante de Analítica'],
            'titulares' => ['Analista de Datos', 'Analista BI', 'Ingeniera de Datos'],
            'habilidades' => ['SQL', 'Python', 'Power BI', 'Excel avanzado', 'Pandas', 'ETL', 'PostgreSQL', 'SQL Server', 'Spark', 'Oracle'],
        ],
        'inteligencia-artificial' => [
            'puestos' => ['Científico de Datos', 'Ingeniero de Machine Learning', 'Desarrollador de IA'],
            'titulares' => ['Científica de Datos', 'Ingeniero de Machine Learning'],
            'habilidades' => ['Python', 'Machine Learning', 'TensorFlow', 'Pandas', 'SQL', 'FastAPI', 'Spark', 'Docker'],
        ],
        'ciberseguridad' => [
            'puestos' => ['Analista de Ciberseguridad', 'Especialista en Seguridad de la Información', 'Pentester'],
            'titulares' => ['Analista de Ciberseguridad', 'Especialista en Seguridad'],
            'habilidades' => ['Pentesting', 'ISO 27001', 'Firewalls', 'Linux', 'Redes TCP/IP', 'Active Directory', 'Python', 'Azure'],
        ],
        'redes-y-telecomunicaciones' => [
            'puestos' => ['Administrador de Redes', 'Técnico de Telecomunicaciones', 'Ingeniero de Redes'],
            'titulares' => ['Administrador de Redes', 'Técnico en Redes'],
            'habilidades' => ['Redes TCP/IP', 'Cisco CCNA', 'Firewalls', 'Linux', 'Soporte Windows', 'Active Directory'],
        ],
        'cloud-y-devops' => [
            'puestos' => ['Ingeniero DevOps', 'Arquitecto Cloud', 'Ingeniero SRE'],
            'titulares' => ['Ingeniero DevOps', 'Ingeniera Cloud'],
            'habilidades' => ['Docker', 'Kubernetes', 'AWS', 'Azure', 'Google Cloud', 'Linux', 'CI/CD', 'Terraform', 'Nginx', 'Git'],
        ],
        'soporte-ti' => [
            'puestos' => ['Técnico de Soporte TI', 'Practicante de Soporte TI', 'Analista de Mesa de Ayuda'],
            'titulares' => ['Técnico en Soporte TI', 'Asistente de Mesa de Ayuda'],
            'habilidades' => ['Soporte Windows', 'Active Directory', 'Redes TCP/IP', 'Linux', 'Excel avanzado'],
        ],
        'qa-y-testing' => [
            'puestos' => ['QA Tester', 'Analista QA Automatizador', 'Practicante de QA'],
            'titulares' => ['Analista QA', 'QA Tester'],
            'habilidades' => ['Selenium', 'Cypress', 'Jest', 'PHPUnit', 'Pruebas manuales', 'SQL', 'Jira', 'Scrum', 'JavaScript'],
        ],
        'diseno-uxui' => [
            'puestos' => ['Diseñador UX/UI', 'Diseñadora de Producto', 'Practicante de Diseño UX'],
            'titulares' => ['Diseñadora UX/UI', 'Diseñador de Producto'],
            'habilidades' => ['Figma', 'Diseño UX', 'Tailwind CSS', 'JavaScript', 'Bootstrap'],
        ],
        'gestion-de-proyectos-ti' => [
            'puestos' => ['Jefe de Proyectos TI', 'Scrum Master', 'Product Owner'],
            'titulares' => ['Jefa de Proyectos TI', 'Scrum Master'],
            'habilidades' => ['Scrum', 'Jira', 'Excel avanzado', 'Microservicios', 'APIs REST'],
        ],
    ];

    /** Habilidades blandas e idiomas que se agregan como "deseables". */
    public const COMPLEMENTARIAS = [
        'Trabajo en equipo', 'Comunicación efectiva', 'Resolución de problemas', 'Liderazgo',
        'Pensamiento crítico', 'Adaptabilidad', 'Gestión del tiempo', 'Inglés intermedio', 'Inglés avanzado',
    ];
}
