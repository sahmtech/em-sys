<?php

namespace Modules\OperationsManagmentGovernment\Entities;

use App\Company;
use App\Contact;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetAssessment extends Model
{
    use HasFactory;

    public $table = 'asset_assessment';

    protected $guarded = ['id'];

    public function zone()
    {
        return $this->belongsTo(ProjectZone::class, 'zone_id');
    }

    public function project()
    {
        return $this->zone?->project();
    }
}
