(function($) {
	'use strict';
	$(function() {

		chart_fetch_record();
		/* if($("#bar-chart-grouped").length) {
			var chart_value = chart_fetch_record();
			//alert(chart_value); 
			new Chart(document.getElementById("bar-chart-grouped"), {
				type: 'bar',
				data: {
				  labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Nov", "Dec"],
				  datasets: [
					{
					  label: "Achieved",
					  backgroundColor: "#34c295",
					  data: [54,67,99,24, 43, 56, 121, 34, 140, 150, 89, 32]
					}, {
					  label: "Target",
					  backgroundColor: "#d8d8d8",
					  data: [100,100,150,120, 130, 80, 140, 50, 200, 150, 100, 50]
					}
				  ]
				},
				options: {
				  title: {
					display: false,
					text: 'Population growth (millions)'
				  },
				  scales: {
							xAxes: [{
							stacked: false,
							barPercentage: 0.6,
							position: 'bottom',
							display: true,
							gridLines: {
								display: false,
								drawBorder: false,
							},
							ticks: {
								display: true, //this will remove only the label
								stepSize: 1,
							}
							}],
							yAxes: [{
								stacked: false,
								display: true,
								gridLines: {
									drawBorder: false,
									display: true,
									color: "#ddd",
									borderDash: [12, 2],
								},
								ticks: {
									beginAtZero: true,
									callback: function(value, index, values) {
									return '' + value;
									}
								},
							}]
						},
						legend: {
							display: false
						},
				}
			});
		} */

		var supportTrackerData = {
			labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", ],
			datasets: [{
				label: 'Achieved',
				data: [100, 200, 235, 126, 136, 342, 87, 45, 64],
				backgroundColor: [
					'#34c295', '#34c295', '#34c295', '#34c295', '#34c295', '#34c295', '#34c295', '#34c295', '#34c295',
				],
				borderColor: [
					'#34c295', '#34c295', '#34c295', '#34c295',  '#34c295', '#34c295', '#34c295', '#34c295', '#34c295',
				],
				borderWidth: 1,
				fill: false
			},
			{
					label: 'Target',
					data: [200, 255, 300, 240, 150, 350, 200, 200, 100],
					backgroundColor: [
						'#d8d8d8', '#d8d8d8', '#d8d8d8', '#d8d8d8', '#d8d8d8', '#d8d8d8', '#d8d8d8', '#d8d8d8', '#d8d8d8',
					],
					borderColor: [
						'#d8d8d8', '#d8d8d8', '#d8d8d8', '#d8d8d8', '#d8d8d8', '#d8d8d8', '#d8d8d8', '#d8d8d8', '#d8d8d8',
					],
					borderWidth: 1,
					fill: false
			}
			]

		};
		var supportTrackerOptions = {
			scales: {
				xAxes: [{
				stacked: true,
				barPercentage: 0.6,
				position: 'bottom',
				display: true,
				gridLines: {
					display: false,
					drawBorder: false,
				},
				ticks: {
					display: true, //this will remove only the label
					stepSize: 1,
				}
				}],
				yAxes: [{
					stacked: true,
					display: true,
					gridLines: {
						drawBorder: false,
						display: true,
						color: "#f0f3f6",
						borderDash: [3, 4],
					},
					ticks: {
						beginAtZero: true,
						callback: function(value, index, values) {
						return '' + value;
						}
					},
				}]
			},
			legend: {
				display: false
			},
			legendCallback: function(chart) {
				var text = [];
				text.push('<ul class="' + chart.id + '-legend">');
				for (var i = 0; i < chart.data.datasets.length; i++) {
					text.push('<li><span class="legend-box" style="background:' + chart.data.datasets[i].backgroundColor[i] + ';"></span><span class="legend-label text-dark">');
					if (chart.data.datasets[i].label) {
							text.push(chart.data.datasets[i].label);
					}
					text.push('</span></li>');
				}
				text.push('</ul>');
				return text.join("");
			},
			tooltips: {
				backgroundColor: 'rgba(0, 0, 0, 1)',
			},
			plugins: {
				datalabels: {
					display: false,
					align: 'center',
					anchor: 'center'
				}
			}
		};
		if ($("#supportTracker").length) {
			var barChartCanvas = $("#supportTracker").get(0).getContext("2d");
			// This will get the first returned node in the jQuery collection.
			var barChart = new Chart(barChartCanvas, {
				type: 'bar',
				data: supportTrackerData,
				options: supportTrackerOptions
			});
			document.getElementById('support-tracker-legend').innerHTML = barChart.generateLegend();
		}
		
	});
})(jQuery);




