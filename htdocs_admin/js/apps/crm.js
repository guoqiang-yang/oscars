(function () {
	var cid = 0;
	var tid = 0;

	function main() {
		$('#btn_save').click(_onSaveCustomer);
		$('._j_show_apply_coupon').click(showApplyCoupon);
		$('#_js_apply_coupon').click(applyCoupon);
		$('._j_audit_apply_coupon').click(auditApplyCoupon);
		$('.edit_tracking').click(_onEditTracking);
		$('#_j_btn_edit_tracking').click(_onSubmitTracking);
		$('.note_history').click(_onShowNoteHistory);
		$('#auto_save_customer').click(_onAutoSaveCustomer);
	}

	function _onSaveCustomer() {
		var para = {
			cid: $('input[name=cid]').val(),
			url: $('input[name=last_url]').val(),
			name: $('input[name=name]').val(),
			contact_name: $('input[name=contact_name]').val(),
			contact_uid: $('input[name=contact_uid]').val(),
			mobile: $('input[name=mobile]').val(),
			phone: $('input[name=phone]').val(),
			district: $('select[name=district]').val(),
			area: $('select[name=area]').val(),
			address: $('input[name=address]').val(),
			code: $('input[name=code]').val(),
			member_date: $('input[name=member_date]').val(),
			sales_suid: $('select[name=sales_suid]').val(),
			sales_suid2: $('select[name=sales_suid2]').val(),
			note: $('textarea[name=note]').val(),
			status: $('select[name=status]').val(),
			hometown: $('select[name=hometown]').val(),
			source: $('select[name=source]').val(),
			rival_desc: $('select[name=rival_desc]').val(),
			payment_days: $('input[name=payment_days]').val(),
			bid: $('input[name=bid]').val(),
            qq: $('input[name=qq]').val(),
            weixin: $('input[name=weixin]').val()
		};		
		K.post('/crm/ajax/save_customer.php', para, _onSaveCustomerSucss);
	}

	function _onSaveCustomerSucss(data) {
		if (data.url) {
			window.location.href = data.url;
		}else{
			window.location.href = "/crm/customer_list.php";
		}
	}

	// 申请优惠券
	function showApplyCoupon(){
		var box = $('#applyCouponForCustomer');
		box.modal();
		
		var hCustomerInfo = box.find('input[name=cinfo]');
		var cid = $(this).data('cid'),
			cinfo = $(this).data('cname') +' （ID：' + cid +'）';
		hCustomerInfo.val(cinfo);
		hCustomerInfo.attr('data-cid', cid);
	}
	
	function applyCoupon(){
		var box = $('#applyCouponForCustomer');
		var param = {
			cid: box.find('input[name=cinfo]').attr('data-cid'),
			num: box.find('select[name=num]').val(),
			note: box.find('textarea[name=note]').val()
		};
			
		// check params
		if (isNaN(parseInt(param.cid))) {alert('客户ID不能为空'); return false;}
		if (param.note == '') {alert('请填写申请原因'); return false;}
		
		K.post('/crm/ajax/apply_coupon.php', param, function(ret){
			if (ret.errno == 1){
				box.modal('hide');
				alert('申请成功');
			} else {
				alert('申请失败，销售人员只能为自己的客户申请!');
			}
		});	
	}
	
	// 审核 优惠券申请
	function auditApplyCoupon(){
		var id = $(this).data('id'),
			status = $(this).data('status'),
			msg = $(this).parent().find('span').data('msg');
		
		var _msg = '您是否确定 “' + (status==3?'批准':'驳回') + '” \n' + msg;
		if (!confirm(_msg)) {
			return;
		}

		var para = {id: id, status: status};
		K.post('/crm/ajax/audit_apply_coupon.php', para, function(ret){
			alert('操作成功');
			window.location.reload();
		});
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
		K.post('/crm/ajax/edit_tracking.php', para, _onSubmitTrackingSucc);
	}

	function _onSubmitTrackingSucc() {
		$('#dlgAddProduct').modal('hide');
		location.reload();
	}

	function _onShowNoteHistory() {
		var cid = $(this).data('cid');
		var para = {cid : cid};

		K.post('/crm/ajax/dlg_note_history.php', para, _onShowNoteHistorySucc);
	}
	function _onShowNoteHistorySucc(data) {
		$('#note-history-container').html('').append($(data.html));
	}

	function _onAutoSaveCustomer() {
		if (confirm('确定要执行此操作吗？\n\n该操作会：\n1、用该手机号创建一个全新的用户，密码默认手机号后6位；\n2、跳转到该用户的下单页面;')) {
			var para = {
				mobile: $('input[name=mobile]').val(),
			};
			K.post('/crm/ajax/auto_save_customer.php', para, _onAutoSaveCustomerSucss);
		}
	}

	function _onAutoSaveCustomerSucss(data) {
		window.location.href = "/order/add_order.php?cid=" + data.cid;
	}
	

	main();

})();