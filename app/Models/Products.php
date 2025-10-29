<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'price',
        'qty',
        'category',
        'image',
        'images',
        'ingredients',
        'size',
        
    ];
     protected $casts = [
        'images' => 'array', // automatically cast JSON to array
    ];
    public function category()
{
    return $this->belongsTo(Category::class, 'category_id');
}
public function reviews()
{
    return $this->hasMany(Reviwes::class);
}

// Optional: average rating
public function averageRating()
{
    return $this->reviews()->avg('rating');
}
public function favoritedBy()
{
    return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
}


}
