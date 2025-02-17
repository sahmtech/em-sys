<?php
namespace App;

use App\Contact;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Sales\Entities\SalesProject;

class ProjectDepartment extends Model
{
    use HasFactory;
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];
    protected $dates   = [
        'deleted_at',
    ];
    public function project()
    {
        return $this->belongsTo(SalesProject::class, 'sales_project_id');
    }
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

}
