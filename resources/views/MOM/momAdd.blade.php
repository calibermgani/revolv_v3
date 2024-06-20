@extends('layouts.app3')
@section('content')
    {!! Form::open([
        'url' => url('mom/mom_store') . '?parent=' . request()->parent . '&child=' . request()->child,
        'method' => 'POST',
        'id' => 'momAdd',
        'enctype' => 'multipart/form-data',
    ]) !!}
    @csrf
    <div class="card card-custom custom-card">
        <div class="card-body pt-4 pb-0 px-2">
            <div class="mb-6 ml-4">
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
                    MOM</a>
            </div>
            <div id="mom_div">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row form-group pl-5">
                            <div class="col-md-3">
                                <label class="required">Meeting Title</label>
                                <div class="form-group mb-1">
                                    <input type="text" id="meeting_title" name="meeting_title"
                                        class="white-smoke form-control meeting_title" autocomplete="nope">

                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="required">Attendies</label>
                                <div class="form-group mb-1">
                                    @php
                                        $attendies = App\Http\Helper\Admin\Helpers::getMomAttendiesList();
                                    @endphp
                                    {!! Form::select('meeting_attendies[]', $attendies, null, [
                                        'class' => 'form-control white-smoke meeting_attendies select2',
                                        'id' => 'meeting_attendies',
                                        'multiple' => 'multiple',
                                        'autocomplete' => 'nope',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="required">TimeZone</label>
                                <div class="form-group mb-1">
                                    {!! Form::select('time_zone', $timezones, null, [
                                        'class' => 'form-control white-smoke time_zone select2',
                                        'id' => 'time_zone',
                                        'autocomplete' => 'nope',
                                    ]) !!}

                                </div>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="row form-group pl-5">
                            <input type="hidden" name="meeting_date" value={{ $clickedDate }}>
                            <div class="col-md-3">
                                <label class="required">Start Time</label>
                                <div class="form-group mb-1">
                                    <input type="text" id="start_time" name="start_time"
                                        class="white-smoke form-control start_time" value="" autocomplete="nope">

                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="required">End Time</label>
                                <div class="form-group mb-1">
                                    <input type="text" id="end_time" name="end_time"
                                        class="white-smoke form-control end_time" value="" autocomplete="nope">
                                </div>
                            </div>
                            <div class="col-md-3 options_div">
                                <label class="required">ETA</label>
                                <div class="form-group mb-1">
                                    <input type="date" id="eta" name="eta"
                                        class="white-smoke form-control eta" autocomplete="nope">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row form-group pl-5 pr-5">
                    <div class="col-md-12">
                        <label>Description</label>
                        {{-- <div class="form-group mb-1">
                            {!! Form::textarea('description', null, [
                                'class' => 'white-smoke form-control',
                                'rows' => 3,
                                'id' => 'description',
                            ]) !!}
                        </div> --}}
                        <div class="white-smoke">
                            <div id="kt_quil_1" style="height: 100px"></div>
                            <input type="hidden" name="req_description" id="req_description">
                        </div>
                    </div>
                </div>

                {{-- <div class="row ml-4">
                    <div style="width:91%">
                        <table class="table table-separate table-head-custom no-footer dtr-column" id="mom_list">
                            <thead>
                                <tr>
                                    <th style="background-color: #0969C3 !important;width: 3%">S.No</th>
                                    <th style="background-color: #0969C3 !important">Topics</th>
                                    <th style="background-color: #0969C3 !important">Description</th>
                                    <th style="background-color: #0969C3 !important">Action Item</th>
                                    <th style="background-color: #0969C3 !important">Responsible party</th>
                                    <th style="background-color: #0969C3 !important">ETA</th>
                                    <th style="background-color: #0969C3 !important">+/-</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="form_append_1">
                                    <td>1</td>
                                    <td>
                                        <textarea rows="3" id="topics1" name="topics[]" class="form-control topics"></textarea>
                                    </td>
                                    <td>
                                        <textarea rows="3" id="topic_description1" name="topic_description[]" class="form-control topic_description"></textarea>
                                    </td>
                                    <td>
                                        <textarea rows="3" id="action_item1" name="action_item[]" class="form-control action_item"></textarea>
                                    </td>
                                    <td>
                                        <textarea rows="3" id="responsible_party1" name="responsible_party[]" class="form-control responsible_party"></textarea>
                                    </td>
                                    <td><input type="date" id="topic_eta1" name="topic_eta[]"
                                            class="form-control topic_eta"></td>
                                    <td class="action_btn"><i class="fa fas fa-plus icon-circle2 ml-1 add_new_btn"
                                            id="add_new_btn_1"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div> --}}
                {{-- &nbsp;&nbsp;&nbsp;&nbsp;<div class="mt-1"><button id="add_new_btn1">+ Add New</button></div> --}}

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
                                    <th style="background-color: #139AB3 !important"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="clonetr">
                                    {{-- <td>1</td> --}}
                                    <td>
                                        <textarea rows="3" id="topics" name="topics[]" class="form-control topics" autocomplete="nope"></textarea>
                                    </td>
                                    <td>
                                        <textarea rows="3" id="topic_description" name="topic_description[]" class="form-control topic_description" autocomplete="nope"></textarea>
                                    </td>
                                    <td>
                                        <textarea rows="3" id="action_item" name="action_item[]" class="form-control action_item" autocomplete="nope"></textarea>
                                    </td>
                                    <td>
                                        <textarea rows="3" id="responsible_party" name="responsible_party[]" class="form-control responsible_party" autocomplete="nope"></textarea>
                                    </td>
                                    <td><input type="date" id="topic_eta" name="topic_eta[]"
                                            class="form-control topic_eta" autocomplete="nope"></td>

                                    {{-- <td class="action_btn"><i class="fa fas fa-plus icon-circle2 ml-1 add_new_btn"
                                                id="add_new_btn_1"></i></td> --}}
                                    <td><i class="fa fas fa-minus icon-circle-remove ml-1 deleteButton"></i></td>

                                </tr>
                            </tbody>
                        </table>

                    </div>&nbsp;&nbsp;&nbsp;&nbsp;<div class="mt-1"><input type="button" id="add_new_btn1"
                            class="addButton" value="+ Add New" /></div>
                </div>
                <div class="form-footer mr-5">
                    <button class="btn btn-light-danger" id="clear_submit" tabindex="10" type="button" onClick="window.location.reload();">
                        <span>
                            <span>Clear</span>
                        </span>
                    </button>&nbsp;&nbsp;
                    <button type="submit" class="btn btn-white-black font-weight-bold" id="mom_save">Submit</button>

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
            let counter = 2;
            // function addNewRow() {
            //     var plusButton = '<i class="fa fa-plus icon-circle2 ml-1 add_new_btn" id="' + 'add_new_btn_' + counter +
            //         '"></i>';
            //     var removeButton = '<i class="fa fas fa-minus icon-circle-remove ml-1 remove_btn" id="'+(counter - 1) + '"></i>';

            //     // $('#mom_list tbody tr:last-child td:last-child').addClass('action_btn');

            //     var newRow = [
            //         counter,
            //         '<textarea rows="3" id="topics' + counter +
            //         '" name="topics[]" class="form-control topics"></textarea>',
            //         '<textarea rows="3" id="topic_description' + counter +
            //         '" name="topic_description[]" class="form-control topic_description"></textarea>',
            //         '<textarea rows="3" id="action_item' + counter +
            //         '" name="action_item[]" class="form-control action_item"></textarea>',
            //         '<textarea rows="3" id="responsible_party' + counter +
            //         '" name="responsible_party[]" class="form-control responsible_party"></textarea>',
            //         '<input type="date" id="topic_eta' + counter +
            //         '" name="topic_eta[]" class="form-control topic_eta">',
            //         plusButton
            //     ];

            //     table.row.add(newRow).draw(false);
            //     elementToRemove = 'add_new_btn_' + (counter - 1);
            //     $('#' + elementToRemove).remove();
            //     console.log(removeButton);
            //     $('#mom_list tbody tr:nth-last-child(2) td:last-child').append(removeButton);
            //     counter++;
            // }
            function addNewRow() {
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

                // Add the new row
                var newRowNode = table.row.add(newRow).draw(false).node();

                // Set id attribute to the new <tr> element
                $(newRowNode).attr('id', 'form_append_' + counter);

                // Remove previous plus button and add remove button to the previous row
                elementToRemove = 'add_new_btn_' + (counter - 1);
                $('#' + elementToRemove).remove();
                $('#form_append_' + (counter - 1) + ' td:last-child').html(removeButton);

                counter++;
            }

            const table = $("#mom_list").DataTable({
                processing: true,
                ordering: true,
                clientSide: true,
                lengthChange: false,
                searching: false,
                // pageLength: 20,
                "info": false,
                paging: false,
            });
            $("#addrow").DataTable({
                processing: true,
                ordering: true,
                clientSide: true,
                lengthChange: false,
                searching: false,
                // pageLength: 20,
                "info": false,
                paging: false,
            });

            $(document).on('click', '.remove_btn', function() {
                var button_id = $(this).attr("id");
                $('#form_append_' + button_id + '').remove();
            });
            // document.querySelector('#add_new_btn').addEventListener('click', addNewRow);
            // document.querySelector('#add_new_btn1').addEventListener('click', addNewRow);

            // Automatically add a first row of data
            // addNewRow();
            $(document).on('click', '.add_new_btn', function() {
                addNewRow();
            });

            // $(".addButton").click(function() {
            //     $('.clonetr:last').clone(true).appendTo("#addrow");
            // });

            // $(".deleteButton").click(function() {
            //     if ($('.deleteButton').length > 1) {

            //         $(this).closest("tr").remove();
            //     }

            // });
            $(".addButton").click(function() {
                let cloneRow = $("#addrow tbody tr.clonetr:last").clone(true);
                cloneRow.find('input[type="text"], input[type="date"], textarea').val('');
                $("#addrow tbody").prepend(cloneRow);
                //  $("#addrow tbody tr.clonetr:last td:last").html('<i class="fa fas fa-minus icon-circle-remove ml-1 deleteButton" ></i>');

            });

            $(document).on('click', '.deleteButton', function() {
                if ($('#addrow tbody tr.clonetr').length > 1) {
                    $(this).closest("tr").remove();
                }
            });
            $(document).on('click', '#mom_save', function(e) {
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
                    // meetingTitle.css('border-color', '');
                    // meetingAttendies.next('.select2').find(".select2-selection").css('border-color', '');
                    // timeZone.next('.select2').find(".select2-selection").css('border-color', '');
                    // startTimeVal.css('border-color', '');
                    // endTimeVal.css('border-color', '');
                    // etaVal.css('border-color', '');
                    document.querySelector('#momAdd').submit();
                }
            });
        </script>
    @endpush
