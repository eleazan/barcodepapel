<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RunBatchTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string,array<int,string>> */
    public function rules(): array
    {
        return [
            'cantidad' => ['required', 'integer', 'min:1', 'max:5000'],
        ];
    }

    /** @return array<string,string> */
    public function messages(): array
    {
        return [
            'cantidad.required' => 'Indica cuántos elementos quieres procesar.',
            'cantidad.integer'  => 'La cantidad tiene que ser un número entero.',
            'cantidad.min'      => 'Hay que procesar al menos un elemento.',
            'cantidad.max'      => 'Como máximo 5.000 elementos por lote.',
        ];
    }
}
