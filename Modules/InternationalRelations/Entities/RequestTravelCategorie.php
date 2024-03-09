<?php

namespace Modules\InternationalRelations\Entities;

use App\Contact;
use App\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestTravelCategorie extends Model
{
    use HasFactory;

    protected $guarded = ['id'];


    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }
    public function company()
    {
        return $this->belongsTo(Contact::class, 'travel_agency_id');
    }
}
