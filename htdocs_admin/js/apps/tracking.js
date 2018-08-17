(function () {
    
    function main(){

        // 客户回访
        _init_customerTracking();
        $('#_j_position_customer_tracking').on('click', customerTracking);
		$('.edit_tracking').click(_onEditTracking);
		$('#_j_btn_edit_tracking').click(_onSubmitTracking);
		$('.note_history').click(_onShowNoteHistory);
        $('#delay_tracking').click(_onHideTrackingContent);
        $('#add_tracking').click(_onShowTrackingContent);
        $('#tracking').click(_onShowTrackingTime);
        $('#no_tracking').click(_onHideTrackingTime);
    }

    // 客户回访
    function _init_customerTracking() {
        var hCtracking = $('#_j_position_customer_tracking');
        var cid = hCtracking.attr('data-cid');
        
        if (hCtracking.length == 0 || typeof cid == 'undefined') {
            return;
        }
        
        _getCustomerTrackingList(cid, 0);
    }
    
    function _getCustomerTrackingList(cid, start) {
        var para = {cid:cid, start:start};
        
        K.post('/crm2/ajax/get_customer_tracking.php', para, function(ret) {
            $('#_j_position_customer_tracking').html(ret.html);
        });
    }
    
    function customerTracking(event) {
        var target = $(event.target);
        
        if (target.has('._j_pagetruning_tracking')){
            var cid = $('#_j_position_customer_tracking').attr('data-cid'),
            start = target.attr('data-start');
    
            _getCustomerTrackingList(cid, start);
        }
    }
    
    function _onEditTracking() {
		cid = $(this).data('cid');
	}
    
    function _onSubmitTracking() {
		var dueDate = $('#tracking_due_date').val();
		var note = $('#note').val();
		var needTracking = $('input[name=need_tracking]:checked').val();

		if (needTracking == 1 && K.isEmpty(dueDate))
		{
			alert('回访日期必须选！');
			return false;
		}

		var para = {cid: cid, due_date: dueDate, need_tracking: needTracking, note: note};
		K.post('/crm2/ajax/edit_tracking.php', para, function(){
            window.location.reload();
        });
	}
    
    function _onShowNoteHistory() {
		var cid = $(this).data('cid');
		var para = {cid : cid};

		K.post('/crm2/ajax/dlg_note_history.php', para, function(data){
            $('#note-history-container').html('').append($(data.html));
        });
	}

    function _onHideTrackingContent() {
        $('#tracking_content').css('display', 'none');
    }

    function _onShowTrackingContent() {
        $('#tracking_content').css('display', '');
    }

    function _onShowTrackingTime() {
        console.log('aaaaa');
        console.log($('#tracking_time'));
        $('#tracking_time').css('display', '');
    }

    function _onHideTrackingTime() {
        console.log('aaaaa');
        console.log($('#tracking_time'));
        $('#tracking_time').css('display', 'none');
    }
    
    main();
})();

