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
 <p style="margin-top: 5rem;margin-left: 5.5rem;">{{$title}}</p>
                    <div class="table-responsive" style="margin-left: 5rem;margin-right: 5rem;">


                        <table class="table" border="1" style="border-collapse: collapse">
                            <thead>
                                <tr>
                                    <th
                                        style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;" width="8%">
                                        User Name</th>
                                    @foreach ($headers as $header)
                                        <th
                                            style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                            {{ $header }}</th>
                                    @endforeach
                                     <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;" width="8%">Actual Target</th>
                                     <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;" width="8%">Achieved Target</th>
                                     <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;" width="8%">%</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if (isset($BodyDetails) && count($BodyDetails) > 0)
                                    @foreach ($BodyDetails as $data)
                                        <tr>
                                            <td style="text-align: center; padding: 5px;" width="8%">
                                                {{  $data['user'] != null ? $data['user'].' - '.App\Http\Helper\Admin\Helpers::getUserNameByEmpId($data['user']) : '--' }}
                                            </td>
                                            @foreach ($data['hourlyCount'] as $count)
                                                <td style="text-align: center;padding: 5px;">{{ $count }}</td>
                                            @endforeach
                                            <td style="text-align: center;padding: 5px;" width="8%">{{ $data['slaTarget'] }}</td>
                                           <td style="text-align: center;padding: 5px;" width="8%">{{ $data['reachedTarget'] }}</td>
                                           <td style="{{ $data['achievedPercentage'] >= 95 ? 'color: green;' : 'color: red;' }} text-align: center; padding: 5px;" width="8%">
                                            {{ $data['achievedPercentage'] . '%' }}
                                        </td>
                                        
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="14" style="text-align: center; padding: 5px;">--No Records--</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        <br>

                    </div>
                    
           
</body>

</html>
