<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportTracking extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['project_id','sub_project_id','fetch_status','request_data','request_date'];
}
