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

        @php
        $today1 = \Carbon\Carbon::now(); // 17:00 is 5 PM in 24-hour format
        $formattedDate = $today1->format('m/d/Y h:i A');
        $detailedDate = $today1->format('Y-m-d');
        $formattedDate = '11/22/2024 04:58 AM';
        // dd($mailBody,$timeSlots);
        @endphp

        <p>Hello Team - Find below the Resolv Hourly report for {{$formattedDate}}</p>

        <table class="table" border="1" style="border-collapse: collapse">
            <thead>
                <tr>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Project</th>
                    @foreach ($timeSlots as $timeSlot)
                        <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">{{ $timeSlot }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>

                @if (isset($mailBody) && count($mailBody) > 0)
                    @foreach ($mailBody as $data)
                  
                        <tr>
                            <td style="text-align: center; padding: 5px;">
                                <a href="http://resolv-aims.com/projects/project_detailed_information?project_id={{ App\Http\Helper\Admin\Helpers::encodeAndDecodeID($data['project_id'],'encode') }}&subproject_id={{ App\Http\Helper\Admin\Helpers::encodeAndDecodeID($data['subproject_id'],'encode') }}&requested_date={{ $formattedDate }}">
                                    {{ $data['project'] }}
                                </a>
                            </td>
                            
                            
                            {{-- <td style="text-align: center;padding: 5px;">{{ $data['project'] }}</td> --}}
                            {{-- @foreach ($timeSlots as $timeSlot) --}}
                            {{-- @php
                            dd($mailBody,$timeSlots,$data,$timeSlot);
                          @endphp --}}
                                {{-- <td style="text-align: center;padding: 5px;">{{ $data['hourlyCount'] }}</td> --}}
                            {{-- @endforeach --}}
                            @foreach ($data['hourlyCount'] as $count)
                            <td style="text-align: center;padding: 5px;">{{ $count }}</td>
                        @endforeach
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 5px;">--No Records--</td>
                    </tr>
                @endif
            </tbody>
        </table>
        <br>
        @include('emails.emailFooter')
    </div>
</body>

</html>
