$(function () {

    $('#sel_days,#city_id').on('change', function(){
        var days = $('#sel_days').val();
        var city_id = $('#city_id').val();
        location.href = "/statistics/cate.php?days=" + days + "&city_id=" + city_id;
    });

    var profit = eval($('#profit').html());
    var price = eval($('#price').html());

    $('#product_price').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
            text: '品类销售额分布'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b><br />共：{point.y}元'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %（共{point.y}元）'
                }
            }
        },
        series: [{
            type: 'pie',
            name: '销售额占比',
            data: price
        }]
    });

    $('#product_profit').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
            text: '品类毛收入分布'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>: <br/>共：{point.y}元'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    format: '<b>{point.name}</b>: {point.percentage:.1f} （共{point.y}元）%'
                }
            }
        },
        series: [{
            type: 'pie',
            name: '毛收入占比',
            data: profit
        }]
    });
});