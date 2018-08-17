(function () {

	function main() {
		$('#_j_cal_freight').click(_onCalFreight);
		$('#_j_cal_carry_fee').click(_onCalCarryFee);
	}

	function _onCalFreight() {
		var curCity = $('#select-city').val();
		var curDistinct = $('#select-district').val();
		var curArea = $('#select-area').val();
		var price = $('#order-price').val();
		var oid = $(this).data('oid');
        var cmid = $('input[name=community_id]').val();
        var deliveryType = $('input[name=delivery_type]:checked').val();

		if (curCity == 0) {
			alert('请选择城市！');
			return false;
		}
		if (curDistinct == 0) {
			alert('请选择城区！');
			return false;
		}

		var para = {oid: oid, price: price, city: curCity, district: curDistinct, area: curArea, cmid: cmid, delivery_type: deliveryType};
		K.post('/order/ajax/cal_freight.php', para, _onCalFreightSucc);
	}

	function _onCalFreightSucc(data) {
		var freight = parseInt(data.freight) / 100;

		$('#freight').val(freight);

		alert('运费为：' + freight + '元！\n\n已自动写入“客户运费”输入框，如果有问题可以随时修正。');
	}

	function _onCalCarryFee() {
		var service = parseInt($('#service').val());
		var floorNum = parseInt($('#floor-num').val());
		var oid = $(this).data('oid');

		if (service == 2 && floorNum == 0) {
			alert('请选择楼层！');
			return false;
		}

		var para = {oid: oid, service: service, floor_num: floorNum};
		K.post('/order/ajax/cal_carry_fee.php', para, _onCalCarryFeeSucc);
	}

	function _onCalCarryFeeSucc(data) {
		var carryFee = parseInt(data.carry_fee) / 100;

		$('#carry_fee').val(carryFee);

		alert('搬运费为：' + carryFee + '元！\n\n已自动写入“搬运费”输入框，如果有问题可以随时修正。');
	}

	main();

})();