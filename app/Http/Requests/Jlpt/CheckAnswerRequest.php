<?php

namespace App\Http\Requests\Jlpt;

use Illuminate\Foundation\Http\FormRequest;

/** Umpan balik satu soal (hanya mode latihan): id soal + pilihan 1..4. */
class CheckAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // id soal bisa angka (1), atau teks ("2-4", "v1-1")
            'question' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9\-]+$/'],
            'choice' => ['required', 'integer', 'between:1,4'],
        ];
    }
}
