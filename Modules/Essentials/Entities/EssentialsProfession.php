<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class EssentialsProfession extends Model
{
    protected $guarded = ['id'];
    public function specializations(): HasMany
    {
        return $this->hasMany(EssentialsSpecialization::class, 'profession_id');
    }
}