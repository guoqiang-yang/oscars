(function () {

	function main() {
		//查看距离及运费
		onShowDistanceFee();
	}

	function onShowDistanceFee() {
		var oid = $('#oid').val();
		var para = {oid: oid};

		K.post('/order/ajax/show_distance_fee.php', para, _onShowDistanceFeeSucc);
	}

	function _onShowDistanceFeeSucc(data) {
		$('#distance_fee_content').html(data.html);
	}

	main();

})();