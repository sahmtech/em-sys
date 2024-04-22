<?php

namespace Modules\CEOManagment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestsTypesField extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function requestsTypes()
    {
        return $this->belongsTo(RequestsType::class, 'request_type_id');
    }
}
