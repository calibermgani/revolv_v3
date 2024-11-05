<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeLogin extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['in_time', 'out_time', 'user_id', 'duration', 'break_hrs','login_date','logout_date'];
}
