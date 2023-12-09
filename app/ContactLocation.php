<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Sales\Entities\SalesProject;

class ContactLocation extends Model
{
    use HasFactory;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function project()
    {
        return $this->belongsTo(SalesProject::class, 'sales_project_id');
    }
   
}
