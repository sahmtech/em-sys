<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentNotificationsUser extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function sentNotification()
    {
        return $this->belongsTo(SentNotification::class, 'sent_notifications_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_id');
    }
}
