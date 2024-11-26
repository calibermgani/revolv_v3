@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card">
        <div class="card card-custom custom-top-border">
            <div class="card-body py-0 px-7">
                <div class="table-responsive" style="margin-left: 5rem;margin-right: 5rem;">
                    <table class="table" border="1" style="border-collapse: collapse">
                        <thead>
                            <tr>
                                <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;"
                                    width="8%">
                                    User Name</th>
                                @foreach ($headers as $header)
                                    <th
                                        style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                        {{ $header }}</th>
                                @endforeach
                                <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;"
                                    width="8%">Reached Target</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($BodyDetails) && count($BodyDetails) > 0)
                                @foreach ($BodyDetails as $data)
                                    <tr>
                                        <td style="text-align: center; padding: 5px;" width="8%">
                                            {{ $data['user'] != null ? $data['user'] . ' - ' . App\Http\Helper\Admin\Helpers::getUserNameByEmpId($data['user']) : '--' }}
                                        </td>
                                        @foreach ($data['hourlyCount'] as $count)
                                            <td style="text-align: center;padding: 5px;">{{ $count }}</td>
                                        @endforeach
                                        <td style="text-align: center;padding: 5px;" width="8%">
                                            {{ $data['reachedTarget'] }}</td>
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
    </div>
@endsection
