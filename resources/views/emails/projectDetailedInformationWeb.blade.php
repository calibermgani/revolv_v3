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
    {{-- <div class="card card-custom custom-card">
        <div class="card-body p-0">
            <div class="card-header border-0 px-4">
                <div class="row">
                    <div class="col-md-6">
                        <span class="project_header" style="margin-left: 4px !important;">User Detailed Information</span>
                    </div>
                </div>
                <div class="card-body py-0 px-7"> --}}
                    <div class="table-responsive pb-2">


                        <table class="table table-separate table-head-custom no-footer dtr-column" id="prj_detail_inf">
                            <thead>
                                <tr>
                                    <th
                                        style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                        User Name</th>
                                    @foreach ($headers as $header)
                                        <th
                                            style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                            {{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>

                                @if (isset($BodyDetails) && count($BodyDetails) > 0)
                                    @foreach ($BodyDetails as $data)
                                        <tr>
                                            <td style="text-align: center; padding: 5px;">
                                                {{ $data['user'] }}
                                            </td>
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

                    </div>
                {{-- </div>
            </div>
        </div> --}}
</body>

</html>
