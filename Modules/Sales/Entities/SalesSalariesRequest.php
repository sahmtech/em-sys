<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\User;
use Modules\Essentials\Entities\EssentialsCountry;
use Modules\Essentials\Entities\EssentialsProfession;

class salesSalariesRequest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

   
    public function user()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }
}
