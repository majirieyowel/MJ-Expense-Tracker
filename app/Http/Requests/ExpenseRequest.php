<?php

namespace App\Http\Requests;

use App\Rules\ExpenseDateLimit;
use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
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
            "item" => "required|string",
            "amount" => "required|numeric|gt:0",
            "date" => ["required", new ExpenseDateLimit]
        ];
    }
}
