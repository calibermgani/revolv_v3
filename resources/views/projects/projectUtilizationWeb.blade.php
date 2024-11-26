@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card">
        <div class="card card-custom custom-top-border">
            <div class="card-body mr-8 ml-12" id="filter_section">
                {!! Form::open([
                    'url' => url('projects/project_work_web') . '?parent=' . request()->parent . '&child=' . request()->child,
                    ,
                    'class' => 'form',
                    'id' => 'formSearch',
                    'enctype' => 'multipart/form-data',
                ]) !!}
                @csrf

                <div class="row mr-0 ml-0">
                    <div class="col-md-3">
                        <div class="form-group row row_mar_bm">
                            <div class="col-md-10">
                                {!! Form::date('request_date', isset($yesterday) && !empty($yesterday) ? $yesterday : null, [
                                    'class' => 'form-control white-smoke pop-non-edt-val',
                                    'autocomplete' => 'none',
                                    'style' => 'cursor:pointer',
                                    'rows' => 3,
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group row">

                            <div class="col-md-10">
                                <button type="submit" class="btn  btn-white-black font-weight-bold"
                                    id="filter_search">Search</button>
                                &nbsp;&nbsp; <button class="btn btn-light-danger" id="filter_clear" tabindex="10"
                                    type="button">
                                    <span>
                                        <span>Clear</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body py-0 px-7">
                <div class="table-responsive pb-2">

                    <table class="table table-separate table-head-custom no-footer dtr-column clients_list_filter"
                        border="1" style="border-collapse: collapse" id="project_utilization_table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Inventory Uploaded</th>
                                <th>Total Users - AR</th>
                                <th>Logged Resolv - AR</th>
                                <th>Production Users - AR</th>
                                <th>AR</th>
                                <th>Logged Resolv - QA</th>
                                <th>Production - QA</th>
                                <th>QA</th>
                            </tr>
                        </thead>
                        <tbody>

                            @if (isset($mailBody) && count($mailBody) > 0)
                                @foreach ($mailBody as $data)
                                    <tr>
                                        <td>{{ $data['project'] }}</td>
                                        <td>{{ $data['Chats'] == 0 ? 'No' : 'Yes' }}</td>
                                        <td>{{ $data['total_ar'] }}</td>
                                        <td>{{ $data['logged_resolv_ar'] }}</td>
                                        <td>{{ $data['prodcution_ar'] }}</td>
                                        <td>{{ $data['Coder'] == 0 ? 'No Activity' : $data['Coder'] }}</td>
                                        <td>{{ $data['logged_resolv_qa'] }}</td>
                                        <td>{{ $data['prodcution_qa'] }}</td>
                                        <td>{{ $data['QA'] == 0 ? 'No Activity' : $data['QA'] }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 5px;">--No Records--</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('view.scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            var table = $("#project_utilization_table").DataTable({
                processing: true,
                ordering: true,
                clientSide: true,
                lengthChange: false,
                searching: true,
            });
        });
    </script>
@endpush
