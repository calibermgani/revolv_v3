<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class project extends Model
{
    use SoftDeletes;

    protected $table ='projects';
    protected $fillable = ['project_id','project_name','added_by','status'];
}
