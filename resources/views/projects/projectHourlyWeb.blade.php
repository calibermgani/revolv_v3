@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card" id="hourly_card">
        <div class="card-body p-0">
            <div class="card-header border-0 px-4">
                <div class="row">
                    <div class="col-md-6">
                        <span class="project_header" style="margin-left: 4px !important;">Hourly</span>
                    </div>
                    <div class="col-md-6">
                        <div class="row" style="justify-content: flex-end;margin-right:1.4rem">
                            <div class="outside" href="javascript:void(0);"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card card-custom custom-top-border">
       
            <div class="card-body mr-8 ml-12" id="filter_section">
                {!! Form::open([
                    'url' => url('projects/project_hourly_web') . '?parent=' . request()->parent . '&child=' . request()->child,
                    'class' => 'form',
                    'id' => 'formSearch',
                    'enctype' => 'multipart/form-data',
                ]) !!}
                @csrf

                <div class="row mr-0 ml-0">
                    <div class="col-md-3">
                        <div class="form-group row row_mar_bm">
                            <div class="col-md-10">
                                <input type="datetime-local" id="startDateTime" name="startDateTime" class="form-control"
                                    value="{{ old('startDateTime', isset($startTime) ? $startTime->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group row row_mar_bm">
                            <div class="col-md-10">
                                <input type="datetime-local" id="endDateTime" name="endDateTime" class="form-control"
                                    value="{{ old('endDateTime', isset($endTime) ? $endTime->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group row">

                            <div class="col-md-10">
                                <button type="submit" class="btn  btn-white-black font-weight-bold"
                                    id="filter_search">Search</button>
                                &nbsp;&nbsp; <button class="btn btn-light-danger" id="filter_clear" tabindex="10"
                                    type="button">
                                    <span>
                                        <span>Clear</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body py-0 px-7">
                <div class="table-responsive pb-2">
                    @php
                        $today1 = \Carbon\Carbon::now(); // 17:00 is 5 PM in 24-hour format
                        $formattedDate = $today1->format('m/d/Y h:i A');
                    @endphp

                    <table class="table table-separate table-head-custom no-footer dtr-column clients_list_filter"
                        id="project_hourly_table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Manager Name</th>
                                <th>Billable FTE</th>
                                <th>SLA Target</th>
                                <th>Target/day</th>
                                <th>Target/Hour</th>
                                @foreach ($headers as $timeSlot)
                                    <th> {{ $timeSlot }}</th>
                                @endforeach                             
                                <th>AR Reason</th>
                                <th>QA Reason</th>
                              
                            </tr>
                        </thead>
                        <tbody>

                            @if (isset($mailBody) && count($mailBody) > 0)
                                @foreach ($mailBody as $data)
                                @php                              
                                    $reasonList = App\Models\ProjectReason::with(['project_ar_reason_type','project_qa_reason_type'])->where('project_id',$data['project_id'])->where('sub_project_id',$data['subproject_id'])->whereBetween('updated_at', [$startTime, $endTime])->get();
                                    $arReasons = $qaReasons = []; 
                                    if(count($reasonList) > 0) {
                                        foreach($reasonList as $reasonData) {
                                            $arReason = isset($reasonData) && isset($reasonData->project_ar_reason_type) ? $reasonData->project_ar_reason_type->reason_type : '--';
                                            if($reasonData->ar_others_comments != NULL){
                                                $arReasons[] = $arReason.' - '.$reasonData->ar_others_comments.'('.date('m/d/Y h:i A',strtotime($reasonData->updated_at)).')'; 
                                            } else {
                                                $arReasons[] = $arReason != '--' ? $arReason.'('.date('m/d/Y h:i A',strtotime($reasonData->updated_at)).')' : '';
                                            }
                                            $qaReason=isset($reasonData) && isset($reasonData->project_qa_reason_type) ? $reasonData->project_qa_reason_type->reason_type : '--';
                                            if($reasonData->qa_others_comments != NULL){
                                                $qaReasons[] = $qaReason.' - '.$reasonData->qa_others_comments.'('.date('m/d/Y h:i A',strtotime($reasonData->updated_at)).')'; 
                                            } else {
                                                $qaReasons[] = $qaReason != '--' ? $qaReason.'('.date('m/d/Y h:i A',strtotime($reasonData->updated_at)).')' : '';
                                            }
                                        }
                                        $arReasonString = implode(', ', array_filter($arReasons));
                                        $qaReasonString = implode(', ', array_filter($qaReasons));
                                    } else {
                                        $arReasons[] = '--'; 
                                        $arReasonString = '--';
                                        $qaReasons[] = '--'; 
                                        $qaReasonString = '--';
                                    }
                                      App\Jobs\GetProjSubPrjJob::dispatch($data['project_id'],$data['subproject_id'])->delay(now()->addSeconds(5));
                                      $prjTotalDetailsCacheKey = 'project_'.$data['project_id'].$data['subproject_id'].'totalDetails' ;
                                      $prjBillableFTE = Cache::get($prjTotalDetailsCacheKey, 0);    
                                      if (!is_array($prjBillableFTE)) {
                                            $prjBillableFTE = ['prjMgrName' => '--', 'prjBillableCount' => '--', 'projectSLATarget' => '--'];
                                        }                     
                                        $targetPerHour =  ((float)$prjBillableFTE['prjBillableCount'] * (float)$prjBillableFTE['projectSLATarget'] / 8);        
                                @endphp
                                    <tr>
                                        <td>
                                            <a target="_blank"
                                                href="http://resolv-aims.com/projects/project_detailed_information_web?project_id={{ App\Http\Helper\Admin\Helpers::encodeAndDecodeID($data['project_id'], 'encode') }}&subproject_id={{ App\Http\Helper\Admin\Helpers::encodeAndDecodeID($data['subproject_id'], 'encode') }}&startTime={{ $startTime }}&endTime={{ $endTime }}">
                                                {{ $data['project'] }}
                                            </a>
                                        </td>
                                        {{-- <td>{{ucwords(strtolower($prjBillableFTE['prjMgrName'])) ?? $prjBillableFTE}}</td>
                                        <td>{{$prjBillableFTE['prjBillableCount'] ?? $prjBillableFTE}}</td>
                                        <td>{{$prjBillableFTE['projectSLATarget'] ?? $prjBillableFTE}}</td>
                                        <td>{{(round((int)$prjBillableFTE['prjBillableCount'] * (int)$prjBillableFTE['projectSLATarget'])) ?? $prjBillableFTE}}</td>
                                        <td>{{(round((int)$prjBillableFTE['prjBillableCount'] * (int)$prjBillableFTE['projectSLATarget']/8)) ?? $prjBillableFTE}}</td> --}}
                                        <td>
                                            @if(is_array($prjBillableFTE) && isset($prjBillableFTE['prjMgrName']))
                                                {{ ucwords(strtolower($prjBillableFTE['prjMgrName'])) }}
                                            @else
                                            {{ is_array($prjBillableFTE) && $prjBillableFTE['prjMgrName']  == null ? '--' : $prjBillableFTE }}
                                            @endif
                                        </td>
                                        <td>
                                            {{ is_array($prjBillableFTE) && isset($prjBillableFTE['prjBillableCount']) ? $prjBillableFTE['prjBillableCount'] : (is_array($prjBillableFTE) && $prjBillableFTE['prjBillableCount']  == null ? '--' : $prjBillableFTE) }}
                                        </td>
                                        <td>
                                            {{ is_array($prjBillableFTE) && isset($prjBillableFTE['projectSLATarget']) ? $prjBillableFTE['projectSLATarget'] : (is_array($prjBillableFTE) && $prjBillableFTE['projectSLATarget'] == null ? '--' : $prjBillableFTE) }}
                                        </td>
                                        <td>
                                            @if(is_array($prjBillableFTE) && isset($prjBillableFTE['prjBillableCount'], $prjBillableFTE['projectSLATarget']))
                                                {{ round((float)$prjBillableFTE['prjBillableCount'] * (float)$prjBillableFTE['projectSLATarget']) }}
                                            @else
                                                {{ is_array($prjBillableFTE) && ($prjBillableFTE['prjBillableCount'] == null  || $prjBillableFTE['projectSLATarget'] == null) ? '--'  : $prjBillableFTE }}
                                            @endif
                                        </td>
                                        <td>
                                            @if(is_array($prjBillableFTE) && isset($prjBillableFTE['prjBillableCount'], $prjBillableFTE['projectSLATarget']))
                                                {{ floor($targetPerHour) == $targetPerHour ? $targetPerHour : round($targetPerHour, 1); }}
                                            @else
                                                {{ is_array($prjBillableFTE) && ($prjBillableFTE['prjBillableCount'] == null  || $prjBillableFTE['projectSLATarget'] == null) ? '--' : $prjBillableFTE }}
                                            @endif
                                        </td>                                        
                                        {{-- @foreach ($data['hourlyCount'] as $count)
                                            @if($prjBillableFTE != '--')
                                                <td style="color: {{ $count < (int)$prjBillableFTE['prjBillableCount'] * (int)$prjBillableFTE['projectSLATarget'] / 8 ? 'red' : 'black' }}; font-weight: {{ $count < (int)$prjBillableFTE['prjBillableCount'] * (int)$prjBillableFTE['projectSLATarget'] / 8 ? 'bold' : 'normal' }};">
                                                    {{ $count }}
                                                </td>
                                            @else
                                                <td style="color:red !important">{{ $count }}</td>
                                            @endif
                                        @endforeach --}}
                                        {{-- @foreach ($data['hourlyCount'] as $count)
                                            @if (is_array($prjBillableFTE) && $prjBillableFTE != '--' && isset($prjBillableFTE['prjBillableCount'], $prjBillableFTE['projectSLATarget']))
                                                @php
                                                    $targetValue = (int)$prjBillableFTE['prjBillableCount'] * (int)$prjBillableFTE['projectSLATarget'] / 8;
                                                @endphp
                                                <td style="color: {{ $count < $targetValue ? 'red' : 'black' }}; font-weight: {{ $count < $targetValue ? 'bold' : 'normal' }};">
                                                    {{ $count }}
                                                </td>
                                            @else
                                                <td style="color: red !important;">{{ $count }}</td>
                                            @endif
                                        @endforeach --}}
                                        @foreach ($data['hourlyCount'] as $count)
                                            @if (is_array($prjBillableFTE) && isset($prjBillableFTE['prjBillableCount'], $prjBillableFTE['projectSLATarget']))
                                                @php
                                                    $targetValue = (float)$prjBillableFTE['prjBillableCount'] * (float)$prjBillableFTE['projectSLATarget'] / 8;
                                                @endphp
                                                <td style="color: {{ $count < $targetValue ? 'red' : 'black' }}; font-weight: {{ $count < $targetValue ? 'bold' : 'normal' }};">
                                                    {{ is_scalar($count) ? $count : json_encode($count) }}
                                                </td>
                                            @else
                                                <td style="color: red !important;">{{ is_scalar($count) ? $count : json_encode($count) }}</td>
                                            @endif
                                        @endforeach


                                        
                                        {{-- <td>{{trim($arReasonString,",")}}</td>
                                        <td>{{trim($qaReasonString,",")}}</td> --}}
                                        <td>{{ is_string($arReasonString) ? trim($arReasonString, ",") : implode(', ', (array) $arReasonString) }}</td>
                                        <td>{{ is_string($qaReasonString) ? trim($qaReasonString, ",") : implode(', ', (array) $qaReasonString) }}</td> 

                                       
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="15" style="text-align: center; padding: 5px;">--No Records--</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
@endsection
@push('view.scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $("#project_hourly_table").DataTable({
                processing: true,
                ordering: true,
                clientSide: true,
                lengthChange: false,
                searching: true,
                pageLength: 20,
                scrollCollapse: true,
                scrollX: true,
                buttons: [{
                    "extend": 'excel',
                    "text": `<span data-dismiss="modal" data-toggle="tooltip" data-placement="left" data-original-title="Export" style="font-size:13px"> <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" fill="currentColor" class="bi bi-box-arrow-up" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1z"/><path fill-rule="evenodd" d="M7.646.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 1.707V10.5a.5.5 0 0 1-1 0V1.707L5.354 3.854a.5.5 0 1 1-.708-.708z"/>
                             </svg>&nbsp;&nbsp;&nbsp;<span>Export</span></span>`,
                    "className": 'btn btn-primary-export text-white',
                    "title": 'Resolv Hourly',
                    "filename": 'resolv_hourly_report',
                    "exportOptions": {
                        "columns": ':not(.notexport)' // Exclude first two columns
                    }
                }],
                dom: "<'row'<'col-md-12'f><'col-md-12't>><'row'<'col-md-5 pt-2'i><'col-md-7 pt-2'p>>"
            })
            table.buttons().container()
                .appendTo('.outside');
                
            $(document).on('click', '#filter_clear', function() {
                location.reload();
            })
            $(document).on('click', '#filter_search', function() {
                KTApp.block('#hourly_card', {
                    overlayColor: '#000000',
                    state: 'danger',
                    opacity: 0.1,
                    message: 'Fetching...',
                });
           });
        });
    </script>
@endpush
