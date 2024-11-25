@extends('layouts.app3')
@section('content')

<div class="table-responsive pb-2">

    {{-- <h4>
        <p>Hello Team, </p>
    </h4> --}}

    <p>Resolv utilization report for {{$yesterday->format('m/d/Y')}}</p>
    {{-- <p>Please find below the daily update for the production inventory : 06/07/2024</p> --}}
   
    <table   class="table table-separate table-head-custom no-footer dtr-column clients_list_filter"  border="1" style="border-collapse: collapse">
        <thead>
            <tr>
                <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Project</th>
                {{-- <th style="text-align: left;padding: 5px;">Chats</th> --}}
                <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Inventory Uploaded</th>
                <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Total Users - AR</th>
                <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Logged Resolv - AR</th>
                <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Production Users - AR</th>
                <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">AR</th>
                {{-- <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Total QA</th> --}}
                <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Logged Resolv - QA</th>
                <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Production - QA</th>
                <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">QA</th>
                {{-- <th style="text-align: left;padding: 5px;">Balance</th> --}}
            </tr>
        </thead>
        <tbody>

            @if (isset($mailBody) && count($mailBody) > 0)
                @foreach ($mailBody as $data)
                    <tr>
                        <td style="text-align: center;padding: 5px;">{{ $data['project'] }}</td>
                        <td style="text-align: center;padding: 5px;">{{ $data['Chats'] == 0 ? 'No' : 'Yes' }}</td>
                        <td style="text-align: center;padding: 5px;">{{ $data['total_ar']}}</td>
                        <td style="text-align: center;padding: 5px;">{{ $data['logged_resolv_ar']}}</td>
                        <td style="text-align: center;padding: 5px;">{{$data['prodcution_ar']}}</td>
                        <td style="text-align: center;padding: 5px;">{{ $data['Coder'] == 0 ? 'No Activity' : $data['Coder']}}</td>
                        {{-- <td style="text-align: center;padding: 5px;">{{$data['total_qa']}}</td> --}}
                        <td style="text-align: center;padding: 5px;">{{ $data['logged_resolv_qa']}}</td>
                        <td style="text-align: center;padding: 5px;">{{$data['prodcution_qa']}}</td>
                        <td style="text-align: center;padding: 5px;">{{ $data['QA'] == 0 ? 'No Activity' : $data['QA']}}</td>
                        {{-- <td style="text-align: left;padding: 5px;">{{ $data['Balance'] }}</td> --}}
                    </tr>
                @endforeach
            @else
            <tr>
                <td colspan="4" style="text-align: center; padding: 5px;">--No Records--</td>
            </tr>
            @endif
        </tbody>
    </table>
   
</div>
@endsection