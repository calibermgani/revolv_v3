<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MomParent extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['meeting_title', 'meeting_attendies', 'time_zone', 'start_time', 'end_time','meeting_date','start','end','eta','req_description','added_by'];
}
