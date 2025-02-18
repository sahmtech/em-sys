<?php

namespace Modules\OperationsManagmentGovernment\Entities;

use App\Contact;
use App\User;
use Illuminate\Database\Eloquent\Model;


class Report extends Model
{
    protected $guarded = ['id'];
    public $table = 'operations_reports';

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function penalty()
    {
        return $this->hasOne(Penalty::class, 'report_id');
    }

    public function lostItems()
    {
        return $this->hasOne(LostItems::class, 'report_id');
    }

    public function subscriberStatus()
    {
        return $this->hasOne(SubscriberStatus::class, 'report_id');
    }

    public function photoConsents()
    {
        return $this->hasOne(PhotoConsents::class, 'report_id');
    }

    public function incident()
    {
        return $this->hasOne(Incident::class, 'report_id');
    }
}
