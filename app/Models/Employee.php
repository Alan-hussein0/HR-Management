<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'manager_id',
        'salary',
        'hired_at',
        'job_title',
    ];

    protected $casts = [
        'hired_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    
    // public function logs()
    // {
    //     return $this->hasMany(Log::class);
    // }
}