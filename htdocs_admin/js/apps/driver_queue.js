(function () {

	function main() {
		$('#change_check_in').on('click', changeCheckIn);
		$('#change_accept').on('click', changeAccept);
		$('.change_queue_type').on('click', changeDid);
		$('.clear_refuse_num').on('click', clearRefuseNum);
		$('.clear_queue_status').on('click', clearQueueStatus);
	}

	function changeDid() {
		var did = $(this).data('did');

		$('#check_did').val(did);
	}

	function changeCheckIn() {
		var newType = $('input[name=new_type]:checked').val();
		var wid = $('#check_in_wid').val();
		var did = $('#check_did').val();

		if (K.isEmpty(newType)) {
			alert('请选择签到状态');
			return false;
		}

		if (parseInt(newType) == 1) {
			if (K.isEmpty(wid) || parseInt(wid) == 0) {
				alert('请选择签到仓库');
				return false;
			}
		}

		if (K.isEmpty(did) || parseInt(did) == 0) {
			alert('请选择司机');
			return false;
		}

		var para = {new_type: newType, did: did, wid: wid};

		$(this).attr('disabled', true);
		K.post('/logistics/ajax/change_check_in.php', para, onSaveSucc);
	}

	function onSaveSucc(data) {
		alert('更改签到状态成功！');
		window.location.reload();
	}

	function changeAccept() {
		var newType = $('input[name=new_type]:checked').val();
		var did = $('#check_did').val();

		if (K.isEmpty(newType)) {
			alert('请选择接单状态');
			return false;
		}

		if (K.isEmpty(did) || parseInt(did) == 0) {
			alert('请选择司机');
			return false;
		}

		var para = {new_type: newType, did: did};

		$(this).attr('disabled', true);
		K.post('/logistics/ajax/change_accept_order.php', para, onSaveSucc2);
	}

	function onSaveSucc2(data) {
		alert('更改接货状态成功！');
		window.location.reload();
	}

	function clearRefuseNum() {
		if (confirm('确定清除该司机的拒单次数？')) {
			var did = $(this).data('did');

			if (parseInt(did) == 0) {
				alert('请选择司机');
				return false;
			}

			var para = {did: did};

			$(this).attr('disabled', true);
			K.post('/logistics/ajax/clear_refuse_num.php', para, onSaveSucc3);
		}
	}

	function onSaveSucc3(data) {
		alert('清除拒单次数成功！');
		window.location.reload();
	}

	function clearQueueStatus() {
		var	para = {
			did: $(this).attr('data-did'),
            line_id: $(this).attr('data-line-id'),
		}
		K.post('/logistics/ajax/clear_queue_status.php', para, function () {
			alert('清除队列状态成功！');
			window.location.reload();
        })
    }

	main();

})();