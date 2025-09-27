<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginUserRequest extends FormRequest
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
                'exists:users,email'
            ],
            'password' => [
                'required',
                'string',
                'min:6'
            ],
            'device_name' => [
                'nullable',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_\.]+$/'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.exists' => 'No account found with this email address',
            'email.max' => 'Email address cannot exceed 255 characters',
            
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 6 characters',
            
            'device_name.max' => 'Device name cannot exceed 100 characters',
            'device_name.regex' => 'Device name contains invalid characters',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
                $seconds = RateLimiter::availableIn($this->throttleKey());
                
                throw ValidationException::withMessages([
                    'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
                ]);
            }
        });
    }

    protected function throttleKey(): string
    {
        return strtolower($this->input('email')) . '|' . $this->ip();
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
            'device_name' => $this->device_name ? trim($this->device_name) : 'Unknown Device',
        ]);
    }

    private function isSuspiciousActivity(): bool
    {
        // add suspicious activity detection logic
        // like checking if too many failed attempts from this ip
        return false;
    }
}