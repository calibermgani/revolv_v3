<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\MainMenuPermission;
use App\Models\SubMenuPermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class MenuPermissionController extends Controller
{
    public function index()
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {

            return view('MenuPermission/add');
        } else {
            return redirect('/');
        }
    }

    public function store(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {

            try {
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                if (isset($request->mList)) {
                    /* Sub Menu permission Start */
                    $main_menu = '';
                    $sub_menu = '';
                    $side_menu = '';
                    $value = explode(",", $request->mList);
                    SubMenuPermission::where('user_id', $request->user_id)->forceDelete();
                    // SideSubMenuPermission::where('user_id', $request->user_id)->forceDelete();
                    foreach ($value as $element) {
                        if (substr_count($element, '_') == "0") {
                            $main_menu .= $element . ",";
                            MainMenuPermission::updateOrCreate([
                                'user_id' => $request->user_id,
                            ], [
                                'user_id' => $request->user_id,
                                'parent_id' => trim($main_menu, ","),
                                'menu_permission_given_by' => $userId,
                            ]);
                        } elseif (substr_count($element, '_') == "2") {
                            $sub_menu .= $element . ",";
                            $explode_data = explode("_", $element);
                            SubMenuPermission::updateOrCreate([
                                'user_id' => $request->user_id,
                                'parent_id' => $explode_data[1],
                                'sub_menu_id' => $explode_data[2]
                            ], [
                                'user_id' => $request->user_id,
                                'parent_id' => $explode_data[1],
                                'sub_menu_id' => $explode_data[2],
                                'sub_menu_permission_given_by' => $userId,
                            ]);
                        }
                        // else {
                        //     $side_menu .= $element . ",";
                        //     $explode_data = explode("_", $element);
                        //     $side_sub_menu_permission = SideSubMenuPermission::updateOrCreate([
                        //         'user_id' => $request->user_id,
                        //         'menu_id' => $explode_data[1],
                        //         'sub_menu_id' => $explode_data[2],
                        //         'side_sub_menu_id' => $explode_data[3]
                        //     ], [
                        //         'user_id' => $request->user_id,
                        //         'menu_id' => $explode_data[1],
                        //         'sub_menu_id' => $explode_data[2],
                        //         'side_sub_menu_id' => $explode_data[3],
                        //         'given_by_id' => Auth::user()->id,
                        //     ]);
                        // }
                    }
                    /* Sub Menu permission End*/

                    return 1;
                } else {
                    return 0;
                }
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
}
