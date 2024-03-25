<?php

namespace Modules\Sales\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesUnSupportedWorker extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function profession()
    {

        return $this->belongsTo(\Modules\Essentials\Entities\EssentialsProfession::class, 'profession_id');
    }
    public function nationality()
    {

        return $this->belongsTo(\Modules\Essentials\Entities\EssentialsCountry::class, 'nationality_id');
    }
    public function specialization()
    {

        return $this->belongsTo(\Modules\Essentials\Entities\EssentialsSpecialization::class, 'specialization_id');
    }
}
