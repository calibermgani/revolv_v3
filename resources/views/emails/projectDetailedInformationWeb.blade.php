
    <div class="card card-custom custom-card">
        <div class="card-body p-0">
            <div class="card-header border-0 px-4">
                <div class="row">
                    <div class="col-md-6">
                        <span class="project_header" style="margin-left: 4px !important;">User Detailed Information</span>
                    </div>
                </div>
                <div class="card-body py-0 px-7">
                    <div class="table-responsive pt-5">


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
                </div>
            </div>
        </div>

    <style>
        .dropdown-item.active {
            color: #ffffff;
            text-decoration: none;
            background-color: #888a91;
        }

        .modal-left .modal-dialog {
            margin-top: 90px;
            margin-left: 320px;
            margin-right: auto;
        }

        .modal-left .modal-content {
            border-radius: 5px;
        }

        .modal-right .modal-dialog {
            margin-left: auto;
            margin-right: 220px;
            transition: margin 5s ease-in-out;
        }

        .modal-right .modal-content {
            border-radius: 5px;
        }

        nav {
            float: right !important;
        }
    </style>
    @push('view.scripts')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script>
            $(document).ready(function() {
                var table = $("#client_assigned_list").DataTable({
                    processing: true,
                    ordering: true,
                    clientSide: true,
                    lengthChange: false,
                });
            })
        </script>
