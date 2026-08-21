<?php

declare(strict_types=1);

namespace App\Http\Requests\Store;

use App\Rules\CodigoPostalConReparto;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
            'customer_name'      => ['required', 'string', 'min:3', 'max:255'],
            'customer_email'     => ['nullable', 'email:rfc', 'max:255'],
            'customer_phone'     => ['required', 'string', 'min:9', 'max:30', 'regex:/^[0-9+\s().-]+$/'],
            'delivery_address'   => ['required', 'string', 'min:5', 'max:500'],
            'postal_code'        => ['required', 'digits:5', app(CodigoPostalConReparto::class)],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'acepta_condiciones' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required'      => 'Necesitamos tu nombre para el reparto.',
            'customer_name.min'           => 'El nombre es demasiado corto.',
            'customer_email.email'        => 'Introduce un email válido para enviarte la confirmación.',
            'customer_phone.required'     => 'El teléfono es obligatorio: lo usamos para avisarte de la entrega.',
            'customer_phone.min'          => 'El teléfono es demasiado corto.',
            'customer_phone.regex'        => 'El teléfono solo puede contener números y los signos + ( ) - y espacios.',
            'delivery_address.required'   => 'Indica la dirección de entrega.',
            'delivery_address.min'        => 'La dirección es demasiado corta. Incluye calle, número y piso.',
            'postal_code.required'        => 'El código postal es obligatorio.',
            'postal_code.digits'          => 'El código postal debe tener 5 dígitos.',
            'notes.max'                   => 'Las indicaciones no pueden superar los 1.000 caracteres.',
            'acepta_condiciones.accepted' => 'Debes aceptar las condiciones de venta y reparto.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'customer_name'    => 'nombre',
            'customer_email'   => 'email',
            'customer_phone'   => 'teléfono',
            'delivery_address' => 'dirección de entrega',
            'postal_code'      => 'código postal',
            'notes'            => 'indicaciones',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'postal_code'    => trim((string) $this->input('postal_code')),
            'customer_name'  => trim((string) $this->input('customer_name')),
            'customer_email' => $this->filled('customer_email')
                ? trim((string) $this->input('customer_email'))
                : null,
        ]);
    }

    /**
     * Datos listos para PlaceOrderService, sin el checkbox de condiciones.
     *
     * @return array<string, mixed>
     */
    public function orderData(): array
    {
        return collect($this->validated())
            ->except('acepta_condiciones')
            ->all();
    }
}
