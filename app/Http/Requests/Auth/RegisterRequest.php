<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Turnstile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim(strip_tags($this->name)) : $this->name,
            'email' => is_string($this->email) ? Str::lower(trim($this->email)) : $this->email,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()->uncompromised()],
            // Honeypot: real users never see or fill this field.
            'website' => ['prohibited'],
            // Only enforced when TURNSTILE_SECRET_KEY is configured (see App\Rules\Turnstile).
            'cf_turnstile_response' => [new Turnstile($this->ip())],
        ];
    }

    /** Only these fields ever reach the model (role etc. can't be injected). */
    public function accountData(): array
    {
        return $this->safe()->only(['name', 'email', 'password']);
    }
}
