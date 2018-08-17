(function () {
    window.mapHandler = null;

    $(document).ready(function () {
        $('.address_results').on('click', selectAddress);
        initMap();
    });

})();

function selectAddress(evt) {
    var tgt = '';

    if ($(evt.target).hasClass('map_address')) {
        tgt = $(evt.target);
    } else if ($(evt.target).parent().hasClass('map_address')) {
        tgt = $(evt.target).parent();
    }

    if (tgt.length == 0) {
        alert('选择失败！请重试！');
        return false;
    }

    var para = {
        title: tgt.find('.title').html(),
        address: tgt.find('.addr').html(),
        lng: tgt.attr('data-lng'),
        lat: tgt.attr('data-lat'),
        city: tgt.attr('data-city')
    };

    // suggest 没有返回经纬度坐标，需要在查询一次
    if (tgt.attr('data-from') == 'suggest') {
        var local = new BMap.LocalSearch(mapHandler, { //智能搜索
            onSearchComplete: function () {
                var poiInfo = local.getResults().getPoi(0);    //获取第一个智能搜索的结果

                para.address = poiInfo.address;
                para.lng = poiInfo.point.lng;
                para.lat = poiInfo.point.lat;

                _searchAddressCallback(para);
            }
        });
        local.search(para.address);
    } else { // map 有足够的信息
        _searchAddressCallback(para);
    }

}

function _searchAddressCallback(paraFromMap) {
    var paras = {};
    paras.city_inMap = paraFromMap.city;

    $('#community_name').val(paraFromMap.title);
    $('#address').val(paraFromMap.address);    
    var cid = $('#cid').val();
    var uid = $('#uid').val();
    var oid = $('#oid').val();
    var contact_phone = $('#contact_phone').val();
    var contact_name = $('#contact_name').val();
    var select_city = $('#select_city').val();
    var select_district = $('#select_district').val();
    var select_area = $('#select_area').val();
    var from = $('#from').val();
    var platform = $('#platform').val();
    var version = $('#version').val();
    var delivery_type = $('#delivery_type').val();
    var params = {
    	cid : cid,
    	order_uid : uid,
    	oid : oid,
    	contact_phone : contact_phone,
    	contact_name : contact_name,
    	select_city : select_city,
    	select_district : select_district,
    	select_area : select_area,
    	from : from,
    	community_name : paraFromMap.title,
    	community_address : paraFromMap.address,
    	community_lat : paraFromMap.lat,
    	community_lng : paraFromMap.lng,
    	platform : platform,
    	version : version,
    	delivery_type : delivery_type
    };
    K.location('/order/add_user_address_h5.php', params);
}

function initMap() {
    $(document).ready(function () {
        // 显示默认位置地图
        var city = parseInt($('#city_id').val());
        if (city == 500) {
            _showMap('allmap', {lng: 106.581293, lat: 29.564282}, 17);
        } else if (city == 120) {
            _showMap('allmap', {lng: 117.220074, lat: 39.142245}, 17);
        } else if (city == 1310) {
            _showMap('allmap', {lng: 116.713851, lat: 39.51733}, 17);
        } else {
            _showMap('allmap', {lng: 116.404, lat: 39.915}, 17);
        }

        // 获取用户当前位置
        _showCurrPosition();

        // 注册拖动事件，并在地图标记坐标，返回坐标点
        _markCenterPoint4Dragend();

        // suggest 搜素
        _suggestInMap();
    });
}

function _showMap(id, pos, scale) {
    mapHandler = new BMap.Map(id);
    mapHandler.centerAndZoom(new BMap.Point(pos.lng, pos.lat), scale);
    mapHandler.enableScrollWheelZoom(true);

    // 添加带有定位的导航控件
    var navigationControl = new BMap.NavigationControl({
        // 靠左上角位置
        anchor: BMAP_ANCHOR_TOP_LEFT,
        // LARGE类型
        type: BMAP_NAVIGATION_CONTROL_LARGE,
        // 启用显示定位
        enableGeolocation: true
    });
    mapHandler.addControl(navigationControl);

    // 添加定位控件
    var geolocationControl = new BMap.GeolocationControl();
    geolocationControl.addEventListener("locationSuccess", function (e) {
        // 定位成功事件
        _showNearCommunitys(e.point);
    });
    geolocationControl.addEventListener("locationError", function (e) {
        // 定位失败事件
        alert('定位失败！');
    });
    mapHandler.addControl(geolocationControl);
}

