<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CallerChartsWorkLogs extends Model
{
    use SoftDeletes;
    protected $table ='caller_charts_work_logs';
    protected $fillable = [
        'emp_id', 'project_id', 'sub_project_id', 'record_id', 'start_time', 'end_time','work_time','record_status'
    ];
}
