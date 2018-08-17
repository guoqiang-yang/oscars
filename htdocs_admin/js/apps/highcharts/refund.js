/**
 * Created by qihua on 17/2/21.
 */

var reasonData = $.parseJSON($('#reason_data').html());
var reasonDetailData = $.parseJSON($('#reason_detail_data').html());

$('#refund_reason').highcharts({
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false
    },
    title: {
        text: '退货原因一级分类'
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
                format: '<b>{point.name}</b>: {point.num}({point.percentage:.1f}%)<br /><p><a target="_blank" href="/aftersale/refund_list.php?reason_type={point.reason_type}&from_date={point.from_date}&end_date={point.end_date}">查看退款单</a></p>',
                style: {
                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                }
            },
            events: {
                click: function(e) {
                    var index = e.point.index;

                    console.log(e);

                    $('#refund_reason_detail').highcharts({
                        chart: {
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false
                        },
                        title: {
                            text: '退货原因二级分类'
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
                                    format: '<b>{point.name}</b>: {point.num}({point.percentage:.1f}%)<br /><p><a target="_blank" href="/aftersale/refund_list.php?reason_type={point.reason_type}&reason={point.reason}&from_date={point.from_date}&end_date={point.end_date}">查看退款单</a></p>',
                                    style: {
                                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                                    }
                                }
                            }
                        },
                        series: [{
                            type: 'pie',
                            name: '退货原因',
                            data: reasonDetailData[index]
                        }]
                    });
                }
            },
        }
    },
    series: [{
        type: 'pie',
        name: '退货原因',
        data: reasonData
    }]
});

var reasonTypeData = $.parseJSON($('#reason_type_data').html());
console.log(reasonTypeData);
$('#refund_type').highcharts({
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false
    },
    title: {
        text: '退货类型占比图'
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
                format: '<b>{point.name}</b>: {point.num}({point.percentage:.1f}%)<br /><p><a target="_blank" href="/aftersale/refund_list.php?type={point.type}&from_date={point.from_date}&end_date={point.end_date}">查看退款单</a></p>',
                style: {
                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                }
            }
        }
    },
    series: [{
        type: 'pie',
        name: '退货类型',
        data: reasonTypeData
    }]
});

var widRefundKeys = $.parseJSON($('#wid_refund_data_key').html());
var widRefundVals = $.parseJSON($('#wid_refund_data_val').html());
$('#refund_by_wid').highcharts({
    chart: {
        type: 'bar'
    },
    title: {
        text: '库房退货率'
    },
    xAxis: {
        categories: widRefundKeys,
        title: {
            text: null
        }
    },
    yAxis: {
        min: 0,
        title: {
            text: '退货率',
            align: 'high'
        },
        labels: {
            overflow: 'justify'
        }
    },
    tooltip: {
        valueSuffix: '%'
    },
    plotOptions: {
        bar: {
            dataLabels: {
                enabled: true
            }
        }
    },
    credits: {
        enabled: false
    },
    series: [{
        name: '退货率',
        data: widRefundVals
    }]
});


(function() {

    $('.check_brand_products').on('click', showBrandProducts);

    function showBrandProducts() {
        var bid = $(this).data('bid');

        $('.brnad_products').each(function(i) {
            $(this).css('display', 'none');
        });

        $('#brand_product_' + bid).css('display', '');
    }
} )();