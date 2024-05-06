<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Essentials\Entities\EssentialsBankAccounts;

class BankAccount extends Model
{
    protected $guarded = ['id'];


    public static function forDropdown()
    {
        $bankAccounts = BankAccount::all();
        
        
        $bankAccount_ = [];
       
        foreach ($bankAccounts as $bankAccount) {
            $bankAccount_[$bankAccount->id]=$bankAccount->account_number . ' - ' . $bankAccount->bank->name;    
        }


        return $bankAccount_ ;
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }


    public function bank()
    {
        return $this->belongsTo(EssentialsBankAccounts::class, 'bank_id');
    }
}