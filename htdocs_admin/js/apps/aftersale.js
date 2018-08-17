(function () {

	function main() {
		$('#btn_save').on('click', saveInfo);
		$('#btn_deal_save').on('click', saveLogInfo);
/*		$('#btn_deal').on('click', deal);
		$('.finish_task').on('click', finish);*/
		$('.content').on('click', showContent);
		$('#type').on('change', changeType);
		$('#duty_department').on('change', changeDepartment);
		$('#fb_type').on('change', changeFbType);
		$('#deal_method').on('change', dealMethod);
		$('input:radio').on('click', changeAssign);
		$('#_adtask_change_role').on('change', function(){
			var role = $(this).val();
			$('#exec_suid').html(changeRole(role));
		});
		// 分类切换
		$('#_adtask_change_objtype').on('change', function(){
			var objtype = $(this).val();
			$('#short_desc').html(changeCateOfShortDesc(objtype));
		});

		$('#short_desc').on('change', function(){
			var objtype = $(this).val();
			$('#short_desc').html(changeShortDesc(objtype));
		});
	}

	function dealMethod() {
		var method = $('#deal_method').val();
			switch (method) {
				//未处理完成
				case '1':
					$('#deal_assign').css('display', '');
					break;
				//处理完成
				case '2':
					$('#deal_assign').css('display', 'none');
					$('#deal_assign_container').css('display', 'none');
					break;
				//尚未完成
				case '3':
					$('#deal_assign').css('display', '');
					break;
				//关闭工单
				case '4':
					$('#deal_assign').css('display', 'none');
					break;
				default:
					$('#deal_assign').css('display', 'none');
					$('#deal_done').css('display', '');
					$('#deal_assign_container').css('display', 'none');
			}
		$('input:radio:checked').attr('checked', false);
	}

	function changeRole(role){

		var staffsHtml = '<option value="0">请选择</option>';
		var allStaffs = eval('(' + $('input[name=all_staffs]').val() + ')');

		for(var _role in allStaffs){
			if (_role == role){
				for(var i in allStaffs[_role]){
					staffsHtml += '<option value="'+allStaffs[_role][i].suid+'">'+allStaffs[_role][i].name+'</option>'
				}
			}
		}

		return staffsHtml;
	}

	function changeShortDesc(shortdesc) {
		switch (shortdesc){
			case '12':
				$('#type_container').css('display', '');
				$('#type_label').text('退货单ID：');
				break;
			case '13':
				$('#type_container').css('display', '');
				$('#type_label').text('换货单ID：');
				break;
			case '14':
				$('#type_container').css('display', '');
				$('#type_label').text('补漏单ID：');
			default:
				$('#type_container').css('display', 'none');
		}
	}

	function changeCateOfShortDesc(objtype){
		switch (objtype) {
			//订单
			case '1':
				$('#oid_container').css('display', '');
				$('#objid_text').text('订单ID：');
				break;
			default:
				$('#oid_container').css('display', 'none');
				$('#type_container').css('display', 'none');
		}
		var shortDescHtml = '<option value="0">请选择</option>';
		var allShortDescs = eval('(' + $('input[name=all_short_descs]').val() + ')');

		for (var _objtype in allShortDescs){
			if (_objtype == objtype){
				for (var i in allShortDescs[_objtype]){
					shortDescHtml += '<option value="'+i+'">'+allShortDescs[_objtype][i]+'</option>';
				}
			}
		}

		return shortDescHtml;
	}

	function changeAssign() {
		var assign = $('input:radio:checked').val();
		switch (assign) {
			//指派给其他组
			case '1':
				$('#deal_member').val('0');
				$('#deal_assign_container').css('display', '');
				$('#deal_assign_own').css('display', 'none');
				$('#deal_assign_other').css('display', '');
				break;
			//指派给本组
			case '2':
				$('#deal_department').val('0');
				$('#deal_assign_container').css('display', '');
				$('#deal_assign_own').css('display', '');
				$('#deal_assign_other').css('display', 'none');
				break;
			default:
				$('#deal_department').val('0');
				$('#deal_member').val('0');
				$('input:checkbox:checked').attr('checked', false);
				$('#deal_assign_own').css('display', 'none');
				$('#deal_assign_other').css('display', 'none');
		}
	}

	function saveLogInfo() {
		var optionId = $('input:radio:checked').val();
		var para = {
			sid: $('#id').val(),
			action: $('#deal_method').val(),
			content: $('#exec_result').val(),
			exec_suid: 0
		};

		if (para.action == 0) {
			alert('请选择处理方式');
			return false;
		}
		if (optionId) {
			para.assign = optionId;
		}else {
			para.assign = 0;
		}
		if (para.assign != 3 && para.content == '') {
			alert('请填写处理方案');
			return false;
		}
		if ((para.action != 2 &&  para.action != 4) && optionId === undefined) {
			alert('请选择指派对象');
			return false;
		}
		if (optionId == 1 && $('#_adtask_change_role').val() == 0) {
			alert('请指派部门');
			return false;
		}else if(optionId == 1 && $('#exec_suid').val() != 0){
			para.exec_role = $('#_adtask_change_role').val();
			para.exec_suid = $('#exec_suid').val();
		}else if(optionId == 1){
			para.exec_role = $('#_adtask_change_role').val();
			para.exec_suid = 0;
		}
		if (optionId == 2 && $('#deal_member').val() == 0) {
			alert('请指派本组成员');
			return false;
		}else if(optionId == 2 && $('#deal_member').val() != 0) {

			para.exec_suid = $('#deal_member').val();
		}
		$(this).attr("disabled", true);
		K.post('/aftersale/ajax/saveLog.php', para, function (ret) {
			alert('保存成功');
			window.location.href = '/aftersale/list.php';
		});
	}

	function saveInfo() {
		//制作抄送部门接受人的uids
		var copy_department = '';
		$.each($('input:checked'), function(i, v){
			copy_department += v.value+',';
		});
		copy_department = copy_department.substr(0,copy_department.length-1);
		var para = {
			id: $('#id').val(),
			objid: $('#objid').val(),
			type: $('#_adtask_change_objtype').val(),
			typeid: $('#short_desc').val(),
			rid: $('#rid').val(),
			exec_role: $('#_adtask_change_role').val(),
			exec_suid: $('#exec_suid').val(),
			content: $('#content').val(),
			fb_type:$('#fb_type').val(),
			fb_uid:$('#fb_id').val(),
			contact_name:$('#fb_name').val(),
			contact_way:$('#fb_contact').val(),
			contact_mobile:$('#fb_mobile').val(),
			pic_ids:$('#_j_upload_view_img').attr('src'),
			copy_uids:copy_department
		};

		if (K.isEmpty(para.typeid) || parseInt(para.typeid) == 0) {
			alert('请选择问题类型');
			return false;
		}
		if (K.isEmpty(para.content)) {
			alert('请填写问题内容');
			return false;
		}
		if (para.fb_type == 0) {
			alert('请选择投诉人类型');
			return false;
		}
		if (para.type == 1 && K.isEmpty(para.objid)) {
			alert('请填写相关订单号');
			return false;
		}
		if (para.type !=1 && K.isEmpty(para.fb_uid)) {
			if (K.isEmpty(para.contact_name) && (K.isEmpty(para.contact_mobile))) {
				alert('请填写反馈人姓名及联系方式');
				return false;
			}
		}
		if (para.type == 1 && (para.typeid == 12 || para.typeid == 13 || para.typeid == 14) && K.isEmpty(para.rid)) {
			alert('请填写补漏/换货/退货单号');
			return false;
		}
		if (para.fb_type == 4 && (K.isEmpty(para.contact_mobile) && K.isEmpty(para.contact_name))) {
			alert('工作人员电话或姓名至少填一个');
			return false;
		}
		$(this).attr("disabled", true);
		K.post('/aftersale/ajax/save.php', para, function (ret) {
			$(this).attr("disabled", false);
			alert('保存成功');
			window.location.href = '/aftersale/list.php';
		});
	}

	function showContent() {
		$('#real_content').text($(this).data('content'));
		$('#real_result').text($(this).data('result'));
	}

	function changeType() {
		var type = parseInt($("#type").val());
		$('#type_other').css('display', '');
		switch (type) {
			//退货单
			case 1:
				$('#oid_container').css('display', '');
				$('#objid_text').text('订单ID：');
				$('#type_container').css('display', '');
				$('#type_label').text('退货单ID：');
				break;
			//换货单
			case 2:
				$('#oid_container').css('display', '');
				$('#objid_text').text('订单ID：');
				$('#type_container').css('display', '');
				$('#type_label').text('换货单ID：');
				break;
			//补漏单
			case 3:
				$('#oid_container').css('display', '');
				$('#objid_text').text('订单ID：');
				$('#type_container').css('display', '');
				$('#type_label').text('补漏单ID：');
				break;
			//投诉
			case 4:
				$('#oid_container').css('display', '');
				$('#objid_text').text('订单ID：');
				$('#type_container').css('display', 'none');
				break;
			//回访
			case 5:
				$('#oid_container').css('display', '');
				$('#objid_text').text('订单ID：');
				$('#type_container').css('display', 'none');
				break;
			//其他
			case 6:
				$('#oid_container').css('display', '');
				$('#objid_text').text('订单ID：');
				$('#type_container').css('display', 'none');
				$('#type_other').css('display', 'none');
				break;
			//回访,其他
			default:
				$('#oid_container').css('display', 'none');
				$('#type_container').css('display', 'none');
				$('#type_other').css('display', 'none');
		}
	}

	function changeFbType() {

		var fb_type = parseInt($("#fb_type").val());

		switch (fb_type) {
			//客户
			case 1:
				$('#fb_id_container').css('display', '');
				$('#fb_id_lable').text('客户ID：');
				$('#fb_name_container').css('display', '');
				$('#fb_name_lable').text('客户姓名：');
				$('#fb_mobile_container').css('display', '');
				$('#fb_mobile_lable').text('客户电话：');
				$('#fb_contact_container').css('display', '');
				$('#mobile_remark').html('都不填写则默认订单对应的收货人信息');
				break;
			//司机
			case 2:
				$('#fb_id_container').css('display', '');
				$('#fb_id_lable').text('司机ID：');
				$('#fb_name_container').css('display', '');
				$('#fb_name_lable').text('司机姓名：');
				$('#fb_mobile_container').css('display', '');
				$('#fb_mobile_lable').text('司机电话：');
				$('#fb_contact_container').css('display', '');
				$('#mobile_remark').html('都不填写则默认订单对应的司机信息');
				break;
			//搬运工
			case 3:
				$('#fb_id_container').css('display', '');
				$('#fb_id_lable').text('搬运工ID：');
				$('#fb_name_container').css('display', '');
				$('#fb_name_lable').text('搬运工姓名：');
				$('#fb_mobile_container').css('display', '');
				$('#fb_mobile_lable').text('搬运工电话：');
				$('#fb_contact_container').css('display', '');
				$('#mobile_remark').html('都不填写则默认订单对应的搬运工信息');
				break;
			//工作人员
			case 4:
				$('#fb_id_container').css('display', '');
				$('#fb_id_lable').text('员工ID：');
				$('#fb_name_container').css('display', '');
				$('#fb_name_lable').text('员工姓名：');
				$('#fb_mobile_container').css('display', '');
				$('#fb_mobile_lable').text('员工电话：');
				$('#fb_contact_container').css('display', 'none');
				$('#mobile_remark').html('ID或电话至少填写一个');
				break;
			default:
				$('#fb_id_container').css('display', 'none');
				$('#fb_name_container').css('display', 'none');
				$('#fb_mobile_container').css('display', 'none');
				$('#fb_contact_container').css('display', 'none');
		}
	}

	function changeDepartment() {
		var department = $('#duty_department').val();
		var options = $('#exec_suid').find('option');
		$('#exec_suid').val(0);
		for (var i = 1; i < options.length; i++) {
			var option = options.get(i);
			if ($(option).data('did') == department) {
				$(option).css('display', '');
			} else {
				$(option).css('display', 'none');
			}
		}

	}

	main();
	changeType();
	changeDepartment();
	changeFbType();
	dealMethod();
	if(typeof(oid)!="undefined" && oid>0)
	{
		$('#_adtask_change_objtype').val(1);
		$('#short_desc').html(changeCateOfShortDesc('1'));
	}
	if(typeof(id)!="undefined" && id>0)
	{
		$('#_adtask_change_objtype').val(1);
		$('#short_desc').html(changeCateOfShortDesc('1')).val(16);

	}
})();