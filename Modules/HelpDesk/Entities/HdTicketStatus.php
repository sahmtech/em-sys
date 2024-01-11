<?php

namespace Modules\HelpDesk\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HdTicketStatus extends Model
{

    use HasFactory;
    protected $guarded = ['id'];

    public static function forDropdown()
    {
        return  HdTicketStatus::pluck('title', 'id')->orderBy('sortorder', 'asc');
    }

    public function hdTickets()
    {
        return $this->hasMany(HdTicket::class, 'status_id');
    }
}
