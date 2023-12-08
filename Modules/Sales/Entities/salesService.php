<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;

class salesService extends Model
{
   
    protected $guarded = ['id'];

    public function profession()
    {
     
        return $this->belongsTo(\Modules\Essentials\Entities\EssentialsProfession::class, 'profession_id');
    }
    public function specialization()
    {
     
        return $this->belongsTo(\Modules\Essentials\Entities\EssentialsSpecialization::class, 'specialization_id');
    }

    public function nationality()
    {
     
        return $this->belongsTo(\Modules\Essentials\Entities\EssentialsCountry::class, 'nationality_id');
    }
}
