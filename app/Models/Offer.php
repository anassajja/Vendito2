<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [ // these are the fields that can be mass assigned
        'title',
        'description',
        'price',
        'image',
        'category',
        'location',
        'condition',
        'delivery',
        'negotiable',
        'phone',
        'status',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
