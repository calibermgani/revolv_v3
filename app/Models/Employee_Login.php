<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Eloquent;

class Employee_Login extends Model

{
    protected $table = 'mybox_employee_login';



    public function Shift_type()
    {
        return $this->hasOne('App\Models\Shift_type', 'id','shift_type');
    }

    public function shift_name()
    {
        return $this->hasOne(Shift_type::class, 'id', 'shift_type');
    }

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }
}
