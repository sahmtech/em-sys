<?php

namespace Modules\Sales\Entities;

use Modules\Sales\Entities\salesContract;
use App\Contact;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Essentials\Entities\Shift;
use Modules\Essentials\Entities\EssentialsCity;

class SalesProject extends Model
{
    protected $guarded = ['id'];

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function project_city()
    {
        return $this->belongsTo(EssentialsCity::class, 'city');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'assigned_to');
    }

    public function Shifts()
    {
        return $this->hasMany(Shift::class, 'project_id');
    }
    public function salesContract()
    {
        return $this->hasOne(salesContract::class, 'sales_project_id');
    }


}