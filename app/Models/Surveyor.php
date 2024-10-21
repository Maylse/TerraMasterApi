<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Surveyor extends Model
{
    use HasFactory;

    protected $table = 'surveyors'; // Specify the table name if it's not the plural of the model name

    protected $fillable = [
        'user_id',        // Foreign key to users table
        'certification_id',
        'license_number', // Unique ID for surveyor certification
        'pricing',  
              // Pricing for services
        // Add any other attributes specific to surveyors
    ];

    // Define the relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // You can also define other relationships, if necessary
}
