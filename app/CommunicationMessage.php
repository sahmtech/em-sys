<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationMessage extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function replies()
    {
        return $this->hasMany(CommunicationReplie::class);
    }
    public function attachments()
    {
        return $this->hasMany(CommunicationAttachment::class, 'communication_message_id');
    }
}
