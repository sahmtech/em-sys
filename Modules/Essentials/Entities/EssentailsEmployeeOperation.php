<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\User;
class EssentailsEmployeeOperation extends Model
{
    use HasFactory;
      
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    protected $fillable = [];
    
   
}