(function () {
    var curProducts = {};
	function main() {
		$('#_j_btn_save_exchanged').click(onSaveExchanged);
        $('#_j_btn_cancel_exchanged').click(onCancelExchanged);
        $('#_j_btn_delete_exchanged').click(onDeleteExchanged);
		$('._j_chg_exchanged_step').click(onChgExchangedStep);
		$('#reason_type').change(changeReasonDetail);
        $('#reason_detail').change(showReasonDetail);
        $(document).on('click','#add_product',onOrderSelectProduct);
        $('#need_storage').change(changeNeedStorage);
        $('#type').change(changeDelivery);
		$(document).ready(getRefundReason);
        var exchanged_product =$('#exchanged_products').attr('data-products');
        exchanged_product = eval('(' + exchanged_product + ')');
        if(exchanged_product){
            $.each(exchanged_product,function (n, value) {
                var product = {
                    pid: value.pid, num: value.num,
                    pname: value.pname, price: value.price, cate: value.cate
                };
                curProducts[value.pid] = product;
            });
            _redrawProducts();
        }
        if($('need_storage').val() == 0)
        {
            $('#carry_fee').attr('disabled', true).val(0);
            $('#freight').attr('disabled', true).val(0);
            $('#privilege').attr('disabled', true).val(0);
        }
	}

    function changeDelivery() {
        var type = $('#type').val();
        if(type == 1){
            $('#select_delivery').show();
            $('#virtual_products').show();
        }else{
            $('#select_delivery').hide();
            $('#virtual_products').hide();
        }
    }

    function changeNeedStorage() {
        var need_storage = $('#need_storage').val();
        if(need_storage == 1)
        {
            $('#carry_fee').attr('disabled', false);
            $('#freight').attr('disabled', false);
            $('#privilege').attr('disabled', false);
        }else{
            $('#carry_fee').attr('disabled', true).val(0);
            $('#freight').attr('disabled', true).val(0);
            $('#privilege').attr('disabled', true).val(0);
        }
        var tmp_str = $('#refund_list').html();
        $('#refund_list').html($('#exchanged_list').html());
        $('#exchanged_list').html(tmp_str);
    }

	function onSaveExchanged(ev) {
		var para = {
			eid: $('#eid').val(),
			oid: $('#oid').val(),
			wid: $('#wid').val(),
			type: $('#type').val(),
            need_storage: $('#need_storage').val(),
			reason_type: $('#reason_type').val(),
            reason_detail: $('#reason_detail').val(),
			note: $('#note').val(),
            carry_fee: Math.round(100 * $('#carry_fee').val()) ,
            freight: Math.round(100 * $('#freight').val()),
            privilege: Math.round(100 * $('#privilege').val())
		};

		if (para.type == 0)
        {
            alert('请选择换货类型！');
            return false;
        }

		if (para.type == 1)
        {
            var date = $('#select_delivery_date').val();
            var	hour_start = $('#select_delivery_time').val();
            var hour_end = $('#select_delivery_time_end').val();

            if (date == '' || parseInt(hour_start) <= 0 || parseInt(hour_end) <= 0)
            {
                alert('请选择配送时间！');
                return false;
            }
            if(parseInt(hour_start)>parseInt(hour_end))
            {
                alert('配送起始时间不能大于结束时间！');
                return false;
            }
            para.delivery_date = date;
            para.delivery_time = hour_start;
            para.delivery_time_end = hour_end;
        }

        if (parseInt(para.reason_type) == 0 || parseInt(para.reason_detail) == 0) {
            alert('请选择换货原因！');

            return false;
        }
        var need_storage = $('#reason_detail').find("option:selected").data("storage");
        if(need_storage != para.need_storage)
        {
            var reason_str = need_storage > 0 ? '需要入库':'不需要入库';
            alert(reason_str);
            return false;
        }

		var refund_products = [];
        var total = 0;

        var is_false = false;
        var product_list = $('#refund_products').find('._j_product');
        product_list.each(function () {
            var product = {};
            var pid = $(this).attr('data-pid');
            var num = $(this).find('input[name=apply_rnum]').val();
            var price = $(this).find('input[name=price]').val();
            var can_refund_num = $(this).find('input[name=can_refund_num]').val();
            var sid = $(this).attr('data-sid');
            if(parseInt(num)>0){
                if(parseInt(can_refund_num)<parseInt(num)){
                    alert('商品ID:'+pid+' 可退数量小于申请退货数量！');
                    is_false = true;
                    return false;
                }
                product.pid = pid;
                product.num = num;
                product.price = price;
                product.sid = sid;
                refund_products.push(product);
                total += num;
            }
        });
        if(is_false)
        {
            return false;
        }
        if (parseInt(total) <= 0)
        {
            alert('请填写退货商品数量！');
            return false;
        }
        var exchanged_products = [];
        var product_list2 = $('#exchanged_products').find('._j_product');
        var total2 = 0;
        var virtual_product_list = $('#virtual_products').find('._j_product');
        if (para.type == 1)
        {
            virtual_product_list.each(function () {
                var product = {};
                var pid = $(this).attr('data-pid');
                var num = $(this).find('input[name=exchanged_num]').val();
                var price = $(this).find('input[name=price]').val();
                var sid = $(this).attr('data-sid');
                if(parseInt(num)>0){
                    product.pid = pid;
                    product.num = num;
                    product.price = price;
                    product.sid = sid;
                    exchanged_products.push(product);
                    total2 += num;
                }
            });
        }
        product_list2.each(function () {
            var product = {};
            var pid = $(this).attr('data-pid');
            var num = $(this).find('input[name=exchanged_num]').val();
            var price = $(this).find('input[name=price]').val();
            var sid = $(this).attr('data-sid');
            if(parseInt(num)>0){
                product.pid = pid;
                product.num = num;
                product.price = price;
                product.sid = sid;
                exchanged_products.push(product);
                total2 += num;
            }
        });

        if (parseInt(total2) <= 0)
        {
            alert('请填写换货商品数量！');
            return false;
        }
        if(para.need_storage == 1)
        {
            para.relproducts = JSON.stringify(refund_products);
            para.excproducts = JSON.stringify(exchanged_products);
        }else{
            para.relproducts = JSON.stringify(exchanged_products);
            para.excproducts = JSON.stringify(refund_products);
        }


		if (!para.eid && !confirm("您是否确定将所选产品换货？")) {
			return false;
		}

		if (!confirm("你选择的换货仓库是：" + para.wid + "号仓库")) {
			return false;
		}

		$(this).attr('disabled', true);
		K.post('/order/ajax/save_exchanged.php', para,
            function(data){
                window.location.href = '/order/edit_exchanged.php?eid=' + data.eid;
            },
            function(err){
                alert(err.errmsg);
                $('#_j_btn_save_exchanged').attr('disabled', false);
                return false;
            }
        );
	}
    
	// 审核通过换货单
	function onChgExchangedStep(ev) {
        var para = {
            eid: $('#eid').val(),
            method: 'audit'
        };
        if(confirm('确定审核通过该换货单？'))
        {
            $(this).attr('disabled', true);
            K.post('/order/ajax/change_exchanged.php', para, function(){
                alert('审核通过换货单操作已成功');
                window.location.reload();
            });
        }
	}

    // 删除换货单
    function onDeleteExchanged() {
        var para = {
            eid: $('#eid').val(),
            method: 'delete'
        };
        if(confirm('确定删除该换货单？'))
        {
            $(this).attr('disabled', true);
            K.post('/order/ajax/change_exchanged.php', para, function(){
                alert('删除换货单操作已成功');
                window.location.href='/aftersale/exchanged_list.php';
            });
        }
    }
    // 取消换货单
    function onCancelExchanged() {
        var para = {
            eid: $('#eid').val(),
            method: 'cancel'
        };
        if(confirm('确定取消该换货单？'))
        {
            $(this).attr('disabled', true);
            K.post('/order/ajax/change_exchanged.php', para, function(){
                alert('取消换货单操作已成功');
                window.location.href='/aftersale/exchanged_list.php';
            });
        }
    }

	function changeReasonDetail() {
	    var type = $('#reason_type').val();
		var reasons = $('#reason_detail').attr('data-reason-detail');
        var obj = eval('(' + reasons + ')');
        var reason_detail = obj[type];
        var html = '<option value="0">-请选择-</option>';
        if(reason_detail){
            $.each(reason_detail,function (n, value) {
                html += '<option data-storage="' + value.need_storage + '" value="' + n + '">' + value.name + '</option>';
            });
        }
        $('#reason_detail').html(html);
    }

    function showReasonDetail() {
        var reason_id = $('#reason_detail').val();
        if(reason_id > 0){
            var need_storage = $('#reason_detail').find("option:selected").data("storage");
            var reason_str = need_storage > 0 ? '需要入库':'不需要入库';
        }else{
            var reason_str = '';
        }

        $('#reason_detail_desc').html(reason_str);
    }

    function getRefundReason() {
        var type = $('#reason_type').val();
        var reason = $('#reason_detail').attr('data-reason');
        var reasons = $('#reason_detail').attr('data-reason-detail');
        var obj = eval('(' + reasons + ')');
        var reason_detail = obj[type];
        var html = '<option value="0">-请选择-</option>';
        if(reason_detail){
            $.each(reason_detail,function (n, value) {
                if (parseInt(reason) == n)
                {
                    html += '<option data-storage="' + value.need_storage + '" value="' + n + '" selected>' + value.name + '</option>';
                    if(reason > 0){
                        var reason_str = value.need_storage > 0 ? '需要入库':'不需要入库';
                    }else{
                        var reason_str = '';
                    }
                    $('#reason_detail_desc').html(reason_str);
                }
                else
                {
                    html += '<option data-storage="' + value.need_storage + '" value="' + n + '">' + value.name + '</option>';
                }
            });
        }
        $('#reason_detail').html(html);
    }

    // 删除订单商品
    function onDeleteOrderProduct(ev) {
        var tgt = $(ev.currentTarget),
            pid = tgt.closest('tr').data('pid');

        if (confirm('确认删除该商品？')) {
            $(this).parent().parent().remove();
        }
    }

    //显示商品
    function _redrawProducts() {
        var productsStr = '';
        for (var key in curProducts) {
            var p = curProducts[key];
            var p_title = p.pname;
            if(p_title.indexOf("<a ") == -1)
            {
                p.pname = '<a href="/shop/edit_product.php?pid='+p.pid+'" target="_blank">'+p.pname+'</a>';
            }
            if(change_exchanged_status == true){
                productsStr += '<tr class="_j_product" data-pid="' + p.pid + '">\
					<td>' + p.pid + '</td>\
					<td>' + p.pname + '</td>\
					<td>' + p.cate + '</td>\
					<td>￥' + (p.price / 100) + '<input type="hidden" name="price" value="' + (p.price / 100) + '"></td>\
					<td><input style="width: 80px; text-align: center;" name="exchanged_num" type="text" class="form-control" value="'+p.num+'"/></td>\
					<td><a href="javascript:void(0);" class="_j_del_order_product">删除</a></td>\
					</tr>';
            }else{
                productsStr += '<tr class="_j_product" data-pid="' + p.pid + '">\
					<td>' + p.pid + '</td>\
					<td>' + p.pname + '</td>\
					<td>' + p.cate + '</td>\
					<td>￥' + (p.price / 100) + '<input type="hidden" name="price" value="' + (p.price / 100) + '"></td>\
					<td>'+p.num+'</td>\
					<td></td>\
					</tr>';
            }
        }
        $('#exchanged_products').html(productsStr);

        $('._j_del_order_product').bind('click', onDeleteOrderProduct);
    }

    // 保存商品
    function onSaveProducts(ev) {
        $('._j_product_item').each(function() {
            var cb = $(this),
                pid = cb.data('pid'),
                num = parseInt(cb.find('input[name=num]').val()),
                name = $('#product_name_' + pid).html(),
                price = $('#product_price_' + pid).data('price'),
                cate = $('#product_cate_' + pid).html();

            if (K.isNumber(num)) {
                if (num > 0) {
                    var product = {
                        pid: pid, num: num,
                        pname: name, price: price, cate: cate
                    };
                    if (undefined == curProducts[pid]) {
                        curProducts[pid] = product;
                    } else if (num != curProducts[pid].num) {
                        curProducts[pid].num = num;
                    }
                }
            }
        });

        _redrawProducts();

        $('#dlgAddProduct').modal('hide');
    }

    // 选择商品
    function onOrderSelectProduct(ev) {
        ev.preventDefault();

        var tgt = $(ev.currentTarget),
            para = {href : tgt.attr('href')};

        K.post('/order/ajax/dlg_get_products.php', para, _onGetProductsSuccess);
    }

    //显示结果，重新绑定事件
    function _onGetProductsSuccess(data) {
        $('#product_list_container').html('').append($(data.html));

        bindEvent();
    }

    //回车搜索
    function onOrderSearchProductKeydown(ev) {
        if(event.keyCode==13) {
            onOrderSearchProduct(ev);
        }
    }

    //搜索
    function onOrderSearchProduct(ev) {
        ev.preventDefault();

        var tgt = $(ev.currentTarget),
            oid = $('#dlgAddProduct').data('oid'),
            keyword = tgt.closest('._j_form').find('input[name=keyword]').val(),
            para = {keyword : keyword, oid : oid}

        K.post('/order/ajax/dlg_get_products.php', para, _onOrderSearchProductSuccess);
    }

    //显示结果，重新绑定事件
    function _onOrderSearchProductSuccess(data) {
        $('#product_list_container').html('').append($(data.html));

        bindEvent();
    }
    function bindEvent() {
        $('a._j_order_select_product').unbind('click').bind('click', onOrderSelectProduct);
        $('._j_order_search_product').unbind('click').bind('click', onOrderSearchProduct);
        $('._j_order_search_product').closest('._j_form').find('input[name=keyword]').unbind('keydown').bind('keydown', onOrderSearchProductKeydown);
        $('#_j_btn_save_products').unbind('click').bind('click', onSaveProducts);
        $('#_j_btn_save_products2').unbind('click').bind('click', onSaveProducts);
    }

	main();

})();