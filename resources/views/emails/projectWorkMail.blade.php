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

    <p>
        Hello Team - Find below the Resolv utilization report for
        {{ $yesterday->format('m/d/Y') }}
    </p>

    @if (isset($mailBody) && count($mailBody) > 0)

        @php
            /*
             * Existing AIMS production count code.
             */
            if (isset($yesterday) && !empty($yesterday)) {
                $prjDetailsList =
                    App\Http\Helper\Admin\Helpers::getAimsProductionEntryCount(
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
                $spanDetailsList =
                    App\Http\Helper\Admin\Helpers::getAimsSubProjectSpan(
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

            /*
             * Required change:
             *
             * Separate records based only on AR Production Count.
             *
             * Coder > 0  = Activity
             * Coder == 0 = No Activity
             */
            $activityMailBody = $sortedMailBody
                ->filter(function ($row) {
                    return isset($row['Coder'])
                        && (int) $row['Coder'] > 0;
                })
                ->values();

            $noActivityMailBody = $sortedMailBody
                ->filter(function ($row) {
                    return !isset($row['Coder'])
                        || (int) $row['Coder'] == 0;
                })
                ->values();

            /*
             * Two separate report tables.
             */
            $reportTables = [
                [
                    'heading' => 'Activity',
                    'rows' => $activityMailBody
                ],
                [
                    'heading' => 'No Activity',
                    'rows' => $noActivityMailBody
                ]
            ];
        @endphp

        @foreach ($reportTables as $reportTable)

            <h3 style="
                margin-top: 20px;
                margin-bottom: 10px;
                font-size: 17px;
                font-weight: bold;
                color: #000000;
            ">
                {{ $reportTable['heading'] }}
            </h3>

            <table class="table"
                   border="1"
                   width="100%"
                   cellpadding="0"
                   cellspacing="0"
                   style="border-collapse: collapse; width: 100%;">

                <thead>
                <tr>

                    <th style="
                        text-align: center;
                        padding: 5px;
                        width: 110px;
                        background-color: #2f75b5;
                        color: #ffffff;
                        font-weight: 100;
                        border-color: black;
                    ">
                        Span
                    </th>

                    <th style="
                        text-align: center;
                        padding: 5px;
                        background-color: #2f75b5;
                        color: #ffffff;
                        font-weight: 100;
                        border-color: black;
                    ">
                        Project
                    </th>

                    <th style="
                        text-align: center;
                        padding: 5px;
                        background-color: #2f75b5;
                        color: #ffffff;
                        font-weight: 100;
                        border-color: black;
                    ">
                        Uploaded Inventory
                    </th>

                    <th style="
                        text-align: center;
                        padding: 5px;
                        background-color: #2f75b5;
                        color: #ffffff;
                        font-weight: 100;
                        border-color: black;
                    ">
                        Billable FTE
                    </th>

                    <th style="
                        text-align: center;
                        padding: 5px;
                        background-color: #2f75b5;
                        color: #ffffff;
                        font-weight: 100;
                        border-color: black;
                    ">
                        SLA Target
                    </th>

                    <th style="
                        text-align: center;
                        padding: 5px;
                        background-color: #2f75b5;
                        color: #ffffff;
                        font-weight: 100;
                        border-color: black;
                    ">
                        AR Production Users
                    </th>

                    <th style="
                        text-align: center;
                        padding: 5px;
                        background-color: #2f75b5;
                        color: #ffffff;
                        font-weight: 100;
                        border-color: black;
                    ">
                        AR Production Count
                    </th>

                    <th style="
                        text-align: center;
                        padding: 5px;
                        background-color: #2f75b5;
                        color: #ffffff;
                        font-weight: 100;
                        border-color: black;
                    ">
                        QA Production Users
                    </th>

                    <th style="
                        text-align: center;
                        padding: 5px;
                        background-color: #2f75b5;
                        color: #ffffff;
                        font-weight: 100;
                        border-color: black;
                    ">
                        QA Production Count
                    </th>

                </tr>
                </thead>

                <tbody>

                @if ($reportTable['rows']->count() > 0)

                    @foreach ($reportTable['rows'] as $mKey => $data)

                        @php
                            /*
                             * Existing code kept unchanged.
                             */
                            $projectIdsString = implode(",", $projectIds);

                            $rowProjectId = $data['project_id'];
                            $rowSubProjectId = $data['sub_project_id'];                          
                            $billableFTE = DB::table('aims_project_du_targets')
                                ->select(
                                    'billable_fte',
                                    'actual_target'
                                )
                                ->where(
                                    'client_id',
                                    $rowProjectId
                                )
                                ->where(
                                    'subproject_id',
                                    $rowSubProjectId
                                )
                                ->first();
                        @endphp

                        <tr>

                            <td style="
                                text-align: center;
                                padding: 5px;
                                width: 110px;
                                white-space: nowrap;
                            ">
                                {{ isset($data['span'])
                                    ? $data['span']
                                    : '--'
                                }}
                            </td>

                            <td style="
                                text-align: left;
                                padding: 5px;
                            ">
                                {{ $data['project'] }}
                            </td>

                            <td style="
                                text-align: center;
                                padding: 5px;
                            ">
                                {{ $data['Chats'] == 0
                                    ? 'No'
                                    : 'Yes'
                                }}
                            </td>

                            <td style="
                                text-align: center;
                                padding: 5px;
                            ">
                                {{ $billableFTE->billable_fte }}
                            </td>

                            <td style="
                                text-align: center;
                                padding: 5px;
                            ">
                                {{ $billableFTE->actual_target }}
                            </td>

                    
                            <td style="
                                text-align: center;
                                padding: 5px;
                            ">
                                {{ $data['prodcution_ar'] }}
                            </td>
                            <td style="
                                text-align: center;
                                padding: 5px;
                            ">
                                {{ $data['Coder'] == 0
                                    ? 'No Activity'
                                    : $data['Coder']
                                }}
                            </td>
                            <td style="
                                text-align: center;
                                padding: 5px;
                            ">
                                {{ $data['prodcution_qa'] }}
                            </td>
                            <td style="
                                text-align: center;
                                padding: 5px;
                            ">
                                {{ $data['QA'] == 0
                                    ? 'No Activity'
                                    : $data['QA']
                                }}
                            </td>

                        </tr>

                    @endforeach
                @else
                    <tr>
                        <td colspan="9"
                            style="
                                text-align: center;
                                padding: 8px;
                            ">
                            --No Records--
                        </td>
                    </tr>

                @endif

                </tbody>

            </table>

            <br>

        @endforeach

    @else

        <table class="table"
               border="1"
               width="100%"
               cellpadding="0"
               cellspacing="0"
               style="border-collapse: collapse; width: 100%;">

            <tbody>
            <tr>
                <td colspan="9"
                    style="
                        text-align: center;
                        padding: 8px;
                    ">
                    --No Records--
                </td>
            </tr>
            </tbody>

        </table>
    @endif
    <br>
    @include('emails.emailFooter')
</div>
</body>
</html>