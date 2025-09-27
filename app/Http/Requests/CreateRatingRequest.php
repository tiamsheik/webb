<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'content_id' => [
                'required',
                'integer',
                'exists:content,content_id'
            ],
            'rating' => [
                'required',
                'integer',
                'min:1',
                'max:5'
            ],
            'review' => [
                'nullable',
                'string',
                'min:10',
                'max:1000'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'content_id.required' => 'Content ID is required',
            'content_id.exists' => 'The specified content does not exist',
            
            'rating.required' => 'Rating is required',
            'rating.integer' => 'Rating must be a number',
            'rating.min' => 'Rating must be at least 1 star',
            'rating.max' => 'Rating cannot exceed 5 stars',
            
            'review.min' => 'Review must be at least 10 characters',
            'review.max' => 'Review cannot exceed 1000 characters',
        ];
    }
}