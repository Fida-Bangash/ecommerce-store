<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // "action" bumps quantity by one step; "quantity" sets it explicitly.
            'action' => ['nullable', 'in:increment,decrement'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
