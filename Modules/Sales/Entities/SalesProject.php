<?php

namespace Modules\Sales\Entities;

use Modules\Sales\Entities\salesContract;
use App\Contact;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesProject extends Model
{
    protected $guarded = ['id'];
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
    public function users()
    {
        return $this->hasMany(User::class, 'assigned_to');
    }
}
