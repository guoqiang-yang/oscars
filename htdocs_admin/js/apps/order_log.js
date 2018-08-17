(function () {

	function main() {
		var oid = $('#oid').val();
		var para = {oid: oid};

		K.post('/order/ajax/get_order_log.php', para, _onGetOrderLogSucc);
	}

	function _onGetOrderLogSucc(data) {
		$('#order_action_list').html(data.html);
	}

	main();

})();