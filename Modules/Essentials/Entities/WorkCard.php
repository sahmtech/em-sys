<?php

namespace Modules\Essentials\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkCard extends Model
{
    use HasFactory;

    protected $table = 'essentials_work_cards';

    protected $fillable = [
        'employee_id',
        'project',
        'workcard_duration',
        'Payment_number',
        'fees',
        'company_name',
        'fixnumber',
        'responsible_user_id' ,
        'border_end_date',
        'residency_end_date'
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id', 'id');
    }
}
