<?php

namespace Modules\CEOManagment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestsType extends Model
{


  protected $guarded = ['id'];
  public function tasks()
  {
    return $this->hasMany(Task::class, 'request_type_id');
  }
}
