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

        <p>Please find below the daily update for the production inventory : {{$yesterday->format('m/d/Y')}}</p>
        {{-- <p>Please find below the daily update for the production inventory : 06/07/2024</p> --}}
       
        <table class="table" border="1" style="border-collapse: collapse">
            <thead>
                <tr>
                    <th style="text-align: left;padding: 5px;">Project</th>
                    {{-- <th style="text-align: left;padding: 5px;">Chats</th> --}}
                    <th style="text-align: left;padding: 5px;">Inventory Uploaded</th>
                    <th style="text-align: left;padding: 5px;">Coder</th>
                    <th style="text-align: left;padding: 5px;">QA</th>
                    {{-- <th style="text-align: left;padding: 5px;">Balance</th> --}}
                </tr>
            </thead>
            <tbody>

                @if (isset($mailBody) && count($mailBody) > 0)
                    @foreach ($mailBody as $data)
                        <tr>
                            <td style="text-align: left;padding: 5px;">{{ $data['project'] }}</td>
                            <td style="text-align: left;padding: 5px;">{{ $data['Chats'] == 0 ? 'No' : 'Yes' }}</td>
                            <td style="text-align: left;padding: 5px;">{{ $data['Coder'] == 0 ? 'No Activity' : $data['Coder']}}</td>
                            <td style="text-align: left;padding: 5px;">{{ $data['QA'] == 0 ? 'No Activity' : $data['QA']}}</td>
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
        <p>Thank you for your attention.</p>
        <br>
        @include('emails.emailFooter')
    </div>
</body>

</html>
