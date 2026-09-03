<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCartItemRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'variant_id' => [
                'nullable',
                'integer',
                Rule::exists('product_variants', 'id')->where('product_id', $this->input('product_id')),
            ],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'buy_now' => ['nullable', 'boolean'],
        ];
    }
}
