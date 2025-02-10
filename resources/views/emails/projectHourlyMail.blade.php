<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>

<style>
    * {
        font-family: Verdana, Arial, sans-serif;
        color: black;
    }

    table {
        font-size: small;
    }

    thead,
    th {
        background-color: #0e969c2b;
    }

    th,
    td {
        text-align: center;
        padding-right: 30px;
    }
</style>

<body>

    <div class="table-responsive pb-2">

        @php
        $today1 = \Carbon\Carbon::now(); // 17:00 is 5 PM in 24-hour format
        $formattedDate = $today1->format('m/d/Y h:i A');
        @endphp

        <p>Hello Team - Find below the Resolv Hourly report for {{$formattedDate}}</p>

        <table class="table" border="1" style="border-collapse: collapse">
            <thead>
                <tr>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Project</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Manager Name</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Billable FTE</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">SLA Target</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Target/day</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Target/Hour</th>
                    @foreach ($timeSlots as $timeSlot)
                        <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">{{ $timeSlot }}</th>
                    @endforeach
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">AR Reason</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">QA Reason</th>
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
                        App\Jobs\getProjectSubProjectBillableFTE::dispatch($data['project_id'],$data['subproject_id'])->delay(now()->addSeconds(5));
                        $prjBillableFTECacheKey = 'project_'.$data['project_id'].$data['subproject_id'].'BillableFTE' ;
                        $prjBillableFTE = Cache::get($prjBillableFTECacheKey, 0);
                        if (!is_array($prjBillableFTE)) {
                            $prjBillableFTE = ['prjMgrName' => '--', 'prjBillableCount' => '--', 'projectSLATarget' => '--'];
                        }            
                    @endphp
                        <tr>
                            <td style="text-align: center; padding: 5px;">
                                <a href="http://resolv-aims.com/projects/project_detailed_information?project_id={{ App\Http\Helper\Admin\Helpers::encodeAndDecodeID($data['project_id'],'encode') }}&subproject_id={{ App\Http\Helper\Admin\Helpers::encodeAndDecodeID($data['subproject_id'],'encode') }}&requested_date={{ $formattedDate }}">
                                    {{ $data['project'] }}
                                </a>
                            </td>
                            
                            {{-- <td style="text-align: center; padding: 5px;">{{ucwords(strtolower($prjBillableFTE['prjMgrName'])) ?? $prjBillableFTE}}</td>
                            <td style="text-align: center; padding: 5px;">{{$prjBillableFTE['prjBillableCount'] ?? $prjBillableFTE}}</td>
                            <td style="text-align: center; padding: 5px;">{{$prjBillableFTE['projectSLATarget'] ?? $prjBillableFTE}}</td>
                            <td style="text-align: center; padding: 5px;">{{(round((int)$prjBillableFTE['prjBillableCount'] * (int)$prjBillableFTE['projectSLATarget'])) ?? $prjBillableFTE}}</td>
                            <td style="text-align: center; padding: 5px;">{{(round((int)$prjBillableFTE['prjBillableCount'] * (int)$prjBillableFTE['projectSLATarget']/8)) ?? $prjBillableFTE}}</td>
                            @foreach ($data['hourlyCount'] as $count)
                             @if($prjBillableFTE != '--')
                                <td style="text-align: center;padding: 5px;color: {{ $count < (int)$prjBillableFTE['prjBillableCount'] * (int)$prjBillableFTE['projectSLATarget'] / 8 ? 'red' : 'black' }}; font-weight: {{ $count < (int)$prjBillableFTE['prjBillableCount'] * (int)$prjBillableFTE['projectSLATarget'] / 8 ? 'bold' : 'normal' }};">
                                    {{ $count }}
                                </td>
                            @else
                                   <td style="color:red !important;text-align: center;padding: 5px;">{{ $count }}</td>
                            @endif
                            @endforeach --}}
                           <td style="text-align: center; padding: 5px;">
                                @if(is_array($prjBillableFTE) && isset($prjBillableFTE['prjMgrName']))
                                    {{ ucwords(strtolower($prjBillableFTE['prjMgrName'])) }}
                                @else
                                   {{ is_array($prjBillableFTE) && $prjBillableFTE['prjMgrName']  == null ? '--' : $prjBillableFTE }}
                                @endif
                            </td>
                           <td style="text-align: center; padding: 5px;">
                                {{ is_array($prjBillableFTE) && isset($prjBillableFTE['prjBillableCount']) ? $prjBillableFTE['prjBillableCount'] : (is_array($prjBillableFTE) && $prjBillableFTE['prjBillableCount']  == null ? '--' : $prjBillableFTE) }}
                            </td>
                           <td style="text-align: center; padding: 5px;">
                                {{ is_array($prjBillableFTE) && isset($prjBillableFTE['projectSLATarget']) ? $prjBillableFTE['projectSLATarget'] : (is_array($prjBillableFTE) && $prjBillableFTE['projectSLATarget']  == null ? '--' : $prjBillableFTE) }}
                            </td>
                           <td style="text-align: center; padding: 5px;">
                                @if(is_array($prjBillableFTE) && isset($prjBillableFTE['prjBillableCount'], $prjBillableFTE['projectSLATarget']))
                                    {{ round((float)$prjBillableFTE['prjBillableCount'] * (float)$prjBillableFTE['projectSLATarget']) }}
                                @else
                                {{ is_array($prjBillableFTE) && ($prjBillableFTE['prjBillableCount'] == null  || $prjBillableFTE['projectSLATarget'] == null) ? '--'  : $prjBillableFTE }}
                                @endif
                            </td>
                           <td style="text-align: center; padding: 5px;">
                                @if(is_array($prjBillableFTE) && isset($prjBillableFTE['prjBillableCount'], $prjBillableFTE['projectSLATarget']))
                                    {{ round((float)$prjBillableFTE['prjBillableCount'] * (float)$prjBillableFTE['projectSLATarget'] / 8) }}
                                @else
                                {{ is_array($prjBillableFTE) && ($prjBillableFTE['prjBillableCount'] == null  || $prjBillableFTE['projectSLATarget'] == null) ? '--'  : $prjBillableFTE }}
                                @endif
                            </td>    
                            @foreach ($data['hourlyCount'] as $count)
                                @if (is_array($prjBillableFTE) && $prjBillableFTE != '--' && isset($prjBillableFTE['prjBillableCount'], $prjBillableFTE['projectSLATarget']))
                                    @php
                                        $targetValue = (float)$prjBillableFTE['prjBillableCount'] * (float)$prjBillableFTE['projectSLATarget'] / 8;
                                    @endphp
                                    <td style="text-align: center;padding: 5px;color: {{ $count < $targetValue ? 'red' : 'black' }}; font-weight: {{ $count < $targetValue ? 'bold' : 'normal' }};">
                                        {{ is_scalar($count) ? $count : json_encode($count) }}
                                    </td>
                                @else
                                    <td style="text-align: center;padding: 5px;color: red !important;">{{ is_scalar($count) ? $count : json_encode($count) }}</td>
                                @endif
                            @endforeach
                           {{-- <td style="text-align: center;padding: 5px;">{{trim($arReasonString,",")}}</td>
                           <td style="text-align: center;padding: 5px;">{{trim($qaReasonString,",")}}</td> --}}
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
        <br>
        @include('emails.emailFooter')
    </div>
</body>

</html>
