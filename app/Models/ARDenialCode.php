<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ARDenialCode extends Model
{
    use HasFactory,SoftDeletes;
     protected $fillable = ['project_id','sub_project_id','denial_code','code_description','status','added_by'];
}
