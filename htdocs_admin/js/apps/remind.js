(function () {
	var cid = 0;

	function main() {
		$('.remind_confirm').click(_onConfirmRemind);
		$('#_j_btn_confirm_remind').click(_onSaveConfirmRemind);
		$('.note-history').click(_onShowNoteHistory);
	}

	function _onConfirmRemind() {
		cid = $(this).data('cid');
	}

	function _onSaveConfirmRemind() {
		var dueDate = $('#payment_due_date').val();
		var note = $('#note').val();

		if (K.isEmpty(dueDate))
		{
			alert('结账日期必须选！');
			return false;
		}

		var para = {cid: cid, due_date: dueDate, note: note};
		K.post('/finance/ajax/remind_confirm.php', para, _onRemindConfirmSuccess);
	}

	function _onRemindConfirmSuccess(data)
	{
		location.reload();
	}

	// 选择商品
	function _onShowNoteHistory() {
		var cid = $(this).data('cid');
		var para = {cid : cid};

		K.post('/finance/ajax/dlg_note_history.php', para, _onShowNoteHistorySucc);
	}
	function _onShowNoteHistorySucc(data) {
		$('#note-history-container').html('').append($(data.html));
	}

	main();

})();