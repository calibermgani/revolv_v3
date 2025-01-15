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
                            $arReasonString = implode(', ', $arReasons);
                            $qaReasonString = implode(', ', $qaReasons);
                        } else {
                            $arReasons[] = '--'; 
                            $arReasonString = '--';
                            $qaReasons[] = '--'; 
                            $qaReasonString = '--';
                        }
                    @endphp
                        <tr>
                            <td style="text-align: center; padding: 5px;">
                                <a href="http://resolv-aims.com/projects/project_detailed_information?project_id={{ App\Http\Helper\Admin\Helpers::encodeAndDecodeID($data['project_id'],'encode') }}&subproject_id={{ App\Http\Helper\Admin\Helpers::encodeAndDecodeID($data['subproject_id'],'encode') }}&requested_date={{ $formattedDate }}">
                                    {{ $data['project'] }}
                                </a>
                            </td>
                            
                            
                            {{-- <td style="text-align: center;padding: 5px;">{{ $data['project'] }}</td> --}}
                            {{-- @foreach ($timeSlots as $timeSlot) --}}
                            {{-- @php
                            dd($mailBody,$timeSlots,$data,$timeSlot);
                          @endphp --}}
                                {{-- <td style="text-align: center;padding: 5px;">{{ $data['hourlyCount'] }}</td> --}}
                            {{-- @endforeach --}}
                            @foreach ($data['hourlyCount'] as $count)
                               <td style="text-align: center;padding: 5px;">{{ $count }}</td>
                           @endforeach
                           <td style="text-align: center;padding: 5px;">{{trim($arReasonString,",")}}</td>
                           <td style="text-align: center;padding: 5px;">{{trim($qaReasonString,",")}}</td>
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
