<?php

namespace Modules\HelpDesk\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HdTicket extends Model
{

    use HasFactory;
    protected $guarded = ['id'];

    public function hdTicketStatus()
    {
        return $this->belongsTo(HdTicketStatus::class, 'status_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function replies()
    {
        return $this->hasMany(HdTicketReply::class, 'ticket_id');
    }
    public function attachments()
    {
        return $this->hasMany(HdAttachment::class, 'ticket_id');
    }
}
