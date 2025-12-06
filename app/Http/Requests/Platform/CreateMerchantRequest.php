<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class CreateMerchantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if platform is authenticated
        return session('platform_authenticated') === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'merchant_name' => ['required', 'string', 'max:255'],
            'merchant_slug' => ['nullable', 'string', 'max:255', 'unique:merchants,slug'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'owner_username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'owner_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'merchant_name.required' => 'Merchant name is required.',
            'owner_name.required' => 'Owner name is required.',
            'owner_email.required' => 'Owner email is required.',
            'owner_email.unique' => 'This email is already registered.',
            'owner_username.required' => 'Owner username is required.',
            'owner_username.unique' => 'This username is already taken.',
            'owner_password.required' => 'Password is required.',
            'owner_password.min' => 'Password must be at least 8 characters.',
            'owner_password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
