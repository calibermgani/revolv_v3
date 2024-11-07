<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\MainMenuPermission;
use App\Models\Menu;
use App\Http\Helper\Admin\Helpers as Helpers;
use Illuminate\Support\Facades\DB;
use App\Models\SubMenuPermission;
use App\Models\SubMenu;
use App\Models\EmployeeLogin;
use Carbon\Carbon;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest')->except('logout');
    }
    public function login() {
        try {
            return view('Auth.login');
        } catch (Exception $e) {
            log::debug($e->getMessage());
        }
    }
    public function dashboard() {
        try {
          return view('Dashboard/dashboard');
        } catch (Exception $e) {
            log::debug($e->getMessage());
        }
    }
    public function storeInSession(Request $request) {
        try {
            /* user login Details in below */
            $value = $request->input('value');
            Session::put('loginDetails', $value);

            /* Menus session details in below */
            if (Session::get('loginDetails') &&  Session::get('loginDetails')['userInfo'] && Session::get('loginDetails')['userInfo']['user_id'] !=null) {
                $userId = Session::get('loginDetails')['userInfo']['user_id'];

                $main_menu = MainMenuPermission::select('parent_id')->where('user_id',$userId)->first();
            if (!empty($main_menu)) {
                $main_menu = explode(",", $main_menu->parent_id);
                $menus = Menu::whereIn('id', $main_menu)->orderBy('menu_order', 'asc')->get();
                Session::put('menusOrder', $menus->sortBy('menu_order'));
            } else {
                $main_menu = [];
                $menus = array();
                Session::put('menusOrder', $menus);
            }

                /* Submenus session details in below */
                    $SubmenuListByuser = SubMenuPermission::join('sub_menus', 'sub_menus.id', '=', 'sub_menu_permissions.sub_menu_id')
                        ->select('sub_menu_id', 'sub_menu_name', 'sub_menu_name_url', 'sub_menus.id as submenu_id', 'sub_menu_name_icon as sub_menu_name_icon','parent_id')
                        ->where('sub_menu_permissions.user_id', $userId)
                        ->whereIn('sub_menu_permissions.parent_id', $main_menu)
                        //->where('sub_menu_permissions.parent_id',1)
                        ->orderBy('sub_menu_order', 'ASC')->get();
                    Session::put('SubmenuListByuser', $SubmenuListByuser);
                    $yesterday = Carbon::yesterday();            
                    $today = Carbon::today();
                    $yesterDayStartDate = $yesterday->setTime(17, 0, 0)->toDateTimeString();
                    $yesterDayEndDate = $today->setTime(8, 0, 0)->toDateTimeString();
                    $Emp_Login = new EmployeeLogin;
                    // $is_existing_login = EmployeeLogin::where('user_id',$userId)
                    // ->whereBetween('created_at', [$yesterDayStartDate, $yesterDayEndDate])
                    // ->count();
                    // if($is_existing_login == 0){
                        $in_time = Carbon::now()->setTimezone('Asia/Kolkata')->format('H:i:s');
                        $Emp_Login->user_id =  $userId;
                        $Emp_Login->in_time = $in_time;
                        $Emp_Login->login_date = Carbon::today()->format('Y-m-d');     
                        $Emp_Login->save();                     
                    // }
            }
            
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            log::debug($e->getMessage());
        }
    }
    public function logout(Request $request) {
        try {
            if (Session::get('loginDetails') &&  Session::get('loginDetails')['userInfo'] && Session::get('loginDetails')['userInfo']['user_id'] !=null) {
                $userId = Session::get('loginDetails')['userInfo']['user_id'];
                $yesterday = Carbon::yesterday();            
                $today = Carbon::today();
                $yesterDayStartDate = $yesterday->setTime(17, 0, 0)->toDateTimeString();
                $yesterDayEndDate = $today->setTime(8, 0, 0)->toDateTimeString();
                // $is_existing_login = EmployeeLogin::where('user_id',$userId)->whereBetween('created_at', [$yesterDayStartDate, $yesterDayEndDate])->first();
                $is_existing_login = EmployeeLogin::where('user_id',$userId)->orderby('id','desc')->first();
                if(!empty($is_existing_login)) {
                    $out_time = Carbon::now()->setTimezone('Asia/Kolkata')->format('H:i:s');
                    $logout_date = Carbon::today()->format('Y-m-d');
                    $duration = (new Carbon($out_time))->diff(new Carbon($is_existing_login->in_time))->format('%H:%I:%S');
                    EmployeeLogin::where('id', $is_existing_login->id)
                    ->where('login_date', $logout_date)
                    ->update([
                    'out_time' => $out_time,
                    'duration'=>$duration,
                    'logout_date' =>$logout_date,
                    ]);
                }
           }
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/');
        } catch (Exception $e) {
            log::debug($e->getMessage());
        }
    }
}
