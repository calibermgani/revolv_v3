<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'emp_id', 'user_name', 'email', 'password', 'user_type', 'designation', 'status', 'reporting_mgr', 'contact_no', 'doj', 'dob', 'practice_client_list_mgr'
    ];
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_name.required'    => 'A title is required.',
            'email.required'    => 'The name field is required',
            // 'doj.required' =>'Date of Join is required',
            //  'contact_no.required' =>'Contact Number is required',
            'reporting_mgr.required' => 'Reporting Manager is required',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'min:6|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'min:6'
        ];
    }

    public function isAdmin()
    {
        // Check for an practice user type and user type column in your users table
        return ($this->user_type == 'Admin' && $this->practice_user_type == 'Manager') ? true : false;
    }

    public function manpowerrequest()
    {

        return $this->belongsTo('App\Models\ManPowerRequest', 'request_by');
    }

    public function children()
    {

        return $this->hasMany('App\Models\User', 'reporting_mgr')->with('children');
    }

    public function personal()
    {

        return $this->hasOne('App\Models\UserPersonalInformation', 'user_id', 'id');
    }

    // public function getDepartmentAttribute()
    // {
    //     return Session::get('department', null);
    // }

    // public function setDepartmentAttribute($value = null)
    // {
    //     Session::put('department', $value);
    // }

    // public function getLocationAttribute()
    // {
    //     return Session::get('location', null);
    // }

    // public function setLocationAttribute($value = null)
    // {
    //     Session::put('location', $value);
    // }

    // public function getShiftAttribute()
    // {
    //     return Session::get('shift', null);
    // }

    // public function setShiftAttribute($value = null)
    // {
    //     Session::put('shift', $value);
    // }

    // public function getCurrentDesignationAttribute()
    // {
    //     return Session::get('current_designation', null);
    // }

    // public function setCurrentDesignationAttribute($value = null)
    // {
    //     Session::put('current_designation', $value);
    // }


    public function designation()
    {
        return $this->hasOne('App\Models\Designation', 'id', 'designation');
    }

    public function user_personal()
    {
        return $this->hasOne('App\Models\UserPersonalInformation', 'user_id', 'id');
    }

    public function user_openingleave()
    {
        return $this->hasOne('App\Models\Leave\LeaveOpeningBalanceModel', 'emp_id', 'emp_id');
    }


    public function parent()
    {
        return $this->belongsTo(User::class, 'reporting_mgr');
    }

    public function user_hrdetails()
    {
        return $this->hasOne('App\Models\UserHrDetails', 'user_id', 'id');
    }

    public function user_professional()
    {
        return $this->hasOne('App\Models\UserProfessional', 'user_id', 'id');
    }

    /*Developer : Vijaya laxmi P*/
    public function designation_info()
    {
        return $this->hasOne('App\Models\Designation', 'id', 'designation');
    }

    /*Developer : Vijaya laxmi P*/
    public function new_joiner_attendance()
    {
        return $this->hasOne('App\Models\NewJoinersAttendanceDetail', 'user_id', 'id');
    }

    /*Developer : Vijaya laxmi P*/
    public function shift_assign()
    {
        return $this->hasOne('App\Models\Shift_assign', 'user_id', 'id');
    }

    /*Developer : Vijaya laxmi P*/
    public function leave_list()
    {
        return $this->hasMany('App\Models\Leave', 'user_id', 'id');
    }

    /*Developer : Vijaya laxmi P*/
    public function leave_permission()
    {
        return $this->hasMany('App\Models\LeavePermission', 'user_id', 'id');
    }

    public function userResignation()
    {
        return $this->hasMany('App\Models\UserResignationModel', 'user_id', 'id');
    }

    public function userResignationInfo()
    {
        return $this->hasOne('App\Models\UserResignationModel', 'user_id', 'id');
    }

     /*Developer : Vijaya laxmi P*/
     public function bio_metric_emp_id()
     {
         return $this->hasMany('App\Models\Mssql\Trans', 'EmpID', 'emp_id');
     }

     public function userBusinessInfo()
    {
        return $this->hasOne('App\Models\UserBusinessInformationModel', 'user_id', 'id');
    }
}
