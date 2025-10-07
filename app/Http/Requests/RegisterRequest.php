<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
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
            'email.unique' => 'Email is already registered.',
            'confirmed_password.same' => 'Password and confirm password do not match.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();

        // Check for specific errors and return appropriate status codes
        if ($errors->has('email') && str_contains($errors->first('email'), 'already registered')) {
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Email is already registered.',
                'errors' => $errors
            ], 409));
        }

        if ($errors->has('confirmed_password')) {
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Password and confirm password do not match.',
                'errors' => $errors
            ], 400));
        }

        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed.',
            'errors' => $errors
        ], 400));
    }
}
