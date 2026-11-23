<?php

namespace Database\Seeders;

use App\Models\Ubicacion;
use Illuminate\Database\Seeder;

/**
 * Catálogo de ubicaciones: los 24 departamentos del Perú y la Provincia
 * Constitucional del Callao, con sus provincias más relevantes.
 */
class UbicacionSeeder extends Seeder
{
    public function run(): void
    {
        $ubicaciones = [
            'Amazonas' => ['Chachapoyas'],
            'Áncash' => ['Huaraz', 'Santa'],
            'Apurímac' => ['Abancay', 'Andahuaylas'],
            'Arequipa' => ['Arequipa', 'Camaná'],
            'Ayacucho' => ['Huamanga'],
            'Cajamarca' => ['Cajamarca', 'Jaén'],
            'Callao' => ['Callao'],
            'Cusco' => ['Cusco', 'La Convención'],
            'Huancavelica' => ['Huancavelica'],
            'Huánuco' => ['Huánuco'],
            'Ica' => ['Ica', 'Chincha', 'Pisco'],
            'Junín' => ['Huancayo', 'Tarma'],
            'La Libertad' => ['Trujillo', 'Pacasmayo'],
            'Lambayeque' => ['Chiclayo', 'Lambayeque'],
            'Lima' => ['Lima', 'Huaral', 'Cañete', 'Huaura'],
            'Loreto' => ['Maynas'],
            'Madre de Dios' => ['Tambopata'],
            'Moquegua' => ['Mariscal Nieto', 'Ilo'],
            'Pasco' => ['Pasco'],
            'Piura' => ['Piura', 'Sullana', 'Talara'],
            'Puno' => ['Puno', 'San Román'],
            'San Martín' => ['Moyobamba', 'San Martín'],
            'Tacna' => ['Tacna'],
            'Tumbes' => ['Tumbes'],
            'Ucayali' => ['Coronel Portillo'],
        ];

        foreach ($ubicaciones as $departamento => $provincias) {
            foreach ($provincias as $provincia) {
                Ubicacion::firstOrCreate(['departamento' => $departamento, 'provincia' => $provincia]);
            }
        }
    }
}
