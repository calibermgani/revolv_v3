<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class MomChild extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = ['mom_id', 'topics', 'topic_description', 'action_item', 'responsible_party','topic_eta','added_by'];
}
