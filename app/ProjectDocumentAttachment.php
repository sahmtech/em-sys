<?php
namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectDocumentAttachment extends Model
{
    use HasFactory;
    use HasFactory, SoftDeletes;

    // The table associated with the model.
    protected $table = 'project_document_attachments';

    // The attributes that are mass assignable.
    protected $fillable = [
        'project_document_id',
        'file_name',
        'file_path',
    ];

    // The attributes that should be hidden for arrays.
    protected $hidden = [
        // Add any fields that you don't want to expose (if needed).
    ];

    /**
     * Get the project document that owns the attachment.
     */
    public function projectDocument()
    {
        return $this->belongsTo(ProjectDocument::class);
    }

}
