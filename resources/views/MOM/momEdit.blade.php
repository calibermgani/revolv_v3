@extends('layouts.app3')
@section('content')
    {!! Form::open([
        'url' => url('mom/mom_update') . '?parent=' . request()->parent . '&child=' . request()->child,
        'method' => 'POST',
        'id' => 'momEdit',
        'enctype' => 'multipart/form-data',
    ]) !!}
    @csrf
    <div class="card card-custom custom-card">
        <div class="card-body pt-4 pb-0 px-2">
            <div class="row mb-6 ml-1">
                <div class="col-sm-12 col-md-6">
                    <a class="project_header"
                        href="{{ url('mom/mom_dashboard') }}?parent={{ request()->parent }}&child={{ request()->child }}">
                        <span class="svg-icon svg-icon-primary svg-icon-lg mr-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="16" fill="currentColor"
                                class="bi bi-arrow-left project_header_row" viewBox="0 0 16 16"
                                style="width: 1.05rem !important;color: #000000 !important;">
                                <path fill-rule="evenodd"
                                    d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                            </svg>
                        </span>
                        MOM Edit</a>
                </div>
                <div class="col-sm-12 col-md-6 pl-10" style="text-align: right">
                    <span>   <i class="fa far fa-trash text-danger btn-light-danger" id="delete_submit"
                        style="cursor:pointer"></i></span>

                </div>
            </div>
            <div id="mom_div">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row form-group pl-5">
                            <div class="col-md-3">
                                <label class="required">Meeting Title</label>
                                <div class="form-group mb-1">
                                    {!! Form::text('meeting_title', $momParent->meeting_title ?? null, [
                                        'class' => 'white-smoke form-control meeting_title',
                                        'id' => 'meeting_title',
                                        'autocomplete' => 'nope',
                                    ]) !!}
                                    <input type="hidden" name="parent_id" id="parent_id" value={{ $momParent->id }}>
                                    <input type="hidden" name="meeting_date" value={{ $momParent->meeting_date }}>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="required">Attendies</label>
                                <div class="form-group mb-1">
                                    @php
                                        $attendies = App\Http\Helper\Admin\Helpers::getMomAttendiesList();
                                    @endphp
                                    {!! Form::select('meeting_attendies[]', $attendies, explode(',', $momParent->meeting_attendies), [
                                        'class' => 'form-control white-smoke meeting_attendies select2',
                                        'id' => 'meeting_attendies',
                                        'multiple' => 'multiple',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="required">TimeZone</label>
                                <div class="form-group mb-1">
                                    {!! Form::select('time_zone', $timezones, $momParent->time_zone, [
                                        'class' => 'form-control white-smoke time_zone select2',
                                        'id' => 'time_zone',
                                    ]) !!}

                                </div>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="row form-group pl-5">
                            <div class="col-md-3">
                                <label class="required">Start Time</label>
                                <div class="form-group mb-1">
                                    <input type="text" id="start_time" name="start_time"
                                        class="white-smoke form-control start_time" value={{ $momParent->start_time }}>

                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="required">End Time</label>
                                <div class="form-group mb-1">
                                    <input type="text" id="end_time" name="end_time"
                                        class="white-smoke form-control end_time" value={{ $momParent->end_time }}>
                                </div>
                            </div>
                            <div class="col-md-3 options_div">
                                <label class="required">ETA</label>
                                <div class="form-group mb-1">
                                    <input type="date" id="eta" name="eta" class="white-smoke form-control eta"
                                        value={{ $momParent->eta }}>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row form-group pl-5 pr-5">
                    <div class="col-md-12">
                        <label>Description</label>
                        <div class="white-smoke">
                            <div id="kt_quil_1" style="height: 100px"></div>
                            {!! Form::hidden('req_description', trim(strip_tags($momParent->req_description)) ?? null, [
                                'class' => 'form-control req_description',
                                'id' => 'req_description',
                                'autocomplete' => 'nope',
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="row ml-4">
                    <div style="width:91%">
                        <table class="table table-separate table-head-custom no-footer dtr-column" id="addrow">
                            <thead>
                                <tr>
                                    {{-- <th style="background-color: #0969C3 !important;width: 3%">S.No</th> --}}
                                    <th style="background-color: #139AB3 !important">Topics</th>
                                    <th style="background-color: #139AB3 !important">Description</th>
                                    <th style="background-color: #139AB3 !important">Action Item</th>
                                    <th style="background-color: #139AB3 !important">Responsible party</th>
                                    <th style="background-color: #139AB3 !important">ETA</th>
                                    <th style="background-color: #139AB3 !important">+/-</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (isset($momChild) && $momChild->isNotEmpty())
                                    <input type="hidden" id="row_count" value="{{ $momChild->count() - 1 }}">
                                    @foreach ($momChild as $key => $data)
                                        @if ($loop->first)
                                            <tr id="form_append_{{ $key }}" class="clonetr">
                                                {{-- <td>{{ $loop->iteration }}</td> --}}
                                                <input type="hidden" name="mc_id[]" id="mc_id{{ $key }}"
                                                    value={{ $data->id ?? '' }}>
                                                <td>
                                                    <textarea rows="3" id="topics{{ $key }}" name="topics[]" class="form-control topics">{{ $data->topics ?? '' }}</textarea>
                                                </td>
                                                <td>
                                                    <textarea rows="3" id="topic_description{{ $key }}" name="topic_description[]"
                                                        class="form-control topic_description">{{ $data->topic_description ?? '' }}</textarea>
                                                </td>
                                                <td>
                                                    <textarea rows="3" id="action_item{{ $key }}" name="action_item[]" class="form-control action_item">{{ $data->action_item ?? '' }}</textarea>
                                                </td>
                                                <td>
                                                    <textarea rows="3" id="responsible_party{{ $key }}" name="responsible_party[]"
                                                        class="form-control responsible_party">{{ $data->responsible_party ?? '' }}</textarea>
                                                </td>
                                                <td><input type="date" id="topic_eta{{ $key }}"
                                                        name="topic_eta[]" class="form-control topic_eta"
                                                        value="{{ $data->topic_eta ?? '' }}"></td>
                                                <td><i class="fa fas fa-minus icon-circle-remove ml-1 deleteButton"></i>
                                                </td>
                                                {{-- <td class="action_btn">
                                                    @if ($key == $momChild->count() - 1)
                                                        <i class="fa fas fa-plus icon-circle2 ml-1 add_new_btn"
                                                            id="add_new_btn_{{ $key }}"></i>
                                                    @else
                                                        <i class="fa fas fa-minus icon-circle-remove ml-1 remove_btn"
                                                            id="{{ $key }}"></i>
                                                    @endif
                                                </td> --}}

                                            </tr>
                                        @else
                                            <tr id="form_append_{{ $key }}" class="clonetr">
                                                {{-- <td>{{ $loop->iteration }}</td> --}}
                                                <input type="hidden" name="mc_id[]" id="mc_id{{ $key }}"
                                                    value={{ $data->id ?? '' }}>
                                                <td>
                                                    <textarea rows="3" id="topics{{ $key }}" name="topics[]" class="form-control topics">{{ $data->topics ?? '' }}</textarea>
                                                </td>
                                                <td>
                                                    <textarea rows="3" id="topic_description{{ $key }}" name="topic_description[]"
                                                        class="form-control topic_description">{{ $data->topic_description ?? '' }}</textarea>
                                                </td>
                                                <td>
                                                    <textarea rows="3" id="action_item{{ $key }}" name="action_item[]" class="form-control action_item">{{ $data->action_item ?? '' }}</textarea>
                                                </td>
                                                <td>
                                                    <textarea rows="3" id="responsible_party{{ $key }}" name="responsible_party[]"
                                                        class="form-control responsible_party">{{ $data->responsible_party ?? '' }}</textarea>
                                                </td>
                                                <td><input type="date" id="topic_eta" name="topic_eta[]"
                                                        class="form-control topic_eta"
                                                        value="{{ $data->topic_eta ?? '' }}"></td>
                                                <td><i class="fa fas fa-minus icon-circle-remove ml-1 deleteButton"></i>
                                                </td>
                                                {{-- <td class="action_btn">
                                                    @if ($key == $momChild->count() - 1)
                                                        <i class="fa fas fa-plus icon-circle2 ml-1 add_new_btn"
                                                            id="add_new_btn_1"></i>
                                                    @else
                                                        <i class="fa fas fa-minus icon-circle-remove ml-1 remove_btn"
                                                            id="{{ $key }}"></i>
                                                    @endif
                                                </td> --}}
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    &nbsp;&nbsp;&nbsp;&nbsp;<div class="mt-1"><input type="button" id="add_new_btn1"
                            class="addButton" value="+ Add New" /></div>
                </div>
                <div class="form-footer mr-5">

                    {{-- <i class="fa far fa-trash text-danger btn-light-danger mr-5" id="delete_submit" style="cursor:pointer"></i>&nbsp;&nbsp; --}}
                    {{-- <button class="btn btn-light-danger" tabindex="10" type="button" id="delete_submit">
                        <span>
                            <span>Delete</span>
                        </span>
                    </button>&nbsp;&nbsp; --}}
                    <button class="btn btn-light-danger" id="clear_submit" tabindex="10" type="button"
                        onClick="window.location.reload();">
                        <span>
                            <span>Clear</span>
                        </span>
                    </button>&nbsp;&nbsp;
                    <button type="submit" class="btn btn-white-black font-weight-bold" id="mom_update">Submit</button>

                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@endsection
<style>
    .ql-formats .ql-image {
        display: none !important;
    }

    .ql-code-block {
        display: none !important;
    }

    td .form-control {
        width: 100% !important;
    }
</style>
@push('view.scripts')
    <script>
        var desVal = @json($reqDescription);
        $('#kt_quil_1').append(desVal);
        var counter = $('#row_count').val();

        function addNewRow() {
            console.log(counter, 'counter');
            counter++;
            var plusButton = '<i class="fa fa-plus icon-circle2 ml-1 add_new_btn" id="' + 'add_new_btn_' + counter +
                '"></i>';
            var removeButton = '<i class="fa fas fa-minus icon-circle-remove ml-1 remove_btn" id="' + (counter - 1) +
                '"></i>';

            var newRow = [
                counter,
                '<textarea rows="3" id="topics' + counter +
                '" name="topics[]" class="form-control topics"></textarea>',
                '<textarea rows="3" id="topic_description' + counter +
                '" name="topic_description[]" class="form-control topic_description"></textarea>',
                '<textarea rows="3" id="action_item' + counter +
                '" name="action_item[]" class="form-control action_item"></textarea>',
                '<textarea rows="3" id="responsible_party' + counter +
                '" name="responsible_party[]" class="form-control responsible_party"></textarea>',
                '<input type="date" id="topic_eta' + counter +
                '" name="topic_eta[]" class="form-control topic_eta">',
                plusButton
            ];

            var newRowNode = table.row.add(newRow).draw(false).node();
            $(newRowNode).attr('id', 'form_append_' + counter);
            elementToRemove = 'add_new_btn_' + (counter - 1);
            $('#' + elementToRemove).remove();
            $('#form_append_' + (counter - 1) + ' td:last-child').html(removeButton);
        }

        const table = $("#addrow").DataTable({
            processing: true,
            ordering: true,
            clientSide: true,
            lengthChange: false,
            searching: false,
            "info": false,
            paging: false,
        });
        $(".addButton").click(function() {
            let cloneRow = $("#addrow tbody tr.clonetr:last").clone(true);
            cloneRow.find('input[type="text"], input[type="hidden"],input[type="date"], textarea').val('');
            $("#addrow tbody").prepend(cloneRow);
            //  $("#addrow tbody tr.clonetr:last td:last").html('<i class="fa fas fa-minus icon-circle-remove ml-1 deleteButton" ></i>');

        });

        $(document).on('click', '.deleteButton', function() {
            if ($('#addrow tbody tr.clonetr').length > 1) {
                $(this).closest("tr").remove();
            }
        });
        // $(document).on('click', '.remove_btn', function() {
        //     var button_id = $(this).attr("id");
        //     $('#form_append_' + button_id + '').remove();
        // });
        // $(document).on('click', '.add_new_btn', function() {
        //     addNewRow();
        // });
        $(document).on('click', '#mom_update', function(e) {
            e.preventDefault();
            var meetingTitle = $('#meeting_title');
            var meetingAttendies = $('#meeting_attendies');
            var timeZone = $('#time_zone');
            var startTimeVal = $('#start_time');
            var endTimeVal = $('#end_time');
            var etaVal = $('#eta');
            if (meetingTitle.val() == '' || meetingAttendies.val() == '' || timeZone.val() == '' || startTimeVal
                .val() == '' || endTimeVal.val() == '' || etaVal.val() == '') {
                if (meetingTitle.val() == '') {
                    meetingTitle.css('border-color', 'red');
                } else {
                    meetingTitle.css('border-color', '');
                }
                console.log(meetingTitle, 'meetingTitle');
                if (meetingAttendies.val() == '') {
                    meetingAttendies.next('.select2').find(".select2-selection").css('border-color', 'red');
                } else {
                    meetingAttendies.next('.select2').find(".select2-selection").css('border-color', '');
                }
                if (timeZone.val() == '') {
                    timeZone.next('.select2').find(".select2-selection").css('border-color', 'red');
                } else {
                    timeZone.next('.select2').find(".select2-selection").css('border-color', '');
                }
                if (startTimeVal.val() == '') {
                    startTimeVal.css('border-color', 'red');
                } else {
                    startTimeVal.css('border-color', '');
                }
                if (endTimeVal.val() == '') {
                    endTimeVal.css('border-color', 'red');
                } else {
                    endTimeVal.css('border-color', '');
                }
                if (etaVal.val() == '') {
                    etaVal.css('border-color', 'red');
                } else {
                    etaVal.css('border-color', '');
                }

                return false;
            } else {
                document.querySelector('#momEdit').submit();
            }
        });
        $(document).on('click', '#delete_submit', function() {
            var id = $('#parent_id').val();
            swal.fire({
                text: "Are you sure you want to delete?",
                icon: "success",
                buttonsStyling: false,
                showCancelButton: true,
                confirmButtonText: "Yes",
                cancelButtonText: "No",
                reverseButtons: true,
                customClass: {
                    confirmButton: "btn font-weight-bold btn-white-black",
                    cancelButton: "btn font-weight-bold  btn-light-danger",
                }
            }).then(function(result) {
                if (result.value == true) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ url('mom/mom_delete') }}",
                        type: "POST",
                        data: {
                            id: id
                        },
                        success: function(response) {

                            js_notification('error', "Event Deleted Successfully");
                            window.location.href = baseUrl + "mom/mom_dashboard" +
                                "?parent=" +
                                getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];

                        }
                    })
                }
            });
        });
    </script>
@endpush
