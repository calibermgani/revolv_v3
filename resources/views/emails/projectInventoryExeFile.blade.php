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
                    @if ($mailBody['duplicateCount'] > 0)
                        <th style="text-align: left;padding: 5px;">Duplicate Count</th>
                    @endif
                    @if ($mailBody['assignedCount'] > 0)
                        <th style="text-align: left;padding: 5px;">Default Assigned Count</th>
                    @endif
                    @if ($mailBody['unAssignedCount'] > 0)
                        <th style="text-align: left;padding: 5px;">UnAssigned Count</th>
                    @endif
                    <th style="text-align: left;padding: 5px;">Inventory Upload Date & Time</th>
                </tr>
            </thead>
            <tbody>
                @if (isset($mailBody) && count($mailBody) > 0)
                    <tr>
                        <td style="text-align: left;padding: 5px;">{{ $mailBody['project'] }}</td>
                        <td style="text-align: left;padding: 5px;">
                            {{ $mailBody['currentCount'] == 0 ? 0 : $mailBody['currentCount'] }}</td>
                        @if ($mailBody['duplicateCount'] > 0)
                            <td>{{ $mailBody['duplicateCount'] == 0 ? 0 : $mailBody['duplicateCount'] }}</td>
                        @endif
                        @if ($mailBody['assignedCount'] > 0)
                            <td>{{ $mailBody['assignedCount'] == 0 ? 0 : $mailBody['assignedCount'] }}</td>
                        @endif
                        @if ($mailBody['unAssignedCount'] > 0)
                            <td>{{ $mailBody['unAssignedCount'] == 0 ? 0 : $mailBody['unAssignedCount'] }}</td>
                        @endif
                        <td style="text-align: left;padding: 5px;">{{ Carbon\Carbon::now()->format('m/d/Y g:i A') }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        <p>Thank you.</p>
        <br>
        @include('emails.emailFooter')
    </div>
</body>

</html>
