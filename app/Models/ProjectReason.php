<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectReason extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['project_id','sub_project_id','manager_id','ar_reason','ar_others_comments','qa_reason','qa_others_comments'];
    public function project_ar_reason_type()
    {
        return $this->hasOne('App\Models\ProjectReasonType', 'id', 'ar_reason');
    }
    public function project_qa_reason_type()
    {
        return $this->hasOne('App\Models\ProjectReasonType', 'id', 'qa_reason');
    }

}
