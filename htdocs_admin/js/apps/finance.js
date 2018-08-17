(function(){
    
    function main(){
        // 客户账户余额，财务操作
        $('._j_finance_op_confirm').on('click', financeOperationConfirm);
        
        //客户收款
        $('#_j_customer_amount_paid').on('click', customerAmountPaid);
        $('#tmp_pay_select_all').on('click', tmpCustomerPaySelectAll);
        
        // 修改：供应商的支付来源
        $('.modify_paid_source').on('click', showModifyPaidSource);
        $('#_j_confirm_single_money_out').on('click', confirmSingleMoneyOut);
        
        // 平台客户的支付
        $('#paid_platform_debit').on('click', savePaidPlatformDebit);
        
        // 客户应收明细 - 修改（支付方式，支付金额）
		$('._j_modify_single_money_in').click(modifySingleMoneyIn);
		$('#_j_confirm_single_money_in').click(confirmSingleMoneyIn);
        
		$('#_j_confirm_balance_2_amount').click(onBalance2Amount);

        //提现分析
        $('#_j_show_embodiment_analysis').on('click',showEmbodimentAnalysis);

        //修改客户账户余额明细单据类型
        $('._j_modify_single_amount').on('click', modifyPayType);
        $('#_j_confirm_modify_payment_type').on('click', confirmModifyPayType);

        //财务操作修改单据类型
        $('._j_chg_obj_type').on('click', modifyObjType);
    }

    function tmpCustomerPaySelectAll(){
        if ($('#tmp_pay_select_all').is(':checked')) //全选
        {
            $('#customer_account_pay').find('input[name=wait_ids]').each(function(){
                this.checked=true;
            });
        }
        else //取消全选
        {
            $('#customer_account_pay').find('input[name=wait_ids]').each(function(){
                this.checked=false;
            });
        }
    }

    // 客户账户余额，财务操作
    function financeOperationConfirm(){
        var dialog = $('#FinanceOperationModal');
        var para = {
            cid: $(this).attr('data-cid'),
            type: dialog.find('input[name=type]:checked').val(),
            price: dialog.find('input[name=price]').val(),
            city_id: $("#select-city select[name=city_id]").val(),
            tax: dialog.find('input[name=tax]').val(),
            note: dialog.find('textarea[name=note]').val(),
            cash_rate: dialog.find('input[name=cash_rate]').val(),
            payment_type: dialog.find('select[name=payment_type]').val()
        };
        if (para.type != 1)
        {
            para.tax = '';
        }

        var payType = $('input[name=type]:checked').val();

		var st = _checkParam();
        
		if (st){
			$(this).attr('disabled', true);
			K.post('/finance/ajax/save_customer_amount.php', para, function(ret){
                if (ret.st == 1){
                    alert('操作成功！');
                    window.location.reload();
                }else{
                    alert(ret.st=-10? '客户不存在': '操作失败！（'+ret.st+'）');
                }
            });
		}
		
		function _checkParam(){
			var st = true;
			if (para.type==undefined) {alert('请选择 “类型”'); st=false;}
			else if (para.note.length==0) { alert('请填写 “备注”'); st=false; }
			else if (para.price.length==0) { alert('请填写 “金额”'); st=false; }
			else if (isNaN(para.price)) { alert('“金额” 必须为数字'); st=false; }
            else if (para.payment_type=='0') { alert('请选择 “支付方式”'); st=false; }
            else if (para.city_id == '0') { alert('请选择 “城市”'); st=false; }
            else if (payType != '1' && para.tax.length != 0) { alert('“客户预付”情况下才可以支付税金！'); $('input[name=tax]').val(''); st=false; }
            else if (isNaN(para.tax)) { alert('“支付税金” 必须为数字'); $('input[name=tax]').val(''); st=false; }
            else if (parseFloat(para.tax) > parseFloat(para.price)) { alert('“支付税金”不能大于“金额”！'); $('input[name=tax]').val(''); st=false; }
			return st;
		}
    }

    function modifyObjType() {
        var obj = $('#FinanceOperationModal input[name=type]:checked').val();
        if (obj == 1)
        {
            $('.pay_tax').css('display', 'block');
        } else {
            $('.pay_tax').css('display', 'none');
        }
        if (obj == 11)
        {
            $('.payment_type_amount').css('display', 'block');
            $('.payment_type_amount select').attr('name', 'payment_type');
            $('.payment_type').css('display', 'none');
            $('.payment_type_pay_back').css('display', 'none');
            $('.payment_type_pay_back select').attr('name', '');
            $('.payment_type select').attr('name', '');
        }
        else if (obj == 8  || obj == 10)
        {
            $('.payment_type_pay_back').css('display', 'block');
            $('.payment_type_pay_back select').attr('name', 'payment_type');
            $('.payment_type').css('display', 'none');
            $('.payment_type_amount').css('display', 'none');
            $('.payment_type_amount select').attr('name', '');
            $('.payment_type select').attr('name', '');
        }
        else
        {
            $('.payment_type').css('display', 'block');
            $('.payment_type select').attr('name', 'payment_type');
            $('.payment_type_pay_back').css('display', 'none');
            $('.payment_type_amount').css('display', 'none');
            $('.payment_type_amount select').attr('name', '');
            $('.payment_type_pay_back select').attr('name', '');
        }
    }
    
    // 客户收款
    function customerAmountPaid(){
        var oids = [];
        var price_sum = 0;
        var moling_sum = 0;
        var box = $("#customer_account_pay");
        $(this).attr('disabled', true);
        var error_status = false;
        box.find('input[name=wait_ids]').each(function () {
            if($(this).is(':checked'))
            {
                var oid = $(this).val();
                var order_amount = parseInt($(this).data('realpay'));
                var realpay = $(this).parent().parent().find('input[name=real_amount]').val();
                if(isNaN(realpay) || realpay<=0)
                {
                    alert('oid:'+oid+' 实付金额不能为空或者小于等于0');
                    error_status = true;
                    return false;
                }
                realpay = parseInt(parseFloat(realpay).toFixed(2)*100);
                if(order_amount < realpay)
                {
                    alert('oid:'+oid+' 实付金额不能大于应收款');
                    error_status = true;
                    return false;
                }
                var moling = parseInt(parseFloat($(this).parent().parent().find('input[name=moling]').val()).toFixed(2)*100);
                if(isNaN(moling))
                {
                    moling = 0;
                }
                if(moling > 100)
                {
                    alert('oid:'+oid+' 抹零金额不能大于1元');
                    error_status = true;
                    return false;
                }
                if(order_amount < (realpay+moling))
                {
                    alert('oid:'+oid+' 实付加抹零金额不能大于应收款');
                    error_status = true;
                    return false;
                }
                price_sum += realpay;
                moling_sum += moling;
                var obj = {oid: oid, realpay:realpay, moling:moling};
                oids.push(obj);
            }
        });
        if(error_status || oids.length == 0)
        {
            $(this).attr('disabled', false);
            return false;
        }
        var para = {
                cid: $(this).attr('data-cid'),
                etime : box.find('input[name=etime]').val(),
                payment_type : box.find('select[name=payment_type]').val(),
                oids : JSON.stringify(oids),
                note : box.find('textarea[name=note]').val() 
            };
            
        if (para.price == 0){
            alert('支付金额不能为 0 ！');
            $(this).attr('disabled', false);
            return;
        }
        
        if (!confirm('确认支付 '+(price_sum/100)+'元，抹零'+(moling_sum/100)+'元？')){
            $(this).attr('disabled', false);
            return;
        }

        if(para.note == '')
        {
            alert('备注必填');
            $(this).attr('disabled', false);
            return;
        }

        K.post('/finance/ajax/save_customer_amount_paid.php', para, function(ret){
            if (ret.st){
                alert('支付成功！！');
                window.location.reload();
            }
        });
    }
    
    // 修改供应商的支付来源
    function showModifyPaidSource()
    {
        var tgtContent = $(this).closest('tr').find('._paid_source'),
            id = $(this).closest('tr').attr('data-id'),
            paidSource = tgtContent.attr('data-src'),
            paidDesc = tgtContent.html();
    
        var box = $('#modifySupplierPaidSource');
        
        paidDesc = paidDesc.length>0? paidDesc: '无';
        box.find('#_j_money_out_detail').html('原：'+paidDesc);
        box.find('select[name=paid_source]').val(paidSource);
        box.find('select[name=paid_source]').attr('data-oldSrc', paidSource);
        box.find('select[name=paid_source]').attr('data-id', id);
        
        box.modal();
            
    }
    
    function confirmSingleMoneyOut()
    {
        var box = $('#modifySupplierPaidSource');
        var para = {
            id: box.find('select[name=paid_source]').attr('data-id'),
            old_source: box.find('select[name=paid_source]').attr('data-oldSrc'),
            new_source: box.find('select[name=paid_source]').val(),
        };
        
        if (para.old_source == para.new_source){
            alert('没有修改款项来源！');return;
        }
        
        $(this).attr('disabled', true);
        K.post('/finance/ajax/modify_money_out.php', para, function(){
            window.location.reload();
        });
    }
    
    function savePaidPlatformDebit(){
        var para = {
            cid: $('form').find('input[name=cid]').val()
        };
        var oids = [];
        $('.wait_paid_order').each(function(){
            var paidPrice = $(this).find('input[name=real_paid]').val();
            var moling = $(this).find('input[name=moling]').val();
            
            if (paidPrice.length > 0 && paidPrice!='0'){
                oids.push($(this).attr('data-oid')+':'+paidPrice+':'+moling);
            }
        });
        
        if (para.cid.length==0){
            alert('不能支付，请联系管理员！'); return false;
        }
        
        if (oids.length==0){
            alert('订单号不能为空'); return false;
        }
        
        para.oids = JSON.stringify(oids);
        
        if (confirm('确认支付这些订单？')){
            K.post('/finance/ajax/save_platform_debit.php', para, function(){
                window.location.reload();
            });
        }
    }
    
    // 修改单条的财务入账 - 弹框显示
	function modifySingleMoneyIn() {
		var id = $(this).attr('data-id'),
			price = Math.abs($(this).attr('data-price')) / 100,
			paymentType = $(this).attr('data-paytype'),
			type = $(this).attr('data-type'),
			paymentName = $(this).parent().parent().find('.payment_name').html();

		// 显示修改框
		var box = $('#modifySingleMoneyInModal');
		box.modal();

		var selectedHandler = box.find('select[name=payment_type]');
		$('#_j_money_in_detail').html('已付：' + price + '元；类型：' + (paymentName || '无'));
		box.find('input[name=price]').val(price);
		selectedHandler.find('option[value=' + paymentType + ']').attr('selected', true);

		$('#_j_confirm_single_money_in').attr('data-id', id);
		$('#_j_confirm_single_money_in').attr('data-type', type);
	}

	// 修改单条的财务入账 - 提交
	function confirmSingleMoneyIn() {
		var box = $('#modifySingleMoneyInModal');
		var id = $(this).attr('data-id'),
			type = $(this).attr('data-type'),
			price = box.find('input[name=price]').val(),
			paymentType = box.find('select[name=payment_type]').val();

		if (id == '0') {
			alert('系统异常，请联系管理员！');
			return false;
		} else if (price == '') {
			alert('支付金额不能为空，无支付请输入 “0”！');
			return false;
		} else if (paymentType == '0') {
			alert('请选择收款方式！');
			return false;
		}

		var para = {id: id, price: price, payment_type: paymentType, type: type};

		$(this).attr('disabled', true);
		K.post('/finance/ajax/save_single_moneyIn_modify.php', para, function () {
			alert('更新成功！');
			window.location.reload();
		});
	}
    
    function onBalance2Amount(ev) {
		if (confirm('确定要将用户多余货款放入余额吗？')) {
			ev.preventDefault();
			var cid = $(this).data('cid'),
				amount = $('#balance_to_amount').val(),
				note = $('#note').val(),
				para = {cid: cid, amount: amount, note: note};

			$(this).attr('disabled', true);
			K.post('/finance/ajax/balance_to_amount.php', para, _onBalance2AmountSuccess);
		}
	}
	function showEmbodimentAnalysis()
    {
        var para = {
            cid: $("input[name='cid']").val()
            };

        K.post('/finance/ajax/get_embodiment_analysis.php', para, function (data) {

                $('#EmbodimentAnalysisModal').html(data.html);
        });
    }

    function modifyPayType()
    {
        var id = $(this).attr('data-id');
        $('#modifyCustomerPaymentType #_j_pay_detail_' + id).css('display', 'block');
        $('#modifyCustomerPaymentType').modal();
    }

    function confirmModifyPayType()
    {
        var para = {
            payment_type: $('select[name=paid_source]').val(),
        };

        $('.payment_type').each(function () {
            if ($(this).css('display') == 'block')
            {
                para.id = $(this).data('id');
            }
        });

        K.post('/finance/ajax/save_customer_payment_type.php', para, function (ret) {
            alert(ret.msg);
            window.location.reload();
        })

    }
    main();
})();