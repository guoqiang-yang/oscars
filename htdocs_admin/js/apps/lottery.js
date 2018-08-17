(function(){
    function main(){
		$('.mark_has_send').on('click', onMarkHasSend);
    }

	// 删除订单
	function onMarkHasSend(ev) {
		var id = $(this).data('id'),
			para = {id: id};

		if (confirm('确认奖品已发放？')) {
			K.post('/activity/ajax/mark_has_send.php', para, _onMarkSuccess);
		}
	}

	function _onMarkSuccess(data) {
		if (data.url) {
			window.location.href = data.url;
		} else {
			window.location.reload();
		}
	}
			main();
})();