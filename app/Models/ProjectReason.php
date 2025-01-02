<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectReason extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['project_id','sub_project_id','manager_id','project_reason','others_comments'];
    public function project_reason_type()
    {
        return $this->hasOne('App\Models\ProjectReasonType', 'id', 'project_reason');
    }

}
