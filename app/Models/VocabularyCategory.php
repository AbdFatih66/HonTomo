<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VocabularyCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name_id', 'name_en', 'slug'];

    public function vocabularies(): HasMany
    {
        return $this->hasMany(Vocabulary::class, 'category_id');
    }
}
