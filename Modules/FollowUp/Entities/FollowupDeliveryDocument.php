<?php

namespace Modules\FollowUp\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class followupDeliveryDocument extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'document_id', 'file_path', 'nots'];


    public function document()
    {
        return $this->belongsTo(FollowupDocument::class, 'document_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}