<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!Hash::check($value, auth()->user()->password)) {
                        $fail('The current password is incorrect.');
                    }
                }
            ],
            'new_password' => [
                'required',
                'confirmed',
                'different:current_password',
                Rules\Password::defaults()
                    ->min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Current password is required',
            
            'new_password.required' => 'New password is required',
            'new_password.confirmed' => 'New passwords do not match',
            'new_password.different' => 'New password must be different from current password',
            'new_password.min' => 'New password must be at least 8 characters',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->current_password === $this->new_password) {
                $validator->errors()->add('new_password', 'New password must be different from current password.');
            }
        });
    }
}