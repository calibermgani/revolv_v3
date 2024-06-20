@extends('layouts.app3')
@section('content')
    <div class="card card-custom custom-card">
        <div class="card-body pt-4 pb-0 px-2">
            <div class="my-client-div">
                <span class="project_header" style="margin:1rem !important">MOM</span>
            </div>
            <div class="mb-4">
                <span class="project_header" style="margin:1rem !important">Calendar</span>
            </div>
            <div id="calendar" style="margin:1rem"></div>
        </div>
    </div>
@endsection
<style>
    .tooltip-card {
        width: auto;
        padding: 10px;
        background-color: #fff;
        border: 0.5px solid #dbd6d6;
        border-radius: 5px;
    }

    .tooltip-header {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .tooltip-body {
        font-size: 12px;
    }

    .attendee {
        margin-bottom: 5px;
    }

    .eta {
        font-weight: bold;
        color: #139AB3;
    }

    #calendar .fc-content,
    #calendar .fc-day-grid-event,
    #calendar .fc-day-top,
    td {
        cursor: pointer;
    }

    /* .card1 {
        width: 200px;
        height: auto;
        border-radius: 6px;
    }

    .card-header1 {
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 10px;
        background-color: #0969C3;
        color: #ffffff;
        padding: 16px;
        border-radius: 8px;
        padding: 16px;
        border-radius: 6px 6px 0px 0px;

    }

    .card-body1 {
        font-size: 12px;
    }

    .card-body1 ul {
         padding-left: 2rem;
    }

    .card-body1 li {
        margin-bottom: 5px;
    } */
</style>
@push('view.scripts')
    <script>
        $(document).ready(function() {

            var KTAppsEducationSchoolCalendar = function() {

                return {
                    init: function() {
                        var todayDate = moment().startOf('day');
                        var YM = todayDate.format('YYYY-MM');
                        var YESTERDAY = todayDate.clone().subtract(1, 'day').format('YYYY-MM-DD');
                        var TODAY = todayDate.format('YYYY-MM-DD');
                        var TOMORROW = todayDate.clone().add(1, 'day').format('YYYY-MM-DD');
                        var calendarEl = document.getElementById('calendar');
                        var calendar = new FullCalendar.Calendar(calendarEl, {
                            plugins: ['bootstrap', 'interaction', 'dayGrid', 'timeGrid', 'list'],
                            themeSystem: 'bootstrap',
                            isRTL: KTUtil.isRTL(),
                            header: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            height: 800,
                            contentHeight: 550,
                            aspectRatio: 3,
                            nowIndicator: true,
                            now: TODAY + 'T09:25:00',
                            views: {
                                dayGridMonth: {
                                    buttonText: 'month'
                                },
                            },
                            defaultView: 'dayGridMonth',
                            defaultDate: TODAY,
                            showNonCurrentDates: false,
                            editable: false,
                            eventLimit: true,
                            navLinks: false,
                            events: [
                                <?php echo trim($calendar_data, '[]'); ?>
                            ],
                            dateClick: function(info) {
                                var clickedDate = info.date;
                                var localDate = new Date(clickedDate.getTime() - (clickedDate
                                    .getTimezoneOffset() * 60000));
                                var formattedDate = localDate.toISOString().split('T')[0];
                                window.location.href = baseUrl + "mom/mom_add/" + btoa(
                                        formattedDate) + "?parent=" +
                                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
                            },
                            eventRender: function(info) {
                                var element = $(info.el);
                                var startDate = customDate(info.event.start);
                                var endDate = customDate(info.event.end);

                                function customDate(dateString) {
                                    date = new Date(dateString);
                                    var hours = date.getHours();
                                    var minutes = date.getMinutes();
                                    var suffix = hours >= 12 ? "PM" : "AM";
                                    hours = hours % 12 || 12;
                                    var formattedTime = hours + ":" + (minutes < 10 ? "0" :
                                        "") + minutes + " " + suffix;
                                    return formattedTime;
                                }

                                function formatDate(dateString) {
                                    var dateParts = dateString.split("-");
                                    var formattedDate = dateParts[1] + '/' + dateParts[2] +
                                        '/' + dateParts[0];
                                    return formattedDate;
                                }
                                if (info.event) {
                                    if (element.hasClass('fc-day-grid-event')) {
                                        var eventContent = '<span class="fc-title">' + info
                                            .event.title + '</span>' + ' - ' +
                                            '<span class="fc-time">' + startDate + ' to ' +
                                            endDate + '</span>';
                                        //  + '<span class="float-right"><i class="fa fa-times pr-3 fa_times_class" aria-hidden="true"></i></span>';
                                        element.find('.fc-content').html(eventContent);
                                        if (info.event.extendedProps) {
                                            var eventtooltip = info.event.extendedProps
                                                .meeting_attendies != null ? info.event
                                                .extendedProps.meeting_attendies.split(',') :
                                                NULL;
                                            var timeZone = info.event.extendedProps.time_zone !=
                                                null ? info.event.extendedProps.time_zone :
                                                null;

                                            var startTime = info.event.extendedProps
                                                .start_time !=
                                                null ? info.event.extendedProps.start_time :
                                                NULL;

                                            var endTime = info.event.extendedProps.end_time !=
                                                null ? info.event.extendedProps.end_time : NULL;

                                            if (startTime != null) {
                                                var startHour = parseInt(startTime.split(":")[
                                                    0]);
                                                var startPeriod = startHour >= 12 ? "PM" : "AM";
                                                var start_time = startTime + " " + startPeriod;
                                            }
                                            if (endTime != null) {
                                                var endHour = parseInt(endTime.split(":")[0]);
                                                var endPeriod = endHour >= 12 ? "PM" : "AM";
                                                var end_time = endTime + " " + endPeriod;
                                            }
                                            var eta = info.event.extendedProps.eta != null ?
                                                formatDate(info.event.extendedProps.eta) : null;

                                            var description = info.event.extendedProps
                                                .req_description != '' ? info.event
                                                .extendedProps
                                                .req_description : null;

                                            var attendeeHtml = '';

                                            // eventtooltip.forEach(function(attendee) {
                                            //     attendeeHtml += '<li>' +
                                            //         attendee + '</li>';
                                            // });
                                            // var tooltipHtml = '<div class="card1">' +
                                            //     '<div class="card-header1">' + info.event.title +
                                            //     '</div>' +
                                            //     '<div class="card-body1">' +
                                            //     '<ul>' + attendeeHtml +
                                            //     '<li>' + time_zone + '</li>' +
                                            //     '<li>' + start_time + '</li>' +
                                            //     '<li>' + end_time + '</li>' +
                                            //     '<li>' + eta + '</li>' + '</ul>' +
                                            //     '</div>' +
                                            //     '</div>';
                                            var tooltipHtml = '<div class="tooltip-card">' +
                                                '<div class="tooltip-body">' +
                                                '<p><span class="eta">Title: </span>' + info
                                                .event.title + '</p>' +
                                                '<p><span class="eta">Attendies: </span>' +
                                                eventtooltip + '</p>' +
                                                '<p><span class="eta">Time Zone: </span>' +
                                                timeZone + '</p>' +
                                                '<p><span class="eta">Time: </span>' +
                                                startDate +
                                                ' - ' + endDate + '</p>' +
                                                '<p><span class="eta">ETA: </span>' + eta +
                                                '</p>' +
                                                '<span class="eta">Description: </span>' +
                                                description + '</p>' +
                                                '</div>' +
                                                '</div>';

                                            element.data('content', tooltipHtml);
                                            element.data('html', true);
                                            element.data('placement', 'top');
                                            KTApp.initPopover(element);
                                        }

                                    }
                                }
                            },
                            eventClick: function(info) {
                                var element = $(info.el);
                                window.location.href = baseUrl + "mom/mom_edit/" + btoa(info
                                        .event.id) +
                                    "?parent=" +
                                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
                            }

                        });
                        calendar.render();
                    }
                };
            }();
            jQuery(document).ready(function() {
                KTAppsEducationSchoolCalendar.init();
            });

        });
    </script>
@endpush
