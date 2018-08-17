$(function () {

	var days = eval($('#days').html());
	var lines = eval($('#lines').html());
	var title = $('#title').val();

	$('#daily_order_num').highcharts({
		title: {
			text: title,
			x: -20 //center
		},
		subtitle: {
			text: '',
			x: -20
		},
		xAxis: {
			categories: days
		},
		yAxis: {
			title: {
				text: ''
			},
			plotLines: [{
				value: 0,
				width: 1,
				color: '#808080'
			}]
		},
		legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'middle',
			borderWidth: 0
		},
		series: lines
	});
});