<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults()
                    ->min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'first_name' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-zA-Z\s\-\.\']+$/'
            ],
            'last_name' => [
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
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already registered',
            'email.regex' => 'Please provide a valid email address format',
            'email.max' => 'Email address cannot exceed 255 characters',
            
            'password.required' => 'Password is required',
            'password.confirmed' => 'Passwords do not match',
            'password.min' => 'Password must be at least 8 characters',
            
            'first_name.required' => 'First name is required',
            'first_name.max' => 'First name cannot exceed 50 characters',
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, dots, and apostrophes',
            
            'last_name.required' => 'Last name is required',
            'last_name.max' => 'Last name cannot exceed 50 characters',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, dots, and apostrophes',
            
            'avatar_url.url' => 'Please provide a valid URL for avatar',
            'avatar_url.max' => 'Avatar URL cannot exceed 500 characters',
            'avatar_url.active_url' => 'Avatar URL must be a working website',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $disposableDomains = [
                'tempmail.com', 'guerrillamail.com', 'mailinator.com', 
                '10minutemail.com', 'throwawaymail.com'
            ];
            
            $emailDomain = substr(strrchr($this->email, "@"), 1);
            if (in_array($emailDomain, $disposableDomains)) {
                $validator->errors()->add('email', 'Disposable email addresses are not allowed.');
            }
        });
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
            'first_name' => trim($this->first_name),
            'last_name' => trim($this->last_name),
        ]);
    }
}