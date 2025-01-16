<?php

namespace App;

namespace App\Models;

class LoginRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'ip_address', 'device', 'location', 'browser', 'os', 'is_successful', 'session_id', 'additional_data',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