function _showCurrPosition() {
    var geolocation = new BMap.Geolocation();
    geolocation.getCurrentPosition(function (r) {
        if (this.getStatus() == BMAP_STATUS_SUCCESS) {
            var mk = new BMap.Marker(r.point);
            mapHandler.panTo(r.point);
            mapHandler.addOverlay(mk);

            _showNearCommunitys(r.point);
        }
        else {
            alert('对不起，定位失败！请使用搜素查找小区（' + this.getStatus() + ')！');
        }
    }, {enableHighAccuracy: true});

}

function _markCenterPoint4Dragend() {

    mapHandler.addEventListener("dragend", function () {
        mapHandler.clearOverlays();

        var center = mapHandler.getCenter();
        var mk = new BMap.Marker(center);
        mapHandler.addOverlay(mk);

        _showNearCommunitys(center);
    });
}

function _showNearCommunitys(poi) {
    var showHtml = '';

    // 当前坐标地址
    var geoc = new BMap.Geocoder();
    geoc.getLocation(poi, function (rs) {
        if (rs.surroundingPois.length > 0) {
            var data = {
                from: 'map',
                city: rs.surroundingPois[0].city,
                title: rs.surroundingPois[0].title,
                address: rs.address,
                lat: poi.lat,
                lng: poi.lng,
                distance: 0,
                flag: 'my_pos'
            };

            showHtml += _renderPage(data);
        }
    });

    // 检索周边地址
    var local = new BMap.LocalSearch(mapHandler);
    var mPoint = new BMap.Point(poi.lng, poi.lat);
    local.searchNearby(["小区", "大厦", "商场", "医院"], mPoint, 1000);
    local.setSearchCompleteCallback(function (results) {
        for (var i = 0, len = results.length; i < len; i++) {
            for (var j = 0; j < results[i].getCurrentNumPois(); j++) {
                var showData = {
                    from: 'map',
                    city: results[i].getPoi(j).city,
                    title: results[i].getPoi(j).title,
                    address: results[i].getPoi(j).address,
                    lat: results[i].getPoi(j).point.lat,
                    lng: results[i].getPoi(j).point.lng,
                    distance: mapHandler.getDistance(poi, results[i].getPoi(j).point).toFixed(2)
                };

                showHtml += _renderPage(showData);
            }
        }

        document.getElementById("map_search_result").innerHTML = showHtml;
    });

}

function _suggestInMap() {
    var ac = new BMap.Autocomplete({    //建立一个自动完成的对象
        input: "community_name",
        location: mapHandler,
        onSearchComplete: function () {
            ac.hide();

            var showHtml = '';
            var results = ac.getResults();
            for (var i = 0; i < results.getNumPois(); i++) {
                var poiInfo = results.getPoi(i);

                if (typeof poiInfo == 'undefined' || poiInfo.city.length == 0) continue;

                var data = {
                    from: 'suggest',
                    city: poiInfo.city,
                    lat: 0,
                    lng: 0,
                    title: poiInfo.business,
                    address: poiInfo.province + poiInfo.city + poiInfo.district + poiInfo.street + poiInfo.business,
                    distance: 0
                };
                showHtml += _renderPage(data);
            }

            if (showHtml.length > 0) {
                $("#suggest_4_address").css('display', 'block');
                $("#map_4_address").css('display', 'none');
                $("#suggest_4_address").html(showHtml);
            }
            else {
                $('#suggest_4_address').css('display', 'none');
                $('#map_4_address').css('display', 'block');
            }
        }
    });

}

function _renderPage(data) {

    var distance = '';
    if (data.distance > 0) {
        data.distance > 1000 ? (data.distance / 1000).toFixed(2) + '公里' : data.distance + '米';
    }

    var showPos = data.flag == 'my_pos' ? '[当前位置]' : '';
    return '<li class="map_address" data-city="' + data.city + '" data-lng="' + data.lng + '" ' + 'data-lat="' + data.lat + '" ' + 'data-from="' + data.from + '">' +
        '<p class="title">' + showPos + data.title + '</p>' +
        '<p class="addr">' + data.address + '</p>' +
        '<span>' + distance + '</span>' +
        '</li>';
}