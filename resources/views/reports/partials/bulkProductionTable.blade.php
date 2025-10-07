@php
    $filteredHeaders = collect($columnsHeader)->reject(fn($h) => $h === 'record_status')->toArray();
@endphp

<thead>
    <tr>
        @foreach ($filteredHeaders as $columnValue)
            <th>
                @switch($columnValue)
                    @case('chart_status')
                        Charge Status
                    @break
                    @case('CE_emp_id')
                        AR Emp Id
                    @break
                    @case('coder_work_date')
                        AR Work Date
                    @break
                    @case('coder_rework_status')
                        AR Rework Status
                    @break
                    @case('ar_denial_codes')
                        Denial Codes
                    @break
                    @case('ar_substatus_codes')
                        Substatus Codes
                    @break
                    @case('ar_action_code')
                        Action Code
                    @break
                    @case('ar_status_code')
                        Status Code
                    @break
                    @case('coder_error_count')
                        AR Error Count
                    @break
                    @case('coder_rework_reason')
                        AR Rework Reason
                    @break
                    @case('ce_hold_reason')
                        AR Hold Reason
                    @break
                    @default
                        {{ ucwords(str_replace(['_else_', '_'], ['/', ' '], $columnValue)) }}
                @endswitch
            </th>
        @endforeach
    </tr>
</thead>
<tbody>
    @forelse ($completedProjectDetails as $row)
        <tr>
            @foreach ($filteredHeaders as $header)
                @php
                    $data = $row->$header ?? '--';

                    if ($header === 'chart_status') {
                        $recordStatus = $row->record_status ?? '';
                        if (strpos($recordStatus, 'CE_') === 0) {
                            $data = str_replace('CE_', 'AR ', $recordStatus);
                        } elseif (strpos($recordStatus, 'QA_') === 0) {
                            $data = str_replace('QA_', 'QA ', $recordStatus);
                        } else {
                            $data = ucwords(str_replace('_', ' ', $recordStatus));
                        }
                    }

                    if ($header === 'work_hours') {
                        $data = $row->work_time ?? '--';
                    }

                    // QA/AR mappings
                    if ($header == 'QA_status_code' && $data != '--') {
                        $data = App\Http\Helper\Admin\Helpers::qaStatusById($data)['status_code'];
                    }
                    if ($header == 'QA_sub_status_code' && $data != '--') {
                        $data = App\Http\Helper\Admin\Helpers::qaSubStatusById($data)['sub_status_code'];
                    }
                    if ($header == 'qa_classification' && $data != '--') {
                        $data = App\Http\Helper\Admin\Helpers::qaClassificationById($data)['qa_classification'];
                    }
                    if ($header == 'qa_category' && $data != '--') {
                        $data = App\Http\Helper\Admin\Helpers::qaCategoryById($data)['qa_category'];
                    }
                    if ($header == 'qa_scope' && $data != '--') {
                        $data = App\Http\Helper\Admin\Helpers::qaScopeById($data)['qa_scope'];
                    }
                    if ($header == 'ar_status_code' && $data != '--' && $data != null) {
                        $status = App\Http\Helper\Admin\Helpers::arStatusById($data);
                        $data = $status['status_code'] ?? $data;
                    }
                    if ($header == 'ar_action_code' && $data != '--' && $data != null) {
                        $action = App\Http\Helper\Admin\Helpers::arActionById($data);
                        $data = $action['action_code'] ?? $data;
                    }
                    if ($header == 'ar_denial_codes' && $data != '--' && $data != null) {
                        $denial = App\Http\Helper\Admin\Helpers::arDenialById($data);
                        $data = $denial['denialCode'] ?? $data;
                    }
                    if ($header == 'ar_substatus_codes' && $data != '--' && $data != null) {
                        $substatus = App\Http\Helper\Admin\Helpers::arSubStatusById($data);
                        $data = $substatus['substatusCode'] ?? $data;
                    }
                @endphp
                <td class="wrap-text">{{ $data }}</td>
            @endforeach
        </tr>
    @empty
        <tr>
            <td colspan="{{ count($filteredHeaders) }}" class="text-center">No records found</td>
        </tr>
    @endforelse
</tbody>
