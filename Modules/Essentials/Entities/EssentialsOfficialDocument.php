<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use App\User;
class EssentialsOfficialDocument extends Model
{
    protected $guarded = ['id'];
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
   
}