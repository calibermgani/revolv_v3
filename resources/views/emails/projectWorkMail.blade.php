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
                    <th style="text-align: center;padding: 5px;width:110px;background-color:#2f75b5;color:#ffffff;font-weight:100;border-color:black;">Span</th>   
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Project</th>
                    {{-- <th style="text-align: left;padding: 5px;">Chats</th> --}}
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Inventory Uploaded</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Total Users - AR</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Logged Resolv - AR</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Production Users - AR</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Production Count - AR</th>
                    {{-- <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">AIMS Production</th> --}}
                    {{-- <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Total QA</th> --}}
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Logged Resolv - QA</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Production Users - QA</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Production Count - QA</th>
                    {{-- <th style="text-align: left;padding: 5px;">Balance</th> --}}
                </tr>
            </thead>
            <tbody>

                @if (isset($mailBody) && count($mailBody) > 0)
                    @php
                        // if ( isset($yesterday) && !empty($yesterday)) {
                        // $prjDetailsList = App\Http\Helper\Admin\Helpers::getAimsProductionEntryCount($projectIds,$subProjectIds,date('Y-m-d',strtotime($yesterday)));  
                        // } else {
                        //     $prjDetailsList = '--';
                        // }   //AIMS Count
                        if ( isset($yesterday) && !empty($yesterday)) {
                             $spanDetailsList = App\Http\Helper\Admin\Helpers::getAimsSubProjectSpan($projectIds,$subProjectIds);  
                        } else {
                            $spanDetailsList = '--';
                        }               
                    @endphp
                   @php
                        /*
                        * Attach the corresponding span to each mailBody row
                        * before sorting. This keeps all row details together.
                        */
                        $sortedMailBody = collect($mailBody)
                            ->map(function ($row, $index) use ($spanDetailsList) {

                                $row['span'] = '--';

                                if (
                                    is_array($spanDetailsList)
                                    && isset($spanDetailsList[$index])
                                    && is_array($spanDetailsList[$index])
                                ) {
                                    $row['span'] = $spanDetailsList[$index]['span'] ?? '--';
                                }

                                return $row;
                            })
                            ->sort(function ($firstRow, $secondRow) {

                                $firstSpan = trim((string) ($firstRow['span'] ?? ''));
                                $secondSpan = trim((string) ($secondRow['span'] ?? ''));

                                /*
                                * Keep missing or unmapped spans at the bottom.
                                */
                                $firstSpanMissing = $firstSpan === '' || $firstSpan === '--';
                                $secondSpanMissing = $secondSpan === '' || $secondSpan === '--';

                                if ($firstSpanMissing !== $secondSpanMissing) {
                                    return $firstSpanMissing ? 1 : -1;
                                }

                                /*
                                * Natural ordering:
                                * DU - 01
                                * DU - 02
                                * DU - 03
                                */
                                $spanComparison = strnatcasecmp($firstSpan, $secondSpan);

                                if ($spanComparison !== 0) {
                                    return $spanComparison;
                                }

                                /*
                                * Within the same span, sort projects alphabetically.
                                */
                                return strnatcasecmp(
                                    trim((string) ($firstRow['project'] ?? '')),
                                    trim((string) ($secondRow['project'] ?? ''))
                                );
                            })
                            ->values();
                    @endphp

                    @foreach ($sortedMailBody as $data)
                    @php
                    $projectIdsString = implode(",",$projectIds);
                    $rowProjectId = $data['project_id'];
                        $arCacheKey = 'project_' . str_replace(',', '_', $projectIdsString) . '_ar_count';
                        $qaCacheKey = 'project_' . str_replace(',', '_', $projectIdsString) . '_qa_count';
                        /*
                        * Default value must have the same array structure expected below.
                        */
                        $totalAR = Illuminate\Support\Facades\Cache::get($arCacheKey, [
                            'totalArList' => [],
                        ]);
                        $totalQA = Illuminate\Support\Facades\Cache::get($qaCacheKey, [
                            'totalQAList' => [],
                        ]);
                        /*
                        * Handle old or invalid cached values such as 0 or null.
                        */
                        $totalARList = is_array($totalAR)
                            && isset($totalAR['totalArList'])
                            && is_iterable($totalAR['totalArList'])
                                ? $totalAR['totalArList']
                                : [];
                        $totalQAList = is_array($totalQA)
                            && isset($totalQA['totalQAList'])
                            && is_iterable($totalQA['totalQAList'])
                                ? $totalQA['totalQAList']
                                : [];
                        $loggedResolvAR = 0;
                        $totalARCount = 0;
                        foreach ($totalARList as $arList) {
                            if (
                                is_array($arList)
                                && isset($arList['client_id'], $arList['assigned_people'])
                                && $arList['client_id'] == $rowProjectId
                                && $arList['assigned_people'] != null
                            ) {
                                $totalARCount += 1;
                                $loggedResolvAR += App\Models\EmployeeLogin::where(
                                        'user_id',
                                        $arList['assigned_people']
                                    )
                                    ->whereBetween('updated_at', [
                                        $data['yesterDayStartDate'],
                                        $data['yesterDayEndDate'],
                                    ])
                                    ->distinct('user_id')
                                    ->count('user_id');
                            }
                        }
                        $loggedResolvQA = 0;
                        foreach ($totalQAList as $qaList) {
                            if (
                                is_array($qaList)
                                && isset($qaList['client_id'], $qaList['assigned_people'])
                                && $qaList['client_id'] == $rowProjectId
                                && $qaList['assigned_people'] != null
                            ) {
                                $loggedResolvQA += App\Models\EmployeeLogin::where(
                                        'user_id',
                                        $qaList['assigned_people']
                                    )
                                    ->whereBetween('updated_at', [
                                        $data['yesterDayStartDate'],
                                        $data['yesterDayEndDate'],
                                    ])
                                    ->distinct('user_id')
                                    ->count('user_id');
                            }
                        }
                    @endphp
                        <tr>
                             <td style="text-align: center;padding:5px;width:110px;white-space:nowrap;">
                                {{ $data['span'] ?? '--' }}
                            </td>  
                            <td style="text-align: left;padding: 5px 10px;width:250px;white-space:nowrap;">
                                {{ $data['project'] }}
                            </td>                         
                            <td style="text-align: center;padding: 5px;">{{ $data['Chats'] == 0 ? 'No' : 'Yes' }}</td>
                            <td style="text-align: center;padding: 5px;">{{ $totalARCount}}</td>
                            <td style="text-align: center;padding: 5px;">{{ $loggedResolvAR}}</td>
                            <td style="text-align: center;padding: 5px;">{{$data['prodcution_ar']}}</td>
                            <td style="text-align: center;padding: 5px;">{{ $data['Coder'] == 0 ? 'No Activity' : $data['Coder']}}</td>
                            {{-- <td style="text-align: center; padding: 5px; {{ $prjDetailsList != '--' &&  $prjDetailsList != null ? ($data['Coder'] != 0 && $data['Coder'] == $prjDetailsList[$mKey]['aims_count'] ? 'color:green' : 'color:red') : 'color:red' }}">
                                {{  $prjDetailsList != '--' &&  $prjDetailsList != null ? $prjDetailsList[$mKey]['aims_count'] :  $prjDetailsList}}</td> --}}
                            {{-- <td style="text-align: center;padding: 5px;">{{$data['total_qa']}}</td> --}}
                            <td style="text-align: center;padding: 5px;">{{ $loggedResolvQA}}</td>
                            <td style="text-align: center;padding: 5px;">{{$data['prodcution_qa']}}</td>
                            <td style="text-align: center;padding: 5px;">{{ $data['QA'] == 0 ? 'No Activity' : $data['QA']}}</td>
                            {{-- <td style="text-align: left;padding: 5px;">{{ $data['Balance'] }}</td> --}}
                        </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="10" style="text-align: center; padding: 5px;">--No Records--</td>
                </tr>
                @endif
            </tbody>
        </table>
        <br>
        @include('emails.emailFooter')
    </div>
</body>

</html>
