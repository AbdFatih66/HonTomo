<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->email)) {
            $this->merge(['email' => Str::lower(trim($this->email))]);
        }
    }

    public function rules(): array
    {
        // Format only — never "exists:users", that would reveal which emails are registered.
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
        ];
    }
}
