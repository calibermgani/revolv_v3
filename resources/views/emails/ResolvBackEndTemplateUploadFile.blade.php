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
            <p>Hello All, </p>
        </h4>

        <p>I am pleased to inform you that the backend upload template {{$mailBody['file_name']}} has been successfully uploaded for the {{$mailBody['project']}} Project.</p>

      
        <p>Thank you.</p>
        <br>
        @include('emails.emailFooter')
    </div>
</body>

</html>
