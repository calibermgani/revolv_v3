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

    <p>
        Hello Team - Find below the Resolv utilization report for
        {{ $yesterday->format('m/d/Y') }}
    </p>

    {{-- <p>Please find below the daily update for the production inventory : 06/07/2024</p> --}}

    <table class="table" border="1" style="border-collapse: collapse">

        <thead>
        <tr>

            {{-- Added Span column --}}
            <th style="text-align: center;padding: 5px;width:110px;background-color:#2f75b5;color:#ffffff;font-weight:100;border-color:black;">
                Span
            </th>

            <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                Project
            </th>

            {{-- <th style="text-align: left;padding: 5px;">Chats</th> --}}

            <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                Inventory Uploaded
            </th>

            <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                Total Users - AR
            </th>

            <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                Logged Resolv - AR
            </th>

            <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                Production Users - AR
            </th>

            <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                Production Count - AR
            </th>

            {{-- <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                AIMS Production
            </th> --}}

            {{-- <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                Total QA
            </th> --}}

            <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                Logged Resolv - QA
            </th>

            <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                Production Users - QA
            </th>

            <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                Production Count - QA
            </th>

            {{-- <th style="text-align: left;padding: 5px;">Balance</th> --}}

        </tr>
        </thead>

        <tbody>

        @if (isset($mailBody) && count($mailBody) > 0)

            @php
                /*
                 * Existing AIMS production count code.
                 */
                if (isset($yesterday) && !empty($yesterday)) {
                    $prjDetailsList = App\Http\Helper\Admin\Helpers::getAimsProductionEntryCount(
                        $projectIds,
                        $subProjectIds,
                        date('Y-m-d', strtotime($yesterday))
                    );
                } else {
                    $prjDetailsList = '--';
                }

                /*
                 * Get span details.
                 */
                if (isset($yesterday) && !empty($yesterday)) {
                    $spanDetailsList = App\Http\Helper\Admin\Helpers::getAimsSubProjectSpan(
                        $projectIds,
                        $subProjectIds
                    );
                } else {
                    $spanDetailsList = [];
                }

                /*
                 * Attach span to the corresponding original project row.
                 *
                 * After attaching the span, sort the complete row.
                 * Therefore, project and all AR/QA details remain together.
                 */
                $sortedMailBody = collect($mailBody)
                    ->map(function ($row, $index) use ($spanDetailsList) {

                        if (
                            isset($spanDetailsList[$index]) &&
                            isset($spanDetailsList[$index]['span'])
                        ) {
                            $row['span'] = $spanDetailsList[$index]['span'];
                        } else {
                            $row['span'] = '--';
                        }

                        return $row;
                    })
                    ->sort(function ($firstRow, $secondRow) {

                        $firstSpan = isset($firstRow['span'])
                            ? trim($firstRow['span'])
                            : '--';

                        $secondSpan = isset($secondRow['span'])
                            ? trim($secondRow['span'])
                            : '--';

                        /*
                         * Missing spans should display at the bottom.
                         */
                        $firstSpanMissing =
                            $firstSpan == '' ||
                            $firstSpan == '--';

                        $secondSpanMissing =
                            $secondSpan == '' ||
                            $secondSpan == '--';

                        if ($firstSpanMissing && !$secondSpanMissing) {
                            return 1;
                        }

                        if (!$firstSpanMissing && $secondSpanMissing) {
                            return -1;
                        }

                        /*
                         * Natural span order:
                         *
                         * DU - 01
                         * DU - 02
                         * DU - 03
                         */
                        $spanResult = strnatcasecmp(
                            $firstSpan,
                            $secondSpan
                        );

                        if ($spanResult != 0) {
                            return $spanResult;
                        }

                        /*
                         * Alphabetical project order inside each span.
                         */
                        $firstProject = isset($firstRow['project'])
                            ? trim($firstRow['project'])
                            : '';

                        $secondProject = isset($secondRow['project'])
                            ? trim($secondRow['project'])
                            : '';

                        return strnatcasecmp(
                            $firstProject,
                            $secondProject
                        );
                    })
                    ->values();
            @endphp

            {{-- Changed only mailBody to sortedMailBody --}}
            @foreach ($sortedMailBody as $mKey => $data)

                @php
                    /*
                     * Existing code kept unchanged.
                     */
                    $projectIdsString = implode(",", $projectIds);

                    $rowProjectId = $data['project_id'];

                    $arCacheKey =
                        'project_' .
                        str_replace(',', '_', $projectIdsString) .
                        '_ar_count';

                    $qaCacheKey =
                        'project_' .
                        str_replace(',', '_', $projectIdsString) .
                        '_qa_count';

                    $totalAR = Illuminate\Support\Facades\Cache::get(
                        $arCacheKey,
                        0
                    );

                    $totalQA = Illuminate\Support\Facades\Cache::get(
                        $qaCacheKey,
                        0
                    );

                    $loggedResolvAR = 0;
                    $totalARCount = 0;

                    /*
                     * Existing AR logic kept unchanged.
                     */
                    foreach ($totalAR['totalArList'] as $key => $arList) {

                        if (
                            $arList['client_id'] == $rowProjectId &&
                            $arList['assigned_people'] != null
                        ) {
                            $totalARCount += 1;

                            $loggedResolvAR += App\Models\EmployeeLogin::where(
                                    'user_id',
                                    $arList['assigned_people']
                                )
                                ->whereBetween(
                                    'updated_at',
                                    [
                                        $data['yesterDayStartDate'],
                                        $data['yesterDayEndDate']
                                    ]
                                )
                                ->distinct('user_id')
                                ->count();
                        }
                    }

                    $loggedResolvQA = 0;

                    /*
                     * Existing QA logic kept unchanged.
                     */
                    foreach ($totalQA['totalQAList'] as $key => $qaList) {

                        if (
                            $qaList['client_id'] == $rowProjectId &&
                            $qaList['assigned_people'] != null
                        ) {
                            $loggedResolvQA += App\Models\EmployeeLogin::where(
                                    'user_id',
                                    $qaList['assigned_people']
                                )
                                ->whereBetween(
                                    'updated_at',
                                    [
                                        $data['yesterDayStartDate'],
                                        $data['yesterDayEndDate']
                                    ]
                                )
                                ->distinct('user_id')
                                ->count();
                        }
                    }
                @endphp

                <tr>

                    {{-- Added Span value --}}
                    <td style="text-align: center;padding:5px;width:110px;white-space:nowrap;">
                        {{ isset($data['span']) ? $data['span'] : '--' }}
                    </td>

                    <td style="text-align: left;padding: 5px;">
                        {{ $data['project'] }}
                    </td>

                    <td style="text-align: center;padding: 5px;">
                        {{ $data['Chats'] == 0 ? 'No' : 'Yes' }}
                    </td>

                    <td style="text-align: center;padding: 5px;">
                        {{ $totalARCount }}
                    </td>

                    <td style="text-align: center;padding: 5px;">
                        {{ $loggedResolvAR }}
                    </td>

                    <td style="text-align: center;padding: 5px;">
                        {{ $data['prodcution_ar'] }}
                    </td>

                    <td style="text-align: center;padding: 5px;">
                        {{ $data['Coder'] == 0 ? 'No Activity' : $data['Coder'] }}
                    </td>

                    {{-- 
                    <td style="text-align: center; padding: 5px;
                        {{ $prjDetailsList != '--' && $prjDetailsList != null
                            ? ($data['Coder'] != 0 &&
                               $data['Coder'] == $prjDetailsList[$mKey]['aims_count']
                                ? 'color:green'
                                : 'color:red')
                            : 'color:red'
                        }}">

                        {{ $prjDetailsList != '--' && $prjDetailsList != null
                            ? $prjDetailsList[$mKey]['aims_count']
                            : $prjDetailsList
                        }}
                    </td>
                    --}}

                    {{-- <td style="text-align: center;padding: 5px;">
                        {{ $data['total_qa'] }}
                    </td> --}}

                    <td style="text-align: center;padding: 5px;">
                        {{ $loggedResolvQA }}
                    </td>

                    <td style="text-align: center;padding: 5px;">
                        {{ $data['prodcution_qa'] }}
                    </td>

                    <td style="text-align: center;padding: 5px;">
                        {{ $data['QA'] == 0 ? 'No Activity' : $data['QA'] }}
                    </td>

                    {{-- <td style="text-align: left;padding: 5px;">
                        {{ $data['Balance'] }}
                    </td> --}}

                </tr>

            @endforeach

        @else

            <tr>
                <td colspan="10" style="text-align: center; padding: 5px;">
                    --No Records--
                </td>
            </tr>

        @endif

        </tbody>
    </table>

    <br>

    @include('emails.emailFooter')

</div>

</body>

</html>