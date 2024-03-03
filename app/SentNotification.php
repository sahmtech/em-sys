<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SentNotification extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function sentNotificationsUser()
    {
        return $this->hasMany(SentNotificationsUser::class, 'sent_notifications_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
