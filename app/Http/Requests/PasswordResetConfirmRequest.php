<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasswordResetConfirmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'exists:users,email'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8'],
            'confirmed_password' => ['required', 'string', 'same:password'],
        ];
    }

    /**
     * Get custom error messages for validator.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'email.exists' => 'Email not found.',
            'confirmed_password.same' => 'Password and confirm password do not match.',
        ];
    }
}
