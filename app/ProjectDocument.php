<?php
namespace App;

use App\User;
use App\ProjectDepartment;
use Illuminate\Database\Eloquent\Model;
use Modules\Sales\Entities\SalesProject;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'projects_documents';

    protected $guarded = ['id'];

    protected $dates = [
        'deleted_at',
    ];

    // Define the relationship with the SalesProject model (if needed)
    public function salesProject()
    {
        return $this->belongsTo(SalesProject::class);
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments()
    {
        return $this->hasMany(ProjectDocumentAttachment::class);
    }
    public function projectDepartment()
    {
        return $this->belongsTo(ProjectDepartment::class, 'project_department_id');
    }

}
