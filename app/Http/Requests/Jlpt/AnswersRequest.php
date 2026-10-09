<?php

namespace App\Http\Requests\Jlpt;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Jawaban sesi tes JLPT: { "<id soal>": 1..4 }. Nilai di luar 1–4 ditolak (422);
 * id soal yang tidak dikenal dibuang diam-diam oleh JlptTestService::sanitize.
 */
class AnswersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['present', 'array', 'max:200'],
            'answers.*' => ['nullable', 'integer', 'between:1,4'],
        ];
    }
}
