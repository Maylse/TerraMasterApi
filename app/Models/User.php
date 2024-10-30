<?php

namespace App\Models;

use Illuminate\Contracts\Auth\CanResetPassword; // Import the CanResetPassword interface
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait; // Import the trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Eloquent\Model;

class User extends Model implements CanResetPassword
{
    use HasFactory, Notifiable, HasApiTokens, CanResetPasswordTrait; // Add CanResetPasswordTrait

    protected $connection = 'mongodb'; // Specify MongoDB connection
    protected $collection = 'users'; // Specify MongoDB collection

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type', 
        'is_admin',// Add user_type to the fillable array
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Ensure passwords are hashed
    ];

    /**
     * Define a one-to-one relationship with LandExpert.
     */
    public function expert()
    {
        return $this->hasOne(LandExpert::class, 'user_id');
    }

    /**
     * Define a one-to-one relationship with Surveyor.
     */
    public function surveyor()
    {
        return $this->hasOne(Surveyor::class, 'user_id');
    }

    /**
     * Define a one-to-many relationship with ConsultationRequest.
     */
    public function consultationRequests()
    {
        return $this->hasMany(ConsultationRequest::class, 'finder_id');
    }
}
