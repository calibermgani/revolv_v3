@extends('layouts.app3')
@section('content')
    <div class="card">
        <div class="row">
            <div class="col-md-6 mt-8">
                <canvas id="barChart" width="500" height = "400"></canvas>
            </div>
            <div class="col-md-6 mt-8">
                <canvas id="lineChart" width="500" height = "400"></canvas>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6  mt-8">
                <canvas id="pieChart" width="200" height = "200"></canvas>
            </div>
            <div class="col-md-6  mt-8">
                {{-- <canvas id="doughnutChart" width="200" height = "200"></canvas> --}}
            </div>
        </div>
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6"></div>
        </div>
    </div>
@endsection
<style>
    #chart-container {
        height: 500px;
    }
</style>
@push('view.scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Get the context of the canvas element
        const ctx = document.getElementById('barChart').getContext('2d');
        const agingData = @json($agingData);
        const labels = [];
        $.each(@json($agingHeader), function(key, val) {
            labels.push(val.days_range);
        });
        const datasets = [];
        Object.keys(agingData).forEach((key) => {
            datasets.push({
                label: key,
                data: agingData[key],
                backgroundColor: '#5d62b5',
                borderWidth: 1,
                barThickness: 40
            });
        });

        const noDataPlugin = {
            id: 'noDataPlugin',
            beforeDraw: (chart) => {
                if (chart.data.datasets.length === 0) {
                    const ctx = chart.ctx;
                    const width = chart.width;
                    const height = chart.height;
                    chart.clear();

                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = '13px Poppins';
                    ctx.fillText('Data not available', width / 2, height / 2);
                    ctx.restore();
                }
            }
        };

        const agingChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Aging - Analysis',
                        padding: {
                            top: 0,
                            bottom: 10,
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 8
                            },
                            boxWidth: 5,
                            boxHeight: 5,

                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMin: 0,
                        ticks: {
                            stepSize: 10,
                            font: {
                                size: 10
                            }
                        },
                        title: {
                            display: true,
                            text: 'Count'
                        },
                        grid: {
                            display: true // Keep vertical grid lines for y-axis
                        }
                    },
                    x: {
                        ticks: {
                            autoSkip: false,
                            font: {
                                size: 10
                            }
                        },
                        title: {
                            display: true,
                            text: 'Days Range'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            },
            plugins: [noDataPlugin]
        });

        //line Chart
        const ctxl = document.getElementById('lineChart').getContext('2d');
        const agingDataLine = @json($agingData);
        const lineLabels = [];
        $.each(@json($agingHeader), function(key, val) {
            lineLabels.push(val.days_range);
        });
        const lineDatasets = [];
        Object.keys(agingDataLine).forEach((key) => {
            lineDatasets.push({
                label: key,
                data: agingDataLine[key],
                borderColor: '#5d62b5', // Line color
                backgroundColor: '#5d62b5', // Fill color under the line
                pointBackgroundColor: '#ffffff', // Point color
                pointBorderColor: '#5d62b5', // Point border color
                pointHoverBackgroundColor: '#5d62b5', // Point hover background color
                pointHoverBorderColor: '#5d62b5',
                borderWidth: 1,

            });
        });

        const lineNoDataPlugin = {
            id: 'noDataPlugin',
            beforeDraw: (chart) => {
                if (chart.data.lineDatasets.length === 0) {
                    const ctx = chart.ctxl;
                    const width = chart.width;
                    const height = chart.height;
                    chart.clear();

                    ctxl.save();
                    ctxl.textAlign = 'center';
                    ctxl.textBaseline = 'middle';
                    ctxl.font = '13px Poppins';
                    ctxl.fillText('Data not available', width / 2, height / 2);
                    ctxl.restore();
                }
            }
        };
        console.log(lineLabels,'lineLabels',lineDatasets);
        const agingChartLine = new Chart(ctxl, {
            type: 'line',
            data: {
                labels: lineLabels,
                datasets: lineDatasets
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Aging - Analysis',
                        padding: {
                            top: 0,
                            bottom: 10,
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 8
                            },
                            boxWidth: 5,
                            boxHeight: 5,

                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMin: 0,
                        ticks: {
                            stepSize: 10,
                            font: {
                                size: 10
                            }
                        },
                        title: {
                            display: true,
                            text: 'Count'
                        },
                        grid: {
                            display: true // Keep vertical grid lines for y-axis
                        }
                    },
                    x: {
                        ticks: {
                            autoSkip: false,
                            font: {
                                size: 10
                            }
                        },
                        title: {
                            display: true,
                            text: 'Days Range'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            },
            plugins: [noDataPlugin]
        });
        // pie chart
        var ctxPie = document.getElementById('pieChart').getContext('2d');
        const agingDataPie = @json($agingData);
        const labelsPie = [];
        $.each(@json($agingHeader), function(key, val) {
            labelsPie.push(val.days_range);
        });
        const datasetsPie = [];
        Object.keys(agingDataPie).forEach((key) => {
            datasetsPie.push({
                label: key,
                data: agingDataPie[key],
                borderColor: '#5d62b5', // Line color
                backgroundColor: '#5d62b5', // Fill color under the line
                pointBackgroundColor: '#ffffff', // Point color
                pointBorderColor: '#5d62b5', // Point border color
                pointHoverBackgroundColor: '#5d62b5', // Point hover background color
                pointHoverBorderColor: '#5d62b5',
                borderWidth: 1,

            });
        });

        // const noDataPluginPie = {
        //     id: 'noDataPlugin',
        //     beforeDraw: (chart) => {
        //         if (chart.data.datasetsPie.length === 0) {
        //             const ctx = chart.ctxPie;
        //             const width = chart.width;
        //             const height = chart.height;
        //             chart.clear();

        //             ctxPie.save();
        //             ctxPie.textAlign = 'center';
        //             ctxPie.textBaseline = 'middle';
        //             ctxPie.font = '13px Poppins';
        //             ctxPie.fillText('Data not available', width / 2, height / 2);
        //             ctxPie.restore();
        //         }
        //     }
        // };
        console.log(labelsPie,datasetsPie);
        var myPieChart = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: labelsPie,
                datasets: [{
                    data: datasetsPie,
                    backgroundColor: ['#F7464A', '#46BFBD', '#FDB45C', '#949FB1', '#4D5360'],
                    hoverBackgroundColor: ['#FF5A5E', '#5AD3D1', '#FFC870', '#A8B3C5', '#616774']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Aging - Analysis',
                        padding: {
                            top: 0,
                            bottom: 10
                        }
                    }
                }
            },
            // plugins: [noDataPluginPie]
        });
        // var myPieChart = new Chart(ctxPie, {
        //     type: 'pie',
        //     data: {
        //         labels: labelsPie,
               
        //         datasets: [{
        //             data: datasetsPie,
        //             backgroundColor: ['#F7464A', '#46BFBD', '#FDB45C', '#949FB1', '#4D5360'],
        //             hoverBackgroundColor: ['#FF5A5E', '#5AD3D1', '#FFC870', '#A8B3C5', '#616774']
        //         }]
        //     },
        //     options: {
        //         responsive: true
        //     }
        // });
        //doughnutChart
        // var ctxDoughnut = document.getElementById('doughnutChart').getContext('2d');
        // var myDoughnutChart = new Chart(ctxDoughnut, {
        //     type: 'doughnut',
        //     data: {
        //         labels: ['Red', 'Green', 'Yellow', 'Grey', 'Dark Grey'],
        //         datasets: [{
        //             data: [300, 50, 100, 40, 120],
        //             backgroundColor: ['#F7464A', '#46BFBD', '#FDB45C', '#949FB1', '#4D5360'],
        //             hoverBackgroundColor: ['#FF5A5E', '#5AD3D1', '#FFC870', '#A8B3C5', '#616774']
        //         }]
        //     },
        //     options: {
        //         responsive: true
        //     }
        // });
    </script>
