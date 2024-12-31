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

        {{-- <h4>
            <p>Hello Team, </p>
        </h4> --}}

        <p>Hello Team - Find below the Resolv utilization report for {{$yesterday->format('m/d/Y')}}</p>
        {{-- <p>Please find below the daily update for the production inventory : 06/07/2024</p> --}}
       
        <table class="table" border="1" style="border-collapse: collapse">
            <thead>
                <tr>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Project</th>
                    {{-- <th style="text-align: left;padding: 5px;">Chats</th> --}}
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Inventory Uploaded</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Total Users - AR</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Logged Resolv - AR</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Production Users - AR</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Production Count - AR</th>
                    {{-- <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Total QA</th> --}}
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Logged Resolv - QA</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Production Users - QA</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Production Count - QA</th>
                    {{-- <th style="text-align: left;padding: 5px;">Balance</th> --}}
                </tr>
            </thead>
            <tbody>

                @if (isset($mailBody) && count($mailBody) > 0)
                    @foreach ($mailBody as $data)
                    @php
                    $projectIdsString = implode(",",$projectIds);
                    $rowProjectId = $data['project_id'];
                         $arCacheKey = 'project_' . str_replace(',', '_', $projectIdsString) . '_ar_count';
                        $qaCacheKey = 'project_' . str_replace(',', '_', $projectIdsString) . '_qa_count';      
                        $totalAR = Illuminate\Support\Facades\Cache::get($arCacheKey, 0);
                        $totalQA = Illuminate\Support\Facades\Cache::get($qaCacheKey, 0);
                    
                        $loggedResolvAR = 0;$totalARCount = 0;
                        foreach($totalAR['totalArList'] as $key => $arList){          
                            if($arList['client_id'] == $rowProjectId && $arList['assigned_people'] != null){
                                $totalARCount += 1;
                            $loggedResolvAR +=  App\Models\EmployeeLogin::where('user_id', $arList['assigned_people'])
                                                ->whereBetween('updated_at', [$data['yesterDayStartDate'], $data['yesterDayEndDate']])
                                                ->distinct('user_id')
                                                ->count();
                            }
                        }
                        $loggedResolvQA = 0;
                        foreach($totalQA['totalQAList'] as $key => $qaList){    
                            if($qaList['client_id'] == $rowProjectId && $qaList['assigned_people'] != null){
                            $loggedResolvQA +=  App\Models\EmployeeLogin::where('user_id', $qaList['assigned_people'])
                                                ->whereBetween('updated_at', [$data['yesterDayStartDate'], $data['yesterDayEndDate']])
                                                ->distinct('user_id')
                                                ->count();
                            }
                        }
                    @endphp
                        <tr>
                            <td style="text-align: center;padding: 5px;">{{ $data['project'] }}</td>
                            <td style="text-align: center;padding: 5px;">{{ $data['Chats'] == 0 ? 'No' : 'Yes' }}</td>
                            <td style="text-align: center;padding: 5px;">{{ $totalARCount}}</td>
                            <td style="text-align: center;padding: 5px;">{{ $loggedResolvAR}}</td>
                            <td style="text-align: center;padding: 5px;">{{$data['prodcution_ar']}}</td>
                            <td style="text-align: center;padding: 5px;">{{ $data['Coder'] == 0 ? 'No Activity' : $data['Coder']}}</td>
                            {{-- <td style="text-align: center;padding: 5px;">{{$data['total_qa']}}</td> --}}
                            <td style="text-align: center;padding: 5px;">{{ $loggedResolvQA}}</td>
                            <td style="text-align: center;padding: 5px;">{{$data['prodcution_qa']}}</td>
                            <td style="text-align: center;padding: 5px;">{{ $data['QA'] == 0 ? 'No Activity' : $data['QA']}}</td>
                            {{-- <td style="text-align: left;padding: 5px;">{{ $data['Balance'] }}</td> --}}
                        </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="9" style="text-align: center; padding: 5px;">--No Records--</td>
                </tr>
                @endif
            </tbody>
        </table>
        <br>
        @include('emails.emailFooter')
    </div>
</body>

</html>
