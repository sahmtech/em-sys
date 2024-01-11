<?php

namespace Modules\HelpDesk\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HdTicketReply extends Model
{

    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'hd_ticket_replies';

    public function hdTicket()
    {
        return $this->belongsTo(HdTicket::class, 'ticket_id');
    }
    public function user()
    {
        return  $this->belongsTo(User::class, 'user_id');
    }
    public function attachments()
    {
        return $this->hasMany(HdAttachment::class, 'reply_id');
    }
}
