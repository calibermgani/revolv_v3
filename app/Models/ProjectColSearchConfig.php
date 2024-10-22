<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ProjectColSearchConfig extends Model
{
    use HasFactory,SoftDeletes;
   protected $fillable = ['project_id','sub_project_id','column_name','column_type','status','enabled_by'];
}
