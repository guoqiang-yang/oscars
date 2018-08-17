(function () {
	var curProducts = {};

	function main() {
		$('#_j_btn_save_order_edit').click(onSaveOrderEdit);
		$('._j_chg_order_step').click(onChgOrderStep);
		$('#add_product').click(onOrderSelectProduct);
	}

	// 保存商品
	function onSaveProducts(ev) {
		$('._j_product_item').each(function() {
			var cb = $(this),
				pid = cb.data('pid'),
				num = parseInt(cb.find('input[name=num]').val()),
				note = cb.find('input[name=note]').val(),
				name = $('#product_name_' + pid).html(),
				price = $('#product_price_' + pid).data('price'),
				cate = $('#product_cate_' + pid).html(),
				carrier_fee = $('#carrier_fee_' + pid).html(),
				carrier_fee_ele = $('#carrier_fee_ele_' + pid).html();

			if (K.isNumber(num)) {
				if (num > 0) {
					var product = {
						pid: pid, num: num, note: note,
						pname: name, price: price, cate: cate,
						carrier_fee: carrier_fee, carrier_fee_ele: carrier_fee_ele
					};
					if (undefined == curProducts[pid]) {
						curProducts[pid] = product;
					} else if (num != curProducts[pid].num) {
						curProducts[pid].num = num;
					}
				} else {
					delete(curProducts[pid]);
				}
			}
		});

		_redrawProducts();

		$('#dlgAddProduct').modal('hide');
	}

	// 删除订单商品
	function onDeleteOrderProduct(ev) {
		var tgt = $(ev.currentTarget),
			pid = tgt.closest('tr').data('pid');

		if (confirm('确认删除该商品？')) {
			delete(curProducts[pid]);
			_redrawProducts();
		}
	}

	//显示商品
	function _redrawProducts() {
		var productsStr = '';
		var price = 0;
		var carrier_fee = 0;
		var carrier_fee_ele = 0;
		for (var key in curProducts) {
			var p = curProducts[key];

			productsStr += '<tr class="_j_product" data-pid="' + p.pid + '">\
					<td>' + p.pid + '</td>\
					<td>' + p.pname + '</td>\
					<td>' + p.cate + '</td>\
					<td>' + p.num +'</td>\
					<td>￥' + (p.price / 100) + '</td>\
					<td>￥' + (p.num * p.price / 100) + '</td>\
					<td>' + p.note + '</td>\
					<td><a href="javascript:void(0);" class="_j_del_order_product">删除</a></td>\
					</tr>';

			price += p.num * p.price;
			carrier_fee += p.num * p.carrier_fee;
			carrier_fee_ele += p.num * p.carrier_fee_ele;
			console.log(carrier_fee);
		}

		if (price > 0) {
			productsStr += '<tr>\
				<td>价格汇总:</td>\
				<td colspan="9">￥<span id="product_total_price">' + (price / 100) + '</span></td>\
			  </tr>';
		}

		$('#order-price').val(price);
		$('#carry-fee').val(carrier_fee);
		$('#carry-fee-ele').val(carrier_fee_ele);
		$('#product_list').html(productsStr);

		$('._j_del_order_product').bind('click', onDeleteOrderProduct);
	}

	// 选择商品
	function onOrderSelectProduct(ev) {
		ev.preventDefault();

		var tgt = $(ev.currentTarget),
			oid = $('#dlgAddProduct').data('oid'),
			para = {href : tgt.attr('href'), oid : oid};

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

	//保存订单
	function onSaveOrderEdit(ev) {
		var para = _getOrderFormInfo();

		// 计算总优惠金额
		para.privilege = 0;
		$('input[name=usedcoupon]').each(function() {
			if($(this).is(':checked')) {
				para.privilege += $(this).attr('data-price') * 1;
			}
		});
		para.privilege += $('input[name=privilege_special]').val() * 1;


		para.product_str = _getProductsStr();
		para.order_step = 1;

		K.post('/order/ajax/save_order.php', para, _onSaveOrderEditSuccess);
	}

	//保存订单成功
	function _onSaveOrderEditSuccess(data) {
		alert('保存成功');
		window.location.href = '/order/edit_order.php?oid=' + data.oid;
	}

	//客服确认，只是step不同而已
	function onChgOrderStep(ev) {
		var para = _getOrderFormInfo();

		// 计算总优惠金额
		para.privilege = 0;
		$('input[name=usedcoupon]').each(function() {
			if($(this).is(':checked')) {
				para.privilege += $(this).attr('data-price')*1;
			}
		});
		para.privilege += $('input[name=privilege_special]').val()*1;

		para.product_str = _getProductsStr();
		para.order_step = 3;

		K.post('/order/ajax/save_order.php', para, _onSaveOrderEditSuccess);
	}

	//获取订单信息
	function _getOrderFormInfo() {
		var para = {
			oid: $('input[name=oid]').val(),
			cid: $('input[name=cid]').val(),
            uid: $('input[name=uid]').val(),
			wid:$('select[name=wid]').val(),
			contact_name: $('input[name=contact_name]').val(),
			contact_phone: $('input[name=contact_phone]').val(),
			contact_phone2: $('input[name=contact_phone2]').val(),
			city : $('select[name=city]').val(),
			district: $('select[name=district]').val(),
			area: $('select[name=area]').val(),
			address: $('input[name=address]').val(),
			freight: $('input[name=freight]').val(),
			privilege: $('input[name=privilege]').val(),
			privilege_note: $('input[name=privilege_note]').val(),
			delivery_date: $('input[name=delivery_date]').val(),
			delivery_time: $('select[name=delivery_time]').val(),
			order_step: $('select[name=order_step]').val(),
			note: $('textarea[name=note]').val(),
			driver_name: $('input[name=driver_name]').val(),
			driver_phone: $('input[name=driver_phone]').val(),
			driver_money: $('input[name=driver_money]').val(),
			payment_type: $('select[name=payment_type]').val(),
			service: $('#service').val(),
			floor_num: $('#floor-num').val(),
			customer_carriage: $('#carry_fee').val()
		};

		return para;
	}

	//获取商品信息，id，数量，备注等
	function _getProductsStr() {
		var products = [];

		for (var key in curProducts) {
			var p = curProducts[key];
			var pid = p.pid;
			var num = parseInt(p.num);
			var note = p.note;

			products.push(pid + ':' + num + ':' + note);
		}

		return products.join(',');
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