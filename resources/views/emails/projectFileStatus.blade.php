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

            <p>Hi All, </p>

            <p>{{$fileStatus}}</p>
            @if (!empty($Inventory_wound_data))
                <table class="table" border="1" style="border-collapse: collapse">
                    <thead>
                        <tr>
                            <th style="text-align: left;padding: 5px;">Ticket Number</th>
                            <th style="text-align: left;padding: 5px;">Patient Name</th>
                            <th style="text-align: left;padding: 5px;">Patient Id</th>
                            <th style="text-align: left;padding: 5px;">DOB</th>
                            <th style="text-align: left;padding: 5px;">DOS</th>
                            <th style="text-align: left;padding: 5px;">Coders E/M ICD 10</th>
                        </tr>
                    </thead>
                    <tbody>
                            @foreach ($Inventory_wound_data as $data)
                            <tr>
                                <td style="text-align: left;padding: 5px;">{{$data['ticket_number']}}</td>
                                <td style="text-align: left;padding: 5px;">{{$data['patient_name']}}</td>
                                <td style="text-align: left;padding: 5px;">{{$data['patient_id']}}</td>
                                <td style="text-align: left;padding: 5px;">{{date('m/d/Y',strtotime($data['dob']))}}</td>
                                <td style="text-align: left;padding: 5px;">{{date('m/d/Y',strtotime($data['dos']))}}</td>
                                <td style="text-align: left;padding: 5px;">{{$data['coders_em_icd_10']}}</td>
                            </tr>
                            @endforeach
                    </tbody>
                </table>
            @endif
            <br>
           @include('emails.emailFooter')
    </div>
</body>

</html>
