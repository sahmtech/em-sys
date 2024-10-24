<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestAttachment extends Model
{
    use HasFactory;
    protected $table = 'request_attachments';
    protected $guarded = ['id'];

    public function Request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

}
