<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Finder extends Model
{
    use HasFactory;
    protected $connection = 'mongodb' ;
    protected $collection = 'finders';
    protected $fillable = [
        'user_id', // Foreign key
        'name', // Include name in fillable fields
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
