@if (in_array($columnName ?? '', ['CE_emp_id', 'QA_emp_id'], true))
    <span data-emp-id="{{ $columnValue }}">{{ \App\Http\Helper\Admin\Helpers::empIdWithUserName($columnValue) }}</span>
@else
    {{ $columnValue }}
@endif
