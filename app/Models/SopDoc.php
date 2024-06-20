<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class SopDoc extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['project_id','sub_project_id','sop_doc','sop_path','added_by'];
    public $timestamps = true;
}
