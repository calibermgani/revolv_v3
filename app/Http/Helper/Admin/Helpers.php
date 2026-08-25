<?php

namespace App\Http\Helper\Admin;

use App\Http\Helper\Admin\EncryptIdAlgorithm as EncryptIdAlgorithm;
use Request;
use Auth;
use DateTime;
use DateInterval;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use phpDocumentor\Reflection\Location;
use Illuminate\Support\Facades\Schema;
use Response;
use Carbon\CarbonPeriod;
use App\Models\User;
use App\Models\SubMenuPermission;
use App\Models\MainMenuPermission;
use App\Models\SubMenu;
use App\Models\Menu;
use App\Models\project;
use App\Models\subproject;
use App\Models\formConfiguration;
use App\Models\QAStatus;
use App\Models\QASubStatus;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Client;
use App\Models\ARStatusCodes;
use App\Models\ARActionCodes;
use App\Models\qaClassCatScope;
use App\Models\ProjectReasonType;
use App\Models\ProjectReason;
use Illuminate\Support\Facades\Cache;
use App\Models\NonWorkableReason;
use Illuminate\Support\Str;
use App\Models\ARDenialCode;
use App\Models\ARSubStatusCode;
// use App\Models\question;
use App\Models\CallerChartsWorkLogs;
// use App\Models\Scenario;

class Helpers
{

	// Common Function for Encode and Decode ID
	public static function encodeAndDecodeID($id, $type = 'encode')
	{
		$encode_decode_alg = 'base64_alg';
		return EncryptIdAlgorithm::$encode_decode_alg($id, $type);
	}

	// Common Function for date Format
	public static function dateFormat($date, $format = '')
	{
		if ($format == '') {
			$format = 'd/m/Y';
		}
		return date($format, strtotime($date));
	}

	// Common Function for time format
	public static function timeFormat($time)
	{
		if (!empty($time) && $time != '') {
			return date('h:i A', strtotime($time));
		} else {
			return '';
		}
	}

	public static function getUserType()
	{
		$userType = [
			'' => '-- Select --',
			'Admin' => 'Admin',
			'Manager' => 'Manager',
			'TeamLead' => 'TeamLead',
			'Executive' => 'Executive',
		];
		return $userType;
	}

	public static function truncate($string, $length, $dots = "...")
	{
		return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
	}

	public static function getProjectScope()
	{
		$projectScope = [
			'' => '-- Select --',
			'FTE' => 'FTE',
			'Collections' => 'Collections',
		];
		asort($projectScope);
		return $projectScope;
	}

	public static function getGender()
	{
		$projectScope = [
			'' => '-- Select --',
			'Male' => 'Male',
			'Female' => 'Female',
			'Others' => 'Others',
		];
		asort($projectScope);
		return $projectScope;
	}

	public static function getLeaveStatus()
	{
		$leave_status = [
			"" => '--Select--',
			"Pending" => "Pending",
			"Approved" => "Approved",
			"Rejected" => "Rejected",
			"Withdraw" => "Leave Cancel",
		];
		asort($leave_status);
		return $leave_status;
	}

	public static function getInventoryTypes()
	{
		$inventory_type = [
			"" => '--Select--',
			"Asset Allocation" => "Asset Allocation",
			"Revoke" => "Revoke",
			"Report Lost" => "Report Lost",
			"Liquidation" => "Liquidation",
			"Warranty" => "Warranty",
			"Report Broken" => "Report Broken",
		];
		asort($inventory_type);
		return $inventory_type;
	}

	public static function getCandidateScope()
	{
		$candidate_scope = [
			"" => '--Select--',
			"Fresher" => "Fresher",
			"Experienced" => "Experienced",

		];
		asort($candidate_scope);
		return $candidate_scope;
	}
	public static function hireStatus()
	{
		$hire_status = [
			"" => '--Select--',
			"Selected" => "Selected",
			"Hold" => "Hold",
			"Rejected" => "Rejected",
			"Interview Scheduled" => "Interview Scheduled",
			"Interview Pending" => "Interview Pending"
		];
		asort($hire_status);
		return $hire_status;
	}

	public static function getGenderMRF()
	{
		$projectScope = [
			'' => '-- Select --',
			'Male' => 'Male',
			'Female' => 'Female',
			'Any' => 'Any',
		];
		asort($projectScope);
		return $projectScope;
	}

	public static function getTicketPriority()
	{
		$projectScope = [
			'' => '-- Select --',
			'1' => 'Highest',
			'2' => 'High',
			'3' => 'Low',
			'4' => 'Lowest',
		];
		asort($projectScope);
		return $projectScope;
	}

	public static function getTicketStatus()
	{
		$projectScope = [
			'' => '-- Select --',
			'1' => 'In Review',
			'2' => 'Done',
			'3' => 'Re Opened',
			'4' => 'Close',
			'5' => 'In Process',
			'6' => 'Passed',
			'7' => 'To Do',
		];

		asort($projectScope);
		return $projectScope;
	}
	public static function getTicketType()
	{
		$projectScope = [
			'' => '-- Select --',
			'1' => 'Improvement',
			'2' => 'Task',
			'3' => 'New Feature',
			'4' => 'Epic',
		];

		asort($projectScope);
		return $projectScope;
	}
	public static function getDepartment()
	{
		$projectScope = [
			'' => '-- Select --',
			'1' => 'HR',
			'2' => 'Finance',
			'3' => 'Compliance',
			'4' => 'Coding',
			'5' => 'IT/Development',
			'6' => 'Management',
			'7' => 'Management',
			'8' => 'Networking',
			'9' => 'Networking',
			'10' => 'Operations',
			'11' => 'Quality',
			'12' => 'Receipt Bank',
			'13' => 'Training'
		];

		asort($projectScope);
		return $projectScope;
	}

