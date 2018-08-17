$(function () {

    $('#sel_days,#city_id').on('change', function(){
        var days = $('#sel_days').val();
        var city_id = $('#city_id').val();
        location.href = "/statistics/user.php?days=" + days + "&city_id=" + city_id;
    });

    var days = eval($('#days').html());
    var total = eval($('#total').html());
    var news = eval($('#new').html());

    $('#daily_new_user').highcharts({
        title: {
            text: '每日新增下单客户',
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
        series: [{
            tooltip: {
                valueSuffix: '个'
            },
            name: '每日新下单用户',
            data: news
        }]
    });

    $('#daily_total_user').highcharts({
        title: {
            text: '每日下单客户总数',
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
        series: [{
            tooltip: {
                valueSuffix: '个'
            },
            name: '每日下单总用户',
            data: total
        }]
    });
});