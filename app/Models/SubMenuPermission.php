<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubMenuPermission extends Model
{
    use SoftDeletes;
    protected $table ='sub_menu_permissions';
    protected $fillable = ['user_id','parent_id','sub_menu_id','sub_menu_permission_given_by'];

    public function subMenuNames() {
        return $this->hasOne(Submenu::class, 'id', 'sub_menu_id');
    }
    public function userDetails() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
    public function mainMenuNames() {
        return $this->hasOne(Menu::class, 'id', 'parent_id');
    }
    public function mainMenuPermission() {
        return $this->hasOne(MainMenuPermission::class, 'user_id', 'user_id');
    }
}
