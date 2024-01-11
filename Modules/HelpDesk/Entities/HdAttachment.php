<?php

namespace Modules\HelpDesk\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HdAttachment extends Model
{

    use HasFactory;
    protected $guarded = ['id'];

    public function hdTicket()
    {
        return $this->belongsTo(HdTicket::class, 'ticket_id');
    }
    public function hdTicketReply()
    {
        return $this->belongsTo(HdTicketReply::class, 'reply_id');
    }
}
