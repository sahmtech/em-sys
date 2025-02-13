<?php

namespace Modules\OperationsManagmentGovernment\Entities;

use App\Company;
use App\Contact;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WaterWeight extends Model
{
    use HasFactory;

    public $table = 'water_weight';

    protected $guarded = ['id'];
    public function Company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    public function CreatedBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function UpdatedBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function DeletedBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function Contact()
    {
        return $this->hasOne(Contact::class, 'contact_id');
    }
}