	public static function listTicketDepartment($department_id = '')
	{
		if ($department_id == 1) {
			$department = 'HR';
		} else if ($department_id == 2) {
			$department = 'Finance';
		} else if ($department_id == 3) {
			$department = 'Compliance';
		} else if ($department_id == 4) {
			$department = 'Coding';
		} else if ($department_id == 5) {
			$department = 'IT/Development';
		} else if ($department_id == 6) {
			$department = 'Management';
		} else if ($department_id == 7) {
			$department = 'Management';
		} else if ($department_id == 8) {
			$department = 'Networking';
		} else if ($department_id == 9) {
			$department = 'Networking';
		} else if ($department_id == 10) {
			$department = 'Operations';
		} else if ($department_id == 11) {
			$department = 'Quality';
		} else if ($department_id == 12) {
			$department = 'Receipt Bank';
		} else if ($department_id == 13) {
			$department = 'Training';
		} else
			$department = '-';
		return $department;
	}
	public static function listTicketStatus($status_id = '')
	{
		if ($status_id == 1) {
			$status_id = 'In Review';
		} else if ($status_id == 2) {
			$status_id = 'Done';
		} else if ($status_id == 3) {
			$status_id = 'Re Opened';
		} else if ($status_id == 4) {
			$status_id = 'Close';
		} else if ($status_id == 5) {
			$status_id = 'In Process';
		} else if ($status_id == 6) {
			$status_id = 'Passed';
		} else if ($status_id == 7) {
			$status_id = 'To Do';
		} else
			$status_id = '-';
		return $status_id;
	}
	public static function listTicketType($type_id)
	{
		if ($type_id == 1) {
			$type = 'Improvement';
		} else if ($type_id == 2) {
			$type = 'Task';
		} else if ($type_id == 3) {
			$type = 'New Feature';
		} else if ($type_id == 4) {
			$type = 'Epic';
		} else
			$type = '-';
		return @$type;
	}
	public static function listTicketPriority($priority_id)
	{
		if ($priority_id == 1) {
			$priority = 'Highest';
		} else if ($priority_id == 2) {
			$priority = 'High';
		} else if ($priority_id == 3) {
			$priority = 'Low';
		} else if ($priority_id == 4) {
			$priority = 'Lowest';
		} else
			$priority = '-';
		return @$priority;
	}
	public static function getPermission()
	{
		if (Session::get('loginDetails') &&  Session::get('loginDetails')['userInfo'] && Session::get('loginDetails')['userInfo']['user_id'] != null) {
			$main_menu = MainMenuPermission::select('parent_id')->where('user_id', Session::get('loginDetails')['userInfo']['user_id'])->first();
		}
		if (!empty($main_menu)) {
			$main_menu = explode(",", $main_menu->parent_id);
			$menus = Menu::whereIn('id', $main_menu)->orderBy('menu_order', 'asc')->get();
			return $menus->sortBy('menu_order');
		} else {
			$menus = array();
			return $menus;
		}
	}

	public static function getSubmenuListByuser($user_id, $parent_id)
	{

		DB::enableQueryLog();
		$sub_menu_list = SubMenuPermission::join('sub_menus', 'sub_menus.id', '=', 'sub_menu_permissions.sub_menu_id')
			->select('sub_menu_id', 'sub_menu_name', 'sub_menu_name_url', 'sub_menus.id as submenu_id', 'sub_menu_name_icon as sub_menu_name_icon')
			->where('sub_menu_permissions.user_id', $user_id)
			->where('sub_menu_permissions.parent_id', $parent_id)
			->orderBy('sub_menu_order', 'ASC')->get();

		return $sub_menu_list;
	}

	public static function getPermissionPage()
	{
		$permission_tables = [
			'' => '-- Select --',
			'users' => 'Users'
		];

		asort($permission_tables);
		return $permission_tables;
	}

	public static function getDocumentReason()
	{
		$documentReason = [
			'' => '-- Select --',
			'Submit' => 'Submitted',
			'Later' => 'Pending',
			'Not have' => 'Un Available',
		];
		asort($documentReason);
		return $documentReason;
	}


	public static function getsourceDetail()
	{
		$sourceDetails = [
			'' => '-- Select --',
			'Employee Referral' => 'Employee Referral',
			'Direct Walk-in' => 'Direct Walk-in',
			'Company Website' => 'Company Website',
			'Job Fair' => 'Job Fair',
			'Campus Interview' => 'Campus Interview',
			'Web Portal' => 'Job site/Social network',
			'Others' => 'Others',
		];
		asort($sourceDetails);
		return $sourceDetails;
	}

	public static function getSpecialtyInterview()
	{
		$speciality = [
			'' => '-- Select --',
			'Surgery - Facility' => 'Surgery - Facility',
			'E&M - OP' => 'E&M - OP',
			'Denial/Rejection' => 'Denial/Rejection',
			'Ancillary' => 'Ancillary',
			'ED Facility' => 'ED Facility',
			'Observation' => 'Observation',
			'IP - DRG' => 'IP - DRG',
			'ER Profee' => 'ER Profee',
			'ICD Coding' => 'ICD Coding',
			'E&M - IP/OP' => 'E&M - IP/OP',
			'Pathology' => 'Pathology',
			'Radiology' => 'Radiology',
			'HCC' => 'HCC',
			'Superbill' => 'Superbill',
			'Anesthesia' => 'Anesthesia',
			'IVR' => 'IVR',
			'Surgery - Provider' => 'Surgery - Provider',
		];
		asort($speciality);
		return $speciality;
	}

	public static function getBgvData()
	{
		$bgv_data = [
			'' => '-- Select --',
			'1' => 'Annexmed Team',
		];
		return $bgv_data;
	}

	public static function getWHM()
	{
		$getWhm = [
			'Elan Lakshmanan' => 'Elan Lakshmanan',
		];
		return $getWhm;
	}


	public static function getEmployeedetailsbyId($user_id)
	{

		$user_data = User::with(['user_personal'])->where('id', $user_id)->first();
		return $user_data;
	}

	public static function getTotalExp()
	{
		$total_exp = [
			'' => '-- Select --',
			'1' => '1 Year',
			'2' => '2 Year',
			'3' => '3 Year',
			'4' => '4 Year',
			'5' => '5 Year',
			'6' => '6 Year',
			'7' => '7 Year',
			'8' => '8 Year',
			'9' => '9 Year',
			'10' => '10 Year',
			'11' => '11 Year',
			'12' => '12 Year',
			'13' => '13 Year',
			'14' => '14 Year',
			'15' => '15 Year',
			'16' => '15+ Year',
		];

		return $total_exp;
	}
	public static function getTotalHealthExp()
	{
		$total_health_exp = [
			'' => '-- Select --',
			'1' => '1 Year',
			'2' => '2 Year',
			'3' => '3 Year',
			'4' => '4 Year',
			'5' => '5 Year',
			'6' => '6 Year',
			'7' => '7 Year',
			'8' => '8 Year',
			'9' => '9 Year',
			'10' => '10 Year',
			'11' => '11 Year',
			'12' => '12 Year',
			'13' => '13 Year',
			'14' => '14 Year',
			'15' => '15 Year',
		];

		return $total_health_exp;
	}

