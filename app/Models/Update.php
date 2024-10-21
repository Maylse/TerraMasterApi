<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Update extends Model
{
    use HasFactory;

       // Add title to the fillable properties
       protected $fillable = [
        'title', // Allow mass assignment for title
        'content', // Assuming content is also a fillable property
        // Add any other fields that should be mass assignable
    ];
}
