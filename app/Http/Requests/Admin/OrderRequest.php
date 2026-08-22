<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name'      => ['required', 'string', 'max:255'],
            'customer_email'     => ['nullable', 'email', 'max:255'],
            'customer_phone'     => ['required', 'string', 'max:20'],
            'delivery_address'   => ['required', 'string', 'max:500'],
            'postal_code'        => ['required', 'string', 'max:10'],
            'status'             => ['required', Rule::in(array_keys(Order::STATUSES))],
            'delivery_fee'       => ['required', 'numeric', 'min:0'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_name.required'    => 'El nombre del cliente es obligatorio.',
            'customer_phone.required'   => 'El teléfono es obligatorio.',
            'delivery_address.required' => 'La dirección de entrega es obligatoria.',
            'postal_code.required'      => 'El código postal es obligatorio.',
            'items.required'            => 'Agrega al menos un producto al pedido.',
            'items.min'                 => 'Agrega al menos un producto al pedido.',
        ];
    }
}
