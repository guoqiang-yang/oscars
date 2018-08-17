(function () {

	function main() {
        $('#_j_show_supplier_list').click(showSupplierList);
        $('#dlgSupplierList').click(addSupplierEvent);
		$('._j_select_supplier').click(onSelectSupplier);
		//$('#_j_btn_save_order_quick').click(onSaveOrderQuick);
		$('._j_order_search_supplier').closest('._j_form').keydown(_onSeachSupplierKeydown);
       
        $('#_j_supplier_history_products').click(getSupplierHistoryProducts);
        $('._j_save_history_inorder_products').click(saveHistoryInorderProduct);
        $('.modify_inorder_product').click(modifyInorderProduct);
        $('#_j_modify_inorder_submit').click(modifyInorderSubmit);
        $('#_j_btn_confirm_pre_pay').click(confirmPrePay);
        
        // 临采新新逻辑 2016-09-09 
        $('#tmp_purchase_select_all').on('click', tmpPurchaseSelectAll);
        $('#show_create_tmp_purchase').on('click', showCreateTmpPurchase);
        $('#show_create_tmp_purchase_dlg').on('click', regEventOnTmpPurchase);
        $('#create_tmp_purchase').on('click', createTmpInorder);
        
        $('#tmp_inorder_complate').click(tmpInorderComplate);

        //外包临采逻辑 2018-03-09
        $('#create_tmp_outsourcer_purchase').on('click', createTmpOutsourcerInorder);
        $('._j_purchase_history_orders').on('click', getSkuOrderList);
        $('._j_purchase_2_history_orders').on('click', getSkuOrderList2);
        $('#tmp_outsourcer_wid').on('change', changeOutsourcerShow);
        changeOutsourcerShow();
        
        // 临采新逻辑 2016-07-06 【下线】
        $('.save_tmp_had_bought').on('click', saveTmpHadBought);
        $('#_j_save_tmp_2_inorder').click(saveTmpPurchased2Inorder);
        
        if ($('#_j_left_tmp_2_inorder').length){
            getLeftTmp2Inorder();
        }
        
        // 临采老逻辑 [下线]
        $('._j_del_temp_had_purchased').click(delTempHadPurchased);

        $('#in_order_type').change(changeInOrderType);

        //采购单审核
        $('.change_in_order_status').on('click', changeInOrderStatus);
	}

	function changeOutsourcerShow() {
        $('#tmp_outsourcer_id option').each(function () {
            if($(this).val() != 0 && $('#tmp_outsourcer_wid').val() != $(this).attr('data-wid'))
            {
                if($(this).is(":checked"))
                {
                    $('#tmp_outsourcer_id option:first').prop("selected", 'selected');
                }
                $(this).hide();
            }else{
                $(this).show();
            }
        })
    }

    function showSupplierList(){
        var box = $('#dlgSupplierList');
        var para = {
                keyword: box.find('input[name=keyword]').val()
            };

        K.post('/warehouse/ajax/dlg_supplier_list.php', para, function(ret){
            $('#supplier_list_area').html(ret.html);
        });
        
        box.modal();
    }
    
    function addSupplierEvent(evt){
        if ($(evt.target).hasClass('_j_select_supplier')){
            onSelectSupplier(evt);
        } else if ($(evt.target).hasClass('_j_order_search_supplier')){
            showSupplierList();
        }
    }

	function _onSeachSupplierKeydown(ev) {
		if(event.keyCode==13){
			showSupplierList(ev);
		}
	}

	function onSelectSupplier(ev) {
		var tgt = $(ev.target),
			form = tgt.closest('tr'),
			sid = form.find('._j_item_sid').text(),
			name = form.find('._j_item_name').text(),
			contact_name = form.find('._j_item_contact_name').text(),
			phone = form.find('._j_item_phone').text(),
			inOrderForm = $('._j_in_order_form');

		inOrderForm.find('input[name=sid]').val(sid);
		inOrderForm.find('input[name=name]').val(name);
		inOrderForm.find('input[name=contact_name]').val(contact_name);
		inOrderForm.find('input[name=contact_phone]').val(phone);
		$('#dlgSupplierList').modal('hide');
	}

	function _getOrderFormInfo() {
		var form = $('._j_in_order_form');
		var para = {
			sid: form.find('input[name=sid]').val(),
			contact_name: form.find('input[name=contact_name]').val(),
			contact_phone: form.find('input[name=contact_phone]').val(),
			privilege: $('input[name=privilege]').val(),
			privilege_note: $('input[name=privilege_note]').val(),
			delivery_date: $('input[name=delivery_date]').val(),
			note : $('textarea[name=note]').val()
		};
		return para;
	}
    
    // 未找到调用，暂且注释掉；如果使用，请修改其中的pid->sid
//	function onSaveOrderQuick(ev) {
//
//		var para = _getOrderFormInfo();
//		var products = [];
//
//		$('._j_product').each(function(){
//			var cb = $(this),
//				pid = cb.data('pid'),
//				num = parseInt(cb.find('input[name=num]').val()),
//				price = parseFloat(cb.find('input[name=price]').val());
//			if (K.isNumber(num)) {
//				products.push(pid + ':' + num + ':' + price);
//			}
//		});
//
//		para.product_str = products.join(',');
//
//		K.post('/warehouse/ajax/save_order_quick.php', para, _onSaveOrderEditSuccess);
//	}


	function _onSaveOrderEditSuccess(data) {
		alert('采购单已生成');
		window.location.href = '/warehouse/edit_in_order.php?oid=' + data.oid;
	}

    /*=====================  临采逻辑  =====================*/
    function tmpPurchaseSelectAll(){
        if ($('#tmp_purchase_select_all').is(':checked')) //全选
        {
            $('.temp_product_list').find('input[name=wait_inorder]').each(function(){
                this.checked=true;
            });
        }
        else //取消全选
        {
            $('.temp_product_list').find('input[name=wait_inorder]').each(function(){
                this.checked=false;
            });
        }
    }
    
    // 显示创建临采单的确认框
    function showCreateTmpPurchase(evt){
        evt.preventDefault();
        
        var plist = [];
        var dataObj, domObj;
        $('.temp_product_list').find('input[name=wait_inorder]').each(function(){
            if($(this).is(':checked')){
                domObj = $(this).closest('.product');
                dataObj = {};
                dataObj.pid = domObj.attr('data-pid');
                dataObj.sid = domObj.attr('data-sid');
                dataObj.oid = domObj.attr('data-oid');
                dataObj.vnum = domObj.attr('data-vnum');
                plist.push(dataObj);
            }
        });
        
        if (plist.length == 0){
            alert('请选择商品！'); return;
        }
        
        var wid = $('body').find('select[name=wid]').val();
        if (wid.length==0 || wid=='0'){
            alert('请选择一个仓库'); return;
        }
        
        var para = {
            wid: wid,
            plist: JSON.stringify(plist)
        };
        
    //    $(this).attr('disabled', true);
        K.post('/warehouse/ajax/show_create_tmp_purchase.php', para, 
            function(ret){
                $('#show_create_tmp_purchase_dlg').find('.modal-body').html(ret.html);
                $('#show_create_tmp_purchase_dlg').modal();
            },
            function(err){
                alert(err.errmsg);
                $('#show_create_tmp_purchase').attr('disabled', false);
            }
        );
    }
    
    function regEventOnTmpPurchase(evt){
        var target = evt.target;
        if ($(target).hasClass('save_real_purchase'))
        {
            var obj = $(target).closest('._j_product');
            var realPurchase = obj.find('input[name=real_purchase]').val();
            var vPurchase = obj.find('input[name=real_purchase]').attr('data-vnum');
            var diffNum = realPurchase - vPurchase;
            if (diffNum < 0){
                alert('实际采购数量不能小于订单虚采数量！'); return false;
            } else if (diffNum == 0){
                alert('实际采购数量未修改，不用保存！'); return false;
            }
            
            obj.find('.had_buy').html(realPurchase);
            obj.find('.diff_num').html(diffNum);
        }
    }
    
    // 创建临采单
    function createTmpInorder(){
        var products = [];
        $('#show_create_tmp_purchase_dlg').find('._j_product').each(function(){
            var _product = {};
            _product.pid = $(this).attr('data-pid');
            _product.sid = $(this).attr('data-sid');
            _product.real_vnum = $(this).find('input[name=real_purchase]').val();
            _product.price = $(this).find('input[name=price]').val();
            _product.order_info = [];
            $(this).find('.order_info').each(function(){
                var oinfo = {};
                oinfo.sid = _product.sid;
                oinfo.oid = $(this).attr('data-oid');
                oinfo.vnum = $(this).attr('data-vnum');
                _product.order_info.push(oinfo);
            });
            
            products.push(_product);
        });
        
        if(products.length == 0){
            alert('请选择商品！'); return false;
        }
        
        var para = {
            wid: $('select[name=wid]').val(),
            buy_date: $('input[name=delivery_date]').val(),
            product_list: JSON.stringify(products)
        };
        
        if (para.wid.length==0 || para.wid=='0'){
            alert('请选择仓库'); return false;
        }
        if (para.buy_date.length==0){
            alert('请选择配送日期'); return false;
        }
        
        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/create_tmp_2_inorder.php', para, 
            function(ret){
                if(ret.oid){
                    window.location.href='/warehouse/edit_in_order.php?oid='+ret.oid
                } else {
                    alert('创建采购单失败，请联系管理员！！');return;
                }
                $('#create_tmp_purchase').attr('disabled', false);
            },
            function(err){
                alert(err.errmsg);
                $('#create_tmp_purchase').attr('disabled', false);
            }
        );
    }

    // 创建外包临采单
    function createTmpOutsourcerInorder(evt){
        var products = [];
        $('.temp_product_list').find('.product').each(function(){
            if($(this).find('input[name=wait_inorder]').is(':checked')) {
                var _product = {};
                _product.pid = $(this).attr('data-pid');
                _product.sid = $(this).attr('data-sid');
                _product.cost = $(this).attr('data-cost');
                _product.num = $(this).attr('data-num');
                _product.amount = $(this).attr('data-amount');
                products.push(_product);
            }
        });

        if(products.length == 0){
            alert('请选择商品！'); return false;
        }

        var para = {
            wid: $('select[name=wid]').val(),
            sid: $('select[name=outsourcer_id]').val(),
            bdate: $('input[name=bdate]').val(),
            edate: $('input[name=edate]').val(),
            product_list: JSON.stringify(products)
        };

        if (para.wid.length==0 || para.wid=='0'){
            alert('请选择仓库'); return false;
        }
        if (para.sid.length==0 || para.sid == '0')
        {
            alert('请选择供应商');return false;
        }
        if (para.bdate.length==0){
            alert('请选择配送开始日期'); return false;
        }
        if (para.edate.length==0){
            alert('请选择配送结束日期'); return false;
        }

        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/create_tmp_outsourcer_inorder.php', para,
            function(ret){
                if(ret.oid){
                    window.location.href='/warehouse/edit_in_order.php?oid='+ret.oid
                } else {
                    alert('创建外包临采单失败，请联系管理员！！');return;
                }
                $('#create_tmp_outsourcer_purchase').attr('disabled', false);
            },
            function(err){
                alert(err.errmsg);
                $('#create_tmp_outsourcer_purchase').attr('disabled', false);
            }
        );
    }
    
    
    // 删除已经临采的商品.
    function delTempHadPurchased(){
        var obj = $(this).closest('.purchase_product');
        
        var para = {
            sid: obj.find('.sku_id').html(),
            wid: $('select[name=wid]').val(),
            buy_date: $('input[name=buy_date]').val()
        };
        
        K.post('/warehouse/ajax/del_temp_had_purchased.php', para, function(ret){
            if (ret.st){
                obj.hide();
            } else {
                alert('删除失败！');return;
            }
        });
    }
    
    // 获取供应商的采购历史商品
    function getSupplierHistoryProducts() {
        // 取历史采购商品
        var oid = $(this).attr('data-oid');
        var box = $('#supplierHistoryProduct');
        
        box.attr('data-oid', oid);
        
        K.post('/warehouse/ajax/get_supplier_history_products.php', {oid:oid}, function(ret){
            if (ret.st==1){
                box.find('#show_coopworker_area').html(ret.html);
                box.modal();   
            } else {
                alert('系统错误！');
            }
        });
    }

    // 获取采购商品的相关订单和退单信息
    function getSkuOrderList() {
        // 取历史采购商品
        var oid = $(this).attr('data-oid');
        var sid = $(this).attr('data-sid');
        var box = $('#showSkuHistory');
        box.find('#showSkuHistoryList').html('');
        K.post('/warehouse/ajax/get_sku_inorder_detail.php', {oid:oid,sid:sid}, function(ret){
            if (ret.st==1){
                box.find('#showSkuHistoryList').html(ret.html);
                box.modal();
            } else {
                alert('系统错误！');
            }
        });
    }
    function getSkuOrderList2() {
        // 取历史采购商品
        var sku_id = $(this).attr('data-sid');
        var para = {
            sku_id: sku_id,
            wid: $('select[name=wid]').val(),
            sid: $('select[name=outsourcer_id]').val(),
            bdate: $('input[name=bdate]').val(),
            edate: $('input[name=edate]').val()
        };

        if(para.wid == '' || para.wid == 0)
        {
            alert('请选择仓库');
            return;
        }
        if(para.bdate == '' || para.bdate == 0)
        {
            alert('请选择配送起始日期');
            return;
        }
        if(para.edate == '' || para.edate == 0)
        {
            alert('请选择配送结束日期');
            return;
        }
        if(para.bdate > para.edate)
        {
            alert('配送起始日期不能大于结束日期');
            return;
        }

        var box = $('#showSkuHistory');
        box.find('#showSkuHistoryList').html('');
        K.post('/warehouse/ajax/get_sku_2_inorder_detail.php', para, function(ret){
            if (ret.st==1){
                box.find('#showSkuHistoryList').html(ret.html);
                box.modal();
            } else {
                alert('系统错误！');
            }
        });
    }
    
    // 保存历史购买中选购的商品
    function saveHistoryInorderProduct(){
        var productList = [];
        var box = $('#supplierHistoryProduct');
        var historyProducts = box.find('._j_product');
        
        if (historyProducts.length != 0){
            
            var temp;
            historyProducts.each(function(){
                temp = {};
                temp.sid = $(this).attr('data-sid');
                temp.price = $(this).find('input[name=price]').val()*100;
                temp.num = $(this).find('input[name=num]').val()*1;
                
                if (temp.price!=0 && temp.num!=0){
                    productList.push(temp);
                }
            });
            
            var para = {
                oid: box.attr('data-oid'),
                source: $(this).attr('data-source'),
                product_list: JSON.stringify(productList)
            };
            K.post('/warehouse/ajax/save_history_inorder_product.php', para, function(){
                alert('保存成功！');
                window.location.reload();
            });
        }
        
        box.modal('hide');
        
    }
    
    // 修改采购单商品单价数量
    function modifyInorderProduct(){
        var box = $('#modifyInorderModal'),
            obj = $(this).closest('tr');
    
        box.attr('data-sid', obj.attr('data-sid'));
        box.find('input[name=pname]').val(obj.find('.pname a').html());
        
        var price = obj.find('.pprice').attr('data-price');
        var inorderNum = obj.find('.pnum').attr('data-inorder');
        var source = $(this).attr('data-source');
        var in_order_source = $(this).attr('data-in-order-source');
        box.find('input[name=pprice]').val(price);
        box.find('input[name=pprice]').parent().find('span').html('（原价：'+ price +'）');
        box.find('input[name=pinorder_num]').val(inorderNum);
        box.find('input[name=pinorder_num]').parent().find('span').html('（原采购数量：'+ inorderNum +'）');
        box.find('input[name=pstockin_num]').val(obj.find('.pnum').attr('data-stockin'));
        
        if (source != '1'){
            box.find('input[name=pinorder_num]').attr('disabled', true);
        }
        if (in_order_source == '1'){
            box.find('input[name=pprice]').attr('disabled', true);
        }
        box.find('#_j_modify_inorder_submit').attr('data-source', source);
        
        box.modal();
    }
    
    function modifyInorderSubmit(){
        var box = $('#modifyInorderModal');
        var para = {
            oid: box.attr('data-oid'),
            sid: box.attr('data-sid'),
            price: box.find('input[name=pprice]').val(),
            num: box.find('input[name=pinorder_num]').val()*1,
            source: $(this).attr('data-source')
        };
        
        var stockInNum = box.find('input[name=pstockin_num]').val()*1;
        
        if (para.num < stockInNum){
            alert('采购数量不能小于入库数量！'); return;
        }

        if (para.price == 0)
        {
            alert('采购价格不能为0！');return;
        }
        
        K.post('/warehouse/ajax/modify_inorder_product.php', para, function(){
            window.location.reload();
        });
    }
    
    function confirmPrePay(){
        var _obj = $('#financePrePay');
        var para = {
            in_orderId: $(this).attr('data-oid'),
            payment_type: _obj.find('select[name=payment_type]').val(),
            paid_source: _obj.find('select[name=paid_source]').val(),
            real_amount: _obj.find('input[name=real_amount]').val(),
            note: _obj.find('textarea[name=note]').val(),
            type: 3 //财务预付
        };
        
        if (para.paid_source == 0){
            alert('请选择支付来源！');return;
        }
        
        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/confirm_paid.php', para, function(){
            alert('支付成功！');
            window.location.reload();
        });
    }


    
    /*==================== Will Del =======================*/
    
    function saveTmpHadBought(){
        var dlg = $(this).closest('.product');
        var para = {
            oid: dlg.attr('data-oid'),
            pid: dlg.attr('data-pid'),
            wid: $('form').find('select[name=wid]').val(),
            buy_num: dlg.attr('data-buynum')
        };
        
        var handler = $(this);
        handler.attr('disabled', true);
        K.post('/warehouse/ajax/save_tmp_had_bought.php', para, 
            function(){
                alert('操作已成功！');
                window.location.reload();
            },
            function(err){
                alert(err.errmsg);
                handler.attr('disabled', false);
            }
        );
    }

    // 临采商品创建采购单
    function saveTmpPurchased2Inorder(){
        var productList = [];
        $('.temp_product_list').find('input[name=temp_product]').each(function(){
         
            if($(this).is(':checked')){
                var _product = {};
                var _obj = $(this).closest('.product');
                _product.sid = _obj.attr('data-sid');
                _product.price = _obj.find('input[name=price]').val();
                _product.num = _obj.attr('data-inorder_num')*1;
                _product.oid = _obj.attr('data-oid');
                _product.pid = _obj.attr('data-pid');
                
                if (_product.price==0 || _product.num==0){
                    alert('选择的商品【单价】或【数量】为0， 请核验后，再提交！！');
                    return;
                }
                
                productList.push(_product);
            }
            
        });
        
        if(productList.length == 0){
            alert('请选择商品！'); return false;
        }
        
        var para = {
            wid: $('select[name=wid]').val(),
            buy_date: $('input[name=delivery_date]').val(),
            product_list: JSON.stringify(productList)
        };
        
        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/save_temp_purchased_2_inorder.php', para, 
            function(ret){
                if(ret.oid){
                    window.location.href='/warehouse/edit_in_order.php?oid='+ret.oid
                } else {
                    alert('创建采购单失败，请联系管理员！！');return;
                }
                $('#_j_save_tmp_2_inorder').attr('disabled', false);
            },
            function(err){
                alert(err.errmsg);
                $('#_j_save_tmp_2_inorder').attr('disabled', false);
            }
        );
    }
    
    function getLeftTmp2Inorder(){
        var obj = $('#_j_left_tmp_2_inorder');
        var para = {
            wid: obj.attr('data-wid'),
            delivery_date: obj.attr('data-delivery_date')
        };
        
        K.post('/warehouse/ajax/get_tmp_no_inorder.php', para, function(res){
            $('#_j_left_tmp_2_inorder').html(res.html);
        });
    }
    
    // 临采采购单-完成
    function tmpInorderComplate(){
        var para = {
            oid: $('form').find('input[name=oid]').val()
        };
        
        $(this).attr('disabled', true);
        if (confirm('确认完成【临采单】，请仔细核对商品的数量和价格！')){
            K.post('/warehouse/ajax/complate_tmp_inorder.php', para, function(){
                alert('更新成功！');
                window.location.reload();
            });
        }
    }

    function changeInOrderType() {
        var in_order_type = $(this).val();
        var type_list = $('.payment_type');
        $('input[name=payment_type]').each(function () {
            $(this).removeAttr('checked');
        })
        if (in_order_type == 1)
        {
            type_list.each(function () {
                if ($(this).attr('data-payment-type') < 4)
                {
                    $(this).css('display', 'inline');
                }
                else if ($(this).attr('data-payment-type') == 4)
                {
                    $(this).css('display', 'none');
                }
            });
        }
        else if (in_order_type == 2)
        {
            type_list.each(function () {
                if ($(this).attr('data-payment-type') < 4)
                {
                    $(this).css('display', 'none');
                }
                else if ($(this).attr('data-payment-type') == 4)
                {
                    $(this).css('display', 'inline');
                    $(this).find('input[value="4"]').prop('checked', true);
                }
            });
        }
    }

    function changeInOrderStatus() {
        var para = {
            oid: $(this).attr('data-oid'),
            status: $(this).attr('data-status')
        };

        $(this).attr('disabled', true);
        K.post('/warehouse/ajax/change_in_order_status.php', para, function () {
            alert('操作成功！');
            window.location.reload();
        });
    }

	main();

})();