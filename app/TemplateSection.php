<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateSection extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
