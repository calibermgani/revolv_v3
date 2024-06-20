<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;
use App\Models\QualitySampling;
use App\Models\QualitySamplingHistory;
use Illuminate\Support\Str;
use App\Http\Helper\Admin\Helpers as Helpers;
use Illuminate\Support\Facades\Log;
use App\Models\subproject;
use Illuminate\Support\Facades\Storage;
use App\Models\SopDoc;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class SettingController extends Controller
{
    public function qualitySampling(Request $request)
    {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d'
                ];
                $client = new Client();
                $response = $client->request('POST',  config("constants.PRO_CODE_URL") . '/api/v1_users/get_coder_emp_list', [
                    'json' => $payload
                ]);
                if ($response->getStatusCode() == 200) {
                    $data = json_decode($response->getBody(), true);
                } else {
                    return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                }
                $coderList = $data['coderList'];

                $qaResponse = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_qa_emp_list', [
                    'json' => $payload
                ]);
                if ($qaResponse->getStatusCode() == 200) {
                    $qaData = json_decode($qaResponse->getBody(), true);
                } else {
                    return response()->json(['error' => 'API request failed'], $qaResponse->getStatusCode());
                }

                $qaList = $qaData['qaList'];
                $qaSamplingList = QualitySampling::orderBy('id', 'desc')->get()->toArray();

                return view('settings/qualitySampling', compact('coderList', 'qaList', 'qaSamplingList'));
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function qualitySamplingStore(Request $request)
    {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $data =  $request->all();
                $data['added_by'] = Session::get('loginDetails')['userInfo']['user_id'];
                QualitySampling::create($data);
                return redirect('/sampling' . '?parent=' . request()->parent . '&child=' . request()->child);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function qualitySamplingUpdate(Request $request)
    {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $data =  $request->all();
                $data['added_by'] = Session::get('loginDetails')['userInfo']['user_id'];
                $existingRecord = QualitySampling::where('id', $data["record_id"])->first();
                if ($existingRecord) { //dd($data,$existingRecord);//need to maintain history
                    $historyRecord = $existingRecord->toArray();
                    $historyRecord['quality_sampling_id'] = $historyRecord['id'];
                    unset($historyRecord['id']);
                    QualitySamplingHistory::create($historyRecord);
                    $existingRecord->update($data);
                } else {
                    QualitySampling::create($data);
                }
                return redirect('/sampling' . '?parent=' . request()->parent . '&child=' . request()->child);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function sopImportData()
    {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                return view('settings/sop_create');
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function getSubProjectList(Request $request)
    {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $existingSubProject = [];
                // $existingSubProject = formConfiguration::where('project_id', $request->project_id)->groupBy(['project_id', 'sub_project_id'])
                // ->pluck('sub_project_id')->toArray();
                $data = subproject::where('project_id', $request->project_id)->pluck('sub_project_name', 'sub_project_id')->toArray();
                return response()->json(["subProject" => $data, "existingSubProject" => $existingSubProject]);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function sopDocStore(Request $request)
    {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                DB::beginTransaction();
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $data = $request->all();

                $attachment = $request->file('attachment');
                if ($attachment != '') {
                    $attachmentName = $attachment->getClientOriginalName();
                    $extension6 = $attachment->getClientOriginalExtension();
                    $sopDisplayName = pathinfo($attachmentName, PATHINFO_FILENAME);
                    $onlyFileName = str_replace(' ', '_', $sopDisplayName);
                    $fileNames = $onlyFileName . '_' . date('YmdHis') . '.' . $extension6;

                    if (!Storage::exists('public/pdf_folder/')) {
                        $storage_path = Storage::makeDirectory('/pdf_folder/', 0775, true);
                        $attachment->storeAs('public/pdf_folder/', $fileNames);
                    } else {
                        $attachment->storeAs('public/pdf_folder/', $fileNames); //dd($attachment,'el',$path,$fileNames);
                    }
                    $path = 'storage/pdf_folder/' . $fileNames;
                    $data['sop_doc'] = $sopDisplayName . '.' . $extension6;
                    $data['sop_path'] = $path;
                }
                $data['added_by'] = $userId;
                $existingRecord = SopDoc::where('project_id', $data['project_id'])->where('sub_project_id', $data['sub_project_id'])->first();
                $currentTime = Carbon::now()->format('Y-m-d H:i:s');
                if ($existingRecord) {
                    $existingRecord->deleted_at = $currentTime;
                    $existingRecord->save();
                } 
                    $save_flag = SopDoc::create($data);
                DB::commit();
                if ($save_flag) {
                    // return redirect()->back();
                    return redirect('sop/sop_list' . '?parent=' . request()->parent . '&child=' . request()->child);
                }
            } catch (\Exception $e) {
                DB::rollback();
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function sopList(Request $request)
    {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $sopList = SopDoc::get();
                return view('settings.sopList', compact('sopList'));
            } catch (\Exception $e) {
                DB::rollback();
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
}
