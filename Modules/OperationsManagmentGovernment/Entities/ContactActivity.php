<?php

namespace Modules\OperationsManagmentGovernment\Entities;

use App\Company;
use App\Contact;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactActivity extends Model
{
    use HasFactory;

    public $table = 'contact_activity';

    protected $guarded = ['id'];
}
