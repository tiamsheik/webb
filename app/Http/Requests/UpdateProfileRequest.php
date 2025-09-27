<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        $userId = auth()->id();

        return [
            'first_name' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s\-\.\']+$/'
            ],
            'last_name' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s\-\.\']+$/'
            ],
            'avatar_url' => [
                'nullable',
                'url',
                'max:500',
                'active_url'
            ],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId, 'user_id'),
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'first_name.max' => 'First name cannot exceed 50 characters',
            'first_name.regex' => 'First name contains invalid characters',
            
            'last_name.required' => 'Last name is required',
            'last_name.max' => 'Last name cannot exceed 50 characters',
            'last_name.regex' => 'Last name contains invalid characters',
            
            'avatar_url.url' => 'Please provide a valid URL for avatar',
            'avatar_url.max' => 'Avatar URL cannot exceed 500 characters',
            'avatar_url.active_url' => 'Avatar URL must be a working website',
            
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already registered by another user',
            'email.max' => 'Email address cannot exceed 255 characters',
            'email.regex' => 'Please provide a valid email address format',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->email ? strtolower(trim($this->email)) : null,
            'first_name' => $this->first_name ? trim($this->first_name) : null,
            'last_name' => $this->last_name ? trim($this->last_name) : null,
        ]);
    }
}