(function () {

	function main() {

		showMap();

		$('#_j_btn_save_fee').on('click', onSaveDistanceFee);
	}

	function showMap() {
		var map = new BMap.Map("map");
		map.centerAndZoom(new BMap.Point(116.404, 39.915), 12);

		// 添加带有定位的导航控件
		var navigationControl = new BMap.NavigationControl({
			// 靠左上角位置
			anchor: BMAP_ANCHOR_TOP_LEFT,
			// LARGE类型
			type: BMAP_NAVIGATION_CONTROL_LARGE,
			// 启用显示定位
			enableGeolocation: true
		});
		map.addControl(navigationControl);

		var transit = new BMap.DrivingRoute(map, {
			renderOptions: {
				map: map,
				panel: "r-result",
				enableDragging : true //起终点可进行拖拽
			},
		});

		var originLat = $('#origin-lat').val();
		var originLng = $('#origin-lng').val();
		var origin = new BMap.Point(originLng, originLat);
		console.log(origin);

		var destLat = $('#dest-lat').val();
		var destLng = $('#dest-lng').val();
		var dest = new BMap.Point(destLng, destLat);
		console.log(dest);

		transit.search(origin, dest);
	}

	function onSaveDistanceFee() {
		var para = {
			distance: $('#distance').val(),
			wid: $('#wid').val(),
			community_id: $('#community_id').val(),
			status: $('.status:checked').val(),
			note: $('#note').val(),
			old_distance: $('#distance').attr('data-distance'),
		};

		$(this).attr('disabled', true);
		K.post('/order/ajax/save_distance_fee.php', para, onSaveSucc);
	}

	function onSaveSucc(data) {
		alert('保存成功！');
		window.location.reload();
	}

	main();

})();