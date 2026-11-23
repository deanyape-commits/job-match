<?php

namespace Database\Seeders\Datos;

use Illuminate\Support\Arr;

/**
 * Textos en español para los datos de prueba
 * (Faker es_PE genera nombres y empresas, pero los párrafos los da en latín).
 */
class Textos
{
    public const FUNCIONES = [
        'Desarrollar y mantener nuevas funcionalidades de los sistemas de la empresa.',
        'Participar en las reuniones diarias y en la planificación de cada sprint.',
        'Escribir pruebas automatizadas y documentar el código desarrollado.',
        'Atender y resolver las incidencias reportadas por los usuarios.',
        'Proponer mejoras en los procesos y herramientas del equipo.',
        'Coordinar con las áreas de negocio para entender sus necesidades.',
        'Participar en las revisiones de código del equipo.',
        'Optimizar consultas y mejorar el rendimiento de las aplicaciones.',
        'Elaborar reportes e indicadores para la toma de decisiones.',
        'Configurar, monitorear y dar mantenimiento a servidores y servicios.',
        'Apoyar en la capacitación de los nuevos integrantes del equipo.',
        'Cumplir con las políticas de seguridad de la información.',
    ];

    public const REQUISITOS = [
        'Egresado técnico o universitario de Computación, Sistemas o carreras afines.',
        'Experiencia mínima de 1 año en puestos similares.',
        'Conocimiento de control de versiones con Git.',
        'Disponibilidad para trabajar a tiempo completo.',
        'Inglés a nivel intermedio (deseable).',
        'Capacidad de trabajo en equipo y comunicación efectiva.',
        'Experiencia trabajando con metodologías ágiles (Scrum o Kanban).',
        'Orientación a resultados y ganas de seguir aprendiendo.',
        'Certificaciones relacionadas al puesto (deseable).',
        'Experiencia previa en proyectos para clientes del sector financiero o retail (deseable).',
    ];

    public const EMPRESAS = [
        'Empresa peruana de tecnología que desarrolla soluciones a medida para clientes de banca, retail y salud.',
        'Consultora de transformación digital con presencia en Lima y regiones.',
        'Startup que crea productos digitales para pequeñas y medianas empresas del Perú.',
        'Empresa de servicios cloud y ciberseguridad con más de 10 años en el mercado.',
        'Compañía de analítica de datos que ayuda a sus clientes a tomar mejores decisiones.',
        'Integrador de soluciones de redes y telecomunicaciones para el sector público y privado.',
    ];

    public const RESUMENES = [
        'Profesional de tecnología con experiencia en proyectos de desarrollo y ganas de seguir creciendo en un equipo ágil.',
        'Me apasiona resolver problemas con tecnología. Busco un puesto donde pueda aportar y aprender de profesionales con experiencia.',
        'Egresado con formación técnica sólida y experiencia en prácticas preprofesionales. Responsable, proactivo y orientado a resultados.',
        'Cuento con experiencia trabajando en equipos multidisciplinarios y bajo metodologías ágiles. Me adapto rápido a nuevas herramientas.',
        'Me interesa especializarme en mi área y asumir nuevos retos. Tengo buena comunicación y disposición para aprender.',
        'Profesional con varios años de experiencia liderando proyectos tecnológicos y acompañando a equipos de desarrollo.',
    ];

    public const EXPERIENCIAS = [
        'Desarrollo y mantenimiento de módulos internos de la empresa.',
        'Soporte a usuarios y atención de incidencias de primer y segundo nivel.',
        'Participación en la migración de sistemas a la nube.',
        'Elaboración de reportes y tableros para el área comercial.',
        'Implementación de pruebas automatizadas y mejora de la calidad del software.',
        'Administración de la red y de los equipos de la oficina principal.',
    ];

    public const CARTAS = [
        'Me interesa mucho este puesto porque se alinea con mi experiencia y con lo que quiero seguir aprendiendo. Quedo atento a su respuesta.',
        'Conozco el trabajo de su empresa y me encantaría formar parte del equipo. Creo que puedo aportar desde el primer día.',
        'Tengo experiencia en las tecnologías que solicitan y disponibilidad inmediata. Gracias por la oportunidad.',
        'Estoy buscando un nuevo reto profesional y esta vacante me parece ideal. Adjunto mi CV para su revisión.',
    ];

    public const NOTAS_ENTREVISTA = [
        'Buen dominio técnico. Pasar a la siguiente etapa.',
        'Buena comunicación, le falta experiencia en algunas herramientas.',
        'Candidato muy alineado con la cultura del equipo.',
        'Revisar referencias antes de tomar una decisión.',
    ];

    /** Varias frases al azar de un banco, como lista con guiones. */
    public static function lista(array $banco, int $min, int $max): string
    {
        return '- '.implode("\n- ", Arr::random($banco, rand($min, $max)));
    }
}
