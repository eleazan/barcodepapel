<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NonWorkingDayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'starts_on'       => ['required', 'date'],
            'ends_on'         => ['required', 'date', 'after_or_equal:starts_on'],
            'recurs_annually' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required'          => 'Ponle un nombre al cierre (por ejemplo, «Navidad» o «Vacaciones de agosto»).',
            'starts_on.required'     => 'Indica el día en que empieza.',
            'ends_on.required'       => 'Indica el día en que termina.',
            'ends_on.after_or_equal' => 'El último día no puede ser anterior al primero.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name'      => 'nombre',
            'starts_on' => 'primer día',
            'ends_on'   => 'último día',
        ];
    }

    /**
     * Un cierre de un solo día se envía sin fecha de fin.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'ends_on'         => $this->filled('ends_on') ? $this->input('ends_on') : $this->input('starts_on'),
            'recurs_annually' => $this->boolean('recurs_annually'),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->boolean('recurs_annually')) {
                return;
            }

            // Un cierre que se repite cada año se compara por mes y día, así que
            // no puede cruzar el cambio de año.
            $inicio = (int) date('md', strtotime((string) $this->input('starts_on')));
            $fin    = (int) date('md', strtotime((string) $this->input('ends_on')));

            if ($fin < $inicio) {
                $validator->errors()->add(
                    'recurs_annually',
                    'Un cierre que se repite cada año no puede cruzar el cambio de año. Dalo de alta como dos cierres.',
                );
            }
        });
    }
}