	public static function getCoreCompetency()
	{
		$coreCompetency = [
			'' => '-- Select --',
			'Physician' => 'Physician',
			'Hospital' => 'Hospital',
			'IV' => 'Auth',
			'Appeal' => 'Appeal',
			'Charges' => 'Charges',
			'Payments' => 'Payments',
			'Credit Balance' => 'Credit Balance',
		];
		asort($coreCompetency);
		return $coreCompetency;
	}

	public static function getSourceData()
	{
		$source_data = [
			'' => ' --Select-- ',
			'Source in Sales Tab' => 'Source in Sales Tab',
			'LinkedIn' => 'LinkedIn',
			'Social Media' => 'Social Media',
			'Client Reference' => 'Client Reference',
			'Consultants' => 'Consultants',
			'Direct' => 'Direct',
			'Others' => 'Others',
		];
		return $source_data;
	}
	public static function projectList()
	{
		// $data = project::where('status', 'Active')->pluck('project_name', 'id')->prepend(trans('Select Project'), '')->toArray();
		// $data = project::where('status', 'Active')->pluck('aims_project_name', 'project_id')->prepend(trans('Select Project'), '')->toArray();
		$data = Project::where('status', 'Active')
		->pluck('aims_project_name', 'project_id')
		->map(function ($name) {
			return ucwords(strtolower($name));
		})
		->prepend(trans('Select Project'), '')
		->toArray();

		return $data;
	}

