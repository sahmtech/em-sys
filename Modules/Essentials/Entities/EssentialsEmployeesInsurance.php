<?php

namespace Modules\Essentials\Entities;

use Modules\Essentials\Entities\EssentialsEmployeesFamily;
use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Contact;

use Modules\Essentials\Entities\EssentialsInsuranceClass;
use Modules\Essentials\Entities\EssentialsInsuranceCompany;

class EssentialsEmployeesInsurance extends Model
{

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function essentialsEmployeesFamily()
    {
        return $this->belongsTo(EssentialsEmployeesFamily::class, 'family_id');
    }
    public function insuranceCompany()
    {
        return $this->belongsTo(Contact::class, 'insurance_company_id');
    }
    public function insuranceClass()
    {
        return $this->belongsTo(EssentialsInsuranceClass::class, 'insurance_classes_id');
    }
}
