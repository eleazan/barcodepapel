<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $zoneId = $this->route('delivery_zone')?->id;

        return [
            'postal_code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('delivery_zones')->where(function ($query) {
                    return $query->where('neighborhood', $this->input('neighborhood'));
                })->ignore($zoneId),
            ],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'delivery_fee' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'postal_code.required' => 'El código postal es obligatorio.',
            'postal_code.unique' => 'Ya existe una zona con este código postal y colonia.',
            'delivery_fee.required' => 'La tarifa de envío es obligatoria.',
        ];
    }
}
