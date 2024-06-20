<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualitySampling extends Model
{
    use SoftDeletes;
    protected $fillable = ['project_id','sub_project_id','coder_emp_id','qa_emp_id','qa_percentage','claim_priority','added_by'];
}
