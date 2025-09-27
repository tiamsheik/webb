<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'content_manager']);
    }

    public function rules(): array
    {
        $currentYear = date('Y');
        
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'unique:content,title'
            ],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:2000'
            ],
            'category' => [
                'required',
                'string',
                'in:movie,tv_show,documentary,original'
            ],
            'duration' => [
                'required',
                'integer',
                'min:1',
                'max:1000' 
            ],
            'release_year' => [
                'required',
                'integer',
                'min:1900',
                'max:' . ($currentYear + 5)
            ],
            'rating' => [
                'required',
                'string',
                'in:G,PG,PG-13,R,NC-17'
            ],
            'thumbnail_url' => [
                'required',
                'url',
                'max:500',
                'active_url'
            ],
            'video_url' => [
                'required',
                'url',
                'max:500'
            ],
            'genres' => [
                'required',
                'array',
                'min:1',
                'max:5'
            ],
            'genres.*' => [
                'integer',
                'exists:genres,genre_id'
            ],
            'is_featured' => [
                'boolean'
            ],
            'is_active' => [
                'boolean'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Content title is required',
            'title.max' => 'Title cannot exceed 255 characters',
            'title.unique' => 'Content with this title already exists',
            
            'description.required' => 'Description is required',
            'description.min' => 'Description must be at least 10 characters',
            'description.max' => 'Description cannot exceed 2000 characters',
            
            'category.required' => 'Category is required',
            'category.in' => 'Invalid category selected',
            
            'duration.required' => 'Duration is required',
            'duration.integer' => 'Duration must be a number',
            'duration.min' => 'Duration must be at least 1 minute',
            'duration.max' => 'Duration cannot exceed 1000 minutes',
            
            'release_year.required' => 'Release year is required',
            'release_year.integer' => 'Release year must be a number',
            'release_year.min' => 'Release year must be after 1900',
            'release_year.max' => 'Release year cannot be in the future',
            
            'rating.required' => 'Content rating is required',
            'rating.in' => 'Invalid rating selected',
            
            'thumbnail_url.required' => 'Thumbnail URL is required',
            'thumbnail_url.url' => 'Please provide a valid thumbnail URL',
            'thumbnail_url.active_url' => 'Thumbnail URL must be accessible',
            
            'video_url.required' => 'Video URL is required',
            'video_url.url' => 'Please provide a valid video URL',
            
            'genres.required' => 'At least one genre is required',
            'genres.array' => 'Genres must be provided as an array',
            'genres.min' => 'At least one genre is required',
            'genres.max' => 'Cannot assign more than 5 genres',
            'genres.*.integer' => 'Invalid genre format',
            'genres.*.exists' => 'Selected genre does not exist',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'is_featured' => $this->boolean('is_featured', false),
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}