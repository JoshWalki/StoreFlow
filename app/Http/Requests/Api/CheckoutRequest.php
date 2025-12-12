<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'fulfilment_type' => ['required', 'string', Rule::in(['delivery', 'pickup'])],
            'contact' => ['required', 'array'],
            'contact.first_name' => ['required', 'string', 'max:255'],
            'contact.last_name' => ['required', 'string', 'max:255'],
            'contact.email' => ['required', 'email', 'max:255'],
            'contact.mobile' => ['nullable', 'string', 'max:20'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:1000'],
            'items.*.customizations' => ['nullable', 'array'],
            'items.*.customizations.*.group_id' => ['required', 'integer', 'exists:customization_groups,id'],
            'items.*.customizations.*.option_id' => ['required', 'integer', 'exists:customization_options,id'],
            'items.*.addons' => ['nullable', 'array'],
            'items.*.addons.*.addon_id' => ['nullable', 'integer', 'exists:product_addons,id'],
            'items.*.addons.*.addon_name' => ['nullable', 'string'],
            'items.*.addons.*.option_name' => ['nullable', 'string'],
            'items.*.addons.*.price_adjustment' => ['nullable', 'numeric'],
            'items.*.addons.*.quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'payment_intent_id' => ['nullable', 'string', 'starts_with:pi_'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'account' => ['nullable', 'array'],
            'account.password' => ['nullable', 'string', 'min:8'],
            'account.password_confirmation' => ['nullable', 'string', 'same:account.password'],
        ];

        // Add delivery-specific validation rules
        if ($this->input('fulfilment_type') === 'delivery') {
            $rules['shipping_address'] = ['required', 'array'];
            $rules['shipping_address.name'] = ['required', 'string', 'max:255'];
            $rules['shipping_address.line1'] = ['required', 'string', 'max:255'];
            $rules['shipping_address.line2'] = ['nullable', 'string', 'max:255'];
            $rules['shipping_address.city'] = ['required', 'string', 'max:100'];
            $rules['shipping_address.state'] = ['required', 'string', 'max:100'];
            $rules['shipping_address.postcode'] = ['required', 'string', 'max:20'];
            $rules['shipping_address.country'] = ['required', 'string', 'max:100'];
            $rules['shipping_method_id'] = ['required', 'integer', 'exists:shipping_methods,id'];
        }

        // Add pickup-specific validation rules
        if ($this->input('fulfilment_type') === 'pickup') {
            $rules['pickup_time'] = ['nullable', 'date', 'after:now'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'fulfilment_type.required' => 'The fulfilment type is required.',
            'fulfilment_type.in' => 'The fulfilment type must be either delivery or pickup.',
            'contact.required' => 'Contact information is required.',
            'contact.first_name.required' => 'The contact first name is required.',
            'contact.last_name.required' => 'The contact last name is required.',
            'contact.email.required' => 'The contact email is required.',
            'contact.email.email' => 'The contact email must be a valid email address.',
            'items.required' => 'At least one item is required.',
            'items.min' => 'At least one item is required.',
            'items.*.product_id.required' => 'Each item must have a product_id.',
            'items.*.product_id.exists' => 'The selected product does not exist.',
            'items.*.qty.required' => 'Each item must have a quantity.',
            'items.*.qty.min' => 'Item quantity must be at least 1.',
            'items.*.qty.max' => 'Item quantity cannot exceed 1000.',
            'shipping_address.required' => 'Shipping address is required for delivery orders.',
            'shipping_method_id.required' => 'Shipping method is required for delivery orders.',
            'shipping_method_id.exists' => 'The selected shipping method does not exist.',
            'pickup_time.after' => 'Pickup time must be in the future.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize fulfilment_type to lowercase
        if ($this->has('fulfilment_type')) {
            $this->merge([
                'fulfilment_type' => strtolower($this->fulfilment_type),
            ]);
        }
    }
}