function chart_fetch_record() {
 
/* var targer_value='';
var achieved_value='';
var executive_motnh=''; */

var executive_motnh = [];
var achieved_value = [];
var targer_value = [];
var no_of_days;



	$.ajax({
		url:  baseUrl + "productions/executiveChart",			
		dataType: 'json',		
		success: function(response_value){
		 response_value_param = response_value;

		 if($("#bar-chart-grouped").length) {

			   var chart_value = response_value;
			
				if(chart_value!=''){
				$.each(chart_value, function(i, item) {
				targer_value.push(item.target);
				achieved_value.push(item.achieved);				
				executive_motnh.push(item.Month);
				no_of_days  = item.no_of_days;				
				});

				var target_sum  = eval(targer_value.join("+"));
				var achieved_sum = eval(achieved_value.join("+"));				
				var dt = new Date(); 
			    daysInMonth = new Date(dt.getFullYear(), dt.getMonth()+1, 0).getDate();		
				
				var count_days = weekend_days_count();
				count_days = daysInMonth - count_days;
			

				var target_percentage = Math.round(target_sum /count_days);					
				target_percentage = target_percentage / 100;
				$('#target_achieved_by_month').html(Math.round(achieved_sum / no_of_days)+"%");
				$('#production_percentage_perday').html(target_percentage+'%');

				
				
			}
				
				

			new Chart(document.getElementById("bar-chart-grouped"), {
				type: 'bar',
				data: {
				  /* labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Nov", "Dec"], */
				  labels: executive_motnh,
				  datasets: [
					{
					  label: "Achieved",
					  backgroundColor: "#34c295",
					  // data: [21,22] 
					  data: achieved_value
					}, {
					  label: "Target",
					  backgroundColor: "#d8d8d8",
					  data:targer_value
					  
					}
				  ]
				},
				options: {
				  title: {
					display: false,
					text: 'Population growth (millions)'
				  },
				  scales: {
							xAxes: [{
							stacked: false,
							barPercentage: 0.6,
							position: 'bottom',
							display: true,
							gridLines: {
								display: false,
								drawBorder: false,
							},
							ticks: {
								display: true, //this will remove only the label
								stepSize: 1,
							}
							}],
							yAxes: [{
								stacked: false,
								display: true,
								gridLines: {
									drawBorder: false,
									display: true,
									color: "#ddd",
									borderDash: [12, 2],
								},
								ticks: {
									beginAtZero: true,
									callback: function(value, index, values) {
									return '' + value;
									}
								},
							}]
						},
						legend: {
							display: false
						},
				}
			});
		}




		 
		}
	  });

	// return response_value_param;

}




function weekend_days_count() {

		var d = new Date();
		
		var getTot = new Date(d.getFullYear(), d.getMonth()+1, 0).getDate(); 		
		
		var sat = new Array();   
		var sun = new Array();   

		for(var i=1;i<=getTot;i++){    
		var newDate = new Date(d.getFullYear(),d.getMonth(),i)
		if(newDate.getDay()==0){  
		sun.push(i);
		}
		if(newDate.getDay()==6){   
		sat.push(i);
		}

		}		

		var count_of_days = sun.length + sat.length
		return count_of_days;



}

function daysInMonth_new(month,year) {
	
    return new Date(year, month, 0).getDate();
}



