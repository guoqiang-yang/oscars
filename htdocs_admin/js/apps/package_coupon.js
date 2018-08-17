(function() {

	function main() {
		$('.send_package_coupon').click(_onSendPackageCouponTo);
	}

	function _onSendPackageCouponTo(ev) {
		var cid = $(this).data('cid');
		var para = {cid: cid};

		K.post('/crm2/ajax/send_package_coupon_to.php', para, _onSendSuccess);
	}
	function _onSendSuccess(data) {
		alert('发放完毕！');

		window.location.reload();
	}

	main();

} )();