<?php

namespace App\Http\Requests\Jlpt;

use Illuminate\Foundation\Http\FormRequest;

/** Mulai tes pada sebuah pack: mode ujian (strict) atau latihan (practice). */
class StartAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mode' => ['sometimes', 'string', 'in:strict,practice'],
        ];
    }
}
