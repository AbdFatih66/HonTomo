<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grammar extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_id', 'title_en', 'pattern', 'explanation_id', 'explanation_en',
        'example_japanese', 'example_reading', 'example_translation_id',
        'example_translation_en', 'jlpt_level', 'order',
    ];

    public function title(): string
    {
        return app()->getLocale() === 'en' ? $this->title_en : $this->title_id;
    }
}
