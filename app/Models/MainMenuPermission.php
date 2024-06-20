<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MainMenuPermission extends Model
{
    use SoftDeletes;
    protected $table ='main_menu_permission';
    protected $fillable = ['user_id','parent_id','menu_permission_given_by'];
}
