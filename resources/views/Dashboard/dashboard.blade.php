@extends('layouts.app3')

@section('content')


{{-- <div class="container" style="background-color: #ffffff;height:760px"> --}}
    <div class = "mx-0 my-4 px-4 py-4" style="background-color: #ffffff;width:100%;height:100%">
    <span class="ml-1"><b>Dashboard</b></span>
    <div class="row" style="height:50%">

        <!-- First section -->
        <div class="col-md-6">
            <!-- Left side: Five small cards showing status -->
            <div class="card" style="height:100%">
                <span class="mt-8 ml-4"><b>Today's claims</b></span>
                <div class="card-body mt-10">

                    <div class="row" style="gap:28px">
                        <div class="col-2" style="margin-left:-1.5rem">
                            <div class="card bg_assign text-black dash_card mt-2">
                                <img src="{{ asset('/assets/media/bg/assign_dash.svg') }}" class="dash_icon">
                                <span>30</span>
                                Assigned</div>
                        </div>
                        <div class="col-2">
                            <div class="card bg-comp text-black dash_card  mt-2">
                                <img src="{{ asset('/assets/media/bg/complete_dash.svg') }}" class="dash_icon">
                                <span>20</span>
                                Complete</div>
                        </div>
                        <div class="col-2">
                            <div class="card bg_pend text-black dash_card mt-2">
                                <img src="{{ asset('/assets/media/bg/pending_dash.svg') }}" class="dash_icon">
                                <span>50</span>
                                Pending</div>
                        </div>
                        <div class="col-2">
                            <div class="card bg_hold text-black dash_card mt-2">
                                <img src="{{ asset('/assets/media/bg/hold_dash.svg') }}" class="dash_icon">
                                <span>60</span>
                                On hold</div>
                        </div>
                        <div class="col-2">
                            <div class="card bg_rework text-black dash_card mt-2">
                                <img src="{{ asset('/assets/media/bg/rework_dash.svg') }}" class="dash_icon">
                                <span>700000</span>
                                Rework</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <!-- Right side: Flow chart -->
            <div class="card" style="height:100%">
                <span class="mt-4 ml-4"><b>MTD progress</b></span>
                <div class="card-body">
                    <canvas id="myChart"  width="400" height="165"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4" style="height:45%">
        <!-- Second section -->
        <div class="col-md-6  mb-8">
            <!-- Left card: Table 1 -->
            <div class="card" style="height:100%">
                <span class="mt-4 ml-4"><b>Projects</b></span>
                <div class="card-body"  data-scroll="true" data-height="180">
                    <table class="table table-separate table-head-dashboard no-footer" >
                       <thead>
                        <tr>
                            <th>#</th>
                            <th>Project</th>
                            <th>Completed</th>
                            <th>Pending</th>
                            <th>Rework</th>
                            <th>On hold</th>

                        </tr>
                       </thead>
                       <tbody>
                        <tr>
                            <td>01</td>
                            <td>Home Decor Range</td>
                            <td>20</td>
                            <td>30</td>
                            <td>40</td>
                            <td>50</td>
                        </tr>
                        <tr>
                            <td>02</td>
                            <td>Disney Prince</td>
                            <td>27</td>
                            <td>37</td>
                            <td>47</td>
                            <td>57</td>
                        </tr>
                        <tr>
                            <td>03</td>
                            <td>Aig</td>
                            <td>32</td>
                            <td>53</td>
                            <td>34</td>
                            <td>55</td>
                        </tr>
                        <tr>
                            <td>04</td>
                            <td>AMBC</td>
                            <td>32</td>
                            <td>53</td>
                            <td>34</td>
                            <td>55</td>
                        </tr>
                        <tr>
                            <td>05</td>
                            <td>Rebound</td>
                            <td>32</td>
                            <td>53</td>
                            <td>34</td>
                            <td>55</td>
                        </tr>
                       </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6  mb-8">
            <!-- Right card: Table 2 -->
            <div class="card"  style="height:100%">
                <span class="mt-4 ml-4"><b>On hold</b></span>
                <div class="card-body" data-scroll="true" data-height="180">
                    <table class="table table-separate table-head-dashboard no-footer">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Project</th>
                                <th>Claim No</th>
                                <th>Date</th>
                                <th>Claimed by</th>
                            </tr>
                           </thead>
                           <tbody>
                            <tr>
                                <td>1</td>
                                <td>pro1</td>
                                <td>103</td>
                                <td>12/25/2023</td>
                                <td>Test</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>pro2</td>
                                <td>105</td>
                                <td>12/25/2023</td>
                                <td>Test</td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>pro3</td>
                                <td>203</td>
                                <td>12/25/2023</td>
                                <td>Test</td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>pro4</td>
                                <td>203</td>
                                <td>12/25/2023</td>
                                <td>Test</td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>pro5</td>
                                <td>203</td>
                                <td>12/25/2023</td>
                                <td>Test</td>
                            </tr>
                           </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- </div> --}}





    @endsection
    <style>
        .notice_scroll {
            overflow-y: scroll;
            scrollbar-width: thin;
            scrollbar-color: darkgrey lightgrey;
        }

        .notice_scroll::-webkit-scrollbar {
            width: 4px;
            /*adjust the width as needed*/
        }

        .notice_scroll::-webkit-scrollbar-thumb {
            background-color: lightgrey;
            /* color of the thumb */
        }

        .notice_scroll::-webkit-scrollbar-track {
            background-color: #fff;
            /* color of the track */
        }
    </style>

@push('view.scripts')
    <!-- Include Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Get the context of the canvas element
        var ctx = document.getElementById('myChart').getContext('2d');

        // Define your data
        var data = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Sales',
                backgroundColor: 'rgb(255, 99, 132)',
                borderColor: 'rgb(255, 99, 132)',
                data: [12, 19, 3, 5, 2, 3, 7]
            }]
        };

        // Define chart options
        var options = {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            },

            responsive: true,
            maintainAspectRatio: false
        };

        // Create the chart
        var myChart = new Chart(ctx, {
            type: 'line',
            data: data,
            options: options
        });
    </script>
@endpush

