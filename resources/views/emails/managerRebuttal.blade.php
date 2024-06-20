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

        <p>Hi {{ $reportingPerson != null ? App\Http\Helper\Admin\Helpers::getUserNameById($reportingPerson) : 'All' }}, </p>

        <p>This claim has been rebutted by both the coder and the QA.</p>
             <table class="table" border="1" style="border-collapse: collapse">

                <thead>

                    <tr>
                        @if ($mailBody)
                            @foreach ($mailBody->getAttributes() as $columnName => $columnValue)
                                @php
                                    $columnsToExclude = [
                                        'id',
                                        'invoke_date',
                                        'CE_emp_id',
                                        'QA_emp_id',
                                        'chart_status',
                                        'ce_hold_reason',
                                        'qa_hold_reason',
                                        'qa_work_status',
                                        'QA_required_sampling',
                                        'QA_rework_comments',
                                        'coder_rework_status',
                                        'coder_rework_reason',
                                        'coder_error_count',
                                        'qa_error_count',
                                        'tl_error_count',
                                        'tl_comments',
                                        'QA_status_code',
                                        'QA_sub_status_code',
                                        'QA_followup_date',
                                        'CE_status_code',
                                        'CE_sub_status_code',
                                        'CE_followup_date',
                                        'updated_at',
                                        'created_at',
                                        'deleted_at',
                                    ];
                                @endphp
                                @if (!in_array($columnName, $columnsToExclude))
                                    <th style="text-align: left;padding: 5px;">
                                        {{ str_replace(['_', '_or_'], [' ', '/'], ucwords(str_replace('_', ' ', $columnName))) }}
                                    </th>
                                @endif
                            @endforeach
                        @else
                            @foreach ($columnsHeader as $columnName => $columnValue)
                                <th><input type="hidden" value={{ $columnValue }}>
                                    {{ ucwords(str_replace(['_or_', '_'], ['/', ' '], $columnValue)) }}
                                </th>
                            @endforeach
                        @endif
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @if (isset($mailBody))
                            @foreach ($mailBody->getAttributes() as $columnName => $columnValue)
                                @php
                                    $columnsToExclude = [
                                        'id',
                                        'invoke_date',
                                        'CE_emp_id',
                                        'QA_emp_id',
                                        'chart_status',
                                        'ce_hold_reason',
                                        'qa_hold_reason',
                                        'qa_work_status',
                                        'QA_required_sampling',
                                        'QA_rework_comments',
                                        'coder_rework_status',
                                        'coder_rework_reason',
                                        'coder_error_count',
                                        'qa_error_count',
                                        'tl_error_count',
                                        'tl_comments',
                                        'QA_status_code',
                                        'QA_sub_status_code',
                                        'QA_followup_date',
                                        'CE_status_code',
                                        'CE_sub_status_code',
                                        'CE_followup_date',
                                        'updated_at',
                                        'created_at',
                                        'deleted_at',
                                    ];
                                @endphp
                                @if (!in_array($columnName, $columnsToExclude))
                                    <td style="text-align: left;padding: 5px;">
                                        @if (str_contains($columnValue, '-') && strtotime($columnValue))
                                            {{ date('m/d/Y', strtotime($columnValue)) }}
                                        @else
                                            @if ($columnName == 'chart_status' && str_contains($columnValue, 'CE_'))
                                                {{ str_replace('CE_', '', $columnValue) }}
                                            @else
                                                {{ $columnValue }}
                                            @endif
                                        @endif
                                    </td>
                                @endif
                            @endforeach
                        @endif
                    </tr>
                </tbody>
            </table>
         <p>Since this claim is currently in your queue, we kindly request your guidance on the way to proceed and resolve this matter.</p>
        <br>
        @include('emails.emailFooter')
    </div>
</body>

</html>
