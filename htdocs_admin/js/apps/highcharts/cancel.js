$(function () {

    var days = eval($('#days').html());
    var lines = eval($('#lines').html());
    var title = '订单取消数量';

    $('#daily_cancel_num').highcharts({
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

    var cancelReasonData = $.parseJSON($('#cancel_reason_data').html());
    $('#cancel_reason').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
            text: '取消原因'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.num}({point.percentage:.1f}%)</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    useHTML: true,
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.num}({point.percentage:.1f}%)<br />',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            type: 'pie',
            name: '取消原因',
            data: cancelReasonData
        }]
    });
});