(function () {
	function main() {
		$('#print-order').click(_onPrintOrder);
	}

	function _onPrintOrder() {
		var doublePrint = $('#need_double_print').val();
		if (doublePrint) {
			alert('该用户为特殊用户，请打印两份配送单交给司机！');
		}

		var noPrivilegePrint = $('#no_privilege_print').val();
		if (noPrivilegePrint) {
			alert('该用户为特殊用户，请打印两份无优惠配送单交给司机！');
		}

		var oid = $(this).data('oid');
		var para = {oid : oid};

		K.post('/order/ajax/mark_order_as_printed.php', para, _onPrintOrderSucc);
	}

	function _onPrintOrderSucc(data) {
		window.print();
		console.log('print success');
	}

	main();

})();