<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name_id', 'name_en', 'description_id', 'description_en',
        'icon', 'condition_type', 'condition_value',
    ];

    public function name(): string
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_id;
    }
}
