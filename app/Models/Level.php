<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name_id', 'name_en', 'description_id', 'description_en',
        'order', 'icon', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class)->orderBy('order');
    }

    public function learningPaths(): HasMany
    {
        return $this->hasMany(LearningPath::class)->orderBy('order');
    }

    public function name(): string
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_id;
    }
}
