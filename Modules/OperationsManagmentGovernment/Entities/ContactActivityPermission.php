<?php

namespace Modules\OperationsManagmentGovernment\Entities;

use App\Company;
use App\Contact;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactActivityPermission extends Model
{
    use HasFactory;

    public $table = 'contact_activity_permission';

    protected $guarded = ['id'];

    public function ContactActivity()
    {
        return $this->belongsTo(ContactActivity::class, 'activity_id');
    }
    public function Contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }
}
