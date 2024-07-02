<?php

namespace Modules\CEOManagment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Essentials\Entities\EssentialsDepartment;

class TimeSheetWorkflow extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get the department that owns the workflow.
     */
    public function department()
    {
        return $this->belongsTo(EssentialsDepartment::class, 'department_id');
    }

    /**
     * Get the next department for the workflow.
     */
    public function nextDepartment()
    {
        return $this->belongsTo(EssentialsDepartment::class, 'next_department_id');
    }
}