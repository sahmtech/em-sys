<?php

namespace Modules\FollowUp\Entities;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowupDeliveryDocument extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'document_id', 'file_path', 'nots', 'title'];

    public function attachment()
    {
        return $this->belongsTo(FollowupDocument::class, 'document_id')->where('type', 'Attached');
    }
    public function document()
    {
        return $this->belongsTo(FollowupDocument::class, 'document_id')->where('type', 'Document');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
