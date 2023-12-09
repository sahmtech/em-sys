<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;

class EssentialsUserShift extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'essentials_shift_id');
    }
}
