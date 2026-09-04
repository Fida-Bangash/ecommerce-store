<?php

namespace App\Http\Requests\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
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
            'reviewer_name' => ['required', 'string', 'max:255'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:1000'],
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
            'reviewer_name' => 'name',
            'rating' => 'star rating',
            'comment' => 'review',
        ];
    }
}
