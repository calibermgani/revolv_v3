@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card">
        <div class="card-body py-0 px-7">
            <p style="margin-top: 5rem;margin-left: 0.4rem;">{{ $title }}</p>
            <div class="table-responsive pb-2">
                <table class="table table-separate table-head-custom no-footer dtr-column clients_list_filter" id="project_hourly_detailed">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            @foreach ($headers as $header)
                                <th>
                                    {{ $header }}</th>
                            @endforeach
                            <th>Actual Target</th>
                            <th>Achieved Target</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($BodyDetails) && count($BodyDetails) > 0)
                            @foreach ($BodyDetails as $data)
                            @php
                                App\Jobs\GetUserNameByEmpId::dispatch($data['user'])->delay(now()->addSeconds(5));
                                $userNameCaheKey = "emp_name_{$data['user']}";
                                $userName = Cache::get($userNameCaheKey, 0);    
                            @endphp
                                <td>
                                    {{-- {{ $data['user'] != null ? $data['user'] . ' - ' . App\Http\Helper\Admin\Helpers::getUserNameByEmpId($data['user']) : '--' }} --}}
                                    {{  $data['user'] != null ? $data['user'].' - '.ucwords(strtolower($userName)) : '--' }}  
                                </td>
                                    @foreach ($data['hourlyCount'] as $count)
                                        <td>{{ $count }}</td>
                                    @endforeach
                                    <td>{{ $data['slaTarget'] }}</td>
                                    <td>{{ $data['reachedTarget'] }}</td>
                                    <td style={{$data['achievedPercentage'] >= 95 ? "color:green" : "color:red"}}>{{round($data['achievedPercentage'],2)."%"}}</td>
                                    </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="14" style="text-align: center; padding: 5px;">--No Records--</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@push('view.scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $("#project_hourly_detailed").DataTable({
                processing: true,
                ordering: true,
                clientSide: true,
                lengthChange: false,
                searching: true,
                pageLength: 20,
                "order": [[16, "asc"]],
                scrollCollapse: true,
                scrollX: true,
            });
        });
    </script>
@endpush

