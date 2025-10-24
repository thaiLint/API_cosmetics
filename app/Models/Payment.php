<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
      protected $fillable = [
        'order_id',
        'user_id',
        'shop_account',
        'amount',
        'currency',
        'status',
        'transaction_id',
    ];
      public function user()
    {
        return $this->belongsTo(User::class);
    }
}
