<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class LandExpert extends Model
{
    use HasFactory;
    protected $connection = 'mongodb' ;
    protected $collection = 'land_experts';
     // Specify the attributes that are mass assignable
     protected $fillable = [
        'user_id',  
        'license_number',
        'certification_id',
        'pricing',
    ];

     // Define the relationship to the User model
     public function user()
     {
         return $this->belongsTo(User::class);
     }
}
