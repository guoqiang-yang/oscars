(function () {
    
    function main(){
        // 客户||用户 信息保存
        // $('#_j_chg_customer_identity').change(changeCustomerIdentity);
        $('#_j_save_customer_info').on('click', saveCustomerInfo);
        $('._j_modify_userinfo').on('click', modifyUserInfo);
        $('._j_save_modify_userinfo').on('click', saveModifyUserInfo);
        //$('.customer_kind').on('change', changeCustomerOrderKind);

        //亲朋好友信息保存
        $('.add_customer_friend').on('click', addCustomerFriendInfo);
        $('.edit_customer_friend').on('click', editCustomerFriendInfo);
        $('._j_save_modify_friendinfo').on('click', saveModifyFriendInfo);
        $('.delete_customer_friend').on('click', deleteCustomerFriend);

        //销售流转历史
        $('.showHistorySales').on('click', showSalesChangedHistory);
        
		$('#auto_save_customer').click(_onAutoSaveCustomer);
        
        // 客户销售状态的更改（私海，公海，内海...)
        $('.change_sale_status').click(changeSaleStatus);
        
        // 销售标记客户的级别
        $('.change_sale_level').click(changeSaleLevel);
        
        // 优惠券
		$('._j_show_apply_coupon').click(showApplyCoupon);
		$('#_js_apply_coupon').click(applyCoupon);
		$('._j_audit_apply_coupon').click(auditApplyCoupon);

        //重置密码
        $('._j_reset_pass').click(resetPass);
        //高级编辑
        $('._j_customer_high').click(showCustomerHighInfo);
        $('#_js_save_customer_high').click(saveCustomerHighInfo);
        $('#level_for_saler').change(changeContractDate);
        
        //合并客户
        $('#merge_customer').click(mergeCustomer);
        var hash = document.location.hash;
        if (hash) {
            $('.nav-tabs a[href='+hash+']').tab('show');
        }

        //实名认证审核
        $('#identity_pass').click(passIdentity);
        $('#identity_fail').click(failIdentity);

        //客户类型审核
        $('#customer_type_pass').click(passCustomerType);
        $('#customer_type_fail').click(failCustomerType);

        //保存个人详细信息
        $('._j_save_person_detail_info').click(savePersonInfo);
        $('._j_save_person_cert_info').click(savePersonCertInfo);
    }

    
    function saveCustomerInfo(){
        var box = $(this).closest('form');
        var para = {
            cid:            box.find('input[name=cid]').val(),
			name:           box.find('input[name=name]').val(),
            identity:       box.find('select[name=identity]').val(),
            source:         box.find('select[name=source]').val(),
            city_id:        box.find('select[name=city_id]').val(),
            note:           box.find('textarea[name=note]').val(),
            sales_suid:     box.find('select[name=sales_suid]').val(),
            level_for_sys:  box.find('select[name=level_for_sys]').val(),
	        tax_point:      box.find('input[name=tax_point]').val(),
            
            //联系人信息
            user_name:      box.find('input[name=name]').val(),
            mobile:         box.find('input[name=mobile]').val(),
            hometown:       box.find('select[name=birth_place]').val()
        };

        $(this).attr('disabled', true);
        K.post('/crm2/ajax/save_customer.php', para, function(ret){
            $('#_j_save_customer_info').attr('disabled', false);

            if (ret.st==0){
                alert('保存成功！');
                
                if (ret.url){
                    window.location.href = ret.url;
                } else {
                    window.location.reload();
                }
            } else {
                alert(ret.msg);
            }
        });
    }

    function showCustomerHighInfo() {
        var box = $('#saveCustomerHigh');
        box.find('input[name=cid]').val($(this).attr('data-cid'));
        box.find('input[name=payment_days]').val($(this).attr('data-days'));
        box.find('select[name=status]').val($(this).attr('data-status'));
        box.find('select[name=level_for_sys]').val($(this).attr('data-level'));
        box.find('select[name=has_duty]').val($(this).attr('data-duty'));
        box.find('input[name=discount_ratio]').val($(this).attr('data-ratio'));
        box.find('select[name=level_for_saler]').val($(this).attr('data-customer'));
        box.find('input[name=contract_btime]').val($(this).attr('data-contractb'));
        box.find('input[name=contract_etime]').val($(this).attr('data-contracte'));
        box.find('input[name=payment_amount]').val($(this).attr('data-amount'));
        if($(this).attr('data-customer') >4)
        {
            $('#customer_contract_div').show();
        }else{
            $('#customer_contract_div').hide();
        }
        box.modal();
    }
    function saveCustomerHighInfo(){
        var box = $('#saveCustomerHigh');
        var para = {
            cid:            box.find('input[name=cid]').val(),
            payment_days:   box.find('input[name=payment_days]').val(),
            payment_amount: box.find('input[name=payment_amount]').val(),
            status:         box.find('select[name=status]').val(),
            level_for_sys:  box.find('select[name=level_for_sys]').val(),
            level_for_saler: box.find('select[name=level_for_saler]').val(),
            contract_btime: box.find('input[name=contract_btime]').val(),
            contract_etime: box.find('input[name=contract_etime]').val(),
            has_duty:       box.find('select[name=has_duty]').val(),
            discount_ratio: box.find('input[name=discount_ratio]').val()
        };
        if(isNaN(para.payment_amount) || para.payment_amount == '')
        {
            if(para.level_for_saler == 6)
            {
                alert('赊销合同客户必填账额');
                return;
            }
            para.payment_amount = 0;
        }
        para.payment_amount = parseInt(parseFloat(para.payment_amount).toFixed(2)*100);
        $(this).attr('disabled', true);
        K.post('/crm2/ajax/save_customer_high.php', para, function(ret){
            $('#_js_save_customer_high').attr('disabled', false);

            if (ret.st==0){
                if (ret.url){
                    window.location.href = ret.url;
                } else {
                    alert('保存成功！');
                    window.location.reload();
                }
            } else {
                alert(ret.msg);
            }
        });
    }

    function showSalesChangedHistory() {
        $("#saleHistoryChangedModal").modal('show');
        onGetSalesChangedHistory();
    }

    function onGetSalesChangedHistory(){
        var start_num = $(this).attr('data-start');

        if ("undefined" == typeof start_num){
            start_num = 0;
        }
        var para = {
            cid: $('#saleHistoryChangedModal').data('cid'),
            start: start_num
        };
        K.post('/crm2/ajax/get_customer_sales_changed_history.php', para, function (ret) {
            $('#saleHistoryChangedModal .modal-body').html(ret.html);
            bindEvent2Sales();
        });
    }
    function bindEvent2Sales() {
        $("#saleHistoryChangedModal").on('click','._j_search_sale_history', onGetSalesChangedHistory);
    }

    function addCustomerFriendInfo() {
        var box = $('#modifyFriendInfoModal');
        box.find('.modal-title').html('添加亲朋信息');
        box.attr('data-crid', '0');
        box.find('select[name=relation]').val(0);
        box.find('input[name=name]').val('');
        box.find('input[name=nick_name]').val('');
        box.find('input[name=sex]').val(0);
        box.find('input[name=age]').val('');
        box.find('input[name=mobile]').val('');
        box.find('input[name=weixin]').val('');
        box.find('input[name=qq]').val('');
        box.find('input[name=email]').val('');
        box.find('input[name=interest]').val('');
        box.find('input[name=shape]').val('');
        box.find('input[name=trade]').val('');
        box.find('input[name=note]').val('');
        box.modal();
    }

    function editCustomerFriendInfo() {
        var box = $('#modifyFriendInfoModal');
        box.find('.modal-title').html('编辑亲朋信息');
        box.attr('data-crid', $(this).data('crid'));
        box.find('select[name=relation]').val($(this).data('relation'));
        box.find('input[name=name]').val($(this).data('name'));
        box.find('input[name=nick_name]').val($(this).data('nick_name'));
        box.find('input[name=sex]').val($(this).data('sex'));
        box.find('input[name=age]').val($(this).data('age'));
        box.find('input[name=mobile]').val($(this).data('mobile'));
        box.find('input[name=weixin]').val($(this).data('weixin'));
        box.find('input[name=qq]').val($(this).data('qq'));
        box.find('input[name=email]').val($(this).data('email'));
        box.find('input[name=interest]').val($(this).data('interest'));
        box.find('input[name=shape]').val($(this).data('shape'));
        box.find('input[name=trade]').val($(this).data('trade'));
        box.find('input[name=note]').val($(this).data('note'));
        box.modal();
    }

    function saveModifyFriendInfo(){
        var box = $('#modifyFriendInfoModal');
        var para = {
            type: 'modify',
            cid: box.attr('data-cid'),
            crid: box.attr('data-crid'),
            relation: box.find('select[name=relation]').val(),
            name: box.find('input[name=name]').val(),
            nick_name: box.find('input[name=nick_name]').val(),
            sex: box.find('input[name=sex]').val(),
            age: box.find('input[name=age]').val(),
            mobile: box.find('input[name=mobile]').val(),
            weixin: box.find('input[name=weixin]').val(),
            qq: box.find('input[name=qq]').val(),
            email: box.find('input[name=email]').val(),
            interest: box.find('input[name=interest]').val(),
            shape: box.find('input[name=shape]').val(),
            trade: box.find('input[name=trade]').val(),
            note: box.find('input[name=note]').val()
        };

        if(para.relation == 0)
        {
            alert('请选择关系');
            return false;
        }

        if(para.name == '')
        {
            alert('请填写姓名');
            return false;
        }

        $(this).attr('disabled', true);
        K.post('/crm2/ajax/modify_friend_info.php', para, function(ret){
            $('._j_save_modify_friendinfo').attr('disabled', false);

            if (ret.st == 0){
                alert(para.crid==''?'添加成功！':'更新成功！');
                box.modal('hide');
                window.location.href = '/crm2/edit_customer.php?cid='+para.cid+'#friend';
                window.location.reload();
            } else {
                alert(ret.msg);
            }
        });
    }

    function deleteCustomerFriend() {
        var para = {
            cid: $(this).data('cid'),
            crid: $(this).data('crid')
        };
        var msg = '确定要删除ID为（'+para.crid+'）的亲朋信息吗？请确认';
        if(confirm(msg)){
            K.post('/crm2/ajax/modify_friend_info.php', para, function (ret) {
                if (ret.st == 0){
                    alert('删除成功！');
                    window.location.href = '/crm2/edit_customer.php?cid='+para.cid+'#friend';
                } else {
                    alert(ret.msg);
                }
            })
        }
    }
    
    
    function modifyUserInfo(){
        var type = $(this).attr('data-type');
        var box = $('#modifyUserinfo');
        
        if (type == 'unbind'){
            if (confirm('确定要解除绑定？')){
                var para = {
                    type: 'unbind',
                    cid: box.attr('data-cid'),
                    uid: $(this).closest('._j_one_user').find('.uid').html()
                };
                K.post('/crm2/ajax/modify_user_info.php', para, function(ret){
                    if (ret.st == 0){
                        alert('解除成功！');
                        window.location.reload();
                    } else {
                        alert(ret.msg);
                    }
                });
            }
        } else if (type == 'bind') {
            box.find('.modal-title').html('绑定用户');
            box.attr('data-type', type);
            
            box.attr('data-uid', '0');
            box.find('input[name=name]').val('');
            box.find('input[name=mobile]').val('');
            box.find('select[name=hometown]').val('');
            box.find('input[name=real_name]').val('');
            box.find('input[name=id_card_no]').val('');
            box.modal();
        } else if (type == 'modify') {
            box.find('.modal-title').html('修改用户');
            box.attr('data-type', type);
            
            var obj = $(this).closest('._j_one_user');
            box.attr('data-uid', obj.find('.uid').html());
            box.find('input[name=name]').val(obj.find('.name').html());
            box.find('input[name=mobile]').val(obj.find('.mobile').html());
            
            var _hometown = obj.find('.hometown').html();
            if (_hometown.indexOf('--') < 0){
                box.find('select[name=hometown]').val(_hometown);
            }
            box.find('input[name=real_name]').val(obj.find('.real_name').html());
            box.find('input[name=id_card_no]').val(obj.find('.id_card_no').html());
            box.find('select[name=is_admin]').val(obj.find('.is_admin').attr('data-isadmin'));
            box.modal();
        }
    }
    
    function saveModifyUserInfo(){
        var box = $('#modifyUserinfo');
        var para = {
            type: box.attr('data-type'),
            cid: box.attr('data-cid'),
            uid: box.attr('data-uid'),
            name: box.find('input[name=name]').val(),
            mobile: box.find('input[name=mobile]').val(),
            hometown: box.find('select[name=hometown]').val(),
            real_name: box.find('input[name=real_name]').val(),
            id_card_no: box.find('input[name=id_card_no]').val(),
            is_admin: box.find('select[name=is_admin]').val()
        };
        
        $(this).attr('disabled', true);
        K.post('/crm2/ajax/modify_user_info.php', para, function(ret){
            $('._j_save_modify_userinfo').attr('disabled', false);
            
            if (ret.st == 0){
                alert(para.type=='unbind'?'解除成功！':'更新成功！');
                window.location.reload();
            } else {
                alert(ret.msg);
            }
        });
    }
    
    // 修改客户的是否下单的类型
    function changeCustomerOrderKind(){
        var val = $(this).val();
        
        if (val == 1){ //未下单客户
            $('.customer_kind_no_order').show();
            $('.customer_kind_has_order').hide();
            $('.customer_kind_no_order').attr('name', 'level_for_saler');
            $('.customer_kind_has_order').attr('name', '');
            $('.level_for_saler').show();
            $(this).parent().css('margin-right', '5px');
        } else if (val == 2){   //已下单客户
            $('.customer_kind_has_order').show();
            $('.customer_kind_no_order').hide();
            $('.customer_kind_no_order').attr('name', '');
            $('.customer_kind_has_order').attr('name', 'level_for_saler');
            $('.level_for_saler').show();
            $(this).parent().css('margin-right', '5px');
        } else{
            $('.level_for_saler').hide();
            $('.customer_kind_no_order').attr('name', '');
            $('.customer_kind_has_order').attr('name', '');
            $(this).parent().css('margin-right', '30px');
        }
        
    }
    
    function _onAutoSaveCustomer() {
		if (confirm('确定要执行此操作吗？\n\n该操作会：\n1、用该手机号创建一个全新的用户，密码默认手机号后6位；\n2、跳转到该用户的下单页面;')) {
			var para = {
				mobile: $('input[name=mobile]').val(),
			};
			K.post('/crm2/ajax/auto_save_customer.php', para, function(data){
                window.location.href = "/order/add_order2.php?cid=" + data.cid+ "&uid="+ data.uid;
            });
		}
	}
    
    // 客户销售状态的更改
    function changeSaleStatus(){
        var cid = $(this).closest('.form-horizontal').attr('data-cid'),
            st = $(this).attr('data-type'),
            desc = $(this).html();
    
        var para = {cid: cid, st: st};
        
        if (!confirm("确定要把该客户："+ desc + "?")){
            return;
        }
        
        K.post('/crm2/ajax/change_sale_status.php', para, function(ret){
            if (ret.st == 0){
                alert('更新成功！');
                window.location.reload();
            } else {
                alert(ret.msg);
                return false;
            }
        });
    }
    
    // 更改客户的销售级别
    function changeSaleLevel(){
        var cid = $(this).closest('.form-horizontal').attr('data-cid'),
            st = $(this).attr('data-type'),
            to_level = $(this).attr('data-slevel'),
            desc = $(this).html();
    
        if (!confirm("确定把该客户标记为："+ desc +"?")){
            return;
        }
        
        var para = {cid:cid, st:st, to_level:to_level};
        K.post('/crm2/ajax/change_sale_level2.php', para, function(ret){
            if (ret.st == 0){
                alert('更新成功！'+ ret.msg);
                window.location.reload();
            } else {
                alert(ret.msg);
                return false;
            }
        });
    }

    function changeContractDate() {
        if($(this).val()<5)
        {
            $('#customer_contract_div').hide();
        }else{
            $('#customer_contract_div').show();
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
			cate: box.find('input[name=cate]:checked').val(),
			num: box.find('select[name=num]').val(),
			note: box.find('textarea[name=note]').val()
		};
			
		// check params
		if (isNaN(parseInt(param.cid))) {
			alert('客户ID不能为空');
			return false;
		}

		if (K.isEmpty(param.cate)) {
			alert('请选择优惠券类型');
			return false;
		}

		if (param.note == '') {
			alert('请填写申请原因');
			return false;
		}
		
        $(this).attr('disabled', true);
		K.post('/crm2/ajax/apply_coupon.php', param, function(ret){
			if (ret.errno == 1){
				box.modal('hide');
				alert('申请成功');
			} else {
				alert('申请失败，销售人员只能为自己的客户申请!');
			}
            $('#_js_apply_coupon').attr('disabled', false);
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
		K.post('/crm2/ajax/audit_apply_coupon.php', para, function(ret){
			alert('操作成功');
			window.location.reload();
		});
	}

    //重置密码
    function resetPass(ev) {
        var tgt = $(ev.currentTarget),
            uid = tgt.data('uid'),
            para = {uid:uid};

        if (confirm('确认要将用户密码重置成123456吗？')) {
            K.post('/crm2/ajax/reset_pass.php', para, _onResetPassSuccess);
        }
    }
    function _onResetPassSuccess(data) {
        alert('重置成功！');
    }
    
    
    // 合并客户
    function mergeCustomer(){
        var para = {
            master_mobile: $('body').find('input[name=master_mobile]').val(),
            slave_mobile1: $('body').find('input[name=slave_mobile1]').val(),
            master_cid: $('body').find('input[name=master_cid]').val(),
            slave_cid1: $('body').find('input[name=slave_cid1]').val(),
            master_sales_suid: $('body').find('input[name=master_sales_suid]').val()
        };
        
        if (!confirm('确认要合并改客户， 并将客户分配给指定的销售人员？？'))
        {
            return;
        }
        
        $(this).attr('disabled', true);
        
        K.post('/crm2/ajax/merge_customer.php', para, function(ret){
            
            $('#merge_customer').attr('disabled', false);
            
            if (ret.st == 0)
            {
                alert('合并成功！');
                window.location.href = '/crm2/customer_detail.php?cid='+ ret.data.cid;
            }
            else
            {
                alert(ret.msg);
            }
        });
    }
    
    //确认实名认证
    function passIdentity() {
        var para = {
            cid: $(this).data('cid'),
            method: 'pass'
        };
        if (!confirm('确认通过实名认证吗？？'))
        {
            return;
        }
        K.post('/crm2/ajax/change_identity.php', para, function (ret) {
            if (ret.st == 0)
            {
                alert('确认成功！');
                window.location.href = '/crm2/customer_detail.php?cid='+ ret.data.cid;
            }
            else
            {
                alert(ret.msg);
            }
        });
    }

    //驳回实名认证
    function failIdentity() {
        var para = {
            cid: $(this).data('cid'),
            method: 'fail',
            reason: $('#identity_fail_model').find('textarea[name=identity_reason]').val()
        };
        if(para.reason == '' || para.reason == undefined)
        {
            alert('请填写驳回原因');
            return;
        }
        if (!confirm('确认驳回实名认证吗？？'))
        {
            return;
        }
        K.post('/crm2/ajax/change_identity.php', para, function (ret) {
            if (ret.st == 0)
            {
                alert('驳回成功！');
                window.location.href = '/crm2/customer_detail.php?cid='+ ret.data.cid;
            }
            else
            {
                alert(ret.msg);
            }
        });
    }

    //确认客户类型
    function passCustomerType() {
        var para = {
            cid: $(this).data('cid'),
            method: 'pass'
        };
        if (!confirm('确认通过客户类型吗？？'))
        {
            return;
        }
        K.post('/crm2/ajax/change_customer_type.php', para, function (ret) {
            if (ret.st == 0)
            {
                alert('确认成功！');
                window.location.href = '/crm2/customer_detail.php?cid='+ ret.data.cid;
            }
            else
            {
                alert(ret.msg);
            }
        });
    }

    //驳回客户类型
    function failCustomerType() {
        var para = {
            cid: $(this).data('cid'),
            method: 'fail',
            reason: $('#customer_type_fail_model').find('textarea[name=type_reason]').val()
        };
        if(para.reason == '' || para.reason == undefined)
        {
            alert('请填写驳回原因');
            return;
        }
        if (!confirm('确认驳回客户类型修改吗？？'))
        {
            return;
        }
        K.post('/crm2/ajax/change_customer_type.php', para, function (ret) {
            if (ret.st == 0)
            {
                alert('驳回成功！');
                window.location.href = '/crm2/customer_detail.php?cid='+ ret.data.cid;
            }
            else
            {
                alert(ret.msg);
            }
        });
    }

    function savePersonInfo() {
        var box = $('#detail');
        var para = {
            cid: box.find('input[name=cid]').val(),
            uid: box.find('input[name=uid]').val(),
            age: box.find('input[name=age]').val(),
            birthday: box.find('input[name=birthday]').val(),
            work_age: box.find('input[name=work_age]').val(),
            interest: box.find('input[name=interest]').val(),
            address: box.find('input[name=address]').val(),
            work_area: box.find('input[name=work_area]').val(),
            character_tag: box.find('input[name=character_tag]').val(),
            weixin: box.find('input[name=weixin]').val(),
            qq: box.find('input[name=qq]').val(),
            email: box.find('input[name=email]').val()
        };

        $(this).attr('disabled', true);
        K.post('/crm2/ajax/save_customer_detail_info.php', para, function (ret) {
            alert(ret.msg);
            window.location.reload();
        })
    }

    function savePersonCertInfo()
    {
        var box = $('#certificate');
        var cid = $('#detail').find('input[name=cid]').val();
        //认证相关
        var para = {
            cid: cid,
            identity:      box.find('input[name=identity]').val(),
            real_name:      box.find('input[name=real_name]').val(),
            id_number:      box.find('input[name=id_number]').val(),
            band_card_number:  box.find('input[name=band_card_number]').val(),
            identity_mobile: box.find('input[name=identity_mobile]').val(),
            company_name:    box.find('input[name=company_name]').val(),
            legal_person_name: box.find('input[name=legal_person_name]').val(),
            legal_person_id_number: box.find('input[name=legal_person_id_number]').val(),
            social_credit_number: box.find('input[name=social_credit_number]').val()
        };

        K.post('/crm2/ajax/save_customer_cert_info.php', para, function (data) {

            alert(data.msg);
            window.location.reload();
        });
    }
    main();
})();

