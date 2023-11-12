<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EssentialsDepartment extends Model
{
    use HasFactory;


    protected $guarded = ['id'];
    protected static function newFactory()
    {
        return \Modules\Essentials\Database\factories\EssentialsDepartmentFactory::new();
    }
    public function childs() {

        return $this->hasMany('Modules\Essentials\Entities\EssentialsDepartment','parent_department_id','id') ;

    }
    public static function forDropdown()
    {
        $EssentialsDepartment = EssentialsDepartment::all()->pluck('name','id');
        
        return $EssentialsDepartment;
    }

}
