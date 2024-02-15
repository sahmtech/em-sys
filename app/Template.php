<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function sections()
    {
        return $this->hasMany(TemplateSection::class)->orderBy('order');
    }
}
