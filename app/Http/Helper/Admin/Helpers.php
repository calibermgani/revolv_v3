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
		$data = project::where('status', 'Active')->pluck('aims_project_name', 'project_id')->prepend(trans('Select Project'), '')->toArray();
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
	public static function getUserNameById($id)
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
		]); //http://dev.aims.officeos.in/api/v1_users/cache_get_username_by_id(once integrated cache shall we use this url)
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
		$data = QAStatus::where('status', 'Active')->where('id', $id)->first('status_code');
		return $data;
	}
	public static function qaSubStatusById($id)
	{
		$data = QASubStatus::where('status', 'Active')->where('id', $id)->first('sub_status_code');
		return $data;
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

		$userName = $data['user_name']['user_name'];
		return $userName;
	}
	public static function getprojectResourceList($clientId)
	{
		$userId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
		$payload = [
			'token' => '1a32e71a46317b9cc6feb7388238c95d',
			'client_id' => $clientId,
			'user_id' => $userId
		];
		$client = new Client();
		$response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_resource_name', [
			'json' => $payload
		]);
		if ($response->getStatusCode() == 200) {
			$data = json_decode($response->getBody(), true);
		} else {
			return response()->json(['error' => 'API request failed'], $response->getStatusCode());
		}
		$projectResource = array_filter($data['userDetail']);
		return $projectResource;
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
		$response = $client->request('POST',  config("constants.PRO_CODE_URL") . '/api/v1_users/get_emp_list', [
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
}
