<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class subproject extends Model
{
    use SoftDeletes;

    protected $table ='subprojects';
    protected $fillable = ['project_id','sub_project_id','sub_project_name','added_by','status'];

    public function clientName(){
        return $this->hasOne('App\Models\project','id','project_id');
    }

}
