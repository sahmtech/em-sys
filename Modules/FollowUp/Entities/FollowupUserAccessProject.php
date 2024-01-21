<?php

namespace Modules\FollowUp\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Sales\Entities\SalesProject;

class FollowupUserAccessProject extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function salesProject()
    {
        return $this->belongsTo(SalesProject::class, 'sales_project_id');
    }
}
