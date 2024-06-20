<?php

namespace App\Http\Controllers\MOM;

use App\Http\Controllers\Controller;
use App\Http\Helper\Admin\Helpers as Helpers;
use App\Models\Event;
use App\Models\MomChild;
use App\Models\MomParent;
use App\Models\Timezone;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class MomController extends Controller
{
    public function index(Request $request)
    {if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
        try {
            // if ($request->ajax()) {
            //     $data = MomParent::whereDate('start_time', '>=', $request->start)
            //     ->whereDate('end_time', '<=', $request->end)
            //     ->get(['id', 'meeting_title as title', 'start_time as start', 'end_time as end']);
            //     return response()->json($data);
            // }
            $calendar_values = MomParent::get(['id', 'meeting_title as title', 'start', 'end', 'start_time', 'end_time', 'eta', 'meeting_attendies', 'time_zone', 'req_description'])->toArray();
            $meetingArray = [];
            $momDetails = MomParent::get(['meeting_attendies', 'time_zone'])->toArray();
            foreach ($calendar_values as $key => $data) {
                if ($data['meeting_attendies'] != null) {
                    $attendee = explode(',', $data['meeting_attendies']);
                    $attendeeArray = [];
                    foreach ($attendee as $attendeeKey => $attendeeData) {
                        $attendeeArray[$attendeeKey] = Helpers::getUserNameById($attendeeData);
                    }
                }
                $meetingArray[$key]['id'] = $data['id'];
                $meetingArray[$key]['title'] = $data['title'];
                $meetingArray[$key]['start'] = $data['start'];
                $meetingArray[$key]['end'] = $data['end'];
                $meetingArray[$key]['start_time'] = $data['start_time'];
                $meetingArray[$key]['end_time'] = $data['end_time'];
                $meetingArray[$key]['eta'] = $data['eta'];
                $meetingArray[$key]['meeting_attendies'] = implode(',', $attendeeArray);
                $meetingArray[$key]['time_zone'] = $data['time_zone'] != null ? Timezone::where('id', $data['time_zone'])->first('name')->name : null;
                $meetingArray[$key]['req_description'] = trim(strip_tags($data['req_description']));

            }
            $calendar_data = json_encode($meetingArray);
            // $calendar_values = MomParent::get(['id', 'meeting_title as title', 'start', 'end', 'start_time', 'end_time', 'eta', 'meeting_attendies', 'time_zone', 'req_description']);

            // $calendar_data = $calendar_values->map(function ($data) {
            //     $attendee = explode(',', $data['meeting_attendies']);
            //     $attendeeArray = collect($attendee)->map(function ($attendeeData) {
            //         return Helpers::getUserNameById($attendeeData);
            //     })->implode(',');

            //     return [
            //         'id' => $data['id'],
            //         'title' => $data['title'],
            //         'start' => $data['start'],
            //         'end' => $data['end'],
            //         'start_time' => $data['start_time'],
            //         'end_time' => $data['end_time'],
            //         'eta' => $data['eta'],
            //         'meeting_attendies' => $attendeeArray,
            //         'time_zone' => Timezone::where('id', $data['time_zone'])->first('name')->name,
            //         'req_description' => trim(strip_tags($data['req_description'])),
            //     ];
            // });

            // $calendar_data = $calendar_data->toJson();

            return view('MOM/dashboard', compact('calendar_data'));
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    } else {
        return redirect('/');
    }}

    public function action(Request $request)
    {

        if ($request->ajax()) {
            if ($request->type == 'add') {
                $event = Event::create([
                    'title' => $request->title,
                    'start' => $request->start,
                    'end' => $request->end,
                ]);

                return response()->json($event);
            }

            if ($request->type == 'update') {
                $event = Event::find($request->id)->update([
                    'title' => $request->title,
                    'start' => $request->start,
                    'end' => $request->end,
                ]);

                return response()->json($event);
            }

            if ($request->type == 'delete') {

                $momParent = MomParent::find($request->id)->delete();
                $momChild = MomChild::where('mom_id', $request->id)->delete();
                return response()->json($momParent);
            }
        }
    }
    public function momAdd(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $clickedDate = Helpers::encodeAndDecodeID($request->date, 'decode');
                // dd('hi');
                // if($request->ajax())
                // {
                //     $data = Event::whereDate('start', '>=', $request->start)
                //                ->whereDate('end',   '<=', $request->end)
                //                ->get(['id', 'title', 'start', 'end']);
                //     return response()->json($data);
                // }
                $timezones = Timezone::select('id', DB::raw("concat('(',diff_from_gtm,')',name) as timeZone"))->Orderby('offset')->pluck('timeZone', 'id')->prepend(trans('Select'), '')->toArray();
                return view('MOM/momAdd', compact('timezones', 'clickedDate'));
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function momStore(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {

                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                //  dd($request->all());
                $start = $request->meeting_date . ' ' . $request->start_time;
                $end = $request->meeting_date . ' ' . $request->end_time;
                $momParent = MomParent::create([
                    'meeting_title' => $request->meeting_title,
                    'meeting_attendies' => $request['meeting_attendies'] != null ? implode(",", $request['meeting_attendies']) : null,
                    'time_zone' => $request->time_zone,
                    // 'start_time' => date('Y-m-d H:i:s', strtotime($request->start_time)),
                    // 'end_time' =>  date('Y-m-d H:i:s', strtotime($request->end_time)),
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'meeting_date' => $request->meeting_date,
                    'start' => $start,
                    'end' => $end,
                    'eta' => $request->eta,
                    'req_description' => $request->req_description,
                    'added_by' => $userId,
                ]);
                if ($momParent) {
                    for ($i = 0; $i < count($request['topics']); $i++) {
                        $requiredData['mom_id'] = $momParent->id;
                        $requiredData['topics'] = $request['topics'][$i];
                        $requiredData['topic_description'] = $request['topic_description'][$i];
                        $requiredData['action_item'] = $request['action_item'][$i];
                        $requiredData['responsible_party'] = $request['responsible_party'][$i];
                        $requiredData['topic_eta'] = $request['topic_eta'][$i];
                        $requiredData['added_by'] = $userId;
                        MomChild::create($requiredData);
                        // $momChild = MomChild::create([
                        //     'mom_id' => $momParent->id,
                        //     'topics' => implode(",", $request['topics']),
                        //     'topic_description' => $request['topic_description'],
                        //     'action_item' => $request['action_item'],
                        //     'responsible_party' => $request['responsible_party'],
                        //     'topic_eta' => $request['topic_eta'],
                        // 'added_by'=> $userId,
                        // ]);
                    }
                }
                return redirect('mom/mom_dashboard'.'?parent=' . request()->parent . '&child=' . request()->child);
                //    return view('MOM/dashboard');
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function momEdit(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $requestedId = Helpers::encodeAndDecodeID($request->id, 'decode');
                $momParent = MomParent::where('id', $requestedId)->first();
                $momChild = MomChild::where('mom_id', $requestedId)->get();
                $timezones = Timezone::select('id', DB::raw("concat('(',diff_from_gtm,')',name) as timeZone"))->Orderby('offset')->pluck('timeZone', 'id')->prepend(trans('Select'), '')->toArray();
                $reqDescription = $momParent->req_description;
                return view('MOM/momEdit', compact('momParent', 'momChild', 'timezones', 'reqDescription'));
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function momUpdate(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {

                $data = $request->all();
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $start = $request->meeting_date . ' ' . $request->start_time;
                $end = $request->meeting_date . ' ' . $request->end_time;
                $existingRecord = MomParent::where('id', $request->parent_id)->first();
                if ($existingRecord) {
                    $requiredData['meeting_title'] = $data['meeting_title'];
                    $requiredData['meeting_attendies'] = $request['meeting_attendies'] != null ? implode(",", $request['meeting_attendies']) : null;
                    $requiredData['time_zone'] = $data['time_zone'];
                    $requiredData['start_time'] = $data['start_time'];
                    $requiredData['end_time'] = $data['end_time'];
                    $requiredData['meeting_date'] = $data['meeting_date'];
                    $requiredData['start'] = $start;
                    $requiredData['end'] = $end;
                    $requiredData['eta'] = $request->eta;
                    $requiredData['req_description'] = $request->req_description;
                    $requiredData['added_by'] = $userId;
                    $existingRecord->update($requiredData);
                }
                if ($existingRecord) {
                    // $childExistingRecord = MomChild::whereIn('id',$request['mc_id'])->get();
                    for ($i = 0; $i < count($request['topics']); $i++) {
                        $childData = array(
                            'mom_id' => $request->parent_id,
                            'topics' => $request['topics'][$i],
                            'topic_description' => $request['topic_description'][$i],
                            'action_item' => $request['action_item'][$i],
                            'responsible_party' => $request['responsible_party'][$i],
                            'topic_eta' => $request['topic_eta'][$i],
                            'added_by' => $userId,
                        );
                        if ($request['mc_id'][$i] != null) {
                            MomChild::where('mom_id', '=', $request->parent_id)
                                ->where('id', '=', $request['mc_id'][$i])
                                ->update($childData);
                        } else {
                            MomChild::create($childData);
                        }
                    }
                }
                return redirect('mom/mom_dashboard'.'?parent=' . request()->parent . '&child=' . request()->child);
                //    return view('MOM/dashboard');
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function momDelete(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                if ($request->ajax()) {
                    $momParent = MomParent::find($request->id)->delete();
                    $momChild = MomChild::where('mom_id', $request->id)->delete();
                    return response()->json($momParent);
                }
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

}
