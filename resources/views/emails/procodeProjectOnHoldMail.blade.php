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

        <p>Please find below the on-hold records.</p>
        
        <table class="table" border="1" style="border-collapse: collapse">
            <thead>
                <tr>
                    <th style="text-align: left;padding: 5px;">Project</th>
                     <th style="text-align: left;padding: 5px;">Hold</th>
                 </tr>
            </thead>
            <tbody>

                @if (isset($clientIds) && count($clientIds) > 0)
              
                    @foreach ($clientIds as $data)
                
                        <tr>
                            <td style="text-align: left;padding: 5px;">{{ $mailBody[$data]['project'] }}</td>
                            <td style="text-align: left;padding: 5px;">{{ $mailBody[$data]['Hold'] == 0 ? 'No Activity' : $mailBody[$data]['Hold']}}</td>
                          </tr>
                    @endforeach
                @endif

            </tbody>
        </table>
        <p>Thank you for your attention.</p>
        <br>
        @include('emails.emailFooter')
    </div>
</body>

</html>
