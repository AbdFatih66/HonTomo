<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audio extends Model
{
    use HasFactory;

    protected $fillable = ['file_path', 'label', 'duration_ms', 'voice'];

    public function url(): string
    {
        return asset('storage/' . ltrim($this->file_path, '/'));
    }
}
