<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectReasonType extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['reason_type','reason_access','added_by'];
}
