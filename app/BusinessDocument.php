<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessDocument extends Model
{
    use HasFactory;
    protected $guarded = ['id' ];

    
    public function business()
    {
        return $this->belongTo(App\Business::class ,'business_id');
    }
}
