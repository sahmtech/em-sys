<?php

namespace Modules\Accounting\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostCenter extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected function getTransNameAttribute(){
        $name = app()->getLocale().'_name';
        return $this->$name;
    }
}
