$(function () {

    $('#sel_days,#city_id').on('change', function(){
        var days = $('#sel_days').val();
        var city_id = $('#city_id').val();
        location.href = "/statistics/logistics.php?days=" + days + "&city_id=" + city_id;
    });

    var days = eval($('#days').html());
    var total = eval($('#total').html());
    var price = eval($('#price').html());

    $('#daily_order_num').highcharts({
        title: {
            text: '补贴搬运费毛收入占比',
            x: -20 //center
        },
        subtitle: {
            text: '搬运费占毛收入的百分比',
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
        series: [{
            tooltip: {
                valueSuffix: '%'
            },
            name: '百分比',
            data: total
        }]
    });

    $('#daily_order_price').highcharts({
        title: {
            text: '补贴运费毛收入占比',
            x: -20 //center
        },
        subtitle: {
            text: '运费占毛收入的百分比',
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
        series: [{
            tooltip: {
                valueSuffix: '%'
            },
            name: '百分比',
            data: price
        }]
    });
});