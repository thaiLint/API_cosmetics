<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    use HasFactory;
     protected $fillable = ['user_id', 'skin_type', 'recommended_products'];

    protected $casts = [
        'recommended_products' => 'array',
    ];
}
