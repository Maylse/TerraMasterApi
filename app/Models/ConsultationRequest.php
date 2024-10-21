<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'finder_id',
        'expert_id', 
        'surveyor_id',
        'message',
        'status'
    ];

    public function finder()
    {
        return $this->belongsTo(User::class, 'finder_id');
    }

    public function expert()
    {
        return $this->belongsTo(User::class, 'expert_id');
    }

    public function surveyor()
    {
        return $this->belongsTo(User::class, 'surveyor_id');
    }
}
