/**
 * Created by qihua on 17/2/21.
 */

$('#order_os').highcharts({
    data: {
        table: 'order_os_data'
    },
    chart: {
        type: 'column'
    },
    title: {
        text: '出库订单来源占比'
    },
    yAxis: {
        allowDecimals: false,
        title: {
            text: '%',
            rotation: 0
        }
    },
    tooltip: {
        formatter: function () {
            return '<b>' + this.series.name + '</b><br/>' +
                this.point.y + '% ' + this.point.name.toLowerCase();
        }
    },
    plotOptions: {
        column: {
            //shadow: false,            //不显示阴影
            dataLabels: {                //柱状图数据标签
                enabled: true,              //是否显示数据标签
                //color: '#e3e3e3',        //数据标签字体颜色
                formatter: function () {        //格式化输出显示
                    return (this.y) + '%';
                }
            }
        }
    }
});

$('#delivery_warehouse').highcharts({
    data: {
        table: 'delivery_warehouse_data'
    },
    chart: {
        type: 'column'
    },
    title: {
        text: '各库房出库订单量'
    },
    yAxis: {
        allowDecimals: false,
        title: {
            text: '单',
            rotation: 0
        }
    },
    tooltip: {
        formatter: function () {
            return '<b>' + this.series.name + '</b><br/>' +
                this.point.y + '单 ' + this.point.name.toLowerCase();
        }
    },
    plotOptions: {
        column: {
            //shadow: false,            //不显示阴影
            dataLabels: {                //柱状图数据标签
                enabled: true,              //是否显示数据标签
                //color: '#e3e3e3',        //数据标签字体颜色
                formatter: function () {        //格式化输出显示
                    return (this.y) + '';
                }
            }
        }
    }
});