	public static function subProjectList($project_id)
	{
		// $data = subproject::where('project_id', $project_id)->where('status', 'Active')->pluck('sub_project_name', 'id')->prepend(trans('Select Sub Project'), '')->toArray();
		$data = subproject::where('project_id', $project_id)->pluck('sub_project_name', 'sub_project_id')->prepend(trans('Select Sub Project'), '')->toArray();
		return $data;
	}
	public static function projectName($id)
	{
		// $data = project::where('status', 'Active')->where('id',$id)->first();
		$data = project::where('status', 'Active')->where('project_id', $id)->first();
		return $data;
	}
	public static function subProjectName($projectId, $subProjectId)
	{
		// $data = subproject::where('status', 'Active')->where('project_id',$projectId)->where('id',$subProjectId)->first();
		$data = subproject::where('project_id', $projectId)->where('sub_project_id', $subProjectId)->first();
		return $data;
	}
	public static function formConfig($projectId, $subProjectId)
	{
		$data = formConfiguration::where('status', 'Active')->where('project_id', $projectId)->where('id', $subProjectId)->first();
		return $data;
	}
	public static function getUserNameById1($id)
	{
		$payload = [
			'token' => '1a32e71a46317b9cc6feb7388238c95d',
			'user_id' => $id
		];
		$client = new Client();
		$response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_username_by_id', [
			'json' => $payload
		]);
		if ($response->getStatusCode() == 200) {
			$data = json_decode($response->getBody(), true);
		} else {
			return response()->json(['error' => 'API request failed'], $response->getStatusCode());
		}
		$userName = $data['user_name']['user_name'];
		return $userName;
	}

	public static function getUserEmpIdById($id)
	{
		$payload = [
			'token' => '1a32e71a46317b9cc6feb7388238c95d',
			'user_id' => $id
		];
		$client = new Client();
		$response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_user_emp_id_by_id', [
			'json' => $payload
		]);
		if ($response->getStatusCode() == 200) {
			$data = json_decode($response->getBody(), true);
		} else {
			return response()->json(['error' => 'API request failed'], $response->getStatusCode());
		}
		$userName = $data['user_list']['emp_id'];
		return $userName;
	}
	public static function qaStatusList()
	{
		$data = QAStatus::where('status', 'Active')->pluck('status_code', 'id')->prepend(trans('Select Status'), '')->toArray();
		return $data;
	}
	public static function qaSubStatusList()
	{
		$data = QASubStatus::where('status', 'Active')->pluck('sub_status_code', 'id')->prepend(trans('Select Sub Status'), '')->toArray();
		return $data;
	}
	public static function qaStatusById($id)
	{
		static $cache = [];
		$key = (string) $id;
		if (!array_key_exists($key, $cache)) {
			$cache[$key] = QAStatus::where('status', 'Active')->where('id', $id)->first('status_code');
		}
		return $cache[$key];
	}
	public static function qaSubStatusById($id)
	{
		static $cache = [];
		$key = (string) $id;
		if (!array_key_exists($key, $cache)) {
			$cache[$key] = QASubStatus::where('status', 'Active')->where('id', $id)->first('sub_status_code');
		}
		return $cache[$key];
	}

	public static function getUserNameByEmpId($id)
	{
		$payload = [
			'token' => '1a32e71a46317b9cc6feb7388238c95d',
			'user_emp_id' => $id
		];
		$client = new Client();
		$response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_username_by_empid', [
			'json' => $payload
		]);
		if ($response->getStatusCode() == 200) {
			$data = json_decode($response->getBody(), true);
		} else {
			return response()->json(['error' => 'API request failed'], $response->getStatusCode());
		}
		if(isset($data['user_name'])){
			$userName = $data['user_name']['user_name'];
			return $userName;
		} else {
			$userName = '--';
			return $userName;
		}
				
	}
	public static function getprojectResourceList($clientId)
	{
		//  $userId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
		// $payload = [
		// 	'token' => '1a32e71a46317b9cc6feb7388238c95d',
		// 	'client_id' => $clientId,
		// 	'user_id' => $userId
		// ];
		// $client = new Client();
		// $response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_resource_name_resolv', [
		// 	'json' => $payload
		// ]);
		// if ($response->getStatusCode() == 200) {
		// 	$data = json_decode($response->getBody(), true);
		// } else {
		// 	return response()->json(['error' => 'API request failed'], $response->getStatusCode());
		// }
		// $projectResource = array_filter($data['userDetail']);
		// return $projectResource;
		try {
			$userId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
			$payload = [
					'token' => '1a32e71a46317b9cc6feb7388238c95d',
					'client_id' => $clientId,
					'user_id' => $userId
				];
            $data = retry(3, function () use ($payload) {
                $client = new Client(['verify' => false]);
                $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_resource_name_resolv', [
                    'json' => $payload,
                ]);
                if ($response->getStatusCode() == 200) {
                    $responseData = json_decode($response->getBody(), true);
							return $responseData;
                } elseif ($response->getStatusCode() == 429) {
                    $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                    sleep($retryAfter);
                    throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                } else {
                    throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                }
            }, 4000);
			$projectResource = array_filter($data['userDetail']);
            return $projectResource;
        } catch (\Exception $e) {
            Log::error('Error in getPrjResourceList: ' . $e->getMessage());
            return null;
        }
	}
	public static function getMomAttendiesList()
	{
		$userId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
		$payload = [
			'token' => '1a32e71a46317b9cc6feb7388238c95d'
		];
		$client = new Client();
		$response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_mom_attendies_list', [
			'json' => $payload
		]);
		if ($response->getStatusCode() == 200) {
			$data = json_decode($response->getBody(), true);
		} else {
			return response()->json(['error' => 'API request failed'], $response->getStatusCode());
		}
		$getMomAttendiesList = $data['attendiesList'];
		return $getMomAttendiesList;
	}

	public static function getEmpListPermission()
	{
		$payload = [
			'token' => '1a32e71a46317b9cc6feb7388238c95d'
		];
		$client = new Client();
		$response = $client->request('POST',  config("constants.PRO_CODE_URL") . '/api/v1_users/get_ar_emp_list', [
			'json' => $payload
		]);
		if ($response->getStatusCode() == 200) {
			$data = json_decode($response->getBody(), true);
		} else {
			return response()->json(['error' => 'API request failed'], $response->getStatusCode());
		}
		$coderList = $data['coderList'];
		asort($coderList);
		return array('' => '-- Select --') + $coderList;
	}

	public static function arStatusList()
	{
		$data = ARStatusCodes::where('status', 'Active')->pluck('status_code', 'id')->prepend(trans('Select Status'), '')->toArray();
		return $data;
	}
	public static function arActionList()
	{
		$data = ARActionCodes::where('status', 'Active')->pluck('action_code', 'id')->prepend(trans('Select Action'), '')->toArray();
		return $data;
	}
	public static function arStatusById($id)
	{
		static $cache = [];
		$key = (string) $id;
		if (!array_key_exists($key, $cache)) {
			$cache[$key] = ARStatusCodes::where('status', 'Active')->where('id', $id)->first('status_code');
		}
		return $cache[$key];
	}
	public static function arActionById($id)
	{
		static $cache = [];
		$key = (string) $id;
		if (!array_key_exists($key, $cache)) {
			$cache[$key] = ARActionCodes::where('status', 'Active')->where('id', $id)->first('action_code');
		}
		return $cache[$key];
	}
	public static function qaClassificationById($id)
	{
		static $cache = [];
		$key = (string) $id;
		if (!array_key_exists($key, $cache)) {
			$cache[$key] = qaClassCatScope::where('status', 'Active')->where('id', $id)->first('qa_classification');
		}
		return $cache[$key];
	}
	public static function qaCategoryById($id)
	{
		static $cache = [];
		$key = (string) $id;
		if (!array_key_exists($key, $cache)) {
			$cache[$key] = qaClassCatScope::where('status', 'Active')->where('id', $id)->first('qa_category');
		}
		return $cache[$key];
	}
	public static function qaScopeById($id)
	{
		static $cache = [];
		$key = (string) $id;
		if (!array_key_exists($key, $cache)) {
			$cache[$key] = qaClassCatScope::where('status', 'Active')->where('id', $id)->first('qa_scope');
		}
		return $cache[$key];
	}
	public static function qaClassification()
	{
		$data = qaClassCatScope::where('status', 'Active')->pluck('qa_classification', 'id')->toArray();
		return $data;
	}
	public static function qaCategory()
	{
		$data = qaClassCatScope::where('status', 'Active')->pluck('qa_category', 'id')->toArray();
		return $data;
	}

	public static function qaScope()
	{
		$data = qaClassCatScope::where('status', 'Active')->pluck('qa_scope', 'id')->toArray();
		return $data;
	}
	public static function resolvProjectList()
	{
		$projectIds = formConfiguration::groupby('project_id')->pluck('project_id')->toArray();
		$data =  project::where('status', 'Active')->whereIn('project_id',$projectIds)->pluck('aims_project_name', 'project_id')->prepend(trans('Select Project'), '')->toArray();
		return $data;
	}

	public static function arProjectReasonTypeList()
	{
		$data = ProjectReasonType::whereIn('reason_access',[1,3])->pluck('reason_type', 'id')->prepend(trans('Select Project Reason'), '')->toArray();
		return $data;
	}
	
	public static function qaProjectReasonTypeList()
	{
		$data = ProjectReasonType::whereIn('reason_access',[2,3])->pluck('reason_type', 'id')->prepend(trans('Select Project Reason'), '')->toArray();
		return $data;
	}

	public static function arStatusListBySubPrjId($subProjectId)
	{
		$subProjectId = (int) $subProjectId; 
		$data = DB::table('a_r_status_codes')
		->where('status', 'Active')
		->whereRaw('FIND_IN_SET(?, sub_project_id)', $subProjectId)
		->whereNull('deleted_at')
		->pluck('status_code', 'id')->prepend(trans('Select Status'), '')->toArray();
		
		//$data = ARStatusCodes::where('status', 'Active')->whereJsonContains('sub_project_id', $subProjectId)->pluck('status_code', 'id')->prepend(trans('Select Status'), '')->toArray();
		return $data;
	}

	
	public static function getArResourceName($project_id)
	{
		try {
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $project_id,
            ];
            $data = retry(3, function () use ($payload) {
                $client = new Client(['verify' => false]);
                $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_ar_resource_name_resolv', [
                    'json' => $payload,
                ]);
                if ($response->getStatusCode() == 200) {
                    $responseData = json_decode($response->getBody(), true);

                    if (isset($responseData) && isset($responseData['arDetails'])) {
                        return $responseData['arDetails'];
                    } else {
                        throw new \Exception('arDetails not found in the API response');
                    }
                } elseif ($response->getStatusCode() == 429) {
                    $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                    sleep($retryAfter);
                    throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                } else {
                    throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                }
            }, 4000);
            return $data;
        } catch (\Exception $e) {
            Log::error('Error in getArDetails: ' . $e->getMessage());
            return null;
        }
	}

	public static function getQualityArEmpList()
	{
		try {
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d'
            ];
            $data = retry(3, function () use ($payload) {
                $client = new Client(['verify' => false]);
                $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_quality_ar_emp_list', [
                    'json' => $payload,
                ]);
                if ($response->getStatusCode() == 200) {
                    $responseData = json_decode($response->getBody(), true);

                    if (isset($responseData)) {
                        return $responseData;
                    } else {
                        throw new \Exception('ar&qa Details not found in the API response');
                    }
                } elseif ($response->getStatusCode() == 429) {
                    $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                    sleep($retryAfter);
                    throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                } else {
                    throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                }
            }, 4000);
            return $data;
        } catch (\Exception $e) {
            Log::error('Error in getQualityAr: ' . $e->getMessage());
            return null;
        }
	}
	public static function getemailsAboveTlLevel($project_id)
	{
		try {
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
				'client_id' => $project_id
            ];
            $data = retry(3, function () use ($payload) {
                $client = new Client(['verify' => false]);
                $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_emails_above_tl_level', [
                    'json' => $payload,
                ]);
                if ($response->getStatusCode() == 200) {
                    $responseData = json_decode($response->getBody(), true);

                    if (isset($responseData)) {
                        return $responseData;
                    } else {
                        throw new \Exception('emails above tl level not found in the API response');
                    }
                } elseif ($response->getStatusCode() == 429) {
                    $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                    sleep($retryAfter);
                    throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                } else {
                    throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                }
            }, 4000);
            return $data;
        } catch (\Exception $e) {
            Log::error('Error in getQualityAr: ' . $e->getMessage());
            return null;
        }
	}
	public function getProjects($userId)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                // $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                // $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'user_id' => $userId,
                ];
                $data = retry(3, function () use ($payload) {
                    $client = new Client(['verify' => false]);
                    $response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_clients_on_user', [
                        'json' => $payload,
                    ]);
                    if ($response->getStatusCode() == 200) {
                        // $data = json_decode($response->getBody(), true);
                        $responseData = json_decode($response->getBody(), true);
                        if (isset($responseData)) {
                            return $responseData['clientList'];
                        } else {
                            throw new \Exception('clientList not found in the API response');
                        }
                    } elseif ($response->getStatusCode() == 429) {
                        $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                        sleep($retryAfter);
                        throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                    } else {
                        throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                    }
                }, 4000);
                return $data;
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
	public function getSubProjects($project_id)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                 $payload = [
					'token' => '1a32e71a46317b9cc6feb7388238c95d',
					'client_id' => $project_id,
				];
                $data = retry(3, function () use ($payload) {
                    $client = new Client(['verify' => false]);
                    $response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_practice_on_client', [
                        'json' => $payload,
                    ]);
                    if ($response->getStatusCode() == 200) {
                        $responseData = json_decode($response->getBody(), true);
                        if (isset($responseData)) {
							 $responseData['practiceList'] = Helpers::filterPracticeList(
									$responseData['practiceList'] ?? [],
									$responseData['clientInfo']['id'] ?? null
								);//for filter manually deleted at projects in form_configuration table by tech
								if (empty($responseData['practiceList'])) {
									$responseData['practiceList'] = [];
								}
                            return $responseData;
                        } else {
                            throw new \Exception('practice on client not found in the API response');
                        }
                    } elseif ($response->getStatusCode() == 429) {
                        $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                        sleep($retryAfter);
                        throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                    } else {
                        throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                    }
                }, 4000);
                return $data;
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
	public static function arReasonByPrjandSubPrjId($ProjectId,$subProjectId)
	{
		$data = ProjectReason::where('project_id', $ProjectId)->where('sub_project_id', $subProjectId)->first();
		$ProjectReasonType =ProjectReasonType::where('id',$data->ar_reason)->first();
			$arReasonName = $ProjectReasonType->reason_type;
		// if($data->ar_reason == 8) {

		// }
	
		$arReasonData = ['ar_reason' => $data->ar_reason,'ar_reason_name'=>$arReasonName];
		return $arReasonData;
	}

	public static function qaReasonByPrjandSubPrjId($ProjectId,$subProjectId)
	{
		$data = ProjectReason::where('project_id', $ProjectId)->where('sub_project_id', $subProjectId)->first('qa_reason');
		$ProjectReasonType =ProjectReasonType::where('id',$data->qa_reason)->first();
		$qaReasonName = $ProjectReasonType->reason_type;
		$qaReasonData = ['qa_reason' => $data->qa_reason,'qa_reason_name'=>$qaReasonName];
		return $qaReasonData;
	}

	public static function getUserNameListById($id)
	{
		$payload = [
			'token' => '1a32e71a46317b9cc6feb7388238c95d',
			'user_id' => $id
		];
		$client = new Client();
		$response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_username_list_by_id', [
			'json' => $payload
		]);
		if ($response->getStatusCode() == 200) {
			$data = json_decode($response->getBody(), true);
		} else {
			return response()->json(['error' => 'API request failed'], $response->getStatusCode());
		}
		if(isset($data['user_name_list'])) {
			$userNameList = $data['user_name_list'];
			return $userNameList;
		} else {
			$userNameList = [];
			return $userNameList;
		}
				
	}
	public static function getUserNameListByEmpId($empId)
	{
		$payload = [
			'token' => '1a32e71a46317b9cc6feb7388238c95d',
			'user_emp_id' => $empId
		];
		$client = new Client();
		$response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_username_list_by_emp_id', [
			'json' => $payload
		]);
		if ($response->getStatusCode() == 200) {
			$data = json_decode($response->getBody(), true);
		} else {
			return response()->json(['error' => 'API request failed'], $response->getStatusCode());
		}
		if(isset($data['user_name'])) {
			$userNameList = $data['user_name'];
			return $userNameList;
		} else {
			$userNameList = [];
			return $userNameList;
		}
				
	}

	public static function getProjectSubPrjManagerList($clientIds,$subPrjIds)
    {
        try {
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $clientIds,
                 'sub_prj_ids'=>$subPrjIds   
            ];      // dd($payload);
            // Retry 3 times, with a 2-second delay between each attempt
            $data = retry(3, function () use ($payload) {
                $client = new Client(['verify' => false]);
                $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_resolv_project_manager_list', [
                    'json' => $payload,
                ]);
               // dd($response);
                if ($response->getStatusCode() == 200) {
			
                    $responseData = json_decode($response->getBody(), true);	
                    if (isset($responseData)) {
                        return $responseData['result'];
                    } else {
                        throw new \Exception('prjMgrNameList not found in the API response');
                    }
                } elseif ($response->getStatusCode() == 429) {
                    $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                    sleep($retryAfter);
                    throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                } else {
                    throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                }
            }, 4000);            
            return $data;
        } catch (\Exception $e) {
            Log::error('Error in getPrjMgrNameList: ' . $e->getMessage());
            return null;
        }
    }

	public static function getProjectSubPrjAboveTlLevel()
    {
        try {
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d'
            ];      
            // Retry 3 times, with a 2-second delay between each attempt
            $data = retry(3, function () use ($payload) {
                $client = new Client(['verify' => false]);
                $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_details_above_tl_level', [
                    'json' => $payload,
                ]);
               // dd($response);
                if ($response->getStatusCode() == 200) {
			
                    $responseData = json_decode($response->getBody(), true);	
                    if (isset($responseData)) {
                        return $responseData['people_details'];
                    } else {
                        throw new \Exception('prjAboveTl not found in the API response');
                    }
                } elseif ($response->getStatusCode() == 429) {
                    $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                    sleep($retryAfter);
                    throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                } else {
                    throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                }
            }, 4000);            
            return $data;
        } catch (\Exception $e) {
            Log::error('Error in getPrjMgrNameList: ' . $e->getMessage());
            return null;
        }
    }
	public static function getProjectInformationForHourlyWeb($prjArray,$subPrjArray)
    {
	
            try {
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'projectIds' => $prjArray,
					'subProjectIds' => $subPrjArray,
                    
                ];	  
				$data = retry(3, function () use ($payload) {
					$client = new Client(['verify' => false]);
					$response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_resolv_project_information', [
						'json' => $payload,
					]);
				   // dd($response);
					if ($response->getStatusCode() == 200) {
				
						$responseData = json_decode($response->getBody(), true);	
						if (isset($responseData)) {
							return $responseData['prjDetailsList'];
						} else {
							throw new \Exception('prjList not found in the API response');
						}
					} elseif ($response->getStatusCode() == 429) {
						$retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
						sleep($retryAfter);
						throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
					} else {
						throw new \Exception('API request failed with status: ' . $response->getStatusCode());
					}
				}, 4000);           
				return $data;
			} catch (\Exception $e) {
				Log::error('Error in prjDetailedList: ' . $e->getMessage());
				return null;
			}    
        
    }

	// public static function getProjectInformationForHourlyWeb($prjArray)
    // {
  
	// 	    $cacheKey ='project_' . implode('_', $prjArray) . '_detailed_info';
    //     return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($prjArray) {
    //         try {
    //             $payload = [
    //                 'token' => '1a32e71a46317b9cc6feb7388238c95d',
    //                 'prjArray' => $prjArray,
    //             ];
    
    //             return retry(3, function () use ($payload) {
    //                 $client = new Client(['verify' => false]);
    //                 $response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_resolv_project_information', [
    //                     'json' => $payload,
    //                 ]);
    
    //                 if ($response->getStatusCode() == 200) {
    //                     $responseData = json_decode($response->getBody(), true);
    //                     if (isset($responseData)) {
	// 						return $responseData['prjDetailsList'];
	// 					} else {
	// 						throw new \Exception('prjList not found in the API response');
	// 					}
    //                 } elseif ($response->getStatusCode() == 429) {
    //                     $retryAfter = $response->getHeader('Retry-After')[0] ?? 60;
    //                     sleep($retryAfter);
    //                     throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
    //                 } else {
    //                     throw new \Exception('API request failed with status: ' . $response->getStatusCode());
    //                 }
    //             }, 4000);
    //         } catch (\Exception $e) {
    //             Log::error('Error in getprjDetailedInfHoulryWeb: ' . $e->getMessage());
    //             return null;
    //         }
    //     });
    // }

	public static function getArNonWorkableReasonList()
	{
		$data = NonWorkableReason::where('status', 'Active')->pluck('reason_type', 'id')->toArray();
		return $data;
	}
	public static function nonWorkableReasonName($id)
	{
		// $data = project::where('status', 'Active')->where('id',$id)->first();
		$data = NonWorkableReason::where('status', 'Active')->where('id', $id)->first('reason_type');
		return $data;
	}
	public static function getAimsProductionEntryCount($prjArray,$subPrjArray,$workDate)
    {
	
            try {
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'projectIds' => $prjArray,
					'subProjectIds' => $subPrjArray,
					'workDate' => $workDate,
                    
                ];	  
				$data = retry(3, function () use ($payload) {
					$client = new Client(['verify' => false]);
					$response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_aims_production_entry_count', [
						'json' => $payload,
					]);
				   // dd($response);
					if ($response->getStatusCode() == 200) {
				
						$responseData = json_decode($response->getBody(), true);	
						if (isset($responseData)) {
							return $responseData['prjDetailsList'];
						} else {
							throw new \Exception('prjList not found in the API response');
						}
					} elseif ($response->getStatusCode() == 429) {
						$retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
						sleep($retryAfter);
						throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
					} else {
						throw new \Exception('API request failed with status: ' . $response->getStatusCode());
					}
				}, 4000);           
				return $data;
			} catch (\Exception $e) {
				Log::error('Error in prjDetailedList: ' . $e->getMessage());
				return null;
			}    
        
    }
	public static function getUserList()
	{
		$payload = [
			'token' => '1a32e71a46317b9cc6feb7388238c95d'
		];
		$client = new Client();
		$response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_resolv_user_list', [
			'json' => $payload
		]);
		if ($response->getStatusCode() == 200) {
			$data = json_decode($response->getBody(), true);
		} else {
			return response()->json(['error' => 'API request failed'], $response->getStatusCode());
		}
		if(isset($data['userList'])){
			$userList = $data['userList'];
			return $userList;
		} else {
			$userList = '--';
			return $userList;
		}
				
	}
	public static function getUserNameById($id){
		try {
			$payload = [
				'token' => '1a32e71a46317b9cc6feb7388238c95d',
				'user_id' => $id
			];

			// Retry 3 times, 2 seconds apart
			$data = retry(3, function () use ($payload) {
				$client = new Client(['verify' => false]);

				try {
					$response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_username_by_id', [
						'json' => $payload,
					]);

					if ($response->getStatusCode() === 200) {
						$responseData = json_decode($response->getBody(), true);

						if (isset($responseData) && isset($responseData['user_name']) &&isset($responseData['user_name']['user_name'])) {
							return $responseData['user_name']['user_name'];
						} else {
							throw new \Exception('Username not found in the API response');
						}
					}

				} catch (\GuzzleHttp\Exception\ClientException $e) {
					// Specifically catch 429 error
					if ($e->getResponse()->getStatusCode() == 429) {
						$retryAfter = $e->getResponse()->getHeader('Retry-After')[0] ?? 2;
						sleep($retryAfter); // Wait before retry
						throw new \Exception('Too many requests. Please try again later.');
					}

					throw $e; // Rethrow if not 429
				}
			}, 2000); // 2000ms = 2s delay between retries

			return $data;

		} catch (\Exception $e) {
			Log::error('Error in getUserNameById: ' . $e->getMessage());
			return 'Unable to fetch username right now. Please try again later.';
		}
	}

	Public static function getProjectColumns($projectId, $subProjectId)	{
		
		$excludeColumns = self::getPopupNonVisiblePatientColumns($projectId, $subProjectId);
		$columns = formConfiguration::where('project_id', $projectId)
			->where('sub_project_id', $subProjectId)
			->pluck('label_name', 'id')
			->filter(function ($label) {
				return !in_array($label, ['AR Notes', 'AR At', 'QA At']);
			})
			->map(function ($label) {
				return Str::lower(str_replace([' ', '/'], ['_', '_else_'], $label));
			})
			->filter(function ($column) use ($excludeColumns) {
				return !in_array($column, $excludeColumns, true);
			})
			->toArray();

		return $columns;
	}

	public static function labelNameToColumn($labelName)
	{
		return Str::lower(str_replace([' ', '/'], ['_', '_else_'], $labelName));
	}

	public static function getPopupNonVisiblePatientColumns($projectId = null, $subProjectId = null)
	{
		if (empty($projectId)) {
			return [];
		}

		return formConfiguration::where('project_id', $projectId)
			->where('sub_project_id', $subProjectId)
			->where('field_type_3', 'popup_non_visible')
			->whereNotNull('label_name')
			->where('label_name', '!=', '')
			->pluck('label_name')
			->map(function ($fieldName) {
				return self::labelNameToColumn($fieldName);
			})
			->unique()
			->values()
			->toArray();
	}

	public static function excludePopupNonVisiblePatientColumns($columns, $projectId, $subProjectId = null)
	{
		$excludeColumns = self::getPopupNonVisiblePatientColumns($projectId, $subProjectId);
		if (empty($excludeColumns) || empty($columns)) {
			return is_array($columns) ? array_values($columns) : $columns;
		}

		return array_values(array_diff($columns, $excludeColumns));
	}

	public static function excludePopupNonVisibleSearchFields($searchFields, $projectId, $subProjectId = null)
	{
		$excludeColumns = self::getPopupNonVisiblePatientColumns($projectId, $subProjectId);
		if (empty($excludeColumns) || empty($searchFields)) {
			return $searchFields;
		}

		return $searchFields->filter(function ($field) use ($excludeColumns) {
			$columnName = self::labelNameToColumn($field->column_name ?? '');
			return $columnName === '' || !in_array($columnName, $excludeColumns, true);
		})->values();
	}

	public static function hidePopupNonVisiblePatientFromRecords($records, array $excludeColumns)
	{
		if (empty($excludeColumns) || empty($records)) {
			return $records;
		}

		$transformer = function ($item) use ($excludeColumns) {
			foreach ($excludeColumns as $column) {
				if (is_array($item)) {
					unset($item[$column]);
				} elseif (is_object($item)) {
					unset($item->{$column});
				}
			}
			return $item;
		};

		if (is_object($records) && method_exists($records, 'getCollection')) {
			$records->getCollection()->transform($transformer);
			return $records;
		}

		if ($records instanceof \Illuminate\Support\Collection) {
			return $records->transform($transformer);
		}

		return $transformer($records);
	}

	public static function applyNumericRangeFilter($query, $key, $value, $secondQuery = null)
	{
		if (!is_string($value) || $value === '') {
			return false;
		}

		if (!preg_match('/^\s*(\d+(?:\.\d+)?)\s*(?:-|to)\s*(\d+(?:\.\d+)?)\s*$/i', $value, $rangeMatch)) {
			return false;
		}

		$fromValue = (float) $rangeMatch[1];
		$toValue = (float) $rangeMatch[2];
		if ($fromValue > $toValue) {
			$swapValue = $fromValue;
			$fromValue = $toValue;
			$toValue = $swapValue;
		}

		$query->whereBetween($key, [$fromValue, $toValue]);
		if ($secondQuery !== null) {
			$secondQuery->whereBetween($key, [$fromValue, $toValue]);
		}

		return true;
	}
	
	public static function arDenialList()
	{
		$data = ARDenialCode::select('id', DB::raw("concat(denial_code,' - ',code_description) as denialCode"))->where('status', 'Active')->pluck('denialCode', 'id')->prepend(trans('Select Denial'), '')->toArray();
		return $data;
	}
	public static function arDenialById($id)
	{
		static $cache = [];
		$key = (string) $id;
		if (!array_key_exists($key, $cache)) {
			$cache[$key] = ARDenialCode::select('id', DB::raw("concat(denial_code,' - ',code_description) as denialCode"))->where('status', 'Active')->where('id', $id)->first('denialCode');
		}
		return $cache[$key];
	}
	public static function arSubStatusList()
	{
		$data = ARSubStatusCode::select('id', DB::raw("concat(sub_status_code,' - ',sub_status_code_description) as substatusCode"))->where('status', 'Active')->pluck('substatusCode', 'id')->prepend(trans('Select substatus'), '')->toArray();
		return $data;
	}
	public static function arSubStatusById($id)
	{
		static $cache = [];
		$key = (string) $id;
		if (!array_key_exists($key, $cache)) {
			$cache[$key] = ARSubStatusCode::select('id', DB::raw("concat(sub_status_code,' - ',sub_status_code_description) as substatusCode"))->where('status', 'Active')->where('id', $id)->first('substatusCode');
		}
		return $cache[$key];
	}
	public static function getUserNameByAllEmpId($id)
	{
		$payload = [
			'token' => '1a32e71a46317b9cc6feb7388238c95d',
			'user_emp_id' => $id
		];
		$client = new Client();
		$response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_username_by_allempid', [
			'json' => $payload
		]);
		if ($response->getStatusCode() == 200) {
			$data = json_decode($response->getBody(), true);
		} else {
			return response()->json(['error' => 'API request failed'], $response->getStatusCode());
		}
		if(isset($data['user_name'])){
			$userName = $data['user_name'];
			return $userName;
		} else {
			$userName = '--';
			return $userName;
		}
				
	}
	// public static function getQuestion($statusId)	{
	// 	$data = question::where('status', 'Active')->where('ar_status_id', $statusId)->pluck('question_text', 'id')->toArray();
	// 	return $data;
	// }
	// public static function getQuestionById($id)	{
	// 	$data = question::where('status', 'Active')->where('id', $id)->first('question_text');
	// 	return $data;
	// }
	// public static function getQuestionWithSubQeustion($statusId)	{
	// 	// $data = question::where('status', 'Active')->where('ar_status_id', $statusId)->pluck('question_text', 'id')->toArray();
	// 	 $data = \DB::table('questions')
    //         ->leftJoin('sub_questions', function($join) use ($statusId) {
    //             $join->on('questions.id', '=', 'sub_questions.question_id')
    //                ->where('questions.ar_status_id', $statusId);
    //         })
    //         ->where('questions.ar_status_id', $statusId)
    //         ->select(
    //             'questions.id as question_id',
    //             'questions.question_text',
	// 			'sub_questions.question_type as sub_question_type'
    //         )
    //         ->get()->toArray();
	// 	return $data;
	// }
	public static function callLogRecordWorkTime($project_id, $sub_project_id, $record_id,$record_status)    {
        $data = CallerChartsWorkLogs::
        where('project_id', $project_id)->where('sub_project_id', $sub_project_id)->where('record_id', $record_id)->where('record_status', $record_status)
        ->orderBy('id', 'desc')
        ->first('work_time');
        return $data;
    }
	// public static function getQuestionWithquestionOptions($scenarioId)	{
	// 	// $data = question::where('status', 'Active')->where('ar_status_id', $statusId)->pluck('question_text', 'id')->toArray();
	// 	 $data = \DB::table('questions')
    //         ->leftJoin('question_options', function($join) use ($scenarioId) {
    //             $join->on('questions.id', '=', 'question_options.question_id')
    //                ->where('questions.ar_denial_id', $scenarioId);
    //         })
    //         ->where('questions.ar_denial_id', $scenarioId)
    //         ->select(
    //             'questions.id as question_id',
    //             'questions.question_text',
	// 			'questions.input_type',
	// 				'questions.parent_option_id',
	// 					'questions.validation',
	// 			'question_options.option_label as options'
    //         )
    //         ->get()->toArray();
	// 	return $data;
	// }
	// public static function scenarioList()
	// {
	// 	$data = Scenario::select('id','scenario_text')->where('status', 'Active')->pluck('scenario_text', 'id')->prepend(trans('Select Scenario'), '')->toArray();
	// 	return $data;
	// }
	public static function resolvSubProjectList($project_id) {
		$subProjectIds = formConfiguration::groupby('sub_project_id')->where('project_id', $project_id)->pluck('sub_project_id')->toArray();
		return subproject::where('project_id', $project_id)->whereIn('sub_project_id', $subProjectIds)->pluck('sub_project_name', 'sub_project_id')->prepend(trans('Select Sub Project'), '')->toArray();
	}
    public static function getConfigMap()   {
        return Cache::remember('form_config_map', now()->addHour(), function () {
            $map = [];

            formConfiguration::select('project_id', 'sub_project_id')
                ->whereNotNull('project_id')
                ->whereNotNull('sub_project_id')
                ->distinct()
                ->get()
                ->each(function ($item) use (&$map) {
                    $projectId = (string) $item->project_id;
                    $subProjectId = (string) $item->sub_project_id;

                    $map[$projectId][$subProjectId] = true;
                });

            return $map;
        });
    }

    public static function clearConfigMap()
    {
        Cache::forget('form_config_map');
    }

    public static function getFilteredClientProjects($clientList)
    {
        $configMap = self::getConfigMap();

        if (empty($clientList) || empty($configMap)) {
            return [];
        }

        foreach ($clientList as $pIndex => $project) {
            $projectId = isset($project['id']) ? (string) $project['id'] : null;
            $filtered = [];

            foreach ($project['subprject_name'] ?? [] as $subKey => $subName) {
                $subProjectId = (string) $subKey;

                if ($projectId && isset($configMap[$projectId][$subProjectId])) {
                    $filtered[$subKey] = $subName;
                }
            }

            $clientList[$pIndex]['subprject_name'] = $filtered;
        }

        return array_values(array_filter($clientList, function ($project) {
            return !empty($project['subprject_name']);
        }));
    }

    public static function filterPracticeList($practiceList, $projectId)
    {
        $configMap = self::getConfigMap();

        if (empty($practiceList) || empty($projectId) || empty($configMap)) {
            return [];
        }

        $projectId = (string) $projectId;

        return array_values(array_filter($practiceList, function ($practice) use ($configMap, $projectId) {
            $practiceId = isset($practice['id']) ? (string) $practice['id'] : null;

            return $practiceId && isset($configMap[$projectId][$practiceId]);
        }));
    }
	public static function getAimsSubProjectSpan($prjArray,$subPrjArray)
    {
	
            try {
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'projectIds' => $prjArray,
					'subProjectIds' => $subPrjArray
                    
                ];	  
				$data = retry(3, function () use ($payload) {
					$client = new Client(['verify' => false]);
					$response = $client->request('POST', 'https://aims.officeos.in/api/v1_users/get_aims_sub_project_span', [
						'json' => $payload,
					]);
					if ($response->getStatusCode() == 200) {
				
						$responseData = json_decode($response->getBody(), true);	
						if (isset($responseData)) {
							return $responseData['spanDetailsList'];
						} else {
							throw new \Exception('spanDetailsList not found in the API response');
						}
					} elseif ($response->getStatusCode() == 429) {
						$retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
						sleep($retryAfter);
						throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
					} else {
						throw new \Exception('API request failed with status: ' . $response->getStatusCode());
					}
				}, 4000);           
				return $data;
			} catch (\Exception $e) {
				Log::error('Error in prjDetailedList: ' . $e->getMessage());
				return null;
			}    
        
    }
}
