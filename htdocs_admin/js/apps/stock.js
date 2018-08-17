(function () {

	function main() {
		$('#_j_save_stock').click(_onSaveStock);
		$('#_j_btn_save_stock_in').click(_onSaveStockInEdit);
		$('#_j_btn_confirm_paid').click(stockInConfirmPaid);
		$('#city_id').on('change', changeCity);
		//盘库
		_initCheckBox();
		var _this = this;
		$('.check_stock').on('click', function(){
			var wid = $(this).attr('data-wid');
			var sid = $(this).attr('data-sid');
			_this.hRealStock = $(this).parents('tr').find('._js_real_stock');
            
			_showCheckBox(wid, sid);
		});

		// 库存选择
		$('#_j_select_stock').change(changeStock);

		//发起调拔申请
		$('#_j_chg_stock_shift_apply').on('click', changeStockShiftApply);
		//驳回调拔单
		$('#_j_chg_stock_shift_rebut').on('click', changeStockShiftRebut);
		
		// 移库
		$('#_j_save_stock_shift').on('click', saveStockShift);
		$('#_j_show_shift_product').on('click', selectStockShiftProduct);
		$('._j_cannel_stock_shift').on('click', cannelStockShift);
		$('._j_del_stock_shift_product').on('click', delStockShiftProduct);
		$('#_j_chg_step_stock_shift').on('click', changeStepStockShift);
        
        $('._j_del_stockin').on('click', deleteStockIn);
        
        // 入库单退货
        $('#_j_stockIn_Refund').on('click', showStockinRefund);
        $('#_j_btn_create_refund').on('click', createRefund);
        $('#_j_stockin_refund_confirm').on('click', confirmRefund);
        
        // 临采入库
        $('#_j_tmp_stock_in').on('click', saveTmpStockin);
        
        //入库单批量支付
        $('#_j_confirm_bulk_paid').on('click', confirmBulkPaid);
        
        // 兑账
        $('#checkAccountBox').on('show.bs.modal', showCheckAccountBox);
        $('#_j_save_check_account_box').on('click', saveCheckAccount);

        //盘点计划
        $('#btn_add_plan').on('click', addPlanModalShow);
        $('#inventory_type').on('change', changeInventoryType);
        $('#btn-save-plan').on('click', savePlan);
        $('.is_random').on('click', changeIsRandom);
        $('.edit_inventory_plan').on('click', editInventoryPlan);
        $('.del_inventory_plan').on('click', delInventoryPlan);
        $('#modal_add_plan').on("hide.bs.modal", resetAddPlanModal);
        $('#btn_sure_plan_products').on('click', surePlanProductsModalShow);
        $('#btn_create_task').on('click', createTaskModalShow);
        $('#modal_sure_plan_products').on('click', clickSurePlanProducts);
        $('#btn-save-plan-product').on('click', savePlanProduct);
        $('.allocate_method').on('change', changeAllocateMethod);
        $('#allocate_product_list').on('click', clickAllocateProductList);
        $('#create_inventory_task').on('click', createInventoryTask);
        $('.allocate_inventory_task').on('click', allocateInventoryTask);
        $('#modal_warehouse_staff_list').on('click', clickStaffListModal);
        $('.show_inventory_degree').on('click', showInventoryDegree);
        $('#modal_show_inventory_degree').on('click', clickInventoryDegreeModal);
        $('#diff-product-area-list').on('click', clickDiffProductAreaList);
        $('.deal_diff_num').on('click', dealDiffNUm);
        $('#btn-update-diff-num').on('click', updateDiffNum);
        $('#sure_inventory_plan').on('click', sureInventoryPlan);
        $('#inventory_times').on('change', changePlanTimes);

		//支付金额变动
		$('#stock_in_list input[name=will_pay]').on('change', changeWillPayTotal);

		//生成结算单
		$('#btn_statements').on('click', saveStatements);
		//支付结算单
		$('#_j_pay_stockin_statement').on('click', confirmStockInStatementPaid);
		//全选(结算单)
		$('#checkAll').on('click', checkAll);

		//库存预警
		$('.show_supplier_list_modal').on('click', showSupplierListModal);

		//撤回
		$('._j_recall_stockin').on('click', recallStockIn);
		switch ($('#navbar').attr('data-paid')) {
			case '0':
				$($('#navbar li')[1]).attr('class','active');
				break;
			case '2':
				$($('#navbar li')[2]).attr('class','active');
				break;
			case '3':
				$($('#navbar li')[3]).attr('class','active');
				break;
			case '1':
				$($('#navbar li')[4]).attr('class','active');
				break;
			default:
				$($('#navbar li')[0]).attr('class','active');
				break;
		}

		//其他出库单
        $('#other_stock_out_order_type').on('change', changeOtherStockOutOrderType);
        $('#save_other_stock_out_order').on('click', saveOtherStockOutOrder);
        $('.change_other_stock_out_order').on('click', auditOtherStockOutOrder);
        $('#other_stock_out_product_area').on('click', delOtherStockOutProduct);
		$(document).ready(function () {
            if ($('#save_other_stock_out_order').length > 0)
            {
                var type = $('#_j_stock_shift_form').find('select[name=type]').val();

                if (type == '' || type == 'undefined')
                {
                    return;
                }
                var list = $('#other_stock_out_order_reason').find('option');
                list.each(function () {
                    $(this).hide();
                    if ($(this).attr('data-type') == type) {
                        $(this).show();
                    }
                });
            }
        });

		$('.show_edit_refund_price').on('click', showEditRefundPrice);
		$('#update_refund_product_price').on('click', updateRefundProductPrice);

		//调拨单强制刷新
		$('.refresh_stock_shift_vnum').on('click', refreshStockShiftVnum);
		
		$('.in_use_amount').blur(calRemainderMoney);

		//删除结算单
		$('._j_del_statement').on('click', deleteStatementOrd);

		//查看结算单商品明细
		$('._j_statements_product_detail').on('click', statementProductDetail);
    }

    //修改成本价
	$('.edit_sku_cost').on('click', editSkuCost);
    //修改附加成本
    $('#editFringCost').on('shown.bs.modal', showEditFringCost);
    $('#saveFringCost').on('click', saveFringCost);
    
	//保存成本
	$('.save_stock_cost_price').on('click', saveStockCostPrice);
	//全选和反选逻辑
	function checkAll(){
		$("#stock_in_list input[type='checkbox']").prop('checked', $(this).prop('checked'));
	}

	// 撤回入库单
	function recallStockIn(){
		var para = {
			id: $(this).attr('data-id'),
			num: $(this).attr('data-num'),
            statement_id: $(this).attr('data-statement-id')
		};

		if (!confirm('确认撤回入库单:'+para.id)){
			return;
		}

		K.post('/finance/ajax/recall_stockin.php', para, function(ret){
			if (ret.res=='succ'){
				alert('操作成功！');
				window.location.reload();
			} else {
				alert('操作失败！');
			}
		});
	}

	//更新结算单总计金额
	function changeWillPayTotal() {
		var total =0;
        $('#stock_in_list input[name=will_pay]').each(function () {
            var price = Math.round($(this).val()*100);
            total += price;
        });
        total = (total/100).toFixed(2);
        total = fmoney(total);
        $('#stockin_statement_detail_total').html(total);
        $('.remainder_money').text('剩余应付：￥' + total);
        calRemainderMoney();
	}
	//数字千位符
    function fmoney(s, n) {
        n = n > 0 && n <= 20 ? n : 2;
        s = parseFloat((s + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "";
        var l = s.split(".")[0].split("").reverse(), r = s.split(".")[1];
        t = "";
        for (i = 0; i < l.length; i++) {
            t += l[i] + ((i + 1) % 3 == 0 && (i + 1) != l.length ? "," : "");
        }
        return t.split("").reverse().join("") + "." + r;
    }

    //生成结算单
	function saveStatements() {
		var stockIns = [];
		$(this).attr("disabled",false);
		$('input[name=account_bill]').each(function () {
				if ($(this).is(':checked')){
					stockIns.push($(this).val());
				}
			}
		);
        if(stockIns.length>0) {
            var para = {
                ids: stockIns
            };
            K.post('/warehouse/ajax/get_statements_detail.php', para, function (data) {
                $('#statements_info .header').html(data.header);
                $('#statements_info .modal-body').html(data.html);
                $('#statements_info').modal();
            });
        } else {
            alert('请选择需要结算的入库单');
            $(this).attr("disabled",false);
		}
	}

	function confirmStockInStatementPaid(){
		var bulkpayObj = [];
		var paidSource = $('#_j_bulk_paid_source').val();

		// 需要支付类型
		if (typeof paidSource=='undefined'||paidSource.length==0||paidSource=='0'){
			alert('请选择支付方式！！'); return false;
		}

        if (!confirm('您确定支付吗？'))
        {
            return false;
        }

		var chkVal = true;
		var totalPrices = 0;
		$('input[name=will_pay]').each(function(){
			var _obj, _para;
			_obj = $(this).closest('.stockin_info');
			_para = {
				id: _obj.attr('data-id'),
				price: $(this).val()
			};

			if (_para.price.length==0){
				chkVal = false;
				return false;
			}

			bulkpayObj.push(_para);
			totalPrices += _para.price*1;
		});

		if (!chkVal){
			alert('支付入库单金额不能为空'); return false;
		}
        var useAmount = parseFloat($('input[name=in_use_amount]').val()) * 100;
        var supplier_id = parseFloat($('input[name=supplier_id]').val());

		var para = {
			paid_source: paidSource,
			bluk_datas: JSON.stringify(bulkpayObj),
			statement_id: $(this).attr('data-id'),
			use_amount: useAmount,
            supplier_id: supplier_id,
		};


		$(this).attr('disabled', true);
		K.post('/warehouse/ajax/stockin_statement_paid.php', para,
			function(ret){
				alert('操作已成功！');
				window.location.href="/finance/supplier_bill_list.php?sid="+ret.sid;
				$('#_j_pay_stockin_statement').attr('disabled', false);
			},
			function(err){
				alert(err.errmsg);
				$('#_j_pay_stockin_statement').attr('disabled', false);
			}
		);
	}

	// 保存库存
	function _onSaveStock(ev) {
        
        var para = {
            sid: $('input[name=sid]').val(),
            wid: $('select[name=wid]').val(),
            city_id: $('select[name=city_id]').val(),
            //cost: $('input[name=cost]').val(),//成本不能人为更新 2016-04-25
            purchase_price: $('input[name=purchase_price]').val(),
            alert_threshold: $('input[name=alert_threshold]').val(),
			refer: $(ev.currentTarget).data('refer')
        };

        if (para.wid == '0' && para.city_id == '0'){
            alert('城市和仓库至少选择一个！'); return false;
        }

        if (para.wid == '0' && para.city_id == 'undefined')
        {
            alert('请选择仓库！');
            return;
        }
        
		K.post('/warehouse/ajax/save_stock.php', para, _onSaveStockSuccess);
	}
	function _onSaveStockSuccess(data) {
		alert('保存成功');
		if (data.url) {
			window.location.href = data.url;
		}
	}

	// 保存订单
	function _getStockInFormInfo() {
		var para = {
			id: $('input[name=id]').val(),
			oid: $('input[name=oid]').val(),
			sid: $('input[name=sid]').val(),
			//contact_name: $('input[name=contact_name]').val(),
			//contact_phone: $('input[name=contact_phone]').val(),
			//delivery_date: $('input[name=delivery_date]').val(),
			step: $('select[name=step]').val(),
			note: $('textarea[name=note]').val(),
			wid: $('input[name=wid]').data('wid'),
			payment_type: $('input[name="payment_type"]').val()
		};
		return para;
	}
	function _onSaveStockInEdit(ev) {
		var para = _getStockInFormInfo();

		var products = [];

		if (!confirm("您是否确定将所选产品入库？")) {
			return;
		}

		$('._j_product').each(function(){
			var cb = $(this),
				sid = cb.data('sid'),
				num = parseInt(cb.find('input[name=num]').val()),
				price = parseFloat(cb.find('input[name=price]').val());
			if (cb.data('source')==1 && K.isNumber(num) && num > 0) {
				products.push(sid + ':' + num + ':' + price);
			}
		});

		para.product_str = products.join(',');
        para.source = $(this).closest('form').find('input[name=source]').val();
		$(this).attr('disabled', true);
		K.post('/warehouse/ajax/save_stock_in.php', para, _onSaveStockInEditSuccess);
	}
	function _onSaveStockInEditSuccess(data) {
		if (data.id) {
			window.location.href = '/warehouse/edit_stock_in.php?id=' + data.id;
		}
	}
	
	//出库单确认支付
	function stockInConfirmPaid (){
		var box = $(this).closest('._j_dialog');
		var id = $(this).attr('data-id'),
			payment_type = box.find('select[name="payment_type"]').val(),
            paid_source = box.find('select[name=paid_source]').val(),
			realAmount = box.find('input[name="real_amount"]').val(),
			note = box.find('textarea[name="note"]').val();
		var sid = $(this).attr('data-sid');
		var para = {
			id: id,
			type: $(this).attr('data-type'),
			payment_type: parseInt(payment_type),
            paid_source: parseInt(paid_source),
			real_amount: parseInt(realAmount),
			note: note
		};
		
        if (paid_source == 0){
            alert('请选择支付来源！');return;
        }
        
		if (_checkParams()){
			$(this).attr('disabled', true);
			K.post('/warehouse/ajax/confirm_paid.php', para, function(ret){
				alert('操作成功！');
				window.location.href = '/warehouse/stock_in_lists.php?sid='+sid;
			});
		}
		
		function _checkParams(){
			var st = true;
			if (para.payment_type==0) {alert('请选择 “支付类型”'); st=false;}
			else if (isNaN(para.real_amount)) { alert('请填写“数量” 且 必须为数字'); st=false; }
			
			return st;
		}
	}

	//盘库
	function _showCheckBox(wid, sid){
		this.wid = wid;
		this.sid = sid;

		var box = $('#checkStockBox');
		box.modal();

		//init
		this.hType.attr('checked', false);
		this.hNum.val('');
		this.hRemark.val('');
		
		var _this = this;
		box.find('select[name="wid"] option').each(function(){
			if ($(this).val() == wid){
				$(this).get(0).selected = true;
				
				if (wid != 0){
                    $('#show_curr_num').html(_this.hRealStock.find('._js_real_stock_'+wid).attr('data-num'));
					_this.hWarehouse.attr('disabled', true);
				} else {
                    var stockNotice = '3#'+_this.hRealStock.find('._js_real_stock_3').attr('data-num')
                                    +': 4#'+_this.hRealStock.find('._js_real_stock_4').attr('data-num');
                    $('#show_curr_num').html(stockNotice);
                }
			}
		});
	}

	function _initCheckBox(){
		var box = $('#checkStockBox');
		var _this = this;
		var maxLength = parseInt(box.find('._j_maxLength').html());

		_this.hType = box.find('input[name="type"]');
		_this.hNum = box.find('input[name="num"]');
		_this.hWarehouse = box.find('select[name="wid"]');
		_this.hRemark = box.find('input[name="remark"]');

		hRemark.on('input', function(){
			_calculateInputLength(this);
		});

		$('#checkStockSubmit').on('click', function(){
			_this.type = box.find('input[name="type"]:checked').val();
			_this.num = _this.hNum.val();
			_this.remark = _this.hRemark.val();
			_this.wid = _this.hWarehouse.val();

			var st = _checkVal();
			var data = {
				wid: _this.wid,
				sid: _this.sid,
				type: _this.type,
				num: _this.num,
				remark: _this.remark
			};
			var localThis = this;

			if (st){
				$(this).attr('disabled', true);
				$.ajax({
					url: '/warehouse/ajax/save_chk_stock.php',
					type: 'POST',
					data: data,
					dataType: 'json',
					success: function(ret){
						$(localThis).attr('disabled', false);
						if (ret.errno == 1){
							box.modal('hide');
							_updateShowStock(ret.finalNum);
							alert('更新成功！');
						} else {
							alert(ret.errmsg||'更新失败！请联系管理员！')
						}
					}
				});
			} else {
				return false;
			}
		});

		var _calculateInputLength = function(that){
			var len = $(that).val().length;
			var hCounter = $(that).parent().find('._j_counter');
			hCounter.html(len);

			if (maxLength < len){
				hCounter.css('color', 'red');
			} else {
				hCounter.css('color', 'black');
			}
		};

		var _checkVal = function(){
			var status = true;

			if (_this.num.length == 0) { alert('请填写 “数量”'); status=false; }
			else if (isNaN(_this.num)) { alert('“数量” 必须为数字'); status=false; }
			else if (_this.wid=="0") {alert('请选择 “仓库”'); status=false;}
			//else if (_this.remark.length == 0) { alert('请填写 “备注”'); status=false; }
			else if (_this.remark.length > maxLength){ alert('“备注” 字数应在'+maxLength+'之内'); status=false;}

			return status;
		};

		var _updateShowStock = function(finalNum){
			var hRealStock_Wid = this.hRealStock.find('._js_real_stock_'+this.wid);
			var stocks = hRealStock_Wid.html().split('/');
			var _html = finalNum + ' /' + (stocks.length==2?stocks[1]:0);
			hRealStock_Wid.html(_html);
		};

	}
	
	// 编辑库存，更新对应值
	function changeStock(){
		var wid = $(this).val(),
			cost = $('._j_cost_'+wid).data('cost'+wid),
            purchasePrice = $('._j_purchaseprice_'+wid).data('purchaseprice'+wid),
			alert = $('._j_alert_'+wid).data('alert'+wid);
			
		$('form').find('input[name=cost]').val(cost);
		$('form').find('input[name=alert_threshold]').val(alert);
        $('form').find('input[name=purchase_price]').val(purchasePrice);
		
		if (parseInt(wid)){
			$('#_j_df_cost_desc').hide();
		} else {
			$('#_j_df_cost_desc').show();
		}
		
	}
	
	/*********************移库*************************/

	function changeStockShiftApply() {
		var para = {
			ssid: $(this).data('ssid'),
			type: 'apply'
		};
		K.post("/warehouse/ajax/change_stock_shift_status.php", para, function (ret) {
			alert('申请成功');
			window.location.href="/warehouse/stock_shift_detail.php?ssid="+para.ssid;
		});
	}

	function changeStockShiftRebut() {
		var para = {
			ssid: $(this).data('ssid'),
			type: 'rebut',
			reason: $('#stockShiftRebutModal').find('textarea[name=rebut_reason]').val()
		};
		if(para.reason == '' || para.reason == undefined)
		{
			alert('请填写驳回原因！');
			return false;
		}
		K.post("/warehouse/ajax/change_stock_shift_status.php", para, function (ret) {
			alert('驳回成功');
			window.location.reload();
		});
	}
	
	function saveStockShift(){
		var dialog = $('#_j_stock_shift_form');
		var para = {
			ssid: dialog.attr('data-ssid'),
			wid_out: dialog.find('select[name=out_wid]').val(),
			wid_in: dialog.find('select[name=in_wid]').val(),
			note: dialog.find('textarea[name=note]').val()
		};
		
		if (para.wid_out=='0' || para.wid_in=='0'){
			alert('请选择出入库仓库'); return false;
		} else if (para.wid_out == para.wid_in){
			alert('移库仓库不能相同！'); return false;
		}
		
		K.post('/warehouse/ajax/save_stock_shift.php', para, function(ret){
			alert('保存成功！');
			window.location.href = '/warehouse/stock_shift.php?ssid='+ret.ssid;
		});
	}
	
	function selectStockShiftProduct(){
        var type = $(this).attr('data-type');
        var order_type = $(this).attr('data-order-type');
		var form = $('#_j_stock_shift_form');
		var dialog = $('#dlgShiftStock');
		var widOut = form.find('select[name="out_wid"]').val(),
			ssid = form.attr('data-ssid'),
			queryStr = '',
			keyword = '';
		
		if (widOut==0){
			alert('请选择 移出仓库ID！'); return false;
		} 
		if (ssid==0){
			alert('移库单异常，请重新创建！'); return false;
		}
		
		//刷新选商品的页面
		var _flashSelectedProductForShift = function(){
			var para = {
				wid: widOut,
				ssid: ssid,
				query_str: queryStr,
				keyword: keyword,
                type: type,
                order_type: order_type
			};
			K.post('/warehouse/ajax/block_stock_shift.php', para, function(ret){
				dialog.find('.modal-content').html(ret.html);
			});
		};
		
        if (dialog.find('.modal-content').html().length==0)
        {
            // 默认加载
            _flashSelectedProductForShift();

            //注册点击事件
            dialog.on('click', function(env){
                if ($(env.target).is('._j_select_product_cate')){
                    queryStr = $(env.target).attr('data-href');
                    _flashSelectedProductForShift();
                } else if ($(env.target).is('._j_shift_search_product')){
                    keyword = $(env.target).parent().find('input[name=keyword]').val();
                    _flashSelectedProductForShift();
                } else if ($(env.target).is('#_j_btn_save_products')){
                    _saveStockShiftProducts();
                } else if ($(env.target).is('#_j_btn_add_other_stock_out_products')){
                    _addOtherStockOutProducts();
                }

            });
        }
        
		$('#dlgShiftStock').modal();
		
		
		var _saveStockShiftProducts = function(){
			var products = [];
			var sid, rest, num;
			dialog.find('._j_product_item').each(function(){
				sid = $(this).attr('data-sid');
				rest = $(this).find('input[name=num]').attr('data-rest');
				num = $(this).find('input[name=num]').val();
				
				if (num>0 && parseInt(rest)>=parseInt(num)){
					products.push(sid+':'+num);
				}
			});
			
			var para = {
				products: products,
				ssid: ssid
			};
			
			K.post('/warehouse/ajax/save_stock_shift_product.php', para, function(ret){
				if (ret.ret == 1){
					alert('添加成功！');
					window.location.href = '/warehouse/stock_shift.php?ssid='+ret.ssid;
				} else {
					alert(ret.ret==-1? '选择商品有误，请核对':'添加失败！');
				}
			});
		};
		
		var _addOtherStockOutProducts = function () {
            var products = [];
            var sid, rest, num;
            var sids = '';
            var i = 0;
            var order_type = dialog.attr('data-order-type');
            dialog.find('._j_product_item').each(function(){
                sid = $(this).attr('data-sid');
                rest = $(this).find('input[name=num]').attr('data-rest');
                num = $(this).find('input[name=num]').val();

                if (order_type == '1')
                {
                    if ( num>0 && parseInt(rest)<parseInt(num))
                    {
                        if (i == 0)
                        {
                            sids += sid;
                        }
                        else
                        {
                            sids += '、' + sid ;
                        }
                    }

                    if (num>0 && parseInt(rest)>=parseInt(num)){
                        products.push(sid+':'+num);
                    }
                }
                else if (order_type == '2')
                {
                    if (num > 0)
                    {
                        products.push(sid+':'+num);
                    }
                }
            });


            if (order_type == '1' && sids.length > 0)
            {
                alert('商品' + sids + '库存不足！');
                return;
            }

            var para = {
                oid: $('#dlgShiftStock').attr('data-oid'),
                products: products,
                wid: widOut,
                order_type: order_type
            };

            if (order_type == '1' && products.length == 0)
            {
                alert('未选择商品或库存不足！');
                return;
            }
            if (order_type == '2' && products.length == 0)
            {
                alert('未选择商品！');
                return;
            }

            K.post('/warehouse/ajax/save_other_stock_out_product.php', para, function(ret){
                if (ret.ret == 1){
                    alert('添加成功');
                    window.location.reload();
                } else {
                    alert(ret.ret==-1? '选择商品有误，请核对':'添加失败！');
                }
            });
        }
	}
	
	function cannelStockShift(){
		var ssid = $(this).attr('data-ssid');
		
		if (confirm('确认取消改移库单？')) {
			K.post('/warehouse/ajax/cannel_stock_shift.php', {ssid:ssid}, _operateCallback);
		}
	}
	
	function delStockShiftProduct(){
		var ssid = $(this).attr('data-ssid'),
			sid = $(this).attr('data-sid');
	
		if (confirm('确认删除移库单商品？')){
			para = {ssid:ssid, sid:sid};
			K.post('/warehouse/ajax/del_stock_shift_product.php', para, _operateCallback);
		}
	}
	
	function _operateCallback(ret){
		if (ret.st){
			alert('操作已成功！');
			window.location.reload();
		} else {
			alert('操作失败！');
			return false;
		}
	}
	
	function changeStepStockShift(){
		var ssid = $(this).attr('data-ssid'),
			nextStep = $(this).attr('data-next_step'),
			widOut = $(this).attr('data-wid_out'),
			widIn = $(this).attr('data-wid_in');
	
		if (confirm(nextStep==2? '确认出库？': '确认入库？')){
			para = {ssid:ssid, next_step:nextStep, wid_out:widOut, wid_in:widIn};
			var _this = this;
			$(this).attr('disabled', true);
			K.post('/warehouse/ajax/save_stock_shift.php', para, function(ret){
				
				$(_this).attr('disabled', false);
				if (ret.st){
					alert('操作成功！');
					window.location.href = '/warehouse/stock_shift_list.php';
				} else {
					alert('操作失败！');
				}
			})
		}
	}

    // 删除入库单/入库商品
    function deleteStockIn(){
        var para = {
            optype: $(this).attr('data-optype'),
            id: $(this).attr('data-id'),
            sid: 0
        };
        
        if (!confirm('确认删除' + (para.optype=='del_product'?'入库单商品':'入库单'))){
            return;
        }
        
        if (para.optype == 'del_product'){
            para.sid = $(this).attr('data-sid');
        }
        
        K.post('/warehouse/ajax/delete_stockIn.php', para, function(ret){
            alert('删除成功！');
            if (ret.optype == 'del_product'){
                window.location.reload();
            } else {
                window.location.href = '/warehouse/stock_in_lists.php';
            }
        });
    }
    
    
    // 入库单退货
    function showStockinRefund(){
        var para = {
            id : $(this).attr('data-id'),
            optype : 'show'
        };
        
        K.post('/warehouse/ajax/refund_stockin.php', para, function(ret){
            
            var box = $('#stockinRefundModal');
            box.find('#stockInProductArea').html(ret.html);
            box.attr('data-id', ret.id);
            box.modal();
        });
    }
    
    // 创建退货单
    function createRefund(){
        
        var productList = [];
        var temp = {};
        var status = true;
        var loc;
        $('#stockInProductArea').find('._j_product').each(function(){
            temp = {};
            temp.sid = $(this).attr('data-sid');
            temp.price = $(this).find('input[name=price]').val()*100;
            temp.num = $(this).find('input[name=num]').val()*1;
            loc = $(this).find('input[name=location]');
            temp.loc = loc.length? loc.val(): '';

            if (temp.num > ($(this).find('.snum').html()*1-$(this).find('.rnum').html()*1)){
                alert('退货数量错误！(商品ID：'+temp.sid+'）');
                status = false;
                return;
            }
            if (temp.num > $(this).find('.loc_num').html()*1){
                alert('退货数量大于货位库存！(商品ID：'+temp.sid+'）');
                status = false;
                return;
            }
            if (temp.num!=0 && temp.loc.length==0){
                alert('货位不能为空！(商品ID：'+temp.sid+'）');
                status = false;
                return;
            }
            
            if (temp.price!=0 && temp.num!=0){
                productList.push(temp);
            }
        });
        if (!status){
            return false;
        }
        
        var para = {
            optype: 'create',
            id: $('#stockinRefundModal').attr('data-id'),
            product_list: JSON.stringify(productList)
        };
        
        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/refund_stockin.php', para, 
            function(ret){
                if (ret.st==1 && ret.srid){
                    window.location.reload();
                } else {
                    alert('退货失败！');
                }
                $('#_j_btn_confirm_refund').attr('disabled', false);
            },
            function(ret){
                alert(ret.errmsg);
                $('#_j_btn_create_refund').attr('disabled', false);
            }
        );
    }
    
    // 确认退货 退货出库
    function confirmRefund(){
        var para = {
            id: $(this).attr('data-id'),
            srid: $(this).attr('data-srid'),
            optype: 'confirm'
        };
        
        if (!confirm('确认退货出库？')){
            return false;
        }
        
        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/confirm_refund_stockin.php', para, function(ret){
            alert(ret.st==1? '操作成功': '操作失败');
            window.location.reload();
            //$('#_j_stockin_refund_confirm').attr('disabled', false);
        });
    }

    // 兑账
    function showCheckAccountBox(){
        var para = {
            flag: 'get',
            id: $(event.target).closest('tr').attr('data-id'),
            oid: $(event.target).closest('tr').attr('data-oid'),
            role: $(event.target).closest('tr').attr('data-role')
        };
        
        K.post('/warehouse/ajax/check_account.php', para, 
            function(ret){
                if (ret.errno==0)
                {
                    $('#checkAccountBox').find('.modal-body').html(ret.data.html);
                    if(ret.data.id != 0){
                        $('#_j_save_check_account_box').removeAttr('data-oid');
                        $('#_j_save_check_account_box').attr('data-id', ret.data.id);
                        $('#_j_save_check_account_box').attr('data-role', para.role);
                    } else if (ret.data.oid != 0){
                        $('#_j_save_check_account_box').removeAttr('data-id');
                        $('#_j_save_check_account_box').attr('data-oid', ret.data.oid);
                        $('#_j_save_check_account_box').attr('data-role', para.role);
                    }
                }
                else
                {
                    alert(ret.errmsg);
                    $('#checkAccountBox').modal('hide');
                }
            },
            function(err){
                alert(err.errmsg);
                $('#checkAccountBox').modal('hide');
            }
        );
    }
    
    function saveCheckAccount(){
        var para = {
            flag: 'check',
            id: $(this).attr('data-id'),
            oid: $(this).attr('data-oid'),
            role: $(this).attr('data-role')
        };
        
        K.post('/warehouse/ajax/check_account.php', para,
            function(ret){
                if (ret.errno==0)
                {
                    alert('操作成功');
                    window.location.reload();
                }
                else
                {
                    alert(ret.errmsg);
                }
            },
            function(err){
                alert(err.errmsg);
            }
        );
    }
    
    // 临采入库
    function saveTmpStockin(){
        var para = {
            type: 'tmp',
            oid: $('form').find('input[name=oid]').val()
        };
        var products = [];
        $('.products_area').find('input[name=bluk_tmp_stockin]').each(function(){
            var _para, _obj;
            if ($(this).is(':checked')){
                _obj = $(this).closest('.sales_order');
                _para = {
                    pid: _obj.attr('data-pid'),
                    sid: _obj.attr('data-sid'),
                    oid: _obj.attr('data-oid'),
                    num: _obj.attr('data-num')
                };
                products.push(_para);
            }
        });
        
        if (para.oid.length==0||products.length==0){
            alert('请选择入库商品！');return false;
        }
        para.products = JSON.stringify(products);
        
        K.post('/warehouse/ajax/complate_tmp_stockin.php', para, 
            function(ret){
                alert('操作已成功！');
                $('#_j_tmp_stock_in').attr('disabled', false);
            },
            function(err){
                alert(err.errmsg);
                $('#_j_tmp_stock_in').attr('disabled', false);
            }
        );
    }
    
    function confirmBulkPaid(){
        var bulkpayObj = [];
        var paidSource = $('#_j_bulk_paid_source').val();
        var paidSourceName = $('#_j_bulk_paid_source').find("option:selected").text();
        
        // 需要支付类型
        if (typeof paidSource=='undefined'||paidSource.length==0||paidSource=='0'){
            alert('请选择支付方式！！'); return false;
        }
        
        var chkVal = true;
        var totalPrices = 0;
        $('input[name=bluk_pay]').each(function(){
            var _obj, _para;
            if ($(this).is(':checked')){
                _obj = $(this).closest('.stockin_info');
                _para = {
                    id: _obj.attr('data-id'),
                    price: _obj.find('input[name=will_pay]').val()
                };
                
                if (_para.price.length==0){
                    chkVal = false;
                    return false;
                }
                
                bulkpayObj.push(_para);
                totalPrices += _para.price*1;
            }
        });
        
        if (!chkVal){
            alert('支付入库单金额不能为空'); return false;
        }
        
        if (bulkpayObj.length == 0){
            alert('请勾选【批量付】！'); return false;
        }
        
        var para = {
            paid_source: paidSource,
            bluk_datas: JSON.stringify(bulkpayObj)
        };
        
        //var note = '支付方式：'+paidSourceName+'\n总付金额：'+totalPrices+'元\n';
        
        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/bulk_paid.php', para, 
            function(ret){
                alert('操作已成功！');
                window.location.href="/finance/supplier_bill_list.php?sid="+ret.sid;
                $('#_j_confirm_bulk_paid').attr('disabled', false);
            },
            function(err){
                alert(err.errmsg);
                $('#_j_confirm_bulk_paid').attr('disabled', false);
            }
        );
    }
    
    function changeCity() {
        var city_wid = eval("(" + $(this).attr('data-city-wid') + ")");
        var wid_list = eval("(" + $(this).attr('data-wid-list') + ")");
        var city_id = $(this).val();

        var html = '<option value="0" selected="selected">请选择</option>';
        if (city_id == 0 || city_id == 'undefined')
        {
            $.each(city_wid, function (i) {
                var wids = city_wid[i];
                $.each(wids, function (j) {
                    html += '<option value="' + wids[j] + '" >' + wid_list[wids[j]] + '</option>';
                });
            });
        }
        else
        {
            var wids = city_wid[city_id];
            $.each(wids, function (i) {
                html += '<option value="' + wids[i] + '" >' + wid_list[wids[i]] + '</option>';
            });
        }

        $('#_j_select_stock').html(html);
    }

    function addPlanModalShow() {
        var add_plan_box = $('#modal_add_plan');
        var exec_type = $(this).attr('data-exec-type');
        add_plan_box.attr('data-exec-type', exec_type);
        add_plan_box.modal();
    }

    function changeInventoryType() {
        var plan_box = $('#modal_add_plan');
    	if ($(this).val() == 1)
        {
            $('#inventory_location_area').hide();
            $('#inventory_brand_area').hide();
            plan_box.find('input[name=start_location]').val('');
            plan_box.find('input[name=end_location]').val('');
            plan_box.find('input[name=brand_list]').val('');
        }else if ($(this).val() == 2)
        {
            $('#inventory_location_area').show();
            $('#inventory_brand_area').hide();
            plan_box.find('input[name=brand_list]').val('');
        }else if ($(this).val() == 3)
        {
            $('#inventory_location_area').hide();
            $('#inventory_brand_area').show();
            plan_box.find('input[name=start_location]').val('');
            plan_box.find('input[name=end_location]').val('');
        }
    }

    //创建、编辑盘点计划
    function savePlan() {
        var plan_box = $('#modal_add_plan');
        var para = {
            pid: plan_box.attr('data-pid'),
            wid: plan_box.find('select[name=wid]').val(),
            method: plan_box.find('select[name=method]').val(),
            plan_type: plan_box.find('select[name=plan_type]').val(),
            attribute: plan_box.find('select[name=attribute]').val(),
            times: plan_box.find('select[name=times]').val(),
            type: plan_box.find('select[name=type]').val(),
            start_location: plan_box.find('input[name=start_location]').val(),
            end_location: plan_box.find('input[name=end_location]').val(),
            is_random: plan_box.find('input[name=is_random]:checked').val(),
            random_num: plan_box.find('input[name=random_num]').val(),
            brand_list: plan_box.find('input[name=brand_list]').val(),
			exec_type: plan_box.attr('data-exec-type')
        };

        if (para.is_random == '1' && isNaN(para.random_num))
        {
            alert('抽盘总数必须是数字！');
            return false;
        }

        if (para.is_random == '1' && para.random_num == 0)
        {
            alert('抽盘总数不能为0！');
            return false;
        }

        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/save_inventory_plan.php', para, function () {
            alert('操作成功！');
            window.location.reload();
        });
    }

    function changeIsRandom() {
        var plan_box = $('#modal_add_plan');
        var is_random = plan_box.find('input[name=is_random]:checked').val();
        if (is_random == 1)
        {
            $('#random_num').show();
        }else {
            $('#random_num').hide();
            $('#random_num').val('');
        }
    }

    //编辑盘点计划
    function editInventoryPlan() {
        var plan_box = $('#modal_add_plan');
        var tgt = $(this).closest('tr');
        var plan = eval('(' + tgt.attr('data-plan') + ')');
        var pid = tgt.attr('data-pid');
        var exec_type = $(this).attr('data-exec-type');
        plan_box.attr('data-exec-type', exec_type);
        plan_box.attr('data-pid', pid);
        plan_box.find('select[name=wid]').val(plan.wid);
        plan_box.find('select[name=method]').val(plan.method);
        plan_box.find('select[name=plan_type]').val(plan.plan_type);
        plan_box.find('select[name=attribute]').val(plan.attribute);
        plan_box.find('select[name=times]').val(plan.times);
        plan_box.find('select[name=type]').val(plan.type);
        plan_box.find('.modal-title').html('编辑盘点计划');
        if (plan.type == 2){
            plan_box.find('input[name=start_location]').val(plan.start_location);
            plan_box.find('input[name=end_location]').val(plan.end_location);
            $('#inventory_location_area').show();
        }else if (plan.type == 3){
            plan_box.find('input[name=brand_list]').val(plan.brand_id);
            $('#inventory_brand_area').show();
        }
        if (plan.is_random == 1){
            plan_box.find('input[name=is_random]').get(plan.is_random).checked=true;
            plan_box.find('input[name=random_num]').val(plan.random_num);
            $('#random_num').show();
        }
        plan_box.modal();
    }

    //重置添加计划的模态框
    function resetAddPlanModal() {
        var plan_box = $('#modal_add_plan');
        plan_box.find('select[name=wid]:selected').attr('selected', false);
        plan_box.find('select[name=wid]')[0][0].selected=true;
        plan_box.find('select[name=method]').val(1);
        plan_box.find('select[name=plan_type]').val(1);
        plan_box.find('select[name=attribute]').val(1);
        plan_box.find('select[name=times]').val(1);
        plan_box.find('select[name=type]').val(1);
        plan_box.find('input[name=start_location]').val('');
        plan_box.find('input[name=end_location]').val('');
        plan_box.find('input[name=brand_list]').val('');
        plan_box.find('input[name=is_random]:checked').attr('checked', false);
        plan_box.find('input[name=is_random]').get(0).checked=true;
        plan_box.attr('data-pid', 0);
        plan_box.find('.modal-title').html('创建盘点计划');
        $('#random_num').hide();
        $('#random_num').val('');
        $('#inventory_location_area').hide();
        $('#inventory_brand_area').hide();
    }

    //删除盘点计划
    function delInventoryPlan(){
        if (!confirm('确认删除该盘点计划？'))
        {
            return false;
        }
        var para = {
            pid: $(this).closest('tr').attr('data-pid'),
            exec_type: 'del'
        };

        K.post('/warehouse/ajax/save_inventory_plan.php', para, function () {
            alert('操作成功！');
            window.location.reload();
        });
    }

    function surePlanProductsModalShow() {
        var products_box = $('#modal_sure_plan_products');
        var para = {
            pid: $(this).attr('data-pid'),
            step: $(this).attr('data-step'),
        };

        K.post('/warehouse/ajax/get_inventory_plan_products.php', para, function (ret) {
            products_box.find('.modal-body').html(ret.html);
            products_box.modal();
        }, function () {
            alert('操作失败！');
            $('#btn_sure_plan_products').attr('disabled', false);
        });
    }
    
    function createTaskModalShow() {
        var products_box = $('#modal_create_task');
        products_box.find('input[name=task_num]').val(1);
        products_box.find('input[name=product_num]').val(0);
        var para = {
            pid: $(this).attr('data-pid'),
            step: $(this).attr('data-step'),
            times: $('#inventory_times').val()
        };

        K.post('/warehouse/ajax/get_inventory_plan_products.php', para, function (ret) {
            products_box.find('#allocate_product_list').html(ret.html);
            products_box.modal();
        }, function () {
            alert('操作失败！');
            $('#modal_create_task').attr('disabled', false);
        });
    }

    function clickSurePlanProducts(evt) {
        var tgt = $(evt.target);
        if (tgt.hasClass('change_location'))
        {
            var location = tgt.attr('data-location');
            var list = $(this).find('.row-sku');
            var total = 0;
            list.each(function () {
                $(this).show();
                if (location != 0 && $(this).attr('data-location-letter') != location)
                {
                    $(this).hide();
                }
                else
                {
                    total += 1;
                }
            });
            $('#total_skus').html(total);
        }
    }

    function savePlanProduct() {
        var products_box = $('#modal_sure_plan_products');
        var list = products_box.find('.row-sku');
        var products = [];
        list.each(function () {
            var _para = {
                sid: $(this).attr('data-sid'),
                num: $(this).attr('data-num'),
                location: $(this).attr('data-location'),
            };
            products.push(_para);
        });

        var para = {
            pid: $(this).attr('data-pid'),
            wid: $(this).attr('data-wid'),
            products: JSON.stringify(products)
        };

        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/add_inventory_plan_products.php', para, function () {
            alert('操作成功！');
            window.location.reload();
        });
    }
    
    function changeAllocateMethod() {
        if ($(this).val() == 1)
        {
            var list = $('#modal_create_task').find('.row-sku');
            var total = 0;
            list.each(function () {
                $(this).show();
                total += 1;
            });
            $('#total_skus').html(total);
            $('#location_list').hide();
            $('#input_task_num').show();
            $('#input_product_num').hide();
        }
        if ($(this).val() == 2)
        {
            $('#location_list').show();
            $('#input_task_num').hide();
            $('#input_product_num').show();
        }
    }

    function clickAllocateProductList(evt) {
        var tgt = $(evt.target);
        if (tgt.hasClass('change_location'))
        {
            var location = tgt.attr('data-location');
            var list = $(this).find('.row-sku');
            var total = 0;
            list.each(function () {
                $(this).show();
                if (location != 0 && $(this).attr('data-location-letter') != location)
                {
                    $(this).hide();
                }
                else
                {
                    total += 1;
                }
            });
            $('#total_skus').html(total);
        }
    }

    function createInventoryTask() {
        var task_box = $('#modal_create_task');
        var list = task_box.find('.row-sku');
        var pid = $(this).attr('data-pid');
        var alloc_method = task_box.find('input[name=allocate_method]:checked').val();
        var products = [];
        list.each(function () {
            if ($(this).css('display') != 'none')
            {
                var _para = {
                    sid: $(this).attr('data-sid'),
                    location: $(this).attr('data-location')
                };
                products.push(_para);
            }
        });

        var para = {
            pid: pid,
            wid: $(this).attr('data-wid'),
            times: $('#inventory_times').val(),
            products: JSON.stringify(products),
            alloc_method: alloc_method
        };

        if (alloc_method == '1')
        {
            para.task_num = task_box.find('input[name=task_num]').val();
            if (para.task_num > products.length)
            {
                alert('任务数量不能大于商品数量！');
                return false;
            }
        }
        else if (alloc_method == '2')
        {
            para.product_num = task_box.find('input[name=product_num]').val();
            if (parseInt(para.product_num) <= 0)
            {
                alert('请输入商品数量!' );
                return false;
            }

            if (parseInt(para.product_num) > products.length)
            {
                alert('输入的盘点数量不能大于商品数量!' );
                return false;
            }
        }

        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/create_inventory_task.php', para, function () {
            alert('操作成功！');
            window.location.reload();
        });
    }

    function allocateInventoryTask() {
        var staff_list_box = $('#modal_warehouse_staff_list');
        var para = {
            tid: $(this).closest('tr').attr('data-tid'),
            pid: $(this).closest('tr').attr('data-pid'),
            wid: $(this).closest('tr').attr('data-wid')
        };

        K.post('/warehouse/ajax/get_inventory_staff_list.php', para, function (ret) {
            staff_list_box.find('.modal-body').html(ret.html);
            staff_list_box.modal();
        });
    }

    function clickStaffListModal(evt) {
        var tgt = $(evt.target);
        if (tgt.hasClass('alloc_inventory_task'))
        {
            var para = {
                tid: tgt.closest('tr').attr('data-tid'),
                suid: tgt.closest('tr').attr('data-suid'),
                exec_type: 'allocate'
            };

            K.post('/warehouse/ajax/save_inventory_task.php', para, function () {
                alert('操作成功！');
                window.location.reload();
            });
        }
    }

    function showInventoryDegree() {
    	var degree_box = $('#modal_show_inventory_degree');
    	var para = {
			pid: $(this).closest('tr').attr('data-pid')
		};

		K.post('/warehouse/ajax/get_inventory_plan_degree.php', para, function (ret) {
			degree_box.find('.modal-body').html(ret.html);
            degree_box.modal();
        });
    }

    function clickInventoryDegreeModal(evt){
        var degree_box = $(this);
        var tgt = $(evt.target);
        if (tgt.hasClass('change_times'))
        {
            var times = tgt.attr('data-times');
            var list = degree_box.find('.row_task');

            var task_num = 0;
            var staff_num = 0;
            var suids = [];
            list.each(function () {
                $(this).show();
                if (times == 0 || $(this).attr('data-times') == times)
                {
                    var suid = $(this).attr('data-suid');
                    if (suid != 0)
                    {
                        suids[suid] = suid;
                    }
                    task_num += 1;
                }
                else
                {
                    $(this).hide();
                }
            });
            for (var i = 0; i<=suids.length; i++)
            {
                if (suids[i])
                {
                    staff_num += 1;
                }
            }
        }
        degree_box.find('.total_task_num').html(task_num);
        degree_box.find('.staff_num').html(staff_num);
    }

    function clickDiffProductAreaList(evt) {
        var tgt = $(evt.target);
        if (tgt.hasClass('change_area'))
        {
            var area = tgt.attr('data-area');
            var list = $('#diff_product_area').find('.row-sku');
            list.each(function () {
                $(this).show();
                var row_location = $(this).attr('data-location-letter');
                if (area != 0 && area != row_location)
                {
                    $(this).hide();
                }
            });
        }
    }

    function dealDiffNUm() {
        var diff_box = $('#modal_deal_diff');
        diff_box.find('input[name=num]').val('');
        diff_box.find('textarea[name=diff_note]').val('');
        diff_box.attr('data-pid', '');
        diff_box.attr('data-sid', '');
        diff_box.attr('data-location', '');
        diff_box.attr('data-last-num', '');

        var pid = $(this).closest('tr').attr('data-pid');
        var sid = $(this).closest('tr').attr('data-sid');
        var location = $(this).closest('tr').attr('data-location');
        var last_num = $(this).closest('tr').attr('data-last-num');
        diff_box.attr('data-pid', pid);
        diff_box.attr('data-sid', sid);
        diff_box.attr('data-location', location);
        diff_box.find('input[name=num]').val(last_num);
        diff_box.modal();
    }

    function updateDiffNum() {
        if (!confirm('确定把最终盘点数量更新到库存？'))
        {
            return;
        }
        var diff_box = $('#modal_deal_diff');
        var pid = diff_box.attr('data-pid');
        var sid = diff_box.attr('data-sid');
        var location = diff_box.attr('data-location');
        var num = diff_box.find('input[name=num]').val();
        var note = diff_box.find('textarea[name=diff_note]').val();
        if (num == '' || num == 'undefined' || isNaN(num))
        {
            alert('请输入更新的数量！');
            return;
        }
        if (note == '' || note == 'undefined')
        {
            alert('请输入备注！');
            return;
        }

        var para = {
            pid: pid,
            sid: sid,
            location: location,
            num: num,
            note: note
        };

        $(this).css('display', true);
        K.post('/warehouse/ajax/update_diff_num.php', para, function () {
            alert('操作成功！');
            window.location.reload();
        });
    }

    function sureInventoryPlan() {
        var pid = $(this).attr('data-pid');
        var para = {
            pid: pid
        };

        $(this).css('display', true);
        K.post('/warehouse/ajax/save_inventory_location_stock.php', para, function () {
            alert('操作成功！');
            window.location.reload();
        });
    }

    function changePlanTimes()
    {
        var pid = $(this).closest('form').attr('data-pid');
        var wid = $(this).closest('form').attr('data-wid');
        var times = $(this).val();
        window.location.href = '/warehouse/plan_detail.php?pid='+pid+'&wid='+wid+'&times='+times;
    }

    function changeOtherStockOutOrderType() {
        var type = $(this).val();
        var order_type = $(this).attr('data-order-type');
        var list = $('#other_stock_out_order_reason').find('option');
        if (order_type == '1') {
            if (type == '1') {
                $('#other_stock_out_supplier_area').hide();
            }
            else if (type == '2') {
                $('#other_stock_out_supplier_area').find('input[name=sid]').val('');
                $('#other_stock_out_supplier_area').show();
            }
            else if (type == '3') {
                $('#other_stock_out_supplier_area').find('input[name=sid]').val('');
                $('#other_stock_out_supplier_area').show();
            }
			else if (type == '4') {
				$('#other_stock_out_supplier_area').find('input[name=sid]').val('');
				$('#other_stock_out_supplier_area').show();
			}
        }
        else if (order_type == '2')
        {
            if (type == '1') {
                $('#other_stock_out_supplier_area').find('input[name=sid]').val('');
                $('#other_stock_out_supplier_area').show();
            }
        }


        var num = 0;
        var select_num = 0;
        list.each(function (i) {
            $(this).hide();
            if ($(this).attr('selected') == true) {
                $(this).attr('selected', false);
            }
            if ($(this).attr('data-type') == type) {
                $(this).show();
                if (num != 1) {
                    num++;
                    select_num = i;
                }
            }
        });

        $('#other_stock_out_order_reason').find('option').get(select_num).selected = true;
    }

    function saveOtherStockOutOrder() {
        var form = $('#_j_stock_shift_form');
        var para = {
			order_type: $(this).attr('data-order-type'),
            wid: form.find('select[name=out_wid]').val(),
            type: form.find('select[name=type]').val(),
            reason: form.find('select[name=reason]').val(),
            note: form.find('textarea').val()
        };
        var oid = $(this).attr('data-oid');
        if (para.note == '' || para.note == 'undefined')
        {
            alert('请填写备注！');
            return;
        }

        if (para.wid == '' || para.wid == 'undefined' || para.wid == '0')
        {
            alert('请选择仓库！');
            return;
        }

        if (oid > 0)
        {
            para.oid = oid;
        }

        if ((para.order_type == 1 && (para.type == '2' || para.type == '3' || para.type == '4'))||(para.order_type == 2 && para.type == 1))
        {
            para.supplier_id = form.find('input[name=sid]').val();
        }

        var products = [];
        var list = $('#other_stock_out_product_area').find('input[name=loc_num]');
        var flag = false;
        list.each(function () {
            var sid = $(this).attr('data-sid');
            var loc = $(this).attr('data-loc');
            var num = $(this).val();
            var loc_num = $(this).attr('data-loc-num');
			var note = $(this).parent().parent().next().find('input[name=note]').val();

            if (num == '')
            {
                num = 0;
            }
            if (loc_num == '')
            {
                loc_num = 0;
            }

            if (parseInt(loc_num) < parseInt(num))
            {
                flag = true;
            }

            products.push(sid + ':' + loc + ':' + num + ':' + note);
        });

        if (flag)
        {
            alert('货位商品数量不能大于该货位库存！');
            return;
        }

        para.products = products;

        $(this).attr('disabled', true);
        var href;
        if (para.order_type == 1)
        {
            href = 'other_stock_out_order';
        }
        else if (para.order_type == 2)
        {
            href = 'other_stock_in_order';
        }

        K.post('/warehouse/ajax/save_other_stock_out_order.php', para, function (ret) {
            alert('保存成功！');
            if (oid != '' && oid != 'undefined')
            {
                window.location.href='/warehouse/' + href + '.php';
            }
            else
            {
                window.location.href='/warehouse/add_' + href + '.php?oid=' + ret.oid;
            }
        }, function (ret) {
            alert(ret.errmsg);
            $(this).attr('disabled', false);
        });
    }

    function auditOtherStockOutOrder() {
        var para = {
            oid: $(this).attr('data-oid'),
            exec_type: $(this).attr('data-exec-type'),
            order_type: $(this).attr('data-order-type')
        };

        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/change_other_stock_out_order.php', para, function (ret) {
            alert(ret.msg);
            window.location.reload();
        });
    }

    function delOtherStockOutProduct(evt) {
        if ($(evt.target).hasClass('_j_del_other_stock_out_product')) {
            var para = {
                oid: $(evt.target).attr('data-oid'),
                sid: $(evt.target).attr('data-sid')
            };

            K.post('/warehouse/ajax/del_other_stock_out_product.php', para, function () {
                alert('删除成功！');
                window.location.reload();
            });
        }
    }

    function showSupplierListModal() {
        var sid = $(this).attr('data-sid');
        var para = {
          sid: sid
        };

        K.post('/warehouse/ajax/get_sku_supplier_list.php', para, function (ret) {
            $('#supplier_list_modal').find('.modal-body').html(ret.html);
            $('#supplier_list_modal').modal();
        });
	}

	function showEditRefundPrice() {
        var edit_box = $('#edit_refund_product_price');
        var sid = $(this).closest('tr').attr('data-pid');
        var price = $(this).closest('tr').find('input[name=price]').val();

        edit_box.find('input[name=sid]').val(sid);
        edit_box.find('input[name=price]').val(price);
        edit_box.modal();
	}

	function updateRefundProductPrice() {
        var edit_box = $('#edit_refund_product_price');
        var srid = edit_box.attr('data-srid');
        var sid = edit_box.find('input[name=sid]').val();
        var price = edit_box.find('input[name=price]').val();

        var para = {
            srid: srid,
            sid: sid,
            price: price
        };

        K.post('/warehouse/ajax/update_refund_product_price.php', para, function () {
            alert('操作成功！');
            window.location.reload();
        });
    }

	function refreshStockShiftVnum() {
		var para = {
			ssid: $(this).attr('data-ssid'),
			sid: $(this).attr('data-sid')
		};

		$(this).attr('disabled', true);
		K.post('/warehouse/ajax/refresh_vnum_force.php', para, function (ret) {
			alert(ret.errmsg);
			window.location.reload();
		});
	}

	function editSkuCost()
	{
		var sid = $(this).data('sid');
		var wid = $(this).data('wid');
		var originalCost = $(this).data('price');
		var supplierId = $(this).data('supplier_id');
		$("#edit_cost input[name=stock_id]").val(sid + '_' + wid);
		$("#edit_cost input[name=original_cost]").val(originalCost);
		$("#edit_cost input[name=supplier_id]").val(supplierId);
        $("#edit_cost input[name=stock_id]").prev().html("(原成本￥"+ originalCost/100 + ")");
		$('#edit_cost').modal();
	}

	function saveStockCostPrice() {
		var para = {
			sidWid: $("#edit_cost input[name=stock_id]").val(),
            price: $("#edit_cost input[name=stock_cost]").val(),
            originalPrice: $("#edit_cost input[name=original_cost]").val(),
            supplierId:$("#edit_cost input[name=supplier_id]").val()
		};

        K.post('/warehouse/ajax/save_stock_cost.php', para, function (ret) {
            alert(ret.msg);
            window.location.reload();
        });
    }
    
    function showEditFringCost(evt){

		var clickDom = evt.relatedTarget;
		var data_price = $(clickDom).attr('data-price');
        
		$('.old_fring_cost').html('原附加成本:'+data_price/100+'元');
        $('#saveFringCost').attr('data-wid', $(clickDom).attr('data-wid'));
        $('#saveFringCost').attr('data-sid', $(clickDom).attr('data-sid'));

    }

    function saveFringCost(){

		var newFringCost = $("input[name=fring_cost]").val();
		var param = {
			newFringCost : newFringCost,
			wid : $(this).attr('data-wid'),
			sid : $(this).attr('data-sid')
		};

		if(newFringCost == ''){
			alert('请填写附加成本'); return false;
		}

		K.post('/warehouse/ajax/save_fring_cost.php', param, function(ret) {
            alert('修改成功');
			window.location.reload();
		});
    }
    
    function calRemainderMoney() {
    	//使用余额
		var inUseAmount = parseFloat($('input[name=in_use_amount]').val());
		//当前总余额
		var curAmount = parseFloat($('input[name=supplier_total_amount]').val());
		if (inUseAmount < 0)
		{
			alert('请输入大于等于0的数！');
			$('#_j_pay_stockin_statement').attr('disabled', true);
			return false;
		}
		var payable = 0;
        var total = 0;
        $('#stock_in_list input[name=will_pay]').each(function () {
            var price = Math.round($(this).val()*100);
            total += price;
        });
        payable = parseFloat((total/100));

        if (curAmount > payable)
		{
            if (inUseAmount >= curAmount || (inUseAmount < curAmount && inUseAmount >= payable))
            {
                $('input[name=in_use_amount]').val(payable);
            }
		}

		if (curAmount <= payable)
		{
            if (inUseAmount > curAmount)
            {
                $('input[name=in_use_amount]').val(curAmount);
            }
		}

        //使用余额
        var _inUseAmount = parseFloat($('input[name=in_use_amount]').val());
        if (isNaN(_inUseAmount))
		{
			_inUseAmount = 0;
		}
		//剩余应付
		var remainderMoney = payable - _inUseAmount;

        if (remainderMoney >= 0)
		{
            $('.remainder_money').text('剩余应付：￥' + remainderMoney.toFixed(2));
            $('#_j_pay_stockin_statement').attr('disabled', false);
		} else {
            $('.remainder_money').text('输入错误！');
            $('#_j_pay_stockin_statement').attr('disabled', true);
        }

    }
    
    function deleteStatementOrd() {
		var para = {
			id: $(this).attr('data-id')
		};

		if(!confirm('您确定删除该结算单吗？'))
		{
			return false;
		}

		K.post('/finance/ajax/delete_statement_order.php', para, function (ret) {
			alert(ret.msg);
			window.location.reload();
        });
    }

    function statementProductDetail()
	{
		var para = {
			id: $(this).data('id'),
		};

		if (para.id.length == 0)
		{
			alert('参数错误！');
			return false;
		}
		K.post('/finance/ajax/get_statement_product_detail.php', para, function (ret) {
			$('#statements_product_detail .modal-body').html(ret.html);
            $('#statements_product_detail').modal();
        });
	}

	main();

})();