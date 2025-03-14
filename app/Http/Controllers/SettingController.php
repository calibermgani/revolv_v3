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
use App\Jobs\getQaArEmpList;
use Illuminate\Support\Facades\Cache;
use App\Models\QaSampleRandamizer;
use Illuminate\Support\Facades\Schema;
class SettingController extends Controller
{
    public function qualitySampling(Request $request)
    {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
             
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d'
                ];
                $client = new Client(['verify' => false]);
                $response = $client->request('POST',  config("constants.PRO_CODE_URL") . '/api/v1_users/get_quality_ar_emp_list', [
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
                
              $qaSamplingCoders = $qaSamplingQaEmpList = [];
                $qaSamplingList = QualitySampling::orderBy('id', 'desc')->get()->toArray();
                $qaSamplingCoders = QualitySampling::groupBy('coder_emp_id')->pluck('coder_emp_id')->toArray();
                $qaSamplingQaEmpList = QualitySampling::groupBy('qa_emp_id')->pluck('qa_emp_id','qa_emp_id')->toArray();
                return view('settings/qualitySampling', compact('coderList', 'qaList', 'qaSamplingList','qaSamplingCoders','qaSamplingQaEmpList'));
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
                if($request['_token'] != null) {
                    $filteredRequest = $request->except('_token', 'parent', 'child', 'page', 'project_id', 'sub_project_id', 'coder_emp_id', 'qa_emp_id', 'qa_percentage', 'claim_priority');
                    // $allNullValues = empty(array_filter($filteredRequest, function ($value) {
                    //     return $value !== null && $value !== '';
                    // }));
                
                    // $data['qa_sample_column_name'] = $allNullValues ? null : implode(',', array_keys($filteredRequest));
                    // $data['qa_sample_column_value'] = $allNullValues ? null : implode(',', array_values($filteredRequest));
                    $filteredRequest = array_filter($filteredRequest, function ($value) {
                        return $value !== null && $value !== '';
                    });
                    $data['qa_sample_column_name'] = empty($filteredRequest) ? null : implode(',', array_keys($filteredRequest));
                    $data['qa_sample_column_value'] = empty($filteredRequest) ? null : implode(',', array_values($filteredRequest));
                
                }
              //  dd($data);
              $samplingPercentage = QualitySampling::where('project_id',$data['project_id'])->where('sub_project_id',$data['sub_project_id'])->where('qa_emp_id',$data['qa_emp_id'])->sum('qa_percentage');
               $totalQAPercentage = $samplingPercentage + $data['qa_percentage'];
              if($totalQAPercentage <=  100) { 
                   QualitySampling::create($data);
              } else {
                $allowPercentage = 100 - $samplingPercentage;
                // return redirect('/sampling?parent=' . request('parent') . '&child=' . request('child'))
                // ->with('error', 'Allowed percentage is up to '.$allowPercentage.', but you entered '.$data['qa_percentage'].'.');
                //return back()->withErrors(['error' => 'Allowed percentage is up to ' . $allowPercentage . ', but you entered ' . $data['qa_percentage'] . '.']);
                     session()->flash('error', 'Allowed percentage is up to ' . $allowPercentage . ', but you entered ' . $data['qa_percentage'] . '.');
                return back();
            
              }
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
                if($request['_token'] != null) {
                    $filteredRequest = $request->except('_token', 'parent', 'child', 'page', 'project_id', 'sub_project_id', 'coder_emp_id', 'qa_emp_id', 'qa_percentage', 'claim_priority','record_id');
                    $filteredRequest = array_filter($filteredRequest, function ($value) {
                        return $value !== null && $value !== '';
                    });
                    $data['qa_sample_column_name'] = empty($filteredRequest) ? null : implode(',', array_keys($filteredRequest));
                    $data['qa_sample_column_value'] = empty($filteredRequest) ? null : implode(',', array_values($filteredRequest));
                
                }
                $existingRecord = QualitySampling::where('id', $data["record_id"])->first();
                $samplingPercentage = QualitySampling::where('project_id',$data['project_id'])->where('sub_project_id',$data['sub_project_id'])->where('qa_emp_id',$data['qa_emp_id'])->sum('qa_percentage');
                $recordPercentage = QualitySampling::where('project_id',$data['project_id'])->where('sub_project_id',$data['sub_project_id'])->where('qa_emp_id',$data['qa_emp_id'])->where('id', $data["record_id"])->sum('qa_percentage');
                $totalQAPercentage = ($samplingPercentage-$recordPercentage) + $data['qa_percentage'];
               if($totalQAPercentage <=  100) { 
                    if ($existingRecord) { 
                        $historyRecord = $existingRecord->toArray();
                        $historyRecord['quality_sampling_id'] = $historyRecord['id'];
                        unset($historyRecord['id']);
                        QualitySamplingHistory::create($historyRecord);
                        $existingRecord->update($data);
                    } else {
                        QualitySampling::create($data);
                    }
                } else {
                    $allowPercentage = 100 - ($samplingPercentage-$recordPercentage);
                    session()->flash('error', 'Allowed percentage is up to ' . $allowPercentage . ', but you entered ' . $data['qa_percentage'] . '.');
                    return back();
                
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

    
    // public function getSamplingColumnsList(Request $request)
    // {
    //     if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
    //         try {               
    //            //  $data = QaSampleRandamizer::where('project_id', $request->project_id)->where('sub_project_id', $request->sub_project_id)->get()->toArray();
    //              $dataText = QaSampleRandamizer::where('project_id', $request->project_id)->where('sub_project_id', $request->sub_project_id)->where('sampling_column_input_type','text')
    //              ->pluck('sampling_column_name')->toArray();
    //              $dataSelect = QaSampleRandamizer::where('project_id', $request->project_id)->where('sub_project_id', $request->sub_project_id)->where('sampling_column_input_type','select')->select("sampling_column_name")->get();
    //              $decodedClientName = Helpers::projectName($request->project_id)->project_name;
    //              $decodedsubProjectName = $request->sub_project_id == '--' ? 'project' :Helpers::subProjectName($request->project_id,$request->sub_project_id);
    //              if($decodedsubProjectName != null &&  $decodedsubProjectName != 'project') {
    //               $decodedsubProjectName= $decodedsubProjectName->sub_project_name;
    //              }
    //              $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
    //              $modelName = Str::studly($table_name);
    //              $modelClass = "App\\Models\\" .  $modelName;
    //              $dataSelectQuery = [];
    //              if (class_exists($modelClass)) {
    //               foreach($dataSelect as $sData) {                 
    //                   $dataSelectQuery[$sData->sampling_column_name] = $modelClass::distinct()->pluck($sData->sampling_column_name)->toArray();
    //               }
    //              }
    //              $samplingColumnsDetails = [];
    //              $samplingColumnsDetails['column_data_type'] = 
    //              $samplingColumnsDetails['column_name'] = 
    //              $samplingColumnsDetails['column_options_values'] = 
    //              return response()->json(["data_type_text" => $dataText,"data_type_select" => $dataSelectQuery]);
    //             //  return response()->json(["sampling_column_name" => $data]);
    //         } catch (\Exception $e) {
    //             Log::debug($e->getMessage());
    //         }
    //     } else {
    //         return redirect('/');
    //     }
    // }

    public function getSamplingColumnsList(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {               
                $dataText = QaSampleRandamizer::where('project_id', $request->project_id)
                    ->where('sub_project_id', $request->sub_project_id)
                    ->where('sampling_column_input_type', 'text')
                    ->pluck('sampling_column_name')
                    ->toArray();

                $dataSelect = QaSampleRandamizer::where('project_id', $request->project_id)
                    ->where('sub_project_id', $request->sub_project_id)
                    ->where('sampling_column_input_type', 'select')
                    ->select("sampling_column_name")
                    ->get();

                $decodedClientName = Helpers::projectName($request->project_id)->project_name;
                $decodedsubProjectName = $request->sub_project_id == '--' ? 'project' : Helpers::subProjectName($request->project_id, $request->sub_project_id);
                
                if ($decodedsubProjectName != null && $decodedsubProjectName != 'project') {
                    $decodedsubProjectName = $decodedsubProjectName->sub_project_name;
                }

                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $samplingColumnsDetails = [];

                if (class_exists($modelClass)) {
                    foreach ($dataText as $textColumn) {
                        $samplingColumnsDetails[] = [
                            "sampling_column_name" => $textColumn,
                            "sampling_column_input_type" => "text",
                            "sampling_column_value" => null
                        ];
                    }

                    foreach ($dataSelect as $sData) {
                        $samplingColumnsDetails[] = [
                            "sampling_column_name" => $sData->sampling_column_name,
                            "sampling_column_input_type" => "select",
                            "sampling_column_value" => $modelClass::distinct()->pluck($sData->sampling_column_name)->toArray()
                        ];
                    }
                }
                       
                return response()->json(["sampling_column_name" => $samplingColumnsDetails]);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

}
