<?php

namespace Modules\OperationsManagmentGovernment\Entities;

use App\Company;
use App\Contact;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectZone extends Model
{
    use HasFactory;

    public $table = 'project_zone';

    protected $guarded = ['id'];


    public function Contact()
    {
        return $this->hasOne(Contact::class, 'contact_id');
    }
}
