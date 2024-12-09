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

        {{-- <h4>
            <p>Hello Team, </p>
        </h4> --}}

        <p>Hello Team - Find below the Resolv utilization report for {{$yesterday->format('m/d/Y')}}</p>
        {{-- <p>Please find below the daily update for the production inventory : 06/07/2024</p> --}}
       
        <table class="table" border="1" style="border-collapse: collapse">
            <thead>
                <tr>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Project</th>
                    {{-- <th style="text-align: left;padding: 5px;">Chats</th> --}}
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Inventory Uploaded</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Total Users - AR</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Logged Resolv - AR</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Production Users - AR</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">AR</th>
                    {{-- <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Total QA</th> --}}
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Logged Resolv - QA</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Production - QA</th>
                    <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">QA</th>
                    {{-- <th style="text-align: left;padding: 5px;">Balance</th> --}}
                </tr>
            </thead>
            <tbody>

                @if (isset($mailBody) && count($mailBody) > 0)
                    @foreach ($mailBody as $data)
                        <tr>
                            <td style="text-align: center;padding: 5px;">{{ $data['project'] }}</td>
                            <td style="text-align: center;padding: 5px;">{{ $data['Chats'] == 0 ? 'No' : 'Yes' }}</td>
                            <td style="text-align: center;padding: 5px;">{{ $data['total_ar']}}</td>
                            {{-- <td class="total-ar"  style="text-align: center;padding: 5px;"></td> --}}
                            <td style="text-align: center;padding: 5px;">{{ $data['logged_resolv_ar']}}</td>
                            <td class="logged_resolv_ar" style="text-align: center;padding: 5px;"></td>
                            <td style="text-align: center;padding: 5px;">{{$data['prodcution_ar']}}</td>
                            <td style="text-align: center;padding: 5px;">{{ $data['Coder'] == 0 ? 'No Activity' : $data['Coder']}}</td>
                             <td style="text-align: center;padding: 5px;">{{ $data['logged_resolv_qa']}}</td>
                             {{-- <td class="logged_resolv_qa" style="text-align: center;padding: 5px;"></td> --}}
                            <td style="text-align: center;padding: 5px;">{{$data['prodcution_qa']}}</td>
                            <td style="text-align: center;padding: 5px;">{{ $data['QA'] == 0 ? 'No Activity' : $data['QA']}}</td>
                            
                        </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="9" style="text-align: center; padding: 5px;">--No Records--</td>
                </tr>
                @endif
            </tbody>
        </table>
        <br>
        @include('emails.emailFooter')
    </div>
</body>

</html>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
{{-- <script>
    $(document).ready(function() {
      
        var table = $("#project_utilization_table").DataTable({
            processing: false,
            ordering: false,
            clientSide: false,
            lengthChange: false,
            searching: false,           
        });
        function processAllRows() {
            var rows = table.rows().nodes(); // Fetch all rows across all pages
            $(rows).each(function() {
                var row = $(this);
                var rowProjectId = row.data('project-id');
                var projectId = @json($projectIds);
                var yesterDayStartDate = @json($yesterDayStartDate);
                var yesterDayEndDate = @json($yesterDayEndDate);

                if (projectId) {
                    fetch(`project-ar-qa-counts/` + projectId +
                            `/${yesterDayStartDate}/${yesterDayEndDate}/${rowProjectId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.total_ar !== undefined) {
                                row.find(".total-ar").text(data.total_ar);
                                row.find(".logged_resolv_ar").text(data.logged_resolv_ar);
                                row.find(".logged_resolv_qa").text(data.logged_resolv_qa);                              
                            }
                        })
                        .catch(error => console.error("Error fetching AR/QA counts:", error));
                }
            });
        }

      
        table.on('draw', processAllRows);

   
        processAllRows();
    });
  
</script> --}}
