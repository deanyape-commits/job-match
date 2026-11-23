<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación del formulario de crear/editar vacante.
 */
class VacanteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La autorización por empresa se hace con VacantePolicy en el controlador
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:150'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'ubicacion_id' => ['required', 'exists:ubicaciones,id'],
            'nivel_experiencia' => ['required', 'in:practicante,junior,semi_senior,senior'],
            'modalidad' => ['required', 'in:presencial,remoto,hibrido'],
            'tipo_contrato' => ['required', 'in:tiempo_completo,medio_tiempo,practicas,freelance'],
            'salario_min' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'salario_max' => ['nullable', 'numeric', 'min:0', 'max:99999999', 'gte:salario_min'],
            'puestos' => ['required', 'integer', 'min:1', 'max:100'],
            'cierra_en' => ['nullable', 'date', 'after:today'],
            'descripcion' => ['required', 'string', 'max:5000'],
            'requisitos' => ['required', 'string', 'max:5000'],
            // habilidades[ID] = 1 (obligatoria) o 0 (deseable)
            'habilidades' => ['required', 'array', 'min:1', 'max:15'],
            'habilidades.*' => ['in:0,1'],
            'accion' => ['required', 'in:borrador,publicar'],
        ];
    }

    public function messages(): array
    {
        return [
            'habilidades.required' => 'Agrega al menos una habilidad: es lo que se usa para calcular el match.',
            'salario_max.gte' => 'El salario máximo debe ser mayor o igual que el mínimo.',
        ];
    }

    /** Valida además que los IDs de habilidades existan. */
    public function after(): array
    {
        return [
            function ($validator) {
                $ids = array_keys($this->input('habilidades', []));
                if ($ids && \App\Models\Habilidad::whereIn('id', $ids)->count() !== count($ids)) {
                    $validator->errors()->add('habilidades', 'Alguna habilidad seleccionada no existe.');
                }
            },
        ];
    }
}
