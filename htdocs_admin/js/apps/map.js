(function () {
	var orderList = eval('(' + $('#order_list').html() + ')');
	var i = 0;
	var sum = orderList.length;
	var time;

	//创建地图
	var map = new AMap.Map('container');
	//设置缩放，中心点
	map.setZoom(10);
	map.setCenter([116.39, 39.9]);

	//获取地址插件
	AMap.service('AMap.Geocoder', function () {//回调函数
		//实例化Geocoder
		geocoder = new AMap.Geocoder({
			city: "010"//城市，默认：“全国”
		});

		//南库
		geocoder.getLocation('鑫华祥物资仓库', function (status, result) {
			if (status === 'complete' && result.info === 'OK') {
				var marker = new AMap.Marker({
					icon: 'http://vdata.amap.com/icons/b18/1/2.png',
					position: [result.geocodes[0].location.lng, result.geocodes[0].location.lat],
					map: map
				});

				var content = '<div>三号库</div>' +
					'<div>鑫华祥物资仓库</div>';

				var infowindow = new AMap.InfoWindow({
					content: content,
					offset: new AMap.Pixel(0, -30),
					size: new AMap.Size(230, 0)
				})

				var clickHandle = AMap.event.addListener(marker, 'click', function () {
					infowindow.open(map, marker.getPosition())
				})
			} else {
				//获取经纬度失败
			}
		});

		//北库
		geocoder.getLocation('同鑫九鼎建材城', function (status, result) {
			if (status === 'complete' && result.info === 'OK') {
				var marker = new AMap.Marker({
					icon: 'http://vdata.amap.com/icons/b18/1/2.png',
					position: [result.geocodes[0].location.lng, result.geocodes[0].location.lat],
					map: map
				});

				var content = '<div>四号库</div>' +
					'<div>同鑫九鼎建材城</div>';

				var infowindow = new AMap.InfoWindow({
					content: content,
					offset: new AMap.Pixel(0, -30),
					size: new AMap.Size(230, 0)
				})

				var clickHandle = AMap.event.addListener(marker, 'click', function () {
					infowindow.open(map, marker.getPosition())
				})
			} else {
				//获取经纬度失败
			}
		});

		markAll(i);
	})

	AMap.plugin(['AMap.ToolBar', 'AMap.Scale'], function () {
		var toolBar = new AMap.ToolBar();
		var scale = new AMap.Scale();
		map.addControl(toolBar);
		map.addControl(scale);
	})

	function markAll(i) {
		if (i == sum) {
			clearTimeout(time);
		}

		_mark(orderList[i]);

		i = i + 1;
		time = setTimeout(markAll(i), 100);
	}

	function _mark(order) {
		geocoder.getLocation(order.address, function (status, result) {
			if (status === 'complete' && result.info === 'OK') {
				if (order.has_sand == 1) {
					var marker = new AMap.Marker({
						position: [result.geocodes[0].location.lng, result.geocodes[0].location.lat],
						map: map,
						icon: new AMap.Icon({
							//size: new AMap.Size(50, 50),  //图标大小
							image: "http://haocaisong.oss-cn-hangzhou.aliyuncs.com/static/has_sand.png"
						})
					});
				} else {
					var marker = new AMap.Marker({
						position: [result.geocodes[0].location.lng, result.geocodes[0].location.lat],
						map: map
					});
				}

				var content = '<div><a target="_blank" href="/order/order_detail.php?oid=' + order.oid + '">订单id:' + order.oid + '</a></div>' +
					'<div>联系人:' + order.contact_name + '</div>' +
					'<div>联系电话:' + order.contact_phone + '</div>' +
					'<div>配送日期:' + order.delivery_date + '</div>' +
					'<div>地址:' + order.address + '</div>';

				var infowindow = new AMap.InfoWindow({
					content: content,
					offset: new AMap.Pixel(0, -30),
					size: new AMap.Size(230, 0)
				})

				var clickHandle = AMap.event.addListener(marker, 'click', function () {
					infowindow.open(map, marker.getPosition())
				})
			} else {
				//获取经纬度失败
			}
		});
	}
})();