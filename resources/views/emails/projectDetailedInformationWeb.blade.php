<!DOCTYPE html>
<html>

<head>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
    
    #loading {
        position: fixed;
        display: block;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        text-align: center;
        opacity: 0.7;
        background-color: #fff;
        z-index: 99;
    }
    #loading-text {
        top: 166px;
        left: 50px;
        position: relative;
    }
    #loading-image {
    position: absolute;
    top: 100px;
    /* left: 340px; */
    z-index: 100;
    }
</style>

<body>
 
                    <div class="table-responsive" style="margin-top: 5rem;margin-left: 5rem;margin-right: 5rem;">


                        <table class="table" border="1" style="border-collapse: collapse">
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
                                                {{  $data['user'] != null ? $data['user'].' - '.App\Http\Helper\Admin\Helpers::getUserNameByEmpId($data['user']) : '--' }}
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
                    <div id="loading">
                        <img id="loading-image" src="{{ URL::asset('/assets/media/loader/loader.gif') }}" alt="Loading..." />
                        <p id="loading-text">Please Wait.........</p>
                      </div>
           
</body>
<script>
      $('#loading').show();
</script>
</html>
