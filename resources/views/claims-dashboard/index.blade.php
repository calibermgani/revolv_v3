@extends('layouts.app3')
@section('content')
    <div class="card card-custom mb-5 custom-card" id="claimsDashboard" style="background-color:#D9D9D9">
        <div class="card-body" style="background-color:#D9D9D9;padding:0.25rem !important">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="dash_filter mt-4">
                            <div>
                                <p class="mb-0"><b id="pageTitle">{{ $pageTitle }}</b></p>
                              </div>
                            <div class="d-flex align-items-center flex-wrap" style="gap:8px">
                                <input type="text" id="claimsRange" class="form-control claims-range" placeholder="mm/dd/yyyy - mm/dd/yyyy" autocomplete="off" readonly>
                                <button type="button" class="btn btn-light-green" id="refreshClaims">Refresh</button>
                            </div>
                        </div>
                        <div class="px-4 pb-1 d-flex justify-content-between flex-wrap" style="gap:8px">
                            <span class="text-muted" style="font-size:12px">A selected date runs from 08:00 that morning through 07:59 the next morning. Uploads use the upload date.</span>
                            <span class="text-muted" id="claimsUpdated" style="font-size:12px">Last updated: —</span>
                        </div>
                        <div id="claimsError" class="text-danger px-4" style="display:none;font-size:13px"></div>
                        <div class="card-body" style="padding-top:0.25rem">
                            <div class="row claims-metrics">
                                <div class="col p-2">
                                    <div class="card bg_total text-black dash_card mt-2">
                                        <img src="{{ asset('/assets/media/bg/totalC_dash.svg') }}" class="dash_icon" alt="">
                                        <span class="dash_card_font"><span id="cardUploaded">—</span> <span id="cardUploadedUnit">claim</span></span>
                                        ATB uploaded
                                    </div>
                                </div>
                                <div class="col p-2">
                                    <div class="card bg_assign text-black dash_card mt-2">
                                        <img src="{{ asset('/assets/media/bg/assign_dash.svg') }}" class="dash_icon" alt="">
                                        <span class="dash_card_font"><span id="cardAllocated">—</span> <span id="cardAllocatedUnit">claim</span></span>
                                        Allocated
                                    </div>
                                </div>
                                <div class="col p-2">
                                    <div class="card bg_comp text-black dash_card mt-2">
                                        <img src="{{ asset('/assets/media/bg/complete_dash.svg') }}" class="dash_icon" alt="">
                                        <span class="dash_card_font"><span id="cardWorked">—</span> <span id="cardWorkedUnit">claim</span></span>
                                        Worked
                                    </div>
                                </div>
                                <div class="col p-2">
                                    <div class="card bg_rework text-black dash_card mt-2">
                                        <img src="{{ asset('/assets/media/bg/rework_dash.svg') }}" class="dash_icon" alt="">
                                        <span class="dash_card_font"><span id="cardUnworked">—</span> <span id="cardUnworkedUnit">claim</span></span>
                                        Unworked
                                    </div>
                                </div>
                                <div class="col p-2">
                                    <div class="card bg_pend text-black dash_card mt-2">
                                        <img src="{{ asset('/assets/media/bg/pending_dash.svg') }}" class="dash_icon" alt="">
                                        <span class="dash_card_font"><span id="cardUnallocated">—</span> <span id="cardUnallocatedUnit">claim</span></span>
                                        Unallocated
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 pr-0">
                    <div class="card claims-panel">
                        <div class="dash_card3_filter mt-4 ml-4">
                            <div>
                                <span><b>Actual vs. target</b></span>
                                <div class="text-muted" id="productionSubtitle" style="font-size:12px">Production by subproject</div>
                            </div>
                            <span class="claims-legend"><i class="actual"></i>Actual <i class="target"></i>Target</span>
                        </div>
                        <div class="card-body" id="productionBars" style="padding-top:0.25rem">
                            <div class="text-muted text-center py-5">Fetching...</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7 pl-2">
                    <div class="card claims-panel">
                        <div class="dash_card3_filter mt-4 ml-4">
                            <span><b>Allocation by project</b></span>
                        </div>
                        <div class="card-body" style="padding-top:0.25rem">
                            <table id="allocationTable" class="table table-separate table-head-custom no-footer mb-0">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th class="text-right">Uploaded</th>
                                        <th class="text-right">Allocated</th>
                                        <th class="text-right">Worked</th>
                                        <th class="text-right">Unworked</th>
                                        <th class="text-right">Unallocated</th>
                                    </tr>
                                </thead>
                                <tbody id="scopeRows">
                                    <tr><td colspan="6" class="text-center text-muted">Fetching...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-md-5 pr-0">
                    <div class="card claims-panel">
                        <div class="dash_card3_filter mt-4 ml-4">
                            <div>
                                <span><b>Hourly production</b></span>
                                <div class="text-muted" id="hourTitle" style="font-size:12px">Claims worked by hour</div>
                            </div>
                            <span class="claims-hour-total" id="hourTotal"></span>
                        </div>
                        <div class="card-body" style="padding-top:0.25rem">
                            <div class="claims-hours" id="hourBars"></div>
                            <div id="claimsHourTip" class="claims-hour-tip" style="display:none"></div>
                            <div class="text-muted mt-3" style="font-size:12px">Peak hour: <b id="peakHour" class="text-dark">—</b></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7 pl-2">
                    <div class="card claims-panel">
                        <div class="dash_card3_filter mt-4 ml-4">
                            <span><b>Production summary</b></span>
                        </div>
                        <div class="card-body" style="padding-top:0.25rem">
                            <table id="productionSummaryTable" class="table table-separate table-head-custom no-footer mb-0">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Project</th>
                                        <th class="text-right">Allocated</th>
                                        <th class="text-right">Actual</th>
                                        <th class="text-right">Target</th>
                                    </tr>
                                </thead>
                                <tbody id="employeeRows">
                                    <tr><td colspan="5" class="text-center text-muted">Fetching...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('view.scripts')
    <style>
        #claimsDashboard .dash_card {
            height: auto;
            min-height: 108px;
            padding: 14px 14px 12px 16px;
            gap: 0;
            font-size: 12px;
            line-height: 1.3;
        }
        #claimsDashboard .dash_icon {
            width: 36px;
            height: 36px;
        }
        #claimsDashboard .dash_card_font {
            display: block;
            margin: 8px 0 4px;
            font-size: 20px;
            line-height: 1.2;
        }
        #claimsDashboard .dash_card_font span[id$="Unit"] {
            font-size: 13px;
            font-weight: 500;
        }
        #claimsDashboard .claims-panel {
            height: 352px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        #claimsDashboard .claims-panel .card-body {
            flex: 1;
            overflow: auto;
        }
        @media (max-width: 991px) {
            #claimsDashboard .col-md-6,
            #claimsDashboard .col-md-5,
            #claimsDashboard .col-md-7 { margin-bottom: 8px; }
        }
        #claimsDashboard .claims-legend { font-size: 11px; color: #666; white-space: nowrap; }
        #claimsDashboard .claims-legend i {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 2px;
            margin: 0 4px 0 10px;
            background: #b8eaf3;
        }
        #claimsDashboard .claims-legend i.actual { background: #139AB3; margin-left: 0; }
        #claimsDashboard .claims-bar {
            display: grid;
            grid-template-columns: minmax(90px, 34%) 1fr 72px;
            gap: 10px;
            align-items: center;
            margin-bottom: 14px;
            font-size: 12px;
        }
        #claimsDashboard .claims-bar .name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        #claimsDashboard .claims-track {
            position: relative;
            height: 22px;
            background: #F1F1F1;
            border-radius: 5px;
            overflow: hidden;
        }
        #claimsDashboard .claims-target, #claimsDashboard .claims-actual {
            position: absolute;
            left: 0;
            border-radius: 5px;
        }
        #claimsDashboard .claims-target { top: 0; height: 100%; background: #b8eaf3; }
        #claimsDashboard .claims-actual { top: 5px; height: 12px; background: #139AB3; }
        #claimsDashboard .claims-pct { text-align: right; font-weight: 700; color: #191C24; }
        #claimsDashboard .claims-pct.good { color: #0b9065; }
        #claimsDashboard .claims-pct.low { color: #c77813; }
        #claimsDashboard .claims-hours {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            min-height: 210px;
            overflow-x: auto;
            border-bottom: 1px solid #edf0f5;
            padding: 4px 2px 0;
        }
        #claimsDashboard .claims-hour {
            flex: 1 1 0;
            min-width: 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
        }
        #claimsDashboard .claims-hour-count {
            min-height: 14px;
            margin-bottom: 4px;
            font-size: 10px;
            font-weight: 650;
            line-height: 1;
            color: #191C24;
        }
        #claimsDashboard .claims-hour-track {
            width: 16px;
            height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            background: #f3f6f8;
            border-radius: 6px 6px 0 0;
            overflow: hidden;
        }
        #claimsDashboard .claims-hour-bar {
            width: 100%;
            flex: 0 0 auto;
            background: #8fd0de;
            border-radius: 6px 6px 0 0;
        }
        #claimsDashboard .claims-hour.is-peak .claims-hour-bar { background: #139AB3; }
        #claimsDashboard .claims-hour.is-peak .claims-hour-count { color: #139AB3; }
        #claimsDashboard .claims-hour-label {
            margin-top: 6px;
            font-size: 10px;
            color: #666;
            white-space: nowrap;
        }
        .claims-hour-tip {
            position: fixed;
            z-index: 10000;
            max-width: 420px;
            background: #191C24;
            color: #fff;
            border-radius: 6px;
            padding: 8px 10px;
            font-size: 12px;
            line-height: 1.4;
            pointer-events: none;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
        }
        .claims-hour-tip .claims-tip-title { font-weight: 650; margin-bottom: 4px; }
        .claims-hour-tip .claims-tip-line {
            display: flex;
            justify-content: space-between;
            gap: 16px;
        }
        .claims-hour-tip .claims-tip-line span { white-space: normal; }
        #claimsDashboard .claims-hour-total {
            background: #b8eaf3;
            color: #139AB3;
            font-weight: 700;
            font-size: 11px;
            border-radius: 6px;
            padding: 4px 8px;
        }
        #claimsDashboard .claims-range { width: 250px; background: #fff; cursor: pointer; }
        #claimsDashboard .table.table-head-custom thead th { width: auto; letter-spacing: 0; font-size: 12px; }
        #claimsDashboard .dataTables_wrapper .dataTables_filter {
            float: none;
            text-align: right;
            margin: 0 0 8px;
        }
        #claimsDashboard .dataTables_wrapper .dataTables_filter label {
            margin: 0;
            font-size: 0;
        }
        #claimsDashboard .dataTables_wrapper .dataTables_filter input {
            background: #f3f3f3 url("/devImg/search.svg") no-repeat 10px center !important;
            background-size: 16px !important;
            width: 220px !important;
            height: 34px;
            margin: 0;
            border: none;
            border-radius: 6px;
            padding: 4px 12px 4px 34px;
            font-size: 12px !important;
            font-family: Poppins, sans-serif;
            color: #191C24;
        }
        #claimsDashboard .dataTables_wrapper .dataTables_filter input::placeholder {
            color: transparent;
        }
        #claimsDashboard .dataTables_paginate,
        #claimsDashboard .dataTables_info,
        #claimsDashboard .dataTables_length { display: none; }
        #claimsDashboard td.text-right, #claimsDashboard th.text-right { text-align: right; }
    </style>
    <script>
        (function () {
            var role = @json($role);
            var dataUrl = @json(route('claims-dashboard.data'));
            var titles = {
                user: 'My Production Dashboard',
                tl: 'Team Production Dashboard',
                manager: 'Operations Dashboard'
            };

            function esc(value) {
                return String(value == null || value === '' ? '—' : value).replace(/[&<>"']/g, function (char) {
                    return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[char];
                });
            }

            function num(value) {
                return Number(value || 0).toLocaleString('en-IN');
            }

            function claimWord(value) {
                return Number(value || 0) <= 1 ? 'claim' : 'claims';
            }

            function setCard(countId, unitId, value) {
                document.getElementById(countId).textContent = num(value);
                document.getElementById(unitId).textContent = claimWord(value);
            }

            function cell(value) {
                return '<td class="text-right" data-order="' + Number(value || 0) + '">' + num(value) + '</td>';
            }

            function fillTable(selector, bodyHtml, emptyText) {
                if ($.fn.dataTable.isDataTable(selector)) {
                    $(selector).DataTable().destroy();
                }
                $(selector).find('tbody').html(bodyHtml);
                $(selector).DataTable({
                    searching: true,
                    paging: false,
                    info: false,
                    lengthChange: false,
                    order: [],
                    language: {
                        search: '',
                        searchPlaceholder: '',
                        emptyTable: emptyText,
                        zeroRecords: 'No matching rows.'
                    }
                });
            }

            function bindHourTips() {
                if (bindHourTips.ready) {
                    return;
                }
                bindHourTips.ready = true;
                var box = document.getElementById('hourBars');
                var tip = document.getElementById('claimsHourTip');

                function hideTip() {
                    tip.style.display = 'none';
                }

                box.addEventListener('mouseover', function (event) {
                    var hour = event.target.closest('.claims-hour');
                    if (!hour) {
                        return;
                    }
                    var detail = hour.querySelector('.claims-hour-detail');
                    if (!detail) {
                        hideTip();
                        return;
                    }
                    tip.innerHTML = detail.innerHTML;
                    tip.style.display = 'block';
                    var rect = hour.getBoundingClientRect();
                    var tipRect = tip.getBoundingClientRect();
                    var left = rect.left + (rect.width / 2) - (tipRect.width / 2);
                    var top = rect.top - tipRect.height - 8;
                    if (top < 8) {
                        top = rect.bottom + 8;
                    }
                    if (left < 8) {
                        left = 8;
                    }
                    if (left + tipRect.width > window.innerWidth - 8) {
                        left = window.innerWidth - tipRect.width - 8;
                    }
                    tip.style.left = left + 'px';
                    tip.style.top = top + 'px';
                });
                box.addEventListener('mouseout', function (event) {
                    var hour = event.target.closest('.claims-hour');
                    var next = event.relatedTarget && event.relatedTarget.closest
                        ? event.relatedTarget.closest('.claims-hour')
                        : null;
                    if (hour && hour !== next) {
                        hideTip();
                    }
                });
            }

            function setError(message) {
                var box = document.getElementById('claimsError');
                box.style.display = message ? 'block' : 'none';
                box.textContent = message || '';
            }

            function render(data) {
                document.getElementById('pageTitle').textContent = titles[data.role] || titles[role];
                document.getElementById('productionSubtitle').textContent = data.role === 'user'
                    ? 'My production by subproject'
                    : 'Production by subproject';
                setCard('cardUploaded', 'cardUploadedUnit', data.cards.uploaded);
                setCard('cardAllocated', 'cardAllocatedUnit', data.cards.allocated);
                setCard('cardWorked', 'cardWorkedUnit', data.cards.worked);
                setCard('cardUnworked', 'cardUnworkedUnit', data.cards.unworked);
                setCard('cardUnallocated', 'cardUnallocatedUnit', data.cards.unallocated);
                document.getElementById('claimsUpdated').textContent = 'Last updated: ' + data.generated_at;

                var scopes = data.scopes || [];
                var max = 1;
                scopes.forEach(function (row) {
                    max = Math.max(max, row.actual, row.target);
                });
                document.getElementById('productionBars').innerHTML = scopes.length ? scopes.map(function (row) {
                    var pct = row.target > 0 ? Math.round(row.actual / row.target * 100) : 0;
                    return '<div class="claims-bar"><span class="name" title="' + esc(row.name) + '">' + esc(row.name) + '</span>'
                        + '<div class="claims-track"><div class="claims-target" style="width:' + (row.target / max * 100) + '%"></div>'
                        + '<div class="claims-actual" style="width:' + (row.actual / max * 100) + '%"></div></div>'
                        + '<span class="claims-pct ' + (row.target > 0 && pct >= 100 ? 'good' : 'low') + '">' + num(row.actual) + '/' + num(row.target) + '</span></div>';
                }).join('') : '<div class="text-muted text-center py-5">No scopes in this date range.</div>';

                fillTable('#allocationTable', scopes.map(function (row) {
                    return '<tr><td>' + esc(row.name) + '</td>' + cell(row.uploaded) + cell(row.allocated)
                        + cell(row.worked) + cell(row.unworked) + cell(row.unallocated) + '</tr>';
                }).join(''), 'No claims in this date range.');

                var employees = data.employees || [];
                fillTable('#productionSummaryTable', employees.map(function (row) {
                    return '<tr><td>' + esc(row.name) + '</td><td>' + esc(row.scope) + '</td>' + cell(row.allocated)
                        + cell(row.actual) + cell(row.target) + '</tr>';
                }).join(''), 'No production in this date range.');

                var hourList = data.hours || [];
                var peak = 0;
                hourList.forEach(function (row) { peak = Math.max(peak, row.count); });
                var oneDay = data.start === data.end;
                document.getElementById('hourTitle').textContent = oneDay
                    ? 'Claims worked on ' + moment(data.start).format('MM/DD/YYYY')
                    : 'Hourly totals across the selected dates';
                document.getElementById('hourTotal').textContent = num(data.cards.actual) + ' produced';
                document.getElementById('peakHour').textContent = data.peak
                    ? data.peak.label + ' (' + num(data.peak.count) + ' claims)'
                    : '—';
                document.getElementById('hourBars').innerHTML = hourList.map(function (row) {
                    var height = peak > 0 ? Math.round(row.count / peak * 150) : 0;
                    var isPeak = data.peak && row.label === data.peak.label && row.count > 0;
                    var projects = (row.projects || []).filter(function (project) { return project.count > 0; });
                    var tip = '<div class="claims-tip-title">' + esc(row.label) + ' · ' + num(row.count) + '</div>'
                        + projects.map(function (project) {
                            return '<div class="claims-tip-line"><span>' + esc(project.name) + '</span><b>' + num(project.count) + '</b></div>';
                        }).join('');
                    return '<div class="claims-hour' + (isPeak ? ' is-peak' : '') + '">'
                        + '<span class="claims-hour-detail" hidden>' + tip + '</span>'
                        + '<span class="claims-hour-count">' + (row.count > 0 ? num(row.count) : '') + '</span>'
                        + '<div class="claims-hour-track"><div class="claims-hour-bar" style="height:' + height + 'px"></div></div>'
                        + '<span class="claims-hour-label">' + esc(row.label.slice(0, 2)) + '</span></div>';
                }).join('');
                bindHourTips();
            }

            function selectedRange() {
                var picker = $('#claimsRange').data('daterangepicker');
                if (!picker) {
                    return null;
                }
                return {
                    start: picker.startDate.format('YYYY-MM-DD'),
                    end: picker.endDate.format('YYYY-MM-DD')
                };
            }

            function loadData() {
                var range = selectedRange();
                if (!range || !range.start || !range.end || range.start > range.end) {
                    setError('Select a valid date range.');
                    return;
                }
                var start = range.start;
                var end = range.end;
                setError('');
                window.claimsDashboardLoading = true;
                if (typeof showGlobalLoader === 'function') {
                    showGlobalLoader('Loading...');
                }
                fetch(dataUrl + '?start=' + encodeURIComponent(start) + '&end=' + encodeURIComponent(end) + '&_=' + Date.now(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store',
                    credentials: 'same-origin'
                }).then(function (response) {
                    return response.text().then(function (text) {
                        var body = {};
                        try {
                            body = text ? JSON.parse(text) : {};
                        } catch (parseError) {
                            throw new Error(response.status === 401 ? 'Please sign in again.' : 'Unable to load dashboard counts.');
                        }
                        if (!response.ok) {
                            throw new Error(body.message || 'Unable to load dashboard counts.');
                        }
                        return body;
                    });
                }).then(function (body) {
                    render(body);
                }).catch(function (error) {
                    setError(error.message || 'Unable to load dashboard counts.');
                    document.getElementById('claimsUpdated').textContent = 'Last updated: —';
                }).finally(function () {
                    window.claimsDashboardLoading = false;
                    if (typeof hideGlobalLoader === 'function') {
                        hideGlobalLoader();
                    }
                });
            }

            function previousWorkingDay() {
                var day = moment().subtract(1, 'days');
                while (day.day() === 0 || day.day() === 6) {
                    day.subtract(1, 'days');
                }
                return day;
            }

            $('#claimsRange').daterangepicker({
                startDate: moment(@json($startDate)),
                endDate: moment(@json($endDate)),
                showDropdowns: true,
                autoUpdateInput: true,
                opens: 'left',
                locale: {
                    format: 'MM/DD/YYYY',
                    cancelLabel: 'Cancel',
                    applyLabel: 'Apply',
                    customRangeLabel: 'Custom Range'
                },
                ranges: {
                    'Yesterday': [previousWorkingDay(), previousWorkingDay()],
                    'Today': [moment(), moment()],
                    'Current Month': [moment().startOf('month'), moment()],
                    'Last Month': [
                        moment().subtract(1, 'month').startOf('month'),
                        moment().subtract(1, 'month').endOf('month')
                    ]
                }
            }).on('apply.daterangepicker', function () {
                loadData();
            });

            document.getElementById('refreshClaims').addEventListener('click', loadData);
            loadData();
        })();
    </script>
@endpush
