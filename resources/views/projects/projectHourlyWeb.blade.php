@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card">
        <div class="card card-custom custom-top-border">
            <div class="card-body mr-8 ml-12" id="filter_section">
                {!! Form::open([
                    'url' => url('projects/project_hourly_web'),
                    'class' => 'form',
                    'id' => 'formSearch',
                    'enctype' => 'multipart/form-data',
                ]) !!}
                @csrf

                <div class="row mr-0 ml-0">
                    <div class="col-md-3">
                        <div class="form-group row row_mar_bm">
                            <div class="col-md-10">
                                <input type="datetime-local" id="datetime" name="datetime" class="form-control" value="{{ old('datetime', now()->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group row row_mar_bm">
                            <div class="col-md-10">
                                <input type="text" id="end_time" name="end_time"
                                class="white-smoke form-control end_time" value="" autocomplete="nope">
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
                    @php
                    $today1 = \Carbon\Carbon::now(); // 17:00 is 5 PM in 24-hour format
                    $formattedDate = $today1->format('m/d/Y h:i A');
                    @endphp
            
                    <table class="table table-separate table-head-custom no-footer dtr-column clients_list_filter"
                        border="1" style="border-collapse: collapse">
                        <thead>
                            <tr>
                                <th
                                    style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                    Project</th>
                                @foreach ($headers as $timeSlot)
                                    <th
                                        style="text-align: center;padding: 5px;background-color:#2f75b5;color:#ffffff;font-weight: 100;border-color:black;">
                                        {{ $timeSlot }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>

                            @if (isset($mailBody) && count($mailBody) > 0)
                                @foreach ($mailBody as $data)
                                    <tr>
                                        <td style="text-align: center; padding: 5px;">
                                            <a
                                                href="http://resolv-aims.com/projects/project_detailed_information?project_id={{ App\Http\Helper\Admin\Helpers::encodeAndDecodeID($data['project_id'], 'encode') }}&subproject_id={{ App\Http\Helper\Admin\Helpers::encodeAndDecodeID($data['subproject_id'], 'encode') }}&requested_date={{ $formattedDate }}">
                                                {{ $data['project'] }}
                                            </a>
                                        </td>
                                        @foreach ($data['hourlyCount'] as $count)
                                            <td style="text-align: center;padding: 5px;">{{ $count }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="14" style="text-align: center; padding: 5px;">--No Records--</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
@endsection
