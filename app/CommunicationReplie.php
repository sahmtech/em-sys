<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationReplie extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function communicationMessage()
    {
        return $this->belongsTo(CommunicationMessage::class);
    }

    public function attachments()
    {
        return $this->hasMany(CommunicationAttachment::class, 'communication_reply_id');
    }
}
