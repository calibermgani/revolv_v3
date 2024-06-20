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
            }
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            log::debug($e->getMessage());
        }
    }
    public function logout(Request $request) {
        try {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/');
        } catch (Exception $e) {
            log::debug($e->getMessage());
        }
    }
}