@endpush
{{-- @push('view.scripts')
    <!-- Include fusioncharts core library -->
    <script type="text/javascript" src="https://cdn.fusioncharts.com/fusioncharts/latest/fusioncharts.js"></script>
    <!-- Include fusion theme -->
    <script type="text/javascript"
        src="https://cdn.fusioncharts.com/fusioncharts/latest/themes/fusioncharts.theme.fusion.js"></script>
    <script type="text/javascript">
        //STEP 2 - Chart Data
        const chartData = [{
            "label": "Venezuela",
            "value": "290"
        }, {
            "label": "Saudi",
            "value": "260"
        }, {
            "label": "Canada",
            "value": "180"
        }, {
            "label": "Iran",
            "value": "140"
        }, {
            "label": "Russia",
            "value": "115"
        }, {
            "label": "UAE",
            "value": "100"
        }, {
            "label": "US",
            "value": "30"
        }, {
            "label": "China",
            "value": "30"
        }];

        //STEP 3 - Chart Configurations
        const chartConfig = {
            type: 'column2d',
            renderAt: 'chart-container',
            width: '100%',
            height: '400',
            dataFormat: 'json',
            dataSource: {
                // Chart Configuration
                "chart": {
                    "caption": "Countries With Most Oil Reserves [2017-18]",
                    "subCaption": "In MMbbl = One Million barrels",
                    "xAxisName": "Country",
                    "yAxisName": "Reserves (MMbbl)",
                    "numberSuffix": "K",
                    "theme": "fusion",
                },
                // Chart Data
                "data": chartData
            }
        };
        FusionCharts.ready(function() {
            var fusioncharts = new FusionCharts(chartConfig);
            fusioncharts.render();
        });
    </script>
@endpush --}}
