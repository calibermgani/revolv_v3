@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card">
        <div class="card card-custom custom-top-border">
            <div class="card-body mr-8 ml-12" id="filter_section" style="display:none;border:1px solid #F3F3F3">
                                   
              
                         {!! Form::open([
                            'url' =>
                            url('projects_assigned/' . $clientName . '/' . $subProjectName) .
                                            '?parent=' .
                                            request()->parent .
                                            '&child=' .
                                            request()->child,
                            'class' => 'form',
                            'id' => 'formSearch',
                            'enctype' => 'multipart/form-data',
                        ]) !!}
                        @csrf
                 
                            <div class="row mr-0 ml-0">
                             
                            <div class="col-md-3">
                                <div class="form-group row row_mar_bm">
                                   
                                    <div class="col-md-10">
                                     
                                                {!! Form::foreach('request_date',isset($yesterday) && !empty($yesterday) ? $yesterday : null, [
                                                    'class' => 'form-control ' . $columnName . ' white-smoke pop-non-edt-val',
                                                    'autocomplete' => 'none',
                                                    'style' => 'cursor:pointer',
                                                    'rows' => 3,
                                                    'id' => $columnName,
                                                ]) !!}
                                          
                                    </div>
                                
                                
                                </div>
                            </div>
                      
                            </div>
                      
                    <div class="form-footer" style="justify-content: center !important">                                      
                        <button type="submit" class="btn  btn-white-black font-weight-bold"
                            id="filter_search">Search</button> &nbsp;&nbsp; <button class="btn btn-light-danger" id="filter_clear" tabindex="10" type="button">
                                <span>
                                    <span>Clear</span>
                                </span>
                            </button>                        
                    </div>
             
            </div>
            <div class="card-body py-0 px-7">
                <div class="table-responsive pb-2">
                    <p>Resolv utilization report for {{ $yesterday->format('m/d/Y') }}</p>

                    <table class="table table-separate table-head-custom no-footer dtr-column clients_list_filter"
                        border="1" style="border-collapse: collapse">
                        <thead>
                            <tr>
                                <th
                                    style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                    Project</th>
                                {{-- <th style="text-align: left;padding: 5px;">Chats</th> --}}
                                <th
                                    style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                    Inventory Uploaded</th>
                                <th
                                    style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                    Total Users - AR</th>
                                <th
                                    style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                    Logged Resolv - AR</th>
                                <th
                                    style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                    Production Users - AR</th>
                                <th
                                    style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                    AR</th>
                                {{-- <th style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">Total QA</th> --}}
                                <th
                                    style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                    Logged Resolv - QA</th>
                                <th
                                    style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                    Production - QA</th>
                                <th
                                    style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                    QA</th>
                                {{-- <th style="text-align: left;padding: 5px;">Balance</th> --}}
                            </tr>
                        </thead>
                        <tbody>

                            @if (isset($mailBody) && count($mailBody) > 0)
                                @foreach ($mailBody as $data)
                                    <tr>
                                        <td style="text-align: center;padding: 5px;">{{ $data['project'] }}</td>
                                        <td style="text-align: center;padding: 5px;">
                                            {{ $data['Chats'] == 0 ? 'No' : 'Yes' }}</td>
                                        <td style="text-align: center;padding: 5px;">{{ $data['total_ar'] }}</td>
                                        <td style="text-align: center;padding: 5px;">{{ $data['logged_resolv_ar'] }}</td>
                                        <td style="text-align: center;padding: 5px;">{{ $data['prodcution_ar'] }}</td>
                                        <td style="text-align: center;padding: 5px;">
                                            {{ $data['Coder'] == 0 ? 'No Activity' : $data['Coder'] }}</td>
                                        {{-- <td style="text-align: center;padding: 5px;">{{$data['total_qa']}}</td> --}}
                                        <td style="text-align: center;padding: 5px;">{{ $data['logged_resolv_qa'] }}</td>
                                        <td style="text-align: center;padding: 5px;">{{ $data['prodcution_qa'] }}</td>
                                        <td style="text-align: center;padding: 5px;">
                                            {{ $data['QA'] == 0 ? 'No Activity' : $data['QA'] }}</td>
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

                </div>
            </div>
        </div>
    </div>
@endsection
