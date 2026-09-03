<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        $productId = $this->route('product')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('products', 'slug')->ignore($productId),
            ],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'regular_price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:regular_price'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', Rule::exists('product_images', 'id')->where('product_id', $productId)],
            'status' => ['required', 'in:active,inactive'],

            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', Rule::exists('product_variants', 'id')->where('product_id', $productId)],
            'variants.*.size' => ['nullable', 'string', 'max:50'],
            'variants.*.color' => ['nullable', 'string', 'max:50'],
            'variants.*.color_hex' => ['nullable', 'string', 'max:7'],
            'variants.*.stock_quantity' => ['required_with:variants', 'integer', 'min:0'],
            'variants.*.extra_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'product name',
            'slug' => 'product slug',
            'category_id' => 'category',
            'regular_price' => 'regular price',
            'discount_price' => 'discount price',
            'stock_quantity' => 'stock quantity',
            'description' => 'description',
            'images' => 'product images',
            'images.*' => 'product image',
            'status' => 'product status',
            'variants.*.size' => 'variant size',
            'variants.*.color' => 'variant color',
            'variants.*.stock_quantity' => 'variant stock quantity',
            'variants.*.extra_price' => 'variant extra price',
        ];
    }
}
