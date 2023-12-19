<?php

namespace Modules\Essentials\Entities;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EssentialsResidencyHistory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

}
