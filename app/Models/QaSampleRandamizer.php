<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class QaSampleRandamizer extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['project_id','sub_project_id','sampling_column_name','created_by','updated_by'];
}
