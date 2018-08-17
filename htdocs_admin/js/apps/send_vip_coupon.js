(function() {

	function main() {
		$('.send_vip_coupon').click(_sendVipCoupon);
		$('#send_btn').click(_realSendVipCoupon);
	}

	function _sendVipCoupon(ev) {
		var cid = $(this).data('cid');
		$('#send_to_cid').val(cid);

	}

	function _realSendVipCoupon() {
		var cid = $('#send_to_cid').val();
		var coupon_id = $('#coupon_id').val();
		var num = $('#coupon_num').val();
		var para = {cid: cid, num: num, coupon_id: coupon_id};

		if (confirm('确定要给用户（cid：' + cid + '）发放' + num + '张'+ $("#coupon_id").find("option:selected").text() +'吗？')) {
			K.post('/crm2/ajax/send_vip_coupon.php', para, _onSendSuccess);
		}
	}


	function _onSendSuccess(data) {
		alert('发放完毕！');

		window.location.reload();
	}

	main();

} )();