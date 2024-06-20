<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\InventoryWound;
use App\Models\InventoryWoundDuplicate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Mail;
use App\Mail\ProcodeProjectFile;
use App\Models\MailNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

class AIGController extends Controller {
    public function projectDetails(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $batchSize = 2;
                $project_information = $request->all();

                if($project_information['input_value'] == "File is not there" && $project_information['project_name']) {
                        $fileStatus = "The ".Str::upper($project_information['project_name'])." inventory is not in the specified location. Could you please check and place the inventory files for today as soon as possible. This will help avoid delays in production.";
                        $mailHeader = Str::upper($project_information['project_name'])." - "."File not in Specific folder";
                        $Inventory_wound_data = [];
                        $toMailId = "mgani@caliberfocus.com";
                        Mail::to($toMailId)->send(new ProcodeProjectFile($mailHeader, $fileStatus, $Inventory_wound_data));
                        // $mailNotification['clients'] = $project_information['project_name'];
                        // $mailNotification['sub_clients'] = $project_information['project_name'];
                        // $mailNotification['module'] = $project_information['project_name'];
                        // $mailNotification['date'] = $project_information['project_name'];
                        // $mailNotification['notification_date'] = Carbon::now();
                        // MailNotification::create($mailNotification);

                } else if(empty($project_information['input_value']) && $project_information['project_name']) {
                    $toMailId = "mgani@caliberfocus.com";
                    $fileStatus = "It appears that there are no more records for ".Str::upper($project_information['project_name']).". Could you please verify and replace the files with the correct format";
                    $mailHeader = Str::upper($project_information['project_name'])." - "."Empty Records";
                    $Inventory_wound_data = [];
                    Mail::to($toMailId)->send(new ProcodeProjectFile($mailHeader, $fileStatus,  $Inventory_wound_data));
                } else if($project_information['input_value'] && $project_information['project_name']) {

                    $databaseConnection = Str::lower($project_information['project_name']);
                    Config::set('database.connections.mysql.database', $databaseConnection);
                    $inventoryWound = new InventoryWound();
                    $inventoryWoundColumns = $inventoryWound->getTableColumns();
                    $keysMatchColumns = empty(array_diff(array_keys($project_information['input_value'][0]), $inventoryWoundColumns));
                    $propertyCount = count($project_information['input_value'][0]);

                    if($propertyCount != 34 || $keysMatchColumns == false) {
                        $toMailId = "mgani@caliberfocus.com";
                        $fileStatus = "The inventory format for ".Str::upper($project_information['project_name'])." does not match our records. Could you please verify and replace the files with the correct format";
                        $mailHeader = Str::upper($project_information['project_name'])." - "."File format not match";
                        $Inventory_wound_data = [];
                        Mail::to($toMailId)->send(new ProcodeProjectFile($mailHeader, $fileStatus,  $Inventory_wound_data));
                    } else {
                        $duplicateRecords = [];
                        // foreach($project_information['input_value'] as $Inventory_wound_data) {
                            foreach (array_chunk($project_information['input_value'], $batchSize) as $batch) {
                                foreach ($batch as $Inventory_wound_data) {
                            $Inventory_wound_data['inventory_date'] = date('Y-m-d');
                            $databaseConnection = Str::lower($project_information['project_name']);
                            Config::set('database.connections.mysql.database', $databaseConnection);

                            //collect($Inventory_wound_data)->chunk($batchSize)->each(function () use ($Inventory_wound_data, $databaseConnection, $duplicateRecords) {
                                    $ticketNumber = InventoryWound::where('ticket_number',$Inventory_wound_data['ticket_number'])->where('inventory_date',$Inventory_wound_data['inventory_date'])->first();
                                    if($ticketNumber) {
                                        $inventoryDate = InventoryWoundDuplicate::where('ticket_number',$Inventory_wound_data['ticket_number'])->where('inventory_date',$Inventory_wound_data['inventory_date'])->first();
                                        if($inventoryDate) {
                                            $inventoryDate->update($Inventory_wound_data);
                                        } else {
                                            InventoryWoundDuplicate::create($Inventory_wound_data);
                                        }
                                    } else {
                                    InventoryWound::create($Inventory_wound_data);
                                    }
                                //});
                                }
                            }

                                $duplicateRecords = InventoryWoundDuplicate::where('inventory_date',$Inventory_wound_data['inventory_date'])->get();
                                $toMailId = "mgani@caliberfocus.com";
                                $fileStatus = "The ".Str::upper($project_information['project_name'])." has some duplicate inventory records. Please find the list of duplicates below";
                                $mailHeader = Str::upper($project_information['project_name'])." - "."Duplicate Entries";
                                Mail::to($toMailId)->send(new ProcodeProjectFile($mailHeader, $fileStatus, $duplicateRecords));
                    }

                }

                    $project_details = array(
                        'code' =>200,
                        'message' =>'Testing',
                        //'input_value' =>'success'

                );

                return response()->json(['key' =>$project_details]);
                return $return_value = Response::json($project_details);
            } catch (Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function fileNotInFolder(Request $request)
    {
        $project_information = $request->all();
      //  if($project_information['input_value'] == "File is not there" && $project_information['project_name']) {
        // if($request->project_name && $request->sub_project_name) {
                // $fileStatus = "The ".Str::upper($request->project_name)." inventory is not in the specified location. Could you please check and place the inventory files for today as soon as possible. This will help avoid delays in production.";
                // $mailHeader = Str::upper($request->project_name)." - "."File not in Specific folder";
                $fileStatus = "The  xxx inventory is not in the specified location. Could you please check and place the inventory files for today as soon as possible. This will help avoid delays in production.";
                $mailHeader = "xxx File not in Specific folder";
                $Inventory_wound_data = [];
                // $toMailId = "mgani@caliberfocus.com";
                $toMailId = 'vithya@caliberfocus.com,mgani@caliberfocus.com';
                $toMailId = $toMailId != null ? explode(",",$toMailId) : [];
                Mail::to($toMailId)->send(new ProcodeProjectFile($mailHeader, $fileStatus, $Inventory_wound_data));
                return response()->json([
                    "message" => "Data received successfully",
                    // "input_value"=>$request->project_name,
                    // 'Project_name' =>$request->sub_project_name,
                    'inventory_wond' => 'Testing',

                    ]);
                // } else {
                //     return response()->json([
                //         "message" => "Params Not There",
                //         "input_value"=>$request->project_name,
                //         'Project_name' =>$request->sub_project_name,
                //         'inventory_wond' => 'Testing',

                //         ]);
                // }
       // }
    }
    public function emptyRecordMail(Request $request)
    {
        $project_information = $request->all();
        if($request->project_name && $request->sub_project_name) {
            $toMailId = "mgani@caliberfocus.com";
            $fileStatus = "It appears that there are no more records for ".Str::upper($request->project_name).". Could you please verify and replace the files with the correct format";
            $mailHeader = Str::upper($request->project_name)." - "."Empty Records";
            $Inventory_wound_data = [];
            Mail::to($toMailId)->send(new ProcodeProjectFile($mailHeader, $fileStatus,  $Inventory_wound_data));
            return response()->json([
                "message" => "Data received successfully",
                "input_value"=>$request->project_name,
                'Project_name' =>$request->sub_project_name,
                'inventory_wond' => 'Testing',
                ]);
        } else {
            return response()->json([
                "message" => "Params Not There",
                "input_value"=>$request->project_name,
                'Project_name' =>$request->sub_project_name,
                'inventory_wond' => 'Testing',

                ]);
        }
    }
    public function fileFormatNotMatch(Request $request)
    {
        $project_information = $request->all();
           // $inventoryWound = new InventoryWound();
            $model =   Str::upper($project_information['project_name']).Str::upper($project_information['sub_project_name']);
            $modelClass = new $model;
            $modelClassColumns = $modelClass->getTableColumns();
            $keysMatchColumns = empty(array_diff(array_keys($project_information['input_value'][0]), $modelClassColumns));
            $propertyCount = count($project_information['input_value'][0]);

            if($keysMatchColumns == false) {
                $toMailId = "mgani@caliberfocus.com";
                $fileStatus = "The inventory format for ".Str::upper($project_information['project_name'])." does not match our records. Could you please verify and replace the files with the correct format";
                $mailHeader = Str::upper($project_information['project_name'])." - "."File format not match";
                $Inventory_wound_data = [];
                Mail::to($toMailId)->send(new ProcodeProjectFile($mailHeader, $fileStatus,  $Inventory_wound_data));
            }
    }
    public function duplicateEntryMail(Request $request)
    {
        $project_information = $request->all();
        $duplicateRecords = [];
            foreach (array_chunk($project_information['input_value'], $batchSize) as $batch) {
                foreach ($batch as $Inventory_wound_data) {
            $Inventory_wound_data['inventory_date'] = date('Y-m-d');
            $databaseConnection = Str::lower($project_information['project_name']);
            Config::set('database.connections.mysql.database', $databaseConnection);

                      $ticketNumber = modelClass::where('ticket_number',$Inventory_wound_data['ticket_number'])->where('inventory_date',$Inventory_wound_data['inventory_date'])->first();
                    if($ticketNumber) {
                        $inventoryDate = modelClassDuplicate::where('ticket_number',$Inventory_wound_data['ticket_number'])->where('inventory_date',$Inventory_wound_data['inventory_date'])->first();
                        if($inventoryDate) {
                            $inventoryDate->update($Inventory_wound_data);
                        } else {
                            modelClassDuplicate::create($Inventory_wound_data);
                        }
                    } else {
                    modelClass::create($Inventory_wound_data);
                    }
                }
            }

                $duplicateRecords = modelClassDuplicate::where('inventory_date',$Inventory_wound_data['inventory_date'])->get();
                $toMailId = "mgani@caliberfocus.com";
                $fileStatus = "The ".Str::upper($project_information['project_name'])." has some duplicate inventory records. Please find the list of duplicates below";
                $mailHeader = Str::upper($project_information['project_name'])." - "."Duplicate Entries";
                Mail::to($toMailId)->send(new ProcodeProjectFile($mailHeader, $fileStatus, $duplicateRecords));
    }
}
