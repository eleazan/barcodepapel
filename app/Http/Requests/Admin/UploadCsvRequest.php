<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'csv' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
        ];
    }

    public function messages(): array
    {
        return [
            'csv.required' => 'Debes seleccionar un archivo CSV.',
            'csv.file'     => 'El archivo no es válido.',
            'csv.mimes'    => 'El archivo debe ser un CSV (.csv).',
            'csv.max'      => 'El archivo no puede superar los 20 MB.',
        ];
    }
}
