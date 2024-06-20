<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class formConfiguration extends Model
{
    use SoftDeletes;
    protected $table ='form_configurations';
   protected $fillable = ['project_id','sub_project_id','label_name','input_type','options_name','field_type','field_type_1','field_type_2','field_type_3','added_by','user_type'];
//    protected $casts = [
//     'label_name' => 'array',
//     'input_type' => 'array',
//     'options_name' => 'array',
//     'field_type' => 'array',
//     'field_type_1' => 'array',
//     'field_type_2' => 'array'
//     ];
}
