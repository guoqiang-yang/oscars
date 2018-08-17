(function () {
	var time = 0;
	var isReminding = false;

	function main() {
		setInterval(function() {
			if (isReminding) {
				return false;
			}

			K.post('/order/ajax/get_new_oid.php', '', _onGetOidSucc);
		}, 10000);
	}

	function _onGetOidSucc(data) {
		var maxTime = parseInt($('#max-time').val());
		if (maxTime < 0) {
			isReminding = true;
			return false;
		}

		if (parseInt(data.time) > maxTime) {
			isReminding = true;

			setInterval(function() {
				time++;
				show();
			}, 1000);
			//alert('有新的订单需要处理');
			//window.location.reload();
		}
	}

	function show() {
		var title = document.title.replace("【有新订单】", "");

		if (time % 2 == 0) {
			document.title = "【有新订单】" + title;
		}
		else {
			document.title = title;
		}
	}

	//main();

})();