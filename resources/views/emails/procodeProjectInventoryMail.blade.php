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

        <h4>
            <p>Dear Team, </p>
        </h4>

        <p>I am pleased to inform you that the inventory has been successfully uploaded.</p>
        
        <table class="table" border="1" style="border-collapse: collapse">
            <thead>
                <tr>
                    <th style="text-align: left;padding: 5px;">Project</th>
                     <th style="text-align: left;padding: 5px;">Count</th>
                 </tr>
            </thead>
            <tbody>

                @if (isset($mailBody) && count($mailBody) > 0)
              
                    @foreach ($mailBody as $key => $data)
           
                        <tr>
                            <td style="text-align: left;padding: 5px;">{{ $data['project'] }}</td>
                            <td style="text-align: left;padding: 5px;">{{ $data['currentCount'] == 0 ? 0 : $data['currentCount']}}</td>
                          </tr>
                    @endforeach
                @endif

            </tbody>
        </table>
        <p>Thank you.</p>
        <br>
        @include('emails.emailFooter')
    </div>
</body>

</html>
