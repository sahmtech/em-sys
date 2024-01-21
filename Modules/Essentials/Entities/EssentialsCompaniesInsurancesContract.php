<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Company;
use App\Contact;
class EssentialsCompaniesInsurancesContract extends Model
{
   
    use HasFactory;
    protected $fillable = ['insur_id','company_id','insurance_start_date','insurance_end_date'];
    

    protected $table = 'essentials_companies_insurances_contracts';

    public function company()
{
    return $this->belongsTo(Company::class, 'company_id');
}

public function insurance()
{
    return $this->belongsTo(Contact::class, 'insur_id');
}



}
