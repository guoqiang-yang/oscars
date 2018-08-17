(function () {

	function main() {
		$('#_j_btn_save_order_step1').click(_onSaveOrderStep1);
		$('#_j_btn_save_order_edit').click(_onSaveOrderEdit);
		
        // 选商品的弹框，注册时间
        $('._j_select_product').click(showSelectProducts);
        $('#dlgAddProduct').on('click', clickOnSelectProductBox);
        
        $('._j_order_search_product').click(_onOrderSearchProduct);
		$('._j_order_search_product').closest('._j_form').find('input[name=keyword]').keydown(_onOrderSearchProductKeydown);
        
        
        
		$('#_j_btn_save_products').click(_onSaveProducts);
		$('._j_del_order_product').click(_onDeleteOrderProduct);
        $('._j_show_del_order_tmp_product').click(_onShowDeleteOrderTmpProduct);
        $('#_j_del_order_tmp_product').click(_onDeleteOrderTmpProduct);
		$('._j_in_order_del').click(deleteInOrder);
        
        $('.quick_show_inorder_products').click(quickShowInorderProducts);
        
        // 新库流程相关
        $('#_j_add_warehouse_location').click(addWarehouseLocation);    // 添加货位
        $('#_j_confirm_obj_shelved').click(objShelved);     //上架
        $('#_j_confirm_other_stock_in_product_shelved').click(otherStockInProductShelved);     //上架
        $('._j_check_location_stock').on('show.bs.modal', showCheckLocationStock); //货位盘库
        $('._j_save_chk_location_stock').on('click', saveCheckLocationStock);
        $('#_j_shift_location_stock').on('show.bs.modal', showShiftLocationStock); //货架商品移动
        $('#_j_save_shift_location_stock').on('click', saveShiftLocationStock);
        $('.del_sku_location').on('click', delSkuLocation);
        $('.search_un_shelved_bills').on('click', searchUnshelvedBills);    //未上架的单据清单

        $('.complete_receive').on('click', changeInOrderStatus);

        //删除入库退货单
        $('.del_stockin_refund').click(delStockinRefund);

        //采购单列表
        $('#in_order_status_tabs').on('click', clickInOrderStatusTabs);
        $('#change_shift_type').on('change', changeShiftType);
        
        // 调拨单
        $('#_j_stock_shift_abnormal').on('show.bs.modal', showStockShiftAbnormal);  //调拨单，差异处理
        $('#_j_stock_shift_abnormal_submit').on('click', saveStockShiftAbnormal);   //调拨单，差异保存
        
        //查看商品占用
        $('.get_occupied_products').on('click', showOccupiedByOrder);
        $('.refresh_occupied_products').on('click', refreshOccupiedProducts);

        //标记普采缺货状态
        $('._j_mark_stockout').on('click', markStockOut);
    }
    
    // 选择商品
    function showSelectProducts(ev){
        ev.preventDefault();
		
        var oid = $('#dlgAddProduct').data('oid'),
			para = {href : $(ev.target).attr('href'), oid : oid};

		_getProductListHtml(para);
    }
    
    //订单编辑 - 搜索框
	function _onOrderSearchProductKeydown(ev) {
		if(event.keyCode==13){
			_onOrderSearchProduct(ev);
		}
	}
	function _onOrderSearchProduct(ev) {
		ev.preventDefault();
		var tgt = $(ev.currentTarget),
			oid = $('#dlgAddProduct').data('oid'),
			keyword = tgt.closest('._j_form').find('input[name=keyword]').val(),
			para = {keyword : keyword, oid : oid}

        _getProductListHtml(para);
	}
    
    function clickOnSelectProductBox(evt){
        var target = $(evt.target);
        
        if (target.is('._j_order_select_product')){
            showSelectProducts(evt);
        } 
    }
    
    function _getProductListHtml(para){
        K.post('/warehouse/ajax/dlg_get_products.php', para, function(ret){
            $('#select_product_area').html(ret.html);
            $('#dlgAddProduct').modal();
        });
    }

	// 删除订单商品
	function _onDeleteOrderProduct(ev) {
		var tgt = $(ev.currentTarget),
			oid = tgt.closest('form').find('input[name=oid]').val(),
            wid = tgt.attr('data-wid'),
			sid = tgt.closest('tr').data('sid'),
            source = tgt.attr('data-source'),
			para = {oid:oid, wid:wid, sid:sid, source:source};

		if (confirm('确认删除该商品？')) {
			K.post('/warehouse/ajax/delete_product.php', para, _onDeleteProductSuccess);
		}
	}
	function _onDeleteProductSuccess(data) {
		window.location.reload();
	}
    
    function _onShowDeleteOrderTmpProduct(){
        var para = {
            oid: $(this).closest('form').find('input[name=oid]').val(),
            sid: $(this).closest('._j_product').attr('data-sid'),
            source: $(this).attr('data-source'),
            otype: 'show_tmp'
        };
        
        K.post('/warehouse/ajax/delete_product.php', para,
            function(ret){
                $('#showDelTmpProduct').find('.modal-body').html(ret.html);
                $('#showDelTmpProduct').modal();
            },
            function(err){
                alert(err.errmsg);
                return false;
            }
        );
    }
    
    function _onDeleteOrderTmpProduct(){
        var obj = $('#showDelTmpProduct').find('._j_product');
        var para = {
            otype: 'del_tmp_product',
            source: 2,
            sid: obj.attr('data-sid'),
            oid: obj.attr('data-oid'),
            chg_type: obj.find('select[name=chg_type]').val(),
            del_num: $('#showDelTmpProduct').find('._j_product').find('select[name=del_num]').val()
        };
        
        if (para.chg_type=='0'||para.chg_type.length==0){
            alert('请选择【操作类型】'); return false;
        }
        if (para.del_num=='0'||para.del_num.length==0){
            alert('请选择【删除数量】'); return false;
        }
        
        para.sales_oid = $('#showDelTmpProduct').find('._j_product').find('select[name=del_num]').find("option:selected").attr('data-oid');
        
        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/delete_product.php', para,
            function(){
                alert(para.chg_type==1?'删除':'转普采'+'成功！');
                window.location.reload();
            },
            function(err){
                alert(err.errmsg);
                $('#_j_del_order_tmp_product').attr('disabled', false);
                return false;
            }
        );
    }

	// 保存商品
	function _onSaveProducts(ev) {
		var tgt = $(ev.currentTarget),
			oid = tgt.data('oid'),
            source = tgt.attr('data-source'),
			para = {oid:oid, source:source},
			products = [];

		$('._j_product_item').each(function(){
			var cb = $(this),
				sid = cb.data('sid'),
				num = parseInt(cb.find('input[name=num]').val()),
				price = parseFloat(cb.find('input[name=price]').val());
			if (K.isNumber(num)) {
				products.push(sid + ':' + num + ':' + price);
			}
		});

		if (products.length == 0) {
			alert('请先选择商品');
			return;
		}

		para.product_str = products.join(',');
		K.post('/warehouse/ajax/add_products.php', para, _onSaveProductsSuccess);
	}
	function _onSaveProductsSuccess(data) {
		window.location.reload();
	}


	// 保存订单
	function _getOrderFormInfo() {
		var para = {
			oid: $('input[name=oid]').val(),
			sid: $('input[name=sid]').val(),
			contact_name: $('input[name=contact_name]').val(),
			contact_phone: $('input[name=contact_phone]').val(),
			freight: $('select[name=freight]').val(),
			privilege: $('input[name=privilege]').val(),
			privilege_note: $('input[name=privilege_note]').val(),
			delivery_date: $('input[name=delivery_date]').val(),
			payment_days_date: $('input[name=payment_days_date]').val(),
			order_step: $('select[name=order_step]').val(),
			payment_type: $('input[name=payment_type]:checked').val(),
			note : $('textarea[name=note]').val(),
			wid: $('select[name=wid]').val(),
            in_order_type : $('select[name=in_order_type]').val()
		};
		return para;
	}
	function _onSaveOrderStep1() {

		if ( $('#_j_btn_save_order_step1').attr('disabled') ) return;
		$('#_j_btn_save_order_step1').attr('disabled', true);

		var para = _getOrderFormInfo();
		if (!para.payment_type) {
			$('#_j_btn_save_order_step1').attr('disabled', false);
			alert('请先选择付款方式');
			return;
		} else if (para.wid == '0'){
			$('#_j_btn_save_order_step1').attr('disabled', false);
			alert('请选择仓库');
			return;
		}

		K.post('/warehouse/ajax/save_order.php', para, 
            function(data){
                window.location.href = "/warehouse/add_in_order.php?step=2&oid=" + data.oid;
            },
            function(err){
                $('#_j_btn_save_order_step1').attr('disabled', false);
                alert( err.errmsg );
            }
        );
	}

	// 保存订单
	function _onSaveOrderEdit(ev) {
		var para = _getOrderFormInfo();
		if (!para.payment_type) {
			alert('请先选择付款方式');
			return;
		}
        
		para.step = $('._j_in_order_form').find('input[name=step]').val();

		K.post('/warehouse/ajax/save_order.php', para, _onSaveOrderEditSuccess);
	}
	
	//采购单列表的订单删除
	function deleteInOrder(){
		var oid = $(this).attr('data-oid');
		
		if (confirm('确认删除该采购单？')) {
			K.post('/warehouse/ajax/delete_order.php', {oid:oid}, _onSaveOrderEditSuccess);
		}
	}
	
	function _onSaveOrderEditSuccess(data) {
		alert('保存成功');
		window.location.reload();
	}
    
    // 快速显示采购单商品
    function quickShowInorderProducts(){
        var htmlPos = $(this).closest('tr');
        var oid = htmlPos.attr('data-oid');
        var wid = htmlPos.attr('data-wid');
        var qshowObj = $('#quickShowInorder_'+ oid);
        
        if (qshowObj.length > 0) {
            if (qshowObj.css('display')=='none'){ //存在，并隐藏
                qshowObj.show();
                $(this).html('收起');
            } else {
                qshowObj.hide();
                $(this).html('快速查看');
            }
        }
        else{
            K.post('/warehouse/ajax/quick_show_inorder_products.php', {oid:oid, wid:wid}, function(ret){
                htmlPos.after(ret.html);
                htmlPos.find('.quick_show_inorder_products').html('收起');
            });
        }
        
    }
    

    //////////////////// 新库相关流程 /////////////////////
    
    function addWarehouseLocation(){
        var dlg = $('#addWLocation');
        var para = {
            area: dlg.find('input[name=area]').val(),
            shelf: dlg.find('input[name=shelf]').val(),
            layer: dlg.find('input[name=layer]').val(),
            pos: dlg.find('input[name=pos]').val(),
            wid: dlg.find('select[name=wid]').val(),
            sid: dlg.find('input[name=sid]').val()
        };
        
        // 只简单检测合法性，严格的检测放到服务端处理
        if (para.area.length!=1 || para.shelf.length==0 ||para.wid=='0')
        {
            alert('参数错误，请重新填写！'); return false;
        }
        if (para.sid.length==0)
        {
            alert('请填写skuid，数量，且数量不能为0'); return false;
        }
        
        if (confirm('请确认要在【'+para.wid+'】添加货位？'))
        {
            $(this).attr('disabled', true);
            K.post('/warehouse/ajax/add_location.php', para, function(ret){
                $('#_j_add_warehouse_location').attr('disabled', false);
                if (ret.errno !=0){
                    alert(ret.errmsg);
                } else {
                    alert('添加成功');
                    window.location.reload();
                }
            });
        }
    }
    
    // 上架
    function objShelved(){
        var para = {
            oid: $(this).attr('data-oid'),
            wid: $(this).attr('data-wid'),
            objid: $(this).attr('data-objid'),
            type: $(this).attr('data-type')
        };
        
        var products = [];
        var chkFlag = true;
        var complateFlag = true;
        $('._j_product').each(function(){
            var _para;
            _para = {
                sid: $(this).attr('data-sid'),
                loc: $(this).find('input[name=location]').val()
            };
            
            if(chkFlag == false){
                return;
            }
            
            if ($(this).find('input[name=wait_shelved]').is(':checked')){
                if(_para.loc.length==0){
                    chkFlag = false;
                } else {
                    products.push(_para);
                }
            } else if($(this).find('input[name=wait_shelved]').length>0){
                complateFlag = false;
            }
        });
        
        if (!chkFlag){
            alert('上架货位不能为空！！');
            return false;
        }
        if (products.length == 0){
            alert('无上架商品！'); return false;
        }
        para.products = JSON.stringify(products);
        
        if (confirm('确认上架？'+ (!complateFlag? '【部分上架！！！！！！！】':''))){
            $(this).attr('disabled', true);
            K.post('/warehouse/ajax/save_shelved.php', para, function(ret){
                $('#_j_confirm_obj_shelved').attr('disabled', false);
                
                if (ret.errno == 0){
                    alert('上架完成！');
                    window.location.reload();
                } else {
                    alert(ret.errmsg);
					$(this).attr('disabled', false);
                    return false;
                }
               
            });
        }
    }

    // 盘库
    function showCheckLocationStock(evt){
        var clickDom = evt.relatedTarget;
        var obj = $(clickDom).closest('.dialog');
        
        var loc = obj.find('.loc').attr('data-loc');
        var locNum = loc +' : '+ $(clickDom).attr('data-num');
        $(this).find('.chk_sku_title').html(obj.find('.title').html());
        $(this).find('.show_curr_num').html(locNum);
        $(this).find('.show_curr_num').attr('data-old-num', $(clickDom).attr('data-num'));
        $(this).find('._j_save_chk_location_stock').attr('data-sid', $(clickDom).attr('data-sid'));
        $(this).find('._j_save_chk_location_stock').attr('data-loc', loc);
    }
    
    function saveCheckLocationStock(){
        var dlg = $(this).closest('._j_check_location_stock');
        var para = {
            sid: $(this).attr('data-sid'),
            loc: $(this).attr('data-loc'),
            //type: dlg.find('input[name=type]:checked').val(),
            num: dlg.find('input[name=num]').val(),
            wid: dlg.find('input[name=wid]').attr('data-wid'),
            reason: dlg.find('select[name=reason]').val(),
            note: dlg.find('textarea[name=note]').val(),
            inventory_type: $(this).attr('data-inventory-type'),
            old_num: dlg.find('.show_curr_num').attr('data-old-num')
        };

        if (para.inventory_type == 'profit' && parseInt(para.num) <= parseInt(para.old_num)) {
            alert('盘盈填写的数量不能小于或等于原货位数量！');return;
        }else if (para.inventory_type == 'loss' && parseInt(para.num) >= parseInt(para.old_num)) {
            alert('盘亏填写的数量不能大于或等于原货位数量！');return;
        }


        if (para.reason.length==0){
            alert('请选择：盈亏原因！'); return;
        }
        if (para.note.length==0){
            alert('请输入备注！'); return;
        }
        if (para.note.length>100){
            alert('备注信息，最多100个字！'); return;
        }
        
        if (para.num.length==0){
            alert('请填写数量！'); return;
        }
        
        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/save_chk_location_stock.php', para, function(){
            alert('操作已成功！');
            window.location.reload();
        });
    }
    
    // 货架货物移动
    function showShiftLocationStock(evt){
        var clickDom = evt.relatedTarget;
        var obj = $(clickDom).closest('.dialog');
        
        var sid = obj.find('.sid').attr('data-sid');
        var loc = obj.find('.loc').attr('data-loc');
        var num = obj.find('.num').attr('data-num');
        var occupied = obj.find('.num').attr('data-occupied');
        $(this).find('.chk_sku_title').html(obj.find('.title').html());
        $(this).find('input[name=src_loc]').val(loc);
        $(this).find('#show_loc_stock').html('库存:'+num+'  占用:'+occupied);
        $(this).find('#_j_save_shift_location_stock').attr('data-sid', sid);
        $(this).find('#_j_save_shift_location_stock').attr('data-loc', loc);
        $(this).find('#_j_save_shift_location_stock').attr('data-freenum', num-occupied);
    }

    function saveShiftLocationStock(){
        var dlg = $('#_j_shift_location_stock');
        var para = {
            sid: $(this).attr('data-sid'),
            src_loc: dlg.find('input[name=src_loc]').val(),
            des_loc: dlg.find('input[name=des_loc]').val(),
            num: dlg.find('input[name=num]').val(),
            wid: dlg.find('input[name=wid]').attr('data-wid'),
            note: dlg.find('textarea[name=note]').val()
        };
        
        if(para.des_loc.length == 0){
            alert('目标货架不能为空！'); return false;
        }
        if (para.num.length==0 || para.num=='0'){
            alert('请输入移架数量！'); return false;
        }
        if (para.note.length>30){
            alert('备注字数过多！');
        }
        
        var freeNum = $(this).attr('data-freenum')*1;
        if (para.num*1 > freeNum){
            alert('货位数量不足，请核对！'); return false;
        }
        
        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/save_shift_location_stock.php', para, 
            function(){
                alert('更新成功！');
                window.location.reload();
            },
            function(ret){
                alert(ret.errmsg);
                // $('#_j_save_shift_location_stock').attr('disabled', false);
                window.location.reload();
            }
        );
    }
    
    function delSkuLocation(){
        var params = {
            id: $(this).attr('data-id'),
            sid: $(this).attr('data-sid'),
            loc: $(this).attr('data-loc'),
            num: $(this).attr('data-num')
        };
        
        if (!confirm('确定要删除该货位！！'))
        {
            return false;
        }
        
        if (params.num != '0')
        {
            alert('删除货位有库存，不能删除！');
            return false;
        }
        
        K.post('/warehouse/ajax/del_sku_location.php', params, 
            function(){
                alert('删除成功！');
                window.location.reload();
            },
            function(err){
                alert(err.errmsg);
            }
        );
    }

    function searchUnshelvedBills() {
        var dlg = $(this).closest('.dialog');
        var para = {
            sid: dlg.find('.sid').attr('data-sid'),
            title: dlg.find('.title').find('span').html(),
            wid: $(this).attr('data-wid'),
            vloc: $(this).attr('data-vloc')
        };

        K.post('/warehouse/ajax/search_unshelved_bills.php', para, function (ret) {
            $('#show_un_shelved_bills').find('.modal-body').html(ret.html);
            $('#show_un_shelved_bills').modal();
        });
    }

    function changeInOrderStatus()
    {
        var oid = $(this).attr('data-oid');
        var next_step = $(this).attr('data-next-step');
        var para = {
            oid: oid,
            wid: $(this).attr('data-wid'),
            next_step: next_step
        };
        
        K.post('/warehouse/ajax/complete_receiver.php', para, function () {
            alert('操作成功');
            window.location.reload();
        });
    }

    function delStockinRefund()
    {
        var para = {
            srid: $(this).attr('data-srid')
        };
        K.post('/warehouse/ajax/del_stockin_refund.php', para, function () {
            alert('删除成功!');
            window.location.reload();
        })
    }

    function otherStockInProductShelved() {
        var products = [];
        var chkFlag = true;
        var complateFlag = true;
        $('._j_product').each(function(){
            var _para;
            _para = {
                sid: $(this).attr('data-sid'),
                loc: $(this).find('input[name=location]').val(),
                num: $(this).find('input[name=num]').val()
            };

            if(chkFlag == false){
                return;
            }

            if ($(this).find('input[name=wait_shelved]').is(':checked')){
                if(_para.loc.length==0){
                    chkFlag = false;
                } else {
                    if (_para.num > 0)
                    {
                        products.push(_para);
                    }
                }
            } else if($(this).find('input[name=wait_shelved]').length>0){
                complateFlag = false;
            }
        });

        if (!chkFlag){
            alert('上架货位不能为空！！');
            return false;
        }
        if (products.length == 0){
            alert('无上架商品！'); return false;
        }

        var para = {
            oid: $(this).attr('data-oid'),
            products: JSON.stringify(products)
        };

        if (confirm('确认上架？'+ (!complateFlag? '【部分上架！！！！！！！】':''))){
            $(this).attr('disabled', true);
            K.post('/warehouse/ajax/save_other_stock_in_product_shelved.php', para, function(ret){
                $('#_j_confirm_other_stock_in_product_shelved').attr('disabled', false);

                if (ret.st == 1){
                    alert('上架完成！');
                    window.location.reload();
                } else {
                    alert(ret.errmsg);
                    $(this).attr('disabled', false);
                    return false;
                }

            }, function (ret) {
                alert(ret.errmsg);
                $(this).attr('disabled', false);
                return false;
            });
        }
    }
    
    function clickInOrderStatusTabs(evt) {
        var step = $(evt.target).closest('li').attr('data-step');
        var status = $(evt.target).closest('li').attr('data-status');
        $('form').find('input[name=step]').val(step);
        $('form').find('input[name=status]').val(status);
        $('form').submit();
    }
    
    function changeShiftType() {
        var box = $('#_j_shift_location_stock');
        var type = $(this).val();
        var loc = $(this).find('option[value=' + type + ']').attr('data-loc');
        var clock = $(this).find('option[value=' + type + ']').attr('data-clock');
        clock = clock? true:false;
        box.find('input[name=des_loc]').val(loc);
        box.find('input[name=des_loc]').attr('disabled', clock);
    }

    // 调拨单 - 异常商品处理
    function showStockShiftAbnormal(evt){
        var clickDom = evt.relatedTarget;
        var obj = $(clickDom).closest('._j_product');
        
        var sid = obj.attr('data-sid');
        var ssid = $('#stockShiftId').val();
        var freeNum = obj.find('.deal_abnormal_num').attr('data-diffnum');
        
        $(this).find('.chk_sku_title').html(obj.find('.title').html());
        $(this).find('#can_deal_num').html(freeNum);
        $(this).find('#_j_stock_shift_abnormal_submit').attr('data-ssid', ssid);
        $(this).find('#_j_stock_shift_abnormal_submit').attr('data-sid', sid);
        $(this).find('#_j_stock_shift_abnormal_submit').attr('data-freenum', freeNum);
        
    }
    
    function saveStockShiftAbnormal(){
        var dlg = $(this).parents('#_j_stock_shift_abnormal');
        var para = {
            ssid: $(this).attr('data-ssid'),
            sid: $(this).attr('data-sid'),
            type: dlg.find('select[name=shift_type]').val(),
            num: dlg.find('input[name=num]').val()*1,
            note: dlg.find('textarea[name=note]').val()
        };
        
        var freeNum = dlg.find('#can_deal_num').html()*1;
        
        if (para.num == 0 || para.type=='0')
        {
            alert('请选择处理类型，并填写数量！'); return;
        }
        if (para.num > freeNum){
            alert('数量异常：不能大于可处理数量！'); return;
        }
        if (para.note == ''){
            alert('请备注原因！'); return;
        }
        
        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/deal_stock_shift_abnormal.php', para,
            function(){
                alert('处理成功');
                window.location.reload();
            },
            function(err){
                alert(err.errmsg);
                $('#_j_stock_shift_abnormal_submit').attr('disabled', false);
            }
        );
    }
    
    function showOccupiedByOrder() {
        var dom = $(this).closest('.dialog');
        var para = {
            sid: dom.find('.sid').attr('data-sid'),
            wid: $(this).attr('data-wid'),
            title: dom.find('.title').children().html()
        };

        K.post('/order/ajax/get_occupied_product_by_order.php', para, function (ret) {

            $('#show_occupied_products').find('.modal-body').html(ret.html);
            $('#show_occupied_products').modal();
        });

    }
    
    function refreshOccupiedProducts()
    {
        var dom = $(this).closest('.dialog');
        var para = {
            sid: dom.find('.sid').attr('data-sid'),
            wid: $(this).attr('data-wid')
        };

        K.post('/warehouse/ajax/refresh_occupied.php', para, function (ret) {
            alert(ret.errmsg);
            window.location.reload();
        });
    }

    function markStockOut()
    {
        var para = {
            oid: $(this).data('oid'),
            sid: $(this).data('sid'),
            type: $(this).data('type'),
        };
        console.log(para.type);

        if (para.type == 1)
        {
            if (!confirm('确定要标记为【外采】！')) {
                return false;
            }

            K.post('/warehouse/ajax/mark_stockout.php', para, function (suc) {
                alert(suc.msg);
                window.location.reload();
            });

        }
        else if (para.type == 2)
        {
            if (!confirm('确定要标记为【晚到】！')) {
                return false;
            }

            K.post('/warehouse/ajax/mark_stockout.php', para,function (suc) {
                alert(suc.msg);
                window.location.reload();
            });
        }
    }

	main();

})();