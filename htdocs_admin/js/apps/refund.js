(function () {

	function main() {
		$('#is_refund_freight').change(_onChangeRefundFreight);
		$('#is_refund_carry_fee').change(_onChangeRefundCarryFee);
        $('#is_traps_freight').change(_onChangeTrapsFreight);
        $('#is_traps_carry_fee').change(_onChangeTrapsCarryFee);
		$('#_j_btn_save_refund').click(onSaveRefund);
        $('#complate_virtual_refund').on('click', complateVirtualRefund)
		$('._j_chg_refund_step').click(onChgRefundStep);
        $('._j_rebut_refund').click(onRebutRefund);
		$('.refund_coupon').click(refundCoupon);
		$('#reason_type').change(changeReasonDetail);
		$('#type').change(changeRefundType);
		$(document).ready(getRefundReason);
	}

	function _onChangeRefundFreight() {
		var isRefundFreight = parseInt($('#is_refund_freight').val());

		if (isRefundFreight == 1) {
			$('#refund_freight_span').css('display', '');
		} else if (isRefundFreight == 2) {
			$('#refund_freight_span').css('display', 'none');
		} else {
			$('#refund_freight_span').css('display', 'none');
		}
	}

	function _onChangeRefundCarryFee() {
		var isRefundCarryFee = parseInt($('#is_refund_carry_fee').val());

		if (isRefundCarryFee == 1) {
			$('#refund_carry_fee_span').css('display', '');
		} else if (isRefundCarryFee == 2) {
			$('#refund_carry_fee_span').css('display', 'none');
		} else {
			$('#refund_carry_fee_span').css('display', 'none');
		}
	}

    function _onChangeTrapsFreight() {
        var isTrapsFreight = parseInt($('#is_traps_freight').val());

        if (isTrapsFreight == 1) {
            $('#traps_freight_span').css('display', '');
        } else if (isTrapsFreight == 2) {
            $('#traps_freight_span').css('display', 'none');
        } else {
            $('#traps_freight_span').css('display', 'none');
        }
    }

    function _onChangeTrapsCarryFee() {
        var isTrapsCarryFee = parseInt($('#is_traps_carry_fee').val());

        if (isTrapsCarryFee == 1) {
            $('#traps_carry_fee_span').css('display', '');
        } else if (isTrapsCarryFee == 2) {
            $('#traps_carry_fee_span').css('display', 'none');
        } else {
            $('#traps_carry_fee_span').css('display', 'none');
        }
    }


	function onSaveRefund(ev) {
		var para = {
			rid: $('#rid').val(),
			oid: $('#oid').val(),
			wid: $('#wid').val(),
			type: $('#type').val(),
			is_refund_freight: $('#is_refund_freight').val(),
			refund_freight: $('#refund_freight').val(),
			is_refund_carry_fee: $('#is_refund_carry_fee').val(),
			refund_carry_fee: $('#refund_carry_fee').val(),
            is_traps_freight: $('#is_traps_freight').val(),
            freight: $('#traps_freight').val(),
            is_traps_carry_fee: $('#is_traps_carry_fee').val(),
            carry_fee: $('#traps_carry_fee').val(),
			reason_type: $('#reason_type').val(),
            reason: $('#reason_detail').val(),
			note: $('#note').val()
		};

		if (para.type == 0)
        {
            alert('请选择退货类型！');
            return;
        }

        if(para.is_refund_freight == 1)
        {
            var max_freight = $('#refund_freight').attr('data-freight');
            if(parseInt(para.refund_freight)> parseInt(max_freight))
            {
                alert('填写的退运费金额大于可退金额');
                return;
            }
        }else {
            para.refund_freight = 0;
        }

        if(para.is_refund_carry_fee == 1)
        {
            var max_fee = $('#refund_carry_fee').attr('data-fee');
            if(parseInt(para.refund_carry_fee)> parseInt(max_fee))
            {
                alert('填写的退搬运费金额大于可退金额');
                return;
            }
        }else {
            para.refund_carry_fee = 0;
        }

		if (para.type == 2)
        {
            var date = $('#select_delivery_date').val();
            var	hour_start = $('#select_delivery_time').val();
            var hour_end = $('#select_delivery_time_end').val();

            if (parseInt(hour_start) <= 0 || parseInt(hour_end) <= 0)
            {
                alert('请选择配送时间！');
                return;
            }
            para.delivery_date = date;
            para.delivery_time = hour_start;
            para.delivery_time_end = hour_end;
        }

        var products = [];


		if (parseInt(para.reason) == 0) {
			alert('请选择退货原因！');

			return false;
		}
		if (!para.rid && !confirm("您是否确定将所选产品退货？")) {
			return false;
		}

		if (!confirm("你选择的退货仓库是：" + para.wid + "号仓库")) {
			return false;
		}

		$('._j_product').each(function () {
			var cb = $(this),
				pid = cb.data('pid'),
				num = parseInt(cb.find('input[name=apply_rnum]').val()),
				price = parseFloat(cb.find('input[name=price]').val());
			if (K.isNumber(num)) {
				products.push(pid + ':' + num + ':' + price);
			}
		});

		$(this).attr('disabled', true);
		para.product_str = products.join(',');
		K.post('/order/ajax/save_refund_new.php', para,
            function(data){
		        if(data.rid >0)
                {
                    window.location.href = '/order/edit_refund_new.php?rid=' + data.rid;
                }
            },
            function(err){
                alert(err.errmsg);
                $('#_j_btn_save_refund').attr('disabled', false);
                return;
            }
        );
	}

    // 驳回退款单商品（未审核状态）
    function onRebutRefund() {
        var rid = $(this).attr('data-rid'),
            oid = $(this).attr('data-oid');

        var para = {rid: rid, oid: oid};

        if (!confirm('确认要驳回该退款单？')) {
            return;
        }

        K.post('/order/ajax/rebut_refund_order.php', para, function (ret) {
            alert(ret.st != 0 ? '操作已成功' : '操作失败！请联系管理员！');
            window.location.reload();
        });
        return;
    }

	// 确认退货单状态
	function onChgRefundStep(ev) {
        $(this).attr('disabled', true);
		ev.preventDefault();
		var tgt = $(ev.currentTarget);
        var para = {
            rid: $('#rid').val(),
            oid: $('#oid').val(),
            optype: tgt.data('optype'),
            next_step: tgt.data('next_step'),
			refund_freight: $('#refund_freight').val(),
            refund_carry_fee: $('#refund_carry_fee').val(),
			is_refund_freight: $('#is_refund_freight').val(),
            is_refund_carry_fee: $('#is_refund_carry_fee').val(),
            freight: $('#traps_freight').val(),
            carry_fee: $('#traps_carry_fee').val(),
            is_traps_freight: $('#is_traps_freight').val(),
            is_traps_carry_fee: $('#is_traps_carry_fee').val()
        };

        var refund_type = $('#type').val();

        if (refund_type == 0)
        {
            alert('请选择退货类型！');
            $(this).attr('disabled', false);
            return;
        }

        if (para.next_step == 2 && refund_type == 2)
		{
		    para.refund_type = refund_type;
			var date = $('#select_delivery_date').val();
			var	hour_start = $('#select_delivery_time').val();
			var hour_end = $('#select_delivery_time_end').val();

			if (parseInt(hour_start) <= 0 || parseInt(hour_end) <= 0)
            {
                alert('请选择配送时间！');
                $(this).attr('disabled', false);
                return;
            }
            para.delivery_date = date + ' ' + hour_start + ':00:00';
            para.delivery_date_end = date + ' ' + hour_end + ':00:00';
		}

        var canSubmit = true;

		//入库，需要再取一下实际入库的数量
		if (para.next_step == 3) {
			var products = [];
			$('._j_product').each(function () {
				var cb = $(this),
					pid = cb.data('pid'),
					rnum = parseInt(cb.find('input[name=rnum]').val()),
                    dnum = parseInt(cb.find('input[name=damaged_num]').val());

                rnum = K.isNumber(rnum)? rnum: 0;
                dnum = K.isNumber(dnum)? dnum: 0;

                var canRefundNum = parseInt(cb.find('input[name=can_refund_num]').val());
                if (canRefundNum < rnum+dnum){
                    alert('入库+损坏数量 不能 大于 可退数量！！');
                    canSubmit = false;
                    $(this).attr('disabled', false);
                    return false;
                }

				products.push(pid + ':' + rnum + ':' + dnum);
			});
			para.product_str = products.join(',');
		}

        //售后审核并提交财务结款，获取最终确认退货的数量
        if (para.optype == 'final_audit'){
            var products = [];
			$('._j_product').each(function () {
				var cb = $(this),
					pid = cb.data('pid'),
					num = parseInt(cb.find('input[name=num]').val());

				if (!K.isNumber(num)) {
					alert('请确认[商品ID:'+pid+'] 的最终退货数量');
                    canSubmit = false;
                    return false;
				}
                var damagedNum = parseInt(cb.find('input[name=damaged_num]').val()),
                    stockRnum = parseInt(cb.find('input[name=rnum]').val());
                if (num < stockRnum){
                    alert('[商品ID:'+pid+'] 最终退货数量不能（少于）入库数量');
                    canSubmit = false;
                    return false;
                }
                if (num > stockRnum+damagedNum){
                    alert('[商品ID:'+pid+'] 最终退货数量不能（大于）入库数量+损坏数量');
                    canSubmit = false;
                    return false;
                }

                products.push(pid + ':' + num);
			});

			para.product_str = products.join(',');
        }

        if (!canSubmit){
            $(this).attr('disabled', false);
            return false;
        }

		if (parseInt(para.is_refund_freight) == 0) {
            $(this).attr('disabled', false);
			alert('请选择是否退运费！');

			return false;
		}

		if (parseInt(para.is_refund_carry_fee) == 0) {
            $(this).attr('disabled', false);
			alert('请选择是否退搬运费！');

			return false;
		}

        K.post('/order/ajax/change_refund_step_new.php', para, function(){
            alert('操作已成功');
            window.location.reload();
        });
	}
    
    function complateVirtualRefund()
    {
        var param = {
            rid : $('#rid').val()
        };
        $(this).attr('disabled', true);
        K.post('/order/ajax/complate_virtual_refund.php', param, 
            function(){
                alert('操作已成功');
                window.location.reload();
            },
            function(err){
                alert(err.errmsg);
                $('#complate_virtual_refund').attr('disabled', false);
            }
        );
    }

	function refundCoupon(ev) {
		ev.preventDefault();
		var rid = $(this).data('rid'),
			id = $(this).data('id'),

			para = {rid: rid, id: id
			};

		$(this).attr('disabled', true);
		K.post('/order/ajax/refund_coupon.php', para, refundCouponSuccess);
	}

	function refundCouponSuccess(data) {
		alert('退券成功！');
		window.location.reload();
	}

	function changeReasonDetail() {
	    var type = $('#reason_type').val();
		var reasons = $('#reason_detail').attr('data-reason-detail');
        var obj = eval('(' + reasons + ')');
        var reason_detail = obj[type];
        var html = '<option value="0">-请选择-</option>';
        if(type>0) {
            $.each(reason_detail, function (n, value) {
                html += '<option value="' + n + '">' + value + '</option>';
            });
        }
        $('#reason_detail').html(html);
    }

    function getRefundReason() {
        var type = $('#reason_type').val();
        var reason = $('#reason_detail').attr('data-reason');
        var reasons = $('#reason_detail').attr('data-reason-detail');
        var obj = eval('(' + reasons + ')');
        var reason_detail = obj[type];
        var html = '<option value="0">-请选择-</option>';
        if(type>0) {
            $.each(reason_detail, function (n, value) {
                if (parseInt(reason) == n) {
                    html += '<option value="' + n + '" selected>' + value + '</option>';
                }
                else {
                    html += '<option value="' + n + '">' + value + '</option>';
                }
            });
        }
        $('#reason_detail').html(html);
    }

    function changeRefundType() {
	    var type = $(this).val();
	    if (type == 2)
        {
            $('#delivery_date').css('display', 'block');
            $('#virtual_product_area').css('display', 'block');
        }
        else if (type == 3 || type==1)
        {
            $('#delivery_date').css('display', 'none');
            $('#virtual_product_area').css('display', 'block');
        }
        else
        {
            $('#delivery_date').css('display', 'none');
            $('#virtual_product_area').css('display', 'none');
        }
        
        if (type == 4){
            $('#refund_type_notice').css('display', 'inline-block');
        }
        else{
            $('#refund_type_notice').css('display', 'none');
        }
    }

	main();

})();