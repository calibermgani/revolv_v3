@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card">
        <div class="card-body py-0 px-7">
            <p style="margin-top: 5rem;margin-left: 0.4rem;">{{ $title }}</p>
            <div class="table-responsive pb-2">
                <table class="table table-separate table-head-custom no-footer dtr-column clients_list_filter" border="1" style="border-collapse: collapse">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            @foreach ($headers as $header)
                                <th>
                                    {{ $header }}</th>
                            @endforeach
                            <th>Reached Target</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($BodyDetails) && count($BodyDetails) > 0)
                            @foreach ($BodyDetails as $data)
                                <td>
                                    {{ $data['user'] != null ? $data['user'] . ' - ' . App\Http\Helper\Admin\Helpers::getUserNameByEmpId($data['user']) : '--' }}
                                    </td>
                                    @foreach ($data['hourlyCount'] as $count)
                                        <td>{{ $count }}</td>
                                    @endforeach
                                    <td>{{ $data['reachedTarget'] }}</td>
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
