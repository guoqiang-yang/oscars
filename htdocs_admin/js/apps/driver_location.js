/**
 * Created by joker on 16/12/14.
 */
$(function () {

    map = window.map || new BMap.Map("mapContainer");

    function addClickHandler(content,marker){
        marker.addEventListener("click",function(e){
                openInfo(content,e)}
        );
    }
    function openInfo(content,e){
        var p = e.target;
        var point = new BMap.Point(p.getPosition().lng, p.getPosition().lat);
        var infoWindow = new BMap.InfoWindow(content,opts);  // 创建信息窗口对象
        map.openInfoWindow(infoWindow,point); //开启信息窗口
    }


    var points = $('#drivers').val();
    var city_name = $('#cityName').val();
    data_info = eval(points);
    center = eval(city_name);
    map.enableScrollWheelZoom();
    map.addControl(new BMap.NavigationControl({anchor:BMAP_ANCHOR_TOP_RIGHT}));
    map.addControl(new BMap.ScaleControl({anchor:BMAP_ANCHOR_BOTTOM_RIGHT}));
    var opts = {
        width : 20,     // 信息窗口宽度
        height: 5,     // 信息窗口高度
        enableMessage:false//设置允许信息窗发送短息
    };
    for(var i=0;i<data_info.length;i++){
        for (var j=0;j<data_info[i].length;j++)
        {
            var icon = new BMap.Icon(data_info[i][j]['imgurl'], new BMap.Size(19,29));
            var pt = new BMap.Point(data_info[i][j]['location'][0],data_info[i][j]['location'][1]);
            var marker = new BMap.Marker(pt, {icon:icon});  // 创建标注
            var content = data_info[i][j]['content'];
            map.addOverlay(marker);               // 将标注添加到地图中
            addClickHandler(content,marker);
        }
    }
    function mapInit() {

        var point = new BMap.Point(center[0], center[1]);
        map.centerAndZoom(point, 12);
        map.enableScrollWheelZoom();
    }
    mapInit();
    map.setMaxZoom(18);

})