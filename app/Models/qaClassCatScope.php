<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class qaClassCatScope extends Model
{
    use SoftDeletes,HasFactory;
    protected $fillable = ['project_id','sub_project_id','status_code_id','sub_status_code_id','qa_classification','qa_category','qa_scope','status','added_by'];
}
