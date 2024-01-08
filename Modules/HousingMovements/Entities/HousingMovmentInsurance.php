<?php

namespace Modules\HousingMovements\Entities;

use App\Contact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HousingMovmentInsurance extends Model
{
    use HasFactory;

    protected $fillable = ['car_id', 'insurance_company_id', 'insurance_start_Date', 'insurance_end_date'];

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'insurance_company_id');
    }
}