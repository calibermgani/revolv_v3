<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AimsUser extends Model
{
    use SoftDeletes;

    protected $table = 'aims_users';

    protected $fillable = [
        'aims_user_id',
        'emp_id',
        'user_name',
        'status',
    ];
}
