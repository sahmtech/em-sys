<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationAttachment extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function communicationMessage()
    {
        return $this->belongsTo(CommunicationMessage::class);
    }

    public function communicationReply()
    {
        return $this->belongsTo(CommunicationReplie::class);
    }
}
