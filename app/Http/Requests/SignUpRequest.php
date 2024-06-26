<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SignUpRequest extends FormRequest
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
            "username" => ["required", "string", "regex:/^[a-zA-Z0-9_ ]+$/", "max:20"],
            "email" => "required|email:rfc,dns|unique:users",
            "password" => ["required", Password::min(8)]
        ];
    }

    public function messages()
    {
        return [
            "username.regex" => "Username can only contain letters, numbers, spaces, or underscores."
        ];
    }
}